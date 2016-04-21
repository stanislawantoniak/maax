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

class Unirgy_DropshipSplit_Block_Cart extends Mage_Checkout_Block_Cart
{


    /**
     * @return object
     */
    public function getUdropShippingMethodsPerActualWebsite()
    {
        $ids = array();
        $model = Mage::getModel('udropship/shipping');
        $shipping = Mage::getModel('udropship/shipping')->getCollection();
        $shipping->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $shipping->getSelect()->join(
            array('udropship_shipping_method' => "udropship_shipping_method"),
            "main_table.shipping_id = udropship_shipping_method.shipping_id",
            array(
                'method' => 'udropship_shipping_method.method_code',
            )
        );
        $shipping->getSelect()->join(
            array('website_table' => $model->getResource()->getTable('udropship/shipping_website')),
            'main_table.shipping_id = website_table.shipping_id',
            array()
        )->where("website_table.website_id IN(?)", array(0, Mage::app()->getWebsite()->getId()));

        foreach($shipping as $shippingItem){
            $ids[] = $shippingItem->getMethod();
        }
        return $ids;
    }


    public function getItems()
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getItems();
        }

        $shippingMethodIds = $this->getUdropShippingMethodsPerActualWebsite();

        $q = $this->getQuote();
        $a = $q->getShippingAddress();
        $methods = array();
        $details = $a->getUdropshipShippingDetails();
        if ($details) {
            $details = Zend_Json::decode($details);
            $methods = isset($details['methods']) ? $details['methods'] : array();
        }

        $quoteItems = $q->getAllVisibleItems();

        Mage::helper('udropship/protected')->prepareQuoteItems($a->getAllItems());

        $vendorItems = array();
        foreach ($quoteItems as $item) {
            $vendorItems[$item->getUdropshipVendor()][] = $item;
        }

        $rates = array();
        $qRates = $a->getGroupedAllShippingRates();
        foreach ($qRates as $cCode=>$cRates) {
            foreach ($cRates as $rate) {
                $vId = $rate->getUdropshipVendor();
                if (!$vId
                    || !in_array($rate->getMethod(), $shippingMethodIds)
                ) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
            }
        }

        $items = array();
        $dummyProduct = Mage::getModel('catalog/product');
        foreach ($vendorItems as $vId=>$vItems) {
            if (!Mage::getStoreConfigFlag('carriers/udsplit/hide_vendor_name')) {
                $items[] = Mage::getModel('udsplit/cart_vendor')
                    ->setPart('header')
                    ->setQuote1($q)
                    ->setData('product', $dummyProduct)
                    ->setVendor(Mage::helper('udropship')->getVendor($vId));
            }
            foreach ($vItems as $item) {
                $items[] = $item;
            }

            $errorsOnly = false;
            if (!empty($rates[$vId])) {
                $errorsOnly = true;
                foreach ($rates[$vId] as $cCode=>$rs) {
//                    $hasRates = false;
                    foreach ($rs as $r) {
                        if (!$r->getErrorMessage()) {
//                            $hasRates = true;
                            $errorsOnly = false;
                        }
                    }
//                    if (!$hasRates) {
//                        unset($rates[$vId][$cCode]);
//                    }
                }
            }

            $items[] = Mage::getModel('udsplit/cart_vendor')
                ->setPart('footer')
                ->setData('product', $dummyProduct)
                ->setVendor(Mage::helper('udropship')->getVendor($vId))
                ->setEstimateRates(isset($rates[$vId]) ? $rates[$vId] : array())
                ->setErrorsOnly($errorsOnly)
                ->setShippingMethod(isset($methods[$vId]) ? $methods[$vId] : null)
                ->setItems($vItems)
                ->setQuote1($q);
        }

        return $items;
    }

    public function getItemHtml(Mage_Sales_Model_Quote_Item $item)
    {
        if ($item instanceof Unirgy_DropshipSplit_Model_Cart_Vendor) {
            $blockName = "vendor_{$item->getVendor()->getId()}_{$item->getPart()}";
            return $this->getLayout()->createBlock('udsplit/cart_vendor', $blockName)
                ->addData($item->getData())
                ->setQuote($item->getQuote1())
                ->toHtml();
        }

        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }
}