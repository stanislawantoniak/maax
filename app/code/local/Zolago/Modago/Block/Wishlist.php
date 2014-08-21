<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 29.07.2014
 */

class Zolago_Modago_Block_Wishlist extends Mage_Wishlist_Block_Abstract
{
    public function isInWishlist(Mage_Catalog_Model_Product $item)
    {
        return Mage::helper('zolagowishlist')->productBelongsToMyWishlist($item);
    }
} 