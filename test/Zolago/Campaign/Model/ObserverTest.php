<?php

class Zolago_Campaign_Model_ObserverTest extends Zolago_TestCase
{

    public function testProductsAttributes()
    {
        Zolago_Campaign_Model_Observer::setProductAttributes();
        Zolago_Campaign_Model_Observer::unsetCampaignAttributes();
    }


        public function testCampaignProducts(){


        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect(array('skuv'));
        $collection
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection->addFieldToFilter('udropship_vendor',5);
        $collection->setPageSize(1000);

        $skuV = array();
        foreach($collection as $collectionI){
            $skuV[] = $collectionI->getData('skuv');
        }
        echo implode(',',$skuV);
    }
}