<?php

/**
 * Class Zolago_Eav_Model_Resource_Entity_Attribute_Collection
 */
class Zolago_Eav_Model_Resource_Entity_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection {

    /**
     * Add store label to attribute by specified store id
     *
     * @param integer $storeId
     * @return $this
     */
    public function addStoreLabel($storeId) {
        $adapter = $this->getConnection();

        $joinExpressionTBS = $adapter
            ->quoteInto('albs.attribute_id = main_table.attribute_id AND albs.store_id = ?', (int)Mage::app()->getStore((int)$storeId)->getAttributeBaseStore());
        $this->getSelect()->joinLeft(
            array('albs' => $this->getTable('eav/attribute_label')),
            $joinExpressionTBS,
            array(/*'store_label_bs' => 'value'*/)
        );

        $joinExpression = $adapter
            ->quoteInto('al.attribute_id = main_table.attribute_id AND al.store_id = ?', (int)$storeId);
        $this->getSelect()->joinLeft(
            array('al' => $this->getTable('eav/attribute_label')),
            $joinExpression,
            array(/*'store_label_al' => 'value'*/)
        );

        $this->getSelect()->columns(array('store_label' =>
            $adapter->getCheckSql('ISNULL(al.value)',
                $adapter->getCheckSql('ISNULL(albs.value)',
                    'main_table.frontend_label',
                    'albs.value'),
                'al.value'
            )));

        return $this;
    }
}
