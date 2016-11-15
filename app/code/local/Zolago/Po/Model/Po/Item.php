<?php

/**
 * Class Zolago_Po_Model_Po_Item
 * @method string getVendorSimpleSku()
 * @method string getName()
 * @method float getQty()
 */
class Zolago_Po_Model_Po_Item extends ZolagoOs_OmniChannelPo_Model_Po_Item
{
    protected $_additionalData = array();
    /**
     * @return array
     */
    public function getDiscountInfo() {
        if(!$this->hasData("discount_info")) {
            $collection = Mage::getResourceModel('zolagosalesrule/relation_collection');
            /* @var $collection Zolago_SalesRule_Model_Resource_Relation_Collection */
            $collection->addFieldToFilter("po_item_id", $this->getId());
            $this->setData('discount_info', $collection->getItems());
        }
        return $this->getData("discount_info");
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        if (!$this->getData('product')) {
            // Check if
            // ->setStoreId($po->getStore()->getId())
            // will not break something
            // NOTE: set store before load make correct attribute value loading
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            $this->setData('product', $product);
        }
        return $this->getData('product');
    }

    /**
     * @return Mage_Catalog_Helper_Image
     */
    public function getProductThumbHelper() {
        $_storeId = $this->getPo() ? $this->getPo()->getStoreId() : Mage::app()->getStore()->getStoreId();
        $thumb = Mage::getResourceModel("catalog/product")->getAttributeRawValue(
                     $this->getProductId(),
                     'thumbnail',
                     $_storeId
                 );

        $product = Mage::getModel("catalog/product")->
                   setId($this->getProductId())->
                   setThumbnail($thumb);
        return Mage::helper("catalog/image")->init($product, 'thumbnail');
    }


    /**
     * Overrride if we have no order item
     * @return int
     */
    public function getQtyToShip() {
        if(!$this->hasOrderItem()) {
            return max(0, $this->getQty()-$this->getQtyShipped()-$this->getQtyCanceled());
        }
        return parent::getQtyToShip();
    }

    /**
     * @return Mage_Sales_Model_Order_Item
     */
    public function getOrderItem() {
        if (is_null($this->_orderItem)) {
            if ($this->getPo()
                    && ($orderItem = Mage::helper('udropship')->getOrderItemById($this->getPo()->getOrder(), $this->getOrderItemId()))
               ) {
                $this->_orderItem = $orderItem;
            }
            else {
                $this->_orderItem = Mage::getModel('sales/order_item')
                                    ->load($this->getOrderItemId());
            }
            // Process abstract order item
            if(!$this->_orderItem->getId()) {
                Mage::helper("zolagopo")->prepareOrderItemByPoItem($this->_orderItem, $this);
                $order = $this->_orderItem->getOrder();
                /* @var $order Mage_Sales_Model_Order */
                $this->_orderItem->save();
                $this->setOrderItemId($this->_orderItem->getId());
                if($this->getId()) {
                    $this->getResource()->saveAttribute($this, "order_item_id");
                }
            }
        }
        return $this->_orderItem;
    }

    public function hasOrderItem() {
        return (bool)(int)$this->getOrderItem()->getId();
    }


    public function _beforeSave() {

        // Process order item if needed
        if(!$this->getOrderItemId()) {
            $this->getOrderItem();
        }

        // Transfer fields
        if((!$this->getId() || $this->isObjectNew()) && !$this->getSkipTransferOrderItemsData()) {
            $transferFields = array(
                                  "price_incl_tax",
                                  "base_price_incl_tax",
                                  "discount_amount",
                                  "discount_percent",
                                  "row_total",
                                  "row_total_incl_tax",
                                  "base_row_total_incl_tax",
                                  "parent_item_id"
                              );
            $orderItem = $this->getOrderItem();
            if($orderItem && $orderItem->getId()) {
                foreach($transferFields as $field) {
                    $this->setData($field, $orderItem->getData($field));
                }
            }

            /** @var Zolago_DropshipTierCommission_Helper_Data $hlp */
            $hlp = Mage::helper("udtiercom");
            $hlp->processPoCommission($this->getPo());
        }


        return parent::_beforeSave();
    }

    /**
     * @return float
     */
    public function getDiscount() {
        return round($this->getDiscountAmount()/$this->getQty(), 4);
    }

    public function getFinalItemPrice() {
        return round($this->getPriceInclTax() - $this->getDiscount(),4);
    }

    public function getConfigurableText() {
        $request = $this->getOrderItem()->getProductOptionByCode("attributes_info");
        $out = array();
        if(is_array($request)) {
            foreach($request as $item) {
                $out[] = Mage::helper("zolagopo")->__($item['label']) . ": " . Mage::helper("zolagopo")->__($item['value']);
            }
        }
        if($out) {
            return implode(", ", $out);
        }
        return "";
    }

    /**
     * @return string
     */
    public function getBundleText()
    {
        $bundleOptions = $this->getOrderItem()->getProductOptionByCode("bundle_options");

        $_helper = Mage::helper("zolagopo");
        $out = array();
        if (!empty($bundleOptions)) {
            $out[] = "<ul>";
            foreach ($bundleOptions as $key => $bundleOption) {
                $out[] = "<li>" . Mage::helper('core')->escapeHtml($bundleOption['label']) . ": ";
                if (isset($bundleOption['value'])) {
                    foreach ($bundleOption['value'] as $bundleOptionValue) {
                        $out[] = Mage::helper('core')->escapeHtml($bundleOptionValue['title']);
                        $out[] = "<br />" . $_helper->__("Qty") . " " . $bundleOptionValue['qty'];
                        $out[] = ", " . $_helper->__("Price") . " " . Mage::helper("core")->currency($bundleOptionValue['price']);
                    }
                }
                $out[] = "</li>";
            }
            $out[] = "</ul>";
        }
        if ($out) {
            return implode("", $out);
        }
        return "";
    }

    /**
     * @return string
     */
    public function getFinalSku() {
//	   $child = $this->getChildItem();
//	   if($child && $child->getId() && $child->getData('vendor_sku')){
//		   return $child->getData('vendor_sku');
//	   }
//
        if($this->getData('vendor_simple_sku')) {
            return $this->getData('vendor_simple_sku');
        }

        if ($this->getData('vendor_sku')) {
            return $this->getData('vendor_sku');
        }

        return $this->getData('sku');
    }


    /**
     * @todo not tested
     * @return Zolago_Po_Model_Po_Item
     */
    public function getParentOrderItem() {
        if(!$this->hasData("parent_order_item")) {
            $parent = Mage::getResourceModel('sales/order_item_collection')->
                      addFieldToFilter("item_id", $this->getParentItemId())->
                      getFirstItem();
            $this->setData("parent_order_item", $parent);
        }
        return $this->getData("parent_order_item");
    }

    /**
     * @todo not tested
     * @return Zolago_Po_Model_Po_Item
     */
    public function getParentItem() {
        if(!$this->hasData("parent_item")) {
            $parent = Mage::getResourceModel('zolagopo/po_item_collection')->
                      addFieldToFilter("order_item_id", $this->getParentItemId())->
                      getFirstItem();
            $this->setData("parent_item", $parent);
        }
        return $this->getData("parent_item");
    }

    /**
     * @return Zolago_Po_Model_Po_Item
     */
    public function getChildItem() {
        if(!$this->hasData("child_item")) {
            $parent = Mage::getResourceModel('zolagopo/po_item_collection')->
                      addFieldToFilter("parent_item_id", $this->getOrderItem()->getId())-> // Order item id ? @todo
                      getFirstItem();
            $this->setData("child_item", $parent);
        }
        return $this->getData("child_item");
    }

    /**
     * @return string
     */
    public function getOneLineDesc() {
        $configurable = $this->getConfigurableText();
        return $this->getName() . " " .
               "(".
               ($configurable ? $configurable . ", " : "") .
               Mage::helper("zolagopo")->__("Qty") .   ": " . (int)$this->getQty() . ", " .
               Mage::helper("zolagopo")->__("Price") . ": " . Mage::helper("core")->currency($this->getPriceInclTax(), true, false) . ", " .
               Mage::helper("zolagopo")->__("Discount").": " . Mage::helper("core")->currency($this->getDiscount(), true, false) . ", " .
               Mage::helper("zolagopo")->__("SKU") .   ": " . $this->getFinalSku() .
               ")";
    }

    public function getFinalRowPrice() {
        return $this->getRowTotalInclTax() - $this->getDiscountAmount();
    }
    
    /**
     * unserialize additional data
     */

    protected function _readAdditionalData() {
        if (!$this->_additionalData) {
            $rawData = $this->getData('additional_data');
            if ($rawData) {
                $this->_additionalData = unserialize($rawData);
            }
        }
    }
    
    /**
     * add value to additional data
     */

    public function setAdditionalData($key,$param) {
        $this->_readAdditionalData();
        $this->_additionalData[$key] = $param;
    }
    
    /**
     * get value from additional data
     */

    public function getAdditionalData($key = null) {
        $this->_readAdditionalData();
        if ($key) {
            return isset($this->_additionalData[$key])? $this->_additionalData[$key]:null;
        } else {
            return $this->_additionalData;
        }
    }
    // override save (serialize additional_data)
    public function save() {
        $data = $this->getAdditionalData();
        if ($data) {
            $this->setData('additional_data',serialize($data));
        }
        return parent::save();
    }
}
