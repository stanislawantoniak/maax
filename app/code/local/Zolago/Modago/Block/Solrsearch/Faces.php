<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 21.08.2014
 */

class Zolago_Modago_Block_Solrsearch_Faces extends Zolago_Solrsearch_Block_Faces
{
    protected function _getCategoryRenderer()
    {
        return "zolagomodago/solrsearch_faces_category";
    }
} 