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

 

class Itoris_GroupedProductPromotions_Block_Checkout_Cart extends Itoris_GroupedProductPromotions_Block_Product_View {

    public function getProductIds() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $productIds = array();
        foreach ($quote->getAllVisibleItems() as $item) {
            $productIds[] = $item->getProductId();
        }
        return $productIds;
    }

    public function prepareRules($configRule) {
        foreach ($configRule as $ruleId => $value) {
            $addToUrl = null;
            foreach ($value['product_config'] as $productConfig) {
                $product = Mage::getModel('catalog/product')->load($productConfig['product_id']);
                if ($this->getDataHelper()->isModuleEnabled('Itoris_ProductPriceVisibility')) {
                    $visibilityProduct = Mage::helper('itoris_productpricevisibility/product')->getPriceVisibilityConfig($product);
                    if ($visibilityProduct['mode'] != 'default') {
                        unset($configRule[$ruleId]);
                        continue;
                    }
                }
                if (!(int)$product->getIsInStock()) {
                    unset($configRule[$ruleId]);
                    continue;
                }

                if ((int)$product->getHasOptions() || $product->getTypeId() == 'configurable') {
                    foreach ($value['product_config'] as $config) {
                        if (is_null($addToUrl)) {
                            $addToUrl .= '?qty[' . $config['product_id'] . ']=' . $config['qty'];
                        } else {
                            $addToUrl .= '&qty[' . $config['product_id'] . ']=' . $config['qty'];
                        }
                    }
                    if (array_key_exists($ruleId, $configRule) && !array_key_exists('url_grouped_product', $configRule[$ruleId])) {
                        $configRule[$ruleId]['url_grouped_product'] = Mage::getModel('catalog/product')->load($value['product_id'])->getProductUrl() . $addToUrl;
                    }
                }
            }
        }
        /*$configRule = array_values($configRule);
        for ($i = 0; $i < count($configRule); $i++) {
            for ($j = 0; $j < count($configRule); $j++) {
                if ((int)$configRule[$i]['position'] < (int)$configRule[$j]['position']) {
                    $hold = $configRule[$i];
                    $configRule[$i] = $configRule[$j];
                    $configRule[$j] = $hold;
                }
            }
        }*/
        /*for ($i = 0; $i < count($configRule); $i++) {
            for ($k = 0; $k < count($configRule[$i]['product_config']); $k++) {
                for ($m = 0; $m < count($configRule[$i]['product_config']); $m++) {
                    if ((int)$configRule[$i]['product_config'][$k]['position'] < (int)$configRule[$i]['product_config'][$m]['position']) {
                        $buffer = $configRule[$i]['product_config'][$k];
                        $configRule[$i]['product_config'][$k] = $configRule[$i]['product_config'][$m];
                        $configRule[$i]['product_config'][$m] = $buffer;
                    }
                }
            }
        }*/

        foreach ($configRule as $config) {
            for ($k = 0; $k < count($config['product_config']); $k++) {
                for ($m = 0; $m < count($config['product_config']); $m++) {
                    if ((int)$config['product_config'][$k]['position'] < (int)$config['product_config'][$m]['position']) {
                        $buffer = $config['product_config'][$k];
                        $config['product_config'][$k] = $config['product_config'][$m];
                        $config['product_config'][$m] = $buffer;
                    }
                }
            }
        }
        
        return $configRule;
    }

    public function getUsedRules() {
        $configRule = array();
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('read');
        $tableProduct = $resource->getTableName('itoris_groupedproductpromotions_rules_product');
        $tableGroup = $resource->getTableName('itoris_groupedproductpromotions_rules_group');
        $usedRules = Mage::getSingleton('itoris_groupedproductpromotions/quote_observer')->getUsedRules();
        foreach ($usedRules as $index => $ruleData) {
            $ruleId = $ruleData['rule_id'];
            $rulesModel = Mage::getModel('itoris_groupedproductpromotions/rules');
            $rulesModel->load($ruleId);
            $otherProductsConfig = $connection->fetchAll("select * from {$tableProduct} where rule_id={$ruleId}");
            $groupIds = $connection->fetchAll("select group_id from {$tableGroup} where rule_id={$ruleId}");
            $idGroupedProduct = (int)$rulesModel->getProductId();
            $groupedProd = Mage::getModel('catalog/product')->load($idGroupedProduct);
            if ($groupedProd->getTypeId() == 'grouped') {
                $subProducts = $groupedProd->getTypeInstance(true)->getAssociatedProducts($groupedProd);
                foreach ($subProducts as $subProduct) {
                    $productPosition[$subProduct->getId()] = $subProduct->getPosition();
                }
            }

            $configRule[$index] = array(
                'rule_id'        => $ruleId,
                'title'          => $rulesModel->getTitle(),
                'product_id'     => $rulesModel->getProductId(),
                'position'       => $rulesModel->getPosition(),
                'status'         => $rulesModel->getStatus(),
                'active_from'    => $rulesModel->getActiveFrom(),
                'active_to'      => $rulesModel->getActiveTo(),
                'group_ids'      => $groupIds,
                'product_config' => array(),
                'show_promoset'  => true,
                'price_method'      => (int)$rulesModel->getPriceMethod(),
                'discount_promoset' => $rulesModel->getDiscountPromoset(),
                'code_promoset'     => (int)$rulesModel->getCode(),
                'fixed_price'       => $rulesModel->getFixedPrice(),
            );
            foreach ($otherProductsConfig as $value) {
                if (array_key_exists($value['product_id'], $productPosition)) {
                    $possProduct = $productPosition[$value['product_id']];
                } else {
                    $possProduct = 0;
                }
                $configRule[$index]['product_config'][] = array(
                    'product_id' => $value['product_id'],
                    'qty'        => $value['qty'],
                    'discount'   => $value['discount'],
                    'type'       => $value['type'],
                    'position'   => $possProduct,
                    'show_promoset'  => true,
                );
            }
        }

        return $this->prepareRules($configRule);
    }

    protected function _displayWithTax() {
        return Mage::helper('tax')->displayCartPriceInclTax();
    }

    protected function getTaxHelper() {
        return Mage::helper('itoris_groupedproductpromotions/tax');
    }

    public function getUsedRulesJson() {
        return Mage::getSingleton('itoris_groupedproductpromotions/quote_observer')->getUsedRulesJson();
    }
}
?>