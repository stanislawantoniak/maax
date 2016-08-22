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

class Itoris_GroupedProductPromotions_Model_Rewrite_Sales_Quote_Item extends Mage_Sales_Model_Quote_Item {

    /**
     * @return Mage_Sales_Model_Quote_Item
     */
    public function calcRowTotal() {
        if ($this->getUseGroupedProductPromotionPrice()) {
            $promoPrice = $this->getGroupedProductPromotionPrice();
            $this->setPrice($promoPrice)
                ->setBaseOriginalPrice($promoPrice);
        }

        return parent::calcRowTotal();
    }
}
?>