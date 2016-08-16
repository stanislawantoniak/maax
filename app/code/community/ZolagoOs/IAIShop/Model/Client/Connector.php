<?php

/**
 * Class ZolagoOs_IAIShop_Model_Client_Connector
 */
class ZolagoOs_IAIShop_Model_Client_Connector
    extends ZolagoOs_IAIShop_Model_Client
{


    /**
     * @see http://www.iai-shop.com/api.phtml?action=method&function=getproducts&method=getProducts
     */
    public function getProducts($params)
    {
        $request = array();
        $action = "getProducts";
        $request[$action]['params'] = $params;
        return $this->doRequest("getproducts", $action, $request);
    }


    /**
     * @see http://www.iai-shop.com/api.phtml?action=method&function=addorders&method=addOrders
     */
    public function addOrders($params)
    {
        /** @var ZolagoOs_IAIShop_Helper_Data $helper */
        $helper = Mage::helper('zosiaishop');
        $orders = array();



        foreach ($params as $n => $param) {
            $orders[$n]['order_type'] = "retail";
            if ($this->getShopId()) {            
                $orders[$n]['shop_id'] = $this->getShopId();
            }
            $orders[$n]['stock_id'] = $param->pos_id;
            $orders[$n]['payment_type'] = $param->payment_method;
            $orders[$n]['currency'] = $helper->getMappedCurrency($param->order_currency);
            $orders[$n]['client_once'] = "y";
            
            $orders[$n]['order_operator'] = $helper->getOrderOperator();
            $orders[$n]['ignore_bridge'] = true;

            $orders[$n]['payment_type'] = $helper->getMappedPayment($param->payment_method);


            $delivery_address = array();
            $delivery_address['firstname'] = $param->delivery_data->delivery_address->delivery_first_name;
            $delivery_address['lastname'] = $param->delivery_data->delivery_address->delivery_last_name;
            $delivery_address['street'] = $param->delivery_data->delivery_address->delivery_street;
            $delivery_address['zip_code'] = $param->delivery_data->delivery_address->delivery_zip_code;
            $delivery_address['city'] = $param->delivery_data->delivery_address->delivery_city;
            $delivery_address['country'] = $helper->getMappedCountry($param->delivery_data->delivery_address->delivery_country);
            $delivery_address['phone'] = $param->delivery_data->delivery_address->phone;
            $delivery_address['additional'] = $param->delivery_data->delivery_point_name;

            $orders[$n]['delivery_address'] = $delivery_address;
            $orders[$n]['delivery_address']['additional'] = $param->delivery_data->inpost_locker_id;

            $orders[$n]['deliverer_id'] = $helper->getMappedDelivery($param->delivery_method);
            $orders[$n]['client_notes'] = $helper->__('Order %s from Modago',$param->order_id);

            $orders[$n]['invoice_requested'] = $param->invoice_data->invoice_required ? 'y':'n';
            if ($param->invoice_data->invoice_required) {
                $orders[$n]['client_once_data']['firstname'] = $param->invoice_data->invoice_address->invoice_first_name;
                $orders[$n]['client_once_data']['lastname'] = $param->invoice_data->invoice_address->invoice_last_name;
                $orders[$n]['client_once_data']['firm'] = $param->invoice_data->invoice_address->invoice_company_name;
                $orders[$n]['client_once_data']['street'] = $param->invoice_data->invoice_address->invoice_street;
                $orders[$n]['client_once_data']['zip_code'] = $param->invoice_data->invoice_address->invoice_street;
                $orders[$n]['client_once_data']['city'] = $param->invoice_data->invoice_address->invoice_city;
                $orders[$n]['client_once_data']['country'] = $helper->getMappedCountry($param->invoice_data->invoice_address->invoice_country);
            } else {
                $orders[$n]['client_once_data'] = $delivery_address;
            }


            $products = array();
            if (!is_array($param->order_items)) {
                $order_items = $param->order_items->item;
            } else {
                $order_items = $param->order_items;
            }
            foreach ($order_items as $i => $order_item) {
                if (!$order_item->is_delivery_item) {
                    $products[$i]['product_sizecode'] = $order_item->item_sku;
                    $products[$i]['rebate'] = $order_item->item_discount;
                    $products[$i]['quantity'] = $order_item->item_qty;
                    $products[$i]['price'] = $order_item->item_value_before_discount;

                    $products[$i]['stock_id'] = $param->pos_id;
                } else {
                    $orders[$n]['delivery_cost'] = $order_item->item_value_after_discount;
                }
            }
            $orders[$n]['products'] = $products;
            unset($i, $products);
        }

        $action = "addOrders";
        $request = array();
        $request[$action] = array();
        $request[$action]['params']['orders'] = $orders;
        return $this->doRequest("addorders", $action, $request);
    }

    /**
     * @see http://www.iai-shop.com/api.phtml?action=method&function=payments&method=addPayment
     */
    public function addPayment($param)
    {
        $helper = Mage::helper('zosiaishop');

        $action = "addPayment";

        $request = array();
        $request[$action] = array();
        $request[$action]['params'] = array();

        $request[$action]['params']['order_number'] = $param->external_order_id;
        $request[$action]['params']['value'] = $param->order_total;
        $request[$action]['params']['type'] = 'payment';
        $request[$action]['params']['payment_form_id'] = $param->payment_method_external_id;

        return $this->doRequest("payments", $action, $request);
    }

    /**
     * @see http://www.iai-shop.com/api.phtml?action=method&function=payments&method=getPaymentForms
     */
    public function getPaymentForms()
    {
        $action = "getPaymentForms";

        $request = array();
        $request[$action] = array();

        return $this->doRequest("payments", $action, $request);
    }

    /**
     * @see http://www.iai-shop.com/api.phtml?action=method&function=getorders&method=getOrders
     */
    public function getOrders($params)
    {

        $helper = Mage::helper('zosiaishop');
        $action = "getOrders";

        $request = array();
        $request['getOrders'] = array();
        $request['getOrders']['params'] = array();
        $request['getOrders']['params']['orders_status'] = array();
        $request['getOrders']['params']['deliverers'] = array();
        $request['getOrders']['params']['orders_id'] = array();
        $request['getOrders']['params']['clients'] = array();
        $request['getOrders']['params']['stocks'] = array();
        $request['getOrders']['params']['orders_sn'] = array();
        foreach ($params as $order) {
            $request['getOrders']['params']['orders_sn'][] = $order;
        }
        $request['getOrders']['params']['order_by'] = array();
        $request['getOrders']['params']['operator_match'] = $helper->getOrderOperator();



        return $this->doRequest("getorders", $action, $request);
    }
}