<?php
class Zolago_Pos_Block_Adminhtml_Pos_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('zolagopos_info_tab');
        $this->setDestElementId('template_edit_form');
        $this->setTitle(Mage::helper('zolagopos')->__('POS Information'));
    }

}
