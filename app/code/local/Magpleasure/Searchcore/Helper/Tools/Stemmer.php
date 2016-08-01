<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

require_once (Mage::getBaseDir('lib').DS."Stemmers".DS."Potter.php");
class Magpleasure_Searchcore_Helper_Tools_Stemmer
{
    protected $_cache = array();

    public function stem($word, $locale = "en_US")
    {
        Varien_Profiler::start("mp::searchcore::index::crc_create");
        $hash = crc32($word);
        Varien_Profiler::stop("mp::searchcore::index::crc_create");
        if (!isset($this->_cache[$hash])){

            $this->_cache[$hash] = $this->_stem($word, $locale);
        }

        return $this->_cache[$hash];
    }

    protected function _stem($word, $locale = "en_US")
    {
        if ($word){
            Varien_Profiler::start("mp::searchcore::index::word_stem");
            $word = PorterStemmer::Stem($word);
            Varien_Profiler::stop("mp::searchcore::index::word_stem");
        }

        return $word;
    }
}