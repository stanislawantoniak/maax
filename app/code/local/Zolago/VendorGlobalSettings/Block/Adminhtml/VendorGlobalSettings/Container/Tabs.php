<?php
class Zolago_VendorGlobalSettings_Block_Adminhtml_VendorGlobalSettings_Container_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('zolagovendorglobalsettings_settings_tab');
        $this->setDestElementId('vendor_global_settings_form');
        $this->setTitle(Mage::helper('zolagovendorglobalsettings')->__('Vendor Global Settings'));
    }
	
	protected function _prepareLayout()
    {
    	// Uncomment to see test tab
    	
		// $this->addTab('test', array(
            // 'label'     => Mage::helper('zolagovendorglobalsettings')->__('Test'),
            // 'content'   => $this->_translateHtml($this->getLayout()
                // ->createBlock('zolagovendorglobalsettings/adminhtml_vendorglobalsettings_container_tab_test')->toHtml()),
            // 'active'    => true
        // ));
		
        return parent::_prepareLayout();
	}
	
	protected function _translateHtml($html)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }
}
