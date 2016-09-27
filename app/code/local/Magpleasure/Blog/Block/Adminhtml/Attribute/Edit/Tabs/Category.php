<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Category
    extends Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $stores = false;

        if (!Mage::app()->isSingleStoreMode()){
            if ($this->isStoreFilterApplied()){
                $stores = array($this->getAppliedStoreId());
            } else {
                $stores = $this->_helper()->getCommon()->getStore()->getFrontendStoreIds();
            }
        }

        /** @var Magpleasure_Blog_Model_Category $category  */
        $category= Mage::getModel('mpblog/category');

        $fieldset = $form->addFieldset('add_category_legend', array('legend' => $this->_helper()->__('Add Categories')));

        $fieldset->addField('add_category', 'checkbox', array(
            'label'     => $this->_helper()->__("Add Categories"),
            'required'  => false,
            'name'      => 'add_category',
            'checked'   => $this->_isChecked('add_category'),
            'onchange'  => "checkboxChanged('add_category');",
        ));

        $fieldset->addField('add_category_values', 'multiselect',array(
            'label'     => $this->_helper()->__('Categories to add'),
            'required'  => false,
            'name'      => 'add_category_values[]',
            'values'    => $category->getCategoryList($stores),
            'disabled'  => !$this->_isChecked('add_category'),
        ));

        $fieldset = $form->addFieldset('remove_category_legend', array('legend' => $this->_helper()->__('Remove Categories')));

        $fieldset->addField('remove_category', 'checkbox', array(
            'label'     => $this->_helper()->__("Remove Categories"),
            'required'  => false,
            'name'      => 'remove_category',
            'checked'   => $this->_isChecked('remove_category'),
            'onchange'  => "checkboxChanged('remove_category');",
        ));

        $fieldset->addField('remove_category_values', 'multiselect',array(
            'label'     => $this->_helper()->__('Categories to remove'),
            'required'  => false,
            'name'      => 'remove_category_values[]',
            'values'    => $category->getCategoryList($stores),
            'disabled'  => !$this->_isChecked('remove_category'),
        ));

        $form->setUseContainer(false);
        $form->setValues($this->_getValues());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("Posted in Categories");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
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
