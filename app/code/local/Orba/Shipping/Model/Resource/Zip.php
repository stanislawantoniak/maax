<?php

/**
 * Class Orba_Shipping_Model_Zip
 *
 * @category    Orba
 * @package     Orba_Shipping
 *
 */
class Orba_Shipping_Model_Resource_Zip extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init table
     */
    protected function _construct()
    {
        $this->_init('orbashipping/zip', 'id');
    }

    /**
     *
     * @param $zip
     *
     * @return Orba_Shipping_Model_Resource_Zip
     * @throws Exception
     */
    public function updateDhlZip($country,$zip)
    {

        $writeConnection = $this->_getWriteAdapter();
        $insert = sprintf(
            "INSERT INTO %s (zip,country) VALUES ('%s','%s') "
            . " ON DUPLICATE KEY UPDATE zip=VALUES(zip),country=VALUES(country)",
            $this->getMainTable(), $zip, $country
        );

        try {
            $writeConnection->query($insert);
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }
}