<?php

class Varien_Data_Form_Element_Textareagh extends Varien_Data_Form_Element_Textarea
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('textareagh');
        $this->setExtType('textareagh');

        if (isset($attributes["rows"])) {
            $this->setRows($attributes["rows"]);
        }
        if (isset($attributes["cols"])) {
            $this->setCols($attributes["cols"]);
        }

    }
}