<?php
class Zolago_Modago_IndexController extends Mage_Core_Controller_Front_Action{
	public function indexAction() {
		$this->loadLayout();
		/*
		$block = $this->getLayout()->getBlock("zolago_modago_home_popularvendors");
		foreach($block->getVendorColleciton() as $vendor){
			echo $block->getVendorName($vendor) . "<br/>";
			echo $block->getVendorMarkUrl($vendor) . "<br/>";
			echo $block->getVendorBaseUrl($vendor) . "<br/>";
			echo $block->getVendorResizedLogoUrl($vendor) . "<br/>";
			echo $vendor->getShoppingCartWatchworldOne() . "<br/>";
			echo $vendor->getShoppingCartWatchworldTwo() . "<br/>";
		}
		die;
		 */
		
		$this->renderLayout();
	}
}