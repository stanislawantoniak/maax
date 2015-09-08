<?php

/**
 * Class Zolago_Solrsearch_Block_Faces_Enum
 */
class Zolago_Solrsearch_Block_Faces_Enum extends Zolago_Solrsearch_Block_Faces_Abstract
{
    public function __construct()
    {
        $this->setTemplate('zolagosolrsearch/standard/searchfaces/enum.phtml');
    }
}