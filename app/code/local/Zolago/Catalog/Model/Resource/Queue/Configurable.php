<?php
/**
 * resource model for product queue
 */
class Zolago_Catalog_Model_Resource_Queue_Configurable extends Zolago_Common_Model_Resource_Queue_Abstract {
    protected $_buffer = 500;
    protected $_dataToSave = array();

    public  function _construct() {

        $this->_init('zolagocatalog/queue_configurable','queue_id');
    }

    public function addToQueue($ids, $websiteId = null) {

        if(!empty($ids)){
            foreach($ids as $productId){
                $this->_prepareData($websiteId, $productId);
            }

            $this->_saveData();
        }
        return $ids;

    }

    protected function _resetData()
    {
        $this->_dataToSave = array();
    }

    protected function _prepareData($websiteId, $productId)
    {
        $key = $this->_buildIndexKey($websiteId, $productId);
        $this->_dataToSave[$key] = array(
            "insert_date" => date('Y-m-d H:i:s'),
            "website_id" => $websiteId,
            "product_id" => $productId
        );
    }

    protected function _buildIndexKey($websiteId, $productId)
    {
        return "$websiteId|$productId";
    }

    /**
     * @return insert products id values
     */
    protected function _saveData()
    {
        $i = $this->_buffer;
        $all = 0;
        $insert = array();
        $this->_getWriteAdapter()->beginTransaction();

        foreach ($this->_dataToSave as $item) {
            $insert[] = $item;
            $i--;
            // Insert via buffer
            if ($i == 0) {
                $i = $this->_buffer;
                $all += $this->_buffer;
                $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $insert, array());
                $insert = array();
            }
        }

        // Insert out of buffer values
        if (count($insert)) {
            $all += count($insert);
            $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $insert, array());
        }

        // Commit transaction
        $this->_getWriteAdapter()->commit();
        $this->_resetData();
        return $all;
    }



}

