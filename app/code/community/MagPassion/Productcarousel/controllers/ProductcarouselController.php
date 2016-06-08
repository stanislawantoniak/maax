<?php 
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Product Carousel front contrller
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_ProductcarouselController extends Mage_Core_Controller_Front_Action{
	/**
 	 * default action
 	 * @access public
 	 * @return void
 	 * @author MagPassion.com
 	 */
 	public function indexAction(){
		$this->loadLayout();
 		/*
        if (Mage::helper('productcarousel/productcarousel')->getUseBreadcrumbs()){
			if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')){
				$breadcrumbBlock->addCrumb('home', array(
							'label'	=> Mage::helper('productcarousel')->__('Home'), 
							'link' 	=> Mage::getUrl(),
						)
				);
				$breadcrumbBlock->addCrumb('productcarousels', array(
							'label'	=> Mage::helper('productcarousel')->__('Product Carousels'), 
							'link'	=> '',
					)
				);
			}
		} */
		$this->renderLayout();
	}
	/**
 	 * view product carousel action
 	 * @access public
 	 * @return void
 	 * @author MagPassion.com
 	 */
	public function viewAction(){
		$productcarouselId 	= $this->getRequest()->getParam('id', 0);
		$productcarousel 	= Mage::getModel('productcarousel/productcarousel')
						->setStoreId(Mage::app()->getStore()->getId())
						->load($productcarouselId);
		if (!$productcarousel->getId()){
			$this->_forward('no-route');
		}
		elseif (!$productcarousel->getStatus()){
			$this->_forward('no-route');
		}
		else{
			Mage::register('current_productcarousel_productcarousel', $productcarousel);
			$this->loadLayout();
            if ($head = $this->getLayout()->getBlock('head')) {
                if (Mage::helper('productcarousel/productcarousel')->getUseLoadJquery()){
                    //echo 'loadingjuery';
                    $head->addJs('magpassion/productcarousel/jquery.min.js');
                    $head->addJs('magpassion/productcarousel/jquery.noconflict.js');
                }
            }
			if ($root = $this->getLayout()->getBlock('root')) {
				$root->addBodyClass('productcarousel-productcarousel productcarousel-productcarousel' . $productcarousel->getId());
			}
			/*
            if (Mage::helper('productcarousel/productcarousel')->getUseBreadcrumbs()){
				if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')){
					$breadcrumbBlock->addCrumb('home', array(
								'label'	=> Mage::helper('productcarousel')->__('Home'), 
								'link' 	=> Mage::getUrl(),
							)
					);
					$breadcrumbBlock->addCrumb('productcarousels', array(
								'label'	=> Mage::helper('productcarousel')->__('Product Carousels'), 
								'link'	=> Mage::helper('productcarousel')->getProductcarouselsUrl(),
						)
					);
					$breadcrumbBlock->addCrumb('productcarousel', array(
								'label'	=> $productcarousel->getBlocktitle(), 
								'link'	=> '',
						)
					);
				}
			}
             
             */
			$this->renderLayout();
		}
	}
}