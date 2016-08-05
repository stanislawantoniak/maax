<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Word extends Magpleasure_Common_Model_Abstract
{
    protected $_config;
    protected $_wordList = array();

    public function getWordId($word)
    {
        Varien_Profiler::start('mp::searchcore::word::has_generation');
        $hash = crc32($word);
        Varien_Profiler::stop('mp::searchcore::word::has_generation');

        if (!isset($this->_wordList[$hash])){

            Varien_Profiler::start('mp::searchcore::word::fetch_word_id');
            $wordId = $this->getResource()->getWordId($word);
            Varien_Profiler::stop('mp::searchcore::word::fetch_word_id');

            $this->_wordList[$hash] = $wordId;
        }

        return $this->_wordList[$hash];
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('searchcore/word');
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
}