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

 

class Itoris_GroupedProductPromotions_Block_Product_View extends Mage_Core_Block_Template {

    const TYPE_FIXED = 0;
    const TYPE_PERCENT = 1;
    const STATUS_ACTIVE = 1;

    protected $rules = null;
    protected $isCMS = false;

    public function getCurrentProduct() {
        return Mage::registry('current_product');
    }

    public function isCms() {
        return $this->isCMS;
    }

    public function getRules() {
        if (is_null($this->rules)) {
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('read');
            $productIds = $this->getProductIds();
            $productCollection = Mage::getModel('itoris_groupedproductpromotions/product')->getCollection();
			$productCollection->addFieldToFilter('product_id', array('in' => $productIds));
			$productCollection->addFieldToFilter('show_promoset', array('eq' => 1));
            $storeId = Mage::app()->getStore()->getId();
            $configRule = array();
            $productPosition = array();
            if (count($productCollection)) {
                foreach ($productCollection as $model) {
                    foreach ($productIds as $productId) {
                        if ($model->getProductId() == $productId) {
                            $ruleId = $model->getRuleId();
                            $rulesModelPrepare = Mage::getModel('itoris_groupedproductpromotions/rules');
                            $rulesModelPrepare->load($ruleId);
                            if ($this->getCurrentProduct() && $this->getCurrentProduct()->getTypeId() == 'grouped' && $rulesModelPrepare->getProductId() != $this->getCurrentProduct()->getId()) {
                                continue;
                            }
                            if ($rulesModelPrepare->getStoreId() == 0 && $storeId) {
                                $rulesModel = Mage::getModel('itoris_groupedproductpromotions/rules');
                                $rulesModel->load($ruleId, 'parent_id');
                                if ($rulesModel->getId() && $storeId == $rulesModel->getStoreId()) {
                                    $ruleId = (int)$rulesModel->getId();
                                } else {
                                    $rulesModel = $rulesModelPrepare;
                                }
                            } else {
                                $rulesModel = Mage::getModel('itoris_groupedproductpromotions/rules');
                                $rulesModel->load($ruleId);
                                if ($rulesModel->getStoreId() != $storeId) {
                                    continue;
                                }
                            }
                            $tableProduct = $resource->getTableName('itoris_groupedproductpromotions_rules_product');
                            $otherProductsConfig = $connection->fetchAll("select * from {$tableProduct} where rule_id={$ruleId} and product_id != {$productId}");
                            $tableGroup = $resource->getTableName('itoris_groupedproductpromotions_rules_group');
                            $groupIds = $connection->fetchAll("select group_id from {$tableGroup} where rule_id={$ruleId}");
                            $idGroupedProduct = (int)$rulesModel->getProductId();
                            $groupedProd = Mage::getModel('catalog/product')->load($idGroupedProduct);
                            if ($groupedProd->getTypeId() == 'grouped') {
                                $subProducts = $groupedProd->getTypeInstance(true)->getAssociatedProducts($groupedProd);
                                foreach ($subProducts as $subProduct) {
                                    $productPosition[$subProduct->getId()] = $subProduct->getPosition();
                                }
                            }
                            if (array_key_exists($productId, $productPosition)) {
                                $possProduct = $productPosition[$productId];
                            } else {
                                $possProduct = 0;
                            }

                            $configRule[$ruleId] = array(
                                'rule_id'        => $ruleId,
                                'title'          => $rulesModel->getTitle(),
                                'product_id'     => $rulesModel->getProductId(),
                                'position'       => $rulesModel->getPosition(),
                                'status'         => $rulesModel->getStatus(),
                                'active_from'    => $rulesModel->getActiveFrom(),
                                'active_to'      => $rulesModel->getActiveTo(),
                                'group_ids'      => $groupIds,
                                'price_method'      => (int)$rulesModel->getPriceMethod(),
                                'discount_promoset' => $rulesModel->getDiscountPromoset(),
                                'code_promoset'     => (int)$rulesModel->getCode(),
                                'fixed_price'       => $rulesModel->getFixedPrice(),
                                'product_config' => array(
                                    array(
                                        'product_id' => $model->getProductId(),
                                        'qty'        => $model->getQty(),
                                        'discount'   => $model->getDiscount(),
                                        'type'       => $model->getType(),
                                        'position'   => $possProduct,
                                        'show_promoset' => $model->getShowPromoset(),
                                    )
                                )
                            );
                            foreach ($otherProductsConfig as $value) {
                                if (array_key_exists($value['product_id'], $productPosition)) {
                                    $possProduct = $productPosition[$value['product_id']];
                                } else {
                                    $possProduct = 0;
                                }
                                $configRule[$ruleId]['product_config'][] = array(
                                    'product_id' => $value['product_id'],
                                    'qty'        => $value['qty'],
                                    'discount'   => $value['discount'],
                                    'type'       => $value['type'],
                                    'position'   => $possProduct,
                                    'show_promoset' => $value['show_promoset'],
                                );
                            }
                        }
                    }
                }
            }
            $this->rules = $this->prepareRules($configRule);
        }

        return $this->rules;
    }

    public function prepareRules($configRule) {
        $currentProduct = $this->getCurrentProduct();
        foreach ($configRule as $ruleId => $value) {
            $addToUrl = null;
            $qtyCurrentProduct = array();
            foreach ($value['product_config'] as $productConfig) {
                $product = Mage::getModel('catalog/product')->load($productConfig['product_id']);
                if ($this->getDataHelper()->isModuleEnabled('Itoris_ProductPriceVisibility')) {
                    $visibilityProduct = Mage::helper('itoris_productpricevisibility/product')->getPriceVisibilityConfig($product);
                    if ($visibilityProduct['mode'] != 'default') {
                        unset($configRule[$ruleId]);
                        continue;
                    }
                }
                if ($productConfig['product_id'] == $currentProduct->getId() && $productConfig['show_promoset'] == 0) {
                    unset($configRule[$ruleId]);
                    continue;
                }
                if (!(int)$product->getIsInStock()) {
                    unset($configRule[$ruleId]);
                    continue;
                }
                if ((int)$product->getHasOptions() || $product->getTypeId() == 'configurable') {
                    foreach ($value['product_config'] as $config) {
                        if ($value['product_id'] != $currentProduct->getId()) {
                            if (is_null($addToUrl)) {
                                $addToUrl .= '?qty[' . $config['product_id'] . ']=' . $config['qty'];
                            } else {
                                $addToUrl .= '&qty[' . $config['product_id'] . ']=' . $config['qty'];
                            }
                        } else {
                            $qtyCurrentProduct[$config['product_id']] = $config['qty'];
                        }
                    }
                    if (array_key_exists($ruleId, $configRule) && !array_key_exists('url_grouped_product', $configRule[$ruleId])) {
                        if ($value['product_id'] != $currentProduct->getId()) {
                            $configRule[$ruleId]['url_grouped_product'] = Mage::getModel('catalog/product')->load($value['product_id'])->getProductUrl() . $addToUrl;
                        } else {
                            $configRule[$ruleId]['url_grouped_product'] = $qtyCurrentProduct;
                        }
                    }
                }
            }
        }
        $configRule = array_values($configRule);
        for ($i = 0; $i < count($configRule); $i++) {
            for ($j = 0; $j < count($configRule); $j++) {
                if ((int)$configRule[$i]['position'] < (int)$configRule[$j]['position']) {
                    $hold = $configRule[$i];
                    $configRule[$i] = $configRule[$j];
                    $configRule[$j] = $hold;
                }
            }
        }
        for ($i = 0; $i < count($configRule); $i++) {
            for ($k = 0; $k < count($configRule[$i]['product_config']); $k++) {
                for ($m = 0; $m < count($configRule[$i]['product_config']); $m++) {
                    if ((int)$configRule[$i]['product_config'][$k]['position'] < (int)$configRule[$i]['product_config'][$m]['position']) {
                        $buffer = $configRule[$i]['product_config'][$k];
                        $configRule[$i]['product_config'][$k] = $configRule[$i]['product_config'][$m];
                        $configRule[$i]['product_config'][$m] = $buffer;
                    }
                }
            }
        }
        return $configRule;
    }

    public function getProductIds() {
        $productIds = array();
        $currentProduct = $this->getCurrentProduct();
        if ($currentProduct->getTypeId() == 'grouped') {
            $subProducts = $currentProduct->getTypeInstance(true)->getAssociatedProducts($currentProduct);
            foreach ($subProducts as $subProduct) {
                $productIds[] = $subProduct->getId();
            }
        }
        $productIds[] = $currentProduct->getId();
        return $productIds;
    }

    public function getDataProduct($productId) {
        $dataProduct = array();
        $product = Mage::getModel('catalog/product')->load((int)$productId);
        $currencyRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::app()->getStore()->getCurrentCurrency());
        $finalPrice = $product->getFinalPrice() * $currencyRate;
        $dataProduct = array(
            'image'       => (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(95),
            'name' 		  => $product->getName(),
            'product_url'  => $product->getProductUrl(),
            'final_price'  => $finalPrice,
            'tier_prices' => $this->getDataHelper()->prepareTierPrices($product),
        );
        return $dataProduct;
    }

    protected function _displayWithTax() {
        return Mage::helper('tax')->getPriceDisplayType() == 2;
    }

    protected function getTaxHelper() {
        return Mage::helper('tax');
    }

    public function getProductsData($ruleConfig, $isCart = false) {
        $productIds = array();
        $configs = $ruleConfig['product_config'];
        foreach ($configs as $config) {
            $productIds[] = $config['product_id'];
        }

        $collection =Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addAttributeToSelect('visibility')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addFieldToFilter('entity_id', array('in' => $productIds))
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite();
        $result = array();
        if ($collection->getSize()) {
            /** @var $taxHelper Mage_Tax_Helper_Data */
            $taxHelper = $this->getTaxHelper();
            $displayWithTax = $this->_displayWithTax();
            $priceIncludingTax = $taxHelper->priceIncludesTax();
            $discountOnPriceIncludingTax = /*!$taxHelper->applyTaxAfterDiscount() && */$taxHelper->discountTax();
            $addTaxToDiscount = !$priceIncludingTax && $displayWithTax && $discountOnPriceIncludingTax;
            $removeTaxFromDiscount = $priceIncludingTax && !$discountOnPriceIncludingTax;
            /** @var $product Mage_Catalog_Model_Product */
            $currencyRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::app()->getStore()->getCurrentCurrency());
            if (!$currencyRate) {
                $currencyRate = 1;
            }
            $totalNormalPrice = 0;
            $code = 0;
            $discount = 0;
            $subproductsByPrice = array();
            $subproductsSortByPrice = array();
            foreach ($collection as $product) {
                foreach ($configs as $subproductData) {
                    if ($subproductData['product_id'] == $product->getId()) {
						$tierPrice = $this->getDataHelper()->getTierPrice($product, $subproductData);
                        $normalPrice = $taxHelper->getPrice($product, ($tierPrice ? $tierPrice : $product->getFinalPrice()) * (int)$subproductData['qty'], $displayWithTax);
                        $totalNormalPrice += $normalPrice;
                        $subproductsByPrice[$product->getId()] = $product->getFinalPrice();
                    }
                }
            }

            asort($subproductsByPrice);
            foreach ($subproductsByPrice as $productId => $productPrice) {
                foreach ($collection as $model) {
                    if ($productId == $model->getId()) {
                        $subproductsSortByPrice[] = $model;
                    }
                }
            }
            $priceMethod = $ruleConfig['price_method'];

            $discountForEntirePromoset = $ruleConfig['discount_promoset'];
            $fixedPriceEntirePromoset = floatval($ruleConfig['fixed_price']);
            if ($priceMethod == 1) { // if discount for entire promoset
                $code = (int)$ruleConfig['code_promoset'];
                if ($code) {
                    $discount = $discountForEntirePromoset;
                } elseif ($totalNormalPrice) {
                    $discount = 1 - $discountForEntirePromoset / $totalNormalPrice;
                }
            } elseif ($priceMethod == 2 && $totalNormalPrice) { // if fixed price for entire promoset
                $discount = $fixedPriceEntirePromoset / $totalNormalPrice;
                $code = 0;
            }
            $totalPackagePrice = 0;
            $endProduct = 0;
            foreach ($subproductsSortByPrice as $product) {
                $finalPrice = $product->getFinalPrice();
                $productData = array(
                    'image'       => (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(95),
                    'name' 		  => $product->getName(),
                    'product_url' => $product->isVisibleInSiteVisibility() ? $product->getProductUrl() : null,
                    'final_price' => $taxHelper->getPrice($product, $finalPrice * $currencyRate, $displayWithTax),
                    'tier_prices' => $this->getDataHelper()->prepareTierPrices($product),
                    'has_options' => $product->getHasOptions() || $product->getTypeId() == 'configurable',
                    'type_id'     => $product->getTypeId(),
                );
                if ($isCart) {
                    $productData['image'] = (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(75);
                }
                $config = $this->getDataHelper()->getProductConfig($product, $configs);
                if (empty($config)) continue;
				
                $tierPrice = $this->getDataHelper()->getTierPrice($product, $config);
                if ($priceMethod == 0) {
                    $discount = $config['discount'];
                    $code = (int)$config['type'];
                }
                $productData['tier_price'] = $tierPrice * $currencyRate;
                $finalPrice = $product->getFinalPrice();
                $qty = $config['qty'];
                $normalPrice = $taxHelper->getPrice($product, $tierPrice ? $tierPrice : $finalPrice, $displayWithTax);

                $promoPrice = 0;
                $endProduct++;
                if ($code) { // 1 = percent, 0 = fixed
                    $promoPrice = round($normalPrice * (1 - $discount/100), 2);
                    $totalPackagePrice += $promoPrice * $qty;
                    if ($priceMethod == 1) {
                        if (count($subproductsSortByPrice) == $endProduct) { // fight with rounding
                            if ($totalPackagePrice < $totalNormalPrice * (1 - $discount/100)) {
                                $promoPrice +=  $totalNormalPrice * (1 - $discount/100) - $totalPackagePrice;
                            } else {
                                $promoPrice -=  $totalPackagePrice - $totalNormalPrice * (1 - $discount/100);
                            }
                        }
                    }
                } else {
                    if ($priceMethod != 0) {
                        $promoPrice = round($normalPrice * $discount, 2);
                        if (count($subproductsSortByPrice) == $endProduct) { // fight with rounding
                            if ($priceMethod == 1) {
                                $promoPrice = round(((($totalNormalPrice - $discountForEntirePromoset) - $totalPackagePrice)) / $qty, 2);
                            } else {
                                $promoPrice = round(($fixedPriceEntirePromoset - $totalPackagePrice) / $qty, 2);
                            }
                        }
                        $totalPackagePrice += $promoPrice * $qty;
                    } else {
                        $promoPrice = $normalPrice - $taxHelper->getPrice($product, $discount, $displayWithTax);
                    }
                }
                $productData['total_discount'] = ($normalPrice - $promoPrice) * $qty * $currencyRate;
                $productData['discount'] = ($normalPrice - $promoPrice) * $currencyRate;
                $result[$product->getId()] = $productData;
            }
        }
        return $result;
    }
	
    public function getSymbolCurrency() {
        $code = Mage::app()->getStore()->getCurrentCurrencyCode();
        $symbol =  Mage::app()->getLocale()->currency($code)->getSymbol();
        if (!is_null($symbol)) {
            return $symbol;
        } else {
            return '';
        }
    }

    public function getTotalDiscount($qty, $price, $discount, $type, $addTax = false, $removeTax = false, $product = null) {
        $totalDiscountRule = 0;
        if ($type == self::TYPE_FIXED) {
            $totalDiscountRule += $discount * $qty;
            if ($addTax) {
                $totalDiscountRule = $this->getTaxHelper()->getPrice($product, $totalDiscountRule, true);
            } else if ($removeTax) {
                $totalDiscountRule = $this->getTaxHelper()->getPrice($product, $totalDiscountRule, false);
            }
        } else {
            $totalDiscountRule += (($discount * $price)/100) * $qty;
        }

        return $totalDiscountRule;
    }

    public function getTotalPrice($qty, $price) {
        return $price * $qty;
    }

    public function correctDate($startDate, $endDate) {
        $currentDate = Mage::app()->getLocale()->date();
        $start = $this->getDataHelper()->getDate($startDate);
        $end = $this->getDataHelper()->getDate($endDate);
        if (!empty($startDate) && !empty($endDate)) {
            if ($start->compareDate($currentDate) !== 1 && $end->compareDate($currentDate) !== -1) {
                return true;
            } else {
                return false;
            }
        } elseif (!empty($startDate) && empty($endDate)) {
            if ($start->compareDate($currentDate) !== 1) {
                return true;
            } else {
                return false;
            }
        } elseif (empty($startDate) && !empty($endDate))  {
            if ($end->compareDate($currentDate) !== -1) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function customerGroup($selectedGroupId) {
        $customer = Mage::getSingleton('customer/session');
        $groupsAsString = '';
        $customerId = (string)$customer->getCustomerGroupId();
        if (!empty($selectedGroupId)) {
            foreach ($selectedGroupId as $key => $value) {
                if ($key == count($selectedGroupId) - 1) {
                    $groupsAsString .=  $value['group_id'];
                } else {
                    $groupsAsString .=  $value['group_id'] . ',';
                }
            }

        } else {
            return true;
        }
        if (strstr($groupsAsString, $customerId) !== false) {
            return true;
        } else {
            return false;
        }
    }

    protected function _toHtml() {
        $this->getRules();
        if (empty($this->rules)) {
            return '';
        }
        return parent::_toHtml();
    }

    public function optionBlock($subProductId, $ruleId) {
        $product = Mage::getModel('catalog/product')->load($subProductId);
        if (!Mage::registry('product')) {
            Mage::register('product', $product);
        }
        $wrapper = $this->getLayout()->createBlock('catalog/product_view');
        $wrapper->setTemplate('catalog/product/view/options/wrapper.phtml');
        $coreTemplate = $this->getLayout()->createBlock('core/template');
        $coreTemplate->setTemplate('catalog/product/view/options/js.phtml');
        $wrapper->append($coreTemplate);

        if ($product->getTypeId() == 'bundle') {
            $bundleBlock = $this->getLayout()->createBlock('bundle/catalog_product_view_type_bundle');
            $bundleBlock->setProduct($product);
            $bundleBlock->setData('rule_id', $ruleId);
            $bundleBlock->setTemplate('itoris/groupedproductpromotions/bundle/bundle.phtml');
            $wrapper->append($bundleBlock);
            $optionBlock = $this->getLayout()->createBlock('itoris_groupedproductpromotions/product_bundle_option');
            $optionBlock->setProduct($product);
            $optionBlock->addRenderer('select', 'itoris_groupedproductpromotions/product_bundle_options_select');
            $optionBlock->addRenderer('multi', 'itoris_groupedproductpromotions/product_bundle_options_multi');
            $optionBlock->addRenderer('radio', 'itoris_groupedproductpromotions/product_bundle_options_radio');
            $optionBlock->addRenderer('checkbox', 'itoris_groupedproductpromotions/product_bundle_options_checkbox');
            $optionBlock->setData('rule_id', $ruleId);
            $optionBlock->setTemplate('bundle/catalog/product/view/type/bundle/options.phtml');
            //$optionBlock->setTemplate('itoris/itoris_groupedproductpromotions/bundle/options.phtml');
        } elseif ($product->getTypeId() == 'configurable') {
            $optionBlock = $this->getLayout()->createBlock('catalog/product_view_type_configurable');
            $optionBlock->setProduct($product);
            $optionBlock->setData('rule_id', $ruleId);
            $optionBlock->setTemplate('itoris/groupedproductpromotions/configurable/options.phtml');
        } else {
            /* @var $optionBlock Mage_Catalog_Block_Product_View_Options */
            $optionBlock = $this->getLayout()->createBlock('catalog/product_view_options');
            $optionBlock->addOptionRenderer('text', 'catalog/product_view_options_type_text', 'catalog/product/view/options/type/text.phtml');
            $optionBlock->addOptionRenderer('file', 'catalog/product_view_options_type_file', 'catalog/product/view/options/type/file.phtml');
            $optionBlock->addOptionRenderer('select', 'catalog/product_view_options_type_select', 'catalog/product/view/options/type/select.phtml');
            $optionBlock->addOptionRenderer('date', 'catalog/product_view_options_type_date', 'catalog/product/view/options/type/date.phtml');
            $optionBlock->setProduct($product);
            $optionBlock->setData('rule_id', $ruleId);
            $optionBlock->setTemplate('itoris/groupedproductpromotions/options.phtml');
        }

        $wrapper->append($optionBlock);
        return $wrapper;
    }

    protected function _prepareLayout() {
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->addItem('skin_js', 'js/bundle.js');
            $head->addItem('js', 'varien/product.js');
        }
        return parent::_prepareLayout();
    }

    public function getConfig($subProductId) {
        $config = array();
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load($subProductId);
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        $tierPrices = $product->getTierPrice();
        if (is_array($tierPrices)) {
            foreach ($tierPrices as $tierPrice) {
                $_tierPrices[] = Mage::helper('core')->currency($tierPrice['website_price'], false, false);
                $_tierPricesInclTax[] = Mage::helper('core')->currency(
                    Mage::helper('tax')->getPrice($product, (int)$tierPrice['website_price'], true),
                    false, false);
            }
        }

        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }
        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * @return Itoris_GroupedProductPromotions_Helper_Data
     */

    public function getDataHelper() {
        return Mage::helper('itoris_groupedproductpromotions');
    }

}
?>