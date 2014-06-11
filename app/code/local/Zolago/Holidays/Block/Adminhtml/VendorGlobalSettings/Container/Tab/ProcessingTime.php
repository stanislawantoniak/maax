<?php
class Zolago_Holidays_Block_Adminhtml_VendorGlobalSettings_Container_Tab_ProcessingTime extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function canShowTab() {
        return 1;
    }

    public function getTabLabel() {
        return Mage::helper('zolagoholidays')->__("Processing Time");
    }

    public function getTabTitle() {
        return Mage::helper('zolagoholidays')->__("Processing Time");
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareForm()
    {
        $helper = Mage::helper('zolagoholidays');
        $model = $this->_getModel();
		
    	$form = new Varien_Data_Form(array(
            'id'        => 'processing_time_form',
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post'
        ));
     
        $fieldset = $form->addFieldset('po', array(
            'legend'    => $helper->__('Purchase order (PO)')
        ));
		
		if ($model->getId()) {
            $fieldset->addField('processingtime_id', 'hidden', array(
                'name' => 'processingtime_id',
            ));
        }
		
		$fieldset->addField('type', 'hidden', array(
            'name'      => 'type',
            'value'     => 1
        ));
        
		$fieldset->addField('days', 'text', array(
            'name'      => 'days',
            'label'     => Mage::helper('zolagoholidays')->__('Days required for processing'),
            'title'     => Mage::helper('zolagoholidays')->__('Days required for processing'),
            'required'  => true,
        ));
		
		$fieldset->addField('hour', 'time', array(
          	'name'      => 'hour',
          	'label'     => Mage::helper('zolagoholidays')->__('Before hour'),
            'class'     => 'required-entry',
            'required'  => true,
          	'value'  => '17,00,00',
        ));
		
        $form->setValues($model->getData());
		$this->setForm($form);
    }
    
    protected function _getModel() {
        return Mage::registry('zolagoholidays_current_processingtime');
    }
    
	public function getSaveUrl() {
        return $this->getUrl('*/*/save');
    }
	
}
