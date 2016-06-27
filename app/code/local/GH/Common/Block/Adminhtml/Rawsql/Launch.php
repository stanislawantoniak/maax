<?php

/**
 * Class Gh_Common_Block_Adminhtml_Rawsql_Launch
 */
class GH_Common_Block_Adminhtml_Rawsql_Launch extends Mage_Adminhtml_Block_Widget_Form_Container {

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
        $id = $this->getModel()->getId();
            $this->_addButton('launch', array (
                                  'label' => Mage::helper('ghcommon')->__('Launch'),
                                  'onclick' => "setLocation('{$this->getUrl('*/*/launch',array('id'=>$id))}')",
                                  'class' => 'dg-totals-lable-1',
                              ));

            if(count(Mage::registry('ghcommon_query_result'))){
                $this->_addButton('downloadcsv', array (
                    'label' => Mage::helper('ghcommon')->__('Download CSV'),
                    'onclick' => "setLocation('{$this->getUrl('*/*/download',array('id' => $id))}')",
                ));
            }
            
            $this->_addButton('edit', array (
                                  'label' => Mage::helper('ghcommon')->__('Edit query'),
                                  'onclick' => "setLocation('{$this->getUrl('*/*/edit',array('id' => $id))}')",
                              ));

                             
        parent::__construct();
        $this->_removeButton('add');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('delete');
    }

    public function getBackUrl() {
        return $this->getUrl("*/*/index");
    }


    public function getHeaderText() {
        return  Mage::helper('ghcommon')->__('Launch query');
    }


}
