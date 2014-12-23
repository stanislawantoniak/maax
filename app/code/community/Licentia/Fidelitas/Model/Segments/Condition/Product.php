<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Model_Segments_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product {

    protected function _addSpecialAttributes(array &$attributes) {
        parent::_addSpecialAttributes($attributes);
//        $attributes['quote_item_qty'] = Mage::helper('fidelitas')->__('Quantity in cart');
//        $attributes['quote_item_price'] = Mage::helper('fidelitas')->__('Price in cart');
//        $attributes['quote_item_row_total'] = Mage::helper('fidelitas')->__('Row total in cart');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object) {

        $list = Mage::registry('current_list');

        $dbAttrName = $this->getAttribute();

        $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                ->addAttributeToFilter('customer_id', $object->getId());

        $ordersIds = array();
        foreach ($orders as $order) {
            $ordersIds[] = $order->getId();
        }

        if ($orders->count() == 0) {
            return false;
        }

        $items = Mage::getResourceModel('sales/order_item_collection')
                ->addAttributeToFilter('order_id', array('in' => $ordersIds));

        foreach ($items as $product) {
            $productIds[] = $product->getProductId();
        }

        $productIds = array_unique($productIds);

        $parsed = $this->getValueParsed();

        if (stripos($this->translateOperator(), 'like') !== false) {

            $parsed = '%' . $parsed . '%';
        }

        $products = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect($dbAttrName)
                ->addAttributeToFilter('entity_id', array('in' => $productIds))
                ->addAttributeToFilter($dbAttrName, array($this->translateOperator() => $parsed));


        if ($products->count() == 0) {
            return false;
        } else {
            return true;
        }

        return parent::validate($object);
    }

    public function collectValidatedAttributes($customerCollection) {

        $attribute = $this->getAttribute();
        $attributes = $this->getRule()->getCollectedAttributes();
        $attributes[$attribute] = true;
        $this->getRule()->setCollectedAttributes($attributes);
        return $this;
    }

    public function translateOperator() {

        $operator = $this->getOperator();

        $newValue = array('==' => 'eq', '!=' => 'neq', '>=' => 'gteq', '<=' => 'lteq', '>' => 'gt', '<' => 'lt', '{}' => 'like', '!{}' => 'nlike', '()' => 'in', '!()' => 'nin');

        if (isset($newValue[$operator]))
            return $newValue[$operator];

        return 'eq';
    }

}
