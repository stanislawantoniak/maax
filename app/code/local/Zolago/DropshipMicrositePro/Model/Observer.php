<?php

class Zolago_DropshipMicrositePro_Model_Observer
{
    public function handleCatalogLayoutRender($observer)
    {
         if ($_vendor = Mage::helper('umicrosite')->getCurrentVendor()) {
			// Do sth with vendor mode
		 }
    }
}