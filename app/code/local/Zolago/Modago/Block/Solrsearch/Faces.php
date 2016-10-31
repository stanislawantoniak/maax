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

    protected function _rewriteBlockType($block)
    {
        switch($block) {
            case "zolagosolrsearch/faces_enum_droplist":
                $block = "zolagomodago/solrsearch_faces_enum_droplist";
                break;

            case "zolagosolrsearch/faces_enum_longlist":
                $block = "zolagomodago/solrsearch_faces_enum_longlist";
                break;

            case "zolagosolrsearch/faces_price":
                $block = "zolagomodago/solrsearch_faces_price";
                break;

            case "zolagosolrsearch/faces_enum_size":
                $block = "zolagomodago/solrsearch_faces_enum_size";
                break;
                
            case "zolagosolrsearch/faces_enum_icon":
                $block = "zolagomodago/solrsearch_faces_enum_icon";
                break;

            case "zolagosolrsearch/faces_flag":
                $block = "zolagosolrsearch/faces_flag";
                break;
            case "zolagosolrsearch/faces_rating":
                $block = "zolagosolrsearch/faces_rating";
                break;

            default:
                $block = "zolagomodago/solrsearch_faces_enum_droplist";
        }

        return $block;
    }

}