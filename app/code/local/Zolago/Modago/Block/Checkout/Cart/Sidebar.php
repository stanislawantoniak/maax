<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 13.08.2014
 */

class Zolago_Modago_Block_Checkout_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    /**
     * Return total shipping cost
     *
     * @todo Implementation
     * @return float
     */
    public function getShippingTotal()
    {
        return 99.00;
    }
} 