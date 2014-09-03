<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 25.08.2014
 */

class Zolago_Modago_Block_Solrsearch_Faces_Price extends Zolago_Solrsearch_Block_Faces_Price
{
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        //Load js for price slider
        $usePriceSilder = (int)Mage::helper('solrsearch')->getSetting('use_price_slider');
        if ($usePriceSilder > 0) {
            $this->setTemplate('zolagosolrsearch/standard/searchfaces/price-slider.phtml');
        }
        $head = $this->getLayout()->getBlock("head");
        if ($head) {
            $head->removeItem("js", "solrsearch/slider.js");
        }

        return $this;
    }
} 