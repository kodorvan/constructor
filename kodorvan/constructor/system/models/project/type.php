<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\project;

// Files of the type
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\project\enumerations\type as project_type;

// Baza database
use mirzaev\baza\database,
	mirzaev\baza\column,
	mirzaev\baza\record,
	mirzaev\baza\enumerations\encoding,
	mirzaev\baza\enumerations\type as baza_type;

// Active Record pattern
use mirzaev\record\interfaces\record as record_interface,
	mirzaev\record\traits\record as record_trait;

// Svoboda time
use svoboda\time\statement as svoboda;

// Built-in libraries
use Exception as exception,
	RuntimeException as exception_runtime;

/**
 * Type
 *
 * @package kodorvan\constructor\models
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class type extends core implements record_interface
{
	use record_trait;

	/**
	 * File
	 *
	 * @var string $database Path to the database file
	 */
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'project' . DIRECTORY_SEPARATOR . 'type.baza';

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
				new column('identifier', baza_type::long_long_unsigned),
				new column('account', baza_type::long_long_unsigned),
				new column('type', baza_type::string, ['length' => 32]),
				new column('special', baza_type::string, ['length' => 256]),
				new column('active', baza_type::char),
				new column('updated', baza_type::integer_unsigned),
				new column('created', baza_type::integer_unsigned)
			)
			->connect($this->file);

		// Initializing the record
		$record instanceof record and $this->record = $record;
	}

	/**
	 * Write
	 *
	 * @param int $account The account identifier
	 * @param project_type $type Type of the project
	 * @param string $special Information about special project
	 * @param int $active Is the record active?
	 *
	 * @return int|false The record identifier, if created
	 */
	public function write(
		int $account,
		project_type $type = project_type::special,
		string $special = '',
		bool $active = true,
	): int|false {
		$record = $this->database->record(
			$this->database->count() + 1,
			$account,
			$type->name,
			$type === project_type::special ? $special : '',
			(int) $active,
			svoboda::timestamp(),
			svoboda::timestamp()
		);

		// Writing the record into the database
		$created = $this->database->write($record);

		// Exit (success)
		return $created ? $record->identifier : false;
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
		$this->record->type = $this->record->type->name;

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
		$this->record->type = project_type::{$this->record->active};

		// Exit (success)
		return $this;
	}
}
