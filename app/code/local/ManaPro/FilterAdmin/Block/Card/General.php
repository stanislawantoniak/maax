<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdmin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterAdmin_Block_Card_General extends Mana_Admin_Block_Crud_Card_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	/**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
		// form - collection of fieldsets
		$form = new Varien_Data_Form(array(
			'id' => 'mf_general',
			'html_id_prefix' => 'mf_general_',
			'use_container' => true,
			'method' => 'post',
			'action' => $this->getUrl('*/*/save', array('_current' => true)),
			'field_name_suffix' => 'fields',
			'model' => $this->getModel(),
		));
        /** @noinspection PhpUndefinedMethodInspection */
        Mage::helper('mana_core/js')->options('edit-form', array('subforms' => array('#mf_general' => '#mf_general')));
		
		// fieldset - collection of fields
        /** @noinspection PhpParamsInspection */
        $fieldset = $form->addFieldset('mfs_general', array(
			'title' => $this->__('General Information'),
			'legend' => $this->__('General Information'),
		));
		$fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

        $field = $fieldset->addField('display', 'checkbox', array(
            'label' => $this->__('Use colors and images'),
            'name' => 'display',
            'required' => true,
            'checked' => $this->getModel()->getData("display") == "color",
        ));
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $this->setForm($form);
        return parent::_prepareForm();
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// TAB PROPERTIES
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel() {
    	return $this->__('General');
    }
    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
    	return $this->__('General');
    }
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
    	return true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
    	return false;
    }
    public function getAjaxUrl() {
    	return Mage::helper('mana_admin')->getStoreUrl('*/*/tabGeneral', 
			array('id' => Mage::app()->getRequest()->getParam('id')), 
			array('ajax' => 1)
		);
    }

    #region Dependencies

    /**
     * @return Mana_Filters_Model_Filter2
     */
    public function getModel() {
        return Mage::registry('m_crud_model');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    #endregion
}