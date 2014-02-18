<?php
class Zolago_Pos_Block_Adminhtml_Pos_Edit extends Mage_Adminhtml_Block_Widget {

    /**
     *  @return Zolago_Pos_Model_Pos
     */
    public function getModel() {
        return Mage::registry('zolagopos_current_pos');
    }

    protected function _prepareLayout() {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('zolagopos')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    'class' => 'back'
                ))
        );
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('zolagopos')->__('Reset'),
                    'onclick' => 'window.location.href = window.location.href'
                ))
        );
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('zolagopos')->__('Save'),
                    'onclick' => 'posControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('zolagopos')->__('Delete'),
                    'onclick' => 'posControl.remove();',
                    'class' => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }

    public function getResetButtonHtml() {
        return $this->getChildHtml('reset_button');
    }

    public function getSaveButtonHtml() {
        return $this->getChildHtml('save_button');
    }

    public function getDeleteButtonHtml() {
        return $this->getChildHtml('delete_button');
    }

    public function getIsNew() {
        return $this->getModel()->getId();
    }
    
    public function getHeaderText() {
        if ($this->getIsNew()) {
            return Mage::helper('zolagopos')->__('Edit POS');
        }
        return  Mage::helper('zolagopos')->__('New POS');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save', array("_current"=>true));
    }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/delete', array("_current"=>true));
    }

}
