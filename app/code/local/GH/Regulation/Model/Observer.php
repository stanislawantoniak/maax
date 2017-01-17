<?php
class Gh_Regulation_Model_Observer
{
	/**
	 * Add tab to vendor view
	 *
	 * @param $observer Varien_Event_Observer
	 * @return Gh_Regulation_Model_Observer
	 */
	public function udropship_adminhtml_vendor_tabs_after($observer)
	{
		if (!Mage::helper("core")->isModuleEnabled('ZolagoOs_OutsideStore')) {
			$block = $observer->getBlock();
			if (!$block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs
				|| !Mage::app()->getRequest()->getParam('id', 0)
			) {
				return;
			}

			if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
				
				$list = Mage::getResourceModel('ghregulation/regulation_kind_collection');
				if (!count($list)) {
					$content = Mage::helper('ghregulation')->__('No kinds');
				} else {
					$content = Mage::app()->getLayout()->createBlock('ghregulation/adminhtml_dropship_settings_type', 'admin.regulation.settings.type')
						->setVendorId(Mage::app()->getRequest()->getParam('id'))
						->toHtml();
				}
				$block->addTab('regulation_type', array(
					'label' => Mage::helper('ghregulation')->__('Regulation document settings'),
					'content' => $content,
				));
				$block->addTab('regulation_accept', array(
					'label' => Mage::helper('ghregulation')->__('Regulation acceptance'),
					'content' => Mage::app()->getLayout()->createBlock('ghregulation/adminhtml_dropship_acceptance', 'admin.regulation.acceptance')
						->setVendorId(Mage::app()->getRequest()->getParam('id'))
						->toHtml()
				));
				$block->addTabToSection('regulation_type', 'regulations', 50);
				$block->addTabToSection('regulation_accept', 'regulations', 55);
			}
		}
	}
	public function udropship_adminhtml_vendor_edit_prepare_form($observer) {
	}
	public function udropship_vendor_save_after($observer) {
	    /*
		$front_controller = Mage::app()->getFrontController();		
		if($front_controller->getRequest()->isPost()){			
			$params = $front_controller->getRequest()->getParams();			
			if(key_exists('vendor_kind', $params)){				
				$vendor_kind = $params['vendor_kind'];
            } else {
                $vendor_kind = array();
            }
    		$vendor = $observer->getVendor();
            $resource = Mage::getSingleton('core/resource');
            $table = $resource->getTableName('ghregulation/regulation_vendor_kind');            
    		$query = sprintf('DELETE FROM %s WHERE vendor_id = "%d"',$table,$vendor->getId());
    		$connection = $resource->getConnection('core_write');
    		$connection->query($query);
    		$tmp = array();
    		if (!empty($vendor_kind)) {
    		    foreach ($vendor_kind as $kind) {
    		        $tmp[] = sprintf('(%d,%d)',$vendor->getId(),$kind);
    		    }
    		}
    		if (count($tmp)) {
    		    $query = sprintf('INSERT INTO %s (vendor_id,regulation_kind_id) VALUES %s',$table,implode(',',$tmp));
    		    $connection->query($query);
    		}
		}
		*/ // nie ustawiamy czy aktywny czy nie
		return $this;

	}
}