<?php

declare(strict_types=1);

namespace kodorvan\constructor;

// Files of the project
use kodorvan\constructor\models\account,
	kodorvan\constructor\models\telegram\settings,
	kodorvan\constructor\models\telegram\commands\start as command_start,
	kodorvan\constructor\models\telegram\commands\society as command_society,
	kodorvan\constructor\models\telegram\commands\language as command_language,
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

// Framework for Telegram
/* use Telegram\Bot\BotsManager as telegram; */
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\RunningMode\Webhook as webhook;

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
$robot = new telegram(TELEGRAM['constructor']['key']);

$webhook = new webhook(secretToken: 'bebra228');
$webhook->setSafeMode(true);

$robot->setRunningMode($webhook);

$robot->middleware(middleware_account::class);
$robot->middleware(middleware_language::class);
$robot->middleware(middleware_localization::class);
$robot->middleware(middleware_authorizations::class);


// Initializing the robot commands handlers
$robot->registerCommand(command_start::class);

$robot->onCommand('start telegram voronka', command_start::class);
$robot->onCommand('start parser', command_start::class);
$robot->onCommand('start calculator', command_start::class);

$robot->registerCommand(command_language::class)->middleware(middleware_settings::class);
$robot->registerCommand(command_society::class);

// Initializing the robot settings language buttons handlers
foreach (language::cases() as $language) {
	// Iterating over languages

	// Initializing language buttons
	$robot->onCallbackQueryData("settings_language_$language->name", fn(telegram $robot) => settings::language(robot: $robot, language: $language));
};

/* $robot->onCbQueryData('project_create', ['process_project_create', 'name']); */

$robot->run();
