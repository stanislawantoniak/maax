<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_GROUPEDPRODUCTPROMOTIONS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */


class Itoris_GroupedProductPromotions_Model_Quote_Observer {

	protected $usedRules = array();

	public function changePrice($obj) {
		if (!Mage::helper('itoris_groupedproductpromotions')->isRegisteredFrontend()) {
			return;
		}
		$this->usedRules = array();
		/** @var $taxHelper Mage_Tax_Helper_Data */
		$taxHelper = Mage::helper('itoris_groupedproductpromotions/tax');
		$priceIncludesTax = $taxHelper->priceIncludesTax();
		$displayWithTax = $taxHelper->displayCartPriceInclTax();
        $discountOnPriceIncludingTax = $taxHelper->discountTax();
        $addTaxToDiscount = !$priceIncludesTax && $discountOnPriceIncludingTax;
        $removeTaxFromDiscount = $priceIncludesTax && !$discountOnPriceIncludingTax;

 		/** @var $quote Mage_Sales_Model_Quote */
		$quote = $obj->getQuote();
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('read');
		$tableRulesProduct = $resource->getTableName('itoris_groupedproductpromotions_rules_product');
		$tableRule = $resource->getTableName('itoris_groupedproductpromotions_rules');
		$storeId = (int)Mage::app()->getStore()->getId();

        $currencyRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::app()->getStore()->getCurrentCurrency());
        if (!$currencyRate) $currencyRate = 1;

		
		//looking for all rules matching products in the cart
		$cartProductQty = array();
		foreach ($quote->getAllVisibleItems() as $item) $cartProductQty[$item->getProductId()] += $item->getQty();
		$cartProductIds = array_keys($cartProductQty);
		if (empty($cartProductIds)) return;
		
		$ruleIds = $connection->fetchCol("select distinct e.rule_id from {$tableRule} as e
						inner join {$tableRulesProduct} as rule_product
							on e.rule_id=rule_product.rule_id and rule_product.product_id in (".implode(',', $cartProductIds).")
						where e.status = 1 and (e.store_id = {$storeId} or e.store_id=0)
						      and e.rule_id not in (select ce.parent_id from {$tableRule} as ce where ce.store_id={$storeId})");		
		if (empty($ruleIds)) return;
		
		$data = $connection->fetchAll("select e.rule_id, e.product_id, e.discount, e.qty, e.type, rule.price_method, rule.discount_promoset, rule.code as code_promoset, rule.fixed_price from {$tableRulesProduct} as e
                        join {$tableRule} as rule
                          on e.rule_id=rule.rule_id
                        where e.rule_id in (".implode(',', $ruleIds).")");
		if (empty($data)) return;

		$promoRules = array();
		foreach($data as $p) {
			if (!isset($promoRules[(int) $p['rule_id']])) $promoRules[(int) $p['rule_id']] = array();
			$promoRules[(int) $p['rule_id']][] = $p;
		}
		$promoRules = array_values($promoRules);

		//sorting rules by the number of products to maximize the match
		for($i = 0; $i < count($promoRules) - 1; $i++) {
			for($o = $i + 1; $o < count($promoRules); $o++) {
				if (count($promoRules[$i]) < count($promoRules[$o])) {
					$tmp = $promoRules[$i];
					$promoRules[$i] = $promoRules[$o];
					$promoRules[$o] = $tmp;
				}
			}
		}
	
		//preparing the slot array
		$productSlots = array();
		foreach ($quote->getAllVisibleItems() as $item) {
			$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
			$customOptions = array();
			if (isset($_customOptions['options'])) foreach((array)$_customOptions['options'] as $_option) $customOptions[$_option['option_id']] = $_option['value'];
			if (isset($_customOptions['info_buyRequest']['super_attribute'])) foreach((array)$_customOptions['info_buyRequest']['super_attribute'] as $key => $value) $customOptions['a'.$key] = $value;
			$productSlots[$item->getItemId()] = array('productId' => (int) $item->getProductId(), 'totalQty' => (int)$item->getQty(), 'availableQty' => (int)$item->getQty(), 'slots' => array(), 'cartItem' => $item, 'customOptions' => serialize($customOptions));
			//$productSlots[$item->getItemId()]['cartItem'] = '';
		}

		//first, combining similar products having the same options
		$slotItems = array_keys($productSlots);
		for($i=0; $i<count($slotItems)-1; $i++) {
			for($o=$i + 1; $o<count($slotItems); $o++) {
				if (isset($productSlots[$slotItems[$i]]) && isset($productSlots[$slotItems[$o]]) &&
					$productSlots[$slotItems[$i]]['availableQty'] > 0 && $productSlots[$slotItems[$o]]['availableQty'] > 0 &&
					$productSlots[$slotItems[$i]]['productId'] == $productSlots[$slotItems[$o]]['productId'] &&
					$productSlots[$slotItems[$i]]['customOptions'] == $productSlots[$slotItems[$o]]['customOptions']) {
						$productSlots[$slotItems[$i]]['availableQty'] += $productSlots[$slotItems[$o]]['availableQty'];
						$productSlots[$slotItems[$i]]['totalQty'] += $productSlots[$slotItems[$o]]['totalQty'];
						$productSlots[$slotItems[$i]]['cartItem']->setQty($productSlots[$slotItems[$i]]['totalQty']);
						$productSlots[$slotItems[$i]]['cartItem']->save();
						$productSlots[$slotItems[$o]]['cartItem']->isDeleted(true);
						$productSlots[$slotItems[$o]]['cartItem']->save();
						unset($productSlots[$slotItems[$o]]);
					}
			}
		}

		//trying to build possible promoset combinations from products available in the cart
		$ruleIndex = 0;
		foreach($promoRules as $promoRule) {
			do {
				$validRule = true; $maxQty = 99999999;
				foreach($promoRule as $product) {
					$slotFound = false;
					foreach($productSlots as $slot) {
						if ($slot['productId'] == (int) $product['product_id'] && $slot['availableQty'] >= (int) $product['qty']) {
							$_maxQty = floor($slot['availableQty'] / $product['qty']);
							if ($_maxQty < $maxQty) $maxQty = $_maxQty;
							$slotFound = true;
							break;
						}
					}
					if (!$slotFound) {
						$validRule = false;
						break;
					}
				}
				if ($validRule) {
					foreach($promoRule as $product) {
						foreach($productSlots as $itemId => $slot) {
							if ($slot['productId'] == (int) $product['product_id'] && $slot['availableQty'] >= (int) $product['qty'] * $maxQty) {
								$productSlots[$itemId]['slots'][] = array(
									'ruleIndex' => $ruleIndex,
									'ruleId' => (int) $product['rule_id'],
									'qty' => (int) $product['qty'],
									'ruleQty' => $maxQty,
									'config' => $product
								);
								$productSlots[$itemId]['availableQty'] -= (int) $product['qty'] * $maxQty;
								break;
							}
						}
					}
					$ruleIndex++;
				}
			} while ($validRule);
		}

		if ($ruleIndex == 0) return;

		//splitting cart items related to different promosets
		foreach($productSlots as $itemId => $slot) {
			for($i = count($slot['slots']) - 1; $i >= 0; $i--) {
				if ($i == 0 && $slot['availableQty'] < 1) continue;
				
				$item = $productSlots[$itemId]['cartItem'];
				$item->setQty($item->getQty() - $slot['slots'][$i]['qty'] * $slot['slots'][$i]['ruleQty']);
				$item->save();
				$productSlots[$itemId]['totalQty'] -= $slot['slots'][$i]['qty'] * $slot['slots'][$i]['ruleQty'];
				
				$item = clone $item;
				$item->setId(null)->setItemId(null)->isDeleted(false);
				$item->setQuote($quote);
				$item->setQty($slot['slots'][$i]['qty'] * $slot['slots'][$i]['ruleQty']);
				$quote->addItem($item);
				$item->save();
				$productSlots[$item->getItemId()] = $slot;
				$productSlots[$item->getItemId()]['totalQty'] = $slot['slots'][$i]['qty'] * $slot['slots'][$i]['ruleQty'];
				$productSlots[$item->getItemId()]['availableQty'] = 0;
				$productSlots[$item->getItemId()]['slots'] = array($slot['slots'][$i]);
				$productSlots[$item->getItemId()]['cartItem'] = $item;
				unset($productSlots[$itemId]['slots'][$i]);
			}
		}
			
		//again, combining similar products having the same options
		$slotItems = array_keys($productSlots);
		for($i=0; $i<count($slotItems)-1; $i++) {
			for($o=$i + 1; $o<count($slotItems); $o++) {
				if (isset($productSlots[$slotItems[$i]]) && isset($productSlots[$slotItems[$o]]) &&
					$productSlots[$slotItems[$i]]['availableQty'] > 0 && $productSlots[$slotItems[$o]]['availableQty'] > 0 &&
					$productSlots[$slotItems[$i]]['productId'] == $productSlots[$slotItems[$o]]['productId'] &&
					$productSlots[$slotItems[$i]]['customOptions'] == $productSlots[$slotItems[$o]]['customOptions']) {
						$productSlots[$slotItems[$i]]['availableQty'] += $productSlots[$slotItems[$o]]['availableQty'];
						$productSlots[$slotItems[$i]]['totalQty'] += $productSlots[$slotItems[$o]]['totalQty'];
						$productSlots[$slotItems[$i]]['cartItem']->setQty($productSlots[$slotItems[$i]]['totalQty']);
						$productSlots[$slotItems[$i]]['cartItem']->save();
						$productSlots[$slotItems[$o]]['cartItem']->isDeleted(true);
						$productSlots[$slotItems[$o]]['cartItem']->save();
						unset($productSlots[$slotItems[$o]]);
					}
			}
		}

		//calculating discounts for all promosets found
		for($i = 0; $i < $ruleIndex; $i++) {
			$initialPromoPrice = 0;
			foreach($productSlots as $itemId => $_slot) {
				if (empty($_slot['slots']) || $_slot['slots'][0]['ruleIndex'] != $i) continue;
				$product = $_slot['cartItem']->getProduct();
				$tierPrice = $this->getDataHelper()->getTierPrice($product, $_slot['slots'][0]['config']);
				$initialPromoPrice += $taxHelper->getPrice($product, ($tierPrice ? $tierPrice : $product->getFinalPrice()) * $currencyRate * (int)$_slot['slots'][0]['qty'], $displayWithTax);
			}
			$rule = array(
				'rule_id' => 0,
				'rule_qty' => 1,
				'rule_price' => 0,
				'rule_total' => 0,
				'rule_discount' => 0,
				'items' => array()
			);
			foreach($productSlots as $itemId => $_slot) {
				if (empty($_slot['slots'])) continue;
				$slot = $_slot['slots'][0];
				if ($slot['ruleIndex'] != $i) continue;
				$cartItem = $_slot['cartItem'];
				$product = $cartItem->getProduct();
				$tierPrice = $this->getDataHelper()->getTierPrice($product, $slot['config']);
				$itemPrice = ($tierPrice ? $tierPrice : $product->getFinalPrice()) * $currencyRate * $slot['qty'];
				
				//applying tax
				$itemPrice = $taxHelper->getPrice($product, $itemPrice, $displayWithTax);
				
				$item = array(
					'item_id' => $itemId,
					'product_id' => (int) $_slot['productId'],
					'price' => $itemPrice,
					'item_orig_qty' => $slot['qty']
				);
				if ((int) $slot['config']['price_method'] == 0) {
					$item['price'] = $item['price'] - ((int) $slot['config']['type'] == 1 ? ($item['price'] / 100 * $slot['config']['discount']) : $slot['config']['discount'] );
				} else {
					$rowToPrice = (float) ((int) $slot['config']['price_method'] == 1 ? $initialPromoPrice - ((int) $slot['config']['code_promoset'] == 1 ? $initialPromoPrice / 100 * $slot['config']['discount_promoset'] : $slot['config']['discount_promoset']) : $slot['config']['fixed_price']);
					$item['price'] = $item['price'] * ($rowToPrice / $initialPromoPrice);
				}
			
				$itemPricePrecise = $item['price'];
				$item['price'] = round($item['price'], 2);
				
				$item['old_price_formatted'] = Mage::app()->getStore()->formatPrice($itemPrice / $slot['qty'], false);
				$rule['rule_id'] = $slot['ruleId'];
				$rule['rule_price'] += $itemPrice;
				$rule['rule_total'] += $itemPricePrecise * $slot['ruleQty'];
				$rule['rule_discount'] = $rule['rule_price'] - $rule['rule_total'] / $slot['ruleQty'];
				$rule['rule_qty'] = $slot['ruleQty'];
				$rule['items'][] = $item;
				
				$cartItem->setGroupedProductPromotionPrice($itemPricePrecise / $currencyRate / $slot['qty'])->setUseGroupedProductPromotionPrice(true);
			}
			$this->usedRules[] = $rule;
		}
	}

	public function getUsedRules() {
		return $this->usedRules;
	}

	public function getUsedRulesJson() {
		$rules = $this->getUsedRules();
		$rules = array_values($rules);
		foreach ($rules as &$rule) {
			$rule['rule_discount_formatted'] = Mage::app()->getStore()->formatPrice($rule['rule_discount'], false);
			$rule['rule_price_formatted'] = Mage::app()->getStore()->formatPrice($rule['rule_price'], false);
			$rule['rule_total_price_formatted'] = Mage::app()->getStore()->formatPrice($rule['rule_total'], false);
		}
		return Zend_Json::encode($rules);
	}
	
    public function getDataHelper() {
        return Mage::helper('itoris_groupedproductpromotions');
    }
}
?>