<?php
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Product Carousel - product relation edit block
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid{
	/**
	 * Set grid params
	 * @access protected
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct(){
		parent::__construct();
		$this->setId('product_grid');
		$this->setDefaultSort('position');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
		if ($this->getProductcarousel()->getId()) {
			$this->setDefaultFilter(array('in_products'=>1));
		}
	}
	/**
	 * prepare the product collection
	 * @access protected 
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Product
	 * @author MagPassion.com
	 */
	protected function _prepareCollection() {
		$collection = Mage::getResourceModel('catalog/product_collection');
		$collection->addAttributeToSelect('price');
		$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
		$collection->joinAttribute('product_name', 'catalog_product/name', 'entity_id', null, 'left', $adminStore);
		if ($this->getProductcarousel()->getId()){
			$constraint = '{{table}}.productcarousel_id='.$this->getProductcarousel()->getId();
		}
		else{
			$constraint = '{{table}}.productcarousel_id=0';
		}
		$collection->joinField('position',
			'productcarousel/productcarousel_product',
			'position',
			'product_id=entity_id',
			$constraint,
			'left');
		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}
	/**
	 * prepare mass action grid
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Product
	 * @author MagPassion.com
	 */ 
	protected function _prepareMassaction(){
		return $this;
	}
	/**
	 * prepare the grid columns
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Product
	 * @author MagPassion.com
	 */
	protected function _prepareColumns(){
		$this->addColumn('in_products', array(
			'header_css_class'  => 'a-center',
			'type'  => 'checkbox',
			'name'  => 'in_products',
			'values'=> $this->_getSelectedProducts(),
			'align' => 'center',
			'index' => 'entity_id'
		));
		$this->addColumn('product_name', array(
			'header'=> Mage::helper('catalog')->__('Name'),
			'align' => 'left',
			'index' => 'product_name',
		));
		$this->addColumn('sku', array(
			'header'=> Mage::helper('catalog')->__('SKU'),
			'align' => 'left',
			'index' => 'sku',
		));
		$this->addColumn('price', array(
			'header'=> Mage::helper('catalog')->__('Price'),
			'type'  => 'currency',
			'width' => '1',
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
			'index' => 'price'
		));
		$this->addColumn('position', array(
			'header'=> Mage::helper('catalog')->__('Position'),
			'name'  => 'position',
			'width' => 60,
			'type'  => 'number',
			'validate_class'=> 'validate-number',
			'index' => 'position',
			'editable'  => true,
		));
	}
	/**
	 * Retrieve selected products
	 * @access protected
	 * @return array
	 * @author MagPassion.com
	 */
	protected function _getSelectedProducts(){
		$products = $this->getProductcarouselProducts();
		if (!is_array($products)) {
			$products = array_keys($this->getSelectedProducts());
		}
		return $products;
	}
 	/**
	 * Retrieve selected products
	 * @access protected
	 * @return array
	 * @author MagPassion.com
	 */
	public function getSelectedProducts() {
		$products = array();
		$selected = Mage::registry('current_productcarousel')->getSelectedProducts();
		if (!is_array($selected)){
			$selected = array();
		}
		foreach ($selected as $product) {
			$products[$product->getId()] = array('position' => $product->getPosition());
		}
		return $products;
	}
	/**
	 * get row url
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getRowUrl($item){
		return '#';
	}
	/**
	 * get grid url
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getGridUrl(){
		return $this->getUrl('*/*/productsGrid', array(
			'id'=>$this->getProductcarousel()->getId()
		));
	}
	/**
	 * get the current productcarousel
	 * @access public
	 * @return MagPassion_Productcarousel_Model_Productcarousel
	 * @author MagPassion.com
	 */
	public function getProductcarousel(){
		return Mage::registry('current_productcarousel');
	}
	/**
	 * Add filter
	 * @access protected
	 * @param object $column
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Product
	 * @author MagPassion.com
	 */
	protected function _addColumnFilterToCollection($column){
		// Set custom filter for in product flag
		if ($column->getId() == 'in_products') {
			$productIds = $this->_getSelectedProducts();
			if (empty($productIds)) {
				$productIds = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
			} 
			else {
				if($productIds) {
					$this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
				}
			}
		} 
		else {
			parent::_addColumnFilterToCollection($column);
		}
		return $this;
	}
}