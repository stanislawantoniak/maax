<?php

class Zolago_Sizetable_Model_Resource_Sizetable extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagosizetable/sizetable','sizetable_id');
    }

    public function getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id)
    {
		Mage::log("$vendor_id, $store_id, $attribute_set_id, $brand_id", null, "table_1.log");
        if (!$vendor_id) {
            return false;
        }
    
        $res = Mage::getSingleton('core/resource');
                
        $sizetable = $res->getTableName('zolagosizetable/sizetable');
        $sizetableScope = $res->getTableName('zolagosizetable/sizetable_scope');
        $sizetableRule = $res->getTableName('zolagosizetable/sizetable_rule');

        if ($brand_id) {
            if ($attribute_set_id) {
                $query_list[] = 'SELECT IF(ISNULL(ss.value),s.default_value,ss.value) as val FROM '.$sizetableRule.' as sr '.
                    'INNER JOIN '.$sizetable.' as s on s.sizetable_id = sr.sizetable_id '.
                    'LEFT JOIN '.$sizetableScope.' as ss ON ss.sizetable_id = sr.sizetable_id AND ss.store_id = \''.$store_id.'\' '.
                    ' WHERE sr.vendor_id = '.$vendor_id.' AND sr.brand_id = '.$brand_id.' AND sr.attribute_set_id = '.$attribute_set_id;
            }
            $query_list[] = 'SELECT IF(ISNULL(ss.value),s.default_value,ss.value) as val FROM '.$sizetableRule.' as sr '.
                'INNER JOIN '.$sizetable.' as s on s.sizetable_id = sr.sizetable_id '.
                'LEFT JOIN '.$sizetableScope.' as ss ON ss.sizetable_id = sr.sizetable_id AND ss.store_id = \''.$store_id.'\' '.
                ' WHERE sr.vendor_id = '.$vendor_id.' AND sr.brand_id = '.$brand_id.' AND sr.attribute_set_id IS NULL';
        }
        $query_list[] = 'SELECT IF(ISNULL(ss.value),s.default_value,ss.value) as val FROM '.$sizetableRule.' as sr '.
            'INNER JOIN '.$sizetable.' as s on s.sizetable_id = sr.sizetable_id '.
            'LEFT JOIN '.$sizetableScope.' as ss ON ss.sizetable_id = sr.sizetable_id AND ss.store_id = \''.$store_id.'\' '.
            ' WHERE sr.vendor_id = '.$vendor_id.' AND sr.brand_id IS NULL AND sr.attribute_set_id IS NULL';
        $conn = $res->getConnection('core_read');

        $query = 'SELECT val FROM ('.implode(' UNION ',$query_list).') AS connect LIMIT 1';

        $results = $conn->fetchOne($query);


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
			$notEmptySizeTable = array_filter($sizetable);
			if(!empty($notEmptySizeTable)) {
				$data[] = array(
					'sizetable_id' => $sizetableId,
					'store_id' => (int)$storeId,
					'value' => serialize($sizetable)
				);
			}
			unset($notEmptySizeTable);
		}


		if(count($data)) {
			$this->_getWriteAdapter()->insertMultiple($table, $data);
		}
		return parent::_afterSave($object);
	}

	/**
	 * @return array
	 */
	public function getScopes($stid) {
		if($stid) {
			$table = $this->getTable('zolagosizetable/sizetable_scope');
			$where = $this->_getReadAdapter()->quoteInto("sizetable_id = ?", $stid);
			$select = $this->_getReadAdapter()->select()->from($table)->where($where);
			$data = $this->_getReadAdapter()->fetchAll($select);
			$out = array();
			foreach ($data as $sizetable) {
				$out[$sizetable['store_id']] = $sizetable['value'];
			}
			return $out;
		}
	}
}
