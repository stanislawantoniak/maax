<?php

$path = Mage::getBaseDir('var').DS.'plupload'.DS;
if (!file_exists($path)) {
    mkdir($path,0777,true);
}