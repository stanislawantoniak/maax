<?php
/** 
 *source for rating options
 */
class Zolago_Catalog_Model_Product_Source_Rating
        extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array (
                array (
                    'value' => '0',
                    'label' => 'No rating',
                ),
                array (
                    'value' => '1',
                    'label' => '1',
                ),
                array (
                    'value' => '2',
                    'label' => '2',
                ),
                array (
                    'value' => '3',
                    'label' => '3',
                ),
                array (
                    'value' => '4',
                    'label' => '4',
                ),
                array (
                    'value' => '5',
                    'label' => '5',
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
        $attributeCode = 'product_rating';

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
                'nullable'   => true,
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
        $index = 'IDX_PRODUCT_RATING';
        $indexes[$index] = array(
            'type'      => 'index',
            'fields'    => array('product_rating')
        );

        $sortable   = $this->getAttribute()->getUsedForSortBy();
        if ($sortable) {
            $index = 'IDX_PRODUCT_RATING_VALUE';

            $indexes[$index] = array(
                'type'      => 'index',
                'fields'    => array('product_rating_value'),
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