<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Tag_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => $this->_helper()->__('Meta Data')));

        $fieldset->addField('meta_title', 'text', array(
            'label' => $this->_helper()->__('Meta Title'),
            'required' => false,
            'name' => 'meta_title',
        ));

        $fieldset->addField('meta_tags', 'text', array(
            'label' => $this->_helper()->__('Meta Keywords'),
            'required' => false,
            'name' => 'meta_tags',
        ));

        $fieldset->addField('meta_description', 'textarea', array(
            'label' => $this->_helper()->__('Meta Description'),
            'required' => false,
            'name' => 'meta_description',
        ));

        if (Mage::getSingleton('adminhtml/session')->getTagData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getTagData());
            Mage::getSingleton('adminhtml/session')->getTagData(null);
        } elseif (Mage::registry('current_tag')) {
            $form->setValues(Mage::registry('current_tag')->getData());
        }
        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("Meta Data");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_helper()->__("Meta Data");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}