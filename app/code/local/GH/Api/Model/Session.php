<?php

/**
 * Class GH_Api_Model_Session
 * @method int getSessionId()
 * @method GH_Api_Model_Session setSessionId(int $sessionId)
 * @method int getUserId()
 * @method GH_Api_Model_Session setUserId(int $userId)
 * @method string getToken()
 * @method GH_Api_Model_Session setToken(string $token)
 * @method string getCreatedAt()
 * @method GH_Api_Model_Session setCreatedAt(string $date)
 */
class GH_Api_Model_Session extends Mage_Core_Model_Abstract {
	const GH_API_SESSION_TOKEN_LENGTH = 64;
	const GH_API_SESSION_TOKEN_ALGO = 'sha256';
	const GH_API_SESSION_TIME = 60; //session time in minutes

	/**
	 * @param GH_Api_Model_User $user
	 * @return GH_Api_Model_Session
	 * @throws Mage_Core_Exception
	 */
	public function createSession(GH_Api_Model_User $user) {
		if($user->isLoggedIn()) {
			$this
				->setUserId($user->getId())
				->setToken($this->generateToken($user->getVendorId()))
				->setCreatedAt($this->getDateModel()->gmtDate())
				->save();
		} else {
			Mage::throwException("Cannot create session if user is not logged in");
		}
		return $this;
	}

	/**
	 * Generates session token
	 * @param int $vendor_id
	 * @return string
	 */
	protected function generateToken($vendor_id) {
		$string = $vendor_id.microtime(true).mt_rand(0,1000);
		$data = shuffle($string);
		return hash(self::GH_API_SESSION_TOKEN_ALGO,$data);
	}

	/**
	 * Checks if token is string and has correct length
	 * @param string $token
	 * @return bool
	 */
	public function validateToken($token) {
		if(is_string($token) && strlen($token) == self::GH_API_SESSION_TOKEN_LENGTH) {
			return true;
		}
		return false;
	}

	/**
	 * Loads session by token, only if token is not expired
	 * @param $token
	 * @return GH_Api_Model_Session
	 */
	public function loadByToken($token) {
		/** @var GH_Api_Model_Session $session */
		$session =  $this->getCollection()
			->addFieldToFilter('token',$token)
			->addFieldToFilter('created_at',$this->getExpirationDate())
			->getFirstItem();
		$this->setData($session->getData());
		return $this;
	}

	/**
	 * Gets expiration date of sessions
	 * @return string
	 */
	protected function getExpirationDate() {
		$timestamp = time() - (self::GH_API_SESSION_TIME * 60);
		return $this->getDateModel()->gmtDate(null,$timestamp);
	}

	/**
	 * Gets Mage date model
	 * @return Mage_Core_Model_Date
	 */
	protected function getDateModel() {
		return Mage::getSingleton('core/date');
	}
}