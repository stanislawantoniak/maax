<?php

/**
 * Class Zolago_Dhl_Model_Zip
 *
 * @category    Zolago
 * @package     Zolago_Dhl
 *
 */
class Zolago_Dhl_Model_Resource_Zip extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init table
     */
    protected function _construct()
    {
        $this->_init('zolagodhl/zip', 'id');
    }

    /**
     *
     * @param $zip
     *
     * @return Zolago_Dhl_Model_Resource_Zip
     * @throws Exception
     */
    public function updateDhlZip($zip)
    {
        $writeConnection = $this->_getWriteAdapter();
        $insert = sprintf(
            "INSERT INTO %s (zip) VALUES ('%s') "
            . " ON DUPLICATE KEY UPDATE zip=VALUES(zip)",
            $this->getMainTable(), $zip
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