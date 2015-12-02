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
    public function getPosMapCollection($vendorId, $filterValue = "")
    {
        if (!$this->hasData("pos_map_collection")) {

            $collection = Mage::getResourceModel('zolagopos/pos_collection');
            /* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
            $collection->addActiveFilter();
            $collection->addShowOnMapFilter();
            $collection->addVendorFilter($vendorId);
            $collection->setOrder("map_name", "ASC");

            if (!empty($filterValue)) {
                $collection
                    ->getSelect()
                    ->where('(postcode=?', $filterValue)
                    ->orWhere("map_name LIKE  ?)", '%' . $filterValue . '%');

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
    public function getMapData($filterValue = "")
    {
        Mage::log($filterValue, null, "map.log");
        $result = "";
        $maps = array();
        $website = Mage::app()->getWebsite();
        if ($website->getHaveSpecificDomain()) {
            $vendorId = $website->getVendorId();

            if ($vendorId) {
                $posMaps = $this->getPosMapCollection($vendorId,$filterValue);
                Mage::log($posMaps->getData(), null, "map.log");
                if($posMaps->count()){

                    foreach ($posMaps as $posMap) {
                        /* @var $posMap Zolago_Pos_Model_Pos */
                        $maps[] = array(
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