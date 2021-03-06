<?php
class Zolago_Customer_ConfirmController 
    extends Mage_Core_Controller_Front_Action
{

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     *  confirm email - action
     */
    protected function _doConfirmAction($params) {
        if (empty($params['token'])) {
            Mage::throwException($this->__('Error: Empty token'));
        }        
        
        $model = Mage::getModel('zolagocustomer/emailtoken');
        $collection = $model->getCollection();
        $collection->setFilterToken($params['token']);
        
        if (!count($collection)) {
            // no token
            Mage::throwException($this->__('Error: Wrong token'));
        }
        
        $searchModel = $collection->getFirstItem();
        $email = $searchModel->getNewEmail();
        $customerId = $searchModel->getCustomerId();
        $searchModel->delete();

        //1. Change customer email
        $modelUser = Mage::getModel('customer/customer');
        $modelUser->load($customerId);
        $storeId = $modelUser->getStoreId();
        $modelUser->setEmail($email);        
        $modelUser->save();

        /* @var $newsletterInviter Zolago_Newsletter_Model_Subscriber */
        Mage::getModel('zolagonewsletter/subscriber')
            ->subscribeCustomer($modelUser);

        //2. Change email in the table udropship_po
        /* @var $poModel Zolago_Po_Model_Po */
        $poModel = Mage::getModel('zolagopo/po');
        $poModel->replaceEmailInPOs($email, $customerId, $storeId);

        //3. Change email in the tables sales_flat_order and sales_flat_order_address
        /* @var $ordersModel Zolago_Sales_Model_Order */
        $ordersModel = Mage::getModel('sales/order');
        $ordersModel->replaceEmailInOrders($email, $customerId, $storeId);

        /* @var $orderAddressModel Zolago_Sales_Model_Order_Address */
        $orderAddressModel = Mage::getModel('sales/order_address');
        $orderAddressModel->replaceEmailInOrderAddress($email,$customerId);

        //4. Change email in the tables sales_flat_quote and sales_flat_quote_address
        /* @var $quoteModel Zolago_Sales_Model_Quote */
        $quoteModel = Mage::getModel('sales/quote');
        $quoteModel->replaceEmailInQuote($email, $customerId, $storeId);

        /* @var $quoteAddressModel Zolago_Sales_Model_Quote_Address */
        $quoteAddressModel = Mage::getModel('sales/quote_address');
        $quoteAddressModel->replaceEmailInQuoteAddress($email,$customerId);
    }

    /**
     * confirm email
     */
    public function confirmAction() {
        $error = null;
        
        try {
            $params = $this->getRequest()->getParams();            
            $this->_doConfirmAction($params);
        } catch (Mage_Core_Exception $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $error = Mage::helper('zolagocustomer')
                ->__('Error: System error during request');
        }
        
        if ($error) {
            $this->_getSession()->addError($error);
        } else {
            $this->_getSession()->addSuccess(Mage::helper('zolagocustomer')
                ->__('Info: Email changed'));            
        }
        return $this->_redirect('customer/account/login');
    }
}
