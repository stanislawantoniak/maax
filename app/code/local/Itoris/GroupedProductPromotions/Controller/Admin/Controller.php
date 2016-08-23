<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_GROUPEDPRODUCTPROMOTIONS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

  

class Itoris_GroupedProductPromotions_Controller_Admin_Controller extends Mage_Adminhtml_Controller_Action {

	public function preDispatch() {
		$result = parent::preDispatch();
		$helper = $this->getDataHelper();
		if (!Itoris_Installer_Client::isAdminRegistered($helper->getAlias())) {
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
			$register = $this->getLayout()->createBlock('itoris_groupedproductpromotions/admin_register');
			$register->setMessage($helper->__('Your copy of the component is not registered! All functions are disabled. Please register. Enter your S/N to register:'));
			Mage::getSingleton('adminhtml/session')->addError($register->toHtml());
			$this->loadLayout();
			$this->getLayout()->getBlock('left')->unsetChildren();
			$this->getLayout()->getBlock('content')->unsetChildren();
			$this->renderLayout();
		}

		return $result;
	}

	/**
	 * @return Itoris_GroupedProductPromotions_Helper_Data
	 */

	protected function getDataHelper() {
		return Mage::helper('itoris_groupedproductpromotions');
	}
}
?>