<?php

class Zolago_Catalog_Vendor_MassController 
	extends Zolago_Dropship_Controller_Vendor_Abstract {
	/**
	 * Index
	 */
	public function indexAction() {
		Mage::register('as_frontend', true);// Tell block class to use regular URL's
		
		$this->_setTheme();
		
		$this->getLayout()->getUpdate()->
				addHandle('default')->
				addHandle('formkey')->
				addHandle('adminhtml_head');

        // add default layout handles for this action
        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();

        $this->generateLayoutXml();

        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        if ( $root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('udropship-vendor');
        }
        if ($head = $this->getLayout()->getBlock('header')) {
            $head->setActivePage("zolagoccatalog");
        }
		
        $this->_initLayoutMessages('udropship/session');
        if (is_array($this->_extraMessageStorages) && !empty($this->_extraMessageStorages)) {
            foreach ($this->_extraMessageStorages as $ilm) {
                $this->_initLayoutMessages($ilm);
            }
        }
		
        $this->renderLayout();
	}
	
}


