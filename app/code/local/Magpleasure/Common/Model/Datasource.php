<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Data Source
 */
class Magpleasure_Common_Model_Datasource extends Varien_Object
{
    protected $_params;

    /**
     * Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    public function setParams(array $params)
    {
        $this->_params = new Varien_Object($params);
        return $this;
    }

    /**
     * Extract Field Names from Pattern
     *
     * @param string $pattern String within variables {{var variable_name}}
     * @return array
     */
    protected function _getFieldsFromPattern($pattern)
    {
        $answers = array();
        if ($pattern){
            preg_match_all("/\{\{[a-z_]{1,}\}\}/", $pattern, $results);
            if (isset($results[0])){
                $results = $results[0];
                if (is_array($results)){
                    foreach ($results as $result){
                        $answer = $result;
                        $answer = str_replace("{{", "", $answer);
                        $answer = str_replace("}}", "", $answer);
                        $answers[] = $answer;
                    }
                }
            }
        }
        return $answers;
    }

    /**
     * Process Object with pattern
     *
     * @param $pattern
     * @param Mage_Core_Model_Abstract $obj
     * @return string
     */
    protected function _processCollectionItem($pattern, Mage_Core_Model_Abstract $obj)
    {
        $result = $pattern;
        foreach ($this->_getFieldsFromPattern($pattern) as $field){
            $result = str_replace("{{{$field}}}", $obj->getData($field), $result);
        }
        return $result;
    }

    protected function _prepareDataFromModel()
    {
        if (!$this->_params){
            return array();
        }

        $params = $this->_params;

        try {
            $rows = array();

            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::getModel($params->getModel())->getCollection();

            $query = $params->getQuery();
            $filterField = $params->getFilterField();
            if ($query && $filterField){
                $collection->addFieldToFilter($filterField, array('like' => "%{$query}%"));
            }

            if ($limit = $params->getLimit()){
                $collection->setPageSize($limit);
            }

            if ($page = $params->getPage()){
                $collection->setCurPage($page);
            }

            $methods = $params->getMethods();
            if ($methods && is_array($methods)){
                foreach ($methods as $method){
                    $parameters = isset($method['parameters']) ? $method['parameters'] : array();
                    $methodName = isset($method['method']) ? $method['method'] : null;

                    if ($methodName){
                        call_user_func(array($collection, $methodName), $parameters);
                    }
                }
            }

            if ($sortField = $params->getSortField()){
                $collection->setOrder($sortField, ($params->getSortDirection() ? $params->getSortDirection() : null));
            }

            if ($collection->getSize()){
                foreach ($collection as $item){
                    /** @var $item Mage_Core_Model_Abstract */
                    $rows[] = array(
                        'id'    => $this->_processCollectionItem($params->getData('entity_id_pattern'), $item),
                        'text' => $this->_processCollectionItem($params->getData('entity_label_pattern'), $item),
                    );
                }
            }

            $result = array(
                'rows' => $rows,
                'total' => $collection->getSize(),
            );

            return $result;
        } catch (Exception $e){
            $this->_commonHelper()->getException()->logException($e);
        }

        return array(
            'rows' => array(),
            'total' => 0,
        );
    }

    public function getArrayData()
    {
        return $this->_prepareDataFromModel();
    }

    /**
     * Retrieves Value
     *
     * @param $value
     * @return string
     */
    public function getLabelByValue($value)
    {
        if (!$this->_params){
            return false;
        }
        $params = $this->_params;

        if ($params->getData('entity_label_pattern') != $params->getData('entity_id_pattern')){

            $model = Mage::getModel($params->getModel())->load($value);
            if ($model->getId()){
                return $this->_processCollectionItem($params->getData('entity_label_pattern'), $model);
            }

        } else {
            return $value;
        }

        return false;
    }
}