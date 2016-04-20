<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Model_Observer
{
    public function checkout_cart_update_items_after($observer)
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return;
        }

        $cart = $observer->getEvent()->getCart();
        $info = $observer->getEvent()->getInfo();
        $quote = $cart->getQuote();
        $address = $quote->getShippingAddress();

        if (!empty($info['estimate_method']) && is_array($info['estimate_method'])) {
            $details = $address->getUdropshipShippingDetails();
            $details = $details ? Zend_Json::decode($details) : array();

            foreach ($info['estimate_method'] as $vId=>$code) {
                $r = $address->getShippingRateByCode($code);
                if (!$r) {
                    continue;
                }
                $details['methods'][$vId] = array(
                    'code' => $code,
                    'cost' => $r->getCost(),
                    'price' => $r->getPrice(),
                    'carrier_title' => $r->getCarrierTitle(),
                    'method_title' => $r->getMethodTitle(),
                );
            }

            $address->setUdropshipShippingDetails(Zend_Json::encode($details));
        }
        if ($address) {
            $address->setShippingMethod('udsplit_total');
        }
    }

    public function checkout_controller_multishipping_shipping_post($observer)
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $quote = $observer->getEvent()->getQuote();

        $methods = $request->getParam('shipping_method');
        $vMethods = $request->getParam('vendor_shipping_method');
        if (!empty($vMethods) && is_array($vMethods)) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                $aId = $address->getId();
                if (empty($vMethods[$aId])) {
                    continue;
                }
                if (empty($methods[$aId])) {
                    $methods[$aId] = 'udsplit_total';
                }

                $details = $address->getUdropshipShippingDetails();
                $details = $details ? Zend_Json::decode($details) : array();

                $cost = 0;
                $price = 0;
                foreach ($vMethods[$aId] as $vId=>$code) {
                    $r = $address->getShippingRateByCode($code);
                    if (!$r) {
                        continue;
                    }
                    $details['methods'][$vId] = array(
                        'code' => $code,
                        'cost' => $r->getCost(),
                        'price' => $r->getPrice(),
                        'carrier_title' => $r->getCarrierTitle(),
                        'method_title' => $r->getMethodTitle(),
                    );
                    $cost += $r->getCost();
                    $price += $r->getPrice();
                }
                foreach ($address->getAllShippingRates() as $rate) {
                    if ($rate->getCode()=='udsplit_total') {
                        $rate->setPrice($price)->setCost($cost)->save();
                        break;
                    }
                }
                $address->setUdropshipShippingDetails(Zend_Json::encode($details));
                $address->save();
            }
        }
        $request->setParam('shipping_method', $methods);
    }

    public function controller_action_layout_render_before_adminhtml_sales_order_create_index($observer)
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return;
        }

        $layout = Mage::app()->getLayout();
        $layout->getBlock('shipping_method')->getChild('form')
            ->setTemplate('udsplit/order_create_shipping.phtml');
    }

    public function adminhtml_version($observer)
    {
        Mage::helper('udropship')->addAdminhtmlVersion('ZolagoOs_OmniChannelSplit');
    }
}