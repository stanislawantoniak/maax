<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'udsplit';

    protected $_methods = array();
    protected $_allowedMethods = array();

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        #return Mage::helper('udsplit/protected')->collectRates($this, $request);
      
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $hl = Mage::helper('udropship');
        $hlp = Mage::helper('udropship/protected');
        $carrierNames = Mage::getSingleton('udropship/source')->getCarriers();

        // prepare data
        $items = $request->getAllItems();
        if (empty($items)) return;

        try {
            $hlp->prepareQuoteItems($items);
        } catch (Exception $e) {
            Mage::helper('udropship')->addMessageOnce($e->getMessage());
            return;
        }

        foreach ($items as $item) {
            $quote = $item->getQuote();
            break;
        }
        $address = $quote->getShippingAddress();
        foreach ($items as $item) {
            if ($item->getAddress()) {
                $address = $item->getAddress();
            }
            break;
        }

        Mage::dispatchEvent('udsplit_carrier_collect_before', array('request'=>$request, 'address'=>$address));

        $requests = $hlp->getRequestsByVendor($items, $request);

        $evTransport = new Varien_Object(array('requests'=>$requests));
        Mage::dispatchEvent('udsplit_carrier_process_vendor_requests', array('transport'=>$evTransport, 'address'=>$address));
        $requests = $evTransport->getRequests();

#foreach ($requests as $r) var_dump($r); exit;

        // get available dropship shipping methods
        $shipping = $hl->getShippingMethods();

        $freeMethods = explode(',', Mage::getStoreConfig('carriers/udropship/free_method', $hlp->getStore()));
        if ($freeMethods) {
            $_freeMethods = array();
            foreach ($freeMethods as $freeMethod) {
                if (is_numeric($freeMethod)) {
                    if ($shipping->getItemById($freeMethod)) {
                        $_freeMethods[] = $freeMethod;
                    }
                } else {
                    if ($shipping->getItemByColumnValue('shipping_code', $freeMethod)) {
                        $_freeMethods[] = $freeMethod;
                    }
                }
                $_freeMethods[] = $freeMethod;
            }
            $freeMethods = $_freeMethods;
        }

        $result = Mage::getModel('udropship/rateResult');
        foreach ($requests as $vId=>$vRequests) {
            $vendor = $hl->getVendor($vId);
            $systemMethods = $hl->getMultiSystemShippingMethodsByProfile($vendor);
            $vMethods = $vendor->getShippingMethods();
            foreach ($vRequests as $cCode=>$req) {
                $vResult = $hlp->collectVendorCarrierRates($req);
                $vRates = $vResult->getAllRates();
                foreach ($vRates as $rate) {
                    $wildcardUsed = false;
                    $hasVendorMethod = false;
                    $smArray = @$systemMethods[$rate->getCarrier()][$rate->getMethod()];
                    if (!empty($smArray)) {
                        foreach ($smArray as $udMethod) {
                            $hasVendorMethod = $hasVendorMethod || !empty($vMethods[$udMethod->getShippingId()]);
                        }
                    }
                    if (!$hasVendorMethod) {
                        if (!empty($systemMethods[$rate->getCarrier()]['*'])) {
                            $wildcardUsed = true;
                        } else {
                            continue;
                        }
                    }
                    if ($wildcardUsed) {
                        $smArray = $systemMethods[$rate->getCarrier()]['*'];
                    }
                    foreach ($smArray as $udMethod) {
                        $udMethod->useProfile($vendor);
                        if (empty($vMethods[$udMethod->getShippingId()])) {
                            continue;
                        }

                    foreach ($vMethods[$udMethod->getId()] as $vMethod) {

                        $_isSkippedShipping = new Varien_Object(array('result'=>false));
                        Mage::dispatchEvent('udropship_vendor_shipping_check_skipped', array(
                            'shipping'=>$udMethod,
                            'address'=>$address,
                            'vendor'=>$vendor,
                            'request'=>$req,
                            'result'=>$_isSkippedShipping
                        ));

                        if ($_isSkippedShipping->getResult()) {
                            continue;
                        }

                        if ($freeMethods
                            && Mage::getStoreConfigFlag('carriers/udropship/free_shipping_allowed', $request->getStoreId())
                            && Mage::getStoreConfigFlag('carriers/udropship/freeweight_allowed', $request->getStoreId())
                            && $this->isRuleFreeshipping($req)
                            && in_array($udMethod->getShippingCode(), $freeMethods)
                        ) {
                            $rate->setPrice(0);
                            $rate->setIsFwFreeShipping(true);
                        }

                        $rate->setPrice($this->getMyMethodPrice($rate->getPrice(), $req, $udMethod->getShippingCode()));

                        $rate->setUdsIsSkip(false);
                        Mage::dispatchEvent('udropship_process_vendor_carrier_single_rate_result', array(
                            'vendor_method'=>$vMethod,
                            'udmethod'=>$udMethod,
                            'address'=>$address,
                            'vendor'=>$vendor,
                            'request'=>$req,
                            'rate'=>$rate,
                        ));

                        if ($rate->getUdsIsSkip()) {
                            continue;
                        }

                        $vendorCode = $vendor->getCarrierCode();
                        if ($req->getForcedCarrierFlag()) {
                            $ecCode = $ocCode = $rate->getCarrier();
                        } else {
                            $ecCode = !empty($vMethod['est_carrier_code'])
                                ? $vMethod['est_carrier_code']
                                : (!empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendorCode);
                            $ocCode = !empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendorCode;
                        }
                        $oldEstCode = null;
                        $resultKey = sprintf('%s-%s', $vId, $udMethod->getShippingCode());
                        if (!empty($resultRates[$resultKey])) {
                            $oldEstCode = $resultRates[$resultKey]->getUdEstCarrier();
                            if (Mage::helper('udropship')->isUdsprofileActive()
                                && $resultRates[$resultKey]->getUdsprofileSortOrder()<$vMethod['sort_order']
                            ) {
                                continue;
                            }
                        }
                        if ($ecCode!=$rate->getCarrier()) {
                            if (!$wildcardUsed && $vendor->getUseRatesFallback()
                                && !Mage::helper('udropship')->isUdsprofileActive()
                            ) {
                                if ($oldEstCode==$ecCode) {
                                    continue;
                                } elseif ($oldEstCode!=$ocCode && $ocCode==$rate->getCarrier()) {
                                    $ecCode = $ocCode;
                                } elseif (!$oldEstCode && $vendorCode==$rate->getCarrier()) {
                                    $ecCode = $vendorCode;
                                } else {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        }
                        if ('**estimate**' == $ocCode) {
                            $ocCode = $ecCode;
                        }
                        if ($wildcardUsed && $ecCode!=$ocCode) {
                            continue;
                        }
                        if ($ecCode!=$rate->getCarrier()) {
                            continue;
                        }
                        if (Mage::helper('udropship')->isUdsprofileActive()) {
                            $codeToCompare = $vMethod['carrier_code'].'_'.$vMethod['method_code'];
                            if (!empty($vMethod['est_use_custom'])) {
                                $codeToCompare = $vMethod['est_carrier_code'].'_'.$vMethod['est_method_code'];
                            }
                            if ($codeToCompare!=$rate->getCarrier().'_'.$rate->getMethod()) {
                                continue;
                            }
                        }
                        if ($ocCode!=$ecCode) {
                            $ocMethod = $udMethod->getSystemMethods($ocCode);
                            if (Mage::helper('udropship')->isUdsprofileActive()) {
                                $ocMethod = $vMethod['method_code'];
                            }
                            if (empty($ocMethod)) {
                                continue;
                            }
                            $methodNames = $hl->getCarrierMethods($ocCode);
                            $rate
                                ->setCarrier($ocCode)
                                ->setMethod($ocMethod)
                                ->setCarrierTitle($carrierNames[$ocCode])
                                ->setMethodTitle($methodNames[$ocMethod])
                            ;
                        }
                        $rate->setPriority(@$vMethod['priority'])
                            ->setUdEstCarrier($ecCode)
                            ->setUdVendorMethod($vMethod)
                            ->setUdVid($vId)
                            ->setUdropshipShippingId($udMethod->getShippingId());

                        if (Mage::helper('udropship')->isUdsprofileActive()) {
                            $rate->setUdsprofileSortOrder($vMethod['sort_order']);
                        }

                        if ($wildcardUsed) {
                            $resultKey .= $rate->getCarrier().'_'.$rate->getMethod();
                        }
                        $resultRates[$resultKey] = $rate;
                        break;
                    }
                    }
                    foreach ($smArray as $udMethod) {
                        $udMethod->resetProfile();
                    }
                }
            }
        }

        if (!empty($resultRates)) {
            foreach ($resultRates as $resultRate) {
                $result->append($resultRate);
                if ($exRate = $hl->getExtraChargeRate($req, $resultRate, $resultRate->getUdVid(), $resultRate->getUdVendorMethod())) {
                    $result->append($exRate);
                }
            }
        }

        foreach ($items as $item) {
            $quote = $item->getQuote();
            break;
        }
        if (empty($quote)) {
            $result->append($hlp->errorResult('udsplit'));
            return $result;
        }

        $address = $quote->getShippingAddress();
        foreach ($items as $item) {
            if ($item->getAddress()) {
                $address = $item->getAddress();
            }
            break;
        }

        $cost = 0;
        $price = 0;
        $details = $address->getUdropshipShippingDetails();
        $methodCodes = array();
        if ($details && ($details = Zend_Json::decode($details)) && !empty($details['methods'])) {
            foreach ($details['methods'] as $vId=>$rate) {
                if (!empty($rate['code'])) {
                    $methodCodes[$vId] = $rate['code'];
                }
            }
        }

        $totalMethod = Mage::getStoreConfig('udropship/customer/estimate_total_method');

        $details = array('version' => Mage::helper('udropship')->getVersion());
        $result->sortRatesByPriority();
        $rates = $result->getAllRates();
        $hasDefPatternMatch = array();
        foreach ($rates as $rate) {
            if ($rate->getErrorMessage()) {
                continue;
            }
            $vId = $rate->getUdropshipVendor();
            $vendor = $hl->getVendor($vId);
            if (!$vId) {
                continue;
            }
            $pattern = $vendor->getDefaultShippingMethodPattern();
            if (!isset($hasDefPatternMatch[$vId])) {
                $hasDefPatternMatch[$vId] = false;
            }
            $hasDefPatternMatch[$vId] = $hasDefPatternMatch[$vId] || ($pattern ? preg_match('#'.preg_quote($pattern).'#i', $rate->getMethodTitle()) : true);
        }
        $matchRank = array();
        $costsByVid = $pricesByVid = array();
        foreach ($rates as $rate) {
            if ($rate->getErrorMessage()) {
                continue;
            }
            $vId = $rate->getUdropshipVendor();
            $vendor = $hl->getVendor($vId);
            if (!$vId) {
                continue;
            }
            $code = $rate->getCarrier().'_'.$rate->getMethod();
            $data = array(
                'code' => $code,
                'cost' => (float)$rate->getCost(),
                'price' => (float)$rate->getPrice(),
                'price_excl' => (float)$this->getShippingPrice($rate->getPrice(), $vendor, $address, 'base'),
                'price_incl' => (float)$this->getShippingPrice($rate->getPrice(), $vendor, $address, 'incl'),
                'tax' => (float)$this->getShippingPrice($rate->getPrice(), $vendor, $address, 'tax'),
                'carrier_title' => $rate->getCarrierTitle(),
                'method_title' => $rate->getMethodTitle(),
                'is_free_shipping' => (int)$rate->getIsFwFreeShipping()
            );

            if (!isset($matchRank[$vId])) {
                $matchRank[$vId] = 0;
            }

            $pattern = $vendor->getDefaultShippingMethodPattern();
            $defPatternMatch = $pattern ? preg_match('#'.preg_quote($pattern).'#i', $rate->getMethodTitle()) : true;

            $isCurMatch = false;
            $curMatchRank = 0;

            if (!empty($methodCodes[$vId]) && $code==$methodCodes[$vId]) {
                $curMatchRank = 1000;
                $isCurMatch = true;
            } elseif ($vendor->getDefaultShippingId()
                && $vendor->getDefaultShippingId()==$rate->getUdropshipShippingId()
                && (!$rate->getHasExtraCharge()
                    || (bool)$vendor->getIsExtraChargeShippingDefault()==(bool)$rate->getIsExtraCharge()
                )) {
                $curMatchRank = 100;
                $isCurMatch = true;
            } elseif ($defPatternMatch) {
                $curMatchRank = 10;
                $isCurMatch = true;
            } elseif (empty($details['methods'][$vId])) {
                $curMatchRank = 1;
                $isCurMatch = true;
            }

            if ($isCurMatch && $curMatchRank>$matchRank[$vId]) {
                $matchRank[$vId] = $curMatchRank;
                // updating already chosen vendor shipping method price
                $details['methods'][$vId] = $data;
                $costsByVid[$vId] = $data['cost'];
                $pricesByVid[$vId] = $data['price'];
            }
        }

        $price = $cost = 0;
        foreach ($pricesByVid as $_vId=>$_price) {
            $price = $hl->applyEstimateTotalPriceMethod($price, $pricesByVid[$_vId]);
            $cost = $hl->applyEstimateTotalCostMethod($cost, $costsByVid[$_vId]);
        }

        if ($rates) {
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier('udsplit');
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod('total');
            $method->setMethodTitle('Total');
            $method->setCost($price);
            //$method->setPrice($this->getMethodPrice($price, 'total'));
            $method->setPrice($price);
            $result->append($method);
        } else {
            $result->append($hlp->errorResult('udsplit'));
        }

        //$address->setUdropshipShippingDetails(Zend_Json::encode($details));
        $address->setShippingMethod('udsplit_total');
        $address->setShippingDescription($this->getConfigData('title'));
        $address->setShippingAmount($price);

        Mage::dispatchEvent('udsplit_carrier_collect_after', array('request'=>$request, 'result'=>$result, 'address'=>$address, 'details'=>$details));
        Mage::dispatchEvent('udropship_carrier_collect_after', array('request'=>$request, 'result'=>$result, 'address'=>$address, 'details'=>$details));

        return $result;
    }

    public function getShippingPrice($baseShipping, $vId, $address, $type)
    {
        return Mage::helper('udropship')->getShippingPrice($baseShipping, $vId, $address, $type);
    }

    public function getMyMethodPrice($cost, $request, $method='')
    {
#if ($_SERVER['REMOTE_ADDR']=='24.20.46.76') { echo "<pre>"; print_r($this->_rawRequest->debug()); exit; }
        $freeMethods = explode(',', Mage::getStoreConfig('carriers/udropship/free_method', $request->getStoreId()));
        $freeShippingSubtotal = $this->getConfigData('free_shipping_subtotal');
        if ($freeShippingSubtotal === null || $freeShippingSubtotal === '') {
            $freeShippingSubtotal = false;
        }
        if (in_array($method, $freeMethods)
            && Mage::getStoreConfigFlag('carriers/udropship/free_shipping_allowed', $request->getStoreId())
            && Mage::getStoreConfigFlag('carriers/udropship/free_shipping_enable', $request->getStoreId())
            && $freeShippingSubtotal!==false
            && $freeShippingSubtotal <= $request->getPackageValueWithDiscount()
        ) {
            $price = '0.00';
        } else {
            $price = $this->getFinalPriceWithHandlingFee($cost);
        }
        return $price;
    }

    public function getAllowedMethods()
    {
        if (empty($this->_allowedMethods)) {
            $this->_allowedMethods = array('total'=>'Total');
        }
        return $this->_allowedMethods;
    }

    protected function _getAllMethods()
    {

    }

    public function getUseForAllProducts()
    {
        return true;
    }

    public function isRuleFreeshipping($request)
    {
        $isFreeshipping = true;
        foreach ($request->getAllItems() as $item) {
            if ($item->getFreeShipping()!==true && $item->getTotalQty()>$item->getFreeShipping()) {
                $isFreeshipping = false;
                break;
            }
        }
        $address = Mage::helper('udropship/item')->getAddress($request->getAllItems());
        if ($address instanceof Varien_Object && $address->getFreeShipping() === true) {
            $isFreeshipping = true;
        }
        return $isFreeshipping;
    }
}
