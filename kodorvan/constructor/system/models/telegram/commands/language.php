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
use mirzaev\languages\language as type;

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
 * Command: language
 *
 * @package kodorvan\constructor\models\telegram\commands
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class language extends command
{
	/**
	 * Command
	 *
	 * @var string $name Name of the command
	 */
	protected string $command = 'language';

	/**
	 * Description
	 *
	 * @var string $description
	 */
	protected ?string $description = 'System language';

	/**
	 * Localizations
	 *
	 * Descriptions of the command
	 *
	 * @var array $localizedDescriptions
	 */
	protected array $localizedDescriptions = [
		'ru' => 'Язык системы',
		'*' => 'System language'
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

		// Initializing the localization
		$localization = $robot->get('localization') ?? new localization($language);

		$this::menu(
			robot: $robot,
			prefix: 'settings_language_',
			title: "🌏 *$localization->settings_language_title*",
			description: $localization->settings_language_description,
			language: $language
		);
	}

	/**
	 * Menu
	 *
	 * Generate and send the language selection menu
	 *
	 * @param telegram $robot The chat-robot instance
	 * @param string|null $prefix The process prefix
	 * @param string|null $title The menu message title
	 * @param string|null $description The menu message description (main content)
	 * @param array $exclude Languages that will be excluded ['ru', 'en'...]
	 * @param type $language The menu message language
	 *
	 * @return void
	 */
	public static function menu(telegram $robot, ?string $prefix = null, ?string $title = null, ?string $description = null, array $exclude = [], type $language = LANGUAGE_DEFAULT): void
	{
		// Initializing the menu message localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the keyboard
		$keyboard = keyboard::make();

		// Initializing the row
		$row = [];

		// Initializing the maximum amount of buttons in a row
		$length = 4;

		// Initializing buffer of languages
		$languages = type::cases();

		// Deleting the actual language from buffer of languages
		unset($languages[array_search($language, $languages, strict: true)]);

		// Sorting buffer of languages by the actual language
		$languages = [$language, ...$languages];

		foreach ($languages as $language) {
			// Iterating over languages

			// Skipping excluded languages
			if (array_search($language->name, $exclude, strict: true) !== false) continue;

			// Writing the language choose button into the buffer of generated keyboard with languages
			$row[] = button::make(
				text: ($language->flag() ? $language->flag() . ' ' : '') . $language->label($language),
				callback_data: $prefix . $language->name
			);

			if (count($row) >= $length) {
				// Reached the limit of buttons in a row

				// Writing the row into the keyboard
				$keyboard->addRow(...$row);

				// Reinitializing the row
				$row = [];
			}
		}

		if (count($row) / $length < 1) {
			// The row was not writed 

			// Writing the row into the keyboard
			$keyboard->addRow(...$row);
		}

		// Writing the row into the keyboard
		$keyboard->addRow(button::make(
			text: '🗂 ' . $localization->settings_language_button_add,
			url: PROJECT_REPOSITORY_LANGUAGE_ADD
		));

		// Sending the message
		$robot->sendMessage(
			text: ($title ?? "🌏 *$localization->settings_language_title*") . "\n\n" . ($description ?? $localization->settings_language_description),
			parse_mode: mode::MARKDOWN,
			disable_notification: true,
			reply_markup: $keyboard,
		);
	}
}
