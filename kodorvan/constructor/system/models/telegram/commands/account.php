<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\commands;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account as model,
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
 * Command: account
 *
 * @package kodorvan\constructor\models\telegram\commands
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class account extends command
{
	/**
	 * Command
	 *
	 * @var string $name Name of the command
	 */
	protected string $command = 'account';

	/**
	 * Description
	 *
	 * @var string $description
	 */
	protected ?string $description = 'Account profile';

	/**
	 * Localizations
	 *
	 * Descriptions of the command
	 *
	 * @var array $localizedDescriptions
	 */
	protected array $localizedDescriptions = [
		'ru' => 'Профиль аккаунта',
		'*' => 'Account profile'
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

		// Declaring buufer of rows about authorizations
		$authorizations = '';

		// Initializing rows about authorization
		foreach ($account->authorizations()?->record->values() as $key => $value) {
			// Iterating over account parameters

			if (match ($key) {
				'identifier', 'account', 'active', 'updated', 'created' => false,
				default => true
			} && !str_starts_with($key, 'system_')) {
				// The value is not metadata and system authorozations

				// Writing into buffer of rows about authorizations
				$authorizations .= ($value ? '✅' : '❎') . ' *' . ($localization["authorization_$key"] ?? $key) . ':* ' . ($value ? $localization->yes : $localization->no) . "\n";
			}
		}

		// Trimming the last line break character
		$authorizations = trim($authorizations, "\n");

		$robot->sendMessage(
			text: implode(
				"\n\n",
				[
					"🫵 *$localization->account_title*",
					$authorizations
				]
			),
			parse_mode: mode::MARKDOWN,
			disable_notification: true
		);
	}
}
