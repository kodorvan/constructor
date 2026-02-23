<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\middlewares;

// Files of the project
use kodorvan\constructor\models\account,
	kodorvan\constructor\models\authorizations;

// The library for languages support
use mirzaev\languages\language as type;

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
final class language
{
	/**
	 * Language
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

			if ($account->language instanceof type) {
				// Initialized the language parameter

				try {
					// Writing the account language into the robot variable
					$robot->set('language', $account->language);
				} catch (error $error) {
					// Not initialized the language

					// Writing the default language into the robot variable
					$robot->set('language', LANGUAGE_DEFAULT ?? type::en);
				}
			} else {
				// Not initialized the language parameter

				// Writing the default language into the robot variable
				$robot->set('language', LANGUAGE_DEFAULT ?? type::en);
			}

			// Continuation of the process
			$next($robot);
		}
	}
}
