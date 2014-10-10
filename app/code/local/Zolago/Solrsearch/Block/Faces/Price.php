<?php

class Zolago_Solrsearch_Block_Faces_Price extends Zolago_Solrsearch_Block_Faces_Abstract
{
    protected $_max;
    protected $_min;
    public function __construct()
    {
        $this->setTemplate('zolagosolrsearch/standard/searchfaces/price.phtml');
    }

    
    //{{{ 
    /**
     * @return int
     */
    public function getMinPriceRange() {
        return $this->_min;
    }
    //}}}
    //{{{ 
    /**
     * @return int
     */
    public function getMaxPriceRange() {
        return $this->_max;
    }
    //}}}
    public function _prepareLayout()
    {
        //Load js for price slider
        $usePriceSilder = (int)Mage::helper('solrsearch')->getSetting('use_price_slider');
        if ($usePriceSilder > 0) {
            $this->setTemplate('solrsearch/standard/searchfaces/price-slider.phtml');
            $head = $this->getLayout()->getBlock('head');
            if ($head) {
               // $head->addJs('solrsearch/slider.js');
            }
        }

        return parent::_prepareLayout();
    }

    public function getSolrData() {
        return $this->getParentBlock()->getSolrData();
    }

    /**
     * Calculate price ranges
     * @todo automatic price ranges and max interval
     * @param array $priceRanges
     * @param decimal $min
     * @param decimal $max
     * @return array:
     */
    protected function calculatePriceRanges()
    {
        $items = $this->getItems();
        $keys = array_keys($items);
        sort($keys);
        reset($keys);
        $min = (float)current($keys);
        $max = (float)array_pop($keys);
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
            $start = floor($min/$range)*$range;
            $elem = array();
            while ($start < $max) {
                $elem['start'] = floor($start);
                $elem['end'] = floor($start+$range);
                if ($elem['end'] > $max) {
                    $elem['end'] = ceil($max);
                }
                $start += $range;
                $returnPriceRanges[] = $elem;
            }
            return (count($returnPriceRanges)>1)? $returnPriceRanges:array();
        }
        // there is no range        
        $solrData = $this->getSolrData();

        $priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();

        $priceRanges = array();
        
        
        if ( isset($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts']) && is_array($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'])) {
            $priceRanges = $solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'];
        }

        $tempPriceRanges = array();
        $tempPriceRanges[] = $min;
        if (is_array($priceRanges)) {
            $index = 0;
            foreach ($priceRanges as $key=>$value) {
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
        return (count($returnPriceRanges)>1)? $returnPriceRanges:array();
        

        /*
        $solrData = $this->getSolrData();

        $priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();

        $priceRanges = array();

        // category
        $category = Mage::registry('current_category');
        $range = 0;
        $min = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['min'])) {
            $min = $solrData['stats']['stats_fields'][$priceFieldName]['min'];
        }

        $max = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['max'])) {
            $max = $solrData['stats']['stats_fields'][$priceFieldName]['max'];
        }
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
            $start = $min;
            $elem = array();
            while ($start < $max) {
                $elem['start'] = floor($start);
                $elem['end'] = floor($start+$range);
                if ($elem['end'] > $max) {
                    $elem['end'] = ceil($max);
                }
                $start += $range;
                $returnPriceRanges[] = $elem;
            }
            return (count($returnPriceRanges)>1)? $returnPriceRanges:array();
        }
        if ( isset($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts']) && is_array($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'])) {
            $priceRanges = $solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'];
        }

        $tempPriceRanges = array();
        $tempPriceRanges[] = $min;
        if (is_array($priceRanges)) {
            $index = 0;
            foreach ($priceRanges as $key=>$value) {
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
        return (count($returnPriceRanges)>1)? $returnPriceRanges:array();
        */
    }

    //{{{ 
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
    //}}}
    //{{{ 
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
    //}}}
    protected function applyPriceRangeProductCount() {
        $priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();
        $priceRanges = $this->calculatePriceRanges();

        $appliedPriceRanges = array();
        $currencySign = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        $currencyPositionSetting = $this->helper('solrsearch')->getSetting('currency_position');
        $counter = 0;
        $limit = count($priceRanges);
        foreach ($priceRanges as $range) {
            $start = floor(floatval($range['start']));
            $end = ceil(floatval($range['end']));
            $formattedStart = $this->getPriceFormat($start);//Mage::app()->getStore()->getCurrentCurrency()->format($start, null, false);
            $formattedEnd = $this->getPriceFormat($end);//Mage::app()->getStore()->getCurrentCurrency()->format($end, null, false);

//			if ($currencyPositionSetting > 0)
//			{
//				$formatted = $currencySign.'&nbsp;'.$start.' - '.$currencySign.'&nbsp;'.$end;
//			}else {
//				$formatted = $start.'&nbsp;'.$currencySign.' - '.$end.'&nbsp;'.$currencySign;
//			}
            $counter++;
            if ($counter == 1) {
                $formatted = 'poniżej '.$formattedEnd;
            } elseif ($counter == $limit) {
                $formatted = 'powyżej '.$formattedStart;
            } else {
                $formatted = 'od '.$formattedStart . " do " . $formattedEnd;
            }

            $rangeItemArray = array(
                                  'start' => $start,
                                  'end' => $end,
                                  'count' => 0,
                                  'formatted' => $formatted,
                                  'value' => $start.' TO '.$end,
                              );
            $items = $this->getItems();
            foreach ($items as $price => $count) {
                $price = floor($price);
                if (floatval($price) >= floatval($start) && floatval($price) <= floatval($end)) {
                    $rangeItemArray['count'] = ($rangeItemArray['count'] + $count);
                }
            }

            $appliedPriceRanges[] = $rangeItemArray;
        }

        return $appliedPriceRanges;
    }

    public function getPriceFormat($price)
    {
        $formattedPrice = $price;
        $currencySign = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $currencyPositionSetting = Mage::helper('solrsearch')->getSetting('currency_position');

        if ($currencyPositionSetting < 1) {
            //After
            $formattedPrice = $price.'&nbsp;'.$currencySign;
        } else {
            $formattedPrice = $currencySign.'&nbsp;'.$price;
        }
        return $formattedPrice;
    }

    public function getFacetPriceRanges()
    {
        return $this->applyPriceRangeProductCount();
    }

    public function getFacesUrl($params = array(), $paramss = NULL)
    {
        return $this->getParentBlock()->getFacesUrl($params, $paramss);
    }
    public function isRangeActive($value) {
        $request = $this->getRequest()->getParam('price_facet');
        return false;
    }
}