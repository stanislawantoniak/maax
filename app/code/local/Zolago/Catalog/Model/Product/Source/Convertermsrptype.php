<?php
/** 
 *source for flag options
 */
class Zolago_Catalog_Model_Product_Source_Convertermsrptype
        extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

	const FLAG_AUTO = 0;
	const FLAG_MANUAL = 1;
	
    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array (
                array (
                    'value' => self::FLAG_AUTO,
                    'label' => Mage::helper('zolagocatalog')->__('From file'),
                ),
                array (
                    'value' => self::FLAG_MANUAL,
                    'label' => Mage::helper('zolagocatalog')->__('Manual'),
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
        $attributeCode = 'converter_price_type';

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
            'fields'    => array('converter_price_type')
        );

        $sortable   = $this->getAttribute()->getUsedForSortBy();
        if ($sortable) {
            $index = 'IDX_PRODUCT_FLAG_VALUE';

            $indexes[$index] = array(
                'type'      => 'index',
                'fields'    => array('converter_price_type_value'),
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