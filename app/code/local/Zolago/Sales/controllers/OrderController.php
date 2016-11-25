<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'ZolagoOs_Rma') .
		DS . "OrderController.php";

class Zolago_Sales_OrderController extends ZolagoOs_Rma_OrderController
{
    /**
     * Customer order history (overwrite title)
     */
    public function historyAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session'));
        $this->getLayout()->getBlock('head')->setTitle($this->__('Orders history'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }
	
	/**
	 * Fix add udqa sessions
	 * @return void
	 */
	protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
		// Fix add udqa sessions
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session'));

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }
    
    /**
     * Opened orders
     */
    public function processAction() {
        $this->loadLayout();
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session'));
        $this->getLayout()->getBlock('head')->setTitle($this->__('Orders in the realization'));
        $this->renderLayout();
    }

	public function attachAction() {
		/** @var Mage_Customer_Model_Session $session */
		$session = Mage::getSingleton("customer/session");
		$langHelper = Mage::helper("zolagosales");
		$token = $this->getRequest()->getParam('token');
		if(!is_null($token) && strlen($token) == 64) {
			/** @var Zolago_Sales_Helper_Attach $helper */
			$helper = Mage::helper("zolagosales/attach");
			$result = $helper->attachOrders($token);
			if($result === true) {
				$session->addSuccess($langHelper->__("Your orders has been successfully attached to account."));
			} else {
				$session->addError($result);
			}
		} elseif($session->getCustomer()->getId()) {
			/** @var Zolago_Customer_Model_Attachtoken $model */
			$model = Mage::getModel("zolagocustomer/attachtoken");
			$model->setData(array(
				"customer_id" => $session->getCustomer()->getId(),
				"token" => Mage::helper("zolagocustomer")->generateToken(),
			));
			$model->save();

			if(!$model->sendMessage()){
				$session->addError($langHelper->__("There was an error while sending your confirmation email!"));
			} else {
				$session->addSuccess($langHelper->__(
					"Please confirm merging your orders by clicking link in email that we have just sent to you. ".
					"Confirmation link will expire after 24 hours."
				));
			}
		}
		$this->_redirect("sales/order/process");
	}
}