<?php

class Zolago_Solrsearch_Block_Faces_Price extends Zolago_Solrsearch_Block_Faces_Abstract
{
    public function __construct()
    {
        $this->setTemplate('zolagosolrsearch/standard/searchfaces/price.phtml');
    }


    public function _prepareLayout()
    {
        //Load js for price slider
        $usePriceSilder = (int)Mage::helper('solrsearch')->getSetting('use_price_slider');
        if ($usePriceSilder > 0) {
            $this->setTemplate('solrsearch/standard/searchfaces/price-slider.phtml');
            $head = $this->getLayout()->getBlock('head');
            if ($head) {
                $head->addJs('solrsearch/slider.js');
            }
        }

        return parent::_prepareLayout();
    }

    public function getSolrData() {
        return $this->getParentBlock()->getSolrData();
    }

    /**
     * Calculate price ranges
     * @param array $priceRanges
     * @param decimal $min
     * @param decimal $max
     * @return array:
     */
    protected function calculatePriceRanges()
    {


        $solrData = $this->getSolrData();

        $priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();

        $priceRanges = array();


        // category
        $category = Mage::registry('current_category');
        $range = 0;
        if ($category) {
            $data = $category->getData();
            if (!empty($data['filter_price_range'])) {
                $range = $data['filter_price_range'];
            }
        }
        $min = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['min'])) {
            $min = $solrData['stats']['stats_fields'][$priceFieldName]['min'];
        }

        $max = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['max'])) {
            $max = $solrData['stats']['stats_fields'][$priceFieldName]['max'];
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
    }

    protected function applyPriceRangeProductCount() {
        $priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();

        $priceRanges = $this->calculatePriceRanges();

        $appliedPriceRanges = array();
        $solrData = $this->getSolrData();
        $priceFacets = array();

        if ( isset($solrData['facet_counts']['facet_fields'][$priceFieldName]) && is_array($solrData['facet_counts']['facet_fields'][$priceFieldName])) {
            $priceFacets = $solrData['facet_counts']['facet_fields'][$priceFieldName];
        }

        $currencySign = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        $currencyPositionSetting = $this->helper('solrsearch')->getSetting('currency_position');

        foreach ($priceRanges as $range) {
            $start = floor(floatval($range['start']));
            $end = ceil(floatval($range['end']));

            $formattedStart = Mage::app()->getStore()->getCurrentCurrency()->format($start, null, false);
            $formattedEnd = Mage::app()->getStore()->getCurrentCurrency()->format($end, null, false);

//			if ($currencyPositionSetting > 0)
//			{
//				$formatted = $currencySign.'&nbsp;'.$start.' - '.$currencySign.'&nbsp;'.$end;
//			}else {
//				$formatted = $start.'&nbsp;'.$currencySign.' - '.$end.'&nbsp;'.$currencySign;
//			}

            $formatted = $formattedStart . " - " . $formattedEnd;

            $rangeItemArray = array(
                                  'start' => $start,
                                  'end' => $end,
                                  'count' => 0,
                                  'formatted' => $formatted,
                                  'value' => $start.' TO '.$end,
                              );
            foreach ($priceFacets as $price => $count) {
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
}