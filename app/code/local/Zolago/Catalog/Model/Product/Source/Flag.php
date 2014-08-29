<?php
/** 
 *source for flag options
 */
class Zolago_Catalog_Model_Product_Source_Flag 
        extends Zolago_Catalog_Model_Product_Source_Abstract {

	const FLAG_PROMOTION = 1;
	const FLAG_SALE = 2;
	
	
//	public function getOptionText($value)
//    {
//        $isMultiple = false;
//        if (strpos($value, ',')) {
//            $isMultiple = true;
//            $value = explode(',', $value);
//        }
//
//        $options = $this->getAllOptions(false);
//
//        if ($isMultiple) {
//            $values = array();
//            foreach ($options as $item) {
//                if (in_array($item['value'], $value)) {
//                    $values[] = $item['label'];
//                }
//            }
//            return $values;
//        }
//
//        foreach ($options as $item) {
//            if ($item['value'] == $value) {
//                return $item['label'];
//            }
//        }
//        return false;
//    }
	
	
	
    public function getAllOptions() {
        if (!$this->_options || $this->_force) {
            $this->_options = array (
                array (
                    'value' => '',
                    'label' => '',
                ),				
                array (
                    'value' => self::FLAG_PROMOTION,
                    'label' => Mage::helper('zolagocatalog')->__('Promotion'),
                ),
                array (
                    'value' => self::FLAG_SALE,
                    'label' => Mage::helper('zolagocatalog')->__('Sale'),
                )
            );
        }
        return $this->_options;
    }
    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $attributeCode = 'product_flag';

        if (Mage::helper('core')->useDbCompatibleMode()) {
            $columns[$attributeCode] = array(
                'type'      =>  'int',
                'unsigned'  => false,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
                $columns[$attributeCode . '_value'] = array(
                    'type'      => 'varchar(255)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
        } else {
            $type =  Varien_Db_Ddl_Table::TYPE_INTEGER;
            $columns[$attributeCode] = array(
                'type'      => $type,
                'length'    => null,
                'unsigned'  => false,
                'nullable'	=> true,
                'default'   => null,
                'extra'     => null,
                'comment'   => $attributeCode . ' column'
            );
                $columns[$attributeCode . '_value'] = array(
                    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length'    => 255,
                    'unsigned'  => false,
                    'nullable'  => true,
                    'default'   => null,
                    'extra'     => null,
                    'comment'   => $attributeCode . ' column'
                );
        }
        return $columns;
    }

    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();
        $index = 'IDX_PRODUCT_FLAG';
        $indexes[$index] = array(
            'type'      => 'index',
            'fields'    => array('product_flag')
        );

        $sortable   = $this->getAttribute()->getUsedForSortBy();
        if ($sortable) {
            $index = 'IDX_PRODUCT_FLAG_VALUE';

            $indexes[$index] = array(
                'type'      => 'index',
                'fields'    => array('product_flag_value'),
            );
        }

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute_option')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}