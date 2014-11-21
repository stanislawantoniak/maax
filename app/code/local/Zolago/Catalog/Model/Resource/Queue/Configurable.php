<?php
/**
 * resource model for product queue
 */
class Zolago_Catalog_Model_Resource_Queue_Configurable extends Zolago_Common_Model_Resource_Queue_Abstract {
    /**
     * @var int
     */
    protected $_buffer = 500;
    /**
     * @var array
     */
    protected $_dataToSave = array();


    /**
     * Init main table
     */
    public  function _construct() {

        $this->_init('zolagocatalog/queue_configurable','queue_id');
    }

    /**
     * Add item to queue
     * @param $ids
     *
     * @return mixed
     */
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

    /**
     * @param $id
     *
     * @return mixed
     */
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

    /**
     * Reset data after save
     */
    protected function _resetData()
    {
        $this->_dataToSave = array();
    }

    /**
     * Prepare data to save
     * @param $productId
     */
    protected function _prepareData($productId)
    {
        $key = $this->_buildIndexKey($productId);
        $this->_dataToSave[$key] = array(
            "insert_date" => date('Y-m-d H:i:s'),
            "product_id" => $productId
        );
    }

    /**
     * @param $productId
     *
     * @return string
     */
    protected function _buildIndexKey($productId)
    {
        return "$productId";
    }

    /**
     * @return insert products id values
     */
    protected function _saveData()
    {

        $this->_getWriteAdapter()->beginTransaction();

        foreach ($this->_dataToSave as $item) {
            $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $item, array());
        }

        // Commit transaction
        $this->_getWriteAdapter()->commit();
        $this->_resetData();
    }

    /**
     * Clear precessed queue
     */
    public function clearQueue()
    {
        $condition = $this->_getWriteAdapter()->quoteInto('status = ?', 1);
        $this->_getWriteAdapter()->delete($this->getTable('zolagocatalog/queue_configurable'), $condition);

    }

}

