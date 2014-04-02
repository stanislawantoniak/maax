<?php

class Zolago_Dropship_Block_Adminhtml_SystemConfigFormField_CategoriesSelect extends Unirgy_Dropship_Block_Adminhtml_SystemConfigFormField_CategoriesSelect
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $value = $element->getValue();
        $cHlp = Mage::helper('udropship/catalog');
        $cOpts = $cHlp->getCategoryValuesExtended();
        if (!$value && $cHlp->getStoreRootCategory()) {
            $value = $cHlp->getStoreRootCategory()->getId();
        }
        $_form = new Varien_Data_Form();
        $_form->addType('categories_select', Mage::getConfig()->getBlockClassName('udropship/categoriesSelect'));
        $catBlock = $_form->addField($element->getId(), 'categories_select', array(
            'name'=>$element->getName(),
            'label'=>Mage::helper('udropship')->__('Select Category'),
            'value'=>$value,
            'values'=>$cOpts,
            'skip_disabled'=>1
        ));
        $html = $catBlock->getElementHtml();
        return $html;
    }

}