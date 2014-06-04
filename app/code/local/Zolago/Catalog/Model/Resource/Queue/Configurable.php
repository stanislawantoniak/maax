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

    public function addToQueue($ids)
    {
        if (!empty($ids)) {
            foreach ($ids as $productId) {
                $this->_prepareData($productId);
            }

            $this->_saveData();
        }
        return $ids;
    }

    public function addToQueueProduct($id)
    {
        if (!empty($id)) {
            $this->getReadConnection()->insert(
                $this->getTable('zolagocatalog/queue_configurable'),
                array("insert_date" => date('Y-m-d H:i:s'), "status" => 0, "product_id" => $id)
            );
        }
        return $id;
    }

    protected function _resetData()
    {
        $this->_dataToSave = array();
    }

    protected function _prepareData($productId)
    {
        $key = $this->_buildIndexKey($productId);
        $this->_dataToSave[$key] = array(
            "insert_date" => date('Y-m-d H:i:s'),
            "product_id" => $productId
        );
    }

    protected function _buildIndexKey($productId)
    {
        return "$productId";
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

    public function clearQueue()
    {
        $condition = $this->_getWriteAdapter()->quoteInto('status = ?', 1);
        $this->_getWriteAdapter()->delete($this->getTable('zolagocatalog/queue_configurable'), $condition);
    }

}

