<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\conversations;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\localization,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\deal,
	kodorvan\constructor\models\deal\enumerations\direction as deal_direction,
	kodorvan\constructor\models\project as model,
	kodorvan\constructor\models\project\enumerations\architecture as project_architecture,
	kodorvan\constructor\models\project\enumerations\purpose as project_purpose,
	kodorvan\constructor\models\project\enumerations\integration as project_integration,
	kodorvan\constructor\models\project\enumerations\status as project_status,
	kodorvan\constructor\models\worker\enumerations\type as worker_type,
	kodorvan\constructor\models\telegram\processes\language\select as process_language_select;

// Library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Baza database
use mirzaev\baza\database,
	mirzaev\baza\column,
	mirzaev\baza\record,
	mirzaev\baza\enumerations\encoding,
	mirzaev\baza\enumerations\type;

// Framework for Telegram
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\Conversations\InlineMenu as menu,
	SergiX44\Nutgram\Telegram\Properties\ParseMode as mode,
	SergiX44\Nutgram\Handlers\Type\Command as command,
	SergiX44\Nutgram\Telegram\Types\Message\Message as message,
	SergiX44\Nutgram\Telegram\Types\Internal\InputFile as input,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as keyboard,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as button;

// Built-in libraries
use Exception as exception,
	Error as error;

/**
 * Telegram project
 *
 * @package kodorvan\constructor\models\telegram
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class project extends menu
{
	/*
	 * Instance
	 * 
	 * @var model $instance The project instance
	 */
	public model $instance;

	/**
	 * Previous
	 *
	 * @var string $previous The message previous text
	 */
	public string $previous = '';

	/**
	 * Cost
	 *
	 * @var int|float $cost Cost per hour
	 */
	public int|float $cost = PROJECT_COST_HOUR_DEFAULT ?? 0;

	/**
	 * Messages
	 *
	 * Registry of messages for cleaning
	 *
	 * @var array $messages
	 */
	public array $messages = [];

	/**
	 * Workers
	 *
	 * Registry of project developments workers
	 *
	 * @var array $workers
	 */
	public array $workers = [];

	/**
	 * Description
	 *
	 * @var string $description The project description (512 symbols)
	 */
	public string $description = '';

	/**
	 * Start
	 * 
	 * Generate the project create menu and start the process
	 *
	 * @param telegram $robot The robot
	 * @param bool $new Create a new process?
	 *
	 * @return void
	 */
	public function start(telegram $robot, bool $new = true, ?model $instance = null): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		if ($new) {
			// New

			// Ending the conversation
			$robot->endConversation();

			if ($instance instanceof model) {
				// Received the project instance

				// Writing into the property
				$this->instance = $instance;
			}
		}

		// Deleting the message buttons
		$this->clearButtons();

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Title
		$title = "🏛 *$localization->project_title*";

		// Declaring the message generation variables
		$payment = $warnings = [];
		$welcome = $time = null;

		// Initializing the project development workers
		if (empty($this->workers)) $this->workers = $this->instance->architecture?->workers() ?? [];

		if ($new) {
			// New

			// Writing the project create message content
			$welcome = $localization->project_description;
		} else {
			// Continue

			// Hours
			$hours = $this->instance->hours();

			// Days
			$days = ceil(($hours / PROJECT_DAY_HOURS) + PROJECT_DAY_ADDITIONAL);

			// Writing the project create message content (hours and days)
			$time = "*$localization->project_time:* $hours$localization->project_time_hours _\($days$localization->project_time_days\)_";

			if (isset($this->cost)) {
				// Initialized the project development cost

				// Calculating the project development costs
				$costs = $this->instance->payment(
					cost: $this->cost,
					hours: $hours,
					programmers: $this->workers[worker_type::programmer->name] ?? 0,
					designers: $this->designers[worker_type::designer->name] ?? 0,
					boosters: $this->boosters[worker_type::booster->name] ?? 0
				);

				// Writing the project create message full cost
				$payment['full'] = "*$localization->project_cost:* " . $costs['full'] . $account->currency->symbol();

				// Writing the project create message cost prepayment
				$payment['prepayment'] = "*$localization->project_cost_prepayment:* " . $costs['prepayment'] . $account->currency->symbol() . ' _\(' . PROJECT_COST_PREPAYMENT_PERCENTS . '%\)_';

				// Writing the project create message cost warning
				$warnings['cost'] = "⚠️ $localization->project_warning_cost";
			}
		}

		// Generating the message text
		$text = implode(
			"\n\n",
			array_filter(
				[
					$title,
					$welcome,
					$time,
					implode("\n", $payment),
					implode("\n", $warnings),
				]
			)
		);

		if ($this->previous !== $text) {
			// The message text was changed

			$this->menuText(
				text: $text,
				opt: [
					'parse_mode' => mode::MARKDOWN
				]
			);

			// Saving the message text
			$this->previous = $text;
		}

		// Initializing the row
		$row = [];

		// Initializing the maximum amount of buttons in a row
		$break = 3;

		if (isset($this->instance->architecture)) {
			// Initialized the project architecture

			// Initializing the buffer for the first row
			$first = [];

			// Writing the project architecture button into the buffer of the first row
			$first[0] = button::make(
				text: $localization['project_architecture_' . $this->instance->architecture?->name] ?? $this->instance->architecture?->label(language: $language),
				callback_data: '@architectures'
			);

			if (isset($this->instance->purpose)) {
				// Initialized the project purpose

				// Writing the project purpose button into the buffer of the first row
				$first[1] = button::make(
					text: $localization['project_purpose_' . $this->instance->purpose?->name] ?? $this->instance->purpose?->label(language: $language),
					callback_data: '@purposes'
				);

				// Writing the project buttons first row
				$this->addButtonRow(...$first);

				// Initializing the project integrations
				$integrations = $this->instance->purpose->integrations();

				if (!empty($integrations)) {
					// Integrations

					// Initializing the button text
					$text = unmarkdown(
						trim(
							implode(
								', ',
								array_map(
									fn(project_integration $integration) => $localization['project_integration_' . $integration?->name] ?? $integration?->label($language) ?? '',
									$this->instance->integrations,
								)
							),
							' '
						)
					);

					// Writing the project integrations button into the buffer of the first row
					$row[] = button::make(
						text: empty($text) ? $localization->project_button_integrations : $text,
						callback_data: '@integrations'
					);


					if (count($row) >= $break) {
						// The buttons row reach the limit

						// Writing the buttons row
						$this->addButtonRow(...$row);

						// Deinitializing the buttons row
						$row = [];
					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::parser,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Server

					if (isset($this->server)) {
						// Initialized the project server

					} else {
						// Not initialized the project server

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Interface

					if (isset($this->interface)) {
						// Initialized the project interface

						if ($this->instance->architecture === project_architecture::program) {
							// Program

							// mobile or desktop
						}
					} else {
						// Not initialized the project interface

						if ($this->instance->architecture === project_architecture::program) {
							// Program

							// mobile or desktop
						}
					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::parser,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Repository

					if (isset($this->repository)) {
						// Initialized the project repository

					} else {
						// Not initialized the project repository

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::parser,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Launch strategy (fast, quality, progressively)

					if (isset($this->strategy)) {
						// Initialized the project strategy

					} else {
						// Not initialized the project strategy

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Testing

					if (isset($this->testing)) {
						// Initialized the project testing

					} else {
						// Not initialized the project testing

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Security

					if (isset($this->security)) {
						// Initialized the project security

					} else {
						// Not initialized the project security

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::parser,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Documenting

					if (isset($this->documenting)) {
						// Initialized the project documenting

					} else {
						// Not initialized the project documenting

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Localization

					if (isset($this->localization)) {
						// Initialized the project localization

					} else {
						// Not initialized the project localization

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::parser,
					project_architecture::site,
					project_architecture::complex => true,
					default => false
				}) {
					// Journal

					if (isset($this->journal)) {
						// Initialized the project journal

					} else {
						// Not initialized the project journal

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Scalability

					if (isset($this->scalability)) {
						// Initialized the project scalability

					} else {
						// Not initialized the project scalability

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::site,
					project_architecture::complex => true,
					default => false
				}) {
					// Framework

					if (isset($this->framework)) {
						// Initialized the project framework

					} else {
						// Not initialized the project framework

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::parser,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Database

					if (isset($this->database)) {
						// Initialized the project database

					} else {
						// Not initialized the project database

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// Depth of development

					if (isset($this->depth)) {
						// Initialized the project depth of development

					} else {
						// Not initialized the project depth of development

					}
				}

				if (match ($this->instance->architecture) {
					project_architecture::chat_robot,
					project_architecture::parser,
					project_architecture::site,
					project_architecture::program,
					project_architecture::complex => true,
					default => false
				}) {
					// architecture of cooperation

					if (isset($this->cooperation)) {
						// Initialized the project cooperation

					} else {
						// Not initialized the project cooperation

					}
				}
			} else {
				// Not initialized the project purpose

				// Writing the project purpose button into the buffer of the first row
				$first[1] =	button::make(
					text: "🔸 $localization->project_button_purpose",
					callback_data: '@purposes'
				);

				// Writing the project buttons first row
				$this->addButtonRow(...$first);
			}
		} else {
			// Not initialized the project architecture

			// Writing the project architecture button
			$this->addButtonRow(
				button::make(
					text: "🔸 $localization->project_button_architecture",
					callback_data: '@architectures'
				)
			);
		}

		if (!empty($row)) {
			// The buttons row has buttons

			// Writing the buttons row
			$this->addButtonRow(...$row);

			// Deinitializing the buttons row
			$row = [];
		}

		if (!$new) {
			// The project development hours was calculated

			// Cost
			$cost = '🛠 ' . (isset($this->cost) ? "$localization->project_button_cost_per_hour: $this->cost" . $account->currency->symbol() : $localization->project_button_cost_per_hour);

			// Writing the project buttons
			$this->addButtonRow(
				button::make(
					text: $cost,
					callback_data: 'set@cost'
				),
				button::make(
					text: '🤠 ' . sprintf($localization->project_button_team, array_sum($this->workers), $localization->project_peoples),
					callback_data: 'open@team'
				)
			);


			// Writing the project cost per hour button
			$this->addButtonRow(
				button::make(
					text: "📦 $localization->project_button_request",
					callback_data: '@request'
				)
			);
		}

		// Updating the message
		$this->orNext('stop')->showMenu();
	}

	/**
	 * Continue
	 * 
	 * Generate the project create menu and continue the process
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function continue(telegram $robot): void
	{
		// Sending the process main menu
		$this->start(robot: $robot, new: false);
	}

	/**
	 * Team
	 * 
	 * Generate the project create team settings menu
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function team(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the project development workers
		if (empty($this->workers)) $this->workers = $this->instance->architecture?->workers() ?? [];

		// Clearing the message buttons
		$this->clearButtons();

		// Writing the row into the keyboard
		$this->addButtonRow(
			button::make(
				text: sprintf("🥷🏻 $localization->project_team_button_programmers", $this->workers[worker_type::programmer->name] ?? [], $localization->project_peoples),
				callback_data: 'set@programmers'
			)
		);

		// Writing the row into the keyboard
		$this->addButtonRow(
			button::make(
				text: sprintf("👽 $localization->project_team_button_designers", $this->workers[worker_type::designer->name] ?? [], $localization->project_peoples),
				callback_data: 'set@designers'
			),
			button::make(
				text: sprintf("🦹🏻‍♀️ $localization->project_team_button_boosters", $this->workers[worker_type::booster->name] ?? [], $localization->project_peoples),
				callback_data: 'set@boosters'
			)
		);

		// Writing the row into the keyboard
		$this->addButtonRow(
			button::make(
				text: "🔏 $localization->project_button_back",
				callback_data: '@continue'
			)
		);

		// Title
		$title = "🤠 $localization->project_team_title";

		// Description
		$description = $localization->project_team_description;

		// Warning: cost
		$warning_cost = '⚠️ ' . $localization->project_team_warning_cost;

		// Generating the message text
		$text = implode(
			"\n\n",
			array_filter(
				[
					$title,
					$description,
					$warning_cost,
				]
			)
		);

		if ($this->previous !== $text) {
			// The message text was changed

			// Updating the message text
			$this->menuText(
				text: $text,
				opt: [
					'parse_mode' => mode::MARKDOWN
				]
			);

			// Saving the message text
			$this->previous = $text;
		}

		// Updating the message 
		$this->orNext('continue')->showMenu();
	}

	/**
	 * Architectures
	 * 
	 * Generate the project architecture select menu
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function architectures(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Generating the message text
		$text = implode(
			"\n\n",
			array_filter(
				[
					"⚙️ *$localization->project_architectures_title*",
					$localization->project_architectures_description,
				]
			)
		);

		if ($this->previous !== $text) {
			// The message text was changed

			// Updating the message text
			$this->menuText(
				text: $text,
				opt: [
					'parse_mode' => mode::MARKDOWN
				]
			);

			// Saving the message text
			$this->previous = $text;
		}

		// Deleting the message buttons
		$this->clearButtons();

		// Initializing the row
		$row = [];

		// Declaring the buffer of the row buttons length
		$length = 0;

		// Initializing the maximum amount of buttons in a row
		$break = 4;

		// Initializing buffer of architectures
		$architectures = project_architecture::cases();

		if (isset($this->instance->architecture)) {
			// Initialized the selected architecture

			// Initializing the selected purpose index
			$selected = array_search($this->instance->architecture ?? null, $architectures, strict: true);

			if ($selected !== false) {
				// Found the selected architecture index

				// Exclude the selected architecture from buffer of architectures
				unset($architectures[$selected]);
			}
		}

		// Declaring the generated buttons registry
		$generated = [];

		foreach ($architectures as $index => $architecture) {
			// Iterating over architectures

			if (array_search($architecture, $generated)) {
				// The architecture button is already generated

				// Skipping the iteration
				continue;
			}

			if ($length + $architecture->length() > $break && !empty($row)) {
				// Reached the limit of buttons in a row

				// Writing the row into the menu
				$this->addButtonRow(...$row);

				// Writing buttons into the generated buttons registry
				$generated += $row;

				// Reinitializing the row buttons length
				$length = 0;

				// Reinitializing the row
				$row = [];
			}

			// Addition to row buttons length
			$length += $architecture->length();

			// Writing the architecture button into the row
			$row[] = button::make(
				text: $localization['project_architecture_' . $architecture->name] ?? $architecture->label(language: $language),
				callback_data: "$architecture->name@architecture"
			);

			// Initializing the next architecture
			$next = $architectures[$index + 1] ?? null;

			if ($next?->length() >= $break) {
				// The next architecture is the full-length button

				// Writing the row into the menu
				$this->addButtonRow(...$row);

				// Writing buttons into the generated buttons registry
				$generated += $row;

				// Reinitializing the row
				$row = [];

				// Reinitializing the row buttons length
				$length = 0;

				// Writing the button into the menu
				$this->addButtonRow(button::make(
					text: $localization['project_architecture_' . $next->name] ?? $next->label(language: $language),
					callback_data: "$next->name@architecture"
				));

				// Writing the button into the generated buttons registry
				$generated[] = $next;
			}
		}

		if (!empty($row)) {
			// The row was not writed 

			// Writing the row into the menu
			$this->addButtonRow(...$row);
		}

		// Deinitializing deprecated variables
		unset($row, $limit, $length, $generated, $architectures, $architecture);

		// Updating the message
		$this->orNext('continue')->showMenu();
	}

	/**
	 * Architecture
	 * 
	 * Write the project architecture
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function architecture(telegram $robot): void
	{
		// Initializing the language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the project architecture
		$this->instance->architecture = project_architecture::{$robot->callbackQuery()->data};

		// Clearing from deprecated parameters
		$this->clear();

		// Sending the popup notification
		$robot->answerCallbackQuery(
			text: $localization['project_architecture_' . $this->instance->architecture?->name] ?? $this->instance->architecture?->label(language: $language),
			show_alert: false
		);

		// Deleting the message buttons
		$this->clearButtons();

		// Sending the process main menu
		$this->continue(robot: $robot);
	}

	/**
	 * Purposes
	 * 
	 * Generate the project purpose select menu
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function purposes(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Generating the message text
		$text = implode(
			"\n\n",
			array_filter([
				"🛠 *$localization->project_purposes_title*",
				$localization->project_purposes_description,
			])
		);

		if ($this->previous !== $text) {
			// The message text was changed

			// Updating the message text
			$this->menuText(
				text: $text,
				opt: [
					'parse_mode' => mode::MARKDOWN
				]
			);

			// Saving the message text
			$this->previous = $text;
		}

		// Deleting the message buttons
		$this->clearButtons();

		// Initializing the row
		$row = [];

		// Declaring the buffer of the row buttons length
		$length = 0;

		// Initializing the maximum amount of buttons in a row
		$break = 4;

		// Initializing buffer of purposes
		$purposes = $this->instance->architecture->purposes();

		if (isset($this->instance->purpose)) {
			// Initialized the selected purpose

			// Initializing the selected purpose index
			$selected = array_search($this->instance->purpose ?? null, $purposes, strict: true);

			if ($selected !== false) {
				// Found the selected purpose index

				// Exclude the selected purpose from buffer of purposes
				unset($purposes[$selected]);
			}
		}

		// Declaring the generated buttons registry
		$generated = [];

		foreach ($purposes as $index => $purpose) {
			// Iterating over purposes

			if (array_search($purpose, $generated)) {
				// The purpose button is already generated

				// Skipping the iteration
				continue;
			}

			if ($length + $purpose->length() > $break && !empty($row)) {
				// Reached the limit of buttons in a row

				// Writing the row into the menu
				$this->addButtonRow(...$row);

				// Writing buttons into the generated buttons registry
				$generated += $row;

				// Reinitializing the row buttons length
				/* $length -= $break; */
				$length = 0;

				// Reinitializing the row
				$row = [];
			}

			// Addition to row buttons length
			$length += $purpose->length();

			// Writing the purpose button into the row
			$row[] = button::make(
				/* text: ($localization['project_purpose_' . $purpose->name] ?? $purpose->label(language: $language)) . (!empty($coefficient) ? ' x' . $coeffici🔹ent : ''), */
				text: $localization['project_purpose_' . $purpose->name] ?? $purpose->label(language: $language),
				callback_data: "$purpose->name@purpose"
			);

			// Initializing the next purpose
			$next = $purposes[$index + 1] ?? null;

			if ($next?->length() >= $break) {
				// The next purpose is the full-length button

				// Writing the row into the menu
				$this->addButtonRow(...$row);

				// Writing buttons into the generated buttons registry
				$generated += $row;

				// Reinitializing the row
				$row = [];

				// Reinitializing the row buttons length
				$length = 0;

				// Writing the button into the menu
				$this->addButtonRow(button::make(
					/* text: ($localization['project_purpose_' . $next->name] ?? $next->label(language: $language)) . (!empty($coefficient) ? ' x' . $coefficient : ''), */
					text: $localization['project_purpose_' . $next->name] ?? $next->label(language: $language),
					callback_data: "$next->name@purpose"
				));

				// Writing the button into the generated buttons registry
				$generated[] = $next;
			}
		}

		if (!empty($row)) {
			// The row was not writed 

			// Writing the row into the menu
			$this->addButtonRow(...$row);
		}

		// Writing the "special" button into the menu
		$this->addButtonRow(button::make(
			/* text: ($localization->project_purpose_special ?? project_purpose::special->label(language: $language)) . ' x' . project_purpose::special->coefficient(), */
			text: $localization->project_purpose_special ?? project_purpose::special->label(language: $language),
			callback_data: project_purpose::special->name . '@purpose'
		));

		// Deinitializing deprecated variables
		unset($row, $limit, $length, $generated, $purposes, $purpose);

		// Updating the message
		$this->orNext('continue')->showMenu();
	}

	/**
	 * Purpose
	 * 
	 * Write the project purpose
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function purpose(telegram $robot): void
	{
		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the project purpose
		$this->instance->purpose = project_purpose::{$robot->callbackQuery()->data};

		// Sending the popup notification
		$robot->answerCallbackQuery(
			text: $localization['project_purpose_' . $this->instance->purpose?->name] ?? $this->instance->purpose?->label(language: $language),
			show_alert: false
		);

		// Deleting the message buttons
		$this->clearButtons();

		// Sending the process main menu
		$this->continue(robot: $robot);
	}

	/**
	 * Integrations
	 * 
	 * Generate the project integrations select menu
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function integrations(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Generating the message text
		$text = implode(
			"\n\n",
			array_filter([
				"📡 *$localization->project_integrations_title*",
				$localization->project_integrations_description,
			])
		);

		if ($this->previous !== $text) {
			// The message text was changed

			// Updating the message text
			$this->menuText(
				text: $text,
				opt: [
					'parse_mode' => mode::MARKDOWN
				]
			);

			// Saving the message text
			$this->previous = $text;
		}

		// Deleting the message buttons
		$this->clearButtons();

		// Initializing the row
		$row = [];

		// Declaring the buffer of the row buttons length
		$length = 0;

		// Initializing the maximum amount of buttons in a row
		$break = 4;

		// Initializing buffer of integrations
		$integrations = $this->instance->purpose->integrations();

		// Declaring the generated buttons registry
		$generated = [];

		foreach ($integrations as $index => $integration) {
			// Iterating over integrations

			if ($length + $integration->length() > $break && !empty($row)) {
				// Reached the limit of buttons in a row

				// Writing the row into the menu
				$this->addButtonRow(...$row);

				// Writing buttons into the generated buttons registry
				$generated += $row;

				// Reinitializing the row buttons length
				$length = 0;

				// Reinitializing the row
				$row = [];
			}

			// Addition to row buttons length
			$length += $integration->length();

			// Initializing the target 
			$target = $this->instance->integrations[$integration->name] ?? null;

			// Writing the integration button into the row
			$row[] = button::make(
				text: (isset($target) && $target ? '🔘 ' : '') . ($localization['project_integration_' . $integration->name] ?? $integration->label(language: $language)),
				callback_data: "$integration->name@integration"
			);

			// Initializing the next integration
			$next = $integrations[$index + 1] ?? null;

			if ($next?->length() >= $break) {
				// The next integration is the full-length button

				// Writing the row into the menu
				$this->addButtonRow(...$row);

				// Writing buttons into the generated buttons registry
				$generated += $row;

				// Reinitializing the row
				$row = [];

				// Reinitializing the row buttons length
				$length = 0;

				// Initializing the target 
				$target = $this->instance->integrations[$integration->name] ?? null;

				// Writing the button into the menu
				$this->addButtonRow(button::make(
					text: (isset($target) && $target ? '🔹' : '') . ($localization['project_integration_' . $integration->name] ?? $integration->label(language: $language)),
					callback_data: "$integration->name@integration"
				));

				// Writing the button into the generated buttons registry
				$generated[] = $next;
			}
		}

		if (!empty($row) > 0) {
			// The row was not writed 

			// Writing the row into the menu
			$this->addButtonRow(...$row);
		}

		// Writing the "back" button into the menu
		$this->addButtonRow(
			button::make(
				text: "🔏 $localization->project_button_back",
				callback_data: '@continue'
			)
		);

		// Deinitializing deprecated variables
		unset($row, $limit, $length, $generated, $integrations, $integration);

		// Updating the message
		$this->orNext('continue')->showMenu();
	}

	/**
	 * Integration
	 * 
	 * Write the project integration
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function integration(telegram $robot): void
	{
		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the integration
		$integration = project_integration::{$robot->callbackQuery()->data};

		if (isset($this->instance->integrations[$integration->name])) {
			// Enabled

			// Disabling
			unset($this->instance->integrations[$integration->name]);
		} else {
			// Disabled

			// Enabling
			$this->instance->integrations = [$integration->name => $integration] + ($this->instance->integrations ?? []);
		};

		// Sending the popup notification
		$robot->answerCallbackQuery(
			text: $localization['project_integrations_' . (isset($this->instance->integrations[$integration->name]) ? 'enabled' : 'disabled')],
			show_alert: false
		);

		// Reopening the integrations menu
		$this->integrations(robot: $robot);
	}

	/**
	 * Cost
	 * 
	 * Write the project cost per hour
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function cost(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the user input message
		$message = $robot->message();

		// Initializing the user input message text
		$text = $message?->text;

		// Initializing the message data
		$data = $robot->callbackQuery()?->data;

		if (!empty($text) && $data !== 'set') {
			// Not empty text

			// Initializing the message filters
			$minimum = 2;
			$maximum = 5;

			// Writing the user input message into the messages registry
			$this->messages[] = $message;

			// Initializing the text length
			$length = mb_strlen($text);

			if ($length >= $minimum) {
				// More than minimum amount of symbols

				// Sanitizing
				$float = filter_var($text, FILTER_SANITIZE_NUMBER_FLOAT);

				if (filter_var($float, FILTER_VALIDATE_FLOAT)) {
					// Number

					// Writing the cost
					$this->cost = (float) $float;

					try {
						foreach ($this->messages as $message) {
							// Iterating over messages registry

							// Deleting the message
							$message?->delete();

							// Waiting just for rofls
							usleep(200);
						}
					} catch (exception $exception) {
						// Sending into the errors output buffer
						error_log($exception->getMessage());
					} finally {
						// Deinitializing the messages registry
						$this->messages = [];
					}

					// Sending the process main menu
					$this->continue(robot: $robot);
				} else {
					// Not a number

					// Sending the message
					$this->messages[] = $robot->sendMessage(
						text: implode(
							"\n\n",
							array_filter(
								[
									"⚠️ $localization->project_cost_error_not_a_number",
								]
							)
						),
						parse_mode: mode::MARKDOWN,
						disable_notification: true,
					);

					// Waiting for the user input
					$this->next('cost');
				}
			} else {
				// Less or equal than minimum amount of symbols

				// Sending the message
				$this->messages[] = $robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								sprintf(
									"⚠️ $localization->project_cost_error_distance",
									$minimum,
									$maximum
								)
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				);

				// Waiting for the user input
				$this->next('cost');
			}
		} else {
			// Empty text

			// Sending the message and reinitializing the messages registry
			$this->messages = [
				$robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								"✏️ *$localization->project_cost_title*",
								sprintf(
									$localization->project_cost_description,
									PROJECT_COST_HOUR_DEFAULT,
									CURRENCY_DEFAULT->symbol() ?? ''
								),
								$localization->project_cost_request,
								"⚠️ $localization->project_cost_warning"
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				)
			];

			// Waiting for the user input
			$this->next('cost');
		}
	}

	/**
	 * Programmers
	 * 
	 * Write the project programmers
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function programmers(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the user input message
		$message = $robot->message();

		// Initializing the user input message text
		$text = $message?->text;

		// Initializing the message data
		$data = $robot->callbackQuery()?->data;

		if (!empty($text) || $text == '0' and $data !== 'set') {
			// Not empty text

			// Initializing the message filters
			$minimum = PROJECT_WORKERS_PROGRAMMERS_MINIMUM;
			$maximum = PROJECT_WORKERS_PROGRAMMERS_MAXIMUM;

			// Writing the user input message into the messages registry
			$this->messages[] = $message;

			// Sanitizing
			$int = filter_var($text, FILTER_SANITIZE_NUMBER_INT);

			if (filter_var($int, FILTER_VALIDATE_INT) || $int == '0') {
				// Number

				if ($int >= $minimum and $int <= $maximum) {
					// Not reached limits

					// Writing the cost
					$this->workers[worker_type::programmer->name] = (int) $int;

					try {
						foreach ($this->messages as $message) {
							// Iterating over messages registry

							// Deleting the message
							$message?->delete();

							// Waiting just for rofls
							usleep(200);
						}
					} catch (exception $exception) {
						// Sending into the errors output buffer
						error_log($exception->getMessage());
					} finally {
						// Deinitializing the messages registry
						$this->messages = [];
					}

					// Sending the process team menu
					$this->team(robot: $robot);
				} else {
					// Reached limits

					// Sending the message
					$this->messages[] = $robot->sendMessage(
						text: implode(
							"\n\n",
							array_filter(
								[
									sprintf(
										"⚠️ $localization->project_team_programmers_error_amount",
										$maximum
									)
								]
							)
						),
						parse_mode: mode::MARKDOWN,
						disable_notification: true,
					);

					// Waiting for the user input
					$this->next('programmers');
				}
			} else {
				// Not a number

				// Sending the message
				$this->messages[] = $robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								"⚠️ $localization->project_team_error_not_a_number",
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				);

				// Waiting for the user input
				$this->next('programmers');
			}
		} else {
			// Empty text

			// Sending the message and reinitializing the messages registry
			$this->messages = [
				$robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								"✏️ *$localization->project_team_programmers_title*",
								$localization->project_team_programmers_description,
								$localization->project_team_programmers_request
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				)
			];

			// Waiting for the user input
			$this->next('programmers');
		}
	}

	/**
	 * Designers
	 * 
	 * Write the project designers
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function designers(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the user input message
		$message = $robot->message();

		// Initializing the user input message text
		$text = $message?->text;

		// Initializing the message data
		$data = $robot->callbackQuery()?->data;

		if (!empty($text) || $text == '0' and $data !== 'set') {
			// Not empty text

			// Initializing the message filters
			$minimum = PROJECT_WORKERS_DESIGNERS_MINIMUM;
			$maximum = PROJECT_WORKERS_DESIGNERS_MAXIMUM;

			// Writing the user input message into the messages registry
			$this->messages[] = $message;

			// Sanitizing
			$int = filter_var($text, FILTER_SANITIZE_NUMBER_INT);

			if (filter_var($int, FILTER_VALIDATE_INT) || $int == '0') {
				// Number

				if ($int >= $minimum and $int <= $maximum) {
					// Not reached limits

					// Writing the cost
					$this->workers[worker_type::designer->name] = (int) $int;

					try {
						foreach ($this->messages as $message) {
							// Iterating over messages registry

							// Deleting the message
							$message->delete();

							// Waiting just for rofls
							usleep(200);
						}
					} catch (exception $exception) {
						// Sending into the errors output buffer
						error_log($exception->getMessage());
					} finally {
						// Deinitializing the messages registry
						$this->messages = [];
					}

					// Sending the process team menu
					$this->team(robot: $robot);
				} else {
					// Reached limits

					// Sending the message
					$this->messages[] = $robot->sendMessage(
						text: implode(
							"\n\n",
							array_filter(
								[
									sprintf(
										"⚠️ $localization->project_team_designers_error_amount",
										$maximum
									)
								]
							)
						),
						parse_mode: mode::MARKDOWN,
						disable_notification: true,
					);

					// Waiting for the user input
					$this->next('designers');
				}
			} else {
				// Not a number

				// Sending the message
				$this->messages[] = $robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								"⚠️ $localization->project_team_error_not_a_number",
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				);

				// Waiting for the user input
				$this->next('designers');
			}
		} else {
			// Empty text

			// Sending the message and reinitializing the messages registry
			$this->messages = [
				$robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								"✏️ *$localization->project_team_designers_title*",
								$localization->project_team_designers_description,
								$localization->project_team_designers_request,
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				)
			];

			// Waiting for the user input
			$this->next('designers');
		}
	}

	/**
	 * Boosters
	 * 
	 * Write the project boosters
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function boosters(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the user input message
		$message = $robot->message();

		// Initializing the user input message text
		$text = $message?->text;

		// Initializing the message data
		$data = $robot->callbackQuery()?->data;

		if (!empty($text) || $text == '0' and $data !== 'set') {
			// Not empty text

			// Initializing the message filters
			$minimum = PROJECT_WORKERS_BOOSTERS_MINIMUM;
			$maximum = PROJECT_WORKERS_BOOSTERS_MAXIMUM;

			// Writing the user input message into the messages registry
			$this->messages[] = $message;

			// Sanitizing
			$int = filter_var($text, FILTER_SANITIZE_NUMBER_INT);

			if (filter_var($int, FILTER_VALIDATE_INT) || $int == '0') {
				// Number

				if ($int >= $minimum and $int <= $maximum) {
					// Not reached limits

					// Writing the cost
					$this->workers[worker_type::booster->name] = (int) $int;

					try {
						foreach ($this->messages as $message) {
							// Iterating over messages registry

							// Deleting the message
							$message?->delete();

							// Waiting just for rofls
							usleep(200);
						}
					} catch (exception $exception) {
						// Sending into the errors output buffer
						error_log($exception->getMessage());
					} finally {
						// Deinitializing the messages registry
						$this->messages = [];
					}

					// Sending the process team menu
					$this->team(robot: $robot);
				} else {
					// Reached limits

					// Sending the message
					$this->messages[] = $robot->sendMessage(
						text: implode(
							"\n\n",
							array_filter(
								[
									sprintf(
										"⚠️ $localization->project_team_boosters_error_amount",
										$maximum
									)
								]
							)
						),
						parse_mode: mode::MARKDOWN,
						disable_notification: true,
					);

					// Waiting for the user input
					$this->next('boosters');
				}
			} else {
				// Not a number

				// Sending the message
				$this->messages[] = $robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								"⚠️ $localization->project_team_error_not_a_number",
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				);

				// Waiting for the user input
				$this->next('boosters');
			}
		} else {
			// Empty text

			// Sending the message and reinitializing the messages registry
			$this->messages = [
				$robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter(
							[
								"✏️ *$localization->project_team_boosters_title*",
								$localization->project_team_boosters_description,
								$localization->project_team_boosters_request,
							]
						)
					),
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
				)
			];

			// Waiting for the user input
			$this->next('boosters');
		}
	}

	/**
	 * Clear
	 * 
	 * Deinitialize all deprecated parameters
	 *
	 * @return void
	 */
	public function clear(): void
	{
		// Initializing the project architecture purposes
		$purposes = $this->instance->architecture->purposes();

		if (empty($purposes)) {
			// The project architecture has no purposes

			// Initializing the project purpose
			$this->instance->purpose = project_purpose::special;
		} else if (count($purposes) === 1) {
			// The project architecture has only 1 purpose

			// Initializing the project purpose
			$this->instance->purpose = $purposes[0];
		} else if (isset($this->instance->purpose) && array_search($this->instance->purpose, $purposes) !== false) {
			// The project architecture purpose is the same from deprecated purpose

			// keep it
		} else {
			// The project can have other purposes

			// Deinitializing the deprecated project purpose
			unset($this->instance->purpose);
		}

		// Deinitializing integrations
		$this->instance->integrations = [];

		// Deinitializing workers
		$this->workers = [];
	}

	/**
	 * Request
	 * 
	 * Create the project and send it into operators chats
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function request(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Initializing the receivers registry
		$receivers = DEALS_RECEIVERS;

		// Architecture
		$architecture = unmarkdown($this->instance->architecture?->label(language: $language) ?? $localization->project_deal_empty);

		// Purpose
		$purpose = unmarkdown(isset($this->instance->purpose) ? $this->instance->purpose->label(language: $language) : $localization->project_deal_empty);

		// Hours
		$hours = $this->instance->hours();

		// Project
		$project = <<<TXT
			*$localization->project_deal_architecture:* $architecture
			*$localization->project_deal_purpose:* $purpose
		TXT;

		// Days
		$days = ceil(($hours / PROJECT_DAY_HOURS) + PROJECT_DAY_ADDITIONAL);

		// Time
		$time = "*$localization->project_deal_time:* $hours$localization->project_deal_time_hours _\($days$localization->project_deal_time_days\)_";

		// Calculating the project development costs
		$costs = $this->instance->payment(
			cost: $this->cost,
			hours: $hours,
			programmers: $this->workers[worker_type::programmer->name] ?? 0,
			designers: $this->designers[worker_type::designer->name] ?? 0,
			boosters: $this->boosters[worker_type::booster->name] ?? 0
		);

		// Creating the deal
		$deal = new deal()->write(
			account: $account->identifier,
			project: $this->instance->identifier,
			direction: deal_direction::outbound,
			description: $this->description,
			hours: $hours,
			cost: $this->cost,
			payment: $costs['full'],
			prepayment: $costs['prepayment'],
			programmers: $this->workers[worker_type::programmer->name] ?? 0,
			designers: $this->workers[worker_type::designer->name] ?? 0,
			boosters: $this->workers[worker_type::booster->name] ?? 0,
		);

		// Title
		$title = '*' . unmarkdown(sprintf("💸 $localization->project_deal_title", $deal->identifier)) . '*';

		// Payment
		$payment = [];

		// Writing the project create message full cost
		$payment['full'] = "*$localization->project_cost:* " . $costs['full'] . $account->currency->symbol();

		// Writing the project create message cost prepayment
		$payment['prepayment'] = "*$localization->project_cost_prepayment:* " . $costs['prepayment'] . $account->currency->symbol() . ' _\(' . PROJECT_COST_PREPAYMENT_PERCENTS . '%\)_';

		// Writing the project status
		$this->instance->status = project_status::requested;

		// Serializing the project record
		$this->instance->serialize();

		// Updating the project record
		$this->instance->update();

		// Deserializing the project record
		$this->instance->deserialize();

		// Generating the message text
		$text = implode(
			"\n\n",
			array_filter(
				[
					$title,
					$project,
					$time,
					implode(
						"\n",
						$payment
					)
				]
			)
		);

		// Initializing the keyboard
		$keyboard = keyboard::make();

		// Writing the row into the keyboard
		$keyboard->addRow(
			button::make(
				text: "✉️ $localization->project_deal_button_chat",
				url: 'https://t.me/' . $robot->user()->username
			)
		);

		// Writing the row into the keyboard
		$keyboard->addRow(
			button::make(
				text: "⚖️ $localization->project_deal_button_edit",
				callback_data: 'project_deal_edit'
			)
		);

		// Writing the row into the keyboard
		$keyboard->addRow(
			button::make(
				text: "❌ $localization->project_deal_button_refuse",
				callback_data: 'project_deal_decline'
			),
			button::make(
				text: "✅ $localization->project_deal_button_accept",
				callback_data: 'project_deal_accept'
			)
		);

		foreach ($receivers as $receiver) {
			// Iterating over receivers

			// Sending the message
			$robot->sendMessage(
				text: $text,
				chat_id: $receiver,
				parse_mode: mode::MARKDOWN,
				disable_notification: true,
				reply_markup: $keyboard
			);
		}

		// Sending the message
		$robot->sendMessage(
			text: "✅ *$localization->project_deal_requested*",
			parse_mode: mode::MARKDOWN,
			disable_notification: true
		);

		// Ending the conversation
		$this->end();
	}

	/**
	 * Stop
	 * 
	 * Delete the project create menu and terminating the process
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function stop(telegram $robot): void
	{
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Sending the message
		$robot->sendMessage(
			text: "⚠️ *$localization->project_cancelled*",
			parse_mode: mode::MARKDOWN,
			disable_notification: true
		);

		// Ending the conversation
		$this->end();
	}

	/**
	 * Accept
	 * 
	 * Accept the project and issue an invoice
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function project_create_accept(telegram $robot): void
	{
		error_log($robot->callbackQuery()->data);
		return;
		// Sending the "typing" action
		/* $robot->sendChatAction('typing'); */

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Initializing the account authorizations
		$authorizations = $account->authorizations();

		if ($authorizations->system_projects) {
			// Authorized to projects (system)

			if ($authorizations->system_invoices) {
				// Authorized to projects (system)

				// Initializing the keyboard
				$keyboard = keyboard::make();

				// Writing the row into the keyboard
				$keyboard->addRow(
					button::make(
						text: "🔏 $localization->project_accepted_button_prepayment",
						url: 'https://t.me/' . $robot->user()->username
					)
				);

				// Initializing the project development hours
				$hours = $this->instance->hours();

				// Initializing the project development costs
				$costs = $this->instance->payment(
					cost: $this->cost,
					hours: $hours,
					programmers: $this->workers[worker_type::programmer->name] ?? 0,
					designers: $this->designers[worker_type::designer->name] ?? 0,
					boosters: $this->boosters[worker_type::booster->name] ?? 0
				);

				// Initializing the receiver account
				$receiver = new account()->read(filter: fn(record $record) => $record->identifier === $this->instance->account);

				// Title
				$title = "🏗 *$localization->project_accepted_title*";

				// Description
				$description = $localization->project_accepted_description;

				// Prepayment
				$prepayment = "*$localization->project_accepted_prepayment:* " . $costs['prepayment'] . $receiver->currency->symbol() . ' _\(' . PROJECT_COST_PREPAYMENT_PERCENTS . '%\)_';

				// Documents
				$documents = $localization->project_accepted_documents;

				// Sending the message
				$robot->sendMessage(
					text: implode(
						"\n\n",
						array_filter([
							$title,
							$description,
							$prepayment,
							$documents
						])
					),
					chat_id: $receiver->telegram_identifier,
					parse_mode: mode::MARKDOWN,
					disable_notification: true,
					reply_markup: $keyboard
				);

				// Ending the conversation
				$this->end();
			} else {
				// Not authorized to projects (system)

				// Sending the message
				$robot->sendMessage(
					text: "⛔ *$localization->not_authorized_system_invoices*",
					parse_mode: mode::MARKDOWN,
				);

				// Ending the conversation
				$robot->endConversation();
			}
		} else {
			// Not authorized to projects (system)

			// Sending the message
			$robot->sendMessage(
				text: "⛔ *$localization->not_authorized_system_projects*",
				parse_mode: mode::MARKDOWN,
			);

			// Ending the conversation
			$robot->endConversation();
		}
	}
}
