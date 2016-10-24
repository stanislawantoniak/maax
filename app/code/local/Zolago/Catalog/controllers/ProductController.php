<?php
require_once Mage::getConfig()->getModuleDir('controllers', "Mage_Catalog") . DS . "ProductController.php";
class Zolago_Catalog_ProductController extends Mage_Catalog_ProductController
{
    
	/**
     * Product view action
     */
    public function viewAction()
    {
        return parent::viewAction();
    }
}
