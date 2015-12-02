<?php

class Zolago_Modago_Block_Map extends Mage_Core_Block_Template
{

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @param $vendorId
     * @param string $filterValue
     * @return Zolago_Pos_Model_Resource_Pos_Collection
     */
    public function getPosMapCollection($vendorId, $filterValue = 0)
    {
        if (!$this->hasData("pos_map_collection")) {

            $collection = Mage::getResourceModel('zolagopos/pos_collection');
            /* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
            $collection->addActiveFilter();
            $collection->addShowOnMapFilter();
            $collection->addVendorFilter($vendorId);

            $collection->setOrder("map_name", "ASC");


            if (!empty($filterValue)) {
                $collection->addFieldToFilter("main_table.pos_id",$filterValue);
            }

            $this->setData("pos_map_collection", $collection);
        }
        return $this->getData("pos_map_collection");
    }

    /**
     * @param string $filterValue
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getMapData($filterValue = 0)
    {
        $result = "";
        $maps = array();
        $website = Mage::app()->getWebsite();
        if ($website->getHaveSpecificDomain()) {
            $vendorId = $website->getVendorId();

            if ($vendorId) {
                $posMaps = $this->getPosMapCollection($vendorId, $filterValue);

                if($posMaps->count()){
                    $maps["poses"] = array();
                    foreach ($posMaps as $posMap) {
                        /* @var $posMap Zolago_Pos_Model_Pos */
                        $maps["poses"][] = array(
                            "id" => $posMap->getId(),
                            "name" => $posMap->getMapName(),
                            "city" => $posMap->getCity(),
                            "street" => $posMap->getStreet(),
                            "postcode" => $posMap->getPostcode(),
                            "latitude" => $posMap->getMapLatitude(),
                            "longitude" => $posMap->getMapLongitude(),
                            "phone" => $posMap->getMapPhone(),
                            "time_opened" => $this->clearNewLines($posMap->getMapTimeOpened())
                        );
                    }
                    $result = json_encode($maps, JSON_HEX_APOS);
                }
            }
        }

        return $result;
    }


    /**
     * Clear text fields (another way it will break markers on the map)
     * @param $description
     * @return mixed|string
     */
    public function clearNewLines($description)
    {
        $description = preg_replace('/\r?\n|\r/', '<br/>', $description);
        $description = str_replace(array("\r\n", "\r", "\n"), "<br/>", $description);
        $description = nl2br($description);
        return $description;
    }


} 