<?php

namespace Rootpress\utils;

use Rootpress\enums\ACFType;

/**
 * Class CRUDUtils
 * @package Rootpress\utils
 */
class CRUDUtils {

	/**
	 * Format an ACF value
	 *
	 * @param array $attrMap
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public static function formatACF( $attrMap, $value ) {
		if ( array_key_exists( 'type', $attrMap ) ) {
			switch ( $attrMap['type'] ) {
				case ACFType::DATE_PICKER:
					if ( array_key_exists( 'format', $attrMap ) ) {
						// Format the date to the YYYYMMDD format
						$value = DateUtils::formatDate( $value, $oldFormat = $attrMap['format'], $newFormat = DateUtils::YYYYMMDD );
					}
					break;
			}
		}

		return $value;
	}
}