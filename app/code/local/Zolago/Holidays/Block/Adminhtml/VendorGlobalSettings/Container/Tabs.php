<?php
class Zolago_Holidays_Block_Adminhtml_VendorGlobalSettings_Container_Tabs 
	extends Zolago_VendorGlobalSettings_Block_Adminhtml_VendorGlobalSettings_Container_Tabs
{

	protected function _prepareLayout()
    {
    	
		$this->addTab('processingtime', array(
            'label'     => Mage::helper('zolagoholidays')->__('Processing time'),
            'content'   => $this->_translateHtml($this->getLayout()
                ->createBlock('zolagoholidays/adminhtml_vendorglobalsettings_container_tab_processingtime')->toHtml()),
            'active'    => true
        ));
		
        return parent::_prepareLayout();
	}
}
