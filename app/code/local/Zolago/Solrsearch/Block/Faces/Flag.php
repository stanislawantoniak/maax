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
	
	public function getItemUrl($item) {
		$originalItem	= $item;
		$face_key		= $this->getAttributeCode();

		if ($this->getProductFlagsFacetValue($item, true)) {
			list($face_key, $item) = $this->getProductFlagsFacetValue($item);
		}
		
		$facetUrl = $this->getFacesUrl(array('fq' => array($face_key => $item)));
		
		if ($this->isItemActive($originalItem)) {
			$facetUrl = $this->getRemoveFacesUrl($face_key, $originalItem);
		}
		return $facetUrl;
	}
	
	public function isItemActive($item) {
		$facetKey = $this->getFacetKey();
		
		if ($this->getProductFlagsFacetValue($item, true)) {
			list($facetKey, $item) = $this->getProductFlagsFacetValue($item, true);
		}
		
		$filterQuery = $this->getFilterQuery();
		if (isset($filterQuery[$facetKey]) && in_array($item, $filterQuery[$facetKey])) {
			return true;
		}
		return false;
	}

	/**
	 * Get Facet Values (Key and Item Value) for the special Product Flags Facet
	 * 
	 * @param string $item
	 * @param boolean $facet
	 * 
	 * @return mixed boolean|array
	 */
	public function getProductFlagsFacetValue($item, $facet = false)
	{
		$facetValue = false;
		
		switch ($item) {
			case $this->_bestsellerFacet:
				$facetValue[]	= ($facet) ? 'is_bestseller_facet' : 'is_bestseller';
				$facetValue[]	= Mage::helper('core')->__('Yes');
				break;
			case $this->_isNewFacet:
				$facetValue[]	= ($facet) ? 'is_new_facet' : 'is_new';
				$facetValue[]	= Mage::helper('core')->__('Yes');
				break;			
			default:
				break;
		}

		return $facetValue;
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
        if(strtolower(Mage::helper('zolagosolrsearch')->__($item)) == 'wyprzedaż') {
            return true;
        }

        return false;
    }

    public function isNew($item)
    {
        if(strtolower(Mage::helper('zolagosolrsearch')->__($item)) == 'nowość') {
            return true;
        }

        return false;
    }

    public function isPromotion($item)
    {
        if(strtolower(Mage::helper('zolagosolrsearch')->__($item)) == 'promocja') {
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
}