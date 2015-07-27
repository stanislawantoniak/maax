<?php

/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Campaign_PlacementController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    public function indexAction()
    {
        Mage::register('as_frontend', true);
        $this->_renderPage(null, 'zolagocampaign_placement');
    }

}
