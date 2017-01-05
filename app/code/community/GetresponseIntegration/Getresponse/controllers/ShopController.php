<?php

/**
 * Class GetresponseIntegration_Getresponse_IndexController
 */
class GetresponseIntegration_Getresponse_ShopController extends Mage_Adminhtml_Controller_Action
{
    public $current_shop_id;
    public $settings;
    public $shop_settings;

	/**
	 * construct
	 */
	protected function _construct()
	{
		$this->current_shop_id = Mage::helper('getresponse')->getStoreId();
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

        $this->shop_settings = Mage::getModel('getresponse/shop')->load($this->current_shop_id)->getData();

		Mage::helper('getresponse/api')->setApiDetails(
            $this->settings->api['api_key'],
            $this->settings->api['api_url'],
            $this->settings->api['api_domain']
		);
	}

	/**
	 * GET getresponse/shop/index
	 */
	public function indexAction()
	{
		$this->_title($this->__('Shop'))->_title($this->__('GetResponse'));

		$this->active_tab = 'shop';

		$this->_initAction();

        $gr_shops = Mage::helper('getresponse/api')->getShops();

        $hasActiveRegistrationSubscription = true;

        if (empty($this->settings->api['campaign_id']) || $this->settings->api['active_subscription'] == 0) {
            $hasActiveRegistrationSubscription = false;
        }

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/shop.phtml')
				->assign('gr_shops', (array) $gr_shops)
                ->assign('shop_enabled', $this->shop_settings['is_enabled'])
                ->assign('current_shop_id', $this->shop_settings['gr_shop_id'])
                ->assign('has_active_registration_subscription', $hasActiveRegistrationSubscription)
		);

		$this->renderLayout();
	}

    public function updateAction()
    {
        $shopEnable = $this->getRequest()->getParam('shop_enabled');
        $shopId = $this->getRequest()->getParam('shop_id');

        if (empty($shopId)) {
            Mage::getSingleton('core/session')->addError('Incorrect shop, please try again.');

            $this->_redirect('getresponse/shop/index');
            return;
        }

        $data = array(
            'is_enabled' => empty($shopEnable) ? 0 : 1,
            'gr_shop_id' => $shopId,
        );

        if (false === Mage::getModel('getresponse/shop')->update($data, $this->current_shop_id)) {
            Mage::getSingleton('core/session')->addError('Error during settings details save.');
        }

        Mage::getSingleton('core/session')->addSuccess('Settings has been updated');
        $this->_redirect('getresponse/shop/index');

	}

    /**
     * AJAX POST getresponse/shop/add
     */
    public function addAction()
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        $params = $this->getRequest()->getParams();

        if (0 === strlen($params['shop_name'])) {
            $data = array(
                'type' => 'error',
                'msg' => 'Shop name is incorrect.'
            );

            echo $coreHelper->jsonEncode($data);
            die;
        }

        $this->settingsHandler();

        /** @var GetresponseIntegration_Getresponse_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('getresponse/api');

        try {
            $shop = $apiHelper->addShop($params['shop_name']);

            $data = array(
                'type' => 'success',
                'msg' => 'Shop '.$shop->name.' successfully created.',
                'shop_id' => $shop->shopId,
                'shop_name' => $shop->name
            );

        } catch (\Exception $e) {
            $data = array(
                'type' => 'error',
                'msg' => 'Shop has not been created.',
            );
        }

        echo $coreHelper->jsonEncode($data);
        die;
	}
}