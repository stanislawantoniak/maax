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

	protected function _construct() {
		$this->_init('ghapi/session');
	}


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
				->setCreatedAt($this->getHelper()->getDate())
				->save();
		} else {
			Mage::throwException("error_session_user_not_logged_in");
		}
		return $this;
	}

	/**
	 * Generates session token
	 * @param int $vendor_id
	 * @return string
	 */
	public function generateToken($vendor_id) {
		$string = $vendor_id.microtime(true).mt_rand(0,1000);
		$data = str_shuffle($string);
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
			->addFieldToFilter('created_at',array('gt' => $this->getExpirationDate()))
			->getFirstItem();
		$this->setData($session->getData());
		return $this;
	}

	/**
	 * Gets expiration date of sessions
	 * @return string
	 */
	protected function getExpirationDate() {
		$timestamp = time() - ($this->getTokenSessionTime() * 60);
		return $this->getHelper()->getDate($timestamp);
	}

    /**
     * Gets from config token session time
     * @return mixed
     */
    public function getTokenSessionTime() {
        return Mage::getStoreConfig('ghapi_options/ghapi_general/ghapi_token_session_time');
    }

	/**
	 * Gets main GH Api helper
	 * @return GH_Api_Helper_Data
	 */
	protected function getHelper() {
		return Mage::helper('ghapi');
	}

    /**
     * Removing expired sessions
     * If userId added removing only for specific user
     *
     * @param null|int $userId
     */
    public function removeExpiredSessions($userId = null) {
        /** @var GH_Api_Model_Resource_Session_Collection $coll */
        $coll = $this->getCollection();
        if (!empty($userId)) {
            $coll->filterByUserId($userId);
        }
        $coll->addFieldToFilter('created_at',
            array(
                'lt' => $this->getExpirationDate()
            )
        );
        $data = $coll->getAllIds();

        /** @var GH_Api_Model_Resource_Session $res */
        $res = $this->getResource();
        $res->removeExpiredSessions($data);
    }
}