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
        
        $modelUser = Mage::getModel('customer/customer');
        $modelUser->load($customerId);
        $modelUser->setEmail($email);        
        $modelUser->save();

        Mage::dispatchEvent("zolagocustomer_change_email_confirm", array('customer'=> $modelUser));
        
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
