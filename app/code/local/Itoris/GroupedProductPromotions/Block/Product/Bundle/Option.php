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



class Itoris_GroupedProductPromotions_Block_Product_Bundle_Option extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle {

	public function getOptionHtml($option) {
		if (!isset($this->_optionRenderers[$option->getType()])) {
			return $this->__('There is no defined renderer for "%s" option type.', $option->getType());
		}
		return $this->getLayout()->createBlock($this->_optionRenderers[$option->getType()])
            ->setRuleId($this->getRuleId())
			->setOption($option)->setProduct($this->getProduct())->toHtml();
	}

    /**
     * @return Itoris_BundleProductPromotions_Helper_Data
     */

    public function getBundlePromotionsDataHelper() {
        return Mage::helper('itoris_bundleproductpromotions');
    }
}
?>