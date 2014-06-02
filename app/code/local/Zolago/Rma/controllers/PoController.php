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


    public function historyAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/po/history');
        }
                                             
        $this->renderLayout();
    }    
    public function newRmaAction()
    {
		/**
		 * @todo Implement
		 */
		$this->_viewAction();
    }

    public function saveRmaAction()
    {
        try {
            $this->_saveRma();
            Mage::getSingleton('core/session')->addSuccess(
                Mage::getStoreConfig('urma/message/customer_success')
            );
            $this->_redirect('*/*/view', array('po_id'=>$this->getRequest()->getParam('po_id')));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirect('*/*/view', array('po_id'=>$this->getRequest()->getParam('po_id')));
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
            $po      = Mage::getModel('zolagopo/po')->load($poId);

            if (!$po->getId()) {
                Mage::throwException($this->__('The order no longer exists.'));
            }

            $data = $this->getRequest()->getParam('rma');
            if (isset($data['items'])) {
                $qtys = $data['items'];
            } else {
                $qtys = array();
            }
            if (isset($data['items_condition'])) {
                $conditions = $data['items_condition'];
            } else {
                $conditions = array();
            }
			
            if ($forSave) {
                $rma = Mage::getModel('zolagorma/servicePo', $po)->prepareRmaForSave($qtys, $conditions);
            } else {
                $rma = Mage::getModel('zolagorma/servicePo', $po)->prepareRma($qtys);
            }

        }

        Mage::register('current_rma', $rma);
        return $rma;
	}

	protected function _initTmpData(){
		
		$data = array(
			"rma" => array(
				"items_condition" => array(
					226 => "unopened",
					228 => "unopened",
					
				),
				"items" => array(
					226 => 3,
					228 => 1,
				),
				"comment_text" => "My test comment",				
				"rma_reason"   => "exchange",
				
			),
			"po_id"		   => 110,
			"order_id"	   => 80
		);
		
		$this->getRequest()->setPost($data);
	}


	protected function _saveRma()
    {
		$this->_initTmpData();
		
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
			
			var_export($rma->getData('udpo_id'));
        }
        $trans->addObject($rma->getPo())->save();
		
        foreach ($rmas as $rma) {
            $rma->sendEmail(!empty($data['send_email']), $comment);
            Mage::helper('urma')->sendNewRmaNotificationEmail($rma, $comment);
        }
        Mage::helper('udropship')->processQueue();
    }
	

}