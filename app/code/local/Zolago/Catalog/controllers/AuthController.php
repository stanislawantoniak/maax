<?php

class Zolago_Catalog_AuthController extends Mage_Core_Controller_Front_Action
{

    public function configurableAction()
    {
        Zolago_Catalog_Model_Observer::processConfigurableQueue();
        echo 'Done';
    }

    public function configurableClearAction()
    {
        Zolago_Catalog_Model_Observer::clearConfigurableQueue();
    }

}



