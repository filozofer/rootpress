<?php

namespace Rootpress\models;

use Rootpress\models\RootpressMigrationInterface;

abstract class RootpressMigration implements RootpressMigrationInterface {

	/**
	 * Corresponding migration number
	 * @var int
	 */
	public $migration_number = 0;

	/**
	 * Migration name
	 */
	public $migration_name = '';

	/**
	 * Declare the migration
	 */
	public static function declareMigration() {

		// Instantiate migration
		$childClass = get_called_class();
		$migration = new $childClass();

		// Declare the migration
		add_action('rootpress_migrations_' . $migration->getMigrationNumber(), [$migration, 'migrate']);
		add_filter('rootpress_migrations_list', function($list) use ($migration) {

			// Handle collision
			if(isset($list[$migration->getMigrationNumber()])) {
				$errorMessage = 'Migration version number collision.';
				$errorMessage .= 'Migration ' . $migration->getMigrationNumber() . 'has multiple implementation name : ';
				$errorMessage .= $list[$migration->getMigrationNumber()] . ' and ' . $migration->getMigrationName();
				throw new \Exception($errorMessage);
			}

			// Add migration name to the list and return it to continue the filter
			$list[$migration->getMigrationNumber()] = $migration->getMigrationNumber() . ' : ' . $migration->getMigrationName();
			return $list;

		});

	}

	/**
	 * Launch the migration
	 * @param string $action
	 */
	public function migrate($action = 'up') {

		// Call the proper callback
		switch ($action) {
			case 'up': $this->up(); break;
			case 'down': $this->down(); break;
		}

	}

	/**
	 * @return int
	 */
	public function getMigrationNumber() {
		return $this->migration_number;
	}

	/**
	 * @param int $migration_number
	 */
	public function setMigrationNumber( $migration_number ) {
		$this->migration_number = $migration_number;
	}

	/**
	 * @return mixed
	 */
	public function getMigrationName() {
		return (!empty($this->migration_name)) ? $this->migration_name : 'Migration ' . $this->getMigrationNumber();
	}

	/**
	 * @param mixed $migration_name
	 */
	public function setMigrationName( $migration_name ) {
		$this->migration_name = $migration_name;
	}

}