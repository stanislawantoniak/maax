<?php

class Unirgy_DropshipSplit_Block_Multishipping_Shipping
    extends Mage_Checkout_Block_Multishipping_Shipping
{
    static $_blockIter = 0;

    public function getAddressItems($a)
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getAddressItems($a);
        }

        $q = $this->getCheckout()->getQuote();
        $methods = array();
        $details = $a->getUdropshipShippingDetails();
        if ($details) {
            $details = Zend_Json::decode($details);
            $methods = isset($details['methods']) ? $details['methods'] : array();
        }

        $aItems = $a->getAllItems();
        $qItems = array();
        foreach ($aItems as $item) {
            $qItem = $q->getItemById($item->getQuoteItemId());
            if (!$qItem->getProduct()) {
                $qItem->setProduct(new Varien_Object());
            }
            $item->setQuoteItem($qItem);
            $qItems[] = $qItem;
        }

        Mage::helper('udropship/protected')->prepareQuoteItems($qItems);

        $vendorItems = array();
        foreach ($aItems as $item) {
            $vendorItems[$item->getUdropshipVendor()][] = $item;
        }

        $rates = array();
        $qRates = $a->getGroupedAllShippingRates();
        foreach ($qRates as $cCode=>$cRates) {
            foreach ($cRates as $rate) {
                $vId = $rate->getUdropshipVendor();
                if (!$vId) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
            }
        }

        $items = array();

        foreach ($vendorItems as $vId=>$vItems) {
            $obj = new Varien_Object();
            if (!Mage::getStoreConfigFlag('carriers/udsplit/hide_vendor_name')) {
                $items[] = $obj->setQuoteItem(Mage::getModel('udsplit/cart_vendor')
                    ->setPart('header')
                    ->setVendor(Mage::helper('udropship')->getVendor($vId)));
            }

            foreach ($vItems as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                $item->setQty(sprintf('%d', $item->getQty()));
                $items[] = $item;
            }

            $errorsOnly = false;
            if (!empty($rates[$vId])) {
                $errorsOnly = true;
                foreach ($rates[$vId] as $cCode=>$rs) {
                    foreach ($rs as $r) {
                        if (!$r->getErrorMessage()) {
                            $errorsOnly = false;
                        }
                    }
                }
            }
#echo "<pre>"; print_r($rates[$vId]); echo "</pre>";
            $obj = new Varien_Object();
            $items[] = $obj->setQuoteItem(Mage::getModel('udsplit/cart_vendor')
                ->setPart('footer')
                ->setVendor(Mage::helper('udropship')->getVendor($vId))
                ->setShowDropdowns(true)
                ->setEstimateRates(isset($rates[$vId]) ? $rates[$vId] : array())
                ->setErrorsOnly($errorsOnly)
                ->setShippingMethod(isset($methods[$vId]) ? $methods[$vId] : null)
                ->setItems($vItems)
                ->setAddress($a)
                ->setQuote1($q));

        }
        return $items;
    }

    public function getShippingRates($address)
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getShippingRates($address);
        }

        $groups = $address->getGroupedAllShippingRates();
        $groups1 = array();
        foreach ($groups as $cCode=>$rates) {
            foreach ($rates as $rate) {
                if ($rate->getUdropshipVendor()) {
                    continue;
                }
                $groups1[$cCode][] = $rate;
            }
        }
        return $groups1;
    }

    public function getItemHtml(Varien_Object $item)
    {
        if ($item instanceof Unirgy_DropshipSplit_Model_Cart_Vendor) {
            $blockName = "vendor_{$item->getVendor()->getId()}_{$item->getPart()}_".(self::$_blockIter++);
            return $this->getLayout()->createBlock('udsplit/multishipping_vendor', $blockName)
                ->addData($item->getData())
                ->setQuote($item->getQuote1())
                ->toHtml();
        }
        return parent::getItemHtml($item);
    }
}