<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Block_Onepage_Review extends Mage_Checkout_Block_Onepage_Review_Info
{
    static $_blockIter = 0;

    public function getItems()
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getItems();
        }

        $q = Mage::getSingleton('checkout/session')->getQuote();
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
                if (!$vId) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
            }
        }

        $items = array();
        foreach ($vendorItems as $vId=>$vItems) {
            $allVirtual = true;
            foreach ($vItems as $item) {
                if (!$item->getIsVirtual()) {
                    $allVirtual = false;
                    break;
                }
            }
            if ($allVirtual && $this->getShowDropdowns()) {
                continue;
            }

            if (!Mage::getStoreConfigFlag('carriers/udsplit/hide_vendor_name')) {
                $items[] = Mage::getModel('udsplit/cart_vendor')
                    ->setPart('header')
                    ->setQuote1($q)
                    ->setVendor(Mage::helper('udropship')->getVendor($vId));
            }

            foreach ($vItems as $item) {
                if ($this->getShowDropdowns() && $item->getIsVirtual()) {
                    continue;
                }
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
#echo "<pre>"; print_r($rates[$vId]); echo "</pre>";
            if (!$allVirtual) {
                $items[] = Mage::getModel('udsplit/cart_vendor')
                    ->setPart('footer')
                    ->setVendor(Mage::helper('udropship')->getVendor($vId))
                    ->setShowDropdowns($this->getShowDropdowns())
                    ->setEstimateRates(isset($rates[$vId]) ? $rates[$vId] : array())
                    ->setErrorsOnly($errorsOnly)
                    ->setShippingMethod(isset($methods[$vId]) ? $methods[$vId] : null)
                    ->setItems($vItems)
                    ->setQuote1($q);
            }
        }
        return $items;
    }

    public function getItemHtml(Varien_Object $item)
    {
        if ($item instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor) {
            $blockName = "vendor_{$item->getVendor()->getId()}_{$item->getPart()}_".(self::$_blockIter++);
            return $this->getLayout()->createBlock('udsplit/onepage_vendor', $blockName)
                ->addData($item->getData())
                ->setQuote($item->getQuote1())
                ->toHtml();
        }
        return parent::getItemHtml($item);
    }
}