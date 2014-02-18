<?php
/**
 * creating form fieldset for pos
 */
class Zolago_Pos_Helper_Form_Fieldset extends Mage_Core_Helper_Abstract {

    protected $_fieldset;
    protected $_model;
    protected $_helper;

    public function __construct($fieldset = null) {
        $this->_helper = Mage::helper('zolagopos');
        if ($fieldset) {
            $this->setFieldset($fieldset);
        }
    }
    public function setFieldset($fieldset) {
        $this->_fieldset = $fieldset;
    }
    /**
     * creating function name from string
     */
    protected function _getActionName($field) {
        $tmp = explode('_',$field);
        $func = '_addField';
        foreach ($tmp as $name) {
            $func .= ucfirst($name);
        }
        return $func;
    }

    /**
     * build form
     */
    public function prepareForm($params) {
        foreach ($params as $field) {
            $action = $this->_getActionName($field);
            $this->$action();
        }
        return $this->_fieldset;
    }

    /**
     * setting model
     */
    public function setModel($model) {  
        $this->_model = $model;
    }
}