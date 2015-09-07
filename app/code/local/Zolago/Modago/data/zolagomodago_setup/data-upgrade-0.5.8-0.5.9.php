<?php
$block = Mage::getModel('cms/block')->load("mypromotions-code-how-to-use");
if($block){
    $block->delete();
}
