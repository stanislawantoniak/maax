<?php

require Mage::getModuleDir('controllers', 'Zolago_Po' ) . DS . "PoController.php";

class Zolago_Rma_PoController extends Zolago_Po_PoController
{
	
	public function rmaAction() {
		/**
		 * RMA List
		 * @todo Implement
		 */
		$this->_viewAction();
	}

	public function rmaListAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/rma/history');
        }
                                             
        $this->renderLayout();
	}
    public function historyAction()
	{
		/**
		 * @todo Implement
		 */
		$this->_viewAction();
    }    
	
    public function newRmaAction()
    {
        if (!$this->_loadValidPo()) {
            return;
        }
		$session =   Mage::getSingleton('core/session');
		/* @var $session Mage_Core_Model_Session */
		
		// Rma data
		$rma = Mage::getModel("zolagorma/rma");
		/* @var $rma Zolago_Rma_Model_Rma */
		if($data=$session->getRma(true)){
			$rma->setData($data);
		}
		Mage::register("current_rma", $rma);
		
		
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/rma/history');
        }
        $this->renderLayout();
    }

    public function saveRmaAction()
    {
		$request = $this->getRequest();
		
        try {
			/**
			 * @todo Add processing carrier and insert tracking
			 */
			$this->_saveRma();
            Mage::getSingleton('core/session')->addSuccess(
                Mage::getStoreConfig('urma/message/customer_success')
            );
            $this->_redirect('*/*/view', array('po_id'=>$this->getRequest()->getParam('po_id')));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->
					addError($e->getMessage())->
					setData("rma", $request->getParam('rma'));
			
            $this->_redirect('*/*/newrma', array('po_id'=>$this->getRequest()->getParam('po_id')));
        }
    }

    protected function _initRma($forSave=false)
    {
        $rma = false;
        $rmaId = $this->getRequest()->getParam('rma_id');
        $poId = $this->getRequest()->getParam('po_id');
        if ($rmaId) {
            $rma = Mage::getModel('urma/rma')->load($rmaId);
        } elseif ($poId) {
            $po = Mage::getModel('zolagopo/po')->load($poId);

            if (!$po->getId()) {
                Mage::throwException($this->__('The order no longer exists.'));
            }
            $data = $this->getRequest()->getParam('rma');
            if (!isset($data['items_single'])) {
                Mage::throwException($this->__('No items.'));
            } 
            $rma = Mage::getModel('zolagorma/servicePo', $po)->prepareRmaForSave($data);
        }
        Mage::register('current_rma', $rma);
        return $rma;
	}

	protected function _saveRma()
    {
		
        $rmas = $this->_initRma(true);
        $data = $this->getRequest()->getPost('rma');
        $data['send_email'] = true;
        $comment = '';
        if (empty($rmas)) {
            Mage::throwException('Return could not be created');
        }

        foreach ($rmas as $rma) {
			/* @var $rma Zolago_Rma_Model_Rma */
            $po = $rma->getPo();
			/* @var $po Zolago_Po_Model_Po */
            $rma->register();
        }

        if (!empty($data['comment_text'])) {
            foreach ($rmas as $rma) {
                $rma->addComment($data['comment_text'], true, true);
                $rma->setCommentText($data['comment_text']);
            }
            $comment = $data['comment_text'];
        }
        if (!empty($data['send_email'])) {
            foreach ($rmas as $rma) {
                $rma->setEmailSent(true);
            }
        }
        $rma->setRmaReason(@$data['rma_reason']);
        $po->setCustomerNoteNotify(!empty($data['send_email']));
        $po->setIsInProcess(true);
        $trans = Mage::getModel('core/resource_transaction');
        foreach ($rmas as $rma) {
            $rma->setIsCutomer(true);
            $trans->addObject($rma);
			
        }
        $trans->addObject($rma->getPo())->save();
		
        foreach ($rmas as $rma) {
            $rma->sendEmail(!empty($data['send_email']), $comment);
            Mage::helper('urma')->sendNewRmaNotificationEmail($rma, $comment);
        }
        Mage::helper('udropship')->processQueue();
    }
}