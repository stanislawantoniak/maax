<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Template
{
    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Prepare AngularJs Template string
     *
     * @param $template
     * @param array $data
     * @return bool
     */
    public function prepareAngularJsTemplate($template, $data = array())
    {
        return str_replace(array("'", "\n", "\r"), array("\\'", "", ""), $this->compileTemplate($template, $data));
    }

    public function compileTemplate($template, $data = array())
    {
        /** @var Magpleasure_Common_Block_Adminhtml_Template $block */
        $block = $this->getLayout()->createBlock('magpleasure/adminhtml_template');
        if ($block){

            $html = $block->setTemplate($template)->addData($data)->toHtml();
            return $html;
        }

        return false;
    }

}