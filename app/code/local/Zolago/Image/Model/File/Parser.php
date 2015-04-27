<?php
class Zolago_Image_Model_File_Parser extends Mage_Core_Model_Abstract {
    protected $_header;
    protected $_line;

    /**
     * get columns number from header
     *
     * @param string $header
     * @return array
     */

    public function parseHeaderColumns($header) {
        $list = explode(';',$header);
        $sku = array_keys($list,'sku');
        $file = array_keys($list,'file');
        $order = array_keys($list,'order');
        $label = array_keys($list,'label');
        if (count($sku)!= 1 ||
                count($file)!=1 ||
                count($order)!=1 ||
                count($label)!=1) {

            Mage::throwException(Mage::helper('zolagocatalog')->__('CSV file first line should contain: sku;file;order;label'));
        }
        $this->_header =  array (
                              'sku' => array_pop($sku),
                              'file' => array_pop($file),
                              'order' => array_pop($order),
                              'label' => array_pop($label),
                          );
        return $this->_header;
    }


    /**
     *
     * @param string $name
     * @param string $pattern
     * @param int $number
     * @return
     */
    protected function _checkArg($name,$pattern,$number) {
        $line = explode(';',$this->_line);
        if (!isset($line[$this->_header[$name]])) {
            Mage::throwException(Mage::helper('zolagocatalog')->__('Wrong file format. Error at line %d - field %s: %s',$number,$name,$this->_line));
        }
        if (!preg_match($pattern,$line[$this->_header[$name]])) {
            Mage::throwException(Mage::helper('zolagocatalog')->__('Wrong file format. Error at line %d - field %s: %s',$number,$name,$this->_line));
        }
    }
    /**
     * check file format
     *
     * @param string $file
     * @return
     */

    public function checkCsvFile($file) {
        foreach ($file as $number=>$line) {
            $this->_line = trim($line);
            if ($this->_line) {
                $this->_checkArg('sku','/([a-zA-Z\.\-\_\ \(\)\{\}ąćłóżźęśńĘÓĄŚŻŹĆŃŁ0-9\:\/@#]+)/',$number);
                $this->_checkArg('file','/([a-zA-Z\.\-\_\ \(\)\{\}ąćłóżźęśńĘÓĄŚŻŹĆŃŁ0-9\:\/@#]+)/',$number);
                $this->_checkArg('order','/[0-9]*/',$number);
                $this->_checkArg('label','/([a-zA-Z\.\-\_\ \(\)\{\}ąćłóżźęśńĘÓĄŚŻŹĆŃŁ0-9]+)?/',$number);
            }
        }

    }

    /**
     * prepare sku import list
     *
     * @param string $file
     * @return array
     */

    public function createImportListChunk($file, $offset = 0, $limit = 0)
    {
        $data = array();
        $importList = array();

        $data['total_count'] = 0;
        $data['list'] = $importList;

        if (!$file) {
            return $data;
        }
        $fullImportList = $this->createImportListFromFile($file);

        if (!empty($limit) || !empty($offset)){
            $importList = array_slice($fullImportList, $offset * $limit , $limit);
        }
        $data['total_count'] = count($fullImportList);
        $data['list'] = $importList;
        $data['full_list'] = $fullImportList;

        return $data;
    }
    /**
     * prepare sku import list  
     *
     * @param string $file
     * @return array
     */

    public function createImportListFromFile($file)
    {
        $importList = array();
        if (!$file) {
            return $importList;
        }
        foreach ($file as $line) {
            if (trim($line)) {
                $tmp = explode(';', $line);
                $out = array (
                    trim($tmp[$this->_header['sku']]),
                    trim($tmp[$this->_header['file']]),
                    trim($tmp[$this->_header['order']]),
                    trim($tmp[$this->_header['label']])
                );
                $importList[$out[0]][] = $out;
            }
        }
        return $importList;
    }


}