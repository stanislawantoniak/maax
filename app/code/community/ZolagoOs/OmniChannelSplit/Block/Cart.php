<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Block_Cart extends Mage_Checkout_Block_Cart
{
    public function getItems()
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getItems();
        }

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
                if (!$vId) {
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
        if ($item instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor) {
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