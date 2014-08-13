<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 29.07.2014
 */

class Zolago_Modago_Block_Wishlist_Links extends Mage_Wishlist_Block_Abstract
{
    public function getAddToWishlstUrl($item)
    {
        return '/wishadd';
    }

    public function getRemoveFromWishlstUrl($item)
    {
        return '/wishremove';
    }
} 