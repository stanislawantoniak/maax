<?php
class ZolagoOs_OmniChannel_Helper_Protected
{
	protected $_address = NULL;
	protected $_quote = NULL;
	protected $_storeId = NULL;
	protected $_store = NULL;
	protected $_quoteItemsPrepared = false;
	protected $_carriers = array();
	protected $_allowReorginizeQuote = false;
	protected $_saveQuoteFlag = false;

	final public static function validateLicense($module)
	{
	    return true;
	}

	public function prepareQuoteItems($items)
	{
		self::validateLicense("ZolagoOs_OmniChannel");
		if( !$this->startAddressPreparation($items) )
		{
			return $this;
		}

		Mage::dispatchEvent("udropship_prepare_quote_items_before", array( "items" => $items ));
		$this->applyDefaultVendorIds($items);
		$this->applyStockAvailability($items);
		Mage::helper("udropship/item")->initBaseCosts($items);
		$this->fixQuoteItemsWeight($items);
		Mage::dispatchEvent("udropship_prepare_quote_items_after", array( "items" => $items ));
		return $this;
	}

	public function startAddressPreparation($items)
	{
		foreach( $items as $item )
		{
			if( !$this->_quote )
			{
				$this->_quote = $item->getQuote();
				$this->_storeId = $this->_quote->getStoreId();
				$this->_store = Mage::app()->getStore($this->_storeId);
			}

			$address = $item->getAddress();
			$addressId = $item->getAddress() ? $item->getAddress()->getCustomerAddressId() : 0;
			break;
		}
		if( !isset($addressId) || !empty($this->_quoteItemsPrepared[$addressId]) )
		{
			return false;
		}

		$this->_quoteItemsPrepared[$addressId] = true;
		return true;
	}

	public function getQuote()
	{
		return $this->_quote;
	}

	public function getStoreId()
	{
		return $this->_storeId;
	}

	public function getStore()
	{
		return $this->_store;
	}

	public function fixQuoteItemsWeight($items)
	{
		self::validateLicense("ZolagoOs_OmniChannel");
		foreach( $items as $addressItem )
		{
			$item = $addressItem;
			if( $item->getParentItem() || $item->getProduct()->getTypeInstance()->isVirtual() )
			{
				continue;
			}

			$weightType = $item->getProduct()->getWeightType();
			$rowWeight = 0;
			if( $item->getHasChildren() && $item->isShipSeparately() )
			{
				foreach( $item->getChildren() as $child )
				{
					if( !$child->getProduct()->getTypeInstance()->isVirtual() && !$weightType )
					{
						$childRowWeight = $item->getQty() * $child->getQty() * $child->getWeight();
						$child->setFullRowWeight($childRowWeight);
						$rowWeight += $childRowWeight;
					}

				}
				if( $weightType )
				{
					$rowWeight += $item->getQty() * $item->getWeight();
				}

			}
			else
			{
				if( !$item->getProduct()->getTypeInstance()->isVirtual() )
				{
					$rowWeight += $item->getQty() * $item->getWeight();
				}

			}

			$item->setFullRowWeight($rowWeight);
		}
		return $this;
	}

	public function applyDefaultVendorIds($items)
	{
		$iHlp = Mage::helper("udropship/item");
		$localVendorId = Mage::helper("udropship")->getLocalVendorId();

		foreach( $items as $item )
		{
			$product = $item->getProduct();
			if( !$product || !$product->getUdropshipVendor() )
			{
				$product = Mage::getModel("catalog/product")->load($item->getProductId());
			}

			$iHlp->setUdropshipVendor($item, $product->getUdropshipVendor() ? $product->getUdropshipVendor() : $localVendorId);

			if( $item->getParentItem() )
			{
				$iHlp->setUdropshipVendor($item->getParentItem(), $item->getUdropshipVendor());
			}

		}
		return $this;
	}

	public function reassignApplyStockAvailability($items)
	{
		return $this->_applyStockAvailability($items, true);
	}

	public function applyStockAvailability($items)
	{
		$result = $this->_applyStockAvailability($items);
		$quote = false;
		foreach( $items as $item )
		{
			$quote = $item->getQuote();
			break;
		}
		if( $quote && Mage::getStoreConfigFlag("udropship/stock/split_bundle_by_vendors") && $this->_allowReorginizeQuote )
		{
			$this->_splitBundleByVendors($quote);
		}

		if( $this->_saveQuoteFlag && $quote )
		{
			$this->_saveQuoteFlag = false;
			$this->_allowReorginizeQuote = false;
			$quote->setUdSkipQuoteCollectTotalsEvent(true);
			$quote->getBillingAddress();
			$quote->getShippingAddress()->setCollectShippingRates(true);
			$quote->collectTotals();
			$quote->save();
			$quote->setUdSkipQuoteCollectTotalsEvent(false);
		}

		return $result;
	}

	protected function _splitBundleByVendors($quote)
	{
		$iHlp = Mage::helper("udropship/item");
		foreach( $quote->getAllItems() as $item )
		{
			if( $item->getProductType() == "bundle" && !$item->isDeleted() )
			{
				$childrenByVendor = array();
				$children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
				foreach( $children as $child )
				{
					$childrenByVendor[$child->getUdropshipVendor()][] = $child;
				}
				if( 1 < count($childrenByVendor) )
				{
					$selectionIds = $iHlp->getItemOption($item, "bundle_selection_ids");
					if( !is_array($selectionIds) )
					{
						$selectionIds = unserialize($selectionIds);
					}

					if( !is_array($selectionIds) )
					{
						continue;
					}

					$product = $item->getProduct();
					$selections = $product->getTypeInstance(true)->getSelectionsByIds($selectionIds, $product);
					$optionsByVendor = array();
					foreach( $selections as $selection )
					{
						foreach( $children as $child )
						{
							if( $child->getProductId() == $selection->getProductId() )
							{
								$optionsByVendor[$child->getUdropshipVendor()][$selection->getOptionId()][] = $selection->getSelectionId();
								break;
							}

						}
					}
					$item->isDeleted(true);
					foreach( $children as $child )
					{
						$child->isDeleted(true);
					}
					$buyRequest = $iHlp->getItemOption($item, "info_buyRequest");
					if( !is_array($buyRequest) )
					{
						$buyRequest = unserialize($buyRequest);
					}

					foreach( $optionsByVendor as $opts )
					{
						$__prod = Mage::getModel("catalog/product")->setStoreId($quote->getStoreId())->load($item->getProductId());
						$_buyRequest = $buyRequest;
						$_buyRequest["bundle_option"] = $opts;
						if( is_array($buyRequest["bundle_option_qty"]) )
						{
							$_buyRequest["bundle_option_qty"] = array();
							foreach( $buyRequest["bundle_option_qty"] as $__optId => $__optQty )
							{
								if( array_key_exists($__optId, $opts) )
								{
									$_buyRequest["bundle_option_qty"][$__optId] = $__optQty;
								}

							}
						}

						$__prod->setSkipCheckRequiredOption(true);
						$quote->addProductAdvanced($__prod, new Varien_Object($_buyRequest), Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE);
						$this->_saveQuoteFlag = true;
					}
				}

			}

		}
	}

	public function setAllowReorginizeQuote($flag)
	{
		$this->_allowReorginizeQuote = $flag;
		return $this;
	}

	public function getAllowReorginizeQuote()
	{
		return $this->_allowReorginizeQuote;
	}

	public function setSaveQuoteFlag($flag)
	{
		$this->_saveQuoteFlag = $flag;
		return $this;
	}

	protected function _applyStockAvailability($items, $isReassign = false)
	{
		Mage::unregister("inApplyStockAvailability");
		Mage::register("inApplyStockAvailability", 1, 1);
		Mage::register("reassignApplyStockAvailability", $isReassign, 1);
		if( $isReassign )
		{
			Mage::register("reassignSkipStockCheck", Mage::getStoreConfigFlag("udropship/stock/reassign_skip_stockcheck", $this->_store), 1);
		}

		try
		{
			$config = Mage::getConfig()->getNode("global/udropship/availability_methods");
			if( $isReassign )
			{
				$method = Mage::getStoreConfig("udropship/stock/reassign_availability", $this->_store);
			}

			if( empty($method) )
			{
				$method = Mage::getStoreConfig("udropship/stock/availability", $this->_store);
			}

			if( !$config->$method || $config->$method->is("disabled") )
			{
				Mage::unregister("reassignSkipStockCheck");
				Mage::unregister("inApplyStockAvailability");
				Mage::unregister("reassignApplyStockAvailability");
				return $this;
			}

			$cb = explode("::",$config->$method->callback);
			$cb[0] = Mage::getSingleton($cb[0]);
			if( empty($cb[0]) || empty($cb[1]) || !is_callable($cb) )
			{
				Mage::throwException(Mage::helper("udropship")->__("Invalid stock availability method callback: %s, %s", $method, $config->$method->callback));
			}

			call_user_func($cb, $items);
			Mage::unregister("reassignSkipStockCheck");
			Mage::unregister("inApplyStockAvailability");
			Mage::unregister("reassignApplyStockAvailability");
		}
		catch( Exception $e )
		{
			Mage::unregister("reassignSkipStockCheck");
			Mage::unregister("inApplyStockAvailability");
			Mage::unregister("reassignApplyStockAvailability");
			throw $e;
		}
	}

	public function getMinPackageWeight()
	{
		$locale = Mage::app()->getLocale();
		return $locale->getNumber(Mage::getStoreConfig("carriers/udropship/min_package_weight", $this->_store));
	}

	public function getRequestsByVendor($items, $request)
	{
		self::validateLicense("ZolagoOs_OmniChannel");
		$hlp = Mage::helper("udropship");
		$iHlp = Mage::helper("udropship/item");
		$localVendorId = $hlp->getLocalVendorId($this->_storeId);
		$requestsArr = array();
		foreach( $items as $item )
		{
			if( $item->getParentItem() || $item->getProduct()->getTypeInstance()->isVirtual() )
			{
				continue;
			}

			$this->_addInitVendorRequestItem($requestsArr, $item);
		}
		$requests = array();
		foreach( $requestsArr as $r )
		{
			$r["package_weight"] = max($r["package_weight"], $this->getMinPackageWeight());
			$r["free_method_weight"] = max($r["free_method_weight"], 0);
			$reqOrig = Mage::getModel("shipping/rate_request")->setData($request->getData())->addData($r);
			$requests[$r["vendor_id"]][$r["carrier_code"]] = $reqOrig;
			$crGroups = array();
			foreach( $reqOrig->getAllItems() as $rItem )
			{
				if( ($rProd = $rItem->getProduct()) && $rProd->getUdropshipCalculateRates() )
				{
					if( $rProd->getUdropshipCalculateRates() == ZolagoOs_OmniChannel_Model_Source::CALCULATE_RATES_ROW )
					{
						$crGroups[$rItem->getId()][] = $rItem->getId();
						$reqOrig->setCalculateRatesByGroupFlag(true);
					}
					else
					{
						if( $rProd->getUdropshipCalculateRates() == ZolagoOs_OmniChannel_Model_Source::CALCULATE_RATES_ITEM )
						{
							for( $crgIdx = 1; $crgIdx <= $rItem->getQty(); $crgIdx++ )
							{
								$crGroups[$rItem->getId() . "-" . $crgIdx][] = array( $rItem->getId() => 1 );
							}
							$reqOrig->setCalculateRatesByGroupFlag(true);
						}

					}

				}

			}
			if( $reqOrig->getCalculateRatesByGroupFlag() )
			{
				$reqOrig->setCalculateRatesGroups($crGroups);
			}

			$estCarriers = array();
			$vendor = $hlp->getVendor($r["vendor_id"]);
			foreach( $vendor->getShippingMethods(true) as $__m )
			{
				foreach( $__m as $m )
				{
					if( !empty($m["est_carrier_code"]) )
					{
						$estCarriers[$m["est_carrier_code"]] = array();
					}

					if( !empty($m["ovrd_carrier_code"]) )
					{
						$estCarriers[$m["ovrd_carrier_code"]] = array();
					}

					if( !empty($m["carrier_code"]) )
					{
						$estCarriers[$m["carrier_code"]] = array();
					}

				}
			}
			foreach( $estCarriers as $cCode => $a )
			{
				if( !empty($requests[$r["vendor_id"]][$cCode]) )
				{
					continue;
				}

				$req = clone $reqOrig;
				$req->setCarrierCode($cCode);
				$requests[$r["vendor_id"]][$cCode] = $req;
			}
		}
		return $requests;
	}

	protected function _getInitVendorRequest($vendor, $carrierCode = null)
	{
		return array( "base_subtotal_incl_tax" => 0, "package_physical_value" => 0, "package_value" => 0, "package_value_with_discount" => 0, "package_weight" => 0, "package_qty" => 0, "package_lines" => 0, "package_cost" => 0, "free_method_weight" => 0, "all_items" => array(  ), "vendor_id" => $vendor->getId(), "carrier_code" => !is_null($carrierCode) ? $carrierCode : $vendor->getCarrierCode(), "orig_country" => $vendor->getCountryId(), "orig_city" => $vendor->getCity(), "orig_postcode" => $vendor->getZip(), "orig_region_code" => $vendor->getRegionCode(), "ups_pickup" => $this->_fixUpsPickup($vendor->getUpsPickup()), "ups_container" => $this->_fixUpsContainer($vendor->getUpsContainer()), "ups_dest_type" => $this->_fixUpsDestType($vendor->getUpsDestType()) );
	}

	protected function _addInitVendorRequestItem(&$requestsArr, $item)
	{
		$hlp = Mage::helper("udropship");
		$iHlp = Mage::helper("udropship/item");
		if( $item->getHasChildren() && $item->isShipSeparately() )
		{
			$childInfoByVendor = $iHlp->getChildrenInfoByVendor($item);
			foreach( $childInfoByVendor as $vId => $cInfo )
			{
				if( empty($requestsArr[$vId]) )
				{
					$vendor = $hlp->getVendor($vId);
					$requestsArr[$vId] = $this->_getInitVendorRequest($vendor);
				}

				$this->_addVendorRequestItem($requestsArr[$vId], $item, $cInfo);
			}
		}
		else
		{
			$vendor = $iHlp->getItemVendor($item, true);
			$vId = $vendor->getId();
			if( empty($requestsArr[$vId]) )
			{
				$requestsArr[$vId] = $this->_getInitVendorRequest($vendor);
			}

			$this->_addVendorRequestItem($requestsArr[$vId], $item, $iHlp->getItemInfo($item));
		}

		return $this;
	}

	protected function _addItemToVendorRequest($vId, &$request, $item, $divider = 1)
	{
		$hlp = Mage::helper("udropship");
		$iHlp = Mage::helper("udropship/item");
		if( $item->getHasChildren() && $item->isShipSeparately() )
		{
			$this->_addVendorRequestItem($request, $item, $iHlp->getChildrenInfoByVendor($item, $vId));
		}
		else
		{
			$this->_addVendorRequestItem($request, $item, $iHlp->getItemInfo($item));
		}

		return $this;
	}

	protected function _addVendorRequestItem(&$request, $item, $itemData, $divider = 1)
	{
		if( is_array($itemData) )
		{
			$itemData = new Varien_Object($itemData);
		}

		$divider = 1 < $divider ? $divider : 1;
		$request["base_subtotal_incl_tax"] += ($itemData->getBaseRowTotal() + $itemData->getBaseTaxAmount()) / $divider;
		$request["package_physical_value"] += $itemData->getBaseRowTotal() / $divider;
		$request["package_value"] += $itemData->getBaseRowTotal() / $divider;
		$request["package_value_with_discount"] += ($itemData->getBaseRowTotal() - $itemData->getBaseDiscountAmount()) / $divider;
		$request["package_weight"] += $itemData->getFullRowWeight() / $divider;
		$request["free_method_weight"] += $itemData->getRowWeight() / $divider;
		$request["package_qty"] += $item->getQty() / $divider;
		$request["package_cost"] += ($item->getQty() * $itemData->getBaseCost()) / $divider;
		$request["package_lines"] += 1;
		if( empty($request["udropship_divider"]) )
		{
			$request["udropship_divider"] = array();
		}

		$request["udropship_divider"][$item->getId()] = $divider;
		$request["all_items"][] = $item;
		return $this;
	}

	protected function _applyRequestDivider($request)
	{
		$this->_processRequestDivider($request, true);
		return $this;
	}

	protected function _revertRequestDivider($request)
	{
		$this->_processRequestDivider($request, false);
		return $this;
	}

	protected function _processRequestDivider($request, $dir)
	{
		if( (string) $request->getData("divider_applied") != (string) $dir )
		{
			foreach( $request->getAllItems() as $item )
			{
				if( ($d = $request->getData("udropship_divider/" . $item->getId())) && 1 < $d )
				{
					$item->setData("qty", (string) $dir ? $item->getQty() / $d : $item->getQty() * $d);
				}

			}
			$request->setData("divider_applied", (string) $dir);
		}

		return $this;
	}

	protected function _fixUpsPickup($value)
	{
		$pickup = array( "RDP" => "01", "OCA" => "07", "OTP" => "06", "LC" => "19", "CC" => "03" );
		$_pickup = array_flip($pickup);
		return array_key_exists($value, $pickup) ? $value : array_key_exists($value, $_pickup) ? $_pickup[$value] : "";
	}

	protected function _fixUpsContainer($value)
	{
		$container = array( "CP" => "00", "ULE" => "01", "UT" => "03", "UEB" => "21", "UW25" => "24", "UW10" => "25" );
		$_container = array_flip($container);
		return array_key_exists($value, $container) ? $value : array_key_exists($value, $_container) ? $_container[$value] : "";
	}

	protected function _fixUpsDestType($value)
	{
		$dest_type = array( "RES" => "01", "COM" => "02" );
		$_dest_type = array_flip($dest_type);
		return array_key_exists($value, $dest_type) ? $value : array_key_exists($value, $_dest_type) ? $_dest_type[$value] : "";
	}

	public function collectVendorCarrierRates($request)
	{
		Mage::dispatchEvent("udropship_collect_vendor_rates_before", array( "request" => $request ));
		$cCode = $request->getCarrierCode();
		$store = $this->_store;
		$v = Mage::helper("udropship")->getVendor($request->getVendorId());
		$oldConfig = array();
		if( !(bool) $store->getConfig("" . "carriers/" . $cCode . "/active") )
		{
			$oldConfig["" . "carriers/" . $cCode . "/active"] = 0;
			$store->setConfig("" . "carriers/" . $cCode . "/active", 1);
		}

		if( $v->getData("use_handling_fee") )
		{
			$v->setData("__carrier_rate_request", $request);
			foreach( array( "fee", "type", "action" ) as $k )
			{
				$configKey = "" . "carriers/" . $cCode . "/handling_" . $k;
				$oldConfig[$configKey] = (bool) $store->getConfig($configKey);
				$store->setConfig($configKey, $v->getDataUsingMethod("" . "handling_" . $k));
			}
			$v->unsetData("__carrier_rate_request");
		}

		if( $request->getCalculateRatesByGroupFlag() )
		{
			$result = $this->_calculateRatesByGroup($request);
		}
		else
		{
			$result = Mage::getModel("shipping/shipping")->collectCarrierRates($cCode, $request)->getResult();
		}

		foreach( $result->getAllRates() as $rate )
		{
			$rate->setUdropshipVendor($request->getVendorId());
		}
		foreach( $oldConfig as $k => $v )
		{
			$store->setConfig($k, $v);
		}
		Mage::dispatchEvent("udropship_collect_vendor_rates_after", array( "request" => $request, "result" => $result ));
		return $result;
	}

	protected function _calculateRatesByGroup($request)
	{
		$cCode = $request->getCarrierCode();
		$store = $this->_store;
		$v = Mage::helper("udropship")->getVendor($request->getVendorId());
		$vId = $request->getVendorId();
		$crGroups = $request->getCalculateRatesGroups();
		$groupedRequests = array();
		$itemsGroupedFlags = array();
		foreach( $request->getAllItems() as $item )
		{
			foreach( $crGroups as $crGroupId => $crgItems )
			{
				foreach( $crgItems as $_crgItemId )
				{
					$crgItemId = $_crgItemId;
					$crgDivider = $crgQty = null;
					if( is_array($_crgItemId) )
					{
						reset($_crgItemId);
						$crgItemId = key($_crgItemId);
						$crgQty = current($_crgItemId);
					}

					if( $crgItemId == $item->getId() )
					{
						if( !is_null($crgQty) )
						{
							$crgDivider = $item->getQty() / $crgQty;
						}
						else
						{
							$crgDivider = 1;
							$crgQty = $item->getQty();
						}

						if( empty($groupedRequests[$crGroupId]) )
						{
							$groupedRequests[$crGroupId] = $this->_getInitVendorRequest($v, $cCode);
						}

						$this->_addItemToVendorRequest($vId, $groupedRequests[$crGroupId], $item, $crgDivider);
						if( empty($itemsGroupedFlags[$item->getId()]) )
						{
							$itemsGroupedFlags[$item->getId()] = 0;
						}

						$itemsGroupedFlags[$item->getId()] += $crgQty;
						if( $item->getQty() < $itemsGroupedFlags[$item->getId()] )
						{
							Mage::throwException(Mage::helper("udropship")->__("Grouped Rates Calculation: qty constraint failed for item (%s [SKU: %s])", $item->getName(), $item->getSku()));
						}

					}

				}
			}
		}
		$fallbackGroup = $this->_getInitVendorRequest($v, $cCode);
		$useFallbackGroupFlag = false;
		foreach( $request->getAllItems() as $item )
		{
			if( !array_key_exists($item->getId(), $itemsGroupedFlags) )
			{
				$this->_addItemToVendorRequest($vId, $fallbackGroup, $item);
				$useFallbackGroupFlag = true;
			}
			else
			{
				if( $itemsGroupedFlags[$item->getId()] < $item->getQty() )
				{
					$crgDivider = $item->getQty() / ($item->getQty() - $itemsGroupedFlags[$item->getId()]);
					$this->_addItemToVendorRequest($vId, $fallbackGroup, $item, $crgDivider);
					$useFallbackGroupFlag = true;
				}

			}

		}
		if( $useFallbackGroupFlag )
		{
			$groupedRequests[] = $fallbackGroup;
		}

		$handlingsByGroup = array();
		if( $store->getConfig("" . "carriers/" . $cCode . "/handling_type") != Mage_Shipping_Model_Carrier_Abstract::HANDLING_TYPE_PERCENT && $store->getConfig("" . "carriers/" . $cCode . "/handling_action") != Mage_Shipping_Model_Carrier_Abstract::HANDLING_ACTION_PERPACKAGE )
		{
			foreach( $groupedRequests as $groupId => $group )
			{
				$handlingsByGroup[$groupId] = $store->getConfig("" . "carriers/" . $cCode . "/handling_fee");
				$handlingsByGroup[$groupId] *= $group["package_weight"] / $request->getPackageWeight();
			}
		}

		$groupedResults = array();
		$processedGroups = array();
		foreach( $groupedRequests as $groupId => $group )
		{
			$group["package_weight"] = max($group["package_weight"], $this->getMinPackageWeight());
			$group["free_method_weight"] = max($group["free_method_weight"], 0);
			$oldHandlingFee = $store->getConfig("" . "carriers/" . $cCode . "/handling_fee");
			if( isset($handlingsByGroup[$groupId]) )
			{
				$store->setConfig("" . "carriers/" . $cCode . "/handling_fee", $handlingsByGroup[$groupId]);
			}

			$__request = Mage::getModel("shipping/rate_request")->setData($request->getData())->addData($group);
			if( ($processedGID = array_search($group, $processedGroups, true)) && (empty($handlingsByGroup) || $handlingsByGroup[$groupId] == $handlingsByGroup[$processedGID]) )
			{
				$groupedResults[$groupId] = $groupedResults[$processedGID];
			}
			else
			{
				$this->_applyRequestDivider($__request);
				$groupedResults[$groupId] = Mage::getModel("shipping/shipping")->collectCarrierRates($cCode, $__request)->getResult();
				$this->_revertRequestDivider($__request);
				$processedGroups[$groupId] = $group;
			}

			$store->setConfig("" . "carriers/" . $cCode . "/handling_fee", $oldHandlingFee);
		}
		$resultsByCode = array();
		foreach( $groupedResults as $groupId => $groupResult )
		{
			$groupedRequests[$groupId]["all_items"] = null;
			foreach( $groupResult->getAllRates() as $groupRate )
			{
				if( $groupRate instanceof Mage_Shipping_Model_Rate_Result_Method )
				{
					$code = $groupRate->getCarrier() . "_" . $groupRate->getMethod();
					if( empty($resultsByCode[$code]) )
					{
						$resultsByCode[$code] = array();
					}

					$resultsByCode[$code][$groupId] = clone $groupRate;
				}

			}
		}
		$result = Mage::getModel("shipping/rate_result");
		foreach( $resultsByCode as $code => $ratesByGroup )
		{
			if( count($ratesByGroup) == count($groupedRequests) )
			{
				$finalRate = null;
				foreach( $ratesByGroup as $groupRate )
				{
					if( is_null($finalRate) )
					{
						$finalRate = $groupRate;
					}
					else
					{
						$finalRate->setCost($finalRate->getCost() + $groupRate->getCost());
						$finalRate->setPrice($finalRate->getPrice() + $groupRate->getPrice());
					}

				}
				if( !is_null($finalRate) )
				{
					$result->append($finalRate);
				}

			}

		}
		return $result;
	}

	public function errorResult($carrierCode = "udropship", $errorMessage = null)
	{
		if( !$this->_store )
		{
			$this->_store = Mage::app()->getStore($this->_storeId);
		}

		$result = Mage::getModel("shipping/rate_result");
		$error = Mage::getModel("shipping/rate_result_error");
		$error->setCarrier($carrierCode);
		$error->setCarrierTitle($this->_store->getConfig("carriers/udropship/title"));
		$defMessage = $this->_store->getConfig("carriers/udropship/specificerrmsg");
		$error->setErrorMessage($defMessage || !$errorMessage ? $defMessage : $errorMessage);
		$result->append($error);
		return $result;
	}

	protected function _needToAddDummy($item, $qtys = array(  ))
	{
		if( $item->getHasChildren() )
		{
			$children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
			foreach( $children as $child )
			{
				if( empty($qtys) )
				{
					if( 0 < $child->getQtyToShip() )
					{
						return true;
					}

				}
				else
				{
					if( isset($qtys[$child->getId()]) && 0 < $qtys[$child->getId()] )
					{
						return true;
					}

				}

			}
			return false;
		}

		if( $parent = $item->getParentItem() )
		{
			if( empty($qtys) )
			{
				if( 0 < $parent->getQtyToShip() )
				{
					return true;
				}

			}
			else
			{
				if( isset($qtys[$parent->getId()]) && 0 < $qtys[$parent->getId()] )
				{
					return true;
				}

			}

			return false;
		}

	}

	protected function _cancelOrder($order)
	{
		foreach( $order->getShipmentsCollection() as $shipment )
		{
			$shipment->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED)->save();
		}
		$order->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_CANCELED)->save();
		return $this;
	}

	public function sales_order_save_after($observer)
	{
		$order = $observer->getEvent()->getOrder();
		$enableVirtual = Mage::getStoreConfig("udropship/misc/enable_virtual", $order->getStoreId());
		if( Mage::helper("udropship")->isUdpoActive() )
		{
			$enableVirtual = true;
		}

		$shippingMethod = Mage::helper("udropship")->explodeOrderShippingMethod($order);
		$shippingDetails = $order->getUdropshipShippingDetails();
		if( !$shippingDetails && !$enableVirtual )
		{
			return NULL;
		}

		$rHlp = Mage::getResourceSingleton("udropship/helper");
		$oUdStatus = Mage::getStoreConfigFlag("udropship/admin/for_update_split_check") ? $rHlp->loadModelFieldForUpdate($order, "udropship_status") : $rHlp->loadModelField($order, "udropship_status");
		if( $order->getUdropshipStatus() == ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_PENDING && $oUdStatus != ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_PENDING )
		{
			$order->setUdropshipStatus($oUdStatus);
		}

		if( $order->getUdropshipStatus() != ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_PENDING )
		{
			if( $order->getUdropshipStatus() == ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_NOTIFIED && $order->getState() == Mage_Sales_Model_Order::STATE_CANCELED )
			{
				$this->_cancelOrder($order);
			}

		}
		else
		{
			if( !$this->_fixGoolecheckout($order) )
			{
				return NULL;
			}

			$this->_setHasMultipleVendors($order);
			$statuses = explode(",", Mage::getStoreConfig("udropship/vendor/make_available_to_dropship", $order->getStoreId()));
			if( !in_array($order->getStatus(), $statuses) )
			{
				return NULL;
			}

			$this->_normalizeOrderShippingDetails($order);
			$oldStatus = $order->getUdropshipStatus();
			try
			{
				$order->setUdropshipOrderSplitFlag(false);
				$order->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_NOTIFIED);
				$order->getResource()->saveAttribute($order, "udropship_status");
				$this->splitOrder($order);
			}
			catch( Exception $e )
			{
				if( !$order->getUdropshipOrderSplitFlag() )
				{
					$order->setUdropshipStatus($oldStatus);
					$order->getResource()->saveAttribute($order, "udropship_status");
				}

				throw $e;
			}
		}

	}

	public function fixGoolecheckout($order)
	{
		return $this->_fixGoolecheckout($order);
	}

	protected function _fixGoolecheckout($order)
	{
		$shippingMethod = Mage::helper("udropship")->explodeOrderShippingMethod($order);
		if( $shippingMethod[0] == "googlecheckout" )
		{
			$updated = false;
			$title = Mage::getStoreConfig("carriers/udropship/title");
			$descr = $order->getShippingDescription();
			if( preg_match("#(?:^| - )" . preg_quote($title) . " - (.*)\$#", $descr, $match) )
			{
				$tries = array( $match[1] );
				$_dummy = explode(" [", $match[1]);
				if( !empty($_dummy[1]) )
				{
					$_dummy[1] = rtrim($_dummy[1], "]");
					array_unshift($tries, $_dummy[0]);
				}

				foreach( $tries as $_try )
				{
					$shipping = Mage::getModel("udropship/shipping")->load($_try, "shipping_title");
					if( $shipping->getId() )
					{
						$shippingMethod[1] = $shipping->getShippingCode();
						$newSM = "udropship_" . $shipping->getShippingCode();
						if( !empty($_dummy[1]) )
						{
							$carrierNames = Mage::getSingleton("udropship/source")->getCarriers();
							$carrierNamesRev = array_flip($carrierNames);
							$__dummy = explode(" - ", $_dummy[1]);
							if( !empty($__dummy[1]) && !empty($carrierNamesRev[$__dummy[0]]) )
							{
								$cMethodNames = Mage::helper("udropship")->getCarrierMethods($carrierNamesRev[$__dummy[0]]);
								$cMethodNamesRev = array_flip($cMethodNames);
								if( !empty($cMethodNamesRev[$__dummy[1]]) )
								{
									$newSM .= "___" . $carrierNamesRev[$__dummy[0]] . "_" . $cMethodNamesRev[$__dummy[1]];
								}

							}

						}

						$order->setShippingMethod($newSM);
						$updated = true;
						break;
					}

				}
			}

			if( !$updated )
			{
				Mage::log("" . "Couldn't find dropship method for Google Checkout: " . $shippingMethod . " (" . $descr . ")");
				return false;
			}

		}

		return true;
	}

	protected function _setHasMultipleVendors($order)
	{
		$items = $order->getAllItems();
		$vIds = array();
		foreach( $items as $orderItem )
		{
			if( $orderItem->getHasChildren() )
			{
				continue;
			}

			$vId = $orderItem->getUdropshipVendor();
			$vIds[$vId] = true;
		}
		$order->setHasMultipleVendors(1 < sizeof($vIds));
	}

	protected function _normalizeOrderShippingDetails($order)
	{
		$order->setUdropshipShippingDetails(Zend_Json::encode(array( "methods" => $this->getOrderVendorRates($order) )));
	}

	public function getOrderVendorRates($order)
	{
		$vendorRates = array();
		$shippingMethod = explode("_", $order->getShippingMethod(), 2);
		$shippingDetails = $order->getUdropshipShippingDetails();
		$details = Zend_Json::decode($shippingDetails);
		if( !empty($details) && !empty($shippingMethod[1]) )
		{
			if( !empty($details["methods"][$shippingMethod[1]]) )
			{
				$vendorRates = $details["methods"][$shippingMethod[1]]["vendors"];
			}
			else
			{
				if( !empty($details["methods"]) )
				{
					$vendorRates = $details["methods"];
				}

			}

		}

		return $vendorRates;
	}

	public function splitOrder($order)
	{
		if( Mage::helper("udropship")->isUdpoActive() )
		{
			Mage::helper("udpo")->splitOrderToPos($order);
		}
		else
		{
			$this->splitOrderToShipments($order);
		}

		return $this;
	}

	public function splitOrderToShipments($order, $qtys = array(  ))
	{
		$hlp = Mage::helper("udropship");
		$convertor = Mage::getModel("sales/convert_order");
		$enableVirtual = Mage::getStoreConfig("udropship/misc/enable_virtual", $order->getStoreId());
		$shippingMethod = explode("_", $order->getShippingMethod(), 2);
		$vendorRates = $this->getOrderVendorRates($order);
		$items = $order->getAllItems();
		$shipping = $hlp->getShippingMethods();
		$orderToPoItemMap = array();
		$shipments = array();
		foreach( $items as $orderItem )
		{
			if( !$orderItem->isDummy(true) && !$orderItem->getQtyToShip() )
			{
				continue;
			}

			if( $orderItem->isDummy(true) && !$this->_needToAddDummy($orderItem, $qtys) )
			{
				continue;
			}

			if( $orderItem->getIsVirtual() && !$enableVirtual )
			{
				continue;
			}

			$vIds = array();
			if( $orderItem->getHasChildren() )
			{
				$children = $orderItem->getChildrenItems() ? $orderItem->getChildrenItems() : $orderItem->getChildren();
				foreach( $children as $child )
				{
					$udpoKey = $child->getUdropshipVendor();
					if( Mage::helper("udropship")->isSeparateShipment($child) && $orderItem->isShipSeparately() )
					{
						$udpoKey .= "-" . ($child->getUdpoSeqNumber() ? $child->getUdpoSeqNumber() : $child->getId());
					}
					else
					{
						if( Mage::helper("udropship")->isSeparateShipment($orderItem) )
						{
							$udpoKey .= "-" . ($orderItem->getUdpoSeqNumber() ? $orderItem->getUdpoSeqNumber() : $orderItem->getId());
						}

					}

					$vIds[$udpoKey] = $child->getUdropshipVendor();
				}
			}
			else
			{
				$udpoKey = $orderItem->getUdropshipVendor();
				$oiParent = $orderItem->getParentItem();
				if( Mage::helper("udropship")->isSeparateShipment($orderItem) && (!$oiParent || $oiParent->isShipSeparately()) )
				{
					$udpoKey .= "-" . ($orderItem->getUdpoSeqNumber() ? $orderItem->getUdpoSeqNumber() : $orderItem->getId());
				}
				else
				{
					if( $oiParent && Mage::helper("udropship")->isSeparateShipment($oiParent) )
					{
						$udpoKey .= "-" . ($oiParent->getUdpoSeqNumber() ? $oiParent->getUdpoSeqNumber() : $oiParent->getId());
					}

				}

				$vIds[$udpoKey] = $orderItem->getUdropshipVendor();
			}

			foreach( $vIds as $udpoKey => $vId )
			{
				$vendor = $hlp->getVendor($vId);
				if( empty($shipments[$udpoKey]) )
				{
					$shipmentStatus = (int) Mage::getStoreConfig("udropship/vendor/default_shipment_status", $order->getStoreId());
					if( "999" != $vendor->getData("initial_shipment_status") )
					{
						$shipmentStatus = $vendor->getData("initial_shipment_status");
					}

					$shipments[$udpoKey] = $convertor->toShipment($order)->setUdropshipVendor($vId)->setUdropshipStatus($shipmentStatus)->setTotalQty(0)->setShippingAmount(0)->setBaseShippingAmount(0)->setShippingAmountIncl(0)->setBaseShippingAmountIncl(0)->setShippingTax(0)->setBaseShippingTax(0);
					if( !empty($vendorRates[$vId]) || !empty($vendorRates[$udpoKey]) || !empty($vendorRates[$vId]["rates_by_seq_number"][$orderItem->getUdpoSeqNumber()]) )
					{
						if( !empty($vendorRates[$udpoKey]) && $udpoKey != $vId )
						{
							$udpoNoSplitWeights[$vId . "-"] = true;
							$v = $vendorRates[$udpoKey];
						}
						else
						{
							if( !empty($vendorRates[$vId]["rates_by_seq_number"][$orderItem->getUdpoSeqNumber()]) )
							{
								$udpoNoSplitWeights[$vId . "-"] = true;
								$v = $vendorRates[$vId]["rates_by_seq_number"][$orderItem->getUdpoSeqNumber()];
							}
							else
							{
								$v = $vendorRates[$vId];
							}

						}

						$_orderRate = 0 < $order->getBaseToOrderRate() ? $order->getBaseToOrderRate() : 1;
						$_baseSa = isset($v["price_excl"]) ? $v["price_excl"] : $v["price"];
						$_sa = Mage::app()->getStore()->roundPrice($_orderRate * $_baseSa);
						if( !($_baseSaIncl = $v["price_incl"]) )
						{
							$_baseSaIncl = $_baseSa;
						}

						$_saIncl = Mage::app()->getStore()->roundPrice($_orderRate * $_baseSaIncl);
						$_baseSaTax = $v["tax"];
						$_saTax = Mage::app()->getStore()->roundPrice($_orderRate * $_baseSaTax);
						$shipments[$udpoKey]->setShippingAmount($_sa)->setBaseShippingAmount($_baseSa)->setShippingAmountIncl($_saIncl)->setBaseShippingAmountIncl($_baseSaIncl)->setShippingTax($_saTax)->setBaseShippingTax($_baseSaTax)->setUdropshipMethod($v["code"])->setUdropshipMethodDescription(!empty($v["carrier_title"]) ? $v["carrier_title"] . " - " . $v["method_title"] : $v["code"]);
					}
					else
					{
						$vShipping = $vendor->getShippingMethods();
						$uMethod = explode("_", $order->getShippingMethod(), 2);
						$uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : "";
						$curShipping = $shipping->getItemByColumnValue("shipping_code", $uMethodCode);
						if( $curShipping && isset($vShipping[$curShipping->getId()]) )
						{
							$curShipping->useProfile($vendor);
							foreach( $vShipping[$curShipping->getId()] as $__vs )
							{
								$carrierCode = $__vs["carrier_code"];
								$methodCode = !empty($__vs["method_code"]) ? $__vs["method_code"] : $curShipping->getSystemMethods($__vs["carrier_code"]);
								$carrierMethods = Mage::helper("udropship")->getCarrierMethods($carrierCode);
								$shipments[$udpoKey]->setUdropshipMethod($carrierCode . "_" . $methodCode)->setUdropshipMethodDescription(Mage::getStoreConfig("carriers/" . $carrierCode . "/title", $order->getStoreId()) . " - " . $carrierMethods[$methodCode]);
								break;
							}
							$curShipping->resetProfile();
						}

					}

				}

				if( $orderItem->isDummy(true) )
				{
					if( $_parentItem = $orderItem->getParentItem() )
					{
						$qty = $orderItem->getQtyOrdered() / $_parentItem->getQtyOrdered();
					}
					else
					{
						$qty = 1;
					}

				}
				else
				{
					if( isset($qtys[$orderItem->getId()]) )
					{
						$qty = $qtys[$orderItem->getId()];
					}
					else
					{
						$qty = $orderItem->getQtyToShip();
					}

				}

				$item = $convertor->itemToShipmentItem($orderItem)->setQty($qty);
				if( !$orderItem->getHasChildren() || $orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE )
				{
					if( abs($orderItem->getBaseCost()) < 0.001 )
					{
						$item->setBaseCost($orderItem->getBasePrice());
					}
					else
					{
						$item->setBaseCost($orderItem->getBaseCost());
					}

				}

				$shipments[$udpoKey]->addItem($item);
				$orderToPoItemMap[$orderItem->getId() . "-" . $vId] = $item;
				$_totQty = $item->getQty();
				if( ($_parentItem = $orderItem->getParentItem()) && isset($orderToPoItemMap[$_parentItem->getId() . "-" . $vId]) )
				{
					$_totQty *= $orderToPoItemMap[$_parentItem->getId() . "-" . $vId]->getQty();
				}

				if( !$orderItem->isDummy(true) )
				{
					$qtyOrdered = $orderItem->getQtyOrdered();
					$_rowDivider = $_totQty / (0 < $qtyOrdered ? $qtyOrdered : 1);
					$iTax = $orderItem->getBaseTaxAmount() * (0 < $_rowDivider ? $_rowDivider : 1);
					$iDiscount = $orderItem->getBaseDiscountAmount() * (0 < $_rowDivider ? $_rowDivider : 1);
					$shipments[$udpoKey]->setBaseTaxAmount($shipments[$udpoKey]->getBaseTaxAmount() + $iTax)->setBaseDiscountAmount($shipments[$udpoKey]->getBaseDiscountAmount() + $iDiscount)->setBaseTotalValue($shipments[$udpoKey]->getBaseTotalValue() + $orderItem->getBasePrice() * $_totQty)->setTotalValue($shipments[$udpoKey]->getTotalValue() + $orderItem->getPrice() * $_totQty)->setTotalQty($shipments[$udpoKey]->getTotalQty() + $qty);
				}

				if( $orderItem->getParentItem() )
				{
					$weightType = $orderItem->getParentItem()->getProductOptionByCode("weight_type");
					if( null !== $weightType && !$weightType )
					{
						$shipments[$udpoKey]->setTotalWeight($shipments[$udpoKey]->getTotalWeight() + $orderItem->getWeight() * $_totQty);
					}

				}
				else
				{
					$weightType = $orderItem->getProductOptionByCode("weight_type");
					if( null === $weightType || $weightType )
					{
						$shipments[$udpoKey]->setTotalWeight($shipments[$udpoKey]->getTotalWeight() + $orderItem->getWeight() * $_totQty);
					}

				}

				if( !$orderItem->getHasChildren() )
				{
					$shipments[$udpoKey]->setTotalCost($shipments[$udpoKey]->getTotalCost() + $item->getBaseCost() * $_totQty);
				}

				$shipments[$udpoKey]->setCommissionPercent($vendor->getCommissionPercent());
				$shipments[$udpoKey]->setTransactionFee($vendor->getTransactionFee());
			}
		}
		Mage::dispatchEvent("udropship_order_save_before", array( "order" => $order, "shipments" => $shipments ));
		$order->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_NOTIFIED);
		$udpoSplitWeights = array();
		foreach( $shipments as $_vUdpoKey => $_vUdpo )
		{
			if( empty($udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]) )
			{
				$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["weights"] = array();
				$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["total_weight"] = 0;
			}

			$weight = 0 < $_vUdpo->getTotalWeight() ? $_vUdpo->getTotalWeight() : 0.001;
			$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["weights"][$_vUdpoKey] = $weight;
			$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["total_weight"] += $weight;
		}
		$transaction = Mage::getModel("core/resource_transaction");
		foreach( $shipments as $udpoKey => $shipment )
		{
			Mage::helper("udropship")->addVendorSkus($shipment);
			if( empty($udpoNoSplitWeights[$shipment->getUdropshipVendor() . "-"]) && !empty($udpoSplitWeights[$shipment->getUdropshipVendor() . "-"]["weights"][$udpoKey]) && 1 < count($udpoSplitWeights[$shipment->getUdropshipVendor() . "-"]["weights"]) )
			{
				$_splitWeight = $udpoSplitWeights[$shipment->getUdropshipVendor() . "-"]["weights"][$udpoKey];
				$_totalWeight = $udpoSplitWeights[$shipment->getUdropshipVendor() . "-"]["total_weight"];
				$shipment->setShippingAmount(($shipment->getShippingAmount() * $_splitWeight) / $_totalWeight);
				$shipment->setBaseShippingAmount(($shipment->getBaseShippingAmount() * $_splitWeight) / $_totalWeight);
				$shipment->setShippingAmountIncl(($shipment->getShippingAmountIncl() * $_splitWeight) / $_totalWeight);
				$shipment->setBaseShippingAmountIncl(($shipment->getBaseShippingAmountIncl() * $_splitWeight) / $_totalWeight);
				$shipment->setShippingTax(($shipment->getShippingTax() * $_splitWeight) / $_totalWeight);
				$shipment->setBaseShippingTax(($shipment->getBaseShippingTax() * $_splitWeight) / $_totalWeight);
			}

			$transaction->addObject($shipment);
		}
		$transaction->addObject($order)->save();
		$order->setUdropshipOrderSplitFlag(true);
		Mage::dispatchEvent("udropship_order_save_after", array( "order" => $order, "shipments" => $shipments ));
		foreach( $shipments as $shipment )
		{
			$hlp->sendVendorNotification($shipment);
		}
		$hlp->processQueue();
	}

	public function arrayCompare($array1, $array2)
	{
		$diff = false;
		foreach( $array1 as $key => $value )
		{
			if( !array_key_exists($key, $array2) )
			{
				$diff[0][$key] = $value;
			}
			else
			{
				if( is_array($value) )
				{
					if( !is_array($array2[$key]) )
					{
						$diff[0][$key] = $value;
						$diff[1][$key] = $array2[$key];
					}
					else
					{
						$new = $this->arrayCompare($value, $array2[$key]);
						if( $new !== false )
						{
							if( isset($new[0]) )
							{
								$diff[0][$key] = $new[0];
							}

							if( isset($new[1]) )
							{
								$diff[1][$key] = $new[1];
							}

						}

					}

				}
				else
				{
					if( $array2[$key] !== $value )
					{
						$diff[0][$key] = $value;
						$diff[1][$key] = $array2[$key];
					}

				}

			}

		}
		foreach( $array2 as $key => $value )
		{
			if( !array_key_exists($key, $array1) )
			{
				$diff[1][$key] = $value;
			}

		}
		return $diff;
	}

}