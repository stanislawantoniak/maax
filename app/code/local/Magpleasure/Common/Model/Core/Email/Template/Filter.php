<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Core_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    protected $_inlineCssFile;
    protected $_inlineCssReplacements = array();

    /**
     * CSS Replacements
     *
     * @return array
     */
    public function getInlineCssReplacements()
    {
        return $this->_inlineCssReplacements;
    }

    public function inlinecssDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['file'])) {
            $this->setInlineCssFile($params['file']);
        }
        return '';
    }

    public function replacecssDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['from']) && isset($params['to'])) {

            $from = $params['from'];
            $to = $params['to'];
            $to = $this->_getVariable($to);

            if ($from && $to){

                $this->_inlineCssReplacements[] = array(
                    $from,
                    $to
                );
            }
        }
        return '';
    }

    /**
     * @param $filename
     * @return $this
     */
    public function setInlineCssFile($filename)
    {
        $this->_inlineCssFile = $filename;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInlineCssFile()
    {
        return $this->_inlineCssFile;
    }
}