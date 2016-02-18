<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    UPDATE cms_block 
        SET content = REPLACE(content,'http://','//')
    WHERE 
        identifier like 'navigation-dropdown%';
    "); 
$installer->run("
    UPDATE cms_block 
        SET content = REPLACE(content,'http://','//')
    WHERE 
        identifier in ('mypromotions_fake_coupons','vendor_regulations_accept','footer-social-icons-website');
    "); 
    

$installer->endSetup();