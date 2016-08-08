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
        $orders = array();

        $deliveryMethodsMapping = array(
            "polish_post" => 6,
            "standard_courier" => 5,
            "inpost_parcel_locker" => 7
        );

        //Sposób zapłaty za zamówienie.
        // Dopuszczalne wartości
        // "cash_on_delivery" - pobranie,
        // "prepaid" - przedplata,
        // "tradecredit" - kredytKupiecki.
        $paymentMethodsMapping = array(
            "online_bank_transfer" => "prepaid",
            "credit_card" => "prepaid",
            "bank_transfer" => "tradecredit",
            "cash_on_delivery" => "cash_on_delivery"

        );

        $countries = array("PL" => "Polska");

        foreach ($params as $n => $param) {
            $orders[$n]['order_type'] = "retail";
            $orders[$n]['shop_id'] = $this->getShopId();
            $orders[$n]['stock_id'] = $param->pos_id;
            $orders[$n]['payment_type'] = $param->payment_method;
            $orders[$n]['currency'] = $param->order_currency;
            $orders[$n]['client_once'] = "y";
            $orders[$n]['order_operator'] = "MODAGO";
            $orders[$n]['ignore_bridge'] = true;

            $orders[$n]['payment_type'] = $paymentMethodsMapping[$param->payment_method];


            $delivery_address = array();
            $delivery_address['firstname'] = $param->delivery_data->delivery_address->delivery_first_name;
            $delivery_address['lastname'] = $param->delivery_data->delivery_address->delivery_last_name;
            $delivery_address['street'] = $param->delivery_data->delivery_address->delivery_street;
            $delivery_address['zip_code'] = $param->delivery_data->delivery_address->delivery_zip_code;
            $delivery_address['city'] = $param->delivery_data->delivery_address->delivery_city;
            $delivery_address['country'] = $countries[$param->delivery_data->delivery_address->delivery_country];
            $delivery_address['phone'] = $param->delivery_data->delivery_address->phone;
            $delivery_address['additional'] = $param->delivery_data->inpost_locker_id;

            $orders[$n]['delivery_address'] = $delivery_address;
            $orders[$n]['delivery_address']['additional'] = $param->delivery_data->inpost_locker_id;

            $orders[$n]['deliverer_id'] = $deliveryMethodsMapping[$param->delivery_method];

            $orders[$n]['invoice_requested'] = $param->invoice_data->invoice_required;
            if ($param->invoice_data->invoice_required) {
                $orders[$n]['client_once_data']['firstname'] = $param->invoice_data->invoice_address->invoice_first_name;
                $orders[$n]['client_once_data']['lastname'] = $param->invoice_data->invoice_address->invoice_last_name;
                $orders[$n]['client_once_data']['firm'] = $param->invoice_data->invoice_address->invoice_company_name;
                $orders[$n]['client_once_data']['street'] = $param->invoice_data->invoice_address->invoice_street;
                $orders[$n]['client_once_data']['zip_code'] = $param->invoice_data->invoice_address->invoice_street;
                $orders[$n]['client_once_data']['city'] = $param->invoice_data->invoice_address->invoice_city;
                $orders[$n]['client_once_data']['country'] = $countries[$param->invoice_data->invoice_address->invoice_country];
            } else {
                $orders[$n]['client_once_data'] = $delivery_address;
            }


            $products = array();
            $order_items = $param->order_items->item;
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

        //krumo($orders);
        $action = "addOrders";

        $request = array();
        $request[$action] = array();
        $request[$action]['params']['orders'] = $orders;

        return $this->doRequest("addorders", $action, $request);
        


        $request = array();
        $request['addOrders'] = array();

        $request['addOrders']['params'] = array();
        $request['addOrders']['params']['orders'] = array();
        $request['addOrders']['params']['orders'][0] = array();
        $request['addOrders']['params']['orders'][0]['order_type'] = "retail";
        $request['addOrders']['params']['orders'][0]['shop_id'] = 1;
        $request['addOrders']['params']['orders'][0]['stock_id'] = 5;
        $request['addOrders']['params']['orders'][0]['payment_type'] = 'cash_on_delivery';
        $request['addOrders']['params']['orders'][0]['currency'] = "PLN";
        $request['addOrders']['params']['orders'][0]['client_once'] = "y";
        $request['addOrders']['params']['orders'][0]['client_once_data'] = array();
        $request['addOrders']['params']['orders'][0]['client_once_data']['firstname'] = "Klient";
        $request['addOrders']['params']['orders'][0]['client_once_data']['lastname'] = "Testowy";
        $request['addOrders']['params']['orders'][0]['client_once_data']['firm'] = "firm";
        $request['addOrders']['params']['orders'][0]['client_once_data']['nip'] = "nip";
        $request['addOrders']['params']['orders'][0]['client_once_data']['street'] = "street";
        $request['addOrders']['params']['orders'][0]['client_once_data']['zip_code'] = "05-270";
        $request['addOrders']['params']['orders'][0]['client_once_data']['city'] = "city";
        $request['addOrders']['params']['orders'][0]['client_once_data']['country'] = "Polska";
        $request['addOrders']['params']['orders'][0]['client_once_data']['email'] = "admin@iai-sa.com";
        $request['addOrders']['params']['orders'][0]['client_once_data']['phone1'] = "2345234234234";
        $request['addOrders']['params']['orders'][0]['client_once_data']['phone2'] = "23423423423423";
        $request['addOrders']['params']['orders'][0]['client_once_data']['language_id'] = 1;
        $request['addOrders']['params']['orders'][0]['client_login'] = "iaiadmin1";
        $request['addOrders']['params']['orders'][0]['deliverer_id'] = 3;
        $request['addOrders']['params']['orders'][0]['delivery_cost'] = 4.0;
        $request['addOrders']['params']['orders'][0]['delivery_address'] = array();
        $request['addOrders']['params']['orders'][0]['delivery_address']['firstname'] = "Klient";
        $request['addOrders']['params']['orders'][0]['delivery_address']['lastname'] = "lastname";
        $request['addOrders']['params']['orders'][0]['delivery_address']['additional'] = "additional";
        $request['addOrders']['params']['orders'][0]['delivery_address']['street'] = "street";
        $request['addOrders']['params']['orders'][0]['delivery_address']['zip_code'] = "05-270";
        $request['addOrders']['params']['orders'][0]['delivery_address']['city'] = "city";
        $request['addOrders']['params']['orders'][0]['delivery_address']['country'] = "Polska";
        $request['addOrders']['params']['orders'][0]['delivery_address']['phone'] = "4534563456345";
        $request['addOrders']['params']['orders'][0]['products'] = array();
        $request['addOrders']['params']['orders'][0]['products'][0] = array();
        $request['addOrders']['params']['orders'][0]['products'][0]['id'] = 4;
        $request['addOrders']['params']['orders'][0]['products'][0]['size_id'] = "1";
        //$request['addOrders']['params']['orders'][0]['products'][0]['product_sizecode'] = "product_sizecode";
        //$request['addOrders']['params']['orders'][0]['products'][0]['stock_id'] = 6;
        $request['addOrders']['params']['orders'][0]['products'][0]['quantity'] = 1;
        $request['addOrders']['params']['orders'][0]['products'][0]['price'] = 200;
        //$request['addOrders']['params']['orders'][0]['products'][0]['remarks'] = "remarks";
//        $request['addOrders']['params']['orders'][0]['wrappers'] = array();
//        $request['addOrders']['params']['orders'][0]['wrappers'][0] = array();
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['id'] = 10;
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['stock_id'] = 11;
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['quantity'] = 12.0;
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['price'] = 13.0;
        $request['addOrders']['params']['orders'][0]['rebate'] = 14.0;
        $request['addOrders']['params']['orders'][0]['order_operator'] = "MODAGO";
        $request['addOrders']['params']['orders'][0]['ignore_bridge'] = true;
        $request['addOrders']['params']['orders'][0]['settings'] = array();
        $request['addOrders']['params']['orders'][0]['settings']['send_mail'] = false;
        $request['addOrders']['params']['orders'][0]['settings']['send_sms'] = false;
        $request['addOrders']['params']['orders'][0]['invoice_requested'] = "invoice_requested";



        return $this->doRequest("addorders", $action, $request);
    }

	/**
	 * @see http://www.iai-shop.com/api.phtml?action=method&function=payments&method=addPayment
	 */
	public function addPayment($params)
	{

		$action = "addPayment";

		$request = array();
		$request[$action] = array();
		$request['addPayment']['params'] = $params; //array();

//		$request['addPayment']['params']['order_number'] = 1;
//		$request['addPayment']['params']['source_id'] = 2;
//		$request['addPayment']['params']['source_type'] = 3;
//		$request['addPayment']['params']['value'] = 4.0;
//		$request['addPayment']['params']['account'] = "account";
//		$request['addPayment']['params']['type'] = 'payment';
//		$request['addPayment']['params']['payment_form_id'] = 5;
//		$request['addPayment']['params']['accounting_date'] = "accounting_date";
//		$request['addPayment']['params']['external_payment_id'] = "external_payment_id";

		return $this->doRequest("payments", $action, $request);
	}

	/**
	 * @see http://www.iai-shop.com/api.phtml?action=method&function=getorders&method=getOrders
	 */
	public function getOrders($params)
	{

		$action = "getOrders";

		$request = array();
		$request['getOrders'] = array();
		$request['getOrders']['params'] = $params; //array();

//		$request['getOrders']['params']['prepaid_status'] = "prepaid_status";
//		$request['getOrders']['params']['orders_status'] = array();
//		$request['getOrders']['params']['orders_status'][0] = "orders_status";
//		$request['getOrders']['params']['deliverers'] = array();
//		$request['getOrders']['params']['deliverers'][0] = "deliverers";
//		$request['getOrders']['params']['payment_type'] = "payment_type";
//		$request['getOrders']['params']['orders_id'] = array();
//		$request['getOrders']['params']['orders_id'][0] = "orders_id";
//		$request['getOrders']['params']['orders_sn'] = array();
//		$request['getOrders']['params']['orders_sn'][0] = 1;
//		$request['getOrders']['params']['clients'] = array();
//		$request['getOrders']['params']['clients'][0] = array();
//		$request['getOrders']['params']['clients'][0]['login'] = "login";
//		$request['getOrders']['params']['clients'][0]['firstname'] = "firstname";
//		$request['getOrders']['params']['clients'][0]['lastname'] = "lastname";
//		$request['getOrders']['params']['clients'][0]['city'] = "city";
//		$request['getOrders']['params']['clients'][0]['email'] = "email";
//		$request['getOrders']['params']['clients'][0]['has_tax_no'] = "has_tax_no";
//		$request['getOrders']['params']['clients'][0]['searching_mode'] = "searching_mode";
//		$request['getOrders']['params']['clients'][0]['company'] = "company";
//		$request['getOrders']['params']['clients'][0]['country_id'] = "country_id";
//		$request['getOrders']['params']['clients'][0]['region_name'] = "region_name";
//		$request['getOrders']['params']['range'] = array();
//		$request['getOrders']['params']['range']['date'] = array();
//		$request['getOrders']['params']['range']['date']['type'] = 'add';
//		$request['getOrders']['params']['range']['date']['typesDate'] = array();
//		$request['getOrders']['params']['range']['date']['typesDate'][0] = 'add';
//		$request['getOrders']['params']['range']['date']['date_begin'] = "date_begin";
//		$request['getOrders']['params']['range']['date']['date_end'] = "date_end";
//		$request['getOrders']['params']['range']['sn'] = array();
//		$request['getOrders']['params']['range']['sn']['sn_begin'] = 2;
//		$request['getOrders']['params']['range']['sn']['sn_end'] = 3;
//		$request['getOrders']['params']['source'] = array();
//		$request['getOrders']['params']['source']['shops_mask'] = 4;
//		$request['getOrders']['params']['source']['shops_id'] = array();
//		$request['getOrders']['params']['source']['shops_id'][0] = 5;
//		$request['getOrders']['params']['source']['auctions_params'] = array();
//		$request['getOrders']['params']['source']['auctions_params']['systems'] = array();
//		$request['getOrders']['params']['source']['auctions_params']['systems'][0] = "systems";
//		$request['getOrders']['params']['source']['auctions_params']['numbers'] = array();
//		$request['getOrders']['params']['source']['auctions_params']['numbers'][0] = 6;
//		$request['getOrders']['params']['source']['auctions_params']['accounts'] = array();
//		$request['getOrders']['params']['source']['auctions_params']['accounts'][0] = array();
//		$request['getOrders']['params']['source']['auctions_params']['accounts'][0]['account_id'] = 7;
//		$request['getOrders']['params']['source']['auctions_params']['accounts'][0]['account_login'] = "account_login";
//		$request['getOrders']['params']['source']['auctions_params']['clients'] = array();
//		$request['getOrders']['params']['source']['auctions_params']['clients'][0] = array();
//		$request['getOrders']['params']['source']['auctions_params']['clients'][0]['client_id'] = 8;
//		$request['getOrders']['params']['source']['auctions_params']['clients'][0]['client_login'] = "client_login";
//		$request['getOrders']['params']['products'] = array();
//		$request['getOrders']['params']['products'][0] = array();
//		$request['getOrders']['params']['products'][0]['id'] = 9;
//		$request['getOrders']['params']['products'][0]['name'] = "name";
//		$request['getOrders']['params']['products'][0]['size'] = "size";
//		$request['getOrders']['params']['products'][0]['size_name'] = "size_name";
//		$request['getOrders']['params']['results_page'] = 10;
//		$request['getOrders']['params']['results_limit'] = 11;
//		$request['getOrders']['params']['invoice_requested'] = "invoice_requested";
//		$request['getOrders']['params']['packages'] = array();
//		$request['getOrders']['params']['packages']['numbers'] = array();
//		$request['getOrders']['params']['packages']['numbers'][0] = "numbers";
//		$request['getOrders']['params']['packages']['has_number'] = "has_number";
//		$request['getOrders']['params']['stocks'] = array();
//		$request['getOrders']['params']['stocks'][0] = array();
//		$request['getOrders']['params']['stocks'][0]['stock_id'] = 12;
//		$request['getOrders']['params']['campaign'] = array();
//		$request['getOrders']['params']['campaign']['campaignId'] = 13;
//		$request['getOrders']['params']['campaign']['discountCodes'] = array();
//		$request['getOrders']['params']['campaign']['discountCodes'][0] = "discountCodes";
//		$request['getOrders']['params']['loyalty_points_flag'] = 'all';
//		$request['getOrders']['params']['operator'] = "operator";
//		$request['getOrders']['params']['person_packing'] = "person_packing";
//		$request['getOrders']['params']['order_by'] = array();
//		$request['getOrders']['params']['order_by'][0] = array();
//		$request['getOrders']['params']['order_by'][0]['element_name'] = "element_name";
//		$request['getOrders']['params']['order_by'][0]['sort_direction'] = "sort_direction";
//		$request['getOrders']['params']['operator_match'] = 'no_assignment';

		return $this->doRequest("getorders", $action, $request);
	}
}