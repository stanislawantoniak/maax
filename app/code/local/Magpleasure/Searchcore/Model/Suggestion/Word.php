<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Suggestion_Word extends Varien_Object
{
    /**
     * @var string
     */
    protected $_origValue = '';
    /**
     * @var string
     */
    protected $_value = '';
    /**
     * @var string
     */
    protected $_suggestValue = '';
    /**
     *
     *
     * @var string
     */
    protected $_query = '';
    /**
     * @var array
     */
    protected $_typos = array();

    /**
     * @return string
     */
    public function getOrigValue()
    {
        return $this->_origValue;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * @param $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->_query = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getSuggestValue()
    {
        return $this->_suggestValue;
    }

    /**
     * @param string $suggestValue
     */
    public function setSuggestValue($suggestValue)
    {
        if ($this->_isUcFirst()){
            $this->_suggestValue = ucfirst($suggestValue);
        } else {
            $this->_suggestValue = $suggestValue;
        }
    }

    protected function _isUcFirst()
    {
        return $this->_origValue == ucfirst($this->_origValue);
    }

    /**
     * @param bool $includeThemselves
     *
     * @return array
     */
    public function getTypos($includeThemselves = false)
    {
        $typos = $this->_typos;

        if ($includeThemselves) {
            $typos[] = $this->_value;
        }

        return $typos;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    public function setWord($value)
    {
        $this->_origValue = $value;

        $value = str_replace(".", "", $value);
        $this->_value = strtolower($value) ;
        $this->_suggestValue = $value;

        $stringHelper = $this->_helper()->getCommon()->getStrings();
        $cleanValue = $stringHelper->strtolower($value);
        $cleanValue = trim($cleanValue);

        $this->_typos = $stringHelper->generateTypos($cleanValue);

        return $this;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }

    /**
     * @return bool
     */
    public function getIsMistyped()
    {
        return strtolower($this->_value) !== strtolower($this->_suggestValue);
    }
}
