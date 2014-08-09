<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Removebutton
    extends Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Link
{
    public function render(Varien_Object $row){
        $target = $this->getColumn()->getLinkTarget() ? $this->getColumn()->getLinkTarget() : "_blank";
        return "<a class='btn btn-xs' href=\"{$this->_getLink($row)}\" target=\"$target\" data-action='remove' data-item=\"{$this->_getValue($row)}\"><i class='icon-remove'></i></a>";
    }

    protected function _getLink(Varien_Object $row) {
        return Mage::getUrl(
            $this->getColumn()->getLinkAction(),
            array($this->getColumn()->getLinkParam()=>$this->_getValue($row))
        );
    }
}