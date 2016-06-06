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
 * Product Carousel admin widget chooser
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Widget_Chooser extends Mage_Adminhtml_Block_Widget_Grid{
	/**
	 * Block construction, prepare grid params
	 * @access public
	 * @param array $arguments Object data
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct($arguments=array()){
		parent::__construct($arguments);
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
		$this->setDefaultFilter(array('chooser_status' => '1'));
	}
	/**
	 * Prepare chooser element HTML
	 * @access public
	 * @param Varien_Data_Form_Element_Abstract $element Form Element
	 * @return Varien_Data_Form_Element_Abstract
	 * @author MagPassion.com
	 */
	public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element){
		$uniqId = Mage::helper('core')->uniqHash($element->getId());
		$sourceUrl = $this->getUrl('productcarousel/adminhtml_productcarousel_productcarousel_widget/chooser', array('uniq_id' => $uniqId));
		$chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
				->setElement($element)
				->setTranslationHelper($this->getTranslationHelper())
				->setConfig($this->getConfig())
				->setFieldsetId($this->getFieldsetId())
				->setSourceUrl($sourceUrl)
				->setUniqId($uniqId);
		if ($element->getValue()) {
			$productcarousel = Mage::getModel('productcarousel/productcarousel')->load($element->getValue());
			if ($productcarousel->getId()) {
				$chooser->setLabel($productcarousel->getBlocktitle());
			}
		}
		$element->setData('after_element_html', $chooser->toHtml());
		return $element;
	}
	/**
	 * Grid Row JS Callback
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getRowClickCallback(){
		$chooserJsObject = $this->getId();
		$js = '
			function (grid, event) {
				var trElement = Event.findElement(event, "tr");
				var productcarouselId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
				var productcarouselTitle = trElement.down("td").next().innerHTML;
				'.$chooserJsObject.'.setElementValue(productcarouselId);
				'.$chooserJsObject.'.setElementLabel(productcarouselTitle);
				'.$chooserJsObject.'.close();
			}
		';
		return $js;
	}
	/**
	 * Prepare a static blocks collection
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Widget_Chooser
	 * @author MagPassion.com
	 */
	protected function _prepareCollection(){
		$collection = Mage::getModel('productcarousel/productcarousel')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	/**
	 * Prepare columns for the a grid
	 * @access protected 
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Widget_Chooser
	 * @author MagPassion.com
	 */
	protected function _prepareColumns(){
		$this->addColumn('chooser_id', array(
			'header'	=> Mage::helper('productcarousel')->__('Id'),
			'align' 	=> 'right',
			'index' 	=> 'entity_id',
			'type'		=> 'number',
			'width' 	=> 50
		));
		
		$this->addColumn('chooser_blocktitle', array(
			'header'=> Mage::helper('productcarousel')->__('Block Title'),
			'align' => 'left',
			'index' => 'blocktitle',
		));
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'=> Mage::helper('productcarousel')->__('Store Views'),
				'index' => 'store_id',
				'type'  => 'store',
				'store_all' => true,
				'store_view'=> true,
				'sortable'  => false,
			));
		}
		$this->addColumn('chooser_status', array(
			'header'=> Mage::helper('productcarousel')->__('Status'),
			'index' => 'status',
			'type'  => 'options',
			'options'   => array(
				0 => Mage::helper('productcarousel')->__('Disabled'),
				1 => Mage::helper('productcarousel')->__('Enabled')
			),
		));
		return parent::_prepareColumns();
	}
	/**
	 * get url for grid
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getGridUrl(){
		return $this->getUrl('adminhtml/productcarousel_productcarousel_widget/chooser', array('_current' => true));
	}
	/**
	 * after collection load
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Widget_Chooser
	 * @author MagPassion.com
	 */
	protected function _afterLoadCollection(){
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}
}