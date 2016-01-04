<?php

/**
 * Class Gh_Common_Block_Adminhtml_Rawsql_Edit
 */
class GH_Common_Block_Adminhtml_Rawsql_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * @return GH_Common_Model_Sql
     */
    public function getModel() {
        return Mage::registry('ghcommon_sql');
    }

    public function __construct() {
        $this->_objectId = 'id';
        $this->_blockGroup = 'ghcommon';
        $this->_controller = 'adminhtml_rawsql';
        $this->_updateButton('save','onclick','rawsqlControl.save(0);');
        if (!$this->getIsNew()) {
            $this->_addButton('launch', array (
                                  'label' => Mage::helper('ghcommon')->__('Launch'),
                                  'onclick' => 'rawsqlControl.save(1)',
                                  'class' => 'dg-totals-lable-1',
                              ));
        }
        parent::__construct();
    }

    public function getBackUrl() {
        return $this->getUrl("*/*/index");
    }

    public function getIsNew() {
        return !(int)$this->getModel()->getId();
    }

    public function getHeaderText() {
        if (!$this->getIsNew()) {
            return Mage::helper('ghcommon')->__('Edit query');
        }
        return  Mage::helper('ghcommon')->__('New query');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save', array("_current" => true));
    }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/delete', array("_current" => true));
    }

}
