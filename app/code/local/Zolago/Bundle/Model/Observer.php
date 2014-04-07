<?php
class Zolago_Bundle_Model_Observer extends Mage_Bundle_Model_Observer
{
   
    /**
     * Add price index data for catalog product collection
     * only for front end
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function loadProductOptions($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
		
		if(!$collection->getFlag("skip_price_data")){
			$collection->addPriceData();
		}

        return $this;
    }
}
