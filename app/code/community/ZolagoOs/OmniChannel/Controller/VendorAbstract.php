<?php
class ZolagoOs_OmniChannel_Controller_VendorAbstract extends Mage_Core_Controller_Front_Action
{
    protected $_loginFormChecked = false;

    /**
     * @return Zolago_Dropship_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('udropship/session');
    }

    protected function _setTheme()
    {
        $theme = explode('/', Mage::getStoreConfig('udropship/vendor/interface_theme'));
        if (empty($theme[0]) || empty($theme[1])) {
            $theme = 'default/default';
        }
        Mage::getDesign()->setPackageName($theme[0])->setTheme($theme[1]);
    }

    protected $_extraMessageStorages = array();
    public function setExtraMessageStorages($storages)
    {
        $this->_extraMessageStorages = (array)$storages;
    }

	/**
	 * @param null|array $handles
	 * @param null|string $active
	 * @param null|string $title
	 */
    protected function _renderPage($handles=null, $active=null, $title = null)
    {
        $this->_setTheme();
        $this->loadLayout($handles);
        $root = $this->getLayout()->getBlock('root');

        if ($root) {
            $root->addBodyClass('udropship-vendor');
        }
		if ($active && ($header = $this->getLayout()->getBlock('header'))) {
			$header->setActivePage($active);
		}
		if (!empty($title)) {
			$head = $this->getLayout()->getBlock('head');
			$head->setTitle($title);
		}
        /*
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $pager = $this->getLayout()->getBlock('shipment.grid.toolbar');
            if (!$pager) {
                $pager = $this->getLayout()->getBlock('product.grid.toolbar');
            }
            if ($pager) {
                $pager->setTemplate('page/html/pager13.phtml');
            }
        }
        */
        $this->_initLayoutMessages('udropship/session');
        if (is_array($this->_extraMessageStorages) && !empty($this->_extraMessageStorages)) {
            foreach ($this->_extraMessageStorages as $ilm) {
                $this->_initLayoutMessages($ilm);
            }
        }
        $this->renderLayout();
    }

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
		/***********************************************************************
		 *  Changning locale
		 ***********************************************************************/
		if(!$this->_getSession()->getLocale()){
			$this->_getSession()->setLocale(Mage::app()->getLocale()->getLocaleCode());
		}
		if(!Mage::registry("dropship_switch_lang")){
			Mage::register("dropship_switch_lang", 1);
		}
        // a brute-force protection here would be nice
        parent::preDispatch();

        $r = $this->getRequest();

        if (!$r->isDispatched()) {
            return;
        }
        $action = $r->getActionName();
        $session = Mage::getSingleton('udropship/session');

        if (!$session->isLoggedIn() && !Mage::registry('udropship_login_checked')) {
            Mage::register('udropship_login_checked', true);
            if ($r->getPost('login')) {
                $login = $this->getRequest()->getPost('login');
                if (!empty($login['username']) && !empty($login['password'])) {
                    try {
                        if (!$session->login($login['username'], $login['password'])) {
                            $session->addError($this->__('Invalid username or password.'));
                        }
                        $session->setUsername($login['username']);
                    }
                    catch (Exception $e) {
                        $session->addError($e->getMessage());
                    }
                } else {
                    $session->addError($this->__('Login and password are required'));
                }
                if ($session->isLoggedIn()) {
                    $this->_loginPostRedirect();
                }
            }
            if (!preg_match('#^(login|logout|password|setlocale|accept|confirmRegulation|regulationexpired|regulationaccepted|saveVendorDocumentPost|acceptPost|getDocument|getVendorUploadedDocument)#i', $action)) {
                $this->_forward('login', 'vendor', 'udropship');
            }
        } else {
            if (Mage::helper('udropship')->isModuleActive('ZolagoOs_OmniChannelVendorPortalUrl')) {
                Mage::getConfig()->setNode('global/models/core/rewrite/url', 'ZolagoOs_OmniChannelVendorPortalUrl_Model_Url');
            } else {
                Mage::getConfig()->setNode('global/models/core/rewrite/url', 'ZolagoOs_OmniChannel_Model_Url');
            }
        }

    }

    protected function _loginPostRedirect()
    {
        $this->_getSession()->loginPostRedirect($this);
    }

    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        if (!is_null($module)) {
            $module = Mage::app()->getFrontController()->getRouterByRoute($module)->getFrontNameByRoute($module);
        }
        return parent::_forward($action, $controller, $module, $params);
    }

}