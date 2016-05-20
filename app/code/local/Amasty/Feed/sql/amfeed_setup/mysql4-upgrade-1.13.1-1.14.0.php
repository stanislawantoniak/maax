<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
    $installer = $this;
    $installer->startSetup();
    
    $installer->run("
                insert  into `{$this->getTable('amfeed/template')}` 
                (`status`,`generated_at`,`delivery_at`,`type`,`title`,`filename`,`mode`,`cond_stock`,`cond_disabled`,`cond_type`,`cond_advanced`,`xml_header`,`xml_body`,`xml_footer`,`xml_item`,`csv`,`csv_header`,`csv_enclosure`,`csv_delimiter`,`frm_date`,`frm_price`,`frm_url`,`frm_image_url`,`frm_dont_use_category_in_url`,`frm_use_parent`,`default_image`,`delivery_type`,`delivered`,`send_email`,`ftp_host`,`ftp_user`,`ftp_pass`,`ftp_folder`,`ftp_is_passive`,`info_total`,`info_cnt`,`info_errors`,`freq`,`on_days`,`hour_from`,`hour_to`,`error_email`,`max_images`) values 
                ('0','2013-10-03 21:16:44','0000-00-00 00:00:00','2','TheFind.com','TheFind.com','0','1','1','simple,grouped,configurable,virtual,bundle,downloadable','a:3:{s:4:\"attr\";a:0:{}s:2:\"op\";a:1:{i:0;s:2:\"eq\";}s:3:\"val\";a:1:{i:0;s:0:\"\";}}','','','',NULL,'a:12:{s:4:\"name\";a:6:{i:0;s:5:\"Title\";i:1;s:11:\"Description\";i:2;s:10:\"Image_Link\";i:3;s:8:\"Page_URL\";i:4;s:5:\"Price\";i:5;s:3:\"SKU\";}s:6:\"before\";a:7:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";}s:4:\"type\";a:6:{i:0;s:9:\"attribute\";i:1;s:9:\"attribute\";i:2;s:9:\"attribute\";i:3;s:9:\"attribute\";i:4;s:9:\"attribute\";i:5;s:9:\"attribute\";}s:4:\"attr\";a:6:{i:0;s:4:\"name\";i:1;s:11:\"description\";i:2;s:5:\"image\";i:3;s:3:\"url\";i:4;s:5:\"price\";i:5;s:3:\"sku\";}s:3:\"txt\";a:7:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";}s:9:\"meta_tags\";a:7:{i:0;s:10:\"meta_title\";i:1;s:10:\"meta_title\";i:2;s:10:\"meta_title\";i:3;s:10:\"meta_title\";i:4;s:10:\"meta_title\";i:5;s:10:\"meta_title\";i:6;s:10:\"meta_title\";}s:6:\"images\";a:7:{i:0;s:7:\"image_1\";i:1;s:7:\"image_1\";i:2;s:7:\"image_1\";i:3;s:7:\"image_1\";i:4;s:7:\"image_1\";i:5;s:7:\"image_1\";i:6;s:7:\"image_1\";}s:5:\"after\";a:7:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";}s:6:\"format\";a:7:{i:0;s:5:\"as_is\";i:1;s:10:\"strip_tags\";i:2;s:5:\"as_is\";i:3;s:5:\"as_is\";i:4;s:5:\"price\";i:5;s:5:\"as_is\";i:6;s:5:\"as_is\";}s:12:\"image_format\";a:7:{i:0;s:4:\"base\";i:1;s:4:\"base\";i:2;s:4:\"base\";i:3;s:4:\"base\";i:4;s:4:\"base\";i:5;s:4:\"base\";i:6;s:4:\"base\";}s:6:\"length\";a:7:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";}s:6:\"parent\";a:7:{i:0;s:2:\"no\";i:1;s:2:\"no\";i:2;s:2:\"no\";i:3;s:2:\"no\";i:4;s:2:\"no\";i:5;s:2:\"no\";i:6;s:2:\"no\";}}','1','110','9','','0','0','0','0','0','0','0','0',NULL,NULL,NULL,NULL,NULL,'0','113','113','0','0','','0','0',NULL,'0');
    ");
    $installer->endSetup();
?>
