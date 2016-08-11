<?php

/**
 * Entity attribute option collection
 *
 * Class Zolago_Eav_Model_Resource_Entity_Attribute_Option_Collection
 */
class Zolago_Eav_Model_Resource_Entity_Attribute_Option_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection {

    /**
     * Add store filter to collection
     *
     * @param int $storeId
     * @param bool $useDefaultValue
     * @return $this
     */
    public function setStoreFilter($storeId = null, $useDefaultValue = true) {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $adapter = $this->getConnection();

        $joinCondition    = $adapter->quoteInto('tsv.option_id = main_table.option_id AND tsv.store_id = ?', $storeId);
        $tbvStoreId       = (int)Mage::app()->getStore($storeId)->getAttributeBaseStore();

        if ($useDefaultValue) {
            $this->getSelect()
                ->join(
                    array('tdv' => $this->_optionValueTable), // Table Default Value -> tdv
                    'tdv.option_id = main_table.option_id',
                    array('default_value' => 'value'));
            if ($tbvStoreId) {
                $this->getSelect()
                    ->joinLeft(
                        array('tbv' => $this->_optionValueTable), // Table Base Value -> tbv
                        $adapter->quoteInto('tbv.option_id = main_table.option_id AND tbv.store_id = ?', $tbvStoreId),
                        array('base_value' => 'value'));
                $this->getSelect()
                    ->joinLeft(
                        array('tsv' => $this->_optionValueTable), // Table Store Value -> tsv
                        $joinCondition,
                        array(
                            'store_default_value' => 'value',
                            'value' => $adapter->getCheckSql('tsv.value_id > 0', 'tsv.value',
                                $adapter->getCheckSql('tbv.value_id > 0', 'tbv.value', 'tdv.value'))
                        ));
            } else {
                $this->getSelect()
                    ->joinLeft(
                        array('tsv' => $this->_optionValueTable), // Table Store Value -> tsv
                        $joinCondition,
                        array(
                            'store_default_value' => 'value',
                            'value' => $adapter->getCheckSql('tsv.value_id > 0', 'tsv.value', 'tdv.value')
                        ));
            }
            $this->getSelect()
                ->where('tdv.store_id = ?', 0);
        } else {
            $this->getSelect()
                ->joinLeft(
                    array('tsv' => $this->_optionValueTable),
                    $joinCondition,
                    'value')
                ->where('tsv.store_id = ?', $storeId);
        }

        $this->setOrder('value', self::SORT_ORDER_ASC);
        return $this;
    }

}
