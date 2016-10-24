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
        if (Mage::helper('zolagocommon')->useGalleryConfiguration()) {
            Mage::throwException('Do not use on gallery');
            // poza galerią to działa tylko przez przypadek
            // na galerii nie będzie działać wcale
        }


        $orders = array();

        $dateModel = Mage::getModel('core/date');
        $collection = Mage::getResourceModel('udpo/po_item_collection');

        $collection->getSelect()->join(
            array('po' => $collection->getTable('udpo/po')),
            'main_table.parent_id=`po`.entity_id',
            array('po.grand_total_incl_tax','po.increment_id','item_entity_id'=>'main_table.entity_id','po.order_id')
       );
        $collection->getSelect()
            ->columns('concat(main_table.entity_id,"_",po.entity_id) as unique_id');

        $collection->getSelect()->join(
            array('order_payment' => $collection->getTable('sales/order_payment')),
            'order_payment.parent_id=`po`.order_id',
            array('*')
        );

//        $collection->addAttributeToFilter('order_payment.method', "banktransfer");
        $collection->addAttributeToFilter('main_table.parent_item_id', array("null" => true));

        //Last 2 month orders
        $collection->addAttributeToFilter('po.created_at', array("gteq" => new Zend_Db_Expr("DATE_SUB(CURDATE(), INTERVAL 2 MONTH)")));
        $collection->setRowIdFieldName('unique_id');
        $collection->getSelect()->order("po.created_at DESC");

        $options = array();
        foreach ($collection as $collectionItem) {
            $parentId = $collectionItem->getParentId();

            $options[$parentId]["increment_id"] = $collectionItem->getIncrementId();
            $options[$parentId]["date"] = date('d.m.Y', $dateModel->timestamp(strtotime($collectionItem->getCreatedAt())));
            $options[$parentId]["order_total"] = Mage::helper('core')->currency($collectionItem->getGrandTotalInclTax(), true, false);
            $options[$parentId]["customer_firstname"] = $collectionItem->getData('customer_firstname');
            $options[$parentId]["customer_lastname"] = $collectionItem->getData('customer_lastname');
            $options[$parentId]["customer_email"] = $collectionItem->getData('customer_email');
            $options[$parentId]["payment_method"] = $collectionItem->getData('method');

            $options[$parentId]["items"][$collectionItem->getItemEntityId()] = array(
                "id" => $collectionItem->getProductId(),
                "name" => $collectionItem->getName(),
                "sku" => $collectionItem->getVendorSku()
            );
        }

        foreach ($options as $orderId => $option) {
            $out = "<div class='banktransfer-row' title='" . $option["increment_id"] . "'>";
            $out .= "<div class='banktransfer-item banktransfer-left'><b>" . $option["increment_id"] . "</b><br> ".$option['customer_firstname']." ".$option['customer_lastname']."<br>".$option['customer_email']."</div>";


            $out .= "<div class='banktransfer-item banktransfer-center'><ul>";
            foreach ($option["items"] as $optionItem) {
                $out .= "<li>" . $optionItem['name'] . " (" . $optionItem['sku'] . ")</li>";
            }
            $out .= "</ul></div>";


            $out .= "<div class='banktransfer-item banktransfer-right'>"."<b>" . Mage::helper("zolagoadminhtml")->__("Created At") .": <i> ". $option["date"] . "</i></b><br>"."<b>" . Mage::helper("zolagoadminhtml")->__("Order Total") .": <i> ". $option["order_total"] . 
            "</i></b><br/>".Mage::helper('zolagoadminhtml')->__('Payment method').": ". Mage::helper('ghgtm')->getPaymentMethodName($option['payment_method']).
            "</div>";
            $out .= "</div>";

            $out .= "<div class='banktransfer-row banktransfer-line'></div>";


            $orders[$orderId] = $out;
        }

        return $orders;
    }

}
 