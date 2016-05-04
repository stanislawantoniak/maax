<?php

class Zolago_Solrsearch_Block_Faces_Flag extends Zolago_Solrsearch_Block_Faces_Abstract
{
	protected $_bestsellerFacet;
	protected $_isNewFacet;

	const FACET_BESTSELLER	= 'Bestseller';
	const FACET_NEW			= 'New';
			
	public function __construct()
	{
		if (!$this->_bestsellerFacet) {
			$this->_bestsellerFacet = Mage::helper('zolagosolrsearch')->__(self::FACET_BESTSELLER);
		}
		
		if (!$this->_isNewFacet) {
			$this->_isNewFacet = Mage::helper('zolagosolrsearch')->__(self::FACET_NEW);
		}
		
		$this->setTemplate('zolagosolrsearch/standard/searchfaces/flag.phtml');
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

    public function isPromotion($item)
    {
        if(strtolower(Mage::helper('zolagosolrsearch')->__($item)) == 'promotion') {
            return true;
        }

        return false;
    }

    public function getNameBasedOnContent($item)
    {
        $name = '';
        if($this->isBestseller($item)) {
            $name = 'filter_recommended_products_bestseler';
        } else if($this->isNew($item)) {
            $name = 'filter_recommended_products_news';
        } else if($this->isPromotion($item)) {
            $name = 'filter_recommended_products_promotion';
        } else if($this->isSale($item)) {
            $name = 'filter_recommended_products_sale';
        }

        return $name;
    }

    public function getClassBasedOnContent($item)
    {
        $name = '';
        if($this->isBestseller($item)) {
            $name = 'bestseller';
        } else if($this->isNew($item)) {
            $name = 'new';
        } else if($this->isPromotion($item)) {
            $name = 'promotion';
        } else if($this->isSale($item)) {
            $name = 'sale';
        }

        return $name;
    }

    // Can show filter block
    public function getCanShow() {
        return true;
    }
}