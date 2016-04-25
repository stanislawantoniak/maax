<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_Review_Product_Collection extends Mage_Review_Model_Mysql4_Review_Product_Collection
{
    protected function _joinFields()
    {
        $reviewTable = Mage::getSingleton('core/resource')->getTableName('review/review');
        $reviewDetailTable = Mage::getSingleton('core/resource')->getTableName('review/review_detail');

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('sku');

        $this->getSelect()
            ->join(array('rt' => $reviewTable),
                'rt.entity_pk_value = e.entity_id and rt.entity_id=1',
                array('review_id', 'created_at', 'entity_pk_value', 'status_id'))
            ->join(array('rdt' => $reviewDetailTable), 'rdt.review_id = rt.review_id');
        return $this;
    }
}