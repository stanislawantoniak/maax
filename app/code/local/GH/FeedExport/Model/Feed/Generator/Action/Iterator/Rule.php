<?php

/**
 * Class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Rule
 */
class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Rule extends Mirasvit_FeedExport_Model_Feed_Generator_Action_Iterator_Rule
{

    public function getCollection()
    {
        Mage::app()->getStore()->setId(0);
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($this->getFeed()->getStore()->getId())
            ->addAttributeToFilter("status", array("neq" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED))
            ->addAttributeToFilter("visibility", array("neq" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $this->_rule->getConditions()->collectValidatedAttributes($collection);
        return $collection;
    }

}