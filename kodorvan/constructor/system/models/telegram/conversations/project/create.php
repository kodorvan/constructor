<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\conversations\project;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\localization,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\project\enumerations\type as project_type,
	kodorvan\constructor\models\project\enumerations\purpose as project_purpose,
	kodorvan\constructor\models\telegram\processes\language\select as process_language_select;

// Library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

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
use Error as error;

/**
 * Telegram project
 *
 * @package kodorvan\constructor\models\telegram
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class create extends menu
{
	/**
	 * Text
	 *
	 * @var string $text The message text
	 */
	public string $text = '';

	/**
	 * Type
	 *
	 * @var project_type $type The project type
	 */
	public project_type $type;

	/**
	 * Purpose
	 *
	 * @var project_purpose $purpose The project purpose
	 */
	public project_purpose $purpose;

	/**
	 * Start
	 * 
	 * Generate the project create menu and start the process
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function start(telegram $robot): void
	{
		// Initializing the language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the menu message localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Initializing the project development cost
		$cost = $this->cost();

		// Generating the message text
		$text = implode(
			"\n\n",
			[
				"🏛 *$localization->project_create_title*",
				$cost > 0 ? "*$localization->project_create_cost:* " . $cost . ($account->currency?->symbol() ?? CURRENCY_DEFAULT->symbol()) : $localization->project_create_description
			]
		);

		if ($this->text !== $text) {
			$this->menuText(
				text: $text,
				opt: [
					'parse_mode' => mode::MARKDOWN
				]
			);
		}

		if (isset($this->type)) {
			// Initialized the project type

			// Initializing the buffer for the first row
			$first = [];

			// Writing the project type button into the buffer of the first row
			$first[0] = button::make(
				text: $localization['project_type_' . $this->type?->name] ?? $this->type?->label(language: $language),
				callback_data: '@types'
			);

			if (isset($this->purpose) || $this->type === project_type::complex) {
				// Initialized the project purpose

				// Writing the project purpose button into the buffer of the first row
				$first[1] = button::make(
					text: $localization['project_purpose_' . $this->purpose?->name] ?? $this->purpose?->label(language: $language),
					callback_data: '@purposes'
				);

				// Writing the project buttons first row
				$this->addButtonRow(...$first);

				// Initializing the row
				$row = [];

				// Initializing the maximum amount of buttons in a row
				$break = 2;

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::parser,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Integrations

					if (isset($this->integrations)) {
						// Initialized the project integrations

					} else {
						// Not initialized the project integrations

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::parser,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Server

					if (isset($this->server)) {
						// Initialized the project server

					} else {
						// Not initialized the project server

					}
				}

				if (match ($this->type) {
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Interface

					if (isset($this->interface)) {
						// Initialized the project interface

						if ($this->type === project_type::calculator) {
							// Calculator

							// site, mobile or desktop program
						} else if ($this->type === project_type::crm) {
							// CRM

							// site, mobile or desktop program
						} else if ($this->type === project_type::program) {
							// Program

							// mobile or desktop
						}
					} else {
						// Not initialized the project interface

						if ($this->type === project_type::calculator) {
							// Calculator

							// site, mobile or desktop program
						} else if ($this->type === project_type::crm) {
							// CRM

							// site, mobile or desktop program
						} else if ($this->type === project_type::program) {
							// Program

							// mobile or desktop
						}
					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::parser,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Repository

					if (isset($this->repository)) {
						// Initialized the project repository

					} else {
						// Not initialized the project repository

					}
				}

				if (match ($this->type) {
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Testing

					if (isset($this->testing)) {
						// Initialized the project testing

					} else {
						// Not initialized the project testing

					}
				}

				if (match ($this->type) {
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Security

					if (isset($this->security)) {
						// Initialized the project security

					} else {
						// Not initialized the project security

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::parser,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Documenting

					if (isset($this->documenting)) {
						// Initialized the project documenting

					} else {
						// Not initialized the project documenting

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Localization

					if (isset($this->localization)) {
						// Initialized the project localization

					} else {
						// Not initialized the project localization

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::parser,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::complex => true,
					default => false
				}) {
					// Journal

					if (isset($this->journal)) {
						// Initialized the project journal

					} else {
						// Not initialized the project journal

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Scalability

					if (isset($this->scalability)) {
						// Initialized the project scalability

					} else {
						// Not initialized the project scalability

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::crm,
					project_type::site,
					project_type::complex => true,
					default => false
				}) {
					// Framework

					if (isset($this->framework)) {
						// Initialized the project framework

					} else {
						// Not initialized the project framework

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::parser,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Database

					if (isset($this->database)) {
						// Initialized the project database

					} else {
						// Not initialized the project database

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Depth of development

					if (isset($this->depth)) {
						// Initialized the project depth of development

					} else {
						// Not initialized the project depth of development

					}
				}

				if (match ($this->type) {
					project_type::chat_robot,
					project_type::parser,
					project_type::calculator,
					project_type::crm,
					project_type::site,
					project_type::program,
					project_type::complex => true,
					default => false
				}) {
					// Form of cooperation

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
					text: "🔸 $localization->project_create_button_purpose",
					callback_data: '@purposes'
				);

				// Writing the project buttons first row
				$this->addButtonRow(...$first);
			}
		} else {
			// Not initialized the project type

			// Writing the project type button
			$this->addButtonRow(
				button::make(
					text: "🔸 $localization->project_create_button_type",
					callback_data: '@types'
				)
			);
		}

		if ($cost > 0) {
			// The project development cost was calculated

			// Writing the project type button
			$this->addButtonRow(
				button::make(
					text: "☑️ $localization->project_create_button_request",
					callback_data: '@request'
				)
			);
		}

		// Updating the message and saving its text
		$this->text = $this->orNext('stop')->showMenu()->text;
	}

	/**
	 * Types
	 * 
	 * Generate the project type select menu
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function types(telegram $robot): void
	{
		// Initializing the language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the menu message localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Updating the message text
		$this->menuText(
			text: implode(
				"\n\n",
				[
					"⚙️ *$localization->project_create_types_title*",
					$localization->project_create_types_description,
				]
			),
			opt: [
				'parse_mode' => mode::MARKDOWN
			]
		);

		// Deleting the message buttons
		$this->clearButtons();

		// Initializing the row
		$row = [];

		// Declaring the buffer of the row buttons length
		$length = 0;

		// Initializing the maximum amount of buttons in a row
		$break = 4;

		// Initializing buffer of types
		$types = project_type::cases();

		if (isset($this->type)) {
			// Initialized the selected type

			// Initializing the selected purpose index
			$selected = array_search($this->type ?? null, $types, strict: true);

			if ($selected !== false) {
				// Found the selected type index

				// Exclude the selected type from buffer of types
				unset($types[$selected]);
			}
		}

		// Declaring the generated buttons registry
		$generated = [];

		foreach ($types as $index => $type) {
			// Iterating over types

			if (array_search($type, $generated)) {
				// The type button is already generated

				// Skipping the iteration
				continue;
			}

			if ($length + $type->length() > $break && !empty($row)) {
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
			$length += $type->length();

			// Writing the type button into the row
			$row[] = button::make(
				text: $localization['project_type_' . $type->name] ?? $type->label(language: $language),
				callback_data: "$type->name@type"
			);

			// Initializing the next type
			$next = $types[$index + 1] ?? null;

			if ($next?->length() >= $break) {
				// The next type is the full-length button

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
					text: $localization['project_type_' . $next->name] ?? $next->label(language: $language),
					callback_data: "$next->name@type"
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

		// Deinitializing deprecated variables
		unset($row, $limit, $length, $generated, $types, $type);

		// Updating the message and saving its text
		$this->text = $this->showMenu()->text;
	}

	/**
	 * Type
	 * 
	 * Write the project type
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function type(telegram $robot): void
	{
		// Initializing the project type
		$this->type = project_type::{$robot->callbackQuery()->data};

		// Deinitializing the project purpose
		unset($this->purpose);

		if (count($this->type->purposes()) === 1) {
			// The project type has only 1 purpose

			// Initializing the project purpose
			$this->purpose = $this->type->purposes()[0];
		}

		// Deleting the message buttons
		$this->clearButtons();

		// Deleting the message buttons
		$this->start(robot: $robot);
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
		// Initializing the language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the menu message localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Updating the message text
		$this->menuText(
			text: implode(
				"\n\n",
				[
					"🛠 *$localization->project_create_purposes_title*",
					$localization->project_create_purposes_description,
				]
			),
			opt: [
				'parse_mode' => mode::MARKDOWN
			]
		);

		// Deleting the message buttons
		$this->clearButtons();

		// Initializing the row
		$row = [];

		// Declaring the buffer of the row buttons length
		$length = 0;

		// Initializing the maximum amount of buttons in a row
		$break = 4;

		// Initializing buffer of purposes
		$purposes = $this->type->purposes();

		if (isset($this->purpose)) {
			// Initialized the selected purpose

			// Initializing the selected purpose index
			$selected = array_search($this->purpose ?? null, $purposes, strict: true);

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

			// Initializing the coefficient
			$coefficient = $purpose->coefficient();

			// Writing the purpose button into the row
			$row[] = button::make(
				/* text: ($localization['project_purpose_' . $purpose->name] ?? $purpose->label(language: $language)) . (!empty($coefficient) ? ' x' . $coefficient : ''), */
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

				// Initializing the coefficient
				$coefficient = $next->coefficient();

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

		if (!empty($row) > 0) {
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

		// Updating the message and saving its text
		$this->text = $this->showMenu()->text;
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
		// Initializing the project purpose
		$this->purpose = project_purpose::{$robot->callbackQuery()->data};

		// Deleting the message buttons
		$this->clearButtons();

		// Deleting the message buttons
		$this->start(robot: $robot);
	}

	/**
	 * Cost
	 * 
	 * Calculate the project development cost
	 *
	 * @return int|float The project development cost
	 */
	public function cost(): int|float
	{
		// Declaring the project development cost
		$cost = 0;

		if (isset($this->type)) {
			// Initialized the project type

			// Calculating the project development cost
			$cost = $this->type->cost();
		}

		if (isset($this->purpose)) {
			// Initialized the project purpose

			// Calculating the project development cost
			$cost *= $this->purpose->coefficient();
		}

		// Exit (success)
		return $cost;
	}

	/**
	 * Request
	 * 
	 * 
	 *
	 * @param telegram $robot The robot
	 *
	 * @return void
	 */
	public function request(telegram $robot): void
	{
		// Initializing the language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the menu message localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Sending the message
		$robot->sendMessage(
			text: "✅ *$localization->project_create_requested*",
			parse_mode: mode::MARKDOWN,
			disable_notification: true
		);

		// Stopping conversation
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
		// Initializing the language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the menu message localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Sending the message
		$robot->sendMessage(
			text: "⚠️ *$localization->project_create_cancelled*",
			parse_mode: mode::MARKDOWN,
			disable_notification: true
		);

		// Stopping conversation
		$this->end();
	}
}
