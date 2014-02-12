<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipTierShipping
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipTierShipping_Model_ProductAttributeBackend_Rates extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        try {
            $decoded = $object->getData($attrCode);
            if (!is_array($decoded)) {
                $decoded = Mage::helper('udropship')->unserialize($decoded);
            }
            if (is_array($decoded)) {
                usort($decoded, array($this, 'sortBySortOrder'));
            } else {
                $decoded = array();
            }
            $object->setData($attrCode, $decoded);
        } catch (Exception $e) {}

    }

    public function sortBySortOrder($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        return 0;
    }

    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if (($attrValue = $object->getData($attrCode)) && is_array($attrValue)) {
            unset($attrValue['$ROW']);
            unset($attrValue['$$ROW']);
            usort($attrValue, array($this, 'sortBySortOrder'));
            $object->setData($attrCode, Mage::helper('udropship')->serialize($attrValue));
        }
    }
}