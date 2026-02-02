<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\localization,
	kodorvan\constructor\models\settings as model,
	kodorvan\constructor\models\telegram\processes\language\select as process_language_select;

// Library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Framework for Telegram
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\Telegram\Properties\ParseMode as mode,
	SergiX44\Nutgram\Telegram\Types\Message\Message as message,
	SergiX44\Nutgram\Handlers\Type\Command as command,
	SergiX44\Nutgram\Telegram\Types\Internal\InputFile as input,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as keyboard,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as button;

// Built-in libraries
use Error as error;

/**
 * Telegram settings
 *
 * @package kodorvan\constructor\models\telegram
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class settings extends core
{
	/**
	 * Language
	 *
	 * Write the language into the account and the robot instance
	 *
	 * @param telegram $robot The chat-robot instance
	 * @param language $language The language
	 *
	 * @return void
	 */
	public static function language(telegram $robot, language $language = LANGUAGE_DEFAULT): void
	{
		// Initializing the account
		$account = $robot->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing the menu message localization
			$localization = new localization($language);

			if ($localization instanceof localization) {
				// Initialized the localization

				// Initializing the account old language
				$from = $account->language;

				// Writing the language into the account
				$account->language = $language;

				// Serializing the account
				$account->serialize();

				// Writing the account into the database;
				$updated = $account->update();

				// Deserializing the account
				$account->deserialize();

				if ($updated instanceof account) {
					// Writed the account into the database

					// Writing the account into the robot instance
					$robot->set('account', $account);

					try {
						// Initializing the account new language
						$to = $account->language;
						
						// Sending the message
						$robot->sendMessage(
							text: "✅ *$localization->settings_language_update_success:* " . trim($from->flag() . ' ' . $from->label($to)) . ' → *' . trim($to->flag()  . ' ' . $to->label($to)) . '*',
							parse_mode: mode::MARKDOWN,
							disable_notification: true
						);

						// Sending the message
						$robot->answerCallbackQuery(
							text: $to->label($to),
							show_alert: false
						);
					} catch (error $error) {
						// Failed to send the message about language update

						// Writing into the errors output buffer
						error_log((string) $error);

						// Sending the message
						$robot->sendMessage(
							text: "❎ *$localization->settings_language_update_fail*",
							parse_mode: mode::MARKDOWN,
							disable_notification: true
						);

						// Ending the conversation process
						$robot->endConversation();
					}
				}
			}
		}
	}
}
