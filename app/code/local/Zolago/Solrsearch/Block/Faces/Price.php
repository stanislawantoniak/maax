<?php

class Zolago_Solrsearch_Block_Faces_Price extends Zolago_Solrsearch_Block_Faces_Abstract {

	protected $_max;
	protected $_min;

	public function __construct() {
		$this->setTemplate('zolagosolrsearch/standard/searchfaces/price.phtml');
	}
	
    /**
     * overriding - display values will be calculated later
     */

	protected function _prepareItemValue($key,$val) {
	    return array (
	        'count' => $val,
        );
	}

	/**
	 * @return int
	 */
	public function getMinPriceRange() {
		return $this->_min;
	}

	/**
	 * @return int
	 */
	public function getMaxPriceRange() {
		return $this->_max;
	}

	public function getSolrData() {
		return $this->getParentBlock()->getSolrData();
	}
	
	/**
	 * @return array
	 */
	public function calculatePriceRanges() {
		if(!$this->hasData("calculated_ranges")){
			$this->setData("calculated_ranges", $this->_calculatePriceRanges());
		}
		return $this->getData("calculated_ranges");
	}

	/**
	 * Calculate price ranges
	 * @todo automatic price ranges and max interval
	 * @param array $priceRanges
	 * @param decimal $min
	 * @param decimal $max
	 * @return array:
	 */
	protected function _calculatePriceRanges() {
		$items = $this->getItems();
		$keys = array_keys($items);
		/**
		 * @todo bug with item from query stirng like 100 TO 200 in items array
		 */
		$keys = array_filter($keys, "is_numeric");
		
		sort($keys);
		reset($keys);
		$min = (float) current($keys);
		$max = (float) array_pop($keys);
		$this->setMin(floor($min));
		$this->setMax($max);
		
		$category = Mage::registry('current_category');

		$range = null;
		if ($category) {
			$data = $category->getData();
			if (!empty($data['filter_price_range'])) {
				$range = $data['filter_price_range'];
			} else {
				$calculation = Mage::app()->getStore()->getConfig(Mage_Catalog_Model_Layer_Filter_Price::XML_PATH_RANGE_CALCULATION);
				switch ($calculation) {
					case Mage_Catalog_Model_Layer_Filter_Price::RANGE_CALCULATION_AUTO:
						break;
					case Mage_Catalog_Model_Layer_Filter_Price::RANGE_CALCULATION_IMPROVED:
						break;
					case Mage_Catalog_Model_Layer_Filter_Price::RANGE_CALCULATION_MANUAL:
						$range = Mage::app()->getStore()->getConfig(Mage_Catalog_Model_Layer_Filter_Price::XML_PATH_RANGE_STEP);
						break;
				}
			}
		}
		if ($range) {
			$returnPriceRanges = array();
			$start = floor($min / $range) * $range;
			$elem = array();
			while ($start < $max) {
				$elem['start'] = floor($start);
				$elem['end'] = floor($start + $range);
				if ($elem['end'] > $max) {
					$elem['end'] = ceil($max);
				}
				$start += $range;
				$returnPriceRanges[] = $elem;
			}
			return (count($returnPriceRanges) > 1) ? $returnPriceRanges : array();
		}
		// there is no range        
		$solrData = $this->getSolrData();

		$priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();

		$priceRanges = array();


		if (isset($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts']) && is_array($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'])) {
			$priceRanges = $solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'];
		}

		$tempPriceRanges = array();
		$tempPriceRanges[] = $min;
		if (is_array($priceRanges)) {
			$index = 0;
			foreach ($priceRanges as $key => $value) {
				if ($index > 0) {
					$tempPriceRanges[] = $key;
				}
				$index++;
			}
		}
		//$tempPriceRanges[] = $max;

		$returnPriceRanges = array();
		$index = 0;
		foreach ($tempPriceRanges as $item) {
			$start = $item;
			$end = $item;

			if (isset($tempPriceRanges[($index + 1)])) {
				$end = ($tempPriceRanges[($index + 1)] - 1);
				if (($index + 1) == (count($priceRanges) - 1)) {
					$end = $max;
				}
			}
			if ($index < (count($tempPriceRanges) - 1)) {
				$returnPriceRanges[] = array('start' => $start, 'end' => $end);
			}

			$index++;
		}
		return (count($returnPriceRanges) > 1) ? $returnPriceRanges : array();


	}

	/**
	 * setting minimal price
	 * @param float $price
	 * @return 
	 */
	public function setMin($price) {
		if ($this->_min === null) {
			$this->_min = $price;
		}
		if ($price < $this->_min) {
			$this->_min = $price;
		}
	}

	/**
	 * setting maximal price
	 * @param float $price
	 * @return 
	 */
	public function setMax($price) {
		if ($price > $this->_max) {
			$this->_max = $price;
		}
	}

	public function getCurrentStartPrice() {
		$range = $this->getCurrentPriceRange();
		return isset($range[0]) ? $range[0] : 0;
	}
	
	public function getCurrentEndPrice() {
		$range = $this->getCurrentPriceRange();
		return isset($range[1]) ? $range[1] : 0;
	}
	
	public function getCurrentPriceRange() {
		$filterQuery = $this->getFilterQuery();
		if(isset($filterQuery[$this->getFacetKey()])){
			$value = $filterQuery[$this->getFacetKey()];
			if(is_array($value) && count($value)){
				$value = current($value);
			}
			if(is_string($value)){
				return array_map("trim", explode("TO", $value));
			}
		}
		return array();
	}
	public function buildValue($min, $max) {
		
	}
	
    /**
     * calculate url, id and active 
     * 
     * @param array &$item
     */

	protected function _prepareAdditionalValues(&$item) {
	    $value = $item['value'];
	    $item['url'] = $this->getItemUrl($value);
	    $item['itemId'] = $this->getItemId($value);
	    $item['active'] = $this->isItemActive($value);
	}
	
	protected function applyPriceRangeProductCount() {
		$priceRanges = $this->calculatePriceRanges();
		$appliedPriceRanges = array();
		
		foreach ($priceRanges as $l => $range) {
			$start = floor(floatval($range['start']));
			$end = ceil(floatval($range['end']));
			$value = $start . ' TO ' . $end;

			$rangeItemArray = array(
				'start' => $start,
				'end' => $end,
				'count' => 0,
				'formatted' => $this->getFilterContainer()->formatFacetPrice($value),
				'value' => $value,
			);
			$items = $this->getItems();
			foreach ($items as $price => $val) {
				$price = floor($price);
				if (floatval($price) >= floatval($start) && floatval($price) <= floatval($end)) {
					$rangeItemArray['count'] = ($rangeItemArray['count'] + $val['count']);
				}
			}

            if($rangeItemArray['count'] > 0){
                $appliedPriceRanges[] = $rangeItemArray;
            }
		}

        if (!empty($appliedPriceRanges)) {
            $appliedPriceRanges = array_values($appliedPriceRanges);
			$count = count($appliedPriceRanges);			
            for ($i = 0; $i < $count; $i++) {
                if (isset($appliedPriceRanges[$i + 1]) && $appliedPriceRanges[$i]['end'] !== $appliedPriceRanges[$i + 1]['start']) {
					$appliedPriceRanges[$i]['end'] = $appliedPriceRanges[$i + 1]['start'];
					$appliedPriceRanges[$i]['value'] = $appliedPriceRanges[$i]['start'] . ' TO ' . $appliedPriceRanges[$i + 1]['start'];
					$appliedPriceRanges[$i]['formatted'] = $this->getFilterContainer()->formatFacetPrice(
							$appliedPriceRanges[$i]['value']
					);
				};
    			$this->_prepareAdditionalValues($appliedPriceRanges[$i]);					
            }
        }
        // format first and last
		
		foreach($appliedPriceRanges as $i=>$range){
			if($i==0){
				$appliedPriceRanges[0]['value'] = 'TO ' . $appliedPriceRanges[0]['end'];			    
				$this->_prepareAdditionalValues($appliedPriceRanges[0]);			
			}elseif($i==$count-1){
				$appliedPriceRanges[$count-1]['value'] = $appliedPriceRanges[$count-1]['start'] . ' TO';
                $this->_prepareAdditionalValues($appliedPriceRanges[$count-1]);
			}else{
				continue;
			}
			$appliedPriceRanges[$i]['formatted'] = $this->getFilterContainer()->formatFacetPrice(
					$appliedPriceRanges[$i]['value']
			);
		}
		return $appliedPriceRanges;
	}

	public function getPriceFormat($price) {
		$formattedPrice = $price;
		$currencySign = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		$currencyPositionSetting = Mage::helper('solrsearch')->getSetting('currency_position');

		if ($currencyPositionSetting < 1) {
			//After
			$formattedPrice = $price . '&nbsp;' . $currencySign;
		} else {
			$formattedPrice = $currencySign . '&nbsp;' . $price;
		}
		return $formattedPrice;
	}

	public function getFacetPriceRanges() {
		return $this->applyPriceRangeProductCount();
	}

	public function getFacesUrl($params = array(), $paramss = NULL) {
		return $this->getParentBlock()->getFacesUrl($params, $paramss);
	}
	
	public function isItemActive($item) {
		$filterQuery = $this->getFilterQuery();
		if (isset($filterQuery[$this->getFacetKey()])) {
			if(is_array($filterQuery[$this->getFacetKey()])){
				return in_array((string)$item, $filterQuery[$this->getFacetKey()]);
			}
			
			return trim($filterQuery[$this->getFacetKey()])==trim($item);
		}
		return false;
	}
	
	public function getIsRangeActive() {
		return $this->getRequest()->getParam("slider")=="1" && $this->getCurrentPriceRange();
	}
	
	
}