<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{

    const SESSION_KEY = 'mpblog_import_form';

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _getFormFields()
    {
        try {
            $result = $this->_helper()->getConfigValue($this->getImportType(), 'form');
            if ($result){
                return $result->asArray();
            }
        } catch (Exception $e) {}
        return null;
    }

    public function getImportType()
    {
        return $this->getParentBlock()->getImportType();
    }

    protected function _useStoreVisibility()
    {
        return (int)$this->_helper()->getConfigValue($this->getImportType(), 'use_store_visibility');
    }

    protected function _prepareForm()
    {
        $url = $this->getUrl("adminhtml/mpblog_import/process", array('type'=> $this->getImportType()));
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $url,
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);

        $this->setForm($form);
        $fields = $this->_getFormFields();
        if ( ($fields && is_array($fields)) || $this->_useStoreVisibility() ){
            $fieldset = $form->addFieldset('blog_import_form', array('legend' => $this->_helper()->__('Parameters')));
            foreach ($fields as $key => $fieldData){
                $fieldset->addField($key, $fieldData['type'], $fieldData);
            }

            if ($this->_useStoreVisibility()){
                $fieldset->addField('stores', 'multiselect',
                    array(
                        'label'     => $this->_helper()->__('Import to'),
                        'required'  => true,
                        'name'      => 'stores[]',
                        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
                    )
                );

            }
        }



        if (Mage::getSingleton('adminhtml/session')->getData(self::SESSION_KEY)) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getData(self::SESSION_KEY));
            Mage::getSingleton('adminhtml/session')->setData(self::SESSION_KEY, null);
        }

        return parent::_prepareForm();
    }





}