<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _prepareLayout()
    {
        $this->setChild('generate_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Generate today'),
                    'onclick' => "statementsControl.generateToday();",
                    'class' => 'generate_button'
                ))
        );

        return parent::_prepareLayout();
    }

    public function getGenerateTodayUrl() {
        return $this->getUrl('*/*/generate_today');
    }

    public function getTodayDate() {
        return Mage::getModel('core/date')->date('Y-m-d');
    }
}