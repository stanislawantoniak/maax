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

 

class Itoris_GroupedProductPromotions_Model_Observer {

	public function saveProduct($obj) {
        $products = $obj->getDataObject();
        $productId = $products->getId();
        $ruleParam = Mage::app()->getRequest()->getParam('itoris_groupedproductpromotions_rule');
        if (is_array($ruleParam)) {
            ksort($ruleParam);
        }
        $ruleIdsNorForDelete = array();
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('read');
        $table = $resource->getTableName('itoris_groupedproductpromotions_rules');
        $productRuleIds = array();
        $helper = Mage::helper('itoris_groupedproductpromotions');
        $storeId = (int)Mage::app()->getRequest()->getParam('store');
        if (!empty($ruleParam)) {
            $subProducts = $products->getTypeInstance(true)->getAssociatedProducts($products);
            $subProductParam = Mage::app()->getRequest()->getParam('itoris_groupedproductpromotions_product');
            foreach ($ruleParam as $param) {
                if (!isset($param['use_default'])) {
                    $subProductIdsNotForDelete = array();
                    $rulesModel = Mage::getModel('itoris_groupedproductpromotions/rules');
                    $ruleIdDb = (int)$param['rule_id_db'];
                    $rulesModel->load($ruleIdDb);
                    $rulesModel->setTitle($param['title']);
                    $rulesModel->setProductId((int)$productId);
                    $rulesModel->setStatus((int)$param['status']);
                    if (isset($param['position'])) {
                        $rulesModel->setPosition((int)$param['position']);
                    } else {
                        $rulesModel->setPosition(0);
                    }
                    $param = $helper->prepareDates($param, array('active_from', 'active_to'));
                    if (isset($param['active_from']) && !empty($param['active_from'])) {
                        $startDate = $param['active_from'];
                        $rulesModel->setActiveFrom($startDate);
                    } else {
                        $rulesModel->setActiveFrom(null);
                    }
                    if (isset($param['active_to']) && !empty($param['active_to'])) {
                        $endDate = $param['active_to'];
                        $rulesModel->setActiveTo($endDate);
                    } else {
                        $rulesModel->setActiveTo(null);
                    }
                    $parentId = (int)$param['parent_id'];
                    $rulesModel->setParentId($parentId);
                    if ($storeId) {
                        $rulesModel->setStoreId($storeId);
                    } else {
                        $rulesModel->setStoreId(0);
                    }
                    $rulesModel->setPriceMethod(isset($param['price_method']) ? (int)$param['price_method'] : null);
                    $rulesModel->setDiscountPromoset(isset($param['discount_promoset']) ? $param['discount_promoset'] : null);
                    $rulesModel->setCode(isset($param['code']) ? (int)$param['code'] : null);
                    $rulesModel->setFixedPrice(isset($param['fixed_price']) ? $param['fixed_price'] : null);

                    $rulesModel->save();
                    $ruleId = (int)$rulesModel->getId();
                    $ruleIdsNorForDelete[] = $ruleId;
                    $tableGroup = $resource->getTableName('itoris_groupedproductpromotions_rules_group');
                    $valueUserGroup = $param['group'];
                    $connection->query("delete from {$tableGroup} where rule_id={$ruleId}");
                    foreach ($valueUserGroup as $group) {
                        if ($group != '') {
                            $connection->query("insert into {$tableGroup} (rule_id, group_id) values ({$ruleId}, {$group})");
                        }
                    }
                    $productRuleIds[$ruleId] = array();
                    if (!empty($subProductParam)) {
                        $params = array();
                        if (isset($subProductParam[$parentId])) {
                            $params = $subProductParam[$parentId];
                        } else {
                            if ($ruleIdDb != (int)$param['rule_id'] && isset($subProductParam[$ruleIdDb])) {
                                $params = $subProductParam[$ruleIdDb];
                            } elseif(isset($subProductParam[(int)$param['rule_id']])) {
                                $params = $subProductParam[(int)$param['rule_id']];
                            }
                        }

                        foreach ($params as $idSubProduct => $productParam) {
                            $subProductIdsNotForDelete[] = $idSubProduct;
                            $productModel = Mage::getModel('itoris_groupedproductpromotions/product');
                            if (isset($productParam['in_set'])) {
                                if (isset($productParam['product_rule_id_db'])) {
                                    $productModel->load((int)$productParam['product_rule_id_db']);
                                }
                                $productModel->setRuleId($ruleId);
                                $productModel->setProductId($idSubProduct);
                                $productModel->setInSet((int)$productParam['in_set']);
                                if (isset($productParam['qty'])) {
                                    $productModel->setQty((int)$productParam['qty']);
                                } else {
                                    $productModel->setQty(1);
                                }
                                if (isset($productParam['discount'])) {
                                    $productModel->setDiscount($productParam['discount']);
                                } else {
                                    $productModel->setDiscount(0);
                                }
                                $productModel->setType($productParam['type']);
                                if (isset($productParam['show_promoset'])) {
                                    $productModel->setShowPromoset($productParam['show_promoset']);
                                } else {
                                    $productModel->setShowPromoset(0);
                                }
                                $productModel->save();
                                if (array_key_exists($ruleId, $productRuleIds)) {
                                    $productRuleIds[$ruleId][] = $productModel->getId();
                                } else {
                                    $productRuleIds[$ruleId] = array($productModel->getId());
                                }
                            } elseif (isset($productParam['product_rule_id_db'])) {
                                $productModel->load((int)$productParam['product_rule_id_db']);
                                $productModel->delete();
                            }
                        }
                        if (!empty($subProductIdsNotForDelete)) {
                            $subProductIdsNotForDelete = implode(',', $subProductIdsNotForDelete);
                            $tableProduct = $resource->getTableName('itoris_groupedproductpromotions_rules_product');
                            $connection->query("delete from {$tableProduct} where `product_id` not in ({$subProductIdsNotForDelete}) and rule_id={$ruleId}");
                        }
                    }
                }
            }
            /*if (!empty($productRuleIds)) {
                foreach ($productRuleIds as $ruleId => $value) {
                    if (count($value) < 2 || empty($value)) {
                        Mage::getSingleton('core/session')->addError('At least 2 associated products should be selected in Promotion rule');
                        $table = $resource->getTableName('itoris_groupedproductpromotions_rules');
                        $connection->query("delete from {$table} where `rule_id`={$ruleId}");
                        continue;
                    }
                }
            }*/
        }
        if (!empty($ruleIdsNorForDelete)) {
            $ruleIdsNorForDelete = implode(',', $ruleIdsNorForDelete);
            $ruleIds = $connection->fetchAll("select `rule_id` from {$table} where `rule_id` not in ({$ruleIdsNorForDelete}) and product_id={$productId} and store_id={$storeId}");
            //$connection->query("delete from {$table}
            //    where `rule_id` in (select `rule_id` from {$table} where `rule_id` not in ({$ruleIdsNorForDelete}) and product_id={$productId} and store_id={$storeId})
            //    or `parent_id` in (select `rule_id` from {$table} where `rule_id` not in ({$ruleIdsNorForDelete}) and product_id={$productId} and store_id={$storeId})");
        } else {
            $ruleIds = $connection->fetchAll("select rule_id from {$table} where product_id={$productId} and store_id={$storeId}");
        }
        if (!empty($ruleIds)) {
            foreach ($ruleIds as $id) {
                $ruleId = $id['rule_id'];
                $connection->query("delete from {$table} where `rule_id` = {$ruleId} or `parent_id` = {$ruleId}");
            }
        }
    }
}
?>