<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_LinkRefer
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		if(!$this->_getValue($row)){
			return "";
		}

		$attributes = $this->getColumn()->getAttributes();
		$attrsHtml = "";
		if (is_array($attributes)) {
			$attrsHtml = " ";
			foreach ($attributes as $attr => $value) {
				if($value = 'id')
					$value = $this->_getValue($row);
				$attrsHtml .= " " . $attr . "=\"" . $value . "\" ";
			}
		}
		$target = $this->getColumn()->getLinkTarget() ? $this->getColumn()->getLinkTarget() : "_blank";
		return "<a href=\"{$this->_getLink($row)}\" target=\"$target\" class=\"{$this->getColumn()->getLinkClass()}\" $attrsHtml>{$row->getData('link_name')}</a>";
	}

    /**
     * @param Varien_Object $row
     * @return string
     */
    protected function _getLink(Varien_Object $row)
    {
        if ($this->getColumn()->getIsAdminLink()) {
            return Mage::helper("adminhtml")->getUrl(
                $this->getColumn()->getLinkAction(),
                array($this->getColumn()->getLinkParam() => $row->getData('link_param'))
            );
        }
        return Mage::getUrl(
            $this->getColumn()->getLinkAction(),
            array($this->getColumn()->getLinkParam() => $row->getData('link_param'))
        );
    }
}