<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\processes\language;

// Files of the project
use kodorvan\constructor\models\core;

// Library for languages support
use mirzaev\languages\language;

// Baza database
use mirzaev\baza\record;

// Framework for Telegram
use Zanzara\Context as context,
	Zanzara\Telegram\Type\Message as message;

/**
 * Telegram language select 
 *
 * @package kodorvan\constructor\models\telegram\processes\language
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class select extends core
{
	/**
	 * Language
	 *
	 * Send the language choose menu
	 *
	 * @param context $context Request data from Telegram
	 * @param string $prefix Prefix for 'callback_data' (`$prefix . $language->name`)
	 * @param string $title Title of the message
	 * @param string $description Description of the message
	 * @param array $exclude Languages that will be excluded ['ru', 'en'...]
	 *
	 * @return void
	 */
	public static function menu(context $context, string $prefix, string $title, string $description, array $exclude = []): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof record) {
			// Initialized the account

			// Initializing language 
			$language = $context->get('language');

			if ($language) {
				// Initialized language

				// Initializing localization 
				$localization = $context->get('localization');

				if ($localization) {
					// Initialized localization

					// Declaring the buffer of generated keyboard with languages
					$keyboard = [];

					// Initializing the iterator of rows
					$row = 0;

					// Initializing buffer of languages
					$languages = language::cases();

					// Deleting the actual language from buffer of languages
					unset($languages[array_search($language, $languages, strict: true)]);

					// Sorting buffer of languages by the actual language
					$languages = [$language, ...$languages];

					foreach ($languages as $language) {
						// Iterating over languages

						// Skipping excluded languages
						if (array_search($language->name, $exclude, strict: true) !== false) continue;

						// Initializing the row
						$keyboard[$row] ??= [];

						// Writing the language choose button into the buffer of generated keyboard with languages
						$keyboard[$row][] = [
							'text' => ($language->flag() ? $language->flag() . ' ' : '') . $language->label($language),
							'callback_data' => $prefix . $language->name
						];

						// When reaching 4 buttons in a row, move to the next row
						if (count($keyboard[$row]) === 4) ++$row;
					}

					// Writing the button for helping lozalizing
					$keyboard[$row === 0 && empty($keyboard[0]) ? 0 : ++$row] = [
						[
							'text' => '🗂 ' . $localization['select_language_button_add'],
							'url' => 'https://git.svoboda.works/kodorvan\constructor/src/branch/stable/kodorvan/neurobot/system/localizations'
						]
					];

					// Sending the message
					$context->sendMessage(
						$title ?? '🌏 *' . $localization['select_language_title'] . "*\n" . ($description ?? $localization['select_language_description']),
						[
							'reply_markup' => [
								'inline_keyboard' => $keyboard,
								'disable_notification' => true
							],
						]
					);
				} else {
					// Not initialized localization

					// Sending the message
					$context->sendMessage('⚠️ *Failed to initialize localization*')
						->then(function (message $message) use ($context) {
							// Sended the message

							// Ending the conversation process
							$context->endConversation();
						});
				}
			} else {
				// Not initialized language

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize language*')
					->then(function (message $message) use ($context) {
						// Sended the message

						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize your Telegram account*')
				->then(function (message $message) use ($context) {
					// Sended the message

					// Ending the conversation process
					$context->endConversation();
				});
		}
	}
}
