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
    private function getFileSize($filepath){
        if(!file_exists($filepath)) {
            return '0 Mo';
        }
        $bytes = filesize($filepath);
        $s = array('o', 'Ko', 'Mo', 'Go');
        $e = floor(log($bytes)/log(1024));
        return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
    }

}
