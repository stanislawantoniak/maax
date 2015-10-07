<?php

/**
 * /**
 * Class GH_Regulation_VendorController
 */
class GH_Regulation_Dropship_VendorController
    extends Zolago_Dropship_Controller_Vendor_Abstract
{
    public function acceptAction()
    {
        $this->_renderPage();
    }

    public function acceptPostAction()
    {
        $params = $this->getRequest()->getPost();
    }
}