<?php
/**
  
 */

class ZolagoOs_OmniChannel_Adminhtml_BatchController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_batch'));
        $this->renderLayout();
    }


    public function batchLabelsAction()
    {
        $id = $this->getRequest()->getParam('batch_id');
        $batch = Mage::getModel('udropship/label_batch')->load($id);
        $batch->prepareLabelsDownloadResponse();
    }
}