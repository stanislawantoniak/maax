<?php
class Zolago_Common_TestController extends Mage_Core_Controller_Front_Action{
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
}