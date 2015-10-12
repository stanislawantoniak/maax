<?php
/**
 * assign document type to vendor
 */

class GH_Regulation_Block_Adminhtml_Dropship_Settings_Type extends
    Mage_Adminhtml_Block_Widget_Form {


    protected $_form;
    
    /**
     * fieldset with documents kinds
     */
    protected function _createFieldset($item) {
        $hlp = Mage::helper('ghregulation');
        $form = $this->getForm();
        $fieldset = $form->addFieldset('kind_'.$item->getId(),
        array (
            'legend' => $item->getName(),
        ));
        $fieldset->addType('document_list','GH_Regulation_Block_Adminhtml_Dropship_Settings_Type_List');
        $fieldset->addField('active_'.$item->getId(),'checkbox',array(
            'label' => $hlp->__('Is active'),
            'name' => 'vendor_kind[]',            
            'checked' => $item->getIsActive(),
            'value' => $item->getRegulationKindId(),
            
        ));
        $fieldset->addField('typelist_'.$item->getId(),'document_list',array(
            'label' => $hlp->__('Document type list'),            
            'createUrl' => $this->getUrl('*/*/kindEdit',array('kind_id'=> $item->getId(),'id'=>$this->getRequest()->get('id'))),
            'vendor_id' => $this->getRequest()->get('id'),
            'regulation_kind_id' => $item->getId(),
        ));
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $hlp = Mage::helper('ghregulation');
        $vendorId = $this->getRequest()->get('id');
        
        $list = Mage::getResourceModel('ghregulation/regulation_kind_collection');
        $list->getSelect()->
            joinLeft(
                array ('vendor_kind' => Mage::getSingleton('core/resource')->getTableName('ghregulation/regulation_vendor_kind')),
                'vendor_kind.regulation_kind_id = main_table.regulation_kind_id AND vendor_kind.vendor_id = '.$vendorId,
                array('is_active' => 'IF(ISNULL(vendor_kind.regulation_kind_id),0,1)')
            );
        
        foreach ($list as $key => $item) {
            $this->_createFieldset($item);
        }


        return parent::_prepareForm();
    }

}