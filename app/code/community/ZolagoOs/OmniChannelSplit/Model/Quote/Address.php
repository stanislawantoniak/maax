<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Model_Quote_Address extends Mage_Sales_Model_Quote_Address
{
    protected function _dispatchExtcoEvent($event, $args)
    {
        if (Mage::getStoreConfigFlag('carriers/udsplit/extco_dispatch_events')) {
            Mage::dispatchEvent($event, $args);
        }
        return $this;
    }
    protected function __getTrace($trace=null)
    {
        if (is_null($trace) && Mage::getStoreConfigFlag('carriers/udsplit/extco_check')) {
            $trace = mageDebugBacktrace(1,0);
        }
        return $trace;
    }
    protected function _isGoogleCheckout($trace=null)
    {
        $flag = false;
        if (($trace = $this->__getTrace($trace))) {
            $flag = preg_match('/app.code.core.Mage.GoogleCheckout.Model.Api.Xml.Callback.php/', $trace)
                || preg_match('/app.code.core.Mage.GoogleCheckout.controllers.RedirectController.php/', $trace);
            $this->_dispatchExtcoEvent('udsplit_check_is_google_checkout', array('address'=>$this, 'vars'=>array('flag'=>&$flag)));
        }
        return $flag;
    }
    protected function _isPaypalExpress($trace=null)
    {
        $flag = false;
        if (($trace = $this->__getTrace($trace))) {
            $flag = preg_match('/app.code.core.Mage.Paypal.Block.Express.Review.php/', $trace)
                || preg_match('/app.code.core.Mage.Paypal.Model.Express.Checkout.php/', $trace);
            $this->_dispatchExtcoEvent('udsplit_check_is_paypal_express', array('address'=>$this, 'vars'=>array('flag'=>&$flag)));
        }
        return $flag;
    }
    protected function _isExternalCheckout($trace=null)
    {
        $flag = false;
        if (($trace = $this->__getTrace($trace))) {
            $flag = $this->_isGoogleCheckout($trace) || $this->_isPaypalExpress($trace);
            $this->_dispatchExtcoEvent('udsplit_check_is_external_checkout', array('address'=>$this, 'vars'=>array('flag'=>&$flag)));
        }
        return $flag;
    }
    public function setUdropshipShippingDetails($details)
    {
        $this->setData('udropship_shipping_details', $details);
        if ($this->_isExternalCheckout()) {
            if (($quote = $this->getQuote())) {
                $res = $quote->getResource();
                $w = Mage::getSingleton('core/resource')->getConnection('core_write');
                $quote->setUdropshipShippingDetails($details);
                $w->update(
                    $res->getTable('sales/quote'),
                    array('udropship_shipping_details'=>$details),
                    $w->quoteInto($res->getIdFieldName().'=?', $quote->getId())
                );
            }
        }
        return $this;
    }
    public function getUdropshipShippingDetails()
    {
        $details = $this->getData('udropship_shipping_details');
        if (!$details && $this->_isExternalCheckout() && ($quote = $this->getQuote())) {
            $details = $quote->getData('udropship_shipping_details');
        }
        return $details;
    }
    
    public function getShippingMethod()
    {
        if (!$this->getData('shipping_method')
            && $this->getShippingRateByCode('udsplit_total')
        ) {
            $this->setData('shipping_method', 'udsplit_total');
        }
        return $this->getData('shipping_method');
    }

    public function setShippingMethod($method)
    {
        if (!Mage::helper('udsplit')->isActive() || !is_array($method)) {
            $this->setData('shipping_method', $method);
            return $this;
        }

        $hl = Mage::helper('udropship');

        $details = $this->getUdropshipShippingDetails();
        $details = $details ? Zend_Json::decode($details) : array('version'=>Mage::helper('udropship')->getVersion());
        $cost = 0;
        $price = 0;
        $rates = $this->getShippingRatesCollection();
        foreach ($method as $vId=>$code) {
            $r = null;
            foreach ($rates as $rate) {
                if ($rate->getUdropshipVendor()==$vId && $rate->getCode()==$code) {
                    $r = $rate;
                }
            }
            if (!$r) {
                continue;
            }
            $data = array(
                'code' => $code,
                'cost' => (float)$r->getCost(),
                'price' => (float)$r->getPrice(),
                'carrier_title' => $r->getCarrierTitle(),
                'method_title' => $r->getMethodTitle(),
            );
            $details['methods'][$vId] = $data;
            $cost = $hl->applyEstimateTotalPriceMethod($cost, $data['cost']);
            $price = $hl->applyEstimateTotalPriceMethod($price, $data['price']);
        }
        //$price = Mage::getSingleton('udsplit/carrier')->getMethodPrice($price, 'total');

        Mage::dispatchEvent('udsplit_quote_setShippingMethod_price', array('address'=>$this, 'vars'=>array('price'=>&$price, 'details'=>&$details)));

        $this->setUdropshipShippingDetails(Zend_Json::encode($details));
        $method = 'udsplit_total';
        $rate = $this->getShippingRateByCode($method);
        if ($rate) {
            $rate->setCost($cost)->setPrice($price);
        }
        $this->setData('shipping_method', $method);
        return $this;
    }

    public function getGroupedAllShippingRates()
    {
        $qRates = parent::getGroupedAllShippingRates();
        if (Mage::helper('udsplit')->isActive() && $this->_isPaypalExpress()) {
            $rates = array();
            foreach ($qRates as $cCode=>$cRates) {
                foreach ($cRates as $rate) {
                    $vId = $rate->getUdropshipVendor();
                    if (!$vId) {
                        $rates[$cCode][] = $rate;
                    }
                }
            }
        } else {
            $rates = $qRates;
        }
        return $rates;
    }

    public function getShippingRateByCode($code)
    {
        if (is_array($code)) {
            return true;
        }
        return parent::getShippingRateByCode($code);
    }
    
    public function __clone()
    {
        if ($this->getAddressType() == 'billing') {
            $this->unsUdropshipShippingDetails();
        }
        return parent::__clone();
    }
}