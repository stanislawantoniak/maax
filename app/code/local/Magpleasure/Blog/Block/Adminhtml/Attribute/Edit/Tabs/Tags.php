<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Tags
    extends Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('add_tag_legend', array('legend' => $this->_helper()->__('Add Tags')));

        $fieldset->addType('tags', 'Magpleasure_Common_Block_System_Entity_Form_Element_Ajax_Tags');

        $fieldset->addField('add_tag', 'checkbox', array(
            'label'     => $this->_helper()->__("Add Tags"),
            'required'  => false,
            'name'      => 'add_tag',
            'checked'   => $this->_isChecked('add_tag'),
            'onchange'  => "checkboxChanged('add_tag');",
        ));

        $fieldset->addField('add_tag_values', 'tags', array(
            'label' => $this->_helper()->__('Tags'),
            'required' => false,
            'name' => 'add_tag_values',
            'style'     => 'width: 567px;',
            'note'  => $this->_helper()->__("Please enter Tags separated by comma"),
            'data_source' => array(
                'filter_field' => 'name',
                'sort_field' => 'name',
                'sort_direction' => 'ASC',
                'entity_id_pattern' => "{{name}}",
                'entity_label_pattern' => "{{name}}",
                'model' => 'mpblog/tag',
            ),
            'disabled' => !$this->_isChecked('add_tag'),
        ));


        $fieldset = $form->addFieldset('remove_tag_legend', array('legend' => $this->_helper()->__('Remove Tags')));

        $fieldset->addType('tags', 'Magpleasure_Common_Block_System_Entity_Form_Element_Ajax_Tags');

        $fieldset->addField('remove_tag', 'checkbox', array(
            'label'     => $this->_helper()->__("Remove Tags"),
            'required'  => false,
            'name'      => 'remove_tag',
            'checked'   => $this->_isChecked('remove_tag'),
            'onchange'  => "checkboxChanged('remove_tag');",
        ));

        $fieldset->addField('remove_tag_values', 'tags', array(
            'label' => $this->_helper()->__('Tags'),
            'required' => false,
            'name' => 'remove_tag_values',
            'style'     => 'width: 567px;',
            'note'  => $this->_helper()->__("Please enter Tags separated by comma"),
            'data_source' => array(
                'filter_field' => 'name',
                'sort_field' => 'name',
                'sort_direction' => 'ASC',
                'entity_id_pattern' => "{{name}}",
                'entity_label_pattern' => "{{name}}",
                'model' => 'mpblog/tag',
            ),
            'disabled' => !$this->_isChecked('remove_tag'),
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
        return $this->_helper()->__("Tagged with");
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
