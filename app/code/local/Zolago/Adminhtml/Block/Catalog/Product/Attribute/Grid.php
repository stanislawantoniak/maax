<?php
class Zolago_Adminhtml_Block_Catalog_Product_Attribute_Grid extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
		
		$setsOptions = Mage::getModel('zolagoeav/entity_attribute_source_set')->getAllOptions(false);
		foreach ($setsOptions as $option) {
			$setsArray[$option['value']] = $option['label'];
		}
        $visibleOnFrontOptions = array(
            '1' => Mage::helper('catalog')->__('Yes'),
            '0' => Mage::helper('catalog')->__('No'),
        );

        $this->addColumn('set_id', array(
                'header'	=> Mage::helper('zolagoeav')->__('Default Attribute Set'),
                'index'		=> 'set_id',
                'type'		=> 'options',
				'options'	=> $setsArray,
                'width'		=> '100px',
        ));

        $this->addColumn('is_visible_on_front', array(
            'header' => Mage::helper('catalog')->__('Visible on Product Page'),
            'index' => 'is_visible_on_front',
            'type' => 'options',
            'options' => $visibleOnFrontOptions,
            'align' => 'center',
            'width' => '80px',
        ));
        $gridPermissions = Mage::getModel('zolagoeav/entity_attribute_source_gridPermission')->getAllOptions(false);

        $gridPermissionArray = array();
        foreach ($gridPermissions as $_) {
            $gridPermissionArray[$_['value']] = $_['label'];
        }
        unset($_);
        $this->addColumn('grid_permission', array(
            'header' => Mage::helper('catalog')->__('Used in mass grid'),
            'index' => 'grid_permission',
            'type' => 'options',
            'options' => $gridPermissionArray,
            'align' => 'center'
        ));


        $this->addColumn('column_attribute_order', array(
            'header'	=> Mage::helper('zolagoeav')->__('Attribute order'),
            'index'		=> 'column_attribute_order'
        ));
        return parent::_prepareColumns();
    }
}