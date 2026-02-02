<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\middlewares;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\authorizations as model;

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
 * Telegram middleware: authorizations
 *
 * @package kodorvan\constructor\models\telegram\middlewares
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class authorizations extends core
{
	/**
	 * Authorizations
	 *
	 * Initialize the account authorizations and write them into the `authorizations` variable inside the `$robot`
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
			$authorizations = $account->authorizations();

			if ($authorizations instanceof model) {
				// Initialized the account authorizations

				// Writing the account authorizations into the robot variable
				$robot->set('authorizations', $authorizations);

				// Continuation of the process
				$next($robot);
			} else {
				// Not initialized the account authorizations

				// Sending the message
				$robot->sendMessage(
					text: '⚠️ *Failed to initialize your Telegram account authorizations*',
					parse_mode: mode::MARKDOWN
				);

				// Ending the conversation process
				$robot->endConversation();
			}
		}
	}
}
