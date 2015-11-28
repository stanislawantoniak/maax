<?php

class Zolago_Modago_Block_Map extends Mage_Core_Block_Template
{
    /**
     * @return Zolago_Pos_Model_Resource_Pos_Collection
     */
    public function getPosCollection()
    {
        if (!$this->hasData("pos_map_collection")) {

            $collection = Mage::getResourceModel('zolagopos/pos_collection');
            /* @var $collection Unirgy_Dropship_Model_Mysql4_Vendor_Collection */
            $collection->мaddActiveFilter();
            $collection->addFieldToFilter('vendor_id', 10);


            $this->setData("pos_map_collection", $collection);
        }
        return $this->getData("pos_map_collection");
    }


} 