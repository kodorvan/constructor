<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\deal\enumerations\direction as deal_direction,
	kodorvan\constructor\models\project\enumerations\status as project_status,
	kodorvan\constructor\models\project\enumerations\architecture as project_architecture,
	kodorvan\constructor\models\project\enumerations\purpose as project_purpose,
	kodorvan\constructor\models\project\enumerations\integration as project_integration,
	kodorvan\constructor\models\worker\enumerations\type as worker_type;

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
	LogicException as exception_logic,
	RuntimeException as exception_runtime;

/**
 * Deal
 *
 * @package kodorvan\constructor\models
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class deal extends core implements record_interface
{
	use record_trait;

	/**
	 * File
	 *
	 * @var string $file Path to the database file
	 */
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . 'deals.baza';

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
				new column('project', type::long_long_unsigned),
				new column('direction', type::char),
				new column('description', type::string, ['length' => 512]),
				new column('hours', type::integer_unsigned),
				new column('cost', type::float),
				new column('payment', type::float),
				new column('prepayment', type::float),
				new column('programmers', type::integer_unsigned),
				new column('designers', type::integer_unsigned),
				new column('boosters', type::integer_unsigned),
				new column('active', type::char),
				new column('confirmed', type::integer_unsigned),
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
	 * @throws exception_logic when failed to process project integration
	 *
	 * @param int $account The account identifier
	 * @param int $project The project identifier
	 * @param deal_direction $direction Direction of the deal
	 * @param string|null $description Description of the project
	 * @param int $hours Hours of the project development
	 * @param int|float $cost Cost per hour of the project development
	 * @param int|float $payment Payment of the project development
	 * @param int|float $prepayment Prepayment of the project development
	 * @param int $programmers Programmers of the project
	 * @param int $designers Designers of the project
	 * @param int $boosters Boosters of the project
	 * @param int $active Is the record active?
	 *
	 * @return record|false The record, if created
	 */
	public function write(
		int $account,
		int $project,
		deal_direction $direction,
		?string $description = null,
		int $hours = PROJECT_HOURS_MINIMAL,
		int|float $cost = PROJECT_COST_HOUR_DEFAULT,
		int|float $payment,
		int|float $prepayment,
		int $programmers = 0,
		int $designers = 0,
		int $boosters = 0,
		bool $active = true,
	): record|false {
		// Initializing the record
		$record = $this->database->record(
			$this->database->count() + 1,
			$account,
			$project,
			$direction->value,
			$description,
			$hours,
			(float) $cost,
			(float) $payment,
			(float) $prepayment,
			$programmers,
			$designers,
			$boosters,
			(int) $active,
			0,
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
		$this->record->direction = $this->record->direction->value;
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
		$this->record->direction = deal_direction::from($this->record->direction);
		$this->record->active = (bool) $this->record->active;

		// Writing the status of serializing
		$this->serialized = false;

		// Exit (success)
		return $this;
	}

	/**
	 * Project
	 *
	 * Search for the project
	 *
	 * @return project|null The project
	 */
	public function project(): ?project
	{
		// Search for the account project 
		$project = new project()->read(filter: fn(record $record) => $record->identifier === $this->project && $record->active === 1);

		if ($project instanceof project) {
			// Found the account project

			// Deserializing the project
			$project->deserialize();

			// Exit (success)
			return $project;
		}

		// Exit (fail)
		return null;
	}
}
