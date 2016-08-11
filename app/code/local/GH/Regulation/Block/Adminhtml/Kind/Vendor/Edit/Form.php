<?php
/**
 * Brandshop settings form
 */

class GH_Regulation_Block_Adminhtml_Kind_Vendor_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm()
    {
        $request = $this->getRequest();
        
        $vendorId = $request->get('id');
        $kindId = $request->get('kind_id');
        $model = Mage::getModel('ghregulation/regulation_kind')->load($kindId);

        /** @var GH_Regulation_Model_Resource_Regulation_Type_Collection $collection */
        $collection = Mage::getResourceModel('ghregulation/regulation_type_collection')
            ->addFilter('regulation_kind_id',$kindId);
        $typeValues = $collection->toOptionHash(Mage::helper('ghregulation')->__('--- choose document type ---'));

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/kindSave', array('vendor_id' => $vendorId,'kind_id'=>$kindId)),
            'method'    => 'post'
        ));
        
        $fieldset = $form->addFieldset('document_vendor_settings', array('legend' => Mage::helper('ghregulation')->__('Add new document type'), 'class' => 'fieldset-wide'));
        $fieldset->addField('kind_name', 'note', array(
            'label'     => Mage::helper('ghregulation')->__('Document kind'),
            'text' 		=> $model->getData('name'),
        ));
        $fieldset->addField('document_type','select',
            array  (
                'required' => true,
                'name' => 'regulation_type_id',
                'label' => Mage::helper('ghregulation')->__('Document type'),
                'values' => $typeValues,
            )
        );
        $fieldset->addField('date', 'date', array(
            'label'     => Mage::helper('ghregulation')->__('Document apply from'),
            'required'  => true,
            'name'      => 'date',
            'format' 	=> 'yyyy-MM-dd',
            'image'		=> $this->getSkinUrl('images/grid-cal.gif'),
            'time'		=> false,
            'after_element_html' => Mage::helper('ghregulation')->__('<small>Date format (YYYY-MM-DD)</small>'),
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
        
    }
}