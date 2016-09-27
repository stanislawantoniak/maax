<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Category_Edit_Tab_General
    extends Magpleasure_Blog_Block_Adminhtml_Filterable_Widget_Form
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

    protected function _isNew()
    {
        return !Mage::registry('current_category')->getId();
    }

    protected function _getGenerateUrlKeyButtonHtml()
    {
        /** @var $button Mage_Adminhtml_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');

        if ($button){
            $button->addData(array(
                'label' => $this->_helper()->__("Update"),
                'title' => $this->_helper()->__("Update"),
                'onclick' => "Transliteration.transliterate($('name').value.replace('/',' '), 'url_key'); return false;",
                'style'   => 'display: none;',
                'id'    => 'generate_permalink'

            ));
            return $button->toHtml()."
            <script type=\"text/javascript\">
            $('name').observe('blur', function(e){
                if (!$('url_key').value){
                    Transliteration.transliterate($('name').value.replace('/',' '), 'url_key');
                    $('generate_permalink').style.display = 'inline';
                }
            });
            </script>
            ";
        }
        return "";
    }

    protected function _getFormData()
    {
        if (Mage::getSingleton('adminhtml/session')->getPostData()) {
            $data = Mage::getSingleton('adminhtml/session')->getPostData();
            Mage::getSingleton('adminhtml/session')->getPostData(null);
            return $data;

        } elseif (Mage::registry('current_category')) {
            return Mage::registry('current_category')->getData();
        } else {
            return array();
        }
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => $this->_helper()->__('General')));

        $fieldset->addField('name', 'text', array(
            'label' => $this->_helper()->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
            'style'  => 'width: 36em;',
        ));

        $fieldset->addField('url_key', 'text', array(
            'label' => $this->_helper()->__('URL Key'),
            'class' => 'validate-identifier',
            'required' => true,
            'name' => 'url_key',
            'style' => 'display: inline; width: 29em;',
            'note' => $this->_helper()->__('Relative to Website Base URL'),
            'after_element_html' => $this->_isNew() ? $this->_getGenerateUrlKeyButtonHtml() : "",
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label' => $this->_helper()->__('Sort Order'),
            'name' => 'sort_order',
            'note' => $this->_isNew() ? $this->_helper()->__("Automatically generated value") : null,
        ));

        /** @var Magpleasure_Blog_Model_Category $post  */
        $post = Mage::getSingleton('mpblog/category');

        $fieldset->addField('status', 'select',
            array(
                'name'      => 'status',
                'label'     => $this->_helper()->__('Status'),
                'values'    => $post->toOptionArray(),
        ));

        if (!Mage::app()->isSingleStoreMode()){
            if ($this->isStoreFilterApplied()){
                $fieldset->addField('stores', 'hidden',
                    array(
                        'name' => 'stores[]',
                    ));

            } else {

                $fieldset->addField('stores', 'multiselect',
                    array(
                        'label'     => $this->_helper()->__('Visible in'),
                        'required'  => true,
                        'name'      => 'stores[]',
                        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
                    ));
            }
        } else {
            $fieldset->addField('stores', 'hidden',
                array(
                    'name' => 'stores[]',
                )
            );
        }

        $data = $this->_getFormData();

        # Apply global store filter to entity
        if (!Mage::app()->isSingleStoreMode() && $this->isStoreFilterApplied()){
            $data['stores'] = $this->getAppliedStoreId();
        } elseif (Mage::app()->isSingleStoreMode()) {
            $data['stores'] = Mage::app()->getDefaultStoreView()->getId();
        }

        # Define MAX Link Sort Order
        if (!isset($data['sort_order'])){

            $maxSortOrder = 0;

                /** @var Magpleasure_Blog_Model_Category $category */
            $category = Mage::getModel('mpblog/category');

            $appliedStoreId = $this->isStoreFilterApplied() ? $this->getAppliedStoreId() : null;
            $maxSortOrder = $category->getMaxSortOrder($appliedStoreId);

            $data['sort_order'] = $maxSortOrder + 1;
        }

        $form->setValues($data);


        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("General");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_helper()->__("General");
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