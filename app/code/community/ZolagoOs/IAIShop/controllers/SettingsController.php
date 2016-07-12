<?php

/**
 * @method ZolagoOS_IAIShop_Model_Session _getSession()
 */
class ZolagoOS_IAIShop_SettingsController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagoosiaishop');
    }

}
