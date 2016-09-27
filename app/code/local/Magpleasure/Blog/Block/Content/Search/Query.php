<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Search_Query extends Magpleasure_Blog_Block_Sidebar_Search
{
    protected $_words;

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'content';

        return $params;
    }

    /**
     * Search Query Model
     *
     * @return Magpleasure_Searchcore_Model_Query
     */
    public function getQueryModel()
    {
        return Mage::registry(Magpleasure_Blog_Model_Search::SEARCH_QUERY_KEY);
    }

    /**
     * Suggestion Model
     *
     * @return Magpleasure_Searchcore_Model_Suggestion
     */
    public function getWords()
    {
        if (!$this->_words){
            $this->_words = $this->getQueryModel()->getSuggestionWords();
        }
        return $this->_words;
    }

    public function getGetSuggestedQueryUrl(Magpleasure_Searchcore_Model_Suggestion_Word $word)
    {
        if ($word->getQuery()){

            return $this->_helper()->_url()->getUrl(null, Magpleasure_Blog_Helper_Url::ROUTE_SEARCH)."?query=".urlencode($word->getQuery());
        }

        return false;
    }

    public function hasSuggestion()
    {
        if (!$this->getQuery()){
            return false;
        }

        if ($this->getQueryModel()){
            return $this->getQueryModel()->hasSuggestion();
        }

        return false;
    }
}
