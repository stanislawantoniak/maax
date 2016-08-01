<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Type extends Magpleasure_Common_Model_Abstract
{
    protected $_config;

    public function getTypesHash()
    {
        Varien_Profiler::start('mp::searchcore::type_hash');

        /** @var Magpleasure_Searchcore_Model_Resource_Type_Collection $collection */
        $collection = $this->getCollection();
        $collection->setOrder('type_code', Varien_Db_Select::SQL_ASC);
        $types = $collection->getColumnValues('type_code');

        Varien_Profiler::stop('mp::searchcore::type_hash');

        return $this->_helper()->getCommon()->getHash()->getFastMd5Hash($types);
    }

    /**
     * Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }

    public function processTypes(array $types)
    {

        # Check Existence Types
        foreach ($types as $typeCode){

            /** @var Magpleasure_Searchcore_Model_Type $type */
            $type = Mage::getModel('searchcore/type');
            $type->load($typeCode, 'type_code');

            if (!$type->getId()){
                $type
                    ->setTypeCode($typeCode)
                    ->save()
                    ;
            }
        }

        # Remove not existence Types

        /** @var Magpleasure_Searchcore_Model_Resource_Type_Collection $collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter('type_code', array('nin' => $types));

        $collection->flushSelected();

        return $this;
    }

    public function getConfig()
    {
        if (!$this->_config){
            /** @var Magpleasure_Searchcore_Model_Type_Config $config */
            $config = Mage::getModel('searchcore/type_config');
            $config->loadByCode($this->getTypeCode());
            $config->setTypeId($this->getId());

            $this->_config = $config;
        }
        return $this->_config;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('searchcore/type');
    }
}