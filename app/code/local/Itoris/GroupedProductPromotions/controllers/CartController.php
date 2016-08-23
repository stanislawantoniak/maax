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

  

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . '/CartController.php';

class Itoris_GroupedProductPromotions_CartController extends Mage_Checkout_CartController {

	public function addAction() {
        try {
            $this->_getSession()->getMessages(true);
            $url = $this->getRequest()->getParam('back_url');
            $this->_getSession()->setRedirectUrl($url);
            $params = $this->getRequest()->getParams();
            $ruleId = $params['rule_id'];
            $cart = $this->_getCart();
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('read');
            $tableProduct = $resource->getTableName('itoris_groupedproductpromotions_rules_product');
            $productIds = $connection->fetchAll("select product_id, qty from {$tableProduct} where rule_id={$ruleId}");
            $qtyCount = 0;
            foreach ($productIds as $value) {
                $qty = (int)$value['qty'];
                $productId = (int)$value['product_id'];
                if ($qty >= 1) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $productOptions = $product->getOptions();
                    $options = '';
                    foreach ($productOptions as $optionId => $option) {
                        $options .=  $optionId . ',';
                    }
                    $options = substr($options, 0, -1);
                    $optionArray = array();
                    if (array_key_exists('options', $params)) {
                        foreach ($params['options'] as $idOption => $checkValue) {
                            if (strpos($options, (string)$idOption) !== false) {
                                $optionArray[$idOption] = $checkValue;
                            }
                        }
                    }
                    $bundleOptions = array();
                    $bundleOptionsQty = array();
                    $related = array_key_exists('related_product', $params) ? $params['related_product'] : null;
                    if (array_key_exists('bundle_option', $params) && array_key_exists($productId, $params['bundle_option'])) {
                        $bundleOptions = $params['bundle_option'][$productId];
                    }
                    if (array_key_exists('bundle_option_qty', $params) && array_key_exists($productId, $params['bundle_option_qty'])) {
                        $bundleOptionsQty = $params['bundle_option_qty'][$productId];
                    }

                    $associatedProductParams = array(
                        'product' => (string)$productId,
                        'related_product' => $related,
                        'options' => $optionArray,
                        'super_attribute' => isset($params['super_product'][$productId]['super_attribute'])
                            ? $params['super_product'][$productId]['super_attribute'] : null,
                        'bundle_option' => $bundleOptions,
                        'bundle_option_qty' => $bundleOptionsQty,
                        'qty' => $qty
                    );
                    $cart->addProduct($product, $associatedProductParams);

                    if (!empty($related)) {
                        $cart->addProductsByIds(explode(',', $related));
                    }
                    $qtyCount++;
                }
            }
            if ($qtyCount) {
                $cart->save();
                $message = Mage::helper('checkout')->__('Promoset was added to your shopping cart.');
                Mage::getSingleton('core/session')->addSuccess($message);
                $this->_redirect('checkout/cart');
            } else {
                Mage::getSingleton('core/session')->addNotice('Please specify the quantity of product(s)');
                if ($url) {
                    $this->getResponse()->setRedirect($url);
                } else {
                    $this->_redirect('checkout/cart');
                }
            }
            parent::addAction();
            $this->getResponse()->setHttpResponseCode(200);
            $this->_getSession()->getMessages(true);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_goBack();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_goBack();
        }
	}
}
?>