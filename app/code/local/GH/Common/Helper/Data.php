<?php
class GH_Common_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Return max file size for upload file (in Byte)
     */
    public static function getMaxUploadFileSize() {
        return min(GH_Common_Helper_Data::getIniByteValue('post_max_size'), GH_Common_Helper_Data::getIniByteValue('upload_max_filesize'));
    }

    /**
     * Common use with post_max_size & upload_max_filesize
     *
     * @param $setting
     * @return int|string
     */
    public static function getIniByteValue($setting) {
        $val = trim(ini_get($setting));
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }


    /**
     * Clean file name
     * @param $string
     * @param $customChar
     * @return mixed
     */
    public static function cleanFileName($string, $customChar = '')
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^.A-Za-z0-9\-\_]/', $customChar, $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        return preg_replace('/_+/', '_', $string); // Replaces multiple underscores with single one.
    }
}