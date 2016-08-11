<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Observer
{
    public function core_block_abstract_to_html_before($observer)
    {
        $block = $observer->getBlock();
        if (!$block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }

        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs) {
            if (Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review')) {
                $block->addTab('udratings', array(
                    'label'     => Mage::helper('udratings')->__('Vendor Reviews'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('zosratingsadmin/review/customerReviews', array('_current' => true)),
                    'after'     => 'reviews'
                ));
            }
        }
    }
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getBlock();
        if (!$block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }

        if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
            if (Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review')) {
                $block->addTab('udratings', array(
                    'label'     => Mage::helper('udratings')->__('Customer Reviews'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('zosratingsadmin/review/vendorReviews', array('_current' => true)),
                    'after'     => 'products_section'
                ));
            }
        }
    }

    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        $id = $observer->getEvent()->getId();
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('vendor_form');
        $fieldset->addField('allow_udratings', 'select', array(
            'name'      => 'allow_udratings',
            'label'     => Mage::helper('udratings')->__('Allow customers review/rate vendor'),
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
        ));
    }

    public function udropship_shipment_status_save_after($observer)
    {
        $this->_sales_order_shipment_save_before($observer, true);
    }
    public function sales_order_shipment_save_before($observer)
    {
        $this->_sales_order_shipment_save_before($observer, false);
    }
    protected function _sales_order_shipment_save_before($observer, $isStatusEvent)
    {
        $po = $observer->getEvent()->getShipment();
        if ($po->getUdropshipVendor()
            && ($vendor = Mage::helper('udropship')->getVendor($po->getUdropshipVendor()))
            && $vendor->getId()
            && (!$po->getUdratingDate() || $po->getUdratingDate() == '0000-00-00 00:00:00')
        ) {
            $readyStatuses = Mage::getStoreConfig('udropship/vendor_rating/ready_status');
            if (!is_array($readyStatuses)) {
                $readyStatuses = explode(',', $readyStatuses);
            }
            if (in_array($po->getUdropshipStatus(), $readyStatuses)) {
                $po->setUdratingDate(now());
                if ($isStatusEvent) {
                    $po->getResource()->saveAttribute($po, 'udrating_date');
                }
            }
        }
    }

    public function cronSendPendingShipmentsEmails()
    {
        $daysFilter = Mage::getStoreConfig('udropship/vendor_rating/notify_in_days');
        $pendingShipments = Mage::getResourceModel('udratings/review_shipment_collection')
            ->addNotificationDaysFilter($daysFilter)
            ->addPendingFilter();
        $customerIds = $pendingShipments->getCustomerIds();
        foreach ($customerIds as $customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getId()) {
                Mage::helper('udratings')->sendPendingReviewEmail($customer);
            }
        }
    }

}