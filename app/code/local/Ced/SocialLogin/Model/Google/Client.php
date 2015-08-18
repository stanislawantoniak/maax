<?php

class Ced_SocialLogin_Model_Google_Client
{
	const REDIRECT_URI_ROUTE = 'cedsociallogin/google/connect';
	const XML_PATH_ENABLED = 'ced/ced_sociallogin_google/enabled';
	const XML_PATH_CLIENT_ID = 'ced/ced_sociallogin_google/client_id';
	const XML_PATH_CLIENT_SECRET = 'ced/ced_sociallogin_google/client_secret';
	const OAUTH2_REVOKE_URI = 'https://accounts.google.com/o/oauth2/revoke';
	const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';
	const OAUTH2_AUTH_URI = 'https://accounts.google.com/o/oauth2/auth';
	const OAUTH2_SERVICE_URI = 'https://www.googleapis.com/oauth2/v2';
	protected $isEnabled = null;
	protected $clientId = null;
	protected $clientSecret = null;
	protected $redirectUri = null;
	protected $state = '';
	protected $scope = array('https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/userinfo.email',);
	protected $access = 'offline';
	protected $prompt = 'auto';
	protected $token = null;

	public function __construct($params = array())
	{
		if (($this->isEnabled = $this->_isEnabled())) {
			$this->clientId = $this->_getClientId();
			$this->clientSecret = $this->_getClientSecret();
			$this->redirectUri = Mage::getModel('core/url')->sessionUrlVar(
				Mage::getUrl(self::REDIRECT_URI_ROUTE,array('_secure'=>Mage::app()->getStore()->isCurrentlySecure()))
			);
			if (!empty($params['scope'])) {
				$this->scope = $params['scope'];
			}
			if (!empty($params['state'])) {
				$this->state = $params['state'];
			}
			if (!empty($params['access'])) {
				$this->access = $params['access'];
			}
			if (!empty($params['prompt'])) {
				$this->prompt = $params['prompt'];
			}
		}
	}

	protected function _isEnabled()
	{
		return $this->_getStoreConfig(self::XML_PATH_ENABLED);
	}

	protected function _getStoreConfig($xmlPath)
	{
		return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());
	}

	public function isEnabled()
	{
		return (bool)$this->isEnabled;
	}

	public function getClientId()
	{
		return $this->clientId;
	}

	protected function _getClientId()
	{
		return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
	}

	public function getClientSecret()
	{
		return $this->clientSecret;
	}

	protected function _getClientSecret()
	{
		return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
	}

	public function getRedirectUri()
	{
		return $this->redirectUri;
	}

	public function getScope()
	{
		return $this->scope;
	}

	public function getState()
	{
		return $this->state;
	}

	public function setState($state)
	{
		$this->state = $state;
	}

	public function getAccess()
	{
		return $this->access;
	}

	public function setAccess($access)
	{
		$this->access = $access;
	}

	public function getPrompt()
	{
		return $this->prompt;
	}

	public function setPrompt($prompt)
	{
		$this->access = $prompt;
	}

	public function setAccessToken($token)
	{
		$this->token = json_decode($token);
	}

	public function getAccessToken()
	{
		if (empty($this->token)) {
			$this->fetchAccessToken();
		} elseif ($this->isAccessTokenExpired()) {
			$this->refreshAccessToken();
		}
		return json_encode($this->token);
	}

	protected function fetchAccessToken()
	{
		if (empty($_REQUEST['code'])) {
			throw new Exception(Mage::helper('sociallogin')->__('Unable to retrieve access code.'));
		}
		$response = $this->_httpRequest(self::OAUTH2_TOKEN_URI, 'POST', array('code' => $_REQUEST['code'], 'redirect_uri' => $this->redirectUri, 'client_id' => $this->clientId, 'client_secret' => $this->clientSecret, 'grant_type' => 'authorization_code'));
		$response->created = time();
		$this->token = $response;
	}

	protected function _httpRequest($url, $method = 'GET', $params = array())
	{
		$client = new Zend_Http_Client($url, array('timeout' => 60));
		switch ($method) {
			case 'GET':
				$client->setParameterGet($params);
				break;
			case 'POST':
				$client->setParameterPost($params);
				break;
			case 'DELETE':
				break;
			default:
				throw new Exception(Mage::helper('sociallogin')->__('Required HTTP method is not supported.'));
		}
		$response = $client->request($method);        /* Mage::log($response->getStatus() . ' - ' . $response->getBody()); */
		$decoded_response = json_decode($response->getBody());
		if ($response->isError()) {
			$status = $response->getStatus();
			if (($status == 400 || $status == 401)) {
				if (isset($decoded_response->error->message)) {
					$message = $decoded_response->error->message;
				} else {
					$message = Mage::helper('sociallogin')->__('Unspecified OAuth error occurred.');
				}
				throw new Ced_SocialLogin_GoogleOAuthException($message);
			} else {
				$message = Mage::helper('sociallogin')->__('HTTP error %s occurred while issuing request.', $status);
				throw new Exception($message);
			}
		}
		return $decoded_response;
	}

	protected function isAccessTokenExpired()
	{        /* If the token is set to expire in the next 30 seconds. */
		$expired = ($this->token->created + ($this->token->expires_in - 30)) < time();
		return $expired;
	}

	protected function refreshAccessToken()
	{
		if (empty($this->token->refresh_token)) {
			throw new Exception(Mage::helper('sociallogin')->__('No refresh token, unable to refresh access token.'));
		}
		$response = $this->_httpRequest(self::OAUTH2_TOKEN_URI, 'POST', array('client_id' => $this->clientId, 'client_secret' => $this->clientSecret, 'refresh_token' => $this->token->refresh_token, 'grant_type' => 'refresh_token'));
		$this->token->access_token = $response->access_token;
		$this->token->expires_in = $response->expires_in;
		$this->token->created = time();
	}

	public function createAuthUrl()
	{
		$url = self::OAUTH2_AUTH_URI . '?' . http_build_query(array('response_type' => 'code', 'redirect_uri' => $this->redirectUri, 'client_id' => $this->clientId, 'scope' => implode(' ', $this->scope), 'state' => $this->state, 'access_type' => $this->access, 'approval_prompt' => $this->prompt));
		return $url;
	}

	public function api($endpoint, $method = 'GET', $params = array())
	{
		if (empty($this->token)) {
			$this->fetchAccessToken();
		} else if ($this->isAccessTokenExpired()) {
			$this->refreshAccessToken();
		}
		$url = self::OAUTH2_SERVICE_URI . $endpoint;
		$method = strtoupper($method);
		$params = array_merge(array('access_token' => $this->token->access_token), $params);
		$response = $this->_httpRequest($url, $method, $params);
		return $response;
	}

	public function revokeToken()
	{
		if (empty($this->token)) {
			throw new Exception(Mage::helper('sociallogin')->__('No access token available.'));
		}
		if (empty($this->token->refresh_token)) {
			throw new Exception(Mage::helper('sociallogin')->__('No refresh token, nothing to revoke.'));
		}
		$this->_httpRequest(self::OAUTH2_REVOKE_URI, 'POST', array('token' => $this->token->refresh_token));
	}
}

class Ced_SocialLogin_GoogleOAuthException extends Exception
{
}