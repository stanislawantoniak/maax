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
 * Product Carousel edit form tab
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form{	
	
    /* get list categories */
    protected function getListCategories($catid, $level) {
		$prefix = '';
        $strCat = '';
		for ($i = 0; $i < $level; $i++) $prefix .= '---';
		$categories=Mage::getModel('catalog/category')->load($catid)->getChildrenCategories();
		foreach($categories as $cat){ 
			$category=Mage::getModel('catalog/category')->load($cat->entity_id);
			$strCat .= $category->getId().':::'.$prefix.$category->getName().'@@@';			
			$strCat .= $this->getListCategories($category->getId(), $level+1);
		}
		return $strCat;
	}
    
    /**
	 * prepare the form
	 * @access protected
	 * @return Productcarousel_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Form
	 * @author MagPassion.com
	 */
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('productcarousel_');
		$form->setFieldNameSuffix('productcarousel');
		$this->setForm($form);
		$fieldset = $form->addFieldset('productcarousel_form', array('legend'=>Mage::helper('productcarousel')->__('Basic Options')));

		$fieldset->addField('blocktitle', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Block Title'),
			'name'  => 'blocktitle',
			'required'  => true,
			'class' => 'required-entry',

		));

		$fieldset->addField('type', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Type'),
			'name'  => 'type',
			'note'	=> $this->__('Choose type of product collection. If you choose "Custom products", go to "Associated products" tab for manual select product collection.'),
			'required'  => true,
			'class' => 'required-entry',

			'values'=> array(
				array(
					'value' => 'new',
					'label' => Mage::helper('productcarousel')->__('New products'),
				),
				array(
					'value' => 'mostview',
					'label' => Mage::helper('productcarousel')->__('Most view products'),
				),
                array(
					'value' => 'custom',
					'label' => Mage::helper('productcarousel')->__('Custom products'),
				),
			),
            'value'=>'new',
		));
        
        $cateoptions = array();
		$parentid = Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId();
		$strCategories = '0:::All Category@@@'.$this->getListCategories($parentid, 1);
		$arrCate = explode("@@@", $strCategories);
		foreach ($arrCate as $c) 
			if ($c) {
				$tmp = explode(":::", $c);
				$cateoptions[] = array(
					'label' => $tmp[1],
					'value' => $tmp[0],
				);
			}
		
		//print_r($cateoptions);
		
		$fieldset->addField('category_id', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Category'),
			'name'  => 'category_id',
			'values'=> $cateoptions,
            'note'  => $this->__('Only used when choose new products or most view products.'),
			'onchange' => "changeCategory()",
		));
        
        $fieldset->addField('category', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Category'),
			'name'  => 'category',
            'class' => 'hidden',
		));
        
        
        
        
		

		$fieldset->addField('numberproduct', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Number of products to get'),
			'name'  => 'numberproduct',
			'required'  => true,
			'class' => 'required-entry validate-number',

		));
        
       
        
        /*$fieldset->addField('numberproductscroll', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Number of products scroll'),
			'name'  => 'numberproductscroll',
			'required'  => false,
			'class' => 'validate-number',
            'note' => $this->__('Number of products will be scrolled at a time. Set blank to scroll 1 item.'),
            'value' => '1',
		));
        */
        
       
        
		
       
        
       
         
        
		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Status'),
			'name'  => 'status',
			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Enabled'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('Disabled'),
				),
			),
            'value'=>'1',
		));
		if (Mage::app()->isSingleStoreMode()){
			$fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            Mage::registry('current_productcarousel')->setStoreId(Mage::app()->getStore(true)->getId());
		}
		if (Mage::getSingleton('adminhtml/session')->getProductcarouselData()){
			$form->setValues(Mage::getSingleton('adminhtml/session')->getProductcarouselData());
			Mage::getSingleton('adminhtml/session')->setProductcarouselData(null);
		}
		elseif (Mage::registry('current_productcarousel')){
			$form->setValues(Mage::registry('current_productcarousel')->getData());
		}
        
		return parent::_prepareForm();
	}
}