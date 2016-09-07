<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Bundle
    extends Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
{

    protected static $_attributes = array();


    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setTemplate("zolagopo/vendor/po/item/renderer/bundle.phtml");
    }

    public function getBundleText(Zolago_Po_Model_Po_Item $item)
    {
        return $item->getBundleText();
    }

    public function getBundleFormattedText(Zolago_Po_Model_Po_Item $item)
    {
        $out = $this->getBundleText($item);
        if ($out) {
            return $out;
        }
        return "";
    }

    public function getBundleHtml(Zolago_Po_Model_Po_Item $item)
    {
        $text = $this->getBundleFormattedText($item);
        if ($text) {
            return " <em class=\"text-muted\">" . $text . "</em>";
        }
        return "";
    }

    /**
     * @return bool
     */
    public function getIsEditable()
    {
        //Refs#2259 5. zamówienie w portalu sprzedawcy - w linijce mają się pokazywać składniki,
        // ma działać usuwanie całego bundla, nie edytujemy składników, nie ma edycji ceny, ilości
        return false;
    }
}
