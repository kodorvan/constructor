<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Files of the project
use kodorvan\constructor\models\core,
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
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'projects.baza';

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
				new column('deal', type::long_long_unsigned),
				new column('name', type::string, ['length' => 64]),
				new column('status', type::string, ['length' => 16]),
				new column('architecture', type::string, ['length' => 32]),
				new column('purpose', type::string, ['length' => 32]),
				new column('integrations', type::integer_unsigned),
				/* new column('programmers', type::integer_unsigned),
				new column('designers', type::integer_unsigned),
				new column('boosters', type::integer_unsigned), */
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
	 * @throws exception_logic when failed to process project integration
	 *
	 * @param int $account The account identifier
	 * @param int|null $deal The deal identifier
	 * @param project_status $status Status of the project
	 * @param string|null $name Name of the project
	 * @param project_architecture|null $architecture Architecture of the project
	 * @param project_purpose|null $purpose Purpose of the project
	 * @param int|array $Inegrations Integrations of the project

	 * @param int $programmers Programmers of the project
	 * @param int $designers Designers of the project
	 * @param int $boosters Boosters of the project

	 * @param int $active Is the record active?
	 *
	 * @return record|false The record, if created
	 */
	public function write(
		int $account,
		?int $deal = null,
		?string $name = null,
		project_status $status = project_status::creating,
		?project_architecture $architecture  = null,
		?project_purpose $purpose  = null,
		int|array $integrations = 0b000000,
		/* int $programmers = 0,
		int $designers = 0,
		int $boosters = 0, */
		bool $active = true,
	): record|false {
		if (empty($name)) {
			// Not received the project name

			// Generating the project name
			$name = 'Project №' . count(new account()->read(filter: fn(record $record) => $record->active === 1 && $record->identifier === $account)?->projects() ?? []);
		}

		if (is_array($integrations)) {
			// Received integrations in array format

			// Initializing the project integrations buffer
			$buffer = 0b000000;

			foreach ($integrations as $integration) {
				// Iterating over integrations

				if ($integration instanceof project_integration) {
					// Project integration

					// Writing the project integration into the project integrations buffer
					$buffer |= $integration->value;
				} else {
					// Not project integration

					throw new exception_logic('Failed to process project integration');
				}
			}

			// Reinitializing the project integrations
			$integrations = $buffer;

			// Deinitializing the project integrations buffer
			unset($buffer);
		}

		// Initializing the record
		$record = $this->database->record(
			$this->database->count() + 1,
			$account,
			(int) $deal,
			$name,
			$status->name,
			$architecture?->name ?? '',
			$purpose?->name ?? '',
			$integrations,
			/* $programmers,
			$designers,
			$boosters, */
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
		$this->record->status = $this->record->status->name;
		$this->record->architecture = $this->record->architecture?->name ?? '';
		$this->record->purpose = $this->record->purpose?->name ?? '';
		$this->record->integrations = project_integration::encode($this->record->integrations);
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
		$this->record->status = project_status::{$this->record->status};
		$this->record->architecture = $this->architecture instanceof project_architecture
			? project_architecture::{$this->architecture->type}
			: null;
		$this->record->purpose = $this->purpose instanceof project_purpose
			? project_purpose::{$this->purpose->type}
			: null;
		$this->record->integrations = project_integration::decode($this->record->integrations);
		$this->record->active = (bool) $this->record->active;

		// Writing the status of serializing
		$this->serialized = false;

		// Exit (success)
		return $this;
	}

	/**
	 * Account
	 *
	 * Search for the account
	 *
	 * @return account|null The account
	 */
	public function account(): ?account
	{
		// Search for the account account
		$account = new account()->read(filter: fn(record $record) => $record->identifier === $this->account && $record->active === 1);

		if ($account instanceof account) {
			// Found the account account

			// Deserializing the record
			$account->deserialize();

			// Exit (success)
			return $account;
		}

		// Exit (fail)
		return null;
	}

	/**
	 * Hours
	 * 
	 * Calculate the project development hours
	 *
	 * @throws exception_runtime The record is not deserialized
	 *
	 * @param bool $absolute Summary all coefficients and then multiply?
	 *
	 * @return int|float The project development hours
	 */
	public function hours(bool $absolute = false): int
	{
		if ($this->serialized) {
			// The record is serialized

			throw new exception_runtime('The record is not deserialized');
		}

		// Initializing start hours
		$start = PROJECT_START_HOURS ?? 1;
		$start < 1 and $start = 1;

		// Initializing additional hours
		$additional = PROJECT_HOURS_ADDITIONAL ?? 0;

		if ($absolute) {
			// The absolute coefficient

			// Declaring coefficient
			$coefficient = PROJECT_START_COEFFICIENT ?? 0;

			if (isset($this->architecture)) {
				// Initialized the project architecture

				// Adding into the coefficient
				$coefficient += $this->architecture->coefficient() ?? 0;
			}

			if (isset($this->purpose)) {
				// Initialized the project purpose

				// Adding into the coefficient
				$coefficient += $this->purpose->coefficient() ?? 0;
			}

			if (!empty($this->integrations)) {
				// Initialized the project integrations

				foreach ($this->integrations as $integration) {
					// Iterating over the project integrations

					// Adding into the coefficient
					$coefficient += $integration->coefficient() ?? 0;
				}
			}

			// Calculating the development hours
			$hours = $start * $coefficient + $additional;

			// Calculating and exit (success)
			return (int) ceil(max($hours, PROJECT_HOURS_MINIMAL));
		} else {
			// The relative coefficient

			// Initializing the development hours
			$hours = $start;

			if (isset($this->architecture)) {
				// Initialized the project architecture

				// Adding into the coefficient
				$hours *= $this->architecture->coefficient() ?? 1;
			}

			if (isset($this->purpose)) {
				// Initialized the project purpose

				// Adding into the coefficient
				$hours *= $this->purpose->coefficient() ?? 1;
			}

			if (!empty($this->integrations)) {
				// Initialized the project integrations

				foreach ($this->integrations as $integration) {
					// Iterating over the project integrations

					// Adding into the coefficient
					$hours *= $integration->coefficient() ?? 1;
				}
			}

			// Calculating with additional hours
			$hours += $additional;

			// Calculating and exit (success)
			return (int) ceil(max($hours, PROJECT_HOURS_MINIMAL));
		}
	}

	/**
	 * Payment
	 * 
	 * Calculate the project development payment
	 *
	 * @param int|float $cost Cost per hour
	 * @param int $hours The project development hours
	 * @param int $programmers Programmers
	 * @param int $designers Designers
	 * @param int $boosters Boosters
	 *
	 * @return array ['full' => int|float, 'prepayment' => int|float]
	 */
	public function payment(
		int|float $cost = PROJECT_COST_HOUR_DEFAULT,
		int $hours = PROJECT_HOURS_MINIMAL,
		int $programmers = 0,
		int $designers = 0,
		int $boosters = 0
	): array {
		// Initializing costs
		$costs = [
			'full' => ceil($hours * $cost),
			'prepayment' => null
		];

		// Initializing default workers amounts
		$default = [
			'programmers' => $this->architecture?->workers()[worker_type::programmer->name] ?? 0,
			'designers' => $this->architecture?->workers()[worker_type::designer->name] ?? 0,
			'boosters' => $this->architecture?->workers()[worker_type::booster->name] ?? 0
		];

		if ($programmers > $default['programmers']) {
			// Programmers amount more than default

			// Calculating the full cost
			$costs['full'] *= $programmers * PROJECT_WORKERS_PROGRAMMERS_COEFFICIENT;
		} else if ($programmers < $default['programmers']) {
			// Programmers amount less than default

			// Calculating the full cost
			$costs['full'] /= max($programmers, 1) * PROJECT_WORKERS_PROGRAMMERS_COEFFICIENT;
		}

		if ($designers > $default['designers']) {
			// Designers amount more than default

			// Calculating the full cost
			$costs['full'] *= $designers * PROJECT_WORKERS_DESIGNERS_COEFFICIENT;
		} else if ($designers < $default['designers']) {
			// Designers amount less than default

			// Calculating the full cost
			$costs['full'] /= max($designers, 1) * PROJECT_WORKERS_DESIGNERS_COEFFICIENT;
		}

		if ($boosters > $default['boosters']) {
			// Boosters amount more than default

			// Calculating the full cost
			$costs['full'] *= $boosters * PROJECT_WORKERS_BOOSTERS_COEFFICIENT;
		} else if ($boosters < $default['boosters']) {
			// Boosters amount less than default

			// Calculating the full cost
			$costs['full'] /= max($boosters, 1) * PROJECT_WORKERS_BOOSTERS_COEFFICIENT;
		}

		// Calculating the prepayment
		$costs['prepayment'] = ceil($costs['full'] * (PROJECT_COST_PREPAYMENT_PERCENTS / 100));

		// Exit (success)
		return $costs;
	}
}
