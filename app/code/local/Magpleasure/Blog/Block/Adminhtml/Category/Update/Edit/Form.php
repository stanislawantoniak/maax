<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Category_Update_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    const CATEGORIES_KEY = 'mpblog_category_update_categories';

    protected $_values = array();

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
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/massUpdateStoreViewGo'),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        $fieldset = $form->addFieldset('add_store_legend', array('legend' => $this->_helper()->__('Add Store View')));

        $fieldset->addField('category_ids', 'hidden', array(
            'name'  => 'category_ids',
        ));

        $fieldset->addField('add_store_view', 'checkbox', array(
            'label'     => $this->_helper()->__("Add Store View"),
            'required'  => false,
            'name'      => 'add_store_view',
            'checked'   => false,
            'onchange'  => "checkboxChanged('add_store_view');",
        ));

        $fieldset->addField('add_store', 'multiselect',array(
            'label'     => $this->_helper()->__('Store View to add'),
            'required'  => false,
            'name'      => 'add_store[]',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'disabled'  => true,
        ));

        $fieldset = $form->addFieldset('remove_store_legend', array('legend' => $this->_helper()->__('Remove Store View')));

        $fieldset->addField('remove_store_view', 'checkbox', array(
            'label'     => $this->_helper()->__("Remove Store View"),
            'required'  => false,
            'name'      => 'remove_store_view',
            'checked'   => false,
            'onchange'  => "checkboxChanged('remove_store_view');",
        ));

        $fieldset->addField('remove_store', 'multiselect',array(
            'label'     => $this->_helper()->__('Store View to remove'),
            'required'  => false,
            'name'      => 'remove_store[]',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'disabled'  => true,
        ));


        $form->setUseContainer(true);
        $form->setValues($this->_getValues());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getValues()
    {
        if (Mage::getSingleton('adminhtml/session')->getCategoryUpdateData()) {
            $this->_values = Mage::getSingleton('adminhtml/session')->getCategoryUpdateData();
            Mage::getSingleton('adminhtml/session')->getCategoryUpdateData(null);
        }

        $this->_values['category_ids'] = implode(",", Mage::registry(self::CATEGORIES_KEY));
        return $this->_values;
    }

}
