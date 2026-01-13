<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Files of the project
use kodorvan\constructor\models\core;

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

// Built-in libraries
use Exception as exception,
	RuntimeException as exception_runtime;

/**
 * Settings
 *
 * @package kodorvan\constructor\models
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class settings extends core implements record_interface
{
	use record_trait;

	/**
	 * File
	 *
	 * @var string $database Path to the database file
	 */
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'settings.baza';

	/**
	 * Database
	 *
	 * @var database $database The database
	 */
	public protected(set) database $database;

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
	 * Write
	 *
	 * @param int $account The account identifier
	 * @param int $active Is the record active?
	 *
	 * @return int|false The record, if created
	 */
	public function write(
		int $account,
		bool $active = true,
	): record|false {
		$record = $this->database->record(
			$this->database->count() + 1,
			$account,
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
		// Serializing the record parameters
		$this->record->active = (int) $this->record->active;

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
		// Deserializing the record parameters
		$this->record->active = (bool) $this->record->active;

		// Exit (success)
		return $this;
	}
}
