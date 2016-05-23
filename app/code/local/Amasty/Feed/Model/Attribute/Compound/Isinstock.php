<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Isinstock extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        function prepareCollection($collection){
            $collection->joinIsInStock();
        }
        
        function getCompoundData($productData){
            $hlr = Mage::helper("amfeed");
            
            return $productData['is_in_stock'] == 1 ?
                $hlr->__("In Stock") : $hlr->__("Out of Stock");
        }
    }
?>