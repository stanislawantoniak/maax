<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Model_Subscribers extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/subscribers');
    }

    function cron() {

        $this->importCustomers();

        Mage::log('started', null, 'fidelitas-sync-subs.log', true);

        $egoi = Mage::getModel('fidelitas/egoi');

        $lists = Mage::getModel('fidelitas/lists')
                ->getCollection()
                ->getData();

        $limit = 1000;
        foreach ($lists as $list) {
            $end = false;
            $start = 0;
            do {
                $egoi->addData(array('listID' => $list['listnum'], 'subscriber' => 'all_subscribers', 'limit' => $limit))->setData('start', $start);

                try {
                    $subscribers = $egoi->getSubscriberData()->getData();
                } catch (Exception $e) {
                    // I Know!!!!!
                    #$subscribers = $fid->getSubscriberData()->getData();
                }

                if (isset($subscribers[0]['ERROR'])) {
                    break;
                }

                if (count($subscribers[0]['subscriber']) < $limit) {
                    $end = true;
                }

                foreach ($subscribers[0]['subscriber'] as $subscriber) {

                    $subscriberData = array_change_key_case($subscriber, CASE_LOWER);

                    if ($subscriberData['status'] != '1') {
                        $jaExiste = Mage::getModel('fidelitas/subscribers')->load($subscriberData['uid'], 'uid');
                        if ($jaExiste->getId()) {
                            $jaExiste->setData('inCron', true)->delete();
                        }
                        continue;
                    }

                    if (strlen($subscriberData['birth_date']) > 0) {
                        $subscriberData['dob'] = $subscriberData['birth_date'];
                    }

                    $jaExiste = Mage::getModel('fidelitas/subscribers')->load($subscriberData['uid'], 'uid');
                    if ($jaExiste->getId()) {
                        $subscriberData['subscriber_id'] = $jaExiste->getId();
                    }
                    try {
                        Mage::getModel('fidelitas/subscribers')->setData($subscriberData)->setData('inCron', true)->save();
                    } catch (Exception $e) {

                    }
                }

                $start +=$limit;
            } while ($end === false);
        }

        Mage::log('end', null, 'fidelitas-sync-subs.log', true);

//        $localSubscribers = Mage::getModel('fidelitas/subscribers')
//                ->getCollection()
//                ->addFieldToFilter('status', '2');
//
//        //Let's delete lists that where removed from the e-goi servers
//        foreach ($localSubscribers as $subscriber) {
//            $subscriber->setData('inCron', TRUE)->delete();
//        }

        Mage::log('start', null, 'fidelitas-sync-core-subs.log', true);
        $this->importCoreNewsletterSubscribers();
        Mage::log('end', null, 'fidelitas-sync-core-subs.log', true);
    }

    public function importCustomers() {
        Mage::log('started', null, 'fidelitas-sync-cust.log', true);
        $client = Mage::getModel('fidelitas/lists')->getClientList();

        if (!$client) {
            return false;
        }

        $lastClientId = (int) Mage::getModel('fidelitas/subscribers')
                        ->getCollection()
                        ->addFieldToFilter('list', $client->getListnum())
                        ->setOrder('customer_id', 'DESC')
                        ->getFirstItem()
                        ->getCustomerId();

        $cellphoneField = Mage::getStoreConfig('fidelitas/config/cellphone');
        $customers = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname')
                ->addAttributeToFilter('entity_id', array('gt' => $lastClientId))
                ->joinAttribute('country_id', 'customer_address/country_id', 'default_billing', null, 'left')
                ->joinAttribute('dob', 'customer/dob', 'entity_id', null, 'left')
                ->joinAttribute($cellphoneField, 'customer_address/' . $cellphoneField, 'default_billing', null, 'left');


        foreach ($customers as $customer) {

            $data['status'] = 1;
            $data['customer_id'] = $customer->getCustomerId();
            $data['email'] = $customer->getData('email');
            $data['first_name'] = $customer->getData('firstname');
            $data['last_name'] = $customer->getData('lastname');
            $data['cellphone'] = $customer->getData('cellphone');
            $data['list'] = $client->getListnum();
            if ($customer->getDob()) {
                $data['birth_date'] = $customer->getDob();
            }

            $cellphoneField = Mage::getStoreConfig('fidelitas/config/cellphone');
            if (strlen($customer->getData($cellphoneField)) > 5) {
                $customer->setData('cellphone', $this->getPrefixForCountry($customer->getCountryId()) . '-' . preg_replace('/\D/', '', $customer->getData($cellphoneField)));
            }

            try {
                Mage::getModel('fidelitas/subscribers')->setData($data)->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        Mage::log('ended', null, 'fidelitas-sync-cust.log', true);
    }

    public function findCustomer($value, $attribute = 'entity_id') {

        $cellphoneField = Mage::getStoreConfig('fidelitas/config/cellphone');
        $customers = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname')
                ->addAttributeToSelect('store_id')
                ->addAttributeToSelect('dob')
                ->addAttributeToFilter($attribute, $value)
                ->joinAttribute('country_id', 'customer_address/country_id', 'default_billing', null, 'left')
                ->joinAttribute($cellphoneField, 'customer_address/' . $cellphoneField, 'default_billing', null, 'left');

        if ($customers->count() == 1) {
            $customer = $customers->getFirstItem();
            if (strlen($customer->getData($cellphoneField)) > 5) {
                $customer->setData('cellphone', $this->getPrefixForCountry($customer->getCountryId()) . '-' . preg_replace('/\D/', '', $customer->getData($cellphoneField)));
            }

            return $customer;
        }

        return false;
    }

    public function importCoreNewsletterSubscribers() {

        $news = Mage::getModel('newsletter/subscriber')
                ->getCollection()
                ->addFieldToFilter('subscriber_status', 1);
        foreach ($news as $n) {
            if (!$n->getStoreId()) {
                continue;
            }

            $data = array();
            $list = Mage::getModel('fidelitas/lists')->getListForStore($n->getStoreId());

            if (!$list->getId()) {
                continue;
            } else {
                $listData = $list->getData();
            }

            if ($this->subscriberExists('email', $n->getSubscriberEmail(), $listData['listnum'])) {
                continue;
            }

            $customer = $this->findCustomer($n->getCustomerId());

            $data['email'] = $n->getData('subscriber_email');
            $data['status'] = 1;
            $data['customer_id'] = $n->getCustomerId();

            if ($customer) {
                $data['email'] = $customer->getData('email');
                $data['first_name'] = $customer->getData('firstname');
                $data['last_name'] = $customer->getData('lastname');
                $data['cellphone'] = $customer->getData('cellphone');
            }

            $data['list'] = $listData['listnum'];

            try {
                Mage::getModel('fidelitas/subscribers')->setData($data)->save();
            } catch (Exception $e) {
                #$this->_getSession()->addNotice($e->getMessage());
            }
        }
    }

    public static function getPhonePrefixs() {
        $phones = self::phonePrefixsList();

        $return = array();
        $return[''] = Mage::helper('fidelitas')->__('-- Please Choose --');
        foreach ($phones as $value) {
            $return[$value[2]] = ucwords(strtolower($value[0])) . ' (+' . $value[2] . ')';
        }

        asort($return);

        return $return;
    }

    public function getPrefixForCountry($countryCode) {

        $phones = self::phonePrefixsList();
        foreach ($phones as $phone) {

            if ($phone[1] == $countryCode) {
                return $phone[2];
            }
        }

        return '';
    }

    public function subscriberExists($field, $value, $list) {

        $model = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter($field, $value)
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('list', $list);

        if ($model->count() != 1) {
            return false;
        }

        return $model->getFirstItem();
    }

    public function save() {

        $data = $this->getData();

        if (!isset($data['listID']) && isset($data['list'])) {
            $data['listID'] = $data['list'];
        }
        if (!isset($data['list']) && isset($data['listID'])) {
            $data['list'] = $data['listID'];
        }
        $customer = $this->findCustomer($data['email'], 'email');

        if ($customer) {
            $data['customer_id'] = $customer->getId();
            $data['birth_date'] = $customer->getData('dob');
            $data['first_name'] = $customer->getData('firstname');
            $data['last_name'] = $customer->getData('lastname');

            if ($customer->getData('cellphone')) {
                $data['cellphone'] = $customer->getData('cellphone');
            }
        }

        $this->addData($data);

        $info = $this->subscriberExists('email', $this->getEmail(), $this->getData('list'));

        if ($info && isset($data['inCallback'])) {
            $this->setId($info->getId());
        }

        if ($this->getData('inCron') === true && !Mage::registry('fidelitas_first_run')) {
            return parent::save();
        }

        $model = Mage::getModel('fidelitas/egoi');

        if ($info) {
            $data['subscriber'] = $info->getUid();
            $this->setId($info->getId());
        } elseif ($info = $this->subscriberExists('subscriber_id', $this->getId(), $this->getData('list'))) {
            $data['subscriber'] = $info->getUid();
            $this->setId($info->getId());
        }

        $model->addData($data);
        $this->addData($data);


        if ($this->getId()) {
            $old = Mage::getModel('fidelitas/subscribers')->load($this->getId());

            if ($this->getData('list') != $old->getData('list')) {

                $modelDelete = Mage::getModel('fidelitas/egoi');
                $dataDelete = array();
                $dataDelete['listID'] = $old->getList();
                $dataDelete['subscriber'] = $old->getUid();
                $modelDelete->setData($dataDelete)->removeSubscriber();
                Mage::dispatchEvent('fidelitas_subscriber_remove', $data);

                $result = $model->addSubscriber();
                Mage::dispatchEvent('fidelitas_subscriber_add', $data);
                if (isset($result['uid'])) {
                    $this->setData('uid', $result->getData('uid'));
                    #$listUpdate = Mage::getModel('fidelitas/lists')->load($this->getData('list'), 'listnum');
                    #$listUpdate->setData('subs_activos', $listUpdate->getData('subs_activos') + 1)->save();
                }
                return parent::save();
            }
        }

        if ($this->getId()) {
            if ($model->getData('uid')) {
                $model->setData('subscriber', $model->getData('uid'));
            }

            $result = $model->editSubscriber();
            Mage::dispatchEvent('fidelitas_subscriber_edit', $data);
        } else {
            $result = $model->setData('status', 1)->addSubscriber();
            Mage::dispatchEvent('fidelitas_subscriber_add', $data);
            if (isset($result['uid'])) {
                $this->setData('uid', $result->getData('uid'));

                #$listUpdate = Mage::getModel('fidelitas/lists')->load($this->getData('list'), 'listnum');
                #$listUpdate->setData('subs_activos', $listUpdate->getData('subs_activos') + 1)->save();
            }
        }

        return parent::save();
    }

    public function getSubscribersInfo($field, $ids, $lisId) {

        $model = $this->getCollection()
                ->addFieldToSelect($field)
                ->addFieldToFilter('list', $lisId)
                ->addFieldToFilter('customer_id', array('in' => $ids));

        $result = array();
        foreach ($model as $subscriber) {
            if (strlen($subscriber->getData($field)) > 0) {
                $result[] = $subscriber->getData($field);
            }
        }

        return $result;
    }

    public function processDeletedCustomer($customer) {

        $subs = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId());
        foreach ($subs as $sub) {
            $sub->setData('customer_id', 0)->save();
        }
    }

    public function getCustomerLists($customerId) {

        $list = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('customer_id', $customerId)
                ->getData();

        $returnList = array();
        foreach ($list as $ret) {
            $returnList[] = $ret['list'];
        }

        return $returnList;
    }

    public function removeCustomerFromList($customerId, $listId) {

        $customers = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToSelect('uid')
                ->addFieldToFilter('list', $listId)
                ->addFieldToFilter('customer_id', $customerId);


        if ($customers->count() != 1) {
            return false;
        }

        $customer = $customers->getFirstItem()->getData();

        $data = array();
        $data['list'] = $listId;
        $data['uid'] = $customer['uid'];

        Mage::getModel('fidelitas/subscribers')->load($customer['uid'], 'uid')->addData($data)->delete();
    }

    public function addCustomerToList($customerId, $listId) {

        $data = array();

        $customer = $this->findCustomer($customerId);

        if ($customer) {
            $data['status'] = '1';
            $data['customer_id'] = $customerId;
            $data['email'] = $customer->getData('email');
            $data['birth_date'] = $customer->getData('dob');
            $data['first_name'] = $customer->getData('firstname');
            $data['last_name'] = $customer->getData('lastname');
            $data['cellphone'] = $customer->getData('cellphone');

            if (substr($data['cellphone'], -1) == '-') {
                unset($data['cellphone']);
            }

            if ($this->subscriberExists('email', $customer->getEmail(), $listId)) {
                return true;
            }
        } else {
            return false;
        }

        $data['list'] = $listId;

        return Mage::getModel('fidelitas/subscribers')->setData($data)->save();
    }

    public function delete() {

        $model = Mage::getModel('fidelitas/egoi');

        $data = array();
        $data['listID'] = $this->getList();
        $data['subscriber'] = $this->getUid();

        if (!$this->getData('inCron')) {
            $model->setData($data)->removeSubscriber();
        }
        return parent::delete();
    }

    public function updateFromNewsletterCore($event) {
        $subscriber = $event->getDataObject();
        $storeId = $subscriber->getStoreId();
        $email = $subscriber->getSubscriberEmail();
        $customerId = $subscriber->getCustomerId();
        $subscriber->setImportMode(true);

        try {
            $list = Mage::getModel('fidelitas/lists')->getListForStore($storeId);

            if (!$list->getId()) {
                return false;
            } else {
                $listId = $list->getListnum();
            }

            if ($subscriber->getSubscriberStatus() == 1 && $subscriber->getIsStatusChanged()) {

                if ($customerId) {
                    $this->addCustomerToList($customerId, $listId);
                } else {
                    $data = array();
                    $data['list'] = $listId;
                    $data['email'] = $email;
                    #$data['status'] = Mage::getStoreConfig('fidelitas/subscription/confirmation') == 0 ? 1 : 0;
                    $data['status'] = 1;
                    $this->setData($data)->save();
                }
            } elseif ($subscriber->getSubscriberStatus() == 3 && $subscriber->getIsStatusChanged()) {

                $sub = $this->getCollection()
                        ->addFieldToFilter('email', $email)
                        ->addFieldToFilter('list', $listId);

                if ($sub->count() == 1) {
                    $sub->getFirstItem()->delete();
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public static function phonePrefixsList() {

        return array(array('CANADA', 'CA', '1'),
            array('PUERTO RICO', 'PR', '1'),
            array('UNITED STATES', 'US', '1'),
            array('ARMENIA', 'AM', '7'),
            array('KAZAKHSTAN', 'KZ', '7'),
            array('RUSSIAN FEDERATION', 'RU', '7'),
            array('EGYPT', 'EG', '20'),
            array('SOUTH AFRICA (Zuid Afrika)', 'ZA', '27'),
            array('GREECE', 'GR', '30'),
            array('NETHERLANDS', 'NL', '31'),
            array('BELGIUM', 'BE', '32'),
            array('FRANCE', 'FR', '33'),
            array('SPAIN (España)', 'ES', '34'),
            array('HUNGARY', 'HU', '36'),
            array('ITALY', 'IT', '39'),
            array('ROMANIA', 'RO', '40'),
            array('SWITZERLAND (Confederation of Helvetia)', 'CH', '41'),
            array('AUSTRIA', 'AT', '43'),
            array('GREAT BRITAIN (United Kingdom)', 'GB', '44'),
            array('UNITED KINGDOM', 'GB', '44'),
            array('DENMARK', 'DK', '45'),
            array('SWEDEN', 'SE', '46'),
            array('NORWAY', 'NO', '47'),
            array('POLAND', 'PL', '48'),
            array('GERMANY (Deutschland)', 'DE', '49'),
            array('PERU', 'PE', '51'),
            array('MEXICO', 'MX', '52'),
            array('CUBA', 'CU', '53'),
            array('ARGENTINA', 'AR', '54'),
            array('BRAZIL', 'BR', '55'),
            array('CHILE', 'CL', '56'),
            array('COLOMBIA', 'CO', '57'),
            array('VENEZUELA', 'VE', '58'),
            array('MALAYSIA', 'MY', '60'),
            array('AUSTRALIA', 'AU', '61'),
            array('INDONESIA', 'ID', '62'),
            array('PHILIPPINES', 'PH', '63'),
            array('NEW ZEALAND', 'NZ', '64'),
            array('SINGAPORE', 'SG', '65'),
            array('THAILAND', 'TH', '66'),
            array('JAPAN', 'JP', '81'),
            array('KOREA (Republic of [South] Korea)', 'KR', '82'),
            array('VIET NAM', 'VN', '84'),
            array('CHINA', 'CN', '86'),
            array('TURKEY', 'TR', '90'),
            array('INDIA', 'IN', '91'),
            array('PAKISTAN', 'PK', '92'),
            array('AFGHANISTAN', 'AF', '93'),
            array('SRI LANKA (formerly Ceylon)', 'LK', '94'),
            array('MYANMAR (formerly Burma)', 'MM', '95'),
            array('IRAN (Islamic Republic of Iran)', 'IR', '98'),
            array('MOROCCO', 'MA', '212'),
            array('ALGERIA (El Djazaïr)', 'DZ', '213'),
            array('TUNISIA', 'TN', '216'),
            array('LIBYA (Libyan Arab Jamahirya)', 'LY', '218'),
            array('GAMBIA, THE', 'GM', '220'),
            array('SENEGAL', 'SN', '221'),
            array('MAURITANIA', 'MR', '222'),
            array('MALI', 'ML', '223'),
            array('GUINEA', 'GN', '224'),
            array('CÔTE D\'IVOIRE (Ivory Coast)', 'CI', '225'),
            array('BURKINA FASO', 'BF', '226'),
            array('NIGER', 'NE', '227'),
            array('TOGO', 'TG', '228'),
            array('BENIN', 'BJ', '229'),
            array('MAURITIUS', 'MU', '230'),
            array('LIBERIA', 'LR', '231'),
            array('SIERRA LEONE', 'SL', '232'),
            array('GHANA', 'GH', '233'),
            array('NIGERIA', 'NG', '234'),
            array('CHAD (Tchad)', 'TD', '235'),
            array('CENTRAL AFRICAN REPUBLIC', 'CF', '236'),
            array('CAMEROON', 'CM', '237'),
            array('CAPE VERDE', 'CV', '238'),
            array('SAO TOME AND PRINCIPE', 'ST', '239'),
            array('EQUATORIAL GUINEA', 'GQ', '240'),
            array('GABON', 'GA', '241'),
            array('CONGO, REPUBLIC OF', 'CG', '242'),
            array('CONGO, THE DEMOCRATIC REPUBLIC OF THE (formerly Zaire)', 'CD', '243'),
            array('ANGOLA', 'AO', '244'),
            array('GUINEA-BISSAU', 'GW', '245'),
            array('ASCENSION ISLAND', '', '247'),
            array('SEYCHELLES', 'SC', '248'),
            array('SUDAN', 'SD', '249'),
            array('RWANDA', 'RW', '250'),
            array('ETHIOPIA', 'ET', '251'),
            array('SOMALIA', 'SO', '252'),
            array('DJIBOUTI', 'DJ', '253'),
            array('KENYA', 'KE', '254'),
            array('TANZANIA', 'TZ', '255'),
            array('UGANDA', 'UG', '256'),
            array('BURUNDI', 'BI', '257'),
            array('MOZAMBIQUE (Moçambique)', 'MZ', '258'),
            array('ZAMBIA (formerly Northern Rhodesia)', 'ZM', '260'),
            array('MADAGASCAR', 'MG', '261'),
            array('RÉUNION', 'RE', '262'),
            array('ZIMBABWE', 'ZW', '263'),
            array('NAMIBIA', 'NA', '264'),
            array('MALAWI', 'MW', '265'),
            array('LESOTHO', 'LS', '266'),
            array('BOTSWANA', 'BW', '267'),
            array('SWAZILAND', 'SZ', '268'),
            array('COMOROS', 'KM', '269'),
            array('MAYOTTE', 'YT', '269'),
            array('SAINT HELENA', 'SH', '290'),
            array('ERITREA', 'ER', '291'),
            array('ARUBA', 'AW', '297'),
            array('FAEROE ISLANDS', 'FO', '298'),
            array('GREENLAND', 'GL', '299'),
            array('GIBRALTAR', 'GI', '350'),
            array('PORTUGAL', 'PT', '351'),
            array('LUXEMBOURG', 'LU', '352'),
            array('IRELAND', 'IE', '353'),
            array('ICELAND', 'IS', '354'),
            array('ALBANIA', 'AL', '355'),
            array('MALTA', 'MT', '356'),
            array('CYPRUS', 'CY', '357'),
            array('FINLAND', 'FI', '358'),
            array('BULGARIA', 'BG', '359'),
            array('LITHUANIA', 'LT', '370'),
            array('LATVIA', 'LV', '371'),
            array('ESTONIA', 'EE', '372'),
            array('MOLDOVA', 'MD', '373'),
            array('BELARUS', 'BY', '375'),
            array('ANDORRA', 'AD', '376'),
            array('MONACO', 'MC', '377'),
            array('SAN MARINO (Republic of)', 'SM', '378'),
            array('VATICAN CITY (Holy See)', 'VA', '379'),
            array('UKRAINE', 'UA', '380'),
            array('SERBIA (Republic of Serbia)', 'RS', '381'),
            array('MONTENEGRO', 'ME', '382'),
            array('CROATIA (Hrvatska)', 'HR', '385'),
            array('SLOVENIA', 'SI', '386'),
            array('BOSNIA AND HERZEGOVINA', 'BA', '387'),
            array('MACEDONIA (Former Yugoslav Republic of Macedonia)', 'MK', '389'),
            array('CZECH REPUBLIC', 'CZ', '420'),
            array('SLOVAKIA (Slovak Republic)', 'SK', '421'),
            array('LIECHTENSTEIN (Fürstentum Liechtenstein)', 'LI', '423'),
            array('FALKLAND ISLANDS (MALVINAS)', 'FK', '500'),
            array('BELIZE', 'BZ', '501'),
            array('GUATEMALA', 'GT', '502'),
            array('EL SALVADOR', 'SV', '503'),
            array('HONDURAS', 'HN', '504'),
            array('NICARAGUA', 'NI', '505'),
            array('COSTA RICA', 'CR', '506'),
            array('PANAMA', 'PA', '507'),
            array('SAINT PIERRE AND MIQUELON', 'PM', '508'),
            array('HAITI', 'HT', '509'),
            array('GUADELOUPE', 'GP', '590'),
            array('BOLIVIA', 'BO', '591'),
            array('GUYANA', 'GY', '592'),
            array('ECUADOR', 'EC', '593'),
            array('FRENCH GUIANA', 'GF', '594'),
            array('PARAGUAY', 'PY', '595'),
            array('MARTINIQUE', 'MQ', '596'),
            array('SURINAME', 'SR', '597'),
            array('URUGUAY', 'UY', '598'),
            array('BONAIRE, ST. EUSTATIUS, AND SABA', 'BQ', '599'),
            array('CURAÃ‡AO', 'CW', '599'),
            array('NETHERLANDS ANTILLES (obsolete)', 'AN', '599'),
            array('SINT MAARTEN', 'SX', '599'),
            array('TIMOR-LESTE (formerly East Timor)', 'TL', '670'),
            array('BRUNEI DARUSSALAM', 'BN', '673'),
            array('NAURU', 'NR', '674'),
            array('PAPUA NEW GUINEA', 'PG', '675'),
            array('TONGA', 'TO', '676'),
            array('SOLOMON ISLANDS', 'SB', '677'),
            array('VANUATU', 'VU', '678'),
            array('FIJI', 'FJ', '679'),
            array('PALAU', 'PW', '680'),
            array('WALLIS AND FUTUNA', 'WF', '681'),
            array('COOK ISLANDS', 'CK', '682'),
            array('NIUE', 'NU', '683'),
            array('SAMOA (formerly Western Samoa)', 'WS', '685'),
            array('KIRIBATI', 'KI', '686'),
            array('NEW CALEDONIA', 'NC', '687'),
            array('TUVALU', 'TV', '688'),
            array('FRENCH POLYNESIA', 'PF', '689'),
            array('TOKELAU', 'TK', '690'),
            array('MICRONESIA (Federated States of Micronesia)', 'FM', '691'),
            array('MARSHALL ISLANDS', 'MH', '692'),
            array('KOREA (Democratic Peoples Republic of [North] Korea)', 'KP', '850'),
            array('HONG KONG (Special Administrative Region of China)', 'HK', '852'),
            array('MACAO (Special Administrative Region of China)', 'MO', '853'),
            array('CAMBODIA', 'KH', '855'),
            array('LAO PEOPLE\'S DEMOCRATIC REPUBLIC', 'LA', '856'),
            array('BANGLADESH', 'BD', '880'),
            array('TAIWAN (Chinese Taipei for IOC)', 'TW', '886'),
            array('MALDIVES', 'MV', '960'),
            array('LEBANON', 'LB', '961'),
            array('JORDAN (Hashemite Kingdom of Jordan)', 'JO', '962'),
            array('SYRIAN ARAB REPUBLIC', 'SY', '963'),
            array('IRAQ', 'IQ', '964'),
            array('KUWAIT', 'KW', '965'),
            array('SAUDI ARABIA (Kingdom of Saudi Arabia)', 'SA', '966'),
            array('YEMEN (Yemen Arab Republic)', 'YE', '967'),
            array('OMAN', 'OM', '968'),
            array('PALESTINIAN TERRITORIES', 'PS', '970'),
            array('UNITED ARAB EMIRATES', 'AE', '971'),
            array('ISRAEL', 'IL', '972'),
            array('BAHRAIN', 'BH', '973'),
            array('QATAR', 'QA', '974'),
            array('BHUTAN', 'BT', '975'),
            array('MONGOLIA', 'MN', '976'),
            array('NEPAL', 'NP', '977'),
            array('TAJIKISTAN', 'TJ', '992'),
            array('TURKMENISTAN', 'TM', '993'),
            array('AZERBAIJAN', 'AZ', '994'),
            array('KYRGYZSTAN', 'KG', '996'),
            array('UZBEKISTAN', 'UZ', '998'),
            array('BAHAMAS', 'BS', '1242'),
            array('BARBADOS', 'BB', '1246'),
            array('ANGUILLA', 'AI', '1264'),
            array('ANTIGUA AND BARBUDA', 'AG', '1268'),
            array('VIRGIN ISLANDS, BRITISH', 'VG', '1284'),
            array('VIRGIN ISLANDS, U.S.', 'VI', '1340'),
            array('CAYMAN ISLANDS', 'KY', '1345'),
            array('BERMUDA', 'BM', '1441'),
            array('GRENADA', 'GD', '1473'),
            array('TURKS AND CAICOS ISLANDS', 'TC', '1649'),
            array('MONTSERRAT', 'MS', '1664'),
            array('NORTHERN MARIANA ISLANDS', 'MP', '1670'),
            array('GUAM', 'GU', '1671'),
            array('AMERICAN SAMOA', 'AS', '1684'),
            array('SAINT LUCIA', 'LC', '1758'),
            array('DOMINICA', 'DM', '1767'),
            array('SAINT VINCENT AND THE GRENADINES', 'VC', '1784'),
            array('DOMINICAN REPUBLIC', 'DO', '1809'),
            array('TRINIDAD AND TOBAGO', 'TT', '1868'),
            array('SAINT KITTS AND NEVIS', 'KN', '1869'),
            array('JAMAICA', 'JM', '1-876'));
    }

}
