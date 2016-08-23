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

  

class Itoris_GroupedProductPromotions_Helper_Form extends Itoris_GroupedProductPromotions_Helper_Data {

	public function prepareElementsValues($form) {
		$values = array();
		$fieldsets = $form->getElements();
		$checkWebsite = (bool)Mage::app()->getRequest()->getParam('website');
		$checkStore = (bool)Mage::app()->getRequest()->getParam('store');
		foreach ($fieldsets as $fieldset) {
			if (get_class($fieldset) == 'Varien_Data_Form_Element_Fieldset') {
				foreach ($fieldset->getElements() as $element) {
					if ($element->getType() == 'link') {
						continue;
					}
					if ($id = $element->getId()) {
						$value = $this->getSettings(true)->getSettingsValue($id);
						if ($value !== null) {
							$values[$id] = $value;
						}
						if ($element->getType() == 'checkbox' && $value) {
							$element->setIsChecked($value);
						}
						$element->setUseParent($this->getSettings(true)->isParentValue($id, (!$checkStore && $checkWebsite)));
						$element->setUseScope($checkWebsite ? ($checkStore ? $this->__('Use Website') : $this->__('Use Default')) : null);
					}
				}
			}
		}

		return $values;
	}
}

?>