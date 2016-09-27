<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Data_Layout extends Magpleasure_Blog_Helper_Data
{
    const CACHE_KEY = 'mp_blog_config_layout';
    const CONFIG_PATH_LAYOUT = 'layout/%s';
    const CONFIG_PATH_LAYOUT_VALUE = 'layout/%s/%s';

    /**
     * Retrieves Blocks from Config
     *
     * @param $type 'content' | 'sidebar'
     * @return array
     */
    public function getBlocks($type)
    {
        $values = array();
        $config = $this->_getConfig();
        $layoutKeys = $this
            ->getCommon()
            ->getConfig()
            ->getArrayFromPath(
                sprintf(self::CONFIG_PATH_LAYOUT, $type),
                $config
            );

        foreach ($layoutKeys as $key){

            $value = $this
                ->getCommon()
                ->getConfig()
                ->getArrayFromPath(
                    sprintf(self::CONFIG_PATH_LAYOUT_VALUE, $type, $key),
                    $config,
                    true
                );

            $value['value'] = $key;
            $values[] = $value;
        }

        //TODO: Sort blocks by 'sort_order'

        return $values;
    }
}