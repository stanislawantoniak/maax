<?php

/**
 * Class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Entity
 */
class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Entity
    extends Mirasvit_FeedExport_Model_Feed_Generator_Action_Iterator_Entity
{
    public function getCollection()
    {
        if ($this->_type == 'product') {


            $feed = $this->getFeed();

            //Pre-Filters
            $productStatus = $feed->getProductStatus();
            $productVisibility = $feed->getProductVisibility();
            $productTypeId = $feed->getProductTypeId();
            $productInventoryIsInStock = $feed->getProductInventoryIsInStock();

            $collection = Mage::getModel('catalog/product')->getCollection()
                ->joinField('qty', 'cataloginventory/stock_item', 'qty',
                    'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                ->addStoreFilter();

            if (!empty($productStatus))
                $collection->addFieldToFilter("status", $productStatus);

            if (!empty($productVisibility))
                $collection->addFieldToFilter("visibility", $productVisibility);

            if (!empty($productTypeId))
                $collection->addFieldToFilter("type_id", $productTypeId);

            if (count($this->getFeed()->getRuleIds()) || Mage::app()->getRequest()->getParam('skip')) {
                $collection->getSelect()->joinLeft(
                    array('rule' => Mage::getSingleton('core/resource')->getTableName('feedexport/feed_product')),
                    'e.entity_id=rule.product_id', array())
                    ->where('rule.feed_id = ?', $this->getFeed()->getId())
                    ->where('rule.is_new = 1');
            }
        } elseif ($this->_type == 'category') {
            $root = Mage::getModel('catalog/category')->load($this->getFeed()->getStore()->getRootCategoryId());

            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')
                ->addFieldToFilter('entity_id', array('nin' => array(1, 2)));

            if (method_exists($collection, 'addPathFilter')) {
                $collection->addPathFilter($root->getPath());
            }
        } elseif ($this->_type == 'review') {
            $collection = Mage::getModel('review/review')->getResourceCollection();

            $collection->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('entity_id', 1)
                ->setDateOrder()
                ->addRateVotes()
                ->load();
        }

        return $collection;
    }
}