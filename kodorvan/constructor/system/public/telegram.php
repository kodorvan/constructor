<?php

declare(strict_types=1);

namespace kodorvan\constructor;

// Files of the project
use kodorvan\constructor\models\account,
	kodorvan\constructor\models\telegram\middlewares,
	kodorvan\constructor\models\telegram\commands,
	kodorvan\constructor\models\telegram\settings;

// Library for languages support
use mirzaev\languages\language;

// Framework for PHP
use mirzaev\minimal\core,
	mirzaev\minimal\route;

// Framework for Telegram
use Zanzara\Zanzara as zanzara,
	Zanzara\Context as context,
	Zanzara\Config as config;

// Enabling debugging
/* ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

// Initializing path to the public directory 
define('INDEX', __DIR__);

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
require(SETTINGS . DIRECTORY_SEPARATOR . 'telegram.php');

// Initializing dependencies
require ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Initializing the configuration
$config = new config();
$config->setParseMode(config::PARSE_MODE_MARKDOWN);
$config->useReactFileSystem(true);

// Initializing the robot
$robot = new Zanzara(ROBOT['key'], $config);

// Initializing the updates listener
$robot->onUpdate(function (context $context): void {});

// Initializing the robot middlewares
$robot->middleware([middlewares::class, 'account']);
$robot->middleware([middlewares::class, 'language']);
$robot->middleware([middlewares::class, 'localization']);
$robot->middleware([middlewares::class, 'authorizations']);

// Initializing the robot commands handlers
$robot->onCommand('start', [commands::class, 'start']);

$robot->onCommand('start telegram voronka', [commands::class, 'start']);
$robot->onCommand('start parser', [commands::class, 'start']);
$robot->onCommand('start calculator', [commands::class, 'start']);

$robot->onCommand('language', [commands::class, 'language'])->middleware([middlewares::class, 'settings']);
$robot->onCommand('society', [commands::class, 'society']);

// Initializing the robot settings language buttons handlers
foreach (language::cases() as $language) {
	// Iterating over languages

	// Initializing language buttons
	$robot->onCbQueryData(["settings_language_$language->name"], fn(context $context) => settings::language($context, $language));
};

$robot->onCbQueryData('project_create', ['process_project_create', 'name']);

// Starting chat-robot
$robot->run();
