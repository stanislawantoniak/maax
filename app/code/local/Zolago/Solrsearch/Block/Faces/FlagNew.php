<?php

class Zolago_Solrsearch_Block_Faces_Flag extends Zolago_Solrsearch_Block_Faces_Abstract
{
	protected $_bestsellerFacet;
	protected $_isNewFacet;

	const FACET_BESTSELLER	= 'Bestseller';
	const FACET_NEW			= 'New';
			
	public function __construct()
	{
		$this->setTemplate('zolagosolrsearch/standard/searchfaces/flags.phtml');
	}
	
	public function getFacetLabel($facetCode=null) {
		return Mage::helper('zolagosolrsearch')->__('Product Flags');
	}
	
	public function isBestseller($item)
    {
        if(strtolower(Mage::helper('zolagosolrsearch')->__($item)) == "bestseller") {
            return true;
        }

        return false;
    }

    public function isSale($item)
    {
        if(strtolower(Mage::helper('zolagosolrsearch')->__($item)) == 'sale') {
            return true;
        }

        return false;
    }

    public function isNew($item)
    {
        if(strtolower(Mage::helper('zolagosolrsearch')->__($item)) == 'new') {
            return true;
        }

        return false;
    }
	
}