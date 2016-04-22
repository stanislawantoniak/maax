<?php
class Zolago_Dropship_Helper_Catalog extends ZolagoOs_OmniChannelVendorProduct_Helper_Udcatalog
{
	const ROOT_CATEGORY_LEVEL = 0;
	
    public function getCategoryValuesExtended($hash=false, $selector=true)
    {
        $values = array();
        if ($selector) {
            if ($hash) {
                $values[''] = Mage::helper('udropship')->__('* Select category');
            } else {
                $values[] = array('label'=>Mage::helper('udropship')->__('* Select category'), 'value'=>'');
            }
        }
		$categories = Mage::getResourceModel('catalog/category_collection')
				->addLevelFilter(self::ROOT_CATEGORY_LEVEL);
		
		foreach ($categories as $category) {
			$this->_attachCategoryValuesExtended($category, $values, 0, $hash);
		}
        return $values;
    }

    protected function _attachCategoryValuesExtended($cat, &$values, $level, $hash=false)
    {
        $children = $cat->getChildrenCategories();
        if (count($children)>0) {
            if ($hash) {
                $values[$cat->getId()] = $cat->getName();
            } elseif ($level !== self::ROOT_CATEGORY_LEVEL) {
                $values[] = array('label'=>$cat->getName(), 'value'=>$cat->getId(), 'level'=>$level, 'disabled'=>true);
            }
            $level+=1;
            foreach ($children as $child) {
                $this->_attachCategoryValuesExtended($child, $values, $level, $hash);
            }
        } else {
            if ($hash) {
                $values[$cat->getId()] = $cat->getName();
            } else {
                $values[] = array('label'=>$cat->getName(), 'value'=>$cat->getId(), 'level'=>$level);
            }
        }
        return $this;
    }	
}