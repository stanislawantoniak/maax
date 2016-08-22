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



class Itoris_GroupedProductPromotions_Block_Promotions extends Itoris_GroupedProductPromotions_Block_Product_View {

    protected function _toHtml() {
        if ($this->getDataHelper()->isRegisteredFrontend()) {
            $this->setTemplate('itoris/groupedproductpromotions/promotions.phtml');
            $this->getRules();
            $this->isCMS = true;
            if (empty($this->rules)) {
                return '';
            }
        }
        return parent::_toHtml();
    }

    public function getRules() {
        if (is_null($this->rules)) {
            $ruleId = (int)$this->getRuleId();
            $rulesModelPrepare = Mage::getModel('itoris_groupedproductpromotions/rules');
            $rulesModelPrepare->load($ruleId);
            $storeId = (int)Mage::app()->getStore()->getId();
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
                    return;
                }
            }
            $productCollection = Mage::getModel('itoris_groupedproductpromotions/product')->getCollection()->addFieldToFilter('rule_id', $ruleId);
            $configRule = array();
            $productPosition = array();
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('read');
            $tableGroup = $resource->getTableName('itoris_groupedproductpromotions_rules_group');
            $groupIds = $connection->fetchAll("select group_id from {$tableGroup} where rule_id={$ruleId}");
            $idGroupedProduct = (int)$rulesModel->getProductId();
            $configRule[$ruleId] = array(
                'rule_id'        => $ruleId,
                'title'          => $rulesModel->getTitle(),
                'product_id'     => $idGroupedProduct,
                'position'       => $rulesModel->getPosition(),
                'status'         => $rulesModel->getStatus(),
                'active_from'    => $rulesModel->getActiveFrom(),
                'active_to'      => $rulesModel->getActiveTo(),
                'group_ids'      => $groupIds,
                'product_config' => array()
            );

            $groupedProd = Mage::getModel('catalog/product')->load($idGroupedProduct);
            if ($groupedProd->getTypeId() == 'grouped') {
                $subProducts = $groupedProd->getTypeInstance(true)->getAssociatedProducts($groupedProd);
                foreach ($subProducts as $subProduct) {
                    $productPosition[$subProduct->getId()] = $subProduct->getPosition();
                }
            }
            foreach ($productCollection as $model) {
                if (array_key_exists($model->getProductId(), $productPosition)) {
                    $possProduct = $productPosition[$model->getProductId()];
                } else {
                    $possProduct = 0;
                }
                if (array_key_exists('product_config', $configRule[$ruleId])) {
                    $configRule[$ruleId]['product_config'][] = array(
                        'product_id' => $model->getProductId(),
                        'qty'        => $model->getQty(),
                        'discount'   => $model->getDiscount(),
                        'type'       => $model->getType(),
                        'position'   => $possProduct,
                        'show_promoset' => $model->getShowPromoset(),

                    );
                }
            }
            $this->rules = $this->prepareRules($configRule);
        }
        return $this->rules;
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

}
?>

