<?php

class GH_Dhl_Block_Adminhtml_Dhl_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{


    public function canShowTab()
    {
        return 1;
    }

    public function getTabLabel()
    {
        return Mage::helper('ghdhl')->__("General");
    }

    public function getTabTitle()
    {
        return Mage::helper('ghdhl')->__("General DHL Account Information");
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $helper = Mage::helper('ghdhl');
        $form = new Varien_Data_Form();

        $dhl = $form->addFieldset('dhl', array('legend' => $helper->__('DHL Settings')));

        $builder = Mage::getModel('ghdhl/form_fieldset_dhl');
        $builder->setFieldset($dhl);
        $builder->prepareForm(
            array(
                'dhl_account',
                'dhl_login',
                'dhl_password',
                'comment',
            ));

        $form->setValues($this->_getValues());
        $this->setForm($form);
    }

    protected function _getValues()
    {
        return $this->_getModel()->getData();
    }

    /**
     * @return Zolago_Pos_Model_Pos
     */
    protected function _getModel()
    {
        return Mage::registry('ghdhl_current_dhl');
    }

}
