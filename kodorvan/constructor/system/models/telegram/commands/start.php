<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\commands;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\localization,
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
	SergiX44\Nutgram\Telegram\Types\Internal\InputFile as input,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as keyboard,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as button;

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
	 * Command
	 *
	 * @var string $name Name of the command
	 */
	protected string $command = 'start';

	/**
	 * Description
	 *
	 * @var string $description
	 */
	protected ?string $description = 'Main menu';

	/**
	 * Localizations
	 *
	 * Descriptions of the command
	 *
	 * @var array $localizedDescriptions
	 */
	protected array $localizedDescriptions = [
		'ru' => 'Главное меню',
		'*' => 'Main menu'
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
		// Initializing the language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the menu message localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the account
		$account = $robot->get('account');

		// Initializing the message last update text
		exec(command: 'git log --oneline $(git describe --tags --abbrev=0 @^ --always)..@ -1 --format="%at" | xargs -I{} date -d @{} "+%Y.%m.%d %H:%M"', output: $git);
		$update = empty($git[0]) ? '' : "🔏 *$localization->menu_update:* " . unmarkdown($git[0]);

		// Calculating amount of projects
		$projects = count($account->projects());

		// Calculating amount of partners
		$partners = count(account::partners());

		// Initializing the keyboard
		$keyboard = keyboard::make();

		// Writing the row into the keyboard
		$keyboard->addRow(
			button::make(
				text: "📂 $localization->menu_button_project_new",
				callback_data: 'project_create'
			),
			button::make(
				text: "🗂 $localization->menu_button_projects: $projects",
				callback_data: 'projects'
			),
		);

		// Writing the row into the keyboard
		$keyboard->addRow(
			button::make(
				text: "📡 $localization->menu_button_operator",
				callback_data: 'operator'
			)
		);

		$robot->sendMessage(
			text: implode(
				"\n\n",
				[
					"📋 *$localization->menu_title*",
					$projects > 0 ? printf($localization->menu_description_partner, $partners) : $localization->menu_description_guest,
					$update
				]
			),
			parse_mode: mode::MARKDOWN,
			disable_notification: true,
			reply_markup: $keyboard
		);
	}
}
