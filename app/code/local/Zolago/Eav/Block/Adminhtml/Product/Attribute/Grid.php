<?php
class Zolago_Eav_Block_Adminhtml_Product_Attribute_Grid extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
		
		$setsOptions = Mage::getModel('zolagoeav/entity_attribute_source_set')->getAllOptions(false);
		foreach ($setsOptions as $option) {
			$setsArray[$option['value']] = $option['label'];
		}
		

        $this->addColumn('set_id', array(
                'header'	=> Mage::helper('zolagoeav')->__('Default Attribute Set'),
                'index'		=> 'set_id',
                'type'		=> 'options',
				'options'	=> $setsArray,
                'width'		=> '100px',
        ));
        return parent::_prepareColumns();
    }
}