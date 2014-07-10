<?php

class Zolago_Solrsearch_Block_Active extends Zolago_Solrsearch_Block_Faces
{
	public function _construct(){
		parent::_construct();
		$this->setTemplate("zolagosolrsearch/standard/active.phtml");
	}
}