<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagopayment/provider');

$installer->getConnection()->truncateTable($table);

$installer->getConnection()->query("
INSERT INTO `". $table ."` (`provider_id`, `code`, `is_active`, `name`, `created_at`, `updated_at`, `type`) VALUES
(1, 'mt', 1, 'mTransfer', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(2, 'inteligo', 1, 'Płacę z Inteligo', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(3, 'mbmt', 1, 'MultiTransfer', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(4, 'pko', 1, 'Płacę z iPKO', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(5, 'bzwbk', 1, 'Przelew24', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(6, 'ingc', 0, 'ING klienci korporacyjni', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(7, 'mbc', 0, 'Millenium bank klienci korporacyjni', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(9, 'kb', 0, 'KB24', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(10, 'pko', 0, 'PKO bank polski', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(11, 'ca', 0, 'Credit Agricole', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(12, 'ipkonet', 0, 'Płacę z IPKONET', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(13, 'bph', 1, 'Przelew z BPH', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(14, 'mr', 0, 'Moje rachunki', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(15, 'ukash', 0, 'Ukash use your cash online', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(16, 'mpay', 0, 'mPay', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(17, 'pb', 0, 'Plus bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(18, 'bgz', 0, 'Bank BGŻ', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(19, 'zabka', 0, 'Żabka', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(20, 'bnp', 0, 'BNP paribas', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(21, 'v', 0, 'Volkswagen bank direct', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(22, 'kp', 0, 'Kantor Polski SA', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(23, 'pekao', 1, 'Pekao24Przelew', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(24, 'ing', 1, 'Płać z ING', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(25, 'bs', 0, 'Bank Spółdzielczy', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(26, 'mb', 1, 'Millennium - Płatności Internetowe', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(27, 'ab', 1, 'Płacę z Alior Bankiem', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(28, 'ch', 1, 'Płacę z Citi Handlowy', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(29, 'rpb', 0, 'Raiffeisen polbank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(30, 'r', 1, 'R-Przelew', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(31, 'm', 0, 'MeritumBank Przelew', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(32, 'tb', 1, 'Pay Way Toyota Bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(33, 'bos', 1, 'Płać z BOŚ', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(34, 'sc', 0, 'SkyCash', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(35, 'dotpayraty', 0, 'Zakupy na raty z dotpay', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(36, 'eb', 1, 'Eurobank - płatność online', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(37, 'gb', 0, 'Getinbank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(38, 'db', 1, 'Szybkie Płatności Internetowe z Deutsche Bank PBC', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(39, 'tmb', 1, 'Płacę z T-Mobile Usługi Bankowe', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(40, 'bp', 0, 'Bank Pocztowy', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(41, 'bdnbnord', 0, 'Bank DnB NORD', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(42, 'iko', 0, 'Płacę z IKO', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(43, 'peopay', 1, 'PeoPay', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(44, 'idea', 1, 'Płacę z Idea Bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(45, 'mbmr', 0, 'mBank mRaty', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(46, 'p', 1, 'Pocztowy24', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(47, 'paypal', 0, 'PayPal', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(48, 'mcm', 1, 'MasterCard Mobile', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'cc'),
(49, 'visa', 1, 'Visa', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'cc'),
(50, 'mc', 1, 'MasterCard', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'cc');

");


$installer->getConnection()->query("
INSERT INTO `". $table ."` (`provider_id`, `code`, `is_active`, `name`, `created_at`, `updated_at`, `type`) VALUES
(51, 'pbs', 1, 'Płacę z PBS', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(52, 'orange', 1, 'Płacę z Orange', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(53, 'blik', 1, 'BLIK', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(54, 'bps', 1, 'Banki Spółdzielcze', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(55, 'bplus', 1, 'Płacę z Plus Bank', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(56, 'bgetin', 1, 'Getin Bank PBL', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(57, 'noblepay', 1, 'Getin Bank PBL', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(58, 'ideacloud', 1, 'Idea Cloud', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway');

");

$installer->endSetup();
