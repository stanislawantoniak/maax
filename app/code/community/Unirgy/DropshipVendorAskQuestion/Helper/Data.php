<?php

class Unirgy_DropshipVendorAskQuestion_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEmptyDate($date)
    {
        return empty($date) || $date=='0000-00-00' || $date=='0000-00-00 00:00:00';
    }
    public function saveFormData($data=null, $id=null)
    {
        $formData = Mage::getSingleton('udqa/session')->getFormData();
        if (!is_array($formData)) {
            $formData = array();
        }
        $data = !is_null($data) ? $data : Mage::app()->getRequest()->getPost();
        $id = !is_null($id) ? $id : Mage::app()->getRequest()->getParam('question_id');
        $formData[$id] = $data;
        Mage::getSingleton('udqa/session')->setFormData($formData);
    }

    public function fetchFormData($id=null)
    {
        $formData = Mage::getSingleton('udqa/session')->getFormData();
        if (!is_array($formData)) {
            $formData = array();
        }
        $id = !is_null($id) ? $id : Mage::app()->getRequest()->getParam('question_id');
        $result = false;
        if (isset($formData[$id]) && is_array($formData[$id])) {
            $result = $formData[$id];
            unset($formData[$id]);
            if (empty($formData)) {
                Mage::getSingleton('udqa/session')->getFormData(true);
            } else {
                Mage::getSingleton('udqa/session')->setFormData($formData);
            }
        }
        return $result;
    }
    public function getCustomerQuestionsCollection()
    {
        return Mage::getModel('udqa/question')->getCollection()
            ->joinShipments()
            ->joinProducts()
            ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
            ->setDateOrder();
    }
    public function getProductQuestionsCollection()
    {
        $questions = Mage::getModel('udqa/question')->getCollection();
        if (!Mage::registry('current_product')) {
            $questions->setEmptyFilter();
        } else {
            $questions
                ->joinProducts()
                ->addPublicProductFilter(Mage::registry('current_product')->getId())
                ->setDateOrder();
        }
        return $questions;
    }

    public function addProductAttributeToSelect($select, $attrCode, $entity_id)
    {
        $alias = $attrCode;
        if (is_array($attrCode)) {
            reset($attrCode);
            $alias = key($attrCode);
            $attrCode = current($attrCode);
        }
        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            $select->columns(array($alias=>new Zend_Db_Expr("''")));
            return $this;
        }
        $attributeId    = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $adapter        = $select->getAdapter();
        $store = Mage::app()->getStore()->getId();

        if ($attribute->isScopeGlobal()) {
            $_alias = 'ta_' . $attrCode;
            $select->joinLeft(
                array($_alias => $attributeTable),
                "{$_alias}.entity_id = {$entity_id} AND {$_alias}.attribute_id = {$attributeId}"
                    . " AND {$_alias}.store_id = 0",
                array()
            );
            $expression = new Zend_Db_Expr("{$_alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;

            $select->joinLeft(
                array($dAlias => $attributeTable),
                "{$dAlias}.entity_id = {$entity_id} AND {$dAlias}.attribute_id = {$attributeId}"
                    . " AND {$dAlias}.store_id = 0",
                array()
            );
            $select->joinLeft(
                array($sAlias => $attributeTable),
                "{$sAlias}.entity_id = {$entity_id} AND {$sAlias}.attribute_id = {$attributeId}"
                    . " AND {$sAlias}.store_id = {$store}",
                array()
            );
            $expression = $this->getCheckSql($this->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value", "{$dAlias}.value");
        }

        $select->columns(array($alias=>$expression));

        return $this;
    }

    public function getCaseSql($valueName, $casesResults, $defaultValue = null)
    {
        $expression = 'CASE ' . $valueName;
        foreach ($casesResults as $case => $result) {
            $expression .= ' WHEN ' . $case . ' THEN ' . $result;
        }
        if ($defaultValue !== null) {
            $expression .= ' ELSE ' . $defaultValue;
        }
        $expression .= ' END';

        return new Zend_Db_Expr($expression);
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }

    public function getIfNullSql($expression, $value = 0)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IFNULL((%s), %s)", $expression, $value);
        } else {
            $expression = sprintf("IFNULL(%s, %s)", $expression, $value);
        }

        return new Zend_Db_Expr($expression);
    }

    public function getStore($question)
    {
        return Mage::app()->getDefaultStoreView();
    }
    public function isNotifyAdminVendor($question)
    {
        $store = Mage::helper('udqa')->getStore($question);
        return !$question->getIsAdminQuestionNotified()
            && Mage::getStoreConfigFlag('udqa/general/send_admin_notifications', $store);
    }
    public function notifyAdminVendor($question)
    {
        Mage::helper('udqa/protected')->notifyAdminVendor($question);
        return $this;
    }

    public function isNotifyAdminCustomer($question)
    {
        $store = Mage::helper('udqa')->getStore($question);
        return !$question->getIsAdminAnswerNotified()
            && $question->getAnswerText()
            && Mage::getStoreConfigFlag('udqa/general/send_admin_notifications', $store);
    }
    public function notifyAdminCustomer($question)
    {
        Mage::helper('udqa/protected')->notifyAdminCustomer($question);
        return $this;
    }

    public function isNotifyCustomer($question)
    {
        $store = Mage::helper('udqa')->getStore($question);
        return !$question->getIsCustomerNotified()
            && $question->getCustomerEmail()
            && $question->canCustomerViewAnswer()
            && Mage::getStoreConfigFlag('udqa/general/send_customer_notifications', $store)
            || $question->getForcedCustomerNotificationFlag()
                && $question->getCustomerEmail();
    }
    public function notifyCustomer($question)
    {
        Mage::helper('udqa/protected')->notifyCustomer($question);
        return $this;
    }

    public function isNotifyVendor($question)
    {
        $store = Mage::helper('udqa')->getStore($question);
        return !$question->getIsVendorNotified()
            && $question->getVendorEmail()
            && $question->getQuestionStatus()==Unirgy_DropshipVendorAskQuestion_Model_Source::UDQA_STATUS_APPROVED
            && Mage::getStoreConfigFlag('udqa/general/send_vendor_notifications', $store)
            || $question->getForcedVendorNotificationFlag()
                && $question->getVendorEmail();
    }
    public function notifyVendor($question)
    {
        Mage::helper('udqa/protected')->notifyVendor($question);
        return $this;
    }

}