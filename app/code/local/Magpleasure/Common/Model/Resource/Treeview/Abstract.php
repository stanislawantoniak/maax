<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Abstract Resource Model
 */
class Magpleasure_Common_Model_Resource_Treeview_Abstract extends Magpleasure_Common_Model_Resource_Abstract
{
    protected $_parentIdField;
    protected $_positionField;

    public function initTree($parentIdField, $positionField)
    {
        $this->_parentIdField = $parentIdField;
        $this->_positionField = $positionField;

        return $this;
    }

    public function getParentIdField()
    {
        return $this->_parentIdField;
    }

    public function getPositionField()
    {
        return $this->_positionField;
    }

    /**
     * Has Children
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function hasChildren(Mage_Core_Model_Abstract $object)
    {
        return !!$this->getChildren($object)->getSize();
    }

    public function getChildren(Mage_Core_Model_Abstract $object)
    {
        /** @var Magpleasure_Common_Model_Resource_Treeview_Collection_Abstract $collection */
        $collection = $object->getCollection();

        $collection
            ->addFieldToFilter($this->getParentIdField(), $object->getData($this->getParentIdField()))
            ->setOrder($this->getPositionField(), 'ASC');
            ;

        return $collection;
    }
}