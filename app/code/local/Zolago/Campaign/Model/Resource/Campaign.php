<?php

class Zolago_Campaign_Model_Resource_Campaign extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocampaign/campaign', "campaign_id");
    }

    /**
     * @param Mage_Core_Model_Abstract $param
     * @return Zolago_Operator_Model_Resource_Operator
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        // Website Assignment
        if ($object->hasData("website_ids")) {
            $this->_setWebsites($object, $object->getData("website_ids"));
        }
        return parent::_afterSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param array $websites
     * @return Zolago_Campaign_Model_Resource_Campaign
     */
    protected function _setWebsites(Mage_Core_Model_Abstract $object, array $websites)
    {
        $table = $this->getTable("zolagocampaign/campaign_website");
        $where = $this->getReadConnection()
            ->quoteInto("campaign_id=?", $object->getId());
        $this->_getWriteAdapter()->delete($table, $where);

        $toInsert = array();
        foreach ($websites as $websiteId) {
            $toInsert[] = array("website_id" => $websiteId, "campaign_id" => $object->getId());
        }
        if (count($toInsert)) {
            $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
        }
        return $this;
    }
    public function setProducts($campaignId, $skuS)
    {
        if (!empty($skuS)) {
            $table = $this->getTable("zolagocampaign/campaign_product");
            $where = $this->getReadConnection()
                ->quoteInto("campaign_id=?", $campaignId);
            $this->_getWriteAdapter()->delete($table, $where);

            $toInsert = array();
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('SKU', array('in' => array('5-9943')))
                ->getAllIds();
            foreach ($collection as $productId) {
                $toInsert[] = array("campaign_id" => $campaignId, "product_id" => $productId);
            }
            if (count($toInsert)) {
                $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
            }
        }

        return $this;
    }
    /**
     * @param Mage_Core_Model_Abstract $object
     * @return type
     */
    public function getAllowedWebsites(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            return array();
        }
        $table = $this->getTable("zolagocampaign/campaign_website");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign_website" => $table), array("website_id"));
        $select->where("campaign_website.campaign_id=?", $object->getId());
        return $this->getReadConnection()->fetchCol($select);
    }
}

