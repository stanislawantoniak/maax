<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Shop
 */
class GetresponseIntegration_Getresponse_Model_Shop extends Mage_Core_Model_Abstract
{
    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('getresponse/shop');
    }

    /**
     * @param array $data
     * @param string $shop_id
     *
     * @return bool
     */
    public function update($data, $shop_id)
    {
        /** @var GetresponseIntegration_Getresponse_Model_Shop $model */
        $model = $this->load($shop_id)->addData($data);

        try {
            $model->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}