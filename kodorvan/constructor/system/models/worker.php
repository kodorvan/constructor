<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\authorizations,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\project,
	kodorvan\constructor\models\account,
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
 * Worker
 *
 * @package kodorvan\constructor\models
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class worker extends core implements record_interface
{
	use record_trait;

	/**
	 * File
	 *
	 * @var string $file Path to the database file
	 */
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'workers.baza';

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
				new column('account', type::long_long_unsigned),
				new column('hour', type::integer_unsigned),
				new column('currency', type::string, ['length' => 3]),
				new column('active', type::char),
				new column('updated', type::integer_unsigned),
				new column('created', type::integer_unsigned)
			)
			->connect($this->file);

		// Initializing the record
		$record instanceof record and $this->record = $record;
	}

	/**
	 * Write
	 *
	 * @param int $account The account identifier
	 * @param int|float $hour Cost per hour
	 * @param currency|string $currency Currency of cost per hour
	 * @param bool $active Is the record active?
	 *
	 * @return record|false The record, if created
	 */
	public function write(
		int $account,
		int|float $hour,
		currency|string $currency = CURRENCY_DEFAULT ?? currency::usd,
		bool $active = true,
	): record|false {
		// Initializing the record
		$record = $this->database->record(
			$this->database->count() + 1,
			(int) $account,
			$hour,
			$currency instanceof currency ? $currency->name : (string) $currency,
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
	 * Account
	 *
	 * Search for the worker account
	 *
	 * @return account|null The account worker
	 */
	public function account(): ?account
	{
		// Search for the worker account
		$account = new account()->read(filter: fn(record $record) => $record->active === 1 && $record->identifier === $this->account);

		if ($account instanceof account) {
			// Found the worker account

			// Exit (success)
			return $account;
		}

		// Exit (fail)
		return null;
	}

	/**
	 * Workers
	 *
	 * Search for workers
	 *
	 * @param int $amount Amount
	 *
	 * @return array Workers
	 */
	public static function workers(int $amount = 100): array
	{
		// Search for workers and exit (success/fail)
		return new static()->database->read(
			filter: fn(record $record) => $record->active === 1,
			amount: $amount
		);
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
		$this->record->currency = $this->record->currency->name;
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
		$this->record->currency = currency::{$this->record->currency} ?? CURRENCY_DEFAULT ?? currency::usd;
		$this->record->active = (bool) $this->record->active;

		// Writing the status of serializing
		$this->serialized = false;

		// Exit (success)
		return $this;
	}
}
