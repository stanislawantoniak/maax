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

class Unirgy_DropshipSplit_Block_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
    public function getEstimateRates()
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getEstimateRates();
        }

        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            foreach ($groups as $cCode=>$rates) {
                foreach ($rates as $i=>$rate) {
                    if ($rate->getUdropshipVendor() || $rate->getCarrier()=='udsplit' && $rate->getMethod()=='total') {
                        unset($groups[$cCode][$i]);
                    }
                    if (empty($groups[$cCode])) {
                        unset($groups[$cCode]);
                    }
                }
            }
            $this->_rates = $groups;
        }
        return $this->_rates;
    }
}