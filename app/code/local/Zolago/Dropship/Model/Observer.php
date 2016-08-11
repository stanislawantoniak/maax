<?php

class Zolago_Dropship_Model_Observer extends ZolagoOs_OmniChannel_Model_Observer {

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
        $time = Mage::getModel('core/date')->timestamp();
        $track = $observer->getEvent()->getTrack();
        $carrierCode = $track->getCarrierCode();
		$allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
        if (in_array($carrierCode,$allowCarriers)
                && Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode)->isTrackingAvailable()
                && !$track->getWebApi()) {
            $track->setNextCheck(date('Y-m-d H:i:s', $time));
            $track->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING);
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

        if (!$block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs)
            return;


        //Addresses
        if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
            $addressBlock = Mage::app()
                ->getLayout()
                ->createBlock('zolagodropship/adminhtml_vendor_edit_tab_addresses', 'vendor.address.form')
                ->setVendorId($v)
                ->toHtml();

            $block->addTab('address_section', array(
                'label' => Mage::helper('zolagodropship')->__('Address'),
                'after' => 'form_section',
                'content' => $addressBlock
            ));
            $block->addTabToSection('address_section', 'vendor_data', 10);
        }

        if (!Mage::app()->getRequest()->getParam('id', 0))
            return;
        if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
            $websitesBlock = Mage::app()
                ->getLayout()
                ->createBlock('zolagodropship/adminhtml_vendor_edit_tab_websites', 'vendor.websites.form')
                ->setVendorId($v)
                ->toHtml();

            $block->addTab('websites_allowed_section', array(
                'label' => Mage::helper('zolagodropship')->__('Websites allowed'),
                'after' => 'form_section',
                'content' => $websitesBlock
            ));
            $block->addTabToSection('websites_allowed_section','vendor_rights',10);
        }
        //Couriers
        if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
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
            $block->addTabToSection('courier_section','logistic',20);

            $block->addTab('brandshop_section', array(
                               'label'     => Mage::helper('zolagodropship')->__('Shops/Brandshops rights'),
                               'class'	   => 'ajax',
                               'after'     => 'form_section',
                               'url'	=> $block->getUrl('zolagoosadmin/adminhtml_vendor/brandshopSettings',array('_current' => true)),
                           ));
            $block->addTabToSection('brandshop_section','vendor_rights',20);



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
		/** @var Zolago_Dropship_Helper_Tabs $helper */
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
            'automatic_strikeout_price_percent',
            'external_id',
            'super_vendor_id',
            'statements_calendar',
            'can_ask',
            'index_by_google',
            'review_status',
            'label_store',
            'root_category',
            'custom_design',
            'sequence',
            'url_key',
            'logo',
	        'legal_entity'
        );
        $this->_addFieldsToFieldset($keys,$fieldset);

        $fieldset->removeField('carrier_code');
        $fieldset->removeField('use_rates_fallback');
        $fieldset->removeField('email_template');
        $fieldset->removeField('vacation_mode');
        $fieldset->removeField('vacation_end');

        // marketing
        $fieldset = $form->addFieldset('marketing', array(
                    'legend'=>Mage::helper('zolagodropship')->__('Marketing content')
                            ));
        $keys = array (
            'vendor_type_label',
            'vendor_landing_page_title',
            'vendor_landing_page_description',
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
            'cart_slogan_two'
        );
        $this->_addFieldsToFieldset($keys,$fieldset);

    }

    public function addUndeliveredTrackData(Varien_Event_Observer $observer) {
        //rma_rma_track_save_before && sales_order_shipment_track_save_before
        $track = $observer->getEvent()->getTrack();
        if( $track->getId() &&
            $track->getData('udropship_status') == Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED &&
            $track->getOrigData('udropship_status') != Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED &&
            $track->getData('track_type') != GH_Statements_Model_Track::TRACK_TYPE_UNDELIVERED) {

            $number = $track->getData('track_number').Zolago_Dropship_Model_Source::TRACK_UNDELIVERED_SUFFIX;
            $newTrack = clone($track);
            $newTrack
                ->setId(null)
                ->setWebApi(true)
                ->setTrackNumber($number)
                ->setTrackType(GH_Statements_Model_Track::TRACK_TYPE_UNDELIVERED)
                ->save();
        }
    }
}