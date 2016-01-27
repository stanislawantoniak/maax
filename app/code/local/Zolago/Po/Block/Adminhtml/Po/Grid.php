<?php
class Zolago_Po_Block_Adminhtml_Po_Grid extends Unirgy_DropshipPo_Block_Adminhtml_Po_Grid
{

    protected function _prepareColumns()
    {
        $this->addColumnAfter('default_pos_name', array(
            'header'    => Mage::helper('zolagopos')->__('POS'),
            'index'     => 'default_pos_name',
            'type'      => 'text',
        ), "order_increment_id");

        return parent::_prepareColumns();
    }

    /**
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();

        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();

            if ($columnIndex = "udropship_vendor") {
                $collection->getSelect()->join(
                    array("vendors" => $collection->getTable('udropship/vendor')), //$name
                    "main_table.udropship_vendor=vendors.vendor_id", //$cond
                    array("vendor_name" => "vendor_name")//$cols = '*'
                );
                $columnIndex = "vendor_name";
            }
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }

}
