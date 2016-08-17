<?php

class Zolago_Adminhtml_Model_Sales_Transactions_Source extends Varien_Object
{

    /**
     * @param bool $selector
     * @return array
     */
    public function toOptionHash($selector = false)
    {
        switch ($this->getPath()) {
            case 'orders_info':
                $out = $this->_getOrdersInfo();
                break;
            default:
                $out = parent::toOptionHash($selector);
        }
        return $out;
    }


    /**
     * @return array
     */
    protected function _getOrdersInfo()
    {
        $orders = array();

        $dateModel = Mage::getModel('core/date');
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales/order')),
            'main_table.order_id=order.entity_id',
            array('*')
        );
//        $collection->getSelect()->join(
//            array('order_payment' => $collection->getTable('sales/order_payment')),
//            'order_payment.parent_id=main_table.order_id',
//            array('*')
//        );

        //$collection->addAttributeToFilter('order_payment.method', "banktransfer");
        $collection->addAttributeToFilter('main_table.parent_item_id', array("null" => true));

        //Last 2 month orders
        $collection->addAttributeToFilter('order.created_at', array("gteq" => new Zend_Db_Expr("DATE_SUB(CURDATE(), INTERVAL 2 MONTH)")));

        $collection->getSelect()->order("order.created_at DESC");

        $_resource = Mage::getSingleton('catalog/product')->getResource();

        $options = array();
        foreach ($collection as $collectionItem) {
            $options[$collectionItem->getEntityId()]["increment_id"] = $collectionItem->getIncrementId();
            $options[$collectionItem->getEntityId()]["date"] = date('d.m.Y', $dateModel->timestamp(strtotime($collectionItem->getCreatedAt())));
            $options[$collectionItem->getEntityId()]["order_total"] = Mage::helper('core')->currency($collectionItem->getBaseGrandTotal(), true, false);
            $options[$collectionItem->getEntityId()]["customer_firstname"] = $collectionItem->getData('customer_firstname');
            $options[$collectionItem->getEntityId()]["customer_lastname"] = $collectionItem->getData('customer_lastname');
            $options[$collectionItem->getEntityId()]["customer_email"] = $collectionItem->getData('customer_email');

            $options[$collectionItem->getEntityId()]["items"][$collectionItem->getItemId()] = array(
                "id" => $collectionItem->getProductId(),
                "name" => $collectionItem->getName(),
                "sku" => $_resource->getAttributeRawValue($collectionItem->getProductId(),  "skuv", 0)
            );
        }

        foreach ($options as $orderId => $option) {
            $out = "<div class='banktransfer-row'>";
            $out .= "<div class='banktransfer-item banktransfer-left'><b>" . $option["increment_id"] . "</b><br> ".$option['customer_firstname']." ".$option['customer_lastname']."<br>".$option['customer_email']."</div>";


            $out .= "<div class='banktransfer-item banktransfer-center'><ul>";
            foreach ($option["items"] as $optionItem) {
                $out .= "<li>" . $optionItem['name'] . " (" . $optionItem['sku'] . ")</li>";
            }
            $out .= "</ul></div>";


            $out .= "<div class='banktransfer-item banktransfer-right'>"."<b>" . Mage::helper("zolagoadminhtml")->__("Created At") .": <i> ". $option["date"] . "</i></b><br>"."<b>" . Mage::helper("zolagoadminhtml")->__("Order Total") .": <i> ". $option["order_total"] . "</i></b>"."</div>";
            $out .= "</div>";

            $out .= "<div class='banktransfer-row banktransfer-line'></div>";


            $orders[$orderId] = $out;
        }

        return $orders;
    }

}
 