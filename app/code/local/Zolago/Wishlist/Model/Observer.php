<?php
/**
 * zolago wishlist observer
 */

class Zolago_Wishlist_Model_Observer extends Mage_Wishlist_Model_Observer {
    
    /**
     * increase favourite flag
     */
     public function wishlistAddAfter() {
         die('obserwer zadziałał');
     }
}