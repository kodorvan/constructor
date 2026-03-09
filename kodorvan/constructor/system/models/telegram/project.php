<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\localization,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\deal,
	kodorvan\constructor\models\project as model,
	kodorvan\constructor\models\project\enumerations\status as project_status,
	kodorvan\constructor\models\worker\enumerations\type as worker_type,
	kodorvan\constructor\models\telegram\processes\language\select as process_language_select,
	kodorvan\constructor\models\telegram\conversations\project as conversation_project;

// Library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Svoboda time
use svoboda\time\statement as svoboda;

// Baza database
use mirzaev\baza\database,
	mirzaev\baza\column,
	mirzaev\baza\record,
	mirzaev\baza\enumerations\encoding,
	mirzaev\baza\enumerations\type;

// Framework for Telegram
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\Telegram\Properties\ParseMode as mode,
	SergiX44\Nutgram\Telegram\Types\Message\Message as message,
	SergiX44\Nutgram\Handlers\Type\Command as command,
	SergiX44\Nutgram\Telegram\Types\Internal\InputFile as input,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as keyboard,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as button;

// Built-in libraries
use DateTime as datetime,
	Error as error;

/**
 * Telegram project
 *
 * @package kodorvan\constructor\models\telegram
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class project extends core
{
	/**
	 * Create
	 *
	 * Starting the project creating process
	 *
	 * @param telegram $robot The chat-robot instance
	 *
	 * @return void
	 */
	public static function create(telegram $robot): void
	{
		// Initializing the account
		$account = $robot->get('account');

		// Initializing the project record
		$record = new model()->write(account: $account->identifier);

		// Initializing the project
		$project = new model(record: $record);

		// Deserializing the record
		$project->deserialize();

		// Starting the project creating process
		conversation_project::begin(
			bot: $robot,
			userId: $robot->userId(),
			chatId: $robot->chatId(),
			data: ['instance' => $project]
		);
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
	public function accept(telegram $robot): void
	{
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

		if ($authorizations->system_deals) {
			// Authorized to deals (system)

			if ($authorizations->system_projects) {
				// Authorized to projects (system)

				if ($authorizations->system_invoices) {
					// Authorized to invoices (system)

					// The message
					$message = $robot->message();

					// The message text
					$text = $message?->text;

					// The project identifier
					preg_match('/^.*#(\d+)$/m', $text, $matches);
					$identifier = (int) $matches[1];
					unset($matches);

					// The deal
					$deal = new deal()->read(filter: fn(record $record) => $record->identifier === $identifier && $record->active === 1);

					// Deserializing the record
					$deal->deserialize();

					if ($deal instanceof deal) {
						// Initialized the deal

						// The project
						$project = $deal->project();

						if ($project instanceof model) {
							// Initialized the project

							if ($project->status === project_status::requested && $deal->confirmed === 0) {
								// The project deal is not confirmed

								// Initializing the keyboard
								$keyboard = keyboard::make();

								// Writing the row into the keyboard
								$keyboard->addRow(
									button::make(
										text: "🔏 $localization->project_accepted_button_prepayment",
										url: 'https://t.me/' . $robot->user()->username
									)
								);

								// Initializing the receiver account
								$receiver = $project->account();

								// Title
								$title = "🏗 *$localization->project_accepted_title*";

								// Description
								$description = $localization->project_accepted_description;

								// Prepayment
								$prepayment = "*$localization->project_accepted_prepayment:* $deal->prepayment" . $receiver->currency->symbol();

								// Documents
								$documents = $localization->project_accepted_documents;

								// Sending the message
								$robot->sendMessage(
									text: implode(
										"\n\n",
										array_filter([
											$title,
											$description,
											$documents,
											$prepayment
										])
									),
									chat_id: $receiver->identifier_telegram,
									parse_mode: mode::MARKDOWN,
									disable_notification: true,
									reply_markup: $keyboard
								);

								// Ending the conversation
								$robot->endConversation();

								// Writing the confirmation date
								$deal->confirmed = svoboda::timestamp();

								// Serializing the record
								$deal->serialize();

								// Updating the deal record
								$deal->update();

								// Deserializing the record
								$deal->deserialize();

								// Initializing the keyboard
								$keyboard = keyboard::make();

								// Writing the row into the keyboard
								$keyboard->addRow(
									button::make(
										text: "✉️ $localization->project_deal_button_chat",
										url: 'https://t.me/' . $robot->user()->username
									)
								);

								// Converting the confirmation to unixtime format
								$unixtime = $deal->confirmed + svoboda::datetime()->getTimestamp();

								// Confirmed
								/* $confirmed = "*$localization->project_accepted_confirmed*: ![" . $unixtime . '](tg://time?unix=' . $unixtime . '&format=r)'; */
								$confirmed = "$localization->project_accepted_confirmed: ![" . $unixtime . '](tg://time?unix=' . $unixtime . '&format=r)';

								// Updating the message buttons
								$message->editText(
									text: implode(
										"\n\n",
										[
											$message->getText(),
											$confirmed
										]
									),
									/* parse_mode: mode::MARKDOWN, */
									reply_markup: $keyboard
								);
							} else {
								// The project deal is confirmed

								// Sending the message
								$robot->sendMessage(
									text: "⚠️ *$localization->project_accepted_already_confirmed*",
									parse_mode: mode::MARKDOWN,
								);

								// Ending the conversation
								$robot->endConversation();
							}
						} else {
							// Not initialized the project

							// Sending the message
							$robot->sendMessage(
								text: "⚠️ *$localization->project_accepted_project_not_found*",
								parse_mode: mode::MARKDOWN,
							);

							// Ending the conversation
							$robot->endConversation();
						}
					} else {
						// Not initialized the project

						// Sending the message
						$robot->sendMessage(
							text: "⚠️ *$localization->project_accepted_deal_not_found*",
							parse_mode: mode::MARKDOWN,
						);

						// Ending the conversation
						$robot->endConversation();
					}
				} else {
					// Not authorized to invoices (system)

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
		} else {
			// Not authorized to deals (system)

			// Sending the message
			$robot->sendMessage(
				text: "⛔ *$localization->not_authorized_system_deals*",
				parse_mode: mode::MARKDOWN,
			);

			// Ending the conversation
			$robot->endConversation();
		}
	}
}
