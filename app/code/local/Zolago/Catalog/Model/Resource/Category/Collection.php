<?php

/**
 * Class Zolago_Catalog_Model_Resource_Category_Collection
 */
class Zolago_Catalog_Model_Resource_Category_Collection extends Mage_Catalog_Model_Resource_Category_Collection {


    /**
     * Join filters
     * use_flag_filter, use_price_filter, use_review_filter
     * @return $this
     */
    public function joinCategoryFilters()
    {
        $categoryEntityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_category')->getId();

        $useFlagFilterId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_category', 'use_flag_filter');

        $usePriceFilterId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_category', 'use_price_filter');

        $useReviewFilterId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_category', 'use_review_filter');

        $this->getSelect()->join(
            array("at_use_flag_filter" => $this->getTable("catalog_category_entity_int")),
            "at_use_flag_filter.entity_id=e.entity_id",
            array("use_flag_filter" => "at_use_flag_filter.value")
        );
        $this->getSelect()->join(
            array("at_use_price_filter" => $this->getTable("catalog_category_entity_int")),
            "at_use_price_filter.entity_id=e.entity_id",
            array("use_price_filter" => "at_use_price_filter.value")
        );
        $this->getSelect()->join(
            array("at_use_review_filter" => $this->getTable("catalog_category_entity_int")),
            "at_use_review_filter.entity_id=e.entity_id",
            array("use_review_filter" => "at_use_review_filter.value")
        );
        $this->getSelect()->where("at_use_flag_filter.attribute_id=?", $useFlagFilterId);
        $this->getSelect()->where("at_use_price_filter.attribute_id=?", $usePriceFilterId);
        $this->getSelect()->where("at_use_review_filter.attribute_id=?", $useReviewFilterId);

        $this->getSelect()->where("at_use_flag_filter.entity_type_id=?", $categoryEntityTypeId);
        $this->getSelect()->where("at_use_price_filter.entity_type_id=?", $categoryEntityTypeId);
        $this->getSelect()->where("at_use_review_filter.entity_type_id=?", $categoryEntityTypeId);

        return $this;
    }

}