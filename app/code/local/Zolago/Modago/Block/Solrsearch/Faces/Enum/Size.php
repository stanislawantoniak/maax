<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 25.08.2014
 */

class Zolago_Modago_Block_Solrsearch_Faces_Enum_Size extends Zolago_Solrsearch_Block_Faces_Enum_Size
{
    public function __construct()
    {
        $this->setTemplate("zolagosolrsearch/standard/searchfaces/enum/size.phtml");
    }
} 