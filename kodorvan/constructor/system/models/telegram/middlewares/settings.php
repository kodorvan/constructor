<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\middlewares;

// Files of the project
use kodorvan\constructor\models\account,
	kodorvan\constructor\models\authorizations;

// The library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Framework for Telegram
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\Telegram\Properties\ParseMode as mode;

// Built-in libraries
use Error as error;

/**
 * Telegram middleware: language
 *
 * @package kodorvan\constructor\models\telegram\middlewares
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class settings
{
	/**
	 * Settings
	 *
	 * Implement the account language
	 *
	 * @param telegram $robot
	 * @param $next
	 *
	 * @return void
	 */
	public function __invoke(telegram $robot, $next): void
	{
		// Is the process stopped?
		if ($robot->get('stop')) return;

		// Initializing the account
		$account = $robot->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing the account authorizations
			$authorizations = $robot->get('authorizations');

			if ($authorizations instanceof authorizations) {
				// Initialized the account authorizations

				if ($authorizations->settings) {
					// Authorized the account to the settings

					// Continuation of the process
					$next($robot);
				} else {
					// Not authorized the account to the settings

					// Initializing localization
					$localization = $robot->get('localization');

					if ($localization) {
						// Initialized localization

						// Sending the message
						$robot->sendMessage(
							text: "⛔ *$localization->not_authorized_settings*",
							parse_mode: mode::MARKDOWN
						);

						// Ending the conversation process
						$robot->endConversation();

						// Stopping the process
						$robot->set('stop', true);
					}
				}
			}
		}
	}
}
