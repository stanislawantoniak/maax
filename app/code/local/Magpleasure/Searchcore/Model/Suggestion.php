<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Suggestion extends Mage_Core_Model_Abstract
{
    const WORD_LIMIT = 5;

    /**
     * An array of Magpleasure_Searchcore_Model_Suggestion_Word  where key is word from original query string
     *
     * @var Magpleasure_Searchcore_Model_Suggestion_Word[]
     */
    protected $_words = array();

    protected $_isPrepared = false;

    /**
     * @param null $query
     *
     * @return Magpleasure_Searchcore_Model_Suggestion_Word
     */
    public function getWords($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQueryText();
        }

        $this->prepareWords($query);

        return $this->_words;
    }

    /**
     * @param string|null $query
     *
     * @return string
     */
    public function prepareWords($query = null)
    {
        if ($this->_isPrepared){
            return $this;
        }

        if (is_null($query)){
            $query = $this->getQueryText();
        }

        if ($query) {

            $stringHelper = $this->_helper()->getCommon()->getStrings();
            $words = $stringHelper->htmlToPlainText($query);

            $words = explode(" ", $words);

            if (count($words) > self::WORD_LIMIT){
                $words = array_slice($words, 0, self::WORD_LIMIT);
            }

            $dbHelper = $this->_helper()->getCommon()->getDatabase();
            $this->_flushWords();

            foreach ($words as $word) {

                /** @var Magpleasure_Searchcore_Model_Suggestion_Word $suggestionWord */
                $suggestionWord = Mage::getModel('searchcore/suggestion_word');
                $searchWords = $suggestionWord
                    ->setWord($word)
                    ->getTypos(true)
                ;

                # fetching most relevance word in DB
                $linkTableName = $dbHelper->getTableName('mp_search_index_word');
                $wordsTableName = $dbHelper->getTableName('mp_search_word');

                $columnStr = "word, count(word) AS 'count'";
                $fromStr = "$wordsTableName INNER JOIN $linkTableName ON $wordsTableName.word_id = $linkTableName.word_id";
                $whereStr = count($searchWords) > 1 ? sprintf("WHERE word in ('%s')", implode("','", $searchWords)) : "WHERE word = '$searchWords[0]'";
                $etcString = 'GROUP BY word ORDER BY count DESC LIMIT 1';

                $result = $dbHelper->fetchAll($columnStr, $fromStr, $whereStr, $etcString);

                if (count($result)) {

                    $suggestWordValue = $result[0]['word'];
                    $suggestionWord->setSuggestValue($suggestWordValue);
                }

                $this->_words[$word] = $suggestionWord;
            }
        }

        $this->_isPrepared = true;

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
     *
     */
    protected function _flushWords()
    {
        foreach ($this->_words as $word) {
            unset($word);
        }
        $this->_words = array();
    }

    /**
     * @param string $query
     */
    protected function _construct($query = null)
    {
        parent::_construct();
        $this->_init('searchcore/suggestion');
    }
}