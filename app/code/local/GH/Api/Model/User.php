<?php

/**
 * Class GH_Api_Model_User
 * @method GH_Api_Model_User setVendorId(int $vendor_id)
 * @method int getVendorId()
 * @method GH_Api_Model_User setPassword(string $password)
 * @method string getPassword()
 * @method GH_Api_Model_User setApiKey(string $api_key)
 * @method string getApiKey()
 */
class GH_Api_Model_User extends Mage_Core_Model_Abstract {

	const GH_API_USER_PASSWORD_LENGTH = 8;
	const GH_API_USER_PASSWORD_HASH_LENGTH = 64;
	const GH_API_USER_PASSWORD_HASH_ALGO = 'sha256';

	const GH_API_USER_API_KEY_LENGTH = 64;
	const GH_API_USER_API_KEY_ALGO = 'sha256';

	protected $isLoggedIn = false;
	protected $session;

	protected function _construct() {
		$this->_init('ghapi/user');
	}

	/**
	 * Creates new api user for vendor
	 * @param int $vendorId
	 * @param string $password
	 * @return GH_Api_Model_User
	 * @throws Mage_Core_Exception
	 */
	public function createUser($vendorId,$password) {
		if($this->apiUserExists($vendorId)) {
			Mage::throwException('User already exists');
		} elseif(!$this->validatePassword($password)) {
			Mage::throwException('Password is too short');
		}

		$this->setVendorId($vendorId)
			->setPassword($this->hashPassword($password,$vendorId))
			->setApiKey($this->generateApiKey($vendorId))
			->save();

		return $this;
	}

    public function updateUserPassword($password,$vendorId){
        $this
            ->setPassword($this->hashPassword($password,$vendorId))
            ->save();
        return $this;
    }

	/**
	 * logins user by user data, creates session and returns session object
	 * @param int $vendorId
	 * @param string $password
	 * @param string $apiKey
	 * @return GH_Api_Model_User
	 * @throws Mage_Core_Exception
	 */
	public function loginUser($vendorId,$password,$apiKey) {
		if(!$this->getSession()->getId() &&
			$this->validatePassword($password) &&
			$this->validateApiKey($apiKey) &&
			$this->apiUserExists($vendorId))
		{
			$password = $this->hashPassword($password,$vendorId);
			$this->loadByVendorId($vendorId);
			if($this->getPassword() != $password) {
				$this->throwPasswordError();
			} elseif($this->getApiKey() != $apiKey) {
				$this->throwApiKeyError();
			} else {
                $this->setIsLoggedIn();
                $session = $this->getSessionModel()->createSession($this);
				if(!$session->getId()) {
					Mage::throwException('error_session_creation');
				} else {
					$this->setSession($session);
                    $session->removeExpiredSessions($session->getUserId());
				}
			}
		}
		return $this;
	}

	/**
	 * loads user by session token and returns session object
	 * @param $sessionToken
	 * @return GH_Api_Model_User
	 * @throws Mage_Core_Exception
	 */
	public function loginBySessionToken($sessionToken) {
		$session = $this->getSessionModel()->loadByToken($sessionToken);
		if($session->getId()) {
			$this->load($session->getUserId());
			$this->setIsLoggedIn();
			$this->setSession($session);
		} else {
			Mage::throwException('error_session_token_invalid');
		}
		return $this;
	}

	/**
	 * Checks if user has logged in
	 * @return bool
	 */
	public function isLoggedIn() {
		return $this->isLoggedIn;
	}

	/**
	 * sets logged in variable
	 * @param $bool
	 */
	public function setIsLoggedIn($bool=true) {
		$this->isLoggedIn = $bool;
	}

	/**
	 * Checks if vendor with provided id exists
	 * @param int $vendorId
	 * @return bool
	 * @throws Mage_Core_Exception
	 */
	protected function validateVendorId($vendorId) {
		if(is_numeric($vendorId)) {
			$vendor = Mage::getModel('udropship/vendor')->load($vendorId);
			if($vendor->getId()) {
				return true;
			}
		}
        $this->throwVendorError();
		return false;
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwVendorError() {
		Mage::throwException('error_vendor_inactive');
	}


	/**
	 * Checks if api user for this vendor already exists
	 * @param int $vendorId
	 * @return bool
	 */
	protected function apiUserExists($vendorId) {
		if($this->validateVendorId($vendorId)) {
			/** @var GH_Api_Model_User $user */
			$user = Mage::getModel('ghapi/user');
			$user->loadByVendorId($vendorId);
			if($user->getId()) {
				return $user;
			}
		}
		return false;
	}

	/**
	 * Checks if provided password is string and has sufficient length
	 * @param string $password
	 * @return bool
	 */
	protected function validatePassword($password) {
		if(is_string($password) && strlen($password) >= self::GH_API_USER_PASSWORD_LENGTH) {
			return true;
		}
		$this->throwPasswordError();
		return false;
	}

	/**
	 * Hashes password. Vendor Id is required for hashes to be unique
	 * @param string $password
	 * @param int $vendorId
	 * @return string
	 */
	protected function hashPassword($password,$vendorId) {
		return hash(self::GH_API_USER_PASSWORD_HASH_ALGO,$password.$vendorId);
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwPasswordError() {
		Mage::throwException('error_password_invalid');
	}

	/**
	 * Generates api_key for gh_api_user
	 * @param int $vendor_id
	 * @return string
	 */
	public function generateApiKey($vendor_id) {
		$string = mt_rand(1000,2000).microtime().$vendor_id;
		$data = str_shuffle($string);
		return hash(self::GH_API_USER_API_KEY_ALGO,$data);
	}

	/**
	 * Checks if provided api key is string and has correct length
	 * @param string $apiKey
	 * @return bool
	 */
	protected function validateApiKey($apiKey) {
		if(is_string($apiKey) && strlen($apiKey) == self::GH_API_USER_API_KEY_LENGTH) {
			return true;
		}
		$this->throwApiKeyError();
		return false;
	}

	/**
	 * @throws Mage_Core_Exception
	 * @return void
	 */
	protected function throwApiKeyError() {
		Mage::throwException('error_webapikey_invalid');
	}


	/**
	 * Gets main GH Api helper
	 * @return GH_Api_Helper_Data
	 */
	protected function getHelper() {
		return Mage::helper('ghapi');
	}

	/**
	 * Gets session variable
	 * @return GH_Api_Model_Session
	 */
	public function getSession() {
		if(is_null($this->session)) {
			$this->setSession($this->getSessionModel());
		}
		return $this->session;
	}

	/**
	 * @param GH_Api_Model_Session $session
	 * @return void
	 */
	protected function setSession(GH_Api_Model_Session $session) {
		$this->session = $session;
	}

	/**
	 * Gets GH Api Session model
	 * @return GH_Api_Model_Session
	 */
	protected function getSessionModel() {
		return Mage::getModel('ghapi/session');
	}

	/**
	 * Loads user by vendor id
	 * @param $vendorId
	 * @return GH_Api_Model_User
	 */
	public function loadByVendorId($vendorId) {
		/** @var GH_Api_Model_User $user */
		$user =  $this->getCollection()
			->addFieldToFilter('vendor_id',$vendorId)
			->getFirstItem();
		$this->setData($user->getData());
		return $this;
	}

	/**
	 * @param int $vendorId
	 * @param string $password
	 * @return bool
	 */
	public function changePassword($vendorId,$password) {
		try {
			$this
				->loadByVendorId($vendorId)
				->setPassword($this->hashPassword($password, $vendorId))
				->save();
		} catch(Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * @param int $vendorId
	 * @return bool
	 */
	public function generateNewApiKey($vendorId) {
		try {
			$this
				->loadByVendorId($vendorId)
				->setPassword($this->generateApiKey($vendorId))
				->save();
		} catch(Exception $e) {
			return false;
		}
		return true;
	}

}