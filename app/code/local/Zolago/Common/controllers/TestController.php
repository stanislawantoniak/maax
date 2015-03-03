<?php
class Zolago_Common_TestController extends Mage_Core_Controller_Front_Action{
	
	public function pricetypeAction() {
		$queue = Mage::getSingleton("zolagocatalog/queue_pricetype");
		/* @var $queue Zolago_Catalog_Model_Queue_Pricetype */
		$queue->process();
		
		$queue2 = Mage::getSingleton("zolagocatalog/queue_configurable");
		/* @var $queue2 Zolago_Catalog_Model_Queue_Configurable */
		$queue2->process();
	}
	
	public function trackingsAction() {
		$model = Mage::getModel('udropship/observer');
		$model->cronCollectTracking();
        $helper = Mage::helper('zolagorma');
        $helper->rmaTracking();
		echo "TAK";
	}
	public function testAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function triggerCampaignAction() {
		Mage::getSingleton('zolagocampaign/observer')->setProductAttributes();
		Mage::getSingleton('zolagocampaign/observer')->processCampaignAttributes();
	}
}