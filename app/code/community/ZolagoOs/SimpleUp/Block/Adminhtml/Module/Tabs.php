<?php
/**
  
 */

class ZolagoOs_SimpleUp_Block_Adminhtml_Module_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('usimpleup_module_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id', 0);

        Mage::dispatchEvent('usimpleup_license_tabs', array('container'=>$this));

        $this->addTab('manage_modules_section', array(
            'label'     => Mage::helper('usimpleup')->__('Manage Modules'),
            'title'     => Mage::helper('usimpleup')->__('Manage Modules'),
            'content'   => $this->getLayout()->createBlock('usimpleup/adminhtml_module_grid')->toHtml(),
        ));

        $this->addTab('add_modules_section', array(
            'label'     => Mage::helper('usimpleup')->__('Add Modules'),
            'title'     => Mage::helper('usimpleup')->__('Add Modules'),
            'content'   => $this->getLayout()->createBlock('core/template')->setTemplate('usimpleup/add_modules.phtml')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}