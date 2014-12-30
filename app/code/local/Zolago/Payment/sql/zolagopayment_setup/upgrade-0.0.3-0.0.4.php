<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagopayment/provider');



$installer->getConnection()->query("
INSERT INTO `". $table ."` (`provider_id`, `code`, `is_active`, `name`, `created_at`, `updated_at`, `type`) VALUES
(1, 'mt', 1, 'mTransfer', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(2, 'inteligo', 1, 'Płacę z Inteligo', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(3, 'mbmt', 1, 'mBank multiTransfer', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(4, 'pko', 1, 'Płacę z PKO', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(5, 'bzwbk', 1, 'Przelew 24 Bank Zachodni WBK', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(6, 'ingc', 1, 'ING klienci korporacyjni', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(7, 'mbc', 1, 'Millenium bank klienci korporacyjni', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),

(9, 'kb', 1, 'KB24', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(10, 'pko', 1, 'PKO bank polski', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(11, 'ca', 1, 'Credit Agricole', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(12, 'ipkonet', 1, 'Płacę z IPKONET', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(13, 'bph', 1, 'Przelew z Bank BPH', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(14, 'mr', 1, 'Moje rachunki', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(15, 'ukash', 1, 'Ukash use your cash online', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(16, 'mpay', 1, 'mPay', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(17, 'pb', 1, 'Plus bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(18, 'bgz', 1, 'Bank BGŻ', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(19, 'zabka', 1, 'Żabka', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(20, 'bnp', 1, 'BNP paribas', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(21, 'v', 1, 'Volkswagen bank direct', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(22, 'kp', 1, 'Kantor Polski SA', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(23, 'pekao', 1, 'Bank Pekao Pekao24Przelew', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(24, 'ing', 1, 'Płać z ING', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(25, 'bs', 1, 'Bank Spółdzielczy', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(26, 'mb', 1, 'Millenium bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(27, 'ab', 1, 'Alior bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(28, 'ch', 1, 'City handlowy', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(29, 'rpb', 1, 'Raiffeisen polbank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(30, 'r', 1, 'R-Przelew', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(31, 'm', 1, 'Meritum bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(32, 'tb', 1, 'Toyota bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(33, 'bos', 1, 'BOŚ bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(34, 'sc', 1, 'SkyCash', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(35, 'dotpayraty', 1, 'Zakupy na raty z dotpay', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(36, 'eb', 1, 'Eurobank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(37, 'gb', 1, 'Getinbank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(38, 'db', 1, 'Deutsche bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(39, 'tmb', 1, 'T-Mobile bank', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(40, 'bp', 1, 'Bank Pocztowy', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(41, 'bdnbnord', 1, 'Bank DnB NORD', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(42, 'iko', 1, 'Płacę z IKO', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(43, 'peopay', 1, 'PeoPay', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(44, 'idea', 1, 'Idea', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(45, 'mbmr', 1, 'mBank mRaty', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(46, 'p', 1, 'Pocztowy24', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(47, 'paypal', 1, 'PayPal', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'gateway'),
(48, 'mcm', 1, 'MasterCard Mobile', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'cc'),
(49, 'visa_mc', 1, 'Visa i MasterCard', '2014-12-28 23:00:00', '2014-12-28 23:00:00', 'cc');

");
 
$installer->endSetup();
