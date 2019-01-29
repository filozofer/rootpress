<?php

namespace Rootpress\utils;

/**
 * FileUtils
 * Many file utils function
 */
class FileUtils {

    /**
     * Get file size form path
     * @param $filepath string path of the file to get the size
     * @return string
     */
    public static function getFileSize($filepath){
	    if(!file_exists($filepath) || filesize($filepath) == 0) {
            return '0 Mo';
        }
        $bytes = filesize($filepath);
        $s = array('o', 'Ko', 'Mo', 'Go');
        $e = floor(log($bytes)/log(1024));
        return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
    }

	/**
	 * Format the file name to a format with full datetime before file extension
	 * Example : filename_2017_02_14_10_44_58.txt
	 * @param $fileName
	 *
	 * @return string The formated file name with full datetime
	 */
    public static function formatFileName($fileName){
	    $explodedFileName = explode('.', $fileName);
	    $explodedFileName[0] .= '_' . date('Y_m_d_H_i_s');
	    return implode('.', $explodedFileName);
    }
}
