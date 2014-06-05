<?php

class Zolago_Po_Block_View extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagopo/view.phtml');
    }

	protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
    }
	
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
        return Mage::registry('current_po');
	}
	
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {		
		return $this->getPo()->getOrder();
    }
	
	/**
	 * @return Mage_Core_Block_Abstract
	 */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }
	
    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getUrl('*/*/history');
        }
        return Mage::getUrl('*/*/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return string
     */
    public function getBackTitle()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::helper('sales')->__('Back to My Orders');
        }
        return Mage::helper('sales')->__('View Another Order');
    }

    public function getInvoiceUrl($po)
    {
        return Mage::getUrl('*/*/invoice', array('po_id' => $po->getId()));
    }

    public function getShipmentUrl($po)
    {
        return Mage::getUrl('*/*/shipment', array('po_id' => $po->getId()));
    }

    public function getCreditmemoUrl($po)
    {
        return Mage::getUrl('*/*/creditmemo', array('po_id' => $po->getId()));
    }

}
