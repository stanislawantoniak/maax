<?php

$path = Mage::getBaseDir('media') . DS . 'banners' . DS;
if (!file_exists($path)) {
    mkdir($path, 0777, true);
}