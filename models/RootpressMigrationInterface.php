<?php

namespace Rootpress\models;

/**
 * Rootpress Migration Interface to obligate dev to implements some methods in their Migration entities
 */
interface RootpressMigrationInterface  {

	/**
	 * Launch the migration
	 */
	public function up();

	/**
	 * Reverse the migration
	 */
	public function down();

}
