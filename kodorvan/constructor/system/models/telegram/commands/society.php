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
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\Telegram\Properties\ParseMode as mode,
	SergiX44\Nutgram\Telegram\Types\Message\Message as message,
	SergiX44\Nutgram\Handlers\Type\Command as command,
	SergiX44\Nutgram\Telegram\Types\Internal\InputFile as input;

/**
 * Command: society
 *
 * @package kodorvan\constructor\models\telegram\commands
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class society extends command
{
	/**
	 * Command
	 *
	 * @var string $name Name of the command
	 */
	protected string $command = 'society';

	/**
	 * Description
	 *
	 * @var string $description
	 */
	protected ?string $description = 'thing about it';

	/**
	 * Localizations
	 *
	 * Descriptions of the command
	 *
	 * @var array $localizedDescriptions
	 */
	protected array $localizedDescriptions = [
		'*' => 'thing about it'
	];

	/**
	 * Handle
	 *
	 * Processing the command
	 *
	 * @param telegram $robot The chat-robot instance
	 *
	 * @return void
	 */
	public function handle(telegram $robot): void
	{
		$robot->sendPhoto(
			photo: input::make(STORAGE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'mushroom.jpg'),
			caption: $robot->get('localization')['why_so_shroomious'] ?? 'why so shroomious',
			disable_notification: true,
			parse_mode: mode::MARKDOWN
		);
	}
}
