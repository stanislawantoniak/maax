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
 * Product Carousel tab on product edit form
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Catalog_Product_Edit_Tab_Productcarousel extends Mage_Adminhtml_Block_Widget_Grid{
	/**
	 * Set grid params
	 * @access protected
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct(){
		parent::__construct();
		$this->setId('productcarousel_grid');
		$this->setDefaultSort('position');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
		if ($this->getProduct()->getId()) {
			$this->setDefaultFilter(array('in_productcarousels'=>1));
		}
	}
	/**
	 * prepare the productcarousel collection
	 * @access protected 
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Catalog_Product_Edit_Tab_Productcarousel
	 * @author MagPassion.com
	 */
	protected function _prepareCollection() {
		$collection = Mage::getResourceModel('productcarousel/productcarousel_collection');
		if ($this->getProduct()->getId()){
			$constraint = 'related.product_id='.$this->getProduct()->getId();
			}
			else{
				$constraint = 'related.product_id=0';
			}
		$collection->getSelect()->joinLeft(
			array('related'=>$collection->getTable('productcarousel/productcarousel_product')),
			'related.productcarousel_id=main_table.entity_id AND '.$constraint,
			array('position')
		);
		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}
	/**
	 * prepare mass action grid
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Catalog_Product_Edit_Tab_Productcarousel
	 * @author MagPassion.com
	 */ 
	protected function _prepareMassaction(){
		return $this;
	}
	/**
	 * prepare the grid columns
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Catalog_Product_Edit_Tab_Productcarousel
	 * @author MagPassion.com
	 */
	protected function _prepareColumns(){
		$this->addColumn('in_productcarousels', array(
			'header_css_class'  => 'a-center',
			'type'  => 'checkbox',
			'name'  => 'in_productcarousels',
			'values'=> $this->_getSelectedProductcarousels(),
			'align' => 'center',
			'index' => 'entity_id'
		));
		$this->addColumn('blocktitle', array(
			'header'=> Mage::helper('productcarousel')->__('Block Title'),
			'align' => 'left',
			'index' => 'blocktitle',
		));
		$this->addColumn('position', array(
			'header'		=> Mage::helper('productcarousel')->__('Position'),
			'name'  		=> 'position',
			'width' 		=> 60,
			'type'		=> 'number',
			'validate_class'=> 'validate-number',
			'index' 		=> 'position',
			'editable'  	=> true,
		));
	}
	/**
	 * Retrieve selected productcarousels
	 * @access protected
	 * @return array
	 * @author MagPassion.com
	 */
	protected function _getSelectedProductcarousels(){
		$productcarousels = $this->getProductProductcarousels();
		if (!is_array($productcarousels)) {
			$productcarousels = array_keys($this->getSelectedProductcarousels());
		}
		return $productcarousels;
	}
 	/**
	 * Retrieve selected productcarousels
	 * @access protected
	 * @return array
	 * @author MagPassion.com
	 */
	public function getSelectedProductcarousels() {
		$productcarousels = array();
		//used helper here in order not to override the product model
		$selected = Mage::helper('productcarousel/product')->getSelectedProductcarousels(Mage::registry('current_product'));
		if (!is_array($selected)){
			$selected = array();
		}
		foreach ($selected as $productcarousel) {
			$productcarousels[$productcarousel->getId()] = array('position' => $productcarousel->getPosition());
		}
		return $productcarousels;
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
		return $this->getUrl('*/*/productcarouselsGrid', array(
			'id'=>$this->getProduct()->getId()
		));
	}
	/**
	 * get the current product
	 * @access public
	 * @return Mage_Catalog_Model_Product
	 * @author MagPassion.com
	 */
	public function getProduct(){
		return Mage::registry('current_product');
	}
	/**
	 * Add filter
	 * @access protected
	 * @param object $column
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Catalog_Product_Edit_Tab_Productcarousel
	 * @author MagPassion.com
	 */
	protected function _addColumnFilterToCollection($column){
		if ($column->getId() == 'in_productcarousels') {
			$productcarouselIds = $this->_getSelectedProductcarousels();
			if (empty($productcarouselIds)) {
				$productcarouselIds = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productcarouselIds));
			} 
			else {
				if($productcarouselIds) {
					$this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productcarouselIds));
				}
			}
		} 
		else {
			parent::_addColumnFilterToCollection($column);
		}
		return $this;
	}
}