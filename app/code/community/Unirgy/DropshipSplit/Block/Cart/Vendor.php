<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipSplit_Block_Cart_Vendor extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('unirgy/dsplit/cart/vendor.phtml');
    }

    public function getSubtotal()
    {
        $subtotal = 0;
        foreach ($this->getItems() as $item) {
            if ($this->helper('tax')->displayCartPriceInclTax() || $this->helper('tax')->displayCartBothPrices()) {
                $subtotal += $this->helper('checkout')->getSubtotalInclTax($item);
            } else {
                $subtotal += $item->getRowTotal();
            }
        }
        return $subtotal;
    }

    public function getWeight()
    {
        $weight = 0;
        foreach ($this->getItems() as $item) {
            $weight += $item->getFullRowWeight();
        }
        return $weight;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function getShippingPrice($price, $flag)
    {
        $address = $this->getQuote1()->getShippingAddress();
        $address->setUdropshipVendor($this->getVendor()->getId());
        return $this->formatPrice($this->helper('tax')->getShippingPrice($price, $flag, $address));
    }

    public function formatPrice($price)
    {
        return $this->getQuote()->getStore()->convertPrice($price, true, false);
    }

    public function isVirtual()
    {
        $vItems = $this->getItems();
        $isVirtual = true;
        $countItems = 0;
        if (!empty($vItems)) {
            foreach ($vItems as $_item) {
                /* @var $_item Mage_Sales_Model_Quote_Item */
                if ($_item->isDeleted() || $_item->getParentItemId()) {
                    continue;
                }
                $countItems ++;
                if (!$_item->getIsVirtual()) {
                    $isVirtual = false;
                }
            }
        }
        return $countItems == 0 ? false : $isVirtual;
    }
}