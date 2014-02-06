<?php
class Zolago_Customer_ConfirmController extends Mage_Core_Controller_Front_Action
{

    /**
     * pobranie sesji
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     *  potwierdzenie maila
     */
    protected function _doConfirmAction($params) {
        if (empty($params['token'])) {
            Mage::throwException($this->__('Error: Empty token'));
        }        
        $model = Mage::getModel('zolagocustomer/emailtoken');
        $collection = $model->getCollection();
        echo 'c:';
        print_R($collection);
        die();
        $collection->setFilterToken($params['token']);
        print_R($collection);
        die();        
        return;
    }

    /**
     * wywołanie akcji potwierdzenia maila
     */
    public function confirmAction() {
        $error = null;
        try {
            $params = $this->getRequest()->getParams();            
            $this->_doConfirmAction($params);
        } catch (Mage_Core_Exception $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = Mage::helper('zolagocustomer')->__('Error: System error during request');
        }
        if ($error) {
            $this->_getSession()->addError($error);
        } else {
            $this->_getSession()->addSuccess(Mage::helper('zolagocustomer')->__('Info: Email changed'));            
        }
        return $this->_redirect('customer/account/login');
    }
}
