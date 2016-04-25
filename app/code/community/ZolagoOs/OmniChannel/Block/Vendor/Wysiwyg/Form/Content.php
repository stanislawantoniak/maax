<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Vendor_Wysiwyg_Form_Content extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $form = new Varien_Data_Form(array('id' => 'wysiwyg_edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->addType('editor', Mage::getConfig()->getBlockClassName('udropship/vendor_wysiwyg_form_element_editor'));

        $config['document_base_url']     = $this->getData('store_media_url');
        $config['store_id']              = $this->getData('store_id');
        $config['add_variables']         = false;
        $config['add_widgets']           = false;
        $config['add_directives']        = true;
        $config['use_container']         = true;
        $config['container_class']       = 'hor-scroll';
        $config['directives_url']        = $this->getUrl('udropship/vendor_wysiwyg/directive');
        $config['files_browser_window_url'] = $this->getUrl('udropship/vendor_wysiwyg_images/index');
        
        $form->addField($this->getData('editor_element_id'), 'editor', array(
            'name'      => 'content',
            'style'     => 'width:725px;height:460px',
            'required'  => true,
            'force_load' => true,
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig($config)
        ));

        return $form->toHtml();
    }
}
