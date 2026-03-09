<?php

declare(strict_types=1);

namespace kodorvan\constructor;

// Files of the project
use kodorvan\constructor\models\account,
	kodorvan\constructor\models\authorizations,
	kodorvan\constructor\models\chat,
	kodorvan\constructor\models\tariff;

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
$account = $account_model->database->read(
	filter: fn(record $record) => $record->domain === 'buddy_volkodav',
	amount: 1,
	offset: 0
)[0] ?? null;

var_dump($account);

// Initializing the account authorizations model
$authorizations_model = new authorizations();

// Searching for the account authorizations
$authorizations = $authorizations_model->database->read(
	filter: fn(record $record) => $record->account === $account->identifier,
	update: function (record $record) {
		$record->system_settings = 1;
		$record->system_deals = 1;
		$record->system_projects = 1;
		$record->system_invoices = 1;
		$record->updated = svoboda::timestamp();
	},
	amount: 1,
	offset: 0
)[0] ?? null;

var_dump($authorizations);
