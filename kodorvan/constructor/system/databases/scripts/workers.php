<?php

declare(strict_types=1);

namespace kodorvan\constructor;

// Files of the project
use kodorvan\constructor\models\account,
	kodorvan\constructor\models\authorizations,
	kodorvan\constructor\models\worker,
	kodorvan\constructor\models\tariff;

// The library for currencies support
use mirzaev\currencies\currency;

// Svoboda time
use svoboda\time\statement as svoboda;

// Baza database
use mirzaev\baza\record;

// Enabling debugging
/* ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

// Initializing path to the public directory 
define('INDEX', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public');

// Initializing path to the root directory
define('ROOT',  INDEX . DIRECTORY_SEPARATOR	. '..' .  DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR	. '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

// Initializing path to the settings directory
define('SETTINGS', INDEX . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings');

// Initializing path to the storage directory
define('STORAGE', INDEX . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage');

// Initializing path to the databases directory
define('DATABASES', INDEX . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'databases');

// Initializing path to the localizations directory
define('LOCALIZATIONS', INDEX . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'localizations');

// Initiailizing telegram data
require(SETTINGS . DIRECTORY_SEPARATOR . 'telegram.php');

// Initializing dependencies
require ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Initializing the account model
$account_model = new account();

// Searching for the account
$account = $account_model->read(
	filter: fn(record $record) => $record->domain === 'buddy_volkodav',
);

var_dump($account);

// Searching for the account authorizations
$authorizations = $account->authorizations();

var_dump($authorizations);

// Creating the worker
$worker = new worker()->write(
	account: $account->identifier,
	hour: 2000,
	currency: currency::rub,
	active: true
);

var_dump($worker);

