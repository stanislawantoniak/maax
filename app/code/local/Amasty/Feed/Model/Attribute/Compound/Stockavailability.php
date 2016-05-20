<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Stockavailability extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        protected $_values = array(
            0 => "No",
            1 => "Yes"
        );
        
        function prepareCollection($collection){
            $collection->joinIsInStock();
        }
        
        function getCompoundData($productData){
            $hlr = Mage::helper("amfeed");
            
            return isset($this->_values[$productData['is_in_stock']]) ? 
                $this->_values[$productData['is_in_stock']] : 
                NULL;
                
        }
        
        function hasFilterCondition(){
            return true;
        }
        
        function validateFilterCondition($productData, $operator, $valueCode){
            return Amasty_Feed_Model_Field_Condition::compare($operator, $this->getCompoundData($productData), $valueCode);
        }
        
        function hasCondition(){
            return true;
        }
        
        function prepareCondition($collection, $operator, $condVal, &$attributesFields){
            $collection->joinIsInStock();
                
            $attributesFields[] = array(
                'attribute' => 'is_in_stock', 
                $operator => isset($this->_values[$condVal]) ? 
                    $this->_values[$condVal] : 
                    NULL
            );
        }
    }
?>