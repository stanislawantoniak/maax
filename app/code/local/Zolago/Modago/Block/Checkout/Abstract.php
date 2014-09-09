<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.09.2014
 */

abstract class Zolago_Modago_Block_Checkout_Abstract extends Mage_Checkout_Block_Onepage
{
    public function getStep1Sidebar()
    {
        return $this->getLayout()->createBlock("cms/block")->setBlockId("checkout-right-column-step-1")->toHtml();
    }
} 