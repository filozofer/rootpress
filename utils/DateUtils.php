<?php

namespace Rootpress\utils;

use DateTime;

/**
 * Class DateUtils
 * @package Rootpress\utils
 */
class DateUtils {
	const YYYYMMDD = 'Ymd';

	/**
	 * Format a string date
	 * @param string $dateToFormat the date to format
	 * @param string $oldFormat the old format of the date
	 * @param string $newFormat the wished format
	 *
	 * @return string
	 */
	public static function formatDate($dateToFormat, $oldFormat, $newFormat){
		// Get the datetime object of the given date
		$dateTime = DateTime::createFromFormat($oldFormat, $dateToFormat);
		// Return the old date if the format fail.
		return $dateTime ? $dateTime->format($newFormat) : $dateToFormat;
	}
}