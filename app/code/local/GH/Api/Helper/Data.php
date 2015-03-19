<?php
class GH_Api_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getWsdlUrl() {
        return Mage::getUrl('ghapi/wsdl');
    }

	/**
	 * Gets date based on timestamp or current one if timestamp is null
	 * @param int|null $timestamp
	 * @return bool|string
	 */
	public function getDate($timestamp=null) {
        $timestamp = is_null($timestamp) ? time() : $timestamp;
        return date('Y-m-d H:i:s',$timestamp);
	}

	/**
	 * @return void
	 * @throws Mage_Core_Exception
	 */
	public function throwUserNotLoggedInException() {
		Mage::throwException('User is not logged in');
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