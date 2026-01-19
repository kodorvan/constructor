<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\commands;

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
use Telegram\Bot\Commands\Command as command;

/**
 * Command: start
 *
 * @package kodorvan\constructor\models\telegram\commands
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class start extends command
{
	/**
	 * Start
	 *
	 * @var string $name Name of the command
	 */
	protected string $name = 'start';

	protected string $description = 'Start Command to get you started';

	public function handle()
	{
		$this->replyWithMessage([
			'text' => 'Hey, there! Welcome to our bot!',
		]);
	}
}
