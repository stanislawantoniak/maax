<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Ced
 * @package     Ced_SocialLogin
 * @author      CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * SocialLogin google Controller
 *
 * @category    Ced
 * @package     Ced_SocialLogin
 * @author      CedCommerce Magento Core Team <Ced_MagentoCoreTeam@cedcommerce.com>
 */

class Ced_SocialLogin_GoogleController extends Mage_Core_Controller_Front_Action
{
	protected $referer = null;

	const GOOGLE_ERROR_ACCESS_DENIED = 'access_denied';

	public function connectAction()
	{

		try {
			$this->_connectCallback();
		} catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}

		echo '<div id="redirect" style="color:#FFF">';
		if($this->getRequest()->getParam('error') == self::GOOGLE_ERROR_ACCESS_DENIED) {
			echo '';
		} elseif(!empty($this->referer)) {
			echo $this->referer;
		} else {
			echo Mage::getUrl('/');
		}
		echo '</div>';
		echo '<script>setTimeout(function(){ window.close(); }, 1100);</script>';
		return;
	}

	public function disconnectAction()
	{
		$customer = Mage::getSingleton('customer/session')->getCustomer();

		try {
			$this->_disconnectCallback($customer);
		} catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}

		if(!empty($this->referer)) {
			$this->_redirectUrl($this->referer);
		} else {
			Mage::helper('sociallogin')->redirect404($this);
		}
	}

	protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
		$this->referer = Mage::getUrl('cedsociallogin/account/google');

		Mage::helper('sociallogin/google')->disconnect($customer);

		Mage::getSingleton('core/session')
			->addSuccess(
				$this->__('You have successfully disconnected your Google account from our store account.')
			);
	}

	protected function _connectCallback() {
		$errorCode = $this->getRequest()->getParam('error');
		$code = $this->getRequest()->getParam('code');
		$state = $this->getRequest()->getParam('state');
		if(!($errorCode || $code) && !$state) {
			// Direct route access - deny
			return;
		}

		$this->referer = Mage::getSingleton('core/session')->getGoogleRedirect();

		if(!$state || $state != Mage::getSingleton('core/session')->getGoogleCsrf()) {
			return;
		}

		Mage::getSingleton('core/session')->getGoogleCsrf('');

		if($errorCode) {
			// Google API read light - abort
			if($errorCode === self::GOOGLE_ERROR_ACCESS_DENIED) {
				return; //user cancelled login
			}

			Mage::log('Error occured during login: '.$errorCode,null,'google_login_errors.log');
			Mage::getSingleton('core/session')
				->addError(
					$this->__('Google login error occured, please try again.')
				);

			return;
		}

		if ($code) {
			// Google API green light - proceed

			$client = Mage::getSingleton('sociallogin/google_client');

			$userInfo = $client->api('/userinfo');

			$token = $client->getAccessToken();

			$customersByGoogleId = Mage::helper('sociallogin/google')
				->getCustomersByGoogleId($userInfo->id);


			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
				// Logged in user
				if($customersByGoogleId->count()) {
					// Google account already connected to other account - deny
					Mage::getSingleton('core/session')
						->addError(
							$this->__('Your Google account is already connected to one of our store accounts.')
						);

					return;
				}

				// Connect from account dashboard - attach
				$customer = Mage::getSingleton('customer/session')->getCustomer();

				Mage::helper('sociallogin/google')->connectByGoogleId(
					$customer,
					$userInfo->id,
					$token
				);

				Mage::getSingleton('core/session')->addSuccess(
					$this->__('Your Google account is now connected to your user account at our store. You can login next time by Google Facebook login button or Store user account.')
				);

				return;
			}

			if($customersByGoogleId->count()) {
				// Existing connected user - login
				$customer = $customersByGoogleId->getFirstItem();

				Mage::helper('sociallogin/google')->loginByCustomer($customer);

				Mage::getSingleton('core/session')
					->addSuccess(
						$this->__('You have successfully logged in using your Google account.')
					);

				return;
			}

			$customersByEmail = Mage::helper('sociallogin/google')
				->getCustomersByEmail($userInfo->email);

			if($customersByEmail->count())  {
				// Email account already exists - attach, login
				$customer = $customersByEmail->getFirstItem();

				Mage::helper('sociallogin/google')->connectByGoogleId(
					$customer,
					$userInfo->id,
					$token
				);

				Mage::getSingleton('core/session')->addSuccess(
					$this->__('We find you already have an account at our store. Your Google account is now connected to your store account.')
				);

				return;
			}

			// New connection - create, attach, login
			if(empty($userInfo->given_name)) {
				throw new Exception(
					$this->__('Sorry, could not retrieve your Google first name. Please try again.')
				);
			}

			if(empty($userInfo->family_name)) {
				throw new Exception(
					$this->__('Sorry, could not retrieve your Google last name. Please try again.')
				);
			}
			Mage::helper('sociallogin/google')->connectByCreatingAccount(
				$userInfo->email,
				$userInfo->given_name,
				$userInfo->family_name,
				$userInfo->id,
				$token
			);

			Mage::getSingleton('core/session')->addSuccess(
				$this->__('Your Google account is now connected to your user account at our store. You can login next time by Google Facebook login button or Store user account.')
			);
		}
	}

}