<?php

class Zolago_Sizetable_Model_Resource_Sizetable extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagosizetable/sizetable','sizetable_id');
    }

    public function getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id)
    {
        error_reporting(E_ALL);
        ini_set("display_errors", 1);

//        /** @var Zolago_Sizetable_Helper_Data $helper */
//        $helper = Mage::helper('zolagosizetable');
//        $brandID = $helper->getBrandId();


        $results = '[dev]';
        $sizetable = $this->getTable('zolagosizetable/sizetable');
        $sizetableScope = $this->getTable('zolagosizetable/sizetable_scope');
        $sizetableRule = $this->getTable('zolagosizetable/sizetable_rule');


        //SELECT count(*) FROM `zolago_sizetable_role` WHERE `vendor_id` = 5 AND `brand_id` = NULL AND `attribute_set_id` = 59

        $query = "
        ";


//       $results = $this->getReadConnection()
//           ->query($query, array(
//               'sizetable' => $sizetable,
//               'sizetableScope' => $sizetableScope,
//               'sizetableRule' => $sizetableRule,
//                'vendor_id' => $vendor_id
//           ))
//           ->fetchAll();
////
//
//
//
//        return $query .' <br/><br/> '. print_r($results, true);
//        return $query;
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
