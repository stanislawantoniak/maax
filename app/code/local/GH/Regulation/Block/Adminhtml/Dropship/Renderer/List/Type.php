<?php
/**
 * renderer for assigned list types
 */
class GH_Regulation_Block_Adminhtml_Dropship_Renderer_List_Type extends 
    Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface {
     
        public function __construct()
        {
            $this->setTemplate('ghregulation/dropship/renderer/list/type.phtml');
            parent::__construct();
        }
    /**
     * @param Varien_Data_Form_Element_Abstract $elem
     *
     * @return html
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
    
    /**
     * list of specific document types
     */

    public function getList() {
        $vendor = $this->getData('vendor_id');
        $kind  = $this->getData('regulation_kind_id');
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('ghregulation/regulation_type');
        $collection = Mage::getResourceModel('ghregulation/regulation_document_vendor_collection');
        $collection->getSelect()
            ->join(array('regulation_type' => $table),
                'main_table.regulation_type_id = regulation_type.regulation_type_id',
                array('regulation_type.name')
            )
            ->where('regulation_type.regulation_kind_id=?',$kind)
            ->where('main_table.vendor_id=?',$vendor)
            ->order('main_table.date','DESC');
        return $collection;
        
    }
    
    /**
     * prepare remove link
     */
     protected function _getDeleteUrl($id) {
         $url = $this->getUrl('*/*/typeDelete',array('vendor_id' => $this->getData('vendor_id'),'document_vendor_id' => $id));
         return $url;
     }
                            
}