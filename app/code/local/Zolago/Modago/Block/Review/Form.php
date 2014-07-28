<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 23.07.2014
 */

class Zolago_Modago_Block_Review_Form extends Mage_Review_Block_Form
{
    /**
     * Returns current product from registry
     *
     * @return null|Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }
} 