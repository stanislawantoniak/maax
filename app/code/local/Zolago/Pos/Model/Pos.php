<?php

/**
 * Class Zolago_Pos_Model_Pos
 *
 * @method string getName()
 * @method string getCity()
 * @method string getStreet()
 * @method string getPostcode()
 * @method string getExternalId()
 * @method int getShowOnMap()
 * @method string getMapName()
 * @method string getMapLatitude()
 * @method string getMapLongitude()
 * @method string getMapPhone()
 * @method string getMapTimeOpened()
 *
 *
 * @method int getIsAvailableAsPickupPoint()
 *
 *
 */
class Zolago_Pos_Model_Pos extends Mage_Core_Model_Abstract
{

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected function _construct()
    {
        $this->_init('zolagopos/pos');
    }


    /**
     * @param ZolagoOs_OmniChannel_Model_Vendor|int $vendor
     * @return boolean
     */
    public function isAssignedToVendor($vendor)
    {
        if ($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor) {
            $vendor = $vendor->getId();
        }
        return $this->getResource()->isAssignedToVendor($this, $vendor);
    }


    /**
     * @return ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection
     */
    public function getVendorCollection()
    {
        $collection = Mage::getResourceModel('udropship/vendor_collection');
        $this->getResource()->addPosToVendorCollection($collection);
        if ($this->getId()) {
            $collection->addFieldToFilter("pos_id", $this->getId());
        } else {
            $collection->addFieldToFilter("pos_id", -1);
        }
        return $collection;
    }

    /**
     * @param array $data
     * @return array
     */
    public function validate($data = null)
    {

        if ($data === null) {
            $data = $this->getData();
        } elseif ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if (!is_array($data)) {
            return false;
        }

        $errors = $this->getValidator()->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;

    }

    public function getRegionText()
    {
        if ($this->getRegionId()) {
            return Mage::getModel("directory/region")->load($this->getRegionId())->getName();
        }
        return $this->getRegion();
    }

    /**
     * @return Zolago_Pos_Model_Pos_Validator
     */
    public function getValidator()
    {
        return Mage::getSingleton("zolagopos/pos_validator");
    }

    /**
     * @return Zolago_Pos_Model_Pos
     */
    public function setDefaults()
    {
        $this->setIsActive(1);
        $this->setMinimalStock(1);
        $this->setCountryId("PL");
        $this->setPriority(1);
        return $this;
    }

    /**
     *
     * @param
     * @return
     */
    public function getSenderAddress()
    {
        $data = $this->getData();
        $address = array(
            'name' => $data['company'],
            'postcode' => $data['postcode'],
            'city' => $data['city'],
            'country' => $data['country_id'],
            'street' => $data['street'],
            'phone' => $data['phone'],
        );
        return $address;
    }
    
    /**
     * availability hours
     * @return string
     */
    public function getAvailabilityHours() {
        if (!$hours = $this->getMapTimeOpened()) {
            $hours = Mage::getStoreConfig('carriers/ghinpost/open_hours');
        }
        return $hours;
    }

    /**
     * Retrieve shipping address info
     *
     * @return array
     */
    public function getShippingAddress()
    {
        $data['street'][] = $this->getStreet();
        $data['postcode'] = $this->getPostcode();
        $data['city'] = $this->getCity();
        $data['country_id'] = $this->getCountryId();
        $data['region_id'] = $this->getRegionText();
        $data['region'] = $this->getRegionText();
        return $data;
    }

    
    /**
     * formatted address as html
     */
     public function getShippingAddressHtml() {
         $out = 
             $this->getName().'<br/>'.
             $this->getStreet().'<br/>'.
             $this->getPostcode().' '.$this->getCity().'<br/>';
         return $out;
     }
    /**
     *
     * @return string
     */
    public function getCountryId()
    {
        return 'PL';
    }
}

