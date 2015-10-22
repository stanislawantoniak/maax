<?php
/**
 * Source for product description status
 */
class Zolago_Catalog_Model_Product_Source_Description
    extends Zolago_Catalog_Model_Product_Source_Abstract {

    const DESCRIPTION_NOT_ACCEPTED = 1;// Nie zatwierdzony
    const DESCRIPTION_WAITING      = 2;// Oczekuje na zatwierdzenie
    const DESCRIPTION_ACCEPTED     = 3;// Zatwierdzony

    public function getAllOptions($withEmpty = false, $defaultValues = false, $noDescription = false) {

        if (!$this->_options || $this->_force) {
            $res = array();
            foreach (self::toOptionHash($withEmpty,$noDescription) as $index => $value) {
                $res[] = array(
                    'value' => $index,
                    'label' => $value
                );
            }
            $this->_options = $res;
        }
        return $this->_options;
    }

    /**
     * @param bool $withEmpty
     * @param bool $noDescription
     * @return array
     */
    public function toOptionHash($withEmpty = false,$noDescription = false) {

        $arr = array();
        if ($withEmpty) {
            $arr[''] = Mage::helper("zolagocatalog")->__("* Please select");
        }
		if($noDescription) {
			$arr[self::DESCRIPTION_NOT_ACCEPTED] = Mage::helper("zolagocatalog")->__("Not accepted");
			$arr[self::DESCRIPTION_WAITING] = Mage::helper("zolagocatalog")->__("Waiting for acceptation by admin");
			$arr[self::DESCRIPTION_ACCEPTED] = Mage::helper("zolagocatalog")->__("Accepted");
		} else {
			$arr[self::DESCRIPTION_NOT_ACCEPTED] = Mage::helper("zolagocatalog")->__("Description not accepted");
			$arr[self::DESCRIPTION_WAITING] = Mage::helper("zolagocatalog")->__("Description waiting for acceptation by admin");
			$arr[self::DESCRIPTION_ACCEPTED] = Mage::helper("zolagocatalog")->__("Description accepted");
		}

        return $arr;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param string $dir
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function addValueSortToCollection($collection, $dir = Varien_Db_Select::SQL_ASC)
    {
        $valueTable1 = $this->getAttribute()->getAttributeCode() . '_t1';
        $valueTable2 = $this->getAttribute()->getAttributeCode() . '_t2';
        $collection->getSelect()
            ->joinLeft(
                array($valueTable1 => $this->getAttribute()->getBackend()->getTable()),
                "e.entity_id={$valueTable1}.entity_id"
                . " AND {$valueTable1}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable1}.store_id=0",
                array())
            ->joinLeft(
                array($valueTable2 => $this->getAttribute()->getBackend()->getTable()),
                "e.entity_id={$valueTable2}.entity_id"
                . " AND {$valueTable2}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                array()
            );


        $valueExpr = $collection->getSelect()->getAdapter()
            ->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");
        /*
        Mage::getResourceModel('eav/entity_attribute_option')
            ->addOptionValueToCollection($collection, $this->getAttribute(), $valueExpr);
        */

        $collection->getSelect()
            ->order("{$this->getAttribute()->getAttributeCode()} {$dir}");

        return $this;
    }
}