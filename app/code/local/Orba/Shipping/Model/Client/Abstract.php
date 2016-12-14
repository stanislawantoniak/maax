<?php
/**
 * abstract carrier client
 */
class Orba_Shipping_Model_Client_Abstract extends Mage_Core_Model_Abstract {
    protected $_auth;
    protected $_shipperAddress;
    protected $_receiverAddress;
    protected $_operator;
    protected $_settings;
    protected $_default_params = array();
    /**
     *
     * authorization data
     * @param string $user
     * @param string $password
     * @param string $account
     * @return
     */

    public function setAuth($user,$password,$account = null) {
        $auth = new StdClass();
        $auth->username = $user;
        $auth->password = $password;
        $auth->account = $account;
        $this->_auth = $auth;
    }

    /**
     * return auth params
     */

    public function getAuth($param = null) {
        if ($param) {
            return empty($this->_auth->$param)? null:$this->_auth->$param;
        }
        return $this->_auth;
    }

    public function setShipperAddress($address) {
        if(isset($address['phone'])) {
            $address['phone'] = $this->getOnlyNumbers($address['phone']);
        }
        $this->_shipperAddress = $address;
    }
    public function setReceiverAddress($address) {
        if(isset($address['phone'])) {
            $address['phone'] = $this->getOnlyNumbers($address['phone']);
        }
        $this->_receiverAddress = $address;
    }
    protected function getOnlyNumbers($value) {
        return filter_var(str_replace(array('+','-'),'',$value), FILTER_SANITIZE_NUMBER_INT);
    }
    /**
     * @param Zolago_Operator_Model_Operator $operator
     */
    public function setOperator($operator) {
        if (!empty($operator)) {
            $this->_operator = $operator;
        }
    }
    public function setShipmentSettings($params) {
        $this->_settings = $params;
    }

    public function setParam($param,$value) {
        if (!isset($this->_default_params[$param])) {
            Mage::throwException(sprintf('Wrong param name: %s',$param));
        }
        $this->_default_params[$param] = $value;
    }

    /**
     * get param from settings or default_params
     *
     * @param string $key param name
     * @return mixed value
     */
    public function getParam($key) {
        if (!isset($this->_settings[$key])) {
            if (!isset($this->_default_params[$key])) {
                return null;
            }
            return $this->_default_params[$key];
        }
        return $this->_settings[$key];
    }
    /**
         * prepare error message
         * @param Exception $xt
         */
    protected function _prepareErrorMessage($xt) {
        $result = array(
                      'error' => $xt->getMessage(),
                  );
        return $result;
    }

    
    /**
     * prepare helper
     */
     protected function _getHelper() {
         return Mage::helper('orbashipping/carrier');
     }
    

    /**
     * save label on disk
     */
    protected function _saveFile($fileName,$fileContent) {
        $ioAdapter			= new Varien_Io_File();
        $fileLocation		= $this->_getHelper()->getFileDir() . $fileName;
        return @$ioAdapter->filePutContent($fileLocation, $fileContent);
    }
    /**
     * return and save file from external service
     */

    public function getLabelFile($trackModel) {
        $file = array (
                    'status' => false,
                    'file' => false,
                    'message' => false,
                );
        $result = $this->getLabels($trackModel);
        $result	= $this->processLabelsResult('getLabels', $result);
        if ($result['status']) {
            $file['file'] = $this->_saveFile($result['labelName'],$result['labelData']);
            $file['status'] = true;
        } else {
            //Error Scenario
            $file['message']	= $result['message'] .PHP_EOL. $this->_getHelper()->__('Please contact Shop Administrator');
        }
        return $file;
    }

}