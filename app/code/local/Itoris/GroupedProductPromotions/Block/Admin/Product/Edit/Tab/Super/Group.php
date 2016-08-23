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

 

class  Itoris_GroupedProductPromotions_Block_Admin_Product_Edit_Tab_Super_Group extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group {

	protected function _prepareCollection()	{
		if ($this->getDataHelper()->isRegisteredAdmin()) {
			$allowProductTypes = array();
			$allowProductTypeNodes = Mage::getConfig()
				->getNode('global/catalog/product/type/grouped/allow_product_types')->children();
			foreach ($allowProductTypeNodes as $type) {
				$allowProductTypes[] = $type->getName();
			}

			$collection = Mage::getModel('catalog/product_link')->useGroupedLinks()
				->getProductCollection()
				->setProduct($this->_getProduct())
				->addAttributeToSelect('*')
				/*->addAttributeToFilter('type_id', $allowProductTypes)*/;

			if ($this->getIsReadonly() === true) {
				$collection->addFieldToFilter('entity_id', array('in' => $this->_getSelectedProducts()));
			}
			$this->_collection = $collection;
		}

		return parent::_prepareCollection();
	}

	public function setCollection($collection) {
		if (!$this->getDataHelper()->isRegisteredAdmin()) {
			return parent::setCollection($collection);
		}
	}

    /**
     * @return Itoris_GroupedProductPromotions_Helper_Data
     */
    public function getDataHelper() {
        return Mage::helper('itoris_groupedproductpromotions');
    }
}
?>