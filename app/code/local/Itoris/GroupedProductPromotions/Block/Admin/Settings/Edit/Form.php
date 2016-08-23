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

  

class Itoris_GroupedProductPromotions_Block_Admin_Settings_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
		$form = new Varien_Data_Form();

		$fieldset = $form->addFieldset('general_fields', array(
			'legend' => $this->__('Grouped Product Promotions'),
		));

		$fieldset->addField('enabled', 'select', array(
			'name'   => 'settings[enabled][value]',
			'label'  => $this->__('Extension Enabled'),
			'title'  => $this->__('Extension Enabled'),
			'values' => array(
				array('label' => $this->__('Yes'),
					'value' => 1),
				array('label' => $this->__('No'),
					'value' => 0),
			),
		))->getRenderer()->setTemplate('itoris/groupedproductpromotions/configuration/form/element.phtml');

		$form->addValues($this->getFormHelper()->prepareElementsValues($form));
		$form->setUseContainer(true);
		$form->setId('edit_form');
		$form->setAction($this->getUrl('adminhtml/groupedproductpromotions_configuration/save', array('_current' => true)));
		$form->setMethod('post');
		$this->setForm($form);
	}

	/**
	 * @return Itoris_GroupedProductPromotions_Helper_Form
	 */
	public function getFormHelper() {
		return Mage::helper('itoris_groupedproductpromotions/form');
	}

}
?>