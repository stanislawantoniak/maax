<?php
/**
 * creating form fieldset 
 */
abstract class Zolago_Common_Model_Form_Fieldset_Abstract  {

    protected $_fieldset;
    protected $_model;
    protected $_helper;

    public function __construct($fieldset = null) {
        $this->_helper = $this->_getHelper();
        if ($fieldset) {
            $this->setFieldset($fieldset);
        }
    }
    
    public function getFieldset() {
        return $this->_fieldset;
    }

    /**
     * abstract method for creating helper
     */
    abstract protected function _getHelper();
    
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

    /**
     * Retrieve url of skins file
     *
     * @param   string $file path to file in skin
     * @param   array $params
     * @return  string
     */
    public function getSkinUrl($file = null, array $params = array()) {
        return Mage::getDesign()->getSkinUrl($file, $params);
    }

    /**
     * field phone
     */
    protected function _addFieldPhone() {
        $this->_fieldset->addField('phone', 'text', array(
                                       'name'          => 'phone',
                                       'label'         => $this->_helper->__('Phone'),
                                       'required'      => true,
                                       'class'         => 'validate-phone-number form-control',
                                       "maxlength"     => 50
                                   ));

    }
    
    /**
     * field email
     */
    protected function _addFieldEmail() {
        $this->_fieldset->addField('email', 'text', array(
                                       'name'          => 'email',
                                       'label'         => $this->_helper->__('Email'),
                                       'class'         => 'validate-email form-control',
                                       "maxlength"     => 100
                                   ));


    }
    /**
     * field first name
     */
    protected function _addFieldFirstname() {
        $this->_fieldset->addField('firstname', 'text', array(
                                       'name'          => 'firstname',
                                       'label'         => $this->_helper->__('First name'),
                                       "maxlength"     => 100,
									   "class"		   => "form-control"
                                   ));


    }
    /**
     * field last name
     */
    protected function _addFieldLastname() {
        $this->_fieldset->addField('lastname', 'text', array(
                                       'name'          => 'lastname',
                                       'label'         => $this->_helper->__('Last name'),
                                       "maxlength"     => 100,
									   "class"		   => "form-control"
                                   ));


    }
    
    /**
     * field is_active
     */
    protected function _addFieldIsActive() {
        $this->_fieldset->addField('is_active', 'select', array(
                                       'name'          => 'is_active',
                                       'label'         => $this->_helper->__('Is active'),
                                       'required'      => true,
									   "class"		   => "form-control",
                                       'options'       => Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray()
                                   ));

    }


}