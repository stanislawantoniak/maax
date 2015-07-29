<?php

class GH_Statements_Block_Adminhtml_Calendar_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{


    public function canShowTab()
    {
        return 1;
    }

    public function getTabLabel()
    {
        return Mage::helper('ghstatements')->__("General");
    }

    public function getTabTitle()
    {
        return Mage::helper('ghstatements')->__("General Calendar Information");
    }
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $helper = Mage::helper('ghstatements');
        $form = new Varien_Data_Form();

        $calendar = $form->addFieldset('calendar', array('legend' => $helper->__('Calendar Settings')));

        $builder = Mage::getModel('ghstatements/form_fieldset_calendar');
        $builder->setFieldset($calendar);
        $builder->prepareForm(
            array(
                'name',
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
        return Mage::registry('ghstatements_current_calendar');
    }

}
