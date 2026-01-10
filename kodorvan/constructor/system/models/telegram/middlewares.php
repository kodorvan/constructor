<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\authorizations;

// The library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Framework for Telegram
use Zanzara\Context as context,
	Zanzara\Telegram\Type\Message as message,
	Zanzara\Middleware\MiddlewareNode as node;

// Built-in libraries
use Error as error;

/**
 * Telegram middlewares
 *
 * @package kodorvan\constructor\models\telegram
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class middlewares extends core
{
	/**
	 * Account (middleware)
	 *
	 * Initialize or registrate the account and write it into the `account` variable inside the `$context`
	 *
	 * @param context $context
	 * @param node $next
	 *
	 * @return void
	 */
	public static function account(context $context, node $next): void
	{
		// Is the process stopped?
		if ($context->get('stop')) return;

		// Initializing the telegram account
		$telegram = $context->getEffectiveUser();

		// Initializing the account
		$account = new account()->initialize($telegram);

		if ($account instanceof account) {
			// Initialized the account

			// Writing the account into the context variable
			$context->set('account', $account);

			// Continuation of the process
			$next($context);
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
	 * Authorizations (middleware)
	 *
	 * Initialize the account authorizations and write them into the `authorizations` variable inside the `$context`
	 *
	 * @param context $context
	 * @param node $next
	 *
	 * @return void
	 */
	public static function authorizations(context $context, node $next): void
	{
		// Is the process stopped?
		if ($context->get('stop')) return;

		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing the account authorizations
			$authorizations = $account->authorizations();

			if ($authorizations instanceof authorizations) {
				// Initialized the account authorizations

				// Writing the account authorizations into the context variable
				$context->set('authorizations', $authorizations);

				// Continuation of the process
				$next($context);
			} else {
				// Not initialized the account

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize your Telegram account authorizations*')
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
	 * Language (middleware)
	 *
	 * Implement the account language
	 *
	 * @param context $context
	 * @param node $next
	 *
	 * @return void
	 */
	public static function language(context $context, node $next): void
	{
		// Is the process stopped?
		if ($context->get('stop')) return;

		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			if ($account->language instanceof language) {
				// Initialized the language parameter

				try {
					// Writing the account language into the context variable
					$context->set('language', $account->language);
				} catch (error $error) {
					// Not initialized the language

					// Writing the default language into the context variable
					$context->set('language', LANGUAGE_DEFAULT ?? language::en);
				}
			} else {
				// Not initialized the language parameter

				// Writing the default language into the context variable
				$context->set('language', LANGUAGE_DEFAULT ?? language::en);
			}

			// Continuation of the process
			$next($context);
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
	 * Localization (middleware)
	 *
	 * Implement the account language and initialize the localization file
	 *
	 * @param context $context
	 * @param node $next
	 *
	 * @return void
	 */
	public static function localization(context $context, node $next): void
	{
		// Is the process stopped?
		if ($context->get('stop')) return;

		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing the language
			$language = $context->get('language');

			if ($language instanceof language) {
				// Initialized the language

				// Initializing path to the localization file
				$file = LOCALIZATIONS . DIRECTORY_SEPARATOR . strtolower($language->label()) . '.php';

				if (file_exists($file) && is_readable($file)) {
					// Found the localization file

					// Initializing localization
					$localization = require($file);

					if (is_array($localization)) {
						// Initialized the localization

						// Writing localization into the context variable
						$context->set('localization', $localization);

						// Continuation of the process
						$next($context);
					} else {
						// Not initialized the localization

						// Sending the message
						$context->sendMessage('⚠️ *Failed to initialize localization*')
							->then(function (message $message) use ($context) {
								// Sended the message

								// Ending the conversation process
								$context->endConversation();
							});
					}
				} else {
					// Not found the localization file

					// Sending the message
					$context->sendMessage('⚠️ *Failed to initialize the localization file*')
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
	 * Settings (middleware)
	 *
	 * Check the account for access to the settings
	 *
	 * @param context $context
	 * @param node $next
	 *
	 * @return void
	 */
	public static function settings(context $context, node $next): void
	{
		// Is the process stopped?
		if ($context->get('stop')) return;

		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing the account authorizations
			$authorizations = $context->get('authorizations');

			if ($authorizations instanceof authorizations) {
				// Initialized the account authorizations

				if ($authorizations->settings) {
					// Authorized the account to the settings

					// Continuation of the process
					$next($context);
				} else {
					// Not authorized the account to the settings

					// Initializing localization
					$localization = $context->get('localization');

					if ($localization) {
						// Initialized localization

						// Sending the message
						$context->sendMessage('⛔ *' . $localization['not_authorized_settings'] . '*')
							->then(function (message $message) use ($context) {
								// Sended the message

								// Ending the conversation process
								$context->endConversation();
							});

						// Stopping the process
						$context->set('stop', true);
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
				}
			} else {
				// Not initialized the account authorizations

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize your Telegram account authorizations*')
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
	 * System settings (middleware)
	 *
	 * Check the account for access to the system settings
	 *
	 * @param context $context
	 * @param node $next
	 *
	 * @return void
	 */
	public static function system_settings(context $context, node $next): void
	{
		// Is the process stopped?
		if ($context->get('stop')) return;

		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing the account authorizations
			$authorizations = $context->get('authorizations');

			if ($authorizations instanceof authorizations) {
				// Initialized the account authorizations

				if ($authorizations->system_settings) {
					// Authorized the account to the system settings

					// Continuation of the process
					$next($context);
				} else {
					// Not authorized the account to the system settings

					// Initializing localization
					$localization = $context->get('localization');

					if ($localization) {
						// Initialized localization

						// Sending the message
						$context->sendMessage('⛔ *' . $localization['not_authorized_system_settings'] . '*')
							->then(function (message $message) use ($context) {
								// Sended the message

								// Ending the conversation process
								$context->endConversation();
							});

						// Stopping the process
						$context->set('stop', true);
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
				}
			} else {
				// Not initialized the account

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize your Telegram account authorizations*')
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

