<?php
class Zolago_Mapper_Block_Adminhtml_Mapper extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('zolagomapper')->__('Create mapper'),
            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
            'class'   => 'add'
        ));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function prepareJsCustomIds() {
        // Shame on me
        /* @var $collection Zolago_Mapper_Model_Resource_Mapper_Collection */
        $collection = Mage::getResourceModel('zolagomapper/mapper_collection');
        /* @var $collection Zolago_Mapper_Model_Resource_Mapper_Collection */
        $collection->setFlag('abstract', true);
        $collection->joinAttributeSet();
        $collection->addCustomId();

        $list = '';
        foreach ($collection as $row) {
            $customId = $row->getCustomId();
            $list .= $customId.',';
        }
        $list = rtrim($list, ',');
        $list = "zolagomapper_mapper_grid_massactionJsObject.setGridIds('" .$list."');";
        return $list;
    }
}