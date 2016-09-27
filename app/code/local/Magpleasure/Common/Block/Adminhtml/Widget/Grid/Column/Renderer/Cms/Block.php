<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Cms_Block
    extends Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $blockId = $this->_getValue($row);
        if ($blockId) {
            $html = "";
            /** @var Mage_Cms_Model_Block $block  */
            $block = Mage::getModel('cms/block');
            if (is_numeric($blockId)){
                $block->load($blockId);
            } else {
                $block->load($blockId, 'identifier');
            }

            $title = $block->getTitle();
            $url = $this->getUrl('adminhtml/cms_block/edit', array('block_id'=>$block->getId()));
            $html .= "<a href=\"{$url}\" target=\"_blank\">{$title}</a>";
            return $html;
        } else {
            return $this->_commonHelper()->__("N/A");
        }
    }



}