<?php

class ZolagoOs_OmniChannelSplit_Block_Multishipping_Overview
    extends Mage_Checkout_Block_Multishipping_Overview
{
    static $_blockIter = 0;

    public function getShippingAddressItems($a)
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getShippingAddressItems($a);
        }

        $q = $this->getCheckout()->getQuote();
        $methods = array();
        $details = $a->getUdropshipShippingDetails();
        if ($details) {
            $details = Zend_Json::decode($details);
            $methods = isset($details['methods']) ? $details['methods'] : array();
        }

        $aItems = $a->getAllVisibleItems();
        $vendorItems = array();
        foreach ($aItems as $item) {
            $item->setQuoteItem($q->getItemById($item->getQuoteItemId()));
            $vendorItems[$item->getUdropshipVendor()][] = $item;
        }

        $items = array();

        foreach ($vendorItems as $vId=>$vItems) {
            $obj = Mage::getModel('udsplit/cart_vendor')
                ->setQuote($q)
                ->setProduct(new Varien_Object());
            if (!Mage::getStoreConfigFlag('carriers/udsplit/hide_vendor_name')) {
                $items[] = $obj->setQuoteItem(Mage::getModel('udsplit/cart_vendor')
                    ->setPart('header')
                    ->setVendor(Mage::helper('udropship')->getVendor($vId)));
            }

            foreach ($vItems as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                $items[] = $item;
            }

#echo "<pre>"; print_r($rates[$vId]); echo "</pre>";
            $obj = Mage::getModel('udsplit/cart_vendor')
                ->setQuote($q)
                ->setProduct(new Varien_Object());
            $items[] = $obj->setQuoteItem(Mage::getModel('udsplit/cart_vendor')
                ->setPart('footer')
                ->setVendor(Mage::helper('udropship')->getVendor($vId))
                ->setShowDropdowns(false)
                ->setEstimateRates(array())
                ->setErrorsOnly(false)
                ->setShippingMethod(isset($methods[$vId]) ? $methods[$vId] : null)
                ->setItems($vItems)
                ->setAddress($a)
                ->setQuote1($q));

        }
        return $items;
    }

    public function getItemHtml(Varien_Object $item)
    {
        if ($item instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor
            || $item->getQuoteItem() instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor
        ) {
            $qItem = !$item instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor
                || $item->getProduct() && !$item->getProduct()->getId()
                    ? $item->getQuoteItem()
                    : $item;
            $blockName = "vendor_{$qItem->getVendor()->getId()}_{$qItem->getPart()}_".(self::$_blockIter++);
            return $this->getLayout()->createBlock('udsplit/multishipping_vendor', $blockName)
                ->addData($qItem->getData())
                ->setQuote($qItem->getQuote1())
                ->toHtml();
        }
        return parent::getItemHtml($item);
    }

    public function getRowItemHtml(Varien_Object $item)
    {
        if ($item instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor
            || $item->getQuoteItem() instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor
        ) {
            return $this->getItemHtml($item);
        }
        return parent::getRowItemHtml($item);
    }
}