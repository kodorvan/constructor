<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\project\enumerations\status as project_status,
	kodorvan\constructor\models\project\enumerations\status as project_type;

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
 * Project
 *
 * @package kodorvan\constructor\models
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class project extends core implements record_interface
{
	use record_trait;

	/**
	 * File
	 *
	 * @var string $file Path to the database file
	 */
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'project.baza';

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
				new column('status', type::string, ['length' => 16]),
				new column('type', type::string, ['length' => 32]),
				new column('name', type::string, ['length' => 64]),
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
	 * @param project_status $status Status of the project
	 * @param project_type $status Type of the project
	 * @param string|null $name Name of the project
	 * @param int $active Is the record active?
	 *
	 * @return record|false The record, if created
	 */
	public function write(
		int $account,
		project_status $status = project_status::creating,
		project_type $type = project_type::special,
		?string $name = null,
		bool $active = true,
	): record|false {
		if (empty($name)) {
			// Not received the project name

			// Generating the project name
			$name = 'Project №' . count(new account()->read(filter: fn(record $record) => $record->active === 1 && $record->account === $account)?->projects() ?? []);
		}

		$record = $this->database->record(
			$this->database->count() + 1,
			$account,
			$status->name,
			$type->name,
			$name,
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
		$this->record->active = (int) $this->record->active;
		$this->record->status = $this->record->status->name;
		$this->record->type = $this->record->type->name;

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
		$this->record->active = (bool) $this->record->active;
		$this->record->status = project_status::{$this->record->status};
		$this->record->type = project_status::{$this->record->type};

		// Writing the status of serializing
		$this->serialized = false;

		// Exit (success)
		return $this;
	}

	/**
	 * Parameters
	 *
	 * Search for all the project properties
	 *
	 * @return self The instance from which the method was called (fluent interface)
	 */
	public function parameters(): self
	{
		// Deserializing the record parameters
		$this->record->active = (bool) $this->record->active;
		$this->record->status = project_status::{$this->record->status};

		if (!$this->serialized) {
			// Not serialized
		

		} else {
			// Serialized

			// Exit (fail)
			throw new exception('The project implementator is serialized');
		}
		/* if ($this->record->type === '') */


		// Exit (success)
		return $this;
	}
}
