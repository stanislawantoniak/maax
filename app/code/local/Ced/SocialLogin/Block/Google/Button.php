<?php class Ced_SocialLogin_Block_Google_Button extends Mage_Core_Block_Template
{
	protected $client = null;
	protected $oauth2 = null;
	protected $userInfo = null;

	protected function _construct()
	{
		parent::_construct();
		$this->client = Mage::getSingleton('sociallogin/google_client');
		if (!($this->client->isEnabled())) {
			return;
		}
		$this->userInfo = Mage::registry('ced_sociallogin_google_userdetails');        /* CSRF protection */

		if(empty($this->userInfo)) {
			$this->userInfo = Mage::getSingleton('sociallogin/google_userdetails')->getUserDetails();
		}

		if (!Mage::getSingleton('core/session')->getGoogleCsrf() || Mage::getSingleton('core/session')->getGoogleCsrf() == '') {
			$csrf = md5(uniqid(rand(), TRUE));
			Mage::getSingleton('core/session')->setGoogleCsrf($csrf);
		} else {
			$csrf = Mage::getSingleton('core/session')->getGoogleCsrf();
		}
		$this->client->setState($csrf);
		if (!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {
			$redirect = Mage::helper('core/url')->getCurrentUrl();
		}                        /* Redirect uri */
		Mage::getSingleton('core/session')->setGoogleRedirect($redirect);
		$this->setTemplate('sociallogin/google/button.phtml');
	}

	protected function _getButtonUrl()
	{
		if (empty($this->userInfo)) {
			return $this->client->createAuthUrl();
		} else {
			return $this->getUrl('cedsociallogin/google/disconnect');
		}
	}

	protected function _getButtonText()
	{
		if (empty($this->userInfo)) {
			if (!($text = Mage::registry('ced_sociallogin_button_text'))) {
				$text = $this->__('Connect');
			}
		} else {
			$text = $this->__('Disconnect');
		}
		return $text;
	}

	protected function _getButtonClass()
	{
		if (empty($this->userInfo)) {
			$text = "ced_google_connect";
		} else {
			$text = "ced_google_disconnect";
		}
		return $text;
	}

	public function isLogged() {
		return (bool)!empty($this->userInfo);
	}

	/**
	 * @return Ced_SocialLogin_Model_Google_Client
	 */
	public function getClient() {
		return $this->client;
	}
}