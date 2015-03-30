<?php
class GH_Api_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getWsdlUrl() {
        return Mage::getUrl('ghapi/wsdl');
    }

    public function getWsdlTestUrl() {
        return Mage::getUrl('ghapi/wsdl/test');
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