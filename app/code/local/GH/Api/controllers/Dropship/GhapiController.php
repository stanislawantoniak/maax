<?php

class GH_Api_Dropship_GhapiController extends Zolago_Dropship_Controller_Vendor_Abstract {

	public function indexAction() {

        // FIX for ACL - GH API Access
        $vendor = $this->_getSession()->getVendor();
        if ($vendor->getData('ghapi_vendor_access_allow')) {
            $this->_renderPage(null, 'ghapi');
        } else {
            return $this->_redirect('udropship/vendor/dashboard');
        }

    }


    public function saveAction()
    {
        // FIX for ACL - GH API Access
        $vendor = $this->_getSession()->getVendor();
        if (!$vendor->getData('ghapi_vendor_access_allow')) {
            return $this->_redirect('udropship/vendor/dashboard');
        }

        $helper = Mage::helper('ghapi');
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }

        $vendorId = (int)$vendor->getVendorId();

        $ghapiVendorPassword = $this->getRequest()->getPost('ghapi_vendor_password');

        /* @var $ghApiUser GH_Api_Model_User */
        $ghApiUser = Mage::getModel('ghapi/user');
        $vendorApiUser = $ghApiUser->loadByVendorId($vendorId);

        // If Edit
        if (is_null($vendorApiUser->getUserId()) || !($vendor->getVendorId() == $vendorApiUser->getVendorId())) {
            throw new Mage_Core_Exception($helper->__("It is not your settings"));
        }

        if (!empty($ghapiVendorPassword)) {
            //update password
            $password = $ghApiUser->updateUserPassword($ghapiVendorPassword, $vendorId);
        }

        $postData = $this->getRequest()->getPost();

	    $vendor->setData('ghapi_reservation_disabled', isset($postData['ghapi_reservation_disabled']) ? 1 : 0);

        $vendor->setData('ghapi_message_new_order', isset($postData['ghapi_message_new_order']) ? 1 : 0);
        $vendor->setData('ghapi_message_order_canceled', isset($postData['ghapi_message_order_canceled']) ? 1 : 0);

        $vendor->setData('ghapi_message_order_payment_changes', isset($postData['ghapi_message_order_payment_changes']) ? 1 : 0);
        $vendor->setData('ghapi_message_order_product_changes', isset($postData['ghapi_message_order_product_changes']) ? 1 : 0);

        $vendor->setData('ghapi_message_order_shipping_changes', isset($postData['ghapi_message_order_shipping_changes']) ? 1 : 0);
        $vendor->setData('ghapi_message_order_invoice_changes', isset($postData['ghapi_message_order_invoice_changes']) ? 1 : 0);

        $vendor->setData('ghapi_message_order_status_changes', isset($postData['ghapi_message_order_status_changes']) ? 1 : 0);

        $vendor->save();

        $this->_getSession()->addSuccess($helper->__('GH API Settings has been saved'));

        return $this->_redirectReferer();
    }

    /**
     * Return test soap client
     *
     * @param GH_Api_Block_Dropship_Answer $block
     * @return false|Mage_Core_Model_Abstract|GH_Api_Model_Soap_Client
     */
    protected function _getClient($block) {
        $client = Mage::getModel('ghapi/soap_client');
        $client->setBlock($block);
        return $client;
    }

    /**
     * Testing soap login funciton
     *
     * @param GH_Api_Block_Dropship_Answer $block
     * @return void
     */
     protected function _prepareDoLogin($block) {
         $client   = $this->_getClient($block);
         $request  = $this->getRequest();
         $vendorId = $request->get('vendorId');
         $password = $request->get('password');
         $apiKey   = $request->get('apiKey');
         $client->doLogin($vendorId,$password,$apiKey);
     }
    /**
     * testing soap getChangeOrderMessage funciton
     * @param GH_Api_Block_Dropship_Answer $block
     * @return void
     */
     protected function _prepareGetChangeOrderMessage($block) {
         $client   = $this->_getClient($block);
         $request  = $this->getRequest();
         $token = $request->get('token');
         $size = $request->get('size');
         $type  = $request->get('messageType',null);
         $orderId = $request->get('orderId',null);
         $client->getChangeOrderMessage($token,$size,$type,$orderId);
     }
     
    /**
     * preparing test for similar actions
     * @param GH_Api_Block_Dropship_Answer $block
     * @param string $name function name
     * @return void
     */
    protected function _prepareListAction($block,$name) {
         $client   = $this->_getClient($block);
         $request  = $this->getRequest();
         $token = $request->get('token');
         $list  = $request->get('list');
         if ($list) {
             $listArray = explode(',',$list);
         } else {
             $listArray = null;
         }
         $client->$name($token,$listArray);
    }

    /**
     * Preparing test for setOrderAsCollected
     *
     * @param GH_Api_Block_Dropship_Answer $block
     * @return void
     */
    public function _prepareSetOrderAsCollected($block) {
        $client   = $this->_getClient($block);
        $request  = $this->getRequest();
        $token    = $request->get('token');
        $list  = $request->get('list');
        if ($list) {
            $listArray = explode(',',$list);
        } else {
            $listArray = null;
        }

        $client->setOrderAsCollected($token,$listArray);
    }

    /**
     * Preparing test for SetOrderShipment
     *
     * @param GH_Api_Block_Dropship_Answer $block
     * @return void
     */
    public function _prepareSetOrderShipment($block) {
        $client   = $this->_getClient($block);
        $request  = $this->getRequest();
        $token    = $request->get('token');
        $orderID  = $request->get('orderID');
        $dateShipped  = $request->get('dateShipped');
        $courier  = $request->get('courier');
        $shipmentTrackingNumber = $request->get('shipmentTrackingNumber');
        $client->setOrderShipment($token, $orderID, $dateShipped, $courier, $shipmentTrackingNumber);
    }

    public function _prepareSetOrderReservation($block) {
        $client   = $this->_getClient($block);
        $request  = $this->getRequest();
        $token    = $request->get('token');
        $orderID  = $request->get('orderID');
        $reservationStatus  = $request->get('reservationStatus');
        $reservationMessage = $request->get('reservationMessage');

        $client->setOrderReservation($token, $orderID, $reservationStatus, $reservationMessage);
    }

    /**
     * Preparing test for getCategories
     *
     * @param GH_Api_Block_Dropship_Answer $block
     */
    public function _prepareGetCategories($block) {
        $client   = $this->_getClient($block);
        $request  = $this->getRequest();
        $token    = $request->get('token');

        $client->getCategories($token);
    }

	/**
	 * Prepare test for updateProductsPricesStocks
	 *
	 * @param GH_Api_Block_Dropship_Answer $block
	 */
	public function _prepareUpdateProductsPricesStocks($block) {
		/** @var GH_Api_Model_Soap_Client $client */
		$client   = $this->_getClient($block);
		/** @var Mage_Core_Controller_Request_Http $request */
		$request  = $this->getRequest();
		$token    = $request->get('token');
		$productsPricesUpdateList = $request->get('productsPricesUpdateList');
		$productsStocksUpdateList = $request->get('productsStocksUpdateList');

		$update = array(
			'productsPricesUpdateList' => $productsPricesUpdateList,
			'productsStocksUpdateList' => $productsStocksUpdateList
		);

//		$update = array(
//			'productsPricesUpdateList' => array(
//				array(
//					'sku' => '05B151-4-150',
//					'pricesTypesList' => array(
//						//priceTypeItem
//						array(
//							'priceType' => 'A',
//							'priceValue' => '10'
//						),
//						//priceTypeItem
//						array(
//							'priceType' => 'B',
//							'priceValue' => '120.99'
//						)
//					)
//				),
//				array(
//					'sku' => '05B151-4-151',
//					'pricesTypesList' => array(
//						//priceTypeItem
//						array(
//							'priceType' => 'A',
//							'priceValue' => '20'
//						),
//						//priceTypeItem
//						array(
//							'priceType' => 'B',
//							'priceValue' => '150.99'
//						)
//					)
//				)
//			),
//			'productsStocksUpdateList' => array(
//				array(
//					'sku' => '05B151-4-150',
//					'posesList' => array(
//						array(
//							'id' => 'SKLEP',
//							'qty' => '5'
//						),
//						array(
//							'id' => 'BB',
//							'qty' => '6'
//						)
//					)
//				),
//				array(
//					'sku' => '05B151-4-151',
//					'posesList' => array(
//						array(
//							'id' => 'SKLEP',
//							'qty' => '1'
//						),
//						array(
//							'id' => 'BB',
//							'qty' => '8'
//						)
//					),
//				)
//			)
//		);
		
		$client->updateProductsPricesStocks($token, $update);
	}

    /**
     * Ajax function from testing soap
     */
     public function testAction() {
		 //ini_set("soap.wsdl_cache_enabled", 0); // todo remove it after done
         $this->loadLayout();
         /** @var GH_Api_Block_Dropship_Answer $block */
         $block = $this->getLayout()->createBlock('ghapi/dropship_answer')->setTemplate('ghapi/dropship/soap/ajaxAnswer.phtml');
         $action = $this->getRequest()->getPost('action');
         switch ($action) {
             case 'doLogin':
                 $this->_prepareDoLogin($block);
                 break;
             case 'getChangeOrderMessage':
                 $this->_prepareGetChangeOrderMessage($block);
                 break;
             case 'setChangeOrderMessageConfirmation':
                 $this->_prepareListAction($block,'setChangeOrderMessageConfirmation');
                 break;
             case 'getOrdersByID':
                 $this->_prepareListAction($block,'getOrdersByID');
                 break;
             case 'setOrderAsCollected':
                 $this->_prepareSetOrderAsCollected($block);
                 break;
             case 'setOrderShipment':
                 $this->_prepareSetOrderShipment($block);
                 break;
             case 'setOrderReservation':
                 $this->_prepareSetOrderReservation($block);
                 break;
             case 'getCategories':
                 $this->_prepareGetCategories($block);
                 break;
			 case 'updateProductsPricesStocks':
				 $this->_prepareUpdateProductsPricesStocks($block);
				 break;
             default:
                 $block->setSoapRequest('error');
                 $block->setSoapResponse('error');
                 break;
         }
         echo $block->toHtml();
     }
}


