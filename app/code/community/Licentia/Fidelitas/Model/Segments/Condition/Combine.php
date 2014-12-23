<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Model_Segments_Condition_Combine extends Mage_Rule_Model_Condition_Combine {

    public function __construct() {
        parent::__construct();
        $this->setType('fidelitas/segments_condition_combine');
    }

    public function getNewChildSelectOptions() {

        $current = Mage::registry('current_segment_rule');

        $customerCondition = Mage::getModel('fidelitas/segments_condition_customer');
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($customerAttributes as $code => $label) {
            $attributes[] = array('value' => 'fidelitas/segments_condition_customer|' . $code, 'label' => $label);
        }

        $conditions = parent::getNewChildSelectOptions();

        $addressCondition = Mage::getModel('fidelitas/segments_condition_address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributesCart = array();
        foreach ($addressAttributes as $code => $label) {
            $attributesCart[] = array('value' => 'fidelitas/segments_condition_address|' . $code, 'label' => $label);
        }

        $addressActivity = Mage::getModel('fidelitas/segments_condition_activity');
        $activityAttributes = $addressActivity->loadAttributeOptions()->getAttributeOption();
        $attributesActivity = array();
        foreach ($activityAttributes as $code => $label) {

            if ($current->getType() != 'customers') {
                if (!in_array($code, array('factivity_abandoned',
                            'factivity_cart_totals',
                            'factivity_cart_number',
                            'factivity_newsletter',
                            'factivity_pending_payment',
                            'factivity_last_order',
                            'factivity_first_order',
                            'factivity_number_orders',
                            'factivity_percentage_complete_orders',
                            'factivity_cart_products',
                        ))) {
                    continue;
                }
            }
            $attributesActivity[] = array('value' => 'fidelitas/segments_condition_activity|' . $code, 'label' => $label);
        }


        if ($current->getType() == 'customers') {
            $productCondition = Mage::getModel('fidelitas/segments_condition_product');
            $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
            $pAttributes = array();
            foreach ($productAttributes as $code => $label) {
                $pAttributes[] = array('value' => 'fidelitas/segments_condition_product|' . $code, 'label' => $label);
            }
        } else {
            $productCondition = Mage::getModel('fidelitas/segments_condition_sku');
            $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
            $pAttributes = array();
            foreach ($productAttributes as $code => $label) {
                $pAttributes[] = array('value' => 'fidelitas/segments_condition_sku|' . $code, 'label' => $label);
            }
        }

        $newsCondition = Mage::getModel('fidelitas/segments_condition_newsletter');
        $newstAttributes = $newsCondition->loadAttributeOptions()->getAttributeOption();
        $nAttributes = array();
        foreach ($newstAttributes as $code => $label) {
            $nAttributes[] = array('value' => 'fidelitas/segments_condition_newsletter|' . $code, 'label' => $label);
        }

        if ($current->getType() == 'customers') {
            $conditions = array_merge_recursive($conditions, array(
                array('label' => Mage::helper('fidelitas')->__('Customer Attribute'), 'value' => $attributes),
            ));
        }

        $conditions = array_merge_recursive($conditions, array(
            array('label' => Mage::helper('fidelitas')->__('Customer Activity'), 'value' => $attributesActivity),
        ));


        $conditions = array_merge_recursive($conditions, array(
            array('label' => Mage::helper('fidelitas')->__('Newsletter Activity'), 'value' => $nAttributes),
            array('label' => Mage::helper('fidelitas')->__('Previous Order - Cart Attribute'), 'value' => $attributesCart),
            array('label' => Mage::helper('fidelitas')->__('Previous Order - Product Attribute'), 'value' => $pAttributes),
        ));


        return $conditions;
    }

    public function collectValidatedAttributes($productCollection) {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

}
