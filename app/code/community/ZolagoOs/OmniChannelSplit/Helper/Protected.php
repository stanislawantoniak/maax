<?php

class ZolagoOs_OmniChannelSplit_Helper_Protected
{
	public function collectRates($carrier, $request) {
		ZolagoOs_OmniChannel_Helper_Protected::validateLicense("ZolagoOs_OmniChannelSplit");
		if (!$carrier->getConfigFlag("active")) {
			return false;
		}

		$hl = Mage::helper("udropship");
		$hlp = Mage::helper("udropship/protected");
		$carrierNames = Mage::getSingleton("udropship/source")->getCarriers();
		$items = $request->getAllItems();
		try {
			$hlp->prepareQuoteItems($items);
		} catch (Exception $e) {
			Mage::helper("udropship")->addMessageOnce($e->getMessage());
			return NULL;
		}
		$requests = $hlp->getRequestsByVendor($items, $request);
		$shipping = $hl->getShippingMethods();
		$systemMethods = $hl->getMultiSystemShippingMethods();
		$freeMethods = explode(",", Mage::getStoreConfig("carriers/udropship/free_method", $hlp->getStore()));
		if ($freeMethods) {
			$_freeMethods = array();
			foreach ($freeMethods as $freeMethod) {
				if (is_numeric($freeMethod)) {
					if ($shipping->getItemById($freeMethod)) {
						$_freeMethods[] = $freeMethod;
					}
				} else {
					if ($shipping->getItemByColumnValue("shipping_code", $freeMethod)) {
						$_freeMethods[] = $freeMethod;
					}
				}
				$_freeMethods[] = $freeMethod;
			}
			$freeMethods = $_freeMethods;
		}

		$result = Mage::getModel("shipping/rate_result");
		foreach ($requests as $vId => $vRequests) {
			$vendor = $hl->getVendor($vId);
			$vMethods = $vendor->getShippingMethods();
			foreach ($vRequests as $cCode => $req) {
				$vResult = $hlp->collectVendorCarrierRates($req);
				$vRates = $vResult->getAllRates();
				foreach ($vRates as $rate) {
					if (empty($systemMethods[$rate->getCarrier()][$rate->getMethod()])) {
						continue;
					}

					foreach ($systemMethods[$rate->getCarrier()][$rate->getMethod()] as $udMethod) {
						if (empty($vMethods[$udMethod->getShippingId()])) {
							continue;
						}

						if ($freeMethods && $req->hasFreeMethodWeight() && $req->getFreeMethodWeight() == 0 && in_array($udMethod->getShippingCode(), $freeMethods)) {
							$rate->setPrice(0);
						}

						$rate->setPrice($carrier->getMyMethodPrice($rate->getPrice(), $req, $udMethod->getShippingCode()));
						$vMethod = $vMethods[$udMethod->getShippingId()];
						$ecCode = !empty($vMethod["est_carrier_code"]) ? $vMethod["est_carrier_code"] : $vendor->getCarrierCode();
						$ocCode = !empty($vMethod["carrier_code"]) ? $vMethod["carrier_code"] : $vendor->getCarrierCode();
						if ($ecCode != $rate->getCarrier()) {
							continue;
						}

						if ($ocCode != $ecCode) {
							$ocMethod = $udMethod->getSystemMethods($ocCode);
							if (empty($ocMethod)) {
								continue;
							}
							$methodNames = $hl->getCarrierMethods($ocCode);
							$rate->setCarrier($ocCode)->setMethod($ocMethod)->setCarrierTitle($carrierNames[$ocCode])->setMethodTitle($methodNames[$ocMethod]);
						}

						$result->append($rate);
						break;
					}
				}
			}
		}
		foreach ($items as $item) {
			$quote = $item->getQuote();
			break;
		}
		if (empty($quote)) {
			$result->append($hlp->errorResult("udsplit"));
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
		if ($details && ($details = Zend_Json::decode($details)) && !empty($details["methods"])) {
			foreach ($details["methods"] as $vId => $rate) {
				if (!empty($rate["code"])) {
					$methodCodes[$vId] = $rate["code"];
				}
			}
		}

		$totalMethod = Mage::getStoreConfig("udropship/customer/estimate_total_method");
		$details = array("version" => Mage::helper("udropship")->getVersion());
		$rates = $result->getAllRates();
		foreach ($rates as $rate) {
			if ($rate->getErrorMessage()) {
				continue;
			}

			$vId = $rate->getUdropshipVendor();
			if (!$vId) {
				continue;
			}

			$code = $rate->getCarrier() . "_" . $rate->getMethod();
			$data = array("code" => $code, "cost" => (double)$rate->getCost(), "price" => (double)$rate->getPrice(), "carrier_title" => $rate->getCarrierTitle(), "method_title" => $rate->getMethodTitle());
			if (empty($methodCodes[$vId]) && empty($details["methods"][$vId]) || !empty($methodCodes[$vId]) && $code == $methodCodes[$vId]) {
				$details["methods"][$vId] = $data;
				$cost = $hl->applyEstimateTotalCostMethod($cost, $data["cost"]);
				$price = $hl->applyEstimateTotalPriceMethod($price, $data["price"]);
			}
		}
		if ($rates) {
			$method = Mage::getModel("shipping/rate_result_method");
			$method->setCarrier("udsplit");
			$method->setCarrierTitle($carrier->getConfigData("title"));
			$method->setMethod("total");
			$method->setMethodTitle("Total");
			$method->setCost($price);
			$method->setPrice($price);
			$result->append($method);
		} else {
			$result->append($hlp->errorResult("udsplit"));
		}

		$address->setUdropshipShippingDetails(Zend_Json::encode($details));
		$address->setShippingMethod("udsplit_total");
		$address->setShippingDescription($carrier->getConfigData("title"));
		$address->setShippingAmount($price);
		Mage::dispatchEvent("udropship_carrier_collect_after", array("request" => $request, "result" => $result, "address" => $address, "details" => $details));
		return $result;
	}

}


