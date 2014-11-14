<?php
class Zolago_Sizetable_Block_Adminhtml_Dropship_Edit_Tab_Settings 
	extends Mage_Adminhtml_Block_Widget {
	
	public function __construct(){
		$this->setTemplate('zolagosizetable/widget/settings.phtml');
		parent::_construct();
		
	}
	protected function _prepareLayout()
    {
        $helper = Mage::helper('zolagosizetable');
        $this->setChild('sizetable_vendor_brand',
                $this->getLayout()->createBlock('zolagosizetable/adminhtml_dropship_settings_grid_brand')
                        ->setData(array(
                            'label'     => Mage::helper('zolagosizetable')->__('Available vendor brands'),                            
                        ))
                );
        /*
        $this->setForm($form);

        $fieldset = $form->addFieldset('sizetable_vendor_brand', array(
            'legend'=>$helper->__('Available vendor brands')
        ));

        $fieldset->addType('sizetable_vendor_brand', 'Zolago_Sizetable_Block_Adminhtml_Dropship_Settings_Grid_Brand');
        
        $fieldset = $form->addFieldset('sizetable_vendor_attributeset', array(
            'legend'=>$helper->__('Available vendor attribute sets')
        ));

        $fieldset->addType('sizetable_vendor_attributeset', 'Zolago_Sizetable_Block_Adminhtml_Dropship_Settings_Grid_Attributeset');
        */        
        return parent::_prepareLayout();
    }
    protected function _getBrandTable() {
        $block = $this->getChild('sizetable_vendor_brand');
        $block->setVendorId($this->getVendorId());
        return $block;
    }
	
}
