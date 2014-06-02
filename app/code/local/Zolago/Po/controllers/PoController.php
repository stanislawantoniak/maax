<?php

class Zolago_Po_PoController extends Mage_Core_Controller_Front_Action
{
	public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }
	
	public function viewAction() {
		$this->_viewAction();
	}
    
	
    /**
     * Init layout, messages and set active block for customer
     *
     * @return null
     */
    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/po/history');
        }
        $this->renderLayout();
    }

	/**
	 * Valid Can view PO
	 * @param Zolago_Po_Model_Po $po
	 * @return boolean
	 */
	protected function _canViewPo(Zolago_Po_Model_Po $po) 
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($po->getId() && $po->getCustomerId() && ($po->getCustomerId() == $customerId)){
            return true;
        }
        return false;
    }
	
    /**
     * Try to load valid po by order_id and register it
     *
     * @param int $poId
     * @return bool
     */
    protected function _loadValidOrder($poId = null)
    {
        if (null === $poId) {
            $poId = (int) $this->getRequest()->getParam('po_id');
        }
        if (!$poId) {
            $this->_forward('noRoute');
            return false;
        }

        $po = Mage::getModel('zolagopo/po')->load($poId);

        if ($this->_canViewPo($po)) {
            Mage::register('current_po', $po);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }
}