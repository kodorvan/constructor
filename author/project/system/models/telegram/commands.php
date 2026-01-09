<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\telegram\processes\language\select as process_language_select;

// Library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Framework for Telegram
use Zanzara\Context as context,
	Zanzara\Telegram\Type\Message as message,
	Zanzara\Telegram\Type\Input\InputFile as file_input;

/**
 * Telegram commands
 *
 * @package kodorvan\constructor\models\telegram
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class commands extends core
{
	/**
	 * Start
	 *
	 * Responce for command: "/start"
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function start(context $context): void
	{
		static::menu($context);
	}

	/**
	 * Menu
	 *
	 * Responce for command: '/menu'
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function menu(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing the language
			$language = $context->get('language');

			if ($language instanceof language) {
				// Initialized the language

				// Initializing localization 
				$localization = $context->get('localization');

				if ($localization) {
					// Initialized localization

					// Initializing the title
					$title = '📋 *' . $localization['menu_title'] . '*';
	
					// Sending the message
					$context->sendMessage(
						<<<TXT
						$title
						TXT,
						[
							'reply_markup' => [
								'inline_keyboard' => [
									/* [
										[
											'text' => '⚙️ ' . $localization[''],
											'callback_data' => ''
										]
									] */
								],
								'disable_notification' => true,
								'remove_keyboard' => true
							],
						]
					)->then(function (message $message) use ($context) {
						// Sended the message

					});
				} else {
					// Not initialized localization

					// Sending the message
					$context->sendMessage('⚠️ *Failed to initialize localization*')
						->then(function (message $message) use ($context) {
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
					// Ending the conversation process
					$context->endConversation();
				});
		}
	}

	/**
	 * Account
	 *
	 * Responce for the command: "/account"
	 *
	 * Sends information about account with menu
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function account(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing localization 
			$localization = $context->get('localization');

			if ($localization) {
				// Initialized localization

				// Initializing title for the message
				$title = '🫵 ' . $localization['account_title'];

				// Declaring buufer of rows about authorizations
				$authorizations = '';

				// Initializing rows about authorization
				foreach ($account->values() as $key => $value) {
					// Iterating over account parameters

					if (str_starts_with($key, 'authorized_')) {
						// Iterating over account authorizations

						// Skipping system authorizations
						if (str_starts_with($key, 'authorized_system_')) continue;

						// Writing into buffer of rows about authorizations
						$authorizations .= ($value ? '✅' : '❎') . ' *' . ($localization["account_$key"] ?? $key) . ':* ' . ($value ? $localization['yes'] : $localization['no']) . "\n";
					}
				}

				// Trimming the last line break character
				$authorizations = trim($authorizations, "\n");

				// Sending the message
				$context->sendMessage(
					<<<TXT
					$title

					$authorizations
					TXT,
					[
						'reply_markup' => [
							'remove_keyboard' => true,
							'disable_notification' => true
						],
						'link_preview_options' => [
							'is_disabled' => true
						]
					]
				);
			} else {
				// Not initialized localization

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize localization*')
					->then(function (message $message) use ($context) {
						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize your Telegram account*')
				->then(function (message $message) use ($context) {
					// Ending the conversation process
					$context->endConversation();
				});
		}
	}

	/**
	 * Language
	 *
	 * Responce for the command: "/language"
	 * 
	 * Send the language selection menu
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function language(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing language 
			$language = $context->get('language');

			if ($language instanceof language) {
				// Initialized language

				// Initializing localization 
				$localization = $context->get('localization');

				if ($localization) {
					// Initialized localization

					// Sending the language selection
					process_language_select::menu(
						context: $context,
						prefix: 'settings_language_',
						title: '🌏 *' . $localization['settings_select_language_title'] . '*',
						description: '🌏 *' . $localization['settings_select_language_description'] . '*'
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

	/**
	 * Society
	 *
	 * Responce for the command: "/society"
	 *
	 * Sends the "mushroom" image and the localized text "why so shroomious"
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function society(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing localization 
			$localization = $context->get('localization');

			if ($localization) {
				// Initialized localization

				// Sending the message
				$context->sendPhoto(
					new file_input(STORAGE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'mushroom.jpg'),
					[
						'caption' => $localization['why_so_shroomious'],
						'disable_notification' => true
					]
				);
			} else {
				// Not initialized localization

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize localization*')
					->then(function (message $message) use ($context) {
						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize your Telegram account*')
				->then(function (message $message) use ($context) {
					// Ending the conversation process
					$context->endConversation();
				});
		}
	}
}

