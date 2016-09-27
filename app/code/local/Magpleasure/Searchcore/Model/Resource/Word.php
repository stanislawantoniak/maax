<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Resource_Word extends Magpleasure_Common_Model_Resource_Abstract
{
    public function getWordId($word, $readOnly = false)
    {
        # 1. Try to find it
        $wordId = $this->_fetchWordId($word);

        # 2. If have no just insert it
        if (!$wordId && !$readOnly){

            $this->_insertWord($word);
            $wordId = $this->_fetchWordId($word);
        }

        return $wordId;
    }

    /**
     * Fast fetching of word id
     *
     * @param $word
     * @return string
     */
    protected function _fetchWordId($word)
    {
        $read = $this->getReadAdapter();
        $select = $read->select();
        $select
            ->from(array('word'=>$this->getMainTable()), array('word_id'))
            ->where("word = ?", $word);

        $wordId = $read->fetchOne($select);
        return $wordId;
    }

    /**
     * Fast word inserting
     *
     * @param $word
     * @return $this
     */
    protected function _insertWord($word)
    {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->insert($this->getMainTable(), array('word' => $word));
        $write->commit();

        return $this;
    }

    protected function _construct()
    {
        $this->_init('searchcore/word', 'word_id');
    }
}