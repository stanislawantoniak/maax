<?php

class ZolagoOs_OmniChannelVendorRatings_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_myEt = 10;
    protected $_oldEntityType = 1;
    protected $_entityType = 1;
    public function myEt()
    {
        return $this->_myEt;
    }
    public function useMyEt()
    {
        return $this->useEt($this->_myEt);
    }
    public function useEt($id=null)
    {
        $result = $this->_entityType;
        if (!is_null($id) && $this->_entityType!=$id) {
            $this->_oldEntityType = $this->_entityType;
            $this->_entityType = $id;
        }
        return $result;
    }
    public function resetEt()
    {
        $this->_entityType = $this->_oldEntityType;
        return $this;
    }
    public function getVendorReviewsCollection($vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        return Mage::getModel('review/review')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage::helper('review')->__('Approved'))
            ->addEntityFilter(Mage::helper('udratings')->myEt(), $vendor->getId())
            ->setFlag('AddRateVotes', true)
            ->setFlag('AddAddressData', true);
    }
    public function getCustomerReviewsCollection()
    {
        return Mage::getModel('review/review')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage::helper('review')->__('Approved'))
            ->addFieldToFilter('main_table.entity_id', Mage::helper('udratings')->myEt())
            ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
            ->setDateOrder()
            ->addRateVotes();
    }
    public function getPendingCustomerReviewsCollection()
    {
        $col = Mage::getResourceModel('udratings/review_shipment_collection')
            ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
            ->addPendingFilter();
        return $col;
    }
    public function saveFormData($data=null, $id=null)
    {
        $formData = Mage::getSingleton('udratings/session')->getFormData();
        if (!is_array($formData)) {
            $formData = array();
        }
        $data = !is_null($data) ? $data : Mage::app()->getRequest()->getPost();
        $id = !is_null($id) ? $id : Mage::app()->getRequest()->getParam('rel_id');
        $formData[$id] = $data;
        Mage::getSingleton('udratings/session')->setFormData($formData);
    }

    public function fetchFormData($id=null)
    {
        $formData = Mage::getSingleton('udratings/session')->getFormData();
        if (!is_array($formData)) {
            $formData = array();
        }
        $id = !is_null($id) ? $id : Mage::app()->getRequest()->getParam('rel_id');
        $result = false;
        if (isset($formData[$id]) && is_array($formData[$id])) {
            $result = $formData[$id];
            unset($formData[$id]);
            if (empty($formData)) {
                Mage::getSingleton('udratings/session')->getFormData(true);
            } else {
                Mage::getSingleton('udratings/session')->setFormData($formData);
            }
        }
        return $result;
    }
    public function getAggregateRatings()
    {
        return Mage::helper('udratings')->getAggregateRatings();
    }
    public function getNonAggregateRatings()
    {
        return Mage::helper('udratings')->getNonAggregateRatings();
    }
    public function getReviewsSummaryHtml($vendor, $templateType = false, $displayIfNoReviews = false)
    {
        $this->_initReviewsHelperBlock();
        return $this->_reviewsHelperBlock->getSummaryHtml($vendor, $templateType, $displayIfNoReviews);
    }
    public function addReviewSummaryTemplate($type, $template)
    {
        $this->_initReviewsHelperBlock();
        $this->_reviewsHelperBlock->addTemplate($type, $template);
    }
    protected $_reviewsHelperBlock;
    protected function _initReviewsHelperBlock()
    {
        if (!$this->_reviewsHelperBlock) {
            $this->_reviewsHelperBlock = Mage::app()->getLayout()->createBlock('udratings/vendor');
        }
    }

    public function sendPendingReviewEmail($customer)
    {
        $store = Mage::app()->getDefaultStoreView();
        Mage::helper('udropship')->setDesignStore($store);
        $shipments = Mage::getResourceModel('udratings/review_shipment_collection')
            ->addCustomerFilter($customer)
            ->addPendingFilter();
        $tpl = Mage::getModel('core/email_template');
        $tpl->sendTransactional(
            $store->getConfig('udropship/vendor_rating/customer_email_template'),
            $store->getConfig('sales_email/shipment/identity'),
            $customer->getEmail(),
            $customer->getName(),
            array(
                'store' => $store,
                'store_name' => $store->getName(),
                'customer' => $customer,
                'shipments' => $shipments
            )
        );
        if ($tpl->getSentSuccess()) {
            foreach ($shipments as $shipment) {
                $shipment->setData('udrating_emails_sent', $shipment->getData('udrating_emails_sent')+1);
                $shipment->getResource()->saveAttribute($shipment, 'udrating_emails_sent');
            }
        }
        Mage::helper('udropship')->setDesignStore();
    }

    public function getCaseSql($valueName, $casesResults, $defaultValue = null)
    {
        $expression = 'CASE ' . $valueName;
        foreach ($casesResults as $case => $result) {
            $expression .= ' WHEN ' . $case . ' THEN ' . $result;
        }
        if ($defaultValue !== null) {
            $expression .= ' ELSE ' . $defaultValue;
        }
        $expression .= ' END';

        return new Zend_Db_Expr($expression);
    }

}
