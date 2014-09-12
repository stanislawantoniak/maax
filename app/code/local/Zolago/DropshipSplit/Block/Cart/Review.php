<?php
/**
 * for checkout review footer
 */

class Zolago_DropshipSplit_Block_Cart_Review extends Zolago_DropshipSplit_Block_Cart_Vendor {
    protected function _construct() {
        $this->setTemplate('unirgy/dsplit/cart/review.phtml');
    }
}
