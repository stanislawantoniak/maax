<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 21.08.2014
 */

class Zolago_Modago_Block_Solrsearch_Faces extends Zolago_Solrsearch_Block_Faces
{
    /**
     * Returns renderer for category filter from Modago theme.
     *
     * @return string
     */
    protected function _getCategoryRenderer()
    {
        return "zolagomodago/solrsearch_faces_category";
    }
} 