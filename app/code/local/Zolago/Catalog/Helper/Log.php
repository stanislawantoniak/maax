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

        $codeRoot = getcwd();

        $file = FALSE;
        if (!empty($log)) {
            set_time_limit(60 * 40);
            ini_set('memory_limit', '512M');
            Zend_Debug::dump($log);
            self::prepareLogDirs($testMode);
            if (!$testMode) {
                $logFileParts = array($codeRoot, self::ZOLAGO_API_LOG_FOLDER, date('Y'), date('m'), self::getFileName());
                $logFile = implode(DS, $logFileParts);
            } else {
                $logFileParts = array($codeRoot, self::ZOLAGO_API_LOG_FOLDER_TEST, self::getFileName(TRUE));
                $logFile = implode(DS, $logFileParts);
            }
            Zend_Debug::dump($logFile);
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
        $codeRoot = getcwd();
        $page = 0;
        $limit = 100;
        $offset = $page * $limit;


        $logFileParts = array($codeRoot, self::ZOLAGO_API_LOG_FOLDER, date('Y'), date('m'), 'zolago_log_05_23_2014_12_44_03.json');
        $logFile = implode(DS, $logFileParts);

        set_time_limit(60 * 40);
        ini_set('memory_limit', '2024M');
        $content = file_get_contents($logFile);

        $res = json_decode($content);

        echo number_format(memory_get_usage(true), 2) . "\n";

        return $res;
    }



    public static function emulateConfigurable()
    {
        $storeId = 1;
        $data = array();

        /*Load xml data*/
        $base_path = Mage::getBaseDir('base');
        // $file = $base_path . '/var/log/price2-0.xml';
        $file = $base_path . '/var/log/price2-1.xml';

        $configurableUpdate = $base_path . '/var/log/configurableUpdate';

        if (!is_dir($configurableUpdate)) {
            mkdir($configurableUpdate);
            @chmod($configurableUpdate, 0777);
        }

        $date = array(
            date('m'),date('d'),date('Y'),date('H'),date('i'),date('s')
        );
        $configurableFile = $base_path . '/var/log/configurableUpdate/configurable_'.implode('_',$date).'.txt';
        @chmod($configurableFile, 0777);

        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
        $document = (array)$xml;



        $merchant = isset($document['merchant']) ? $document['merchant'] : FALSE;
        /*Load xml data*/
        if ($merchant) {


            $priceList = isset($document['priceList']) ? $document['priceList'] : array();

            if (!empty($priceList)) {
                //$priceList not empty, so we can start updating
//                $storeId = 0;
                $productsXML = isset($priceList->product) ? $priceList->product : array();

                if (!empty($productsXML)) {
                    $productsButch = array();
                    foreach ($productsXML as $productsXMLItem) {
                        $attributes = $productsXMLItem->attributes();
                        $skuXML = (string)$productsXMLItem;
//                        $price = (string)$attributes->price;
                        $data[] = "'".$merchant . '-' . $skuXML . "'";
                    }
                    unset($productsXMLItem);
                    unset($price);

                }

            }

        }

        if (!empty($data)) {
            $data = array_merge(array($storeId),$data);
            file_put_contents($configurableFile, implode(',' ,$data));

        }
    }


    /**
     * Prepare log dirs structure
     */
    private function  prepareLogDirs($testMode)
    {
        $codeRoot = getcwd();
        $dirCommon = $codeRoot . DS . self::ZOLAGO_API_LOG_FOLDER;

        if (!is_dir($dirCommon)) {
            mkdir($dirCommon);
            @chmod($dirCommon, 0777);
        }

        if (!$testMode) {
            $dirYear = $codeRoot . DS . self::ZOLAGO_API_LOG_FOLDER . DS . date('Y');
            if (!is_dir($dirYear)) {
                mkdir($dirYear);
                @chmod($dirYear, 0777);
            }


            $dirMonth = $codeRoot . DS . self::ZOLAGO_API_LOG_FOLDER . DS . date('Y') . DS . date('m');
            if (!is_dir($dirMonth)) {
                mkdir($dirMonth);
                @chmod($dirMonth, 0777);
            }
        } else {
            $dirTest = $codeRoot . DS . self::ZOLAGO_API_LOG_FOLDER . DS . 'test';
            if (!is_dir($dirTest)) {
                mkdir($dirTest);
                @chmod($dirTest, 0777);
            }
        }
    }
}