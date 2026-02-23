<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\middlewares;

// Files of the project
use kodorvan\constructor\models\account as model,
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
 * Telegram middleware: account
 *
 * @package kodorvan\constructor\models\telegram\middlewares
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class account
{
	/**
	 * Account
	 *
	 * Initialize or registrate the account and write it into the `account` variable inside the `$robot`
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

		// Initializing the telegram account
		$telegram = $robot->user();

		// Initializing the account
		$account = new model()->initialize(telegram: $telegram);

		if ($account instanceof model) {
			// Initialized the account

			// Writing the account into the robot variable
			$robot->set('account', $account);

			// Continuation of the process
			$next($robot);
		} else {
			// Not initialized the account

			// Sending the message
			$robot->sendMessage(
				text: '⚠️ *Failed to initialize your Telegram account*',
				parse_mode: mode::MARKDOWN
			);

			// Ending the conversation process
			$robot->endConversation();
		}
	}
}
