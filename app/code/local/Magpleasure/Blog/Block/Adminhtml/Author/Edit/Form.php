<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Author_Edit_Form
    extends Magpleasure_Common_Block_Adminhtml_Widget_Ajax_Form
{

    protected function _getRegistryData()
    {
        return Mage::registry('author_model') ? Mage::registry('author_model') : new Varien_Object(array());
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('author_form', array('legend'=>$this->_helper()->__('Author Details')));

        $fieldset->addField('name', 'text', array(
            'label'     =>$this->_helper()->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));


        $example = "https://plus.google.com/109412257237874861202/about";
        $url = "https://support.google.com/webmasters/answer/2539557?hl=en";

        $fieldset->addField('google_profile', 'text', array(
            'label'     =>$this->_helper()->__('Google Profile URL'),
            'required'  => false,
            'class' => 'validate-uri',
            'name'      => 'google_profile',
            'note'      => $this->_helper()->__("Example: <i>%s</i><br/>Read more here - <a href='%s' target='_blank'>Using rel=author</a>.", $example, $url),
        ));

        $fieldset->addField('facebook_profile', 'text', array(
            'label'     =>$this->_helper()->__('Facebook Profile'),
            'required'  => false,
            'class' => 'validate-uri',
            'name'      => 'facebook_profile',
        ));

        $fieldset->addField('twitter_profile', 'text', array(
            'label'     =>$this->_helper()->__('Twitter Profile'),
            'required'  => false,
            'class'     => 'validate-twitter',
            'name'      => 'twitter_profile',
        ));

        if ( $this->_getRegistryData() ) {
            $data = $this->_getRegistryData()->getData();
        } else {
            $data = array();
        }

        # Define default values
        if (!isset($data['name'])){
            $data['name'] = Mage::registry('default_author_name');
        }

        $form->setValues($data);
        $form->setUseContainer(false);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}