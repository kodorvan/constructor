<?php

declare(strict_types=1);

namespace kodorvan\constructor;

// Files of the project
use kodorvan\constructor\models\account,
	kodorvan\constructor\models\localization,
	kodorvan\constructor\models\telegram\settings as telegram_settings,
	kodorvan\constructor\models\telegram\project as telegram_project,
	kodorvan\constructor\models\telegram\commands\start as command_start,
	kodorvan\constructor\models\telegram\commands\account as command_account,
	kodorvan\constructor\models\telegram\commands\society as command_society,
	kodorvan\constructor\models\telegram\commands\language as command_language,
	/* kodorvan\constructor\models\telegram\conversations\project\create as conversation_project_create, */
	kodorvan\constructor\models\telegram\middlewares\account as middleware_account,
	kodorvan\constructor\models\telegram\middlewares\language as middleware_language,
	kodorvan\constructor\models\telegram\middlewares\localization as middleware_localization,
	kodorvan\constructor\models\telegram\middlewares\settings as middleware_settings,
	kodorvan\constructor\models\telegram\middlewares\authorizations as middleware_authorizations;

// Library for languages support
use mirzaev\languages\language;

// Framework for PHP
use mirzaev\minimal\core,
	mirzaev\minimal\route;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Framework for Telegram
use SergiX44\Nutgram\Nutgram as framework_telegram,
	SergiX44\Nutgram\Configuration as framework_telegram_settings,
	SergiX44\Nutgram\RunningMode\Webhook as framework_telegram_webhook,
	SergiX44\Nutgram\Telegram\Properties\ParseMode as mode,
	SergiX44\Nutgram\Telegram\Exceptions\TelegramException as framework_telegram_exception,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as keyboard,
	SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as button;

// The symphony cache library
use Symfony\Component\Cache\Adapter\FilesystemAdapter as cache_adapter,
	Symfony\Component\Cache\Psr16Cache as cache;

// Built-in libraries
use Exception as exception;

// Enabling debugging
/* ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

// Initializing path to the public directory 
define('INDEX', __DIR__ . DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR	. '..');

// Initializing path to the project root directory
define('ROOT',  INDEX . DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

// Initializing path to the directory of views 
define('VIEWS', INDEX . DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR . 'views');

// Initializing path to the directory of settings 
define('SETTINGS', INDEX . DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR . 'settings');

// Initializing system settings 
require SETTINGS . DIRECTORY_SEPARATOR . 'system.php';

// Initializing path to the directory of the storage 
define('STORAGE', INDEX . DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR . 'storage');

// Initializing path to the databases directory
define('DATABASES', INDEX . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'databases');

// Initializing path to the localizations directory
define('LOCALIZATIONS', INDEX . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'localizations');

// Initiailizing Telegram chat-robot settings
define('TELEGRAM', require(SETTINGS . DIRECTORY_SEPARATOR . 'telegram.php'));

// Initializing dependencies
require ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Initializing the robot
$robot = new framework_telegram(
	token: TELEGRAM['key'],
	config: new framework_telegram_settings(
		botName: TELEGRAM['name'],
		cache: new cache(new cache_adapter())
	)
);

$webhook = new framework_telegram_webhook(secretToken: TELEGRAM['password']);
$webhook->setSafeMode(true);

$robot->setRunningMode($webhook);
$robot->throttle(10);

$robot->middleware(middleware_account::class);
$robot->middleware(middleware_language::class);
$robot->middleware(middleware_localization::class);
$robot->middleware(middleware_authorizations::class);

// Start
$robot->registerCommand(command_start::class);
$robot->onCommand('start telegram voronka', command_start::class);
$robot->onCommand('start parser', command_start::class);
$robot->onCommand('start calculator', command_start::class);

// Account
$robot->registerCommand(command_account::class);

// Language
$robot->registerCommand(command_language::class)->middleware(middleware_settings::class);
foreach (language::cases() as $language) {
	// Iterating over languages

	// Select the language
	$robot->onCallbackQueryData('settings_language_$language->name', fn(framework_telegram $robot) => telegram_settings::language(robot: $robot, language: $language));
};

// Society
$robot->registerCommand(command_society::class);

// Project: create
$robot->onCallbackQueryData('project_create', [telegram_project::class, 'create']);

// Project: request
$robot
	->onCallbackQueryData('project_deal_accept', [telegram_project::class, 'accept']);

$robot->onApiError(function (framework_telegram $robot, framework_telegram_exception $exception) {
	try {
		// Writing into the errors output
		error_log($exception->getMessage());

		// Initializing the account language
		$language = $robot->get('language') ?? LANGUAGE_DEFAULT;

		// Initializing the account localization
		$localization = $robot->get('localization') ?? new localization($language);

		// Initializing the keyboard
		$keyboard = keyboard::make();

		// Writing the row into the keyboard
		$keyboard->addRow(
			button::make(
				text: "✉️ $localization->error_button_chat_operator",
				url: PROJECT_OPERATOR_URL ?? PROJECT_MEDIA_URL ?? 'https://t.me/kodorvan'
			)
		);

		// Title
		$title = "🔥 *$localization->error_title*";

		// Description
		$description = $localization->error_description;

		// Repeat
		$repeat = $localization->error_repeat;

		// Sending the message
		$robot->sendMessage(
			text: implode(
				"\n\n",
				[
					$title,
					$description,
					$repeat
				]
			),
			disable_notification: true,
			parse_mode: mode::MARKDOWN,
			reply_markup: $keyboard
		);

		// Ending the conversation
		$robot->endConversation();

		foreach (ERRORS_RECEIVERS as $receiver) {
			// Iterating over errors receivers

			// Initializing the keyboard
			$keyboard = keyboard::make();

			// Writing the row into the keyboard
			$keyboard->addRow(
				button::make(
					text: "✉️ $localization->error_button_chat_user",
					url: 'https://t.me/' . $robot->user()->username
				)
			);

			// Domain
			/* $domain = "*$localization->error_account:* @" . $robot->user()->username; */

			// Sending the message
			$robot->sendMessage(
				text: implode(
					"\n\n",
					[
						$exception->getMessage(),
						/* $domain */
					]
				),
				chat_id: $receiver,
				/* parse_mode: mode::MARKDOWN, */
				reply_markup: $keyboard
			);
		}
	} catch (exception $exception) {
		// Writing into the errors output
		error_log($exception->getMessage());
	}
});

try {
	$robot->run();
} catch (exception $exception) {
	// Writing into the errors output
	error_log($exception->getMessage());
}
