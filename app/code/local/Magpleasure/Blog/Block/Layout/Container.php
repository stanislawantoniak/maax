<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Layout_Container extends Mage_Core_Block_Abstract
{
    /**
     * Blog Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Set Type
     * sidebar/content
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $config = $this->_helper()->getLayoutConfig();

        $blocks = $config->getBlocks($type);

        if ($blocks && is_array($blocks) && count($blocks)){

            foreach ($blocks as $data){

                $object = new Varien_Object($data);

                if ($object->getFrontendBlock()){

                    $alias = $object->getValue();
                    $name = "mp.blog.{$type}.{$alias}";
                    $block = $this
                        ->getLayout()
                        ->createBlock(
                            $object->getFrontendBlock(),
                            $name
                        );

                    if ($block){
                        $this->append($block, $alias);

                    }
                }
            }
        }

        return $this;
    }
}