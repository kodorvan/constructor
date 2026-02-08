<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\middlewares;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\localization as model,
	kodorvan\constructor\models\authorizations;

// The library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Framework for Telegram
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\Telegram\Properties\ParseMode as mode;

// Built-in libraries
use Exception as exception,
	Error as error;

/**
 * Telegram middleware: localization
 *
 * @package kodorvan\constructor\models\telegram\middlewares
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class localization extends core
{
	/**
	 * Localization
	 *
	 * Implement the account language and initialize the localization file
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

			// Initializing the language
			$language = $robot->get('language');

			if ($language instanceof language) {
				// Initialized the language

				// Initializing the localization
				$localization = new model($language);

				// Writing localization into the robot variable
				$robot->set('localization', $localization);

				// Continuation of the process
				$next($robot);
			}
		}
	}
}
