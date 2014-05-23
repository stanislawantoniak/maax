<?php
class Zolago_Catalog_Helper_Log extends Mage_Core_Helper_Abstract {

    const ZOLAGO_API_LOG_FOLDER = 'var/log/zolago_api_log';
    const ZOLAGO_API_LOG_FOLDER_TEST = 'var/log/zolago_api_log/test';
    const ZOLAGO_API_LOG_FILE_NAME = 'zolago_log';

    /**
     * @param array $log
     */
    public static function log($log, $testMode = FALSE)
    {

        $file = FALSE;
        if (!empty($log)) {
            set_time_limit(60 * 40);
            ini_set('memory_limit', '512M');

            self::prepareLogDirs($testMode);
            if (!$testMode) {
                $logFileParts = array(MAGENTO_ROOT, self::ZOLAGO_API_LOG_FOLDER, date('Y'), date('m'), self::getFileName());
                $logFile = implode(DS, $logFileParts);
            } else {
                $logFileParts = array(MAGENTO_ROOT, self::ZOLAGO_API_LOG_FOLDER_TEST, self::getFileName(TRUE));
                $logFile = implode(DS, $logFileParts);
            }

            if (!empty($log)) {
                //echo $log;
                //$file will return int The function returns the number of bytes that were written to the file, or
                // false on failure
                $file = file_put_contents($logFile, $log);
            }
        }
        return $file;
    }

    /** Construct log file name depends on current date
     * @return string
     */
    private function getFileName($testMode = FALSE)
    {
        if (!$testMode) {
            $fileNameParts = array(self::ZOLAGO_API_LOG_FILE_NAME, date('m_d_Y_H_i_s'));
        } else {
            $fileNameParts = array(self::ZOLAGO_API_LOG_FILE_NAME);
        }

        return implode('_', $fileNameParts) . '.json';
    }

    /**
     * Read log file
     * TODO need to improve after test live result
     * @return array
     */
    public static function readZolagoAPILog()
    {
        $page = 0;
        $limit = 100;
        $offset = $page * $limit;


        $logFileParts = array(MAGENTO_ROOT, self::ZOLAGO_API_LOG_FOLDER, date('Y'), date('m'), 'zolago_log_05_23_2014_12_44_03.json');
        $logFile = implode(DS, $logFileParts);

        set_time_limit(60 * 40);
        ini_set('memory_limit', '2024M');
        $content = file_get_contents($logFile);

        $res = json_decode($content);

        echo number_format(memory_get_usage(true), 2) . "\n";

        return $res;
    }


    /**
     * Prepare log dirs structure
     */
    private function  prepareLogDirs($testMode)
    {
        $dirCommon = MAGENTO_ROOT . DS . self::ZOLAGO_API_LOG_FOLDER;

        if (!is_dir($dirCommon)) {
            mkdir($dirCommon);
            @chmod($dirCommon, 0777);
        }

        if (!$testMode) {
            $dirYear = MAGENTO_ROOT . DS . self::ZOLAGO_API_LOG_FOLDER . DS . date('Y');
            if (!is_dir($dirYear)) {
                mkdir($dirYear);
                @chmod($dirYear, 0777);
            }


            $dirMonth = MAGENTO_ROOT . DS . self::ZOLAGO_API_LOG_FOLDER . DS . date('Y') . DS . date('m');
            if (!is_dir($dirMonth)) {
                mkdir($dirMonth);
                @chmod($dirMonth, 0777);
            }
        } else {
            $dirTest = MAGENTO_ROOT . DS . self::ZOLAGO_API_LOG_FOLDER . DS . 'test';
            if (!is_dir($dirTest)) {
                mkdir($dirTest);
                @chmod($dirTest, 0777);
            }
        }
    }
}