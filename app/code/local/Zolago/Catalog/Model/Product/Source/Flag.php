<?php
/** 
 *source for flag options
 */
class Zolago_Catalog_Model_Product_Source_Flag 
        extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array (
                array (
                    'value' => '1',
                    'label' => 'Promotion',
                ),
                array (
                    'value' => '2',
                    'label' => 'Bestseller',
                ),
                array (
                    'value' => '3',
                    'label' => 'New',
                ),
                array (
                    'value' => '4',
                    'label' => 'Sale',
                ),
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
                'type'      => 'varchar(255)',
                'unsigned'  => false,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
        } else {
            $type = Varien_Db_Ddl_Table::TYPE_TEXT ;
            $columns[$attributeCode] = array(
                'type'      => $type,
                'length'    => 255,
                'unsigned'  => false,
                'nullable'   => true,
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
            'fields'    => 'product_flag',
        );
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