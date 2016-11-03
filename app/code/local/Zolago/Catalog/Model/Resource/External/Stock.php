<?php

/**
 * Class Zolago_Catalog_Model_Resource_ExternalStock
 */
class Zolago_Catalog_Model_Resource_External_Stock extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocatalog/external_stock', 'id');
    }

    /**
     * @param $batch
     * @return $this
     */
    public function updateExternalStock($batch)
    {
        if (empty($batch))
            return $this;

        $i = 0;
        $data = [];
        foreach ($batch as $batch) {
            $i++;

            $data[] = $batch;
            if (($i % 2) == 0) {
                $this->_updateExternalStockTable($data);
                $data = array();
            }
        }


        $this->_updateExternalStockTable($data);
        return $this;
    }


    /**
     * @param $data
     * @return $this
     */
    protected function _updateExternalStockTable($data)
    {
        if (empty($data))
            return $this;


        $adapter = $this->_getWriteAdapter();
        $adapter->insertOnDuplicate($this->getMainTable(), $data, array('qty','date_update'));

        return $this;

    }

}