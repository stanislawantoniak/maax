<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.09.2014
 */

class Zolago_Modago_Block_Checkout_Address extends Zolago_Modago_Block_Checkout_Abstract
{
    public function getSaveUrl()
    {
        return Mage::getUrl("checkout/onepage/saveAddress");
    }

    public function getPreviousStepUrl()
    {
        return Mage::getUrl("checkout/cart");
    }

    /**
     * @todo Add country id fetching
     * @return string
     */
    public function getCountryId()
    {
        return "PL";
    }
} 