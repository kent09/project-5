<?php

namespace App\Helpers;

class FileHelper {

    /**
     * Returns the file extension
     *
     * @param $file
     * @return string
     */
    public static function getFileExtension($file) {
        return strtolower($file->getClientOriginalExtension());
    }

}