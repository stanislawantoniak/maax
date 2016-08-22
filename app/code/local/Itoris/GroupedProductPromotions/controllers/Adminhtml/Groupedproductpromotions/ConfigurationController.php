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

  

class Itoris_GroupedProductPromotions_Adminhtml_Groupedproductpromotions_ConfigurationController extends Itoris_GroupedProductPromotions_Controller_Admin_Controller {

	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}

	public function saveAction() {
        $settings = $this->getRequest()->getParam('settings', array());
        $scopeData = $this->getDataHelper()->getScopeData();

        try{
            Mage::getModel('itoris_groupedproductpromotions/settings')->save($settings, $scopeData['scope'], $scopeData['scope_id']);
            $this->_getSession()->addSuccess($this->__('Settings have been saved'));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Settings have not been save'));
        }
        $this->_redirectReferer($this->_getSession()->getBeforeUrl());
	}
}
?>