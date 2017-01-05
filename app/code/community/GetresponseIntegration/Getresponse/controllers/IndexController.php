<?php

/**
 * Class GetresponseIntegration_Getresponse_IndexController
 */
class GetresponseIntegration_Getresponse_IndexController extends Mage_Adminhtml_Controller_Action
{
	public $grapi;
	public $current_shop_id;
	public $settings;
	public $active_tab;
	public $disconnected = false;

	public $layout_positions = array(
			'top.menu' => 'Navigation Bar',
			'after_body_start' => 'Page Top',
			'left' => 'Left Column',
			'right' => 'Right Column',
			'content' => 'Content',
			'before_body_end' => 'Page Bottom',
			'footer' => 'Footer'
	);

	public $block_positions = array(
			'after' => 'Bottom',
			'before' => 'Top',
	);

	public $actions = array(
			'move' => 'Moved',
			'copy' => 'Copied'
	);

	public $automation_statuses = array(
			'1' => 'Enabled',
			'0' => 'Disabled'
	);

	/**
	 * construct
	 */
	protected function _construct()
	{
		$this->current_shop_id = Mage::helper('getresponse')->getStoreId();
		$this->settings = new stdClass();
	}

	/**
	 * Getresponse API instance
	 */
	public static function grapi()
	{
		return GetresponseIntegration_Getresponse_Helper_Api::instance();
	}

	/**
	 * Main init action, et layout and template
	 *
	 * @return $this
	 */
	protected function _initAction()
	{
		$this->settingsHandler();

		$this->loadLayout()->_setActiveMenu('getresponse_menu/settings_page');

		$this->_addLeft($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_menu')
				->setTemplate('getresponse/menu.phtml')
				->assign('active_tab', $this->active_tab)
				->assign('settings', $this->settings)
		);

		if ($this->active_tab != 'index' && empty($this->settings->api['api_key'])) {
			Mage::getSingleton('core/session')
					->addError('Access denied - module is not connected to GetResponse Account.');
			$this->_redirect('getresponse/index/index');
		}

		return $this;
	}

	/**
	 * Main extenction settings
	 */
	private function settingsHandler()
	{
		$this->settings->main['api_url_360_com'] = 'https://api3.getresponse360.com/v3';
		$this->settings->main['api_url_360_pl'] = 'https://api3.getresponse360.pl/v3';

		$this->settings->api = Mage::getModel('getresponse/settings')->load($this->current_shop_id)->getData();
		Mage::helper('getresponse/api')->setApiDetails(
				$this->settings->api['api_key'],
				$this->settings->api['api_url'],
				$this->settings->api['api_domain']
		);
		$this->settings->account = Mage::getModel('getresponse/account')->load($this->current_shop_id)->getData();
		$this->settings->customs = Mage::getModel('getresponse/customs')->getCustoms($this->current_shop_id);
		$this->settings->webforms_settings =
				Mage::getModel('getresponse/webforms')->load($this->current_shop_id)->getData();
		$this->settings->campaigns = Mage::helper('getresponse/api')->getGrCampaigns();
	}

    /**
     * GET getresponse/index/webtraffic
     */
    public function webtrafficAction()
    {
        $this->settings->api = Mage::getModel('getresponse/settings')->load($this->current_shop_id)->getData();

        $this->_title($this->__('Web Traffic Tracking'))
            ->_title($this->__('GetResponse'));

        $this->active_tab = 'webtraffic';

        $this->_initAction();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/webtraffic.phtml')
            ->assign('settings', $this->settings)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/index/activate_webtraffic
     */
    public function activate_webtrafficAction()
    {
        $this->_initAction();
        $params = $this->getRequest()->getParams();

        Mage::getModel('getresponse/settings')->updateSettings(
            array(
               'has_active_traffic_module' => (isset($params['has_active_traffic_module']) && $params['has_active_traffic_module'] == 1) ? 1 : 0
            ),
            $this->current_shop_id
        );

        Mage::getSingleton('core/session')->addSuccess('Settings have been updated.');
        $this->_redirect('getresponse/index/webtraffic');
    }

	/**
	 * GET getresponse/index/index
	 */
	public function indexAction()
	{
		$this->_title($this->__('API Key settings'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'index';

		$this->_initAction();
		$site = ( !empty($this->settings->api['api_key']) && $this->disconnected === false) ? 'account' : 'apikey';

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/' . $site . '.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * POST getresponse/index/apikey
	 */
	public function apikeyAction()
	{
		$api_key = $this->getRequest()->getParam('api_key');

		if ( !$api_key) {
			Mage::getSingleton('core/session')->addError('The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one.');
			$this->_forward('index');

			return;
		}

		$getresponse_360_account = $this->getRequest()->getParam('getresponse_360_account');
		$api_url = ($getresponse_360_account) ? $this->getRequest()->getParam('api_url') : null;
		$api_domain = ($getresponse_360_account) ? $this->getRequest()->getParam('api_domain') : null;

		$this->grapi()->api_key = $api_key;
		$status = $this->grapi()->check_api($api_url, $api_domain);

		$status_array = (array)$status;
		if (empty($status_array) && !empty($api_domain)) {
			Mage::getSingleton('core/session')->addError('Invalid domain.');
			$this->_forward('index');

			return;
		}
		elseif ( !empty($status->codeDescription)) {
			Mage::getSingleton('core/session')->addError('The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one.');
			$this->_forward('index');

			return;
		}
		elseif ( !empty($status->accountId)) {
			if (false === Mage::getModel('getresponse/account')->updateAccount($status, $this->current_shop_id)) {
				Mage::getSingleton('core/session')->addError('Error during account details save.');
			}
		}
		else {
			Mage::getSingleton('core/session')->addError('Error - please try again.');
			$this->_forward('index');

			return;
		}

		Mage::register('api_key', $api_key);
        Mage::getModel('getresponse/customs')->connectCustoms($this->current_shop_id);
		Mage::getSingleton('core/session')->addSuccess('You connected your Magento to GetResponse.');

        $featureTracking = 0;
        $features = $this->grapi()->get_features();

        if ($features instanceof stdClass && $features->feature_tracking == 1) {
            $featureTracking = 1;
        }

		$data = array(
		    'id_shop' => $this->current_shop_id,
            'api_key' => $api_key,
            'api_url' => $api_url,
            'api_domain' => $api_domain,
            'has_gr_traffic_feature_enabled' => $featureTracking
		);

        // getting tracking code
        $trackingCode = (array) $this->grapi()->get_tracking_code();

        if (!empty($trackingCode) && is_object($trackingCode[0]) && 0 < strlen($trackingCode[0]->snippet)) {
            $data['tracking_code_snippet'] = $trackingCode[0]->snippet;
        }

		if (false === Mage::getModel('getresponse/settings')->updateSettings($data, $this->current_shop_id)) {
			Mage::getSingleton('core/session')->addError('Error during settings details save.');
		}

		$this->_redirect('getresponse/index/index');
	}

	/**
	 * GET getresponse/index/export
	 */
	public function exportAction()
	{
		$postData = Mage::getSingleton('core/session')->getExportPost();
		$postData = is_array($postData) ? $postData : [];
		Mage::getSingleton('core/session')->unsExportPost();

		$inputValues['gr_sync_order_data'] = false;

		if (false === empty($postData)) {
			if (isset($postData['gr_sync_order_data'])) {
				$inputValues = $postData['gr_sync_order_data'];
			}
		}

		$this->settings->inputValues = $inputValues;

		$this->_title($this->__('Export customers'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'export';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();
		$this->setNewCampaignSettings();

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/export.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * POST getresponse/index/exported
	 */
	public function exportedAction()
	{
		$this->_initAction();

		Mage::getSingleton('core/session')->setExportPost($_POST);

		$campaign_id = $this->getRequest()->getParam('campaign_id');
		if (empty($campaign_id)) {
			Mage::getSingleton('core/session')->addError('Campaign Id can\'t be empty.');
			$this->_redirect('getresponse/index/export');

			return;
		}

		$params = $this->getRequest()->getParams();

		$this->exportCustomers($campaign_id, $params);

		$this->_redirect('getresponse/index/export');
	}

	/**
	 * GET getresponse/index/viapage
	 */
	public function viapageAction()
	{
		$this->_title($this->__('Subscription via registration page'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'viapage';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();
		$this->setNewCampaignSettings();

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/viapage.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * POST getresponse/index/subscribtion
	 */
	public function subpageAction()
	{
		$this->_initAction();

		$params = $this->getRequest()->getParams();

		if ((isset($params['gr_sync_order_data']) && $params['gr_sync_order_data'] == 1) &&
				empty($params['campaign_id'])
		) {
			Mage::getSingleton('core/session')->addError('Campaign Id can\'t be empty.');
			$this->_redirect('getresponse/index/viapage');

			return;
		}

		if ( !empty($params['gr_custom_field'])) {
			foreach ($params['gr_custom_field'] as $field_key => $field_value) {
				if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', stripslashes(($field_value)))) {
					Mage::getSingleton('core/session')->addError('Incorrect field name: ' . $field_value . '.');
					$this->_redirect('getresponse/index/viapage');

					return;
				}
			}
		}

		$cycleDay = 0;
		if (isset($params['gr_autoresponder']) && 1 == $params['gr_autoresponder']) {
			$cycleDay = $params['cycle_day'];
		}

        Mage::getModel('getresponse/settings')->updateSettings(
            array(
                'campaign_id' => $params['campaign_id'],
                'active_subscription' => (isset($params['active_subscription']) && $params['active_subscription'] == 1) ? 1 : 0,
                'update_address' => (isset($params['gr_sync_order_data']) && $params['gr_sync_order_data'] == 1) ? 1 : 0,
                'cycle_day' => $cycleDay,
                'subscription_on_checkout' => (isset($params['subscription_on_checkout']) && $params['subscription_on_checkout'] == 1) ? 1 : 0
            ),
            $this->current_shop_id
		);

		if ( !empty($params['gr_sync_order_data']) && isset($params['gr_custom_field'])) {
			foreach ($this->settings->customs as $cf) {
				if (in_array($cf['custom_field'], array_keys($params['gr_custom_field']))) {
					Mage::getModel('getresponse/customs')->updateCustom($cf['id_custom'],
							array('custom_value' => $params['gr_custom_field'][$cf['custom_field']],
									'active_custom' => GetresponseIntegration_Getresponse_Model_Customs::ACTIVE
							));
				}
				else {
					Mage::getModel('getresponse/customs')->updateCustom($cf['id_custom'],
							array('active_custom' => GetresponseIntegration_Getresponse_Model_Customs::INACTIVE));
				}
			}
		}

		Mage::getSingleton('core/session')->addSuccess('Subscription settings successfully saved.');

		$this->_redirect('getresponse/index/viapage');
	}

	/**
	 * GET getresponse/index/viawebform
	 */
	public function viawebformAction()
	{
		$this->_title($this->__('Subscription via a form'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'viawebform';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->forms = array();
		$forms = Mage::helper('getresponse/api')->getForms();
		if (!empty($forms)) {
			foreach ($forms as $form) {
				if (isset($form->status) && $form->status == 'published') {
					$this->settings->forms[] = $form;
				}
			}
		}

		$this->settings->webforms = array();
		$webforms = Mage::helper('getresponse/api')->getWebForms();
		if (!empty($webforms)) {
			foreach ($webforms as $webform) {
				if (isset($webform->status) && $webform->status == 'enabled') {
					$this->settings->webforms[] = $webform;
				}
			}
		}

		$this->settings->layout_positions = $this->layout_positions;
		$this->settings->block_positions = $this->block_positions;

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/viawebform.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * POST getresponse/index/subform
	 */
	public function subformAction()
	{
		$this->_initAction();
		$params = $this->getRequest()->getParams();

		if (empty($params['webform_id'])) {
			Mage::getSingleton('core/session')->addError('Webform Id can\'t be empty.');
			$this->_redirect('getresponse/index/viawebform');

			return;
		}

		if (isset($params['webform_title'])) {
			if ($params['webform_title'] == '') {
				Mage::getSingleton('core/session')->addError('Block Title can\'t be empty.');
				$this->_redirect('getresponse/index/viawebform');

				return;
			}
			elseif (strlen($params['webform_title']) > 255) {
				Mage::getSingleton('core/session')->addError('Title is too long. Max: 255 characters.');
				$this->_redirect('getresponse/index/viawebform');

				return;
			}
		}

		if ( !empty($params['gr_webform_type']) && $params['gr_webform_type'] == 'old') {
			$webforms = self::grapi()->get_web_form($params['webform_id']);
		}
		else {
			$webforms = self::grapi()->get_form($params['webform_id']);
		}

		if (empty($webforms->codeDescription)) {
			$active = (isset($params['active_subscription']) && $params['active_subscription'] == 1) ? 1 : 0;
			Mage::getModel('getresponse/webforms')->updateWebforms(
					array('webform_id' => $params['webform_id'],
                          'id_shop' => $this->current_shop_id,
							'active_subscription' => $active,
							'layout_position' => $params['layout_position'],
							'block_position' => $params['block_position'],
							'webform_title' => trim($params['webform_title']),
							'url' => $webforms->scriptUrl
					),
					$this->current_shop_id
			);

			Mage::getSingleton('core/session')->addSuccess('Subscription settings successfully saved.');
		}
		else {
			Mage::getSingleton('core/session')->addError('Error - please try again.');
		}

		$this->_redirect('getresponse/index/viawebform');
	}

	/**
	 * GET getresponse/index/automation
	 */
	public function automationAction()
	{
		$this->_title($this->__('Campaign rules'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'automation';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->actions = $this->actions;
		$this->settings->automation_statuses = $this->automation_statuses;
		$this->settings->categories = $this->getCategories();
		$this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();
		$this->settings->automations =
				Mage::getModel('getresponse/automations')->getAutomations($this->current_shop_id);

		//$store = Mage::getModel('core/store')->load($this->current_shop_id)->getRootCategoryId();
		$this->settings->categories_tree = $this->getTreeCategoriesHTML(1, false);

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/automation.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * AJAX POST getresponse/index/campaign
	 */
	public function campaignAction()
	{
		$this->settingsHandler();

		$params = $this->getRequest()->getParams();

		if (empty($params['campaign_name']) ||
				empty($params['from']) ||
				empty($params['reply_to']) ||
				empty($params['confirmation_subject']) ||
				empty($params['confirmation_body']) ||
				preg_match('/^[\w_]+$/', $params['campaign_name']) == false
		) {
			$data = array(
					'type' => 'error',
					'msg' => 'Error - empty or invalid field.'
			);
			echo Mage::helper('core')->jsonEncode($data);
			die;
		}

		$campaign_name = strtolower($params['campaign_name']);

		$add = Mage::helper('getresponse/api')->addCampaignToGR(
				$campaign_name,
				$params['from'],
				$params['reply_to'],
				$params['confirmation_subject'],
				$params['confirmation_body']
		);

		if (is_object($add) && isset($add->campaignId)) {
			$data = array(
					'type' => 'success',
					'msg' => 'Campaign "' . $campaign_name . '" successfully created.',
					'c' => $params['campaign_name'],
					'cid' => $add->campaignId
			);
		} elseif (is_object($add) && $add->code == 1008) {
			$data = array(
					'type' => 'error',
					'msg' => 'The campaign name you entered already exists. Please enter a different name.'
			);
		} else {
			$data = array(
					'type' => 'error',
					'msg' => 'Campaign "' . $campaign_name . '" has not been added' . ' - ' . $add->message . '.'
			);
		}

		echo Mage::helper('core')->jsonEncode($data);
		die;
	}

	/**
	 * AJAX POST getresponse/index/createautomation
	 */
	public function createAutomationAction()
	{
		$this->settingsHandler();

		$params = $this->getRequest()->getParams();

		if (empty($params['category_id']) ||
				empty($params['action']) ||
				empty($params['campaign_id'])
		) {
			$data = array(
					'type' => 'error',
					'msg' => 'Error - empty or invalid field.'
			);
			echo Mage::helper('core')->jsonEncode($data);
			die;
		}

		$cycle_day = (is_numeric($params['cycle_day']) && $params['cycle_day'] >= 0) ? $params['cycle_day'] : '';
		$add = Mage::getModel('getresponse/automations')->createAutomation(array(
				'id_shop' => $this->current_shop_id,
				'category_id' => $params['category_id'],
				'campaign_id' => $params['campaign_id'],
				'cycle_day' => $cycle_day,
				'action' => $params['action']
		));

		if ($add) {
			$categories = $this->getCategories();
			$data = array(
					'type' => 'success',
					'msg' => 'Rule successfully created.',
					'automation_id' => $add,
					'category_id' => $params['category_id'],
					'campaign_id' => $params['campaign_id'],
					'category_name' => $categories[$params['category_id']]['name'],
					'campaign_name' => $this->settings->campaigns[$params['campaign_id']],
					'cycle_day' => $cycle_day,
					'action' => $params['action']
			);
		}
		else {
			$data = array(
					'type' => 'error',
					'msg' => 'Rule has not been created. Rule already exist.'
			);
		}

		echo Mage::helper('core')->jsonEncode($data);
		die;
	}

	/**
	 * AJAX POST getresponse/index/updateautomation
	 */
	public function updateAutomationAction()
	{
		$this->settingsHandler();
		$params = $this->getRequest()->getParams();

		if (empty($params['category_id']) || empty($params['automation_id']) || empty($params['action']) ||
				empty($params['campaign_id'])
		) {
			$data = array(
					'type' => 'error',
					'msg' => 'Error - empty or invalid field.'
			);
			echo Mage::helper('core')->jsonEncode($data);
			die;
		}

		$cycle_day = (is_numeric($params['cycle_day']) && $params['cycle_day'] >= 0) ? $params['cycle_day'] : '';
		$add = Mage::getModel('getresponse/automations')->updateAutomation(
				$params['automation_id'],
				array(
						'category_id' => $params['category_id'],
						'campaign_id' => $params['campaign_id'],
						'cycle_day' => $cycle_day,
						'action' => $params['action']
				)
		);

		if ($add) {
			$categories = $this->getCategories();
			$data = array(
					'type' => 'success',
					'msg' => 'Rule successfully updated.',
					'automation_id' => $params['automation_id'],
					'category_id' => $params['category_id'],
					'campaign_id' => $params['campaign_id'],
					'category_name' => $categories[$params['category_id']]['name'],
					'campaign_name' => $this->settings->campaigns[$params['campaign_id']],
					'cycle_day' => $cycle_day,
					'action' => $params['action']
			);
		}
		else {
			$data = array(
					'type' => 'error',
					'msg' => 'Rule has not been updated.'
			);
		}

		echo Mage::helper('core')->jsonEncode($data);
		die;
	}

	/**
	 * AJAX POST getresponse/index/deleteautomation
	 */
	public function deleteAutomationAction()
	{
		$params = $this->getRequest()->getParams();

		if (empty($params['automation_id'])) {
			$data = array(
					'type' => 'error',
					'msg' => 'Error - empty or invalid field.'
			);
			echo Mage::helper('core')->jsonEncode($data);
			die;
		}

		$add = Mage::getModel('getresponse/automations')->deleteAutomation($params['automation_id']);

		if ($add) {
			$automations = Mage::getModel('getresponse/automations')->getAutomations($this->current_shop_id);
			$data = array(
					'type' => 'success',
					'msg' => 'Rule successfully deleted.',
					'total' => count($automations)
			);
		}
		else {
			$data = array(
					'type' => 'error',
					'msg' => 'Rule has not been deleted.'
			);
		}

		echo Mage::helper('core')->jsonEncode($data);
		die;
	}

	/**
	 * AJAX POST getresponse/index/changestatus
	 */
	public function changeStatusAction()
	{
		$params = $this->getRequest()->getParams();

		if (empty($params['automation_id'])) {
			$data = array(
					'type' => 'error',
					'msg' => 'An error occurred while trying to update rule status.'
			);
			echo Mage::helper('core')->jsonEncode($data);
			die;
		}

		$add = Mage::getModel('getresponse/automations')->updateAutomation(
				$params['automation_id'],
				array(
						'active' => $params['active']
				)
		);

		if ($add) {
			$data = array(
					'type' => 'success',
					'msg' => 'Status successfully changed.'
			);
		}
		else {
			$data = array(
					'type' => 'error',
					'msg' => 'Status has not been changed.'
			);
		}

		echo Mage::helper('core')->jsonEncode($data);
		die;
	}

	/**
	 * disconnect account
	 */
	public function disconnectAction()
	{
		$this->_initAction();

		Mage::helper('getresponse')->disconnectIntegration($this->current_shop_id);
		Mage::getSingleton('core/session')->addSuccess('You disconnected your Magento from GetResponse.');

		$this->_redirect('getresponse/index/index');
	}

	/**
	 * @param $campaign_id
	 * @param $params
	 *
	 * @return bool
	 */
	public function exportCustomers($campaign_id, $params)
	{
		$customers = Mage::helper('getresponse')->getCustomerCollection();

		//echo "<pre>"; print_r($params); die;

		$cycle_day = '';
		if (isset($params['gr_autoresponder']) && 1 == $params['gr_autoresponder']) {
			$cycle_day = (int)$params['cycle_day'];
		}

		$custom_fields = !empty($params['gr_custom_field']) ? $params['gr_custom_field'] : array();
		$custom_fields = array('firstname' => 'firstname', 'lastname' => 'lastname') + $custom_fields;

		if ( !empty($params['gr_custom_field'])) {
			foreach ($params['gr_custom_field'] as $field_key => $field_value) {
				if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', stripslashes(($field_value)))) {
					Mage::getSingleton('core/session')->addError('Incorrect field name: ' . $field_key . '.');

					return false;
				}
			}
		}

		$reports = [
			'created' => 0,
			'updated' => 0,
			'error' => 0,
		];


		if ( !empty($customers)) {
			foreach ($customers as $customer) {
				$subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
				if ($subscriberModel->isSubscribed()) {
					$result = Mage::helper('getresponse/api')->addContact(
							$campaign_id,
							$customer->getName(),
							$customer->getEmail(),
							$cycle_day,
							Mage::getModel('getresponse/customs')->mapExportCustoms($custom_fields, $customer)
					);

					if (GetresponseIntegration_Getresponse_Helper_Api::CONTACT_CREATED === $result) {
						$reports['created'] ++;
					} elseif(GetresponseIntegration_Getresponse_Helper_Api::CONTACT_UPDATED == $result) {
						$reports['updated'] ++;
					} else {
						$reports['error'] ++;
					}
				}
			}
		}

		//$flashMessage = 'Contact export process has completed. (';
		//$flashMessage .= 'created:'.$reports['created']. ', ';
		//$flashMessage .= 'updated:'.$reports['updated']. ', ';
		//$flashMessage .= 'not added:'.$reports['error'] . ').';

		$flashMessage = 'Export completed!';

		Mage::getSingleton('core/session')->addSuccess($flashMessage);

		return true;
	}

	/**
	 * disable integration if api is not active
	 */
	private function disableIntegrationIfApiNotActive()
	{
		if ( !empty($this->settings->api['api_key'])) {
			$this->grapi()->api_key = $this->settings->api['api_key'];
			$status = $this->grapi()->check_api( $this->settings->api['api_url'], $this->settings->api['api_domain'] );
			if ( !empty($status->codeDescription)) {
				Mage::helper('getresponse')->disconnectIntegration($this->current_shop_id);
				$this->settings->api['api_key'] = null;
				$this->settings->api['api_domain'] = null;
				$this->settings->api['api_url'] = null;
				$this->disconnected = true;
				Mage::getSingleton('core/session')->addError('Invalid API Key. Account has been disconnected.');
				$this->_redirect('getresponse/index/index');
			}
		}
	}

	/**
	 * set from, confirmation subject, confirmation body
	 */
	private function setNewCampaignSettings()
	{
		$locale = Mage::app()->getLocale()->getDefaultLocale();
		$code = strtoupper(substr($locale, 0, 2));

		$from = self::grapi()->get_account_from_fields();
		if (empty($from->codeDescription)) {
			$this->settings->from = $from;
		}
		$confirmation_subject = self::grapi()->get_subscription_confirmations_subject($code);
		if (empty($confirmation_subject->codeDescription)) {
			$this->settings->confirmation_subject = $confirmation_subject;
		}
		$confirmation_body = self::grapi()->get_subscription_confirmations_body($code);
		if (empty($confirmation_body->codeDescription)) {
			$this->settings->confirmation_body = $confirmation_body;
		}
	}

	/**
	 * @param        $parentId
	 * @param        $isChild
	 * @param string $prefix
	 *
	 * @return string
	 */
	function getTreeCategoriesHTML($parentId, $isChild, $prefix = '')
	{
		$options = '';
		$allCats = Mage::getModel('catalog/category')
				->getCollection()
				->addAttributeToSelect('*')
				->addAttributeToFilter('is_active', '1')
				->addAttributeToFilter('parent_id', array('eq' => $parentId));

		foreach ($allCats as $category) {
			$prefix = ($isChild) ? $prefix . '↳' : $prefix;
			$options .= '<option value="' . $category->getId() . '">' . $prefix . ' ' . $category->getName() .
					'</option>';
			$subcats = $category->getChildren();
			if ($subcats != '') {
				$options .= $this->getTreeCategoriesHTML($category->getId(), true, $prefix);
			}
		}

		return $options;
	}

	/**
	 * Get categories
	 * @return array
	 */
	private function getCategories()
	{
		$results = array();
		$categories = Mage::getModel('catalog/category')
				->getCollection()
				->setStoreId($this->current_shop_id)
				->addFieldToFilter('is_active', 1)
				->addAttributeToSelect('*');

		foreach ($categories as $category) {
			$catid = $category->getId();
			$data = $category->getData();
			$results[$catid] = $data;
		}

		return $results;
	}
}