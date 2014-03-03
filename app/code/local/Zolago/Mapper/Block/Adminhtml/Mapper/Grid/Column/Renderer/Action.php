<?php

class Zolago_Mapper_Block_Adminhtml_Mapper_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
	/**
	 * @param Varien_Object $row
	 * @return string
	 */
    public function render(Varien_Object $row){
		if($row->getId()){
			return parent::render($row);
		}
		$oldActions = $this->getColumn()->getActions();
		$this->getColumn()->setActions($this->getColumn()->getAltActions());
		$render = parent::render($row);
		$this->getColumn()->setActions($oldActions);
		return $render;
	}
	
	/**
	 * @param Varien_Object $row
	 * @return string
	 */
	public function _getValue(Varien_Object $row) {
		if($row->getId()){
			return parent::_getValue($row);
		}
		$oldIndex = $this->getColumn()->getIndex();
		$this->getColumn()->setIndex($this->getColumn()->getData("alt_index"));
		$render = parent::_getValue($row);
		$this->getColumn()->setIndex($oldIndex);
		return $render;
	}
}
