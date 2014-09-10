<?php

$path = Mage::getBaseDir('media') . DS . Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner::BANNER_RESIZE_DIRECTORY . DS;
if (!file_exists($path)) {
    mkdir($path, 0777, true);
}