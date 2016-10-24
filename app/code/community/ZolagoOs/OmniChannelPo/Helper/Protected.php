<?php

class ZolagoOs_OmniChannelPo_Helper_Protected
{
	public function splitOrderToPos($order, $qtys = array(), $comment = "")
	{
		ZolagoOs_OmniChannel_Helper_Protected::validateLicense("ZolagoOs_OmniChannelPo");
		if (!Mage::helper("udropship")->isActive($order->getStore())) {
			return false;
		}

		if (($postOrderData = Mage::app()->getRequest()->getPost("order")) && !empty($postOrderData["noautopo_flag"])) {
			$order->setData("noautopo_flag", $postOrderData["noautopo_flag"])->getResource()->saveAttribute($order, "noautopo_flag");
		}

		if ($order->getData("noautopo_flag") && !$order->getIsManualPoFlag()) {
			return false;
		}

		$hlp = Mage::helper("udropship");
		$hlpd = Mage::helper("udropship/protected");
		$poHlp = Mage::helper("udpo");
		$shippingMethod = Mage::helper("udropship")->explodeOrderShippingMethod($order);
		if ($order->hasUdpoVendorRates()) {
			$vendorRates = $order->getUdpoVendorRates();
		} else {
			$vendorRates = $hlpd->getOrderVendorRates($order);
		}

		$poHlp->initOrderUdposCollection($order);
		$udpoIndex = $order->getUdposCollection()->count();
		$items = $order->getAllItems();
		$shipping = $hlp->getShippingMethods();
		$udpoIncrement = Mage::getStoreConfig("udropship/purchase_order/po_increment_type", $order->getStoreId());
		$udpos = array();
		$isVirtual = array();
		$udpoNoSplitWeights = array();
		$canPoItemFlags = array();
		foreach ($items as $orderItem) {
			$canPoItemFlags[$orderItem->getId()] = $poHlp->canPoItem($orderItem, $qtys);
		}
		$orderToPoItemMap = array();
		foreach ($items as $orderItem) {
			if (empty($canPoItemFlags[$orderItem->getId()])) {
				continue;
			}

			$vIds = array();
			if ($orderItem->getHasChildren()) {
				$children = $orderItem->getChildrenItems() ? $orderItem->getChildrenItems() : $orderItem->getChildren();
				foreach ($children as $child) {
					if ($child->hasUdpoUdropshipVendor()) {
						$_vId = $child->getUdpoUdropshipVendor();
					} else {
						$_vId = $child->getUdropshipVendor();
					}

					$udpoKey = $_vId;
					if (!$order->getUdpoNoSplitPoFlag()) {
						if (Mage::helper("udropship")->isSeparatePo($child) && $orderItem->isShipSeparately()) {
							$udpoKey .= "-" . ($child->getUdpoSeqNumber() ? $child->getUdpoSeqNumber() : $child->getId());
						} else {
							if (Mage::helper("udropship")->isSeparatePo($orderItem)) {
								$udpoKey .= "-" . ($orderItem->getUdpoSeqNumber() ? $orderItem->getUdpoSeqNumber() : $orderItem->getId());
							}
						}
					}

					$vIds[$udpoKey] = $_vId;
				}
			} else {
				if ($orderItem->hasUdpoUdropshipVendor()) {
					$_vId = $orderItem->getUdpoUdropshipVendor();
				} else {
					$_vId = $orderItem->getUdropshipVendor();
				}

				$udpoKey = $_vId;
				if (!$order->getUdpoNoSplitPoFlag()) {
					$oiParent = $orderItem->getParentItem();
					if (Mage::helper("udropship")->isSeparatePo($orderItem) && (!$oiParent || $oiParent->isShipSeparately())) {
						$udpoKey .= "-" . ($orderItem->getUdpoSeqNumber() ? $orderItem->getUdpoSeqNumber() : $orderItem->getId());
					} else {
						if ($oiParent && Mage::helper("udropship")->isSeparatePo($oiParent)) {
							$udpoKey .= "-" . ($oiParent->getUdpoSeqNumber() ? $oiParent->getUdpoSeqNumber() : $oiParent->getId());
						}
					}
				}

				$vIds[$udpoKey] = $_vId;
			}

			foreach ($vIds as $udpoKey => $vId) {
				$vendor = $hlp->getVendor($vId);
				if (!isset($isVirtual[$udpoKey])) {
					$isVirtual[$udpoKey] = true;
				}

				if (!$orderItem->getIsVirtual()) {
					$isVirtual[$udpoKey] = false;
				}

				if (empty($udpos[$udpoKey])) {
					$udpoStatus = (int)Mage::getStoreConfig("udropship/purchase_order/default_po_status", $order->getStoreId());
					if ("999" != $vendor->getData("initial_po_status")) {
						$udpoStatus = $vendor->getData("initial_po_status");
					}

					$udpos[$udpoKey] = $poHlp
						->toUdpo($order)
						->setUdropshipVendor($vId)
						->setUdropshipStatus($udpoStatus)
						->setTotalQty(0)
						->setShippingAmount(0)
						->setBaseShippingAmount(0)
						->setShippingAmountIncl(0)
						->setBaseShippingAmountIncl(0)
						->setShippingTax(0)
						->setBaseShippingTax(0)
						->setIsManual($order->getIsManualPoFlag());
					
					if ($udpoIncrement == ZolagoOs_OmniChannelPo_Model_Source::UDPO_INCREMENT_ORDER_BASED) {
						$udpoIndex++;
						$udpos[$udpoKey]->setIncrementId(sprintf("%s-%s", $order->getIncrementId(), $udpoIndex));
					}
					Mage::log("vendorRates: ", null, "po.log");
					Mage::log($vendorRates, null, "po.log");
					if (!empty($vendorRates[$vId]) ||
						!empty($vendorRates[$udpoKey]) ||
						!empty($vendorRates[$vId]["rates_by_seq_number"][$orderItem->getUdpoSeqNumber()]))
					{
						if (!empty($vendorRates[$udpoKey]) && $udpoKey != $vId) {
							$udpoNoSplitWeights[$vId . "-"] = true;
							$v = $vendorRates[$udpoKey];
						} else {
							if (!empty($vendorRates[$vId]["rates_by_seq_number"][$orderItem->getUdpoSeqNumber()])) {
								$udpoNoSplitWeights[$vId . "-"] = true;
								$v = $vendorRates[$vId]["rates_by_seq_number"][$orderItem->getUdpoSeqNumber()];
							} else {
								$v = $vendorRates[$vId];
							}
						}
						Mage::log("vendorRates: ", null, "po.log");
						Mage::log($vendorRates, null, "po.log");

						Mage::log("v: ", null, "po.log");
						Mage::log($v, null, "po.log");

						$_orderRate = 0 < $order->getBaseToOrderRate() ? $order->getBaseToOrderRate() : 1;
						$_um = !empty($v["udpo_method"]) ? $v["udpo_method"] : $v["code"];
						$_umd = !empty($v["udpo_method"]) ? !empty($v["udpo_method_name"]) ? $v["udpo_method_name"] : $v["udpo_method"] : !empty($v["carrier_title"]) ? $v["carrier_title"] . " - " . $v["method_title"] : $v["code"];
						$__shipPrice = isset($v["price_excl"]) ? $v["price_excl"] : $v["price"];
						if (!($__shipPriceIncl = $v["price_incl"])) {
							$__shipPriceIncl = $__shipPrice;
						}

						$__shipPriceTax = $v["tax"];
						Mage::log("order Rate: " . ($_orderRate), null, "po.log");
						Mage::log("setShippingAmount: " . ($_orderRate * $__shipPrice), null, "po.log");

						Mage::log("setBaseShippingAmount: " . ($__shipPrice), null, "po.log");
						Mage::log("setBaseShippingAmountIncl: " . ($_orderRate * $__shipPriceIncl), null, "po.log");

						Mage::log("setBaseShippingAmountIncl: " . ($__shipPriceIncl), null, "po.log");
						Mage::log("setShippingTax: " . ($_orderRate * $__shipPriceTax), null, "po.log");

						Mage::log("setBaseShippingTax: " . ($__shipPriceTax), null, "po.log");

						$udpos[$udpoKey]->setShippingAmount($_orderRate * $__shipPrice)
							->setBaseShippingAmount($__shipPrice)
							->setShippingAmountIncl($_orderRate * $__shipPriceIncl)
							->setBaseShippingAmountIncl($__shipPriceIncl)
							->setShippingTax($_orderRate * $__shipPriceTax)
							->setBaseShippingTax($__shipPriceTax)
							->setUdropshipMethod($_um)
							->setUdropshipMethodDescription($_umd);
					} else {
						$vShipping = $vendor->getShippingMethods();
						$uMethod = explode("_", $order->getShippingMethod(), 2);
						$uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : "";
						$curShipping = $shipping->getItemByColumnValue("shipping_code", $uMethodCode);
						if ($curShipping && isset($vShipping[$curShipping->getId()])) {
							$curShipping->useProfile($vendor);
							foreach ($vShipping[$curShipping->getId()] as $__vs) {
								$carrierCode = $__vs["carrier_code"];
								$methodCode = !empty($__vs["method_code"]) ? $__vs["method_code"] : $curShipping->getSystemMethods($__vs["carrier_code"]);
								$carrierMethods = Mage::helper("udropship")->getCarrierMethods($carrierCode);
								$udpos[$udpoKey]->setUdropshipMethod($carrierCode . "_" . $methodCode)->setUdropshipMethodDescription(Mage::getStoreConfig("carriers/" . $carrierCode . "/title", $order->getStoreId()) . " - " . $carrierMethods[$methodCode]);
								break;
							}
							$curShipping->resetProfile();
						}
					}
				}

				if ($orderItem->isDummy(true)) {
					if ($_parentItem = $orderItem->getParentItem()) {
						$qty = $orderItem->getQtyOrdered() / $_parentItem->getQtyOrdered();
					} else {
						$qty = 1;
					}

				} else {
					if (isset($qtys[$orderItem->getId()])) {
						$qty = $qtys[$orderItem->getId()];
					} else {
						$qty = $poHlp->getOrderItemQtyToUdpo($orderItem);
					}
				}

				$item = $poHlp->itemToUdpoItem($orderItem)->setQty($qty);
				$orderToPoItemMap[$orderItem->getId() . "-" . $udpoKey] = $item;
				if (!$orderItem->getHasChildren() || $orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
					if (0.001 < abs($orderItem->getUdpoBaseCost())) {
						$item->setBaseCost($orderItem->getUdpoBaseCost());
					} else {
						if (abs($orderItem->getBaseCost()) < 0.001) {
							$item->setBaseCost($orderItem->getBasePrice());
						}
					}
				}

				$orderItem->setQtyUdpo($orderItem->getQtyUdpo() + $item->getQty());
				$udpos[$udpoKey]->addItem($item);
				$_totQty = $item->getQty();
				if (($_parentItem = $orderItem->getParentItem()) && isset($orderToPoItemMap[$_parentItem->getId() . "-" . $udpoKey])) {
					$_totQty *= $orderToPoItemMap[$_parentItem->getId() . "-" . $udpoKey]->getQty();
				}

				if (!$orderItem->isDummy(true)) {
					$qtyOrdered = $orderItem->getQtyOrdered();
					$_rowDivider = $_totQty / (0 < $qtyOrdered ? $qtyOrdered : 1);
					$iTax = $orderItem->getBaseTaxAmount() * (0 < $_rowDivider ? $_rowDivider : 1);

					Mage::log("getBaseTaxAmount: " . ($orderItem->getBaseTaxAmount()), null, "po.log");
					Mage::log("iTax: " . ($iTax), null, "po.log");

					$iDiscount = $orderItem->getBaseDiscountAmount() * (0 < $_rowDivider ? $_rowDivider : 1);
					$udpos[$udpoKey]->setBaseTaxAmount($udpos[$udpoKey]->getBaseTaxAmount() + $iTax)
						->setBaseDiscountAmount($udpos[$udpoKey]->getBaseDiscountAmount() + $iDiscount)
						->setBaseTotalValue($udpos[$udpoKey]->getBaseTotalValue() + $orderItem->getBasePrice() * $_totQty)
						->setTotalValue($udpos[$udpoKey]->getTotalValue() + $orderItem->getPrice() * $_totQty)
						->setTotalQty($udpos[$udpoKey]->getTotalQty() + $_totQty);

				}

				if ($orderItem->getParentItem()) {
					$weightType = $orderItem->getParentItem()->getProductOptionByCode("weight_type");
					if (null !== $weightType && !$weightType) {
						$udpos[$udpoKey]->setTotalWeight($udpos[$udpoKey]->getTotalWeight() + $orderItem->getWeight() * $_totQty);
					}
				} else {
					$weightType = $orderItem->getProductOptionByCode("weight_type");
					if (null === $weightType || $weightType) {
						$udpos[$udpoKey]->setTotalWeight($udpos[$udpoKey]->getTotalWeight() + $orderItem->getWeight() * $_totQty);
					}
				}

				if (!$orderItem->getHasChildren()) {
					$udpos[$udpoKey]->setTotalCost($udpos[$udpoKey]->getTotalCost() + $item->getBaseCost() * $_totQty);
				}
				$udpos[$udpoKey]->setCommissionPercent($vendor->getCommissionPercent());
				$udpos[$udpoKey]->setTransactionFee($vendor->getTransactionFee());
			}
		}
		Mage::dispatchEvent("udpo_order_save_before", array("order" => $order, "udpos" => $udpos));
		$order->setUdropshipStatus(ZolagoOs_OmniChannel_Model_Source::ORDER_STATUS_NOTIFIED);
		$udpoSplitWeights = array();
		foreach ($udpos as $_vUdpoKey => $_vUdpo) {
			if (empty($udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"])) {
				$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["weights"] = array();
				$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["total_weight"] = 0;
			}
			$weight = 0 < $_vUdpo->getTotalWeight() ? $_vUdpo->getTotalWeight() : 0.001;
			$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["weights"][$_vUdpoKey] = $weight;
			$udpoSplitWeights[$_vUdpo->getUdropshipVendor() . "-"]["total_weight"] += $weight;
		}
		$transaction = Mage::getModel("core/resource_transaction");
		foreach ($udpos as $udpoKey => $udpo) {
			$udpo->setIsVirtual($isVirtual[$udpoKey]);
			if ($isVirtual[$udpoKey]) {
				$vUdpoStatus = (int)Mage::getStoreConfig("udropship/purchase_order/default_virtual_po_status", $order->getStoreId());
				if ("999" != $vendor->getData("initial_virtual_po_status")) {
					$vUdpoStatus = $vendor->getData("initial_virtual_po_status");
				}
				$udpo->setUdropshipStatus($vUdpoStatus);
			}

			Mage::helper("udropship")->addVendorSkus($udpo);
			if (empty($udpoNoSplitWeights[$udpo->getUdropshipVendor() . "-"]) &&
				!empty($udpoSplitWeights[$udpo->getUdropshipVendor() . "-"]["weights"][$udpoKey]) &&
				1 < count($udpoSplitWeights[$udpo->getUdropshipVendor() . "-"]["weights"]))
			{
				Mage::log("setBaseShippingTax: " . ($udpo->getBaseShippingTax()), null, "po.log");
				$_splitWeight = $udpoSplitWeights[$udpo->getUdropshipVendor() . "-"]["weights"][$udpoKey];
				$_totalWeight = $udpoSplitWeights[$udpo->getUdropshipVendor() . "-"]["total_weight"];
				$udpo->setShippingAmount(($udpo->getShippingAmount() * $_splitWeight) / $_totalWeight);
				$udpo->setBaseShippingAmount(($udpo->getBaseShippingAmount() * $_splitWeight) / $_totalWeight);
				$udpo->setShippingAmountIncl(($udpo->getShippingAmountIncl() * $_splitWeight) / $_totalWeight);
				$udpo->setBaseShippingAmountIncl(($udpo->getBaseShippingAmountIncl() * $_splitWeight) / $_totalWeight);
				$udpo->setShippingTax(($udpo->getShippingTax() * $_splitWeight) / $_totalWeight);
				$udpo->setBaseShippingTax(($udpo->getBaseShippingTax() * $_splitWeight) / $_totalWeight);
			}
			$order->getUdposCollection()->addItem($udpo);
			$transaction->addObject($udpo);
		}
		$order->setLastCreatedUdpos($udpos);
		$transaction->addObject($order->setData("___dummy", 1))->save();
		$order->setUdropshipOrderSplitFlag(true);
		Mage::dispatchEvent("udpo_order_save_after", array("order" => $order, "udpos" => $udpos));
		foreach ($udpos as $udpo) {
			$poHlp->sendVendorNotification($udpo, $comment);
		}
		$hlp->processQueue();
		return count($udpos);
	}
}


