<?php

/**
 * Class GH_Regulation_Block_Adminhtml_List_Renderer_Filename
 */
class GH_Regulation_Block_Adminhtml_List_Renderer_Filename extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row) {
        /** @var GH_Regulation_Model_Regulation_Document $row */
        $value = $row->getFileName();

        return "<a href=\"{$row->getAdminUrl()}\">{$value}</a>";
    }

}
