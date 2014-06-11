<?php
class Zolago_VendorGlobalSettings_Block_Adminhtml_VendorGlobalSettings_Container extends Mage_Adminhtml_Block_Widget {

    // protected function _prepareLayout() {
        // $this->setChild('back_button',
            // $this->getLayout()->createBlock('adminhtml/widget_button')
                // ->setData(array(
                    // 'label' => Mage::helper('zolagovendorglobalsettings')->__('Back'),
                    // 'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    // 'class' => 'back'
                // ))
        // );
        // $this->setChild('reset_button',
            // $this->getLayout()->createBlock('adminhtml/widget_button')
                // ->setData(array(
                    // 'label' => Mage::helper('zolagovendorglobalsettings')->__('Reset'),
                    // 'onclick' => 'window.location.href = window.location.href'
                // ))
        // );
        // $this->setChild('save_button',
            // $this->getLayout()->createBlock('adminhtml/widget_button')
                // ->setData(array(
                    // 'label' => Mage::helper('zolagovendorglobalsettings')->__('Save'),
                    // 'onclick' => 'posControl.save();',
                    // 'class' => 'save'
                // ))
        // );
// 		
        // return parent::_prepareLayout();
    // }
// 
    // public function getBackButtonHtml() {
        // return $this->getChildHtml('back_button');
    // }
// 
    // public function getResetButtonHtml() {
        // return $this->getChildHtml('reset_button');
    // }
// 
    // public function getSaveButtonHtml() {
        // return $this->getChildHtml('save_button');
    // }
// 
    // public function getIsNew() {
        // return $this->getModel()->getId();
    // }
//     
    // public function getHeaderText() {
        // return  Mage::helper('zolagovendorglobalsettings')->__('Global Settings');
    // }
// 
    // public function getSaveUrl() {
        // return $this->getUrl('*/*/save', array("_current"=>true));
    // }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/delete', array("_current"=>true));
    }

}
