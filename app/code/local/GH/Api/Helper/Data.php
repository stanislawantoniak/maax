<?php
class GH_Api_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getWsdlUrl() {
        return Mage::getUrl('ghapi/wsdl');
    }

    public function getWsdlTestUrl() {
        return Mage::getUrl('ghapi/wsdl/test');
    }

    /**
     * function helps to read wsdl from self signed servers
     *
     * @param string $url wsdl file
     * @param array $params wsdl params
     * @return string
     */
    public function prepareWsdlUri($url,&$params) {
        $opts = array(
                    'ssl' => array('verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed' => true)
                );
        $params['stream_context'] = stream_context_create($opts);
        $file = file_get_contents($url,false,stream_context_create($opts));
        $dir = Mage::getBaseDir('var');
        $filename = $dir.'/'.uniqid().'.wsdl';        
        file_put_contents($filename,$file);        
        return $filename;
    }

    /**
     * Gets date based on timestamp or current one if timestamp is null
     * @param int|null $timestamp
     * @return bool|string
     */
    public function getDate($timestamp=null) {
        $time = Mage::getSingleton('core/date')->timestamp();
        $timestamp = is_null($timestamp) ? $time : $timestamp;
        return date('Y-m-d H:i:s',$timestamp);
    }

    /**
     * @param $date
     * @param string $format default Y-m-d H:i:s
     * @return bool
     */
    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

	/**
	 * @param $type
	 * @return bool
	 * @throws Mage_Core_Exception
	 */
	public function validateProductsUpdateType($type) {
		if (!in_array($type, array('price', 'stock'))) {
			Mage::throwException('error_invalid_update_products_type');
		}
		return true;
	}

	public function validateSkus($data) {

		return true;
	}

    /**
     * @return void
     * @throws Mage_Core_Exception
     */
    public function throwUserNotLoggedInException() {
        Mage::throwException('error_user_not_logged_in');
    }

    /**
     * @throws Mage_Core_Exception
     * @return void
     */
    public function throwDbError() {
        Mage::throwException('error_db_error');
    }

    /**
     * returns logged in user by session token
     * if session expired then throws error
     * @param $token
     * @return GH_Api_Model_User
     * @throws Mage_Core_Exception
     */
    public function getUserByToken($token) {
        /** @var GH_Api_Model_User $user */
        $user = Mage::getModel('ghapi/user');
        return $user->loginBySessionToken($token);
    }

}