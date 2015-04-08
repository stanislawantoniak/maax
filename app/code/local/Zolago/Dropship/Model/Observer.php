<?php

class Zolago_Dropship_Model_Observer extends Unirgy_Dropship_Model_Observer {

    /**
     * Clear afetr laod quote better performance
     * @todo possible bugs not compatibile with unirgy?
     * @param type $observer
     * @return Zolago_Dropship_Model_Observer
     */
    public function sales_quote_load_after($observer) {
        if(Mage::helper("zolagocommon")->isUserDataRequest()) {
            return;
        }
        parent::sales_quote_load_after($observer);
    }

    /**
     * @param type $observer
     * @return \Zolago_Dropship_Model_Observer
     */
    public function bindLocale($observer)
    {
        if(!Mage::registry("dropship_switch_lang")) {
            return;
        }

        // Handle locale
        $session = Mage::getSingleton('udropship/session');

        if ($locale=$observer->getEvent()->getLocale()) {
            if ($choosedLocale = $session->getLocale()) {
                $locale->setLocaleCode($choosedLocale);
            }
        }
        return $this;
    }

    /**
     * Add Data to Save Action
     *
     * @param $observer Varien_Event_Observer
     *
     * @return Zolago_Dropship_Model_Observer
     */
    public function addOrbaShippingData(Varien_Event_Observer $observer)
    {
        $time = Mage::getSingleton('core/date');
        $track = $observer->getEvent()->getTrack();
        $carrierCode = $track->getCarrierCode();

        if (in_array($carrierCode,array(Orba_Shipping_Model_Carrier_Dhl::CODE,Orba_Shipping_Model_Carrier_Ups::CODE))
                && Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode)->isTrackingAvailable()
                && !$track->getWebApi()) {
            $track->setNextCheck(date('Y-m-d H:i:s', $time));
            $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
        }

        return $this;
    }

    /**
     * organize vendor tabs
     * @param $observer Varien_Event_Observer
     */
    public function udropship_adminhtml_vendor_tabs_organize($observer) {
        $block = $observer->getEvent()->getBlock();
        $id = $observer->getEvent()->getId();
        $v = Mage::helper('udropship')->getVendor($id);

        if (!$block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs
                || !Mage::app()->getRequest()->getParam('id', 0)
           ) {
            return;
        }
        if ($block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs) {
            $addressBlock = Mage::app()
                            ->getLayout()
                            ->createBlock('zolagodropship/adminhtml_vendor_edit_tab_addresses', 'vendor.address.form')
                            ->setVendorId($v)
                            ->toHtml();

            $block->addTab('address_section', array(
                               'label'     => Mage::helper('zolagodropship')->__('Address'),
                               'after'     => 'form_section',
                               'content'	=> $addressBlock
                           ));
            $courierBlock = Mage::app()
                            ->getLayout()
                            ->createBlock('zolagodropship/adminhtml_vendor_edit_tab_couriers', 'vendor.courier.form')
                            ->setVendorId($v)
                            ->toHtml();

            $block->addTab('courier_section', array(
                               'label'     => Mage::helper('zolagodropship')->__('Couriers'),
                               'after'     => 'form_section',
                               'content'	=> $courierBlock
                           ));
        }
    }
    protected function _overrideConfigData() {
    	$vendor = Mage::registry('vendor_data');
    	$hlp = Mage::helper('udropship');
		
		$children = Mage::getConfig()->getNode('global/udropship/vendor/fields')->children();
		
        foreach ($children as $code=>$node) {
            
			$note = $hlp->__((string)$node->note);
			
			if($code == 'max_shipping_days'){
				
				$maxShippingDays = Mage::getStoreConfig('udropship/vendor/max_shipping_days');
				
				Mage::getConfig()->setNode('global/udropship/vendor/fields/max_shipping_days/note', $note . sprintf(" (Default value is: %u )", $maxShippingDays));

			}
			elseif($code == 'max_shipping_time'){
				
				$maxShippingTime = Mage::getStoreConfig('udropship/vendor/max_shipping_time');
				
				Mage::getConfig()->setNode('global/udropship/vendor/fields/max_shipping_time/note', $note . sprintf(" (Default value is: %s)", str_replace(',', ':', $maxShippingTime)));
				
			}
		}

    }
    protected function _addFieldsToFieldset($keys,$fieldset) {
        $helper = Mage::helper('zolagodropship/tabs');        
        foreach ($keys as $key) {
            $helper->addKeyToFieldset($key,$fieldset);
        }
    }
    public function udropship_adminhtml_vendor_edit_prepare_form($observer) {
        $block = $observer->getEvent()->getBlock();
        $form = $block->getForm();
        $fieldset = $form->getElement('vendor_form');
        
        $this->_overrideConfigData();
        
        $keys = array (
            'new_order_notifications',
            'notify_by_udpo_status',
            'vendor_type',
            'max_shipping_days',
            'max_shipping_time',
            'external_id',
            'super_vendor_id',
            'can_ask',
            'review_status',
            'label_store',
            'root_category',
            'custom_design',
        );        
        $this->_addFieldsToFieldset($keys,$fieldset);
        // marketing
        $fieldset = $form->addFieldset('marketing', array(
                    'legend'=>Mage::helper('zolagodropship')->__('Marketing content')
                            ));
        $keys = array (
            'vendor_type_label',
            'marketing_store_information_title',
            'marketing_store_information',
            'marketing_brand_information_title',
            'marketing_brand_information',
            'terms_seller_information',
            'terms_delivery_information',
            'terms_return_information',
            'store_delivery_headline',
            'store_return_headline',
            'brandshop_info',
            'cart_slogan_one',
            'cart_slogan_two', 
        );        
        $this->_addFieldsToFieldset($keys,$fieldset);

    }
}
