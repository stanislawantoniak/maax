<?php

class Zolago_Solrsearch_Block_Faces_Superattribute extends Zolago_Solrsearch_Block_Faces_Enum
{
    public function __construct()
    {
        $this->setTemplate('zolagosolrsearch/standard/searchfaces/enum/superattribute.phtml');
    }

    public function getCanShow()
    {
        //@todo
        return true;
    }


    public function getItems() {
        if(!$this->hasData("items")) {
            $hiddenItems = array();
            $items = $this->getAllItems();
            krumo($items);
            if($this->getFilterModel()) {
                $items =  $this->filterAndSortOptions(
                    $this->getAllItems(),
                    $this->getFilterModel(),
                    $hiddenItems
                );
            }

            $this->setData("items", $items);
            $this->setData("hidden_items", $hiddenItems);
        }
        return $this->getData("items");
    }
}