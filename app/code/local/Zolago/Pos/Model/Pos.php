<?php

/**
 * Class Zolago_Pos_Model_Pos
 *
 * @method string getPosId()
 * @method string getVendorOwnerId()
 * @method string getExternalId()
 * @method string getIsActive()
 * @method string getClientNumber()
 * @method string getMinimalStock()
 * @method string getName()
 * @method string getCompany()
 * @method string getCountryId()
 * @method string getRegionId()
 * @method string getRegion()
 * @method string getPostcode()
 * @method string getStreet()
 * @method string getCity()
 * @method string getEmail()
 * @method string getPhone()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getPriority()
 * @method string getUseDhl()
 * @method string getDhlLogin()
 * @method string getDhlPassword()
 * @method string getDhlAccount()
 * @method string getDhlEcas()
 * @method string getDhlTerminal()
 * @method string getUseOrbaups()
 * @method string getOrbaupsLogin()
 * @method string getOrbaupsPassword()
 * @method string getOrbaupsAccount()
 * @method string getShowOnMap()
 * @method string getMapName()
 * @method string getMapLatitude()
 * @method string getMapLongitude()
 * @method string getMapPhone()
 * @method string getMapTimeOpened()
 * @method string getBeaconId()
 * @method string getBeaconName()
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
     * @param Unirgy_Dropship_Model_Vendor|int $vendor
     * @return boolean
     */
    public function isAssignedToVendor($vendor)
    {
        if ($vendor instanceof Unirgy_Dropship_Model_Vendor) {
            $vendor = $vendor->getId();
        }
        return $this->getResource()->isAssignedToVendor($this, $vendor);
    }


    /**
     * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
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

}

