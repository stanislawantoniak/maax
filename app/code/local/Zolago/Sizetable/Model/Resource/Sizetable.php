<?php

class Zolago_Sizetable_Model_Resource_Sizetable extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagosizetable/sizetable','sizetable_id');
    }

    public function getSizetableCMS($vendor_id, $store_id, $attribute)
    {
        error_reporting(E_ALL);
        ini_set("display_errors", 1);


        $resource = Mage::getSingleton('zolagosizetable/resource');
        $read = $resource->getConnection('core_read');

        $tableName = $resource->getTableName('catalog/product');


        $query = 'SELECT * FROM ' . $tableName;

        $results = $read->fetchAll($query);


        return $results;
    }

	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return Mage_Core_Model_Abstract|void
	 */
	protected function _afterSave(Mage_Core_Model_Abstract $object) {
		$postData = $object->getPostData();
		$table  = $this->getTable('zolagosizetable/sizetable_scope');
		$sizetableId = (int)$object->getSizetableId();

		// delete all scopes
		$where = array(
			'sizetable_id = ?' => $sizetableId
		);
		$this->_getWriteAdapter()->delete($table, $where);

		$data = array();

		foreach ($postData as $storeId => $sizetable) {
			if($sizetable != '') {
				$data[] = array(
					'sizetable_id' => $sizetableId,
					'store_id' => (int)$storeId,
					'value' => $sizetable
				);
			}
		}
		if(count($data)) {
			$this->_getWriteAdapter()->insertMultiple($table, $data);
		}
		return parent::_afterSave($object);
	}
}
