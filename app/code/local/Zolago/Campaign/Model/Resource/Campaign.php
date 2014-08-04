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
        // Products Assignment
        if ($object->hasData("campaign_products")) {
            $productsStr = $object->getData("campaign_products");
            $products = array();
            if(is_string($productsStr)){
                $products = array_map('trim', explode("," , $productsStr));
            }
            $this->_setProducts($object, $products);
        }
        return parent::_afterSave($object);
    }

    /**
     * @param $campaignId
     * @param $productId
     */
    public function removeProduct($campaignId,$productId){
        $table = $this->getTable("zolagocampaign/campaign_product");

        $where = "campaign_id={$campaignId} AND product_id={$productId}";
        echo $where;
        $this->_getWriteAdapter()->delete($table, $where);
    }
    /**
     * @param Mage_Core_Model_Abstract $object
     * @param array $skuS
     * @return Zolago_Campaign_Model_Resource_Campaign
     */
    protected function _setProducts(Mage_Core_Model_Abstract $object, array $skuS)
    {
        $table = $this->getTable("zolagocampaign/campaign_product");
        $where = $this->getReadConnection()
            ->quoteInto("campaign_id=?", $object->getId());
        $this->_getWriteAdapter()->delete($table, $where);

        $toInsert = array();
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('SKU', array('in' => $skuS))
            ->getAllIds();
        foreach ($collection as $productId) {
            $toInsert[] = array("campaign_id" => $object->getId(), "product_id" => $productId);
        }
        if (count($toInsert)) {
            $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
        }
        return $this;
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
    /**
     * @param Mage_Core_Model_Abstract $object
     * @return array
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

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
    public function getCampaignProducts(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            return array();
        }
        $table = $this->getTable("zolagocampaign/campaign_product");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign_product" => $table), array());
        $select->join(
            array('product' => 'catalog_product_entity'),
            'product.entity_id = campaign_product.product_id',
            array(
                'sku' => 'product.sku'
            )
        );
        $select->where("campaign_product.campaign_id=?", $object->getId());
        return $this->getReadConnection()->fetchCol($select);
    }


    /**
     * @param $productId
     * @return array
     */
    public function getProductCampaign($productId)
    {
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table), array());
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                 'campaign_id' => 'campaign.campaign_id',
                 'campaign_name' => 'campaign.name'
            )
        );
        $select->where("campaign_product.product_id=?", $productId);
        $select->where(
            "campaign.type IN (?)",
            array(Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION, Zolago_Campaign_Model_Campaign_Type::TYPE_SALE)
        );

        return $this->getReadConnection()->fetchAll($select);
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getProductCampaignInfo($productId)
    {
        $table = $this->getTable("zolagocampaign/campaign");
        $select = $this->getReadConnection()->select();
        $select->from(array("campaign" => $table), array());
        $select->join(
            array('campaign_product' => 'zolago_campaign_product'),
            'campaign_product.campaign_id=campaign.campaign_id',
            array(
                 'campaign_id'   => 'campaign.campaign_id',
                 'campaign_name' => 'campaign.name'
            )
        );
        $select->where("campaign_product.product_id=?", $productId);
        $select->where(
            "campaign.type=?", Zolago_Campaign_Model_Campaign_Type::TYPE_INFO
        );

        return $this->getReadConnection()->fetchAll($select);
    }

}

