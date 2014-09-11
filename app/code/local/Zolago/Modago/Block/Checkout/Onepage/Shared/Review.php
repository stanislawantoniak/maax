<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Review
	extends Mage_Checkout_Block_Onepage_Abstract
{

    protected $_block;
    protected function _getCartBlock($type) {
        if (empty($this->_block[$type])) {
            $block = $this->_assignBlock($type);
            $this->_block[$type] = $block;
        }
        return $this->_block[$type];
    }
    
    //{{{ 
    /**
     * 
     * @param string $type
     * @return Mage_Core_Block_Abstract
     */

    //}}}
    public function _assignBlock($type) 
    {
        $block = null;
        switch ($type) {
            case 'cart':
                $block = $this->getLayout()->createBlock('checkout/cart');            
                $block->addItemRender('default','checkout/cart_item_renderer','checkout/cart/item/review.phtml');                
                break;
            case 'split':
                $block = $this->getLayout()->createBlock('udsplit/cart_vendor');            
                break;
        }
        return $block;
    }
	public function getStep3Sidebar()
    {
        return $this->getLayout()->createBlock("cms/block")->setBlockId("checkout-right-column-step-3")->toHtml();
    }
    
    
    //{{{ 
    /**
     * @return array
     */

    //}}}
    public function getItems() {
        $block = $this->_getCartBlock('cart');
        return $block->getItems();
    }
    
    
    //{{{ 
    /**
     * 
     * @param Mage_Sales_Model_Quote_Item
     * @return array
     */

    //}}}
    public function getItemHtml($item) {
        $block = $this->_getCartBlock('cart');
        return $block->getItemHtml($item);
    }
    
    //{{{ 
    /**
     * 
     * @return array
     */
     public function getOptionList() {
         $block = $this->_getCartBlock('split');
         echo '**'.get_class($block);
         return $block->getOptionList();
     }
    //}}}
} 