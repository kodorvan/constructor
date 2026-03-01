<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\authorizations,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\worker,
	kodorvan\constructor\models\project,
	kodorvan\constructor\models\project\enumerations\type as project_type,
	kodorvan\constructor\models\project\enumerations\status as project_status;

// The library for languages support
use mirzaev\languages\language;

// The library for currencies support
use mirzaev\currencies\currency;

// Baza database
use mirzaev\baza\database,
	mirzaev\baza\column,
	mirzaev\baza\record,
	mirzaev\baza\enumerations\encoding,
	mirzaev\baza\enumerations\type;

// Active Record pattern
use mirzaev\record\interfaces\record as record_interface,
	mirzaev\record\traits\record as record_trait;

// Svoboda time
use svoboda\time\statement as svoboda;

// Framework for Telegram
use SergiX44\Nutgram\Telegram\Types\User\User as telegram_user;

// Built-in libraries
use Exception as exception,
	RuntimeException as exception_runtime;

/**
 * Account
 *
 * @package kodorvan\constructor\models
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class account extends core implements record_interface
{
	use record_trait;

	/**
	 * File
	 *
	 * @var string $file Path to the database file
	 */
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'accounts.baza';

	/**
	 * Database
	 *
	 * @var database $database The database
	 */
	public protected(set) database $database;

	/**
	 * Serialized
	 *
	 * @var bool $serialized Is the implementator object serialized?
	 */
	private bool $serialized = true;

	/**
	 * Constructor
	 *
	 * @method record|null $record The record
	 *
	 * @return void
	 */
	public function __construct(?record $record = null)
	{
		// Initializing the database
		$this->database = new database()
			->encoding(encoding::utf8)
			->columns(
				new column('identifier', type::long_long_unsigned),
				new column('identifier_telegram', type::long_long_unsigned),
				new column('domain', type::string, ['length' => 32]),
				new column('name_first', type::string, ['length' => 64]),
				new column('name_second', type::string, ['length' => 64]),
				new column('language', type::string, ['length' => 2]),
				new column('currency', type::string, ['length' => 3]),
				new column('robot', type::char),
				/* new column('', type::), */
				new column('active', type::char),
				new column('updated', type::integer_unsigned),
				new column('created', type::integer_unsigned)
			)
			->connect($this->file);

		// Initializing the record
		$record instanceof record and $this->record = $record;
	}

	/**
	 * Initialize
	 *
	 * @param telegram_user $telegram The telegram account
	 *
	 * @throws exception_runtime if update the account record in the database by the telegram account values
	 * @throws exception_runtime if failed to find the registered account
	 * @throws exception_runtime if failed to registrate the account
	 *
	 * @return static|null The account, if found, updated or created
	 */
	public function initialize(telegram_user $telegram): ?static
	{
		// Searching for the account in the database
		$account = $this->database->read(filter: fn(record $record) => $record->identifier_telegram === $telegram->id, amount: 1)[0] ?? null;

		if ($account instanceof record) {
			// Found the account record

			if (
				$account->domain !== (string) $telegram->username ||
				$account->name_first !== (string) $telegram->first_name ||
				$account->name_second !== (string) $telegram->last_name
			) {
				// The telegram account was updated

				// Updating the account in the database
				$updated = $this->database->read(
					filter: fn(record $record) => $record->identifier_telegram === $telegram->id,
					update: function (record &$record) use ($telegram) {
						// Writing new values into the record
						$record->domain = (string) $telegram->username;
						$record->name_first = (string) $telegram->first_name;
						$record->name_second = (string) $telegram->last_name;
						$record->updated = svoboda::timestamp();
					},
					amount: 1
				)[0] ?? null;

				if ($updated instanceof record && $updated->values() !== $account->values()) {
					// Updated the account in the database

					// Writing the updated record into the account object
					$this->record = $updated;

					// Deserializing parameters
					$this->deserialize();

					// Exit (success)
					return $this;
				} else {
					// Not updated the account in the database

					// Exit (fail)
					throw new exception_runtime('Failed to update the account record in the database by the telegram account values');
				}
			}

			// Writing the found record into the account object
			$this->record = $account;

			// Deserializing parameters
			$this->deserialize();

			// Exit (success)
			return $this;
		} else {
			// Not found the account record

			if ($this->registrate($telegram)) {
				// Registered the account

				// Searching for the registered account in the database
				$registered = $this->database->read(filter: fn(record $record) => $record->identifier_telegram === $telegram->id, amount: 1)[0] ?? null;

				if ($registered instanceof record) {
					// Found the registered account

					// Writing the registered record into the account object
					$this->record = $registered;

					// Deserializing parameters
					$this->deserialize();

					// Exit (success)
					return $this;
				} else {
					// Not found the registered account

					// Exit (fail)
					throw new exception_runtime('Failed to find the registered account');
				}
			} else {
				// Not registered the account

				// Exit (fail)
				throw new exception_runtime('Failed to registrate the account');
			}
		}
	}

	/**
	 * Registrate
	 *
	 * Create the account by the telegram account data
	 *
	 * @param telegram_user $telegram The telegram account
	 *
	 * @return record|false The record, if created
	 */
	public function registrate(telegram_user $telegram): record|false
	{
		// Creating the record
		$record = $this->write(
			telegram_identifier: (int) $telegram->id,
			name_first: (string) $telegram->first_name,
			name_second: (string) $telegram->last_name,
			domain: (string) $telegram->username,
			language: (string) $telegram->language_code,
			currency: CURRENCY_DEFAULT,
			robot: (bool) $telegram->is_bot
		);

		if ($record instanceof record) {
			// The record was writed into the database

			// Initializing the authorizations model
			$authorizations = new authorizations();

			// Creating the authorizations record
			$authorizations->write(account: $record->identifier);

			// Initializing the settings model
			$settings = new settings();

			// Creating the account settings
			$settings->write(account: $record->identifier);

			// Writing the record into the database
			/* $record = $this->database->read(
				filter: fn(record $_record) => $_record->identifier === $record->identifier,
				update: fn(record &$_record) => $_record = $record,
				amount: 1
			)[0] ?? null; */

			// Exit (success)
			return $record;
		}

		// Exit (fail)
		return false;
	}

	/**
	 * Write
	 *
	 * @param int $telegram_identifier The telegram account identifier
	 * @param string $name_first
	 * @param string $name_second
	 * @param string $domain
	 * @param language|string $language
	 * @param currency|string $currency
	 * @param bool $robot Is a robot?
	 * @param bool $active Is the record active?
	 *
	 * @return record|false The record, if created
	 */
	public function write(
		int $telegram_identifier,
		string $domain = '',
		string $name_first = '',
		string $name_second = '',
		language|string $language = LANGUAGE_DEFAULT ?? language::en,
		currency|string $currency = CURRENCY_DEFAULT ?? currency::usd,
		bool $robot = false,
		bool $active = true,
	): record|false {
		// Initializing the record
		$record = $this->database->record(
			$this->database->count() + 1,
			(int) $telegram_identifier,
			$domain,
			$name_first,
			$name_second,
			$language instanceof language ? $language->name : (string) $language,
			$currency instanceof currency ? $currency->name : (string) $currency,
			(int) $robot,
			/* */
			(int) $active,
			svoboda::timestamp(),
			svoboda::timestamp()
		);

		// Writing the record into the database
		$created = $this->database->write($record);

		// Exit (success)
		return $created ? $record : false;
	}

	/**
	 * Serialize
	 *
	 * @return self The instance from which the method was called (fluent interface)
	 */
	public function serialize(): self
	{
		if ($this->serialized) {
			// The record implementor is serialized

			// Exit (fail)
			throw new exception_runtime('The record implementor is already serialized');
		}

		// Serializing the record parameters
		$this->record->language = $this->record->language->name;
		$this->record->currency = $this->record->currency->name;
		$this->record->robot = (int) $this->record->robot;
		$this->record->active = (int) $this->record->active;

		// Writing the status of serializing
		$this->serialized = true;

		// Exit (success)
		return $this;
	}

	/**
	 * Deserialize
	 *
	 * @return self The instance from which the method was called (fluent interface)
	 */
	public function deserialize(): self
	{
		if (!$this->serialized) {
			// The record implementor is deserialized

			// Exit (fail)
			throw new exception_runtime('The record implementor is already deserialized');
		}

		// Deserializing the record parameters
		$this->record->language = language::{$this->record->language} ?? LANGUAGE_DEFAULT ?? language::en;
		$this->record->currency = currency::{$this->record->currency} ?? CURRENCY_DEFAULT ?? currency::usd;
		$this->record->robot = (bool) $this->record->robot;
		$this->record->active = (bool) $this->record->active;

		// Writing the status of serializing
		$this->serialized = false;

		// Exit (success)
		return $this;
	}

	/**
	 * Authorizations
	 *
	 * Search for the account authorizations
	 *
	 * @return authorizations|null The account authorizations
	 */
	public function authorizations(): ?authorizations
	{
		// Search for the account authorizations
		$authorizations = new authorizations()->read(filter: fn(record $record) => $record->active === 1 && $record->account === $this->identifier);

		if ($authorizations instanceof authorizations) {
			// Found the account authorizations

			// Exit (success)
			return $authorizations;
		}

		// Exit (fail)
		return null;
	}

	/**
	 * Worker
	 *
	 * Search for the account worker
	 *
	 * @return worker|null The account worker
	 */
	public function worker(): ?worker
	{
		// Search for the account worker
		$worker = new worker()->read(filter: fn(record $record) => $record->active === 1 && $record->account === $this->identifier);

		if ($worker instanceof worker) {
			// Found the account worker

			// Exit (success)
			return $worker;
		}

		// Exit (fail)
		return null;
	}

	/**
	 * Settings
	 *
	 * Search for the account settings
	 *
	 * @return settings|null The account settings
	 */
	public function settings(): ?settings
	{
		// Search for the account settings 
		$settings = new settings()->read(filter: fn(record $record) => $record->active === 1 && $record->account === $this->identifier);

		if ($settings instanceof settings) {
			// Found the account settings

			// Exit (success)
			return $settings;
		}

		// Exit (fail)
		return null;
	}

	/**
	 * Projects
	 *
	 * Search for the account projects
	 *
	 * @param project_type|null $type Type of the project
	 * @param project_status|null $status Status of the project
	 * @param int $amount Maximum amount
	 *
	 * @return array The account projects
	 */
	public function projects(
		?project_type $type = null,
		?project_status $status = null,
		int $amount = 20
	): array {
		// Search for the account projects 
		$projects = new project()->database->read(
			filter: fn(record $record) =>
			$record->active === 1
				&& $record->account === $this->identifier
				&& ($type === null || $record->type === $type->name)
				&& ($status === null || $record->status === $status->name),
			amount: $amount
		);

		// Exit (success/fail)
		return $projects;
	}

	/**
	 * Partners
	 *
	 * Search for accounts that have at least one project in development
	 *
	 * @param int $amount Maximum amount
	 *
	 * @return array Partners accounts without duplicates
	 */
	public static function partners(int $amount = PHP_INT_MAX): array
	{
		try {
			// Search for projects 
			$projects = new project()->database->read(
				filter: fn(record $record) =>
				$record->active === 1
					&& match (project_status::{$record->status}) {
						project_status::developing, project_status::developed, project_status::launched => true,
						default => false
					},
				amount: $amount
			);

			// Declaring the registry of partners accounts
			$partners = [];

			foreach ($projects as $project) {
				// Iterating over projects

				if (isset($partners[$project->account])) {
					// The partner account is already was in the partners accounts registry

					// Skipping the iteration
					continue;
				}

				// Searching the partner account and writing into the partners accounts registry
				$partners[$project->account] = new account()->read(filter: fn(record $record) => $record->identifier === $project->account);
			}

			// Exit (success)
			return $partners;
		} catch (exception $exception) {
			// Writing the exception into the errors output buffer
			error_log((string) $exception);
		}

		// Exit (fail)
		return [];
	}
}
