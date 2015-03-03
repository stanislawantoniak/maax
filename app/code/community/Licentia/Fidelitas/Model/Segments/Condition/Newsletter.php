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
class Licentia_Fidelitas_Model_Segments_Condition_Newsletter extends Mage_Rule_Model_Condition_Abstract {

    public function loadAttributeOptions() {
        $attributes = array(
            'factivity_percentage_clicks_newsletter' => Mage::helper('fidelitas')->__('Percentage of Clicks Vs Opens'),
            'factivity_percentage_sent_newsletter' => Mage::helper('fidelitas')->__('Percentage of Clicks Vs Sent'),
            'factivity_percentage_opens_newsletter' => Mage::helper('fidelitas')->__('Percentage of Opens Vs Sent'),
            'factivity_percentage_conversions' => Mage::helper('fidelitas')->__('Percentage Conversions Vs Sent'),
            'factivity_amount_conversions' => Mage::helper('fidelitas')->__('Conversions Amount'),
            'factivity_number_conversions' => Mage::helper('fidelitas')->__('Conversions Number'),
            'factivity_lists' => Mage::helper('fidelitas')->__('List'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement() {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType() {

        switch ($this->getAttribute()) {
            case 'factivity_lists':
                return 'select';
        }

        return 'numeric';
    }

    public function getValueElementType() {

        switch ($this->getAttribute()) {
            case 'factivity_lists':
                return 'select';
        }

        return 'text';
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object) {

        $dbAttrName = str_replace('factivity_', '', $this->getAttribute());

        $currentSegment = Mage::registry('current_segment');
        $mType = $currentSegment->getType();



        $list = Mage::registry('current_list');

        if ($dbAttrName == 'percentage_clicks_newsletter') {

            $model = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('clicks')
                    ->addFieldToSelect('email_views')
                    ->addFieldToFilter('list', $list->getListnum());

            if ($mType == 'customers') {
                $model->addFieldToFilter('customer_id', $object->getId());
            } elseif ($mType == 'visitors') {
                $model->addFieldToFilter('email', $object->getCustomerEmail());
            } elseif ($mType == 'both') {
                $model->getSelect()->where('customer_id=' . $model->getConnection()->quoteInto($object->getId()) . ' OR email=?', $object->getCustomerEmail());
            }

            if ($model->count() != 1) {
                return false;
            }

            $perc = round($model->getFirstItem()->getData('clicks') * 100 / $model->getFirstItem()->getData('email_views'));

            $object->setData($this->getAttribute(), $perc);

            return parent::validate($object);
        } elseif ($dbAttrName == 'percentage_opens_newsletter') {

            $model = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('email_views')
                    ->addFieldToSelect('email_sent')
                    ->addFieldToSelect('customer_id')
                    ->addFieldToFilter('customer_id')
                    ->addFieldToFilter('list', $list->getListnum());

            if ($mType == 'customers') {
                $model->addFieldToFilter('customer_id', $object->getId());
            } elseif ($mType == 'visitors') {
                $model->addFieldToFilter('email', $object->getCustomerEmail());
            } elseif ($mType == 'both') {
                $model->getSelect()->where('customer_id=' . $model->getConnection()->quoteInto($object->getId()) . ' OR email=?', $object->getCustomerEmail());
            }

            if ($model->count() != 1) {
                return false;
            }

            $perc = round($model->getFirstItem()->getData('email_views') * 100 / $model->getFirstItem()->getData('email_sent'));

            $object->setData($this->getAttribute(), $perc);

            return parent::validate($object);
        } elseif ($dbAttrName == 'percentage_sent_newsletter') {

            $model = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('clicks')
                    ->addFieldToSelect('email_sent')
                    ->addFieldToFilter('list', $list->getListnum());

            if ($mType == 'customers') {
                $model->addFieldToFilter('customer_id', $object->getId());
            } elseif ($mType == 'visitors') {
                $model->addFieldToFilter('email', $object->getCustomerEmail());
            } elseif ($mType == 'both') {
                $model->getSelect()->where('customer_id=' . $model->getConnection()->quoteInto($object->getId()) . ' OR email=?', $object->getCustomerEmail());
            }

            if ($model->count() != 1) {
                return false;
            }

            $perc = round($model->getFirstItem()->getData('clicks') * 100 / $model->getFirstItem()->getData('email_sent'));

            $object->setData($this->getAttribute(), $perc);

            return parent::validate($object);
        } elseif ($dbAttrName == 'percentage_conversions') {

            $model = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('conversions_number')
                    ->addFieldToSelect('email_sent')
                    ->addFieldToFilter('list', $list->getListnum());

            if ($mType == 'customers') {
                $model->addFieldToFilter('customer_id', $object->getId());
            } elseif ($mType == 'visitors') {
                $model->addFieldToFilter('email', $object->getCustomerEmail());
            } elseif ($mType == 'both') {
                $model->getSelect()->where('customer_id=' . $model->getConnection()->quoteInto($object->getId()) . ' OR email=?', $object->getCustomerEmail());
            }

            if ($model->count() != 1) {
                return false;
            }

            $perc = round($model->getFirstItem()->getData('conversions_number') * 100 / $model->getFirstItem()->getData('email_sent'));

            $object->setData($this->getAttribute(), $perc);

            return parent::validate($object);
        } elseif ($dbAttrName == 'amount_conversions') {

            $model = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('conversions_amount')
                    ->addFieldToFilter('list', $list->getListnum());

            if ($mType == 'customers') {
                $model->addFieldToFilter('customer_id', $object->getId());
            } elseif ($mType == 'visitors') {
                $model->addFieldToFilter('email', $object->getCustomerEmail());
            } elseif ($mType == 'both') {
                $model->getSelect()->where('customer_id=' . $model->getConnection()->quoteInto($object->getId()) . ' OR email=?', $object->getCustomerEmail());
            }

            if ($model->count() != 1) {
                return false;
            }

            $object->setData($this->getAttribute(), $model->getFirstItem()->getData('conversions_amount'));

            return parent::validate($object);
        } elseif ($dbAttrName == 'number_conversions') {

            $model = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('conversions_number')
                    ->addFieldToFilter('list', $list->getListnum());

            if ($mType == 'customers') {
                $model->addFieldToFilter('customer_id', $object->getId());
            } elseif ($mType == 'visitors') {
                $model->addFieldToFilter('email', $object->getCustomerEmail());
            } elseif ($mType == 'both') {
                $model->getSelect()->where('customer_id=' . $model->getConnection()->quoteInto($object->getId()) . ' OR email=?', $object->getCustomerEmail());
            }

            if ($model->count() != 1) {
                return false;
            }

            $object->setData($this->getAttribute(), $model->getFirstItem()->getData('conversions_number'));

            return parent::validate($object);
        } elseif ($dbAttrName == 'lists') {

            $model = Mage::getModel('fidelitas/subscribers')
                    ->getCollection()
                    ->addFieldToSelect('list')
                    ->addFieldToFilter('list', array($this->translateOperator() => $this->getValueParsed()));

            if ($mType == 'customers') {
                $model->addFieldToFilter('customer_id', $object->getId());
            } elseif ($mType == 'visitors') {
                $model->addFieldToFilter('email', $object->getCustomerEmail());
            } elseif ($mType == 'both') {
                $model->getSelect()->where('customer_id=' . $model->getConnection()->quoteInto($object->getId()) . ' OR email=?', $object->getCustomerEmail());
            }

            if ($model->count() > 0) {
                return true;
            } else {
                return false;
            }

            return parent::validate($object);
        }

        if ($model->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function collectValidatedAttributes($customerCollection) {

        $attribute = $this->getAttribute();

        $attributes = $this->getRule()->getCollectedAttributes();
        $attributes[$attribute] = true;
        $this->getRule()->setCollectedAttributes($attributes);

        return $this;
    }

    public function translateOperator() {

        $operator = $this->getOperator();

        $newValue = array('==' => 'eq', '!=' => 'neq', '>=' => 'gteq', '<=' => 'lteq', '>' => 'lt', '<' => 'gt', '{}' => 'like', '!{}' => 'nlike', '()' => 'in', '!()' => 'nin');

        if (isset($newValue[$operator]))
            return $newValue[$operator];

        return 'eq';
    }

    public function getValueSelectOptions() {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'factivity_lists':
                    $options = Mage::getModel('fidelitas/lists')
                            ->getOptionArray();
                    break;
                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

}
