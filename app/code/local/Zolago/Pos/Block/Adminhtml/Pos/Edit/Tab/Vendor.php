<?php
class Zolago_Pos_Block_Adminhtml_Pos_Edit_Tab_Vendor
    extends Mage_Adminhtml_Block_Widget_Container
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    
    public function __construct() {
        $this->setTemplate('zolagopos/pos/edit/tab/vendor.phtml');
        parent::__construct();
    }
    
    public function canShowTab() {
        return 1;
    }

    public function getTabLabel() {
        return Mage::helper('zolagopos')->__("Vendors");
    }

    public function getTabTitle() {
        return Mage::helper('zolagopos')->__("Assigned Vendors");
    }

    public function isHidden() {
        return false;
    }
    
    public function _prepareLayout() {
        // Grid
        $grid =  $this->getLayout()->createBlock('zolagopos/adminhtml_pos_edit_tab_vendor_grid');
        $this->setChild('zolagopos_pos_vendor_grid', $grid);
        
        // Grid serializer
        $serializer = $this->getLayout()->createBlock("adminhtml/widget_grid_serializer", "zolagopos_pos_edit_vendor_serializer");
        /* @var $serializer Mage_Adminhtml_Block_Widget_Grid_Serializer */
        $serializer->initSerializerBlock($grid, "getCollectionData", "vendor");
        $serializer->addColumnInputName(array("in_pos", "is_owner"));
        
        $this->setChild('zolagopos_pos_vendor_grid_serializer', $serializer);
        parent::_prepareLayout();
    }
}
