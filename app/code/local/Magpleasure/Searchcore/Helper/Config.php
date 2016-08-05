<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Helper_Config extends Magpleasure_Searchcore_Helper_Data
{
    const CONFIG_FILE = 'mpsearch.xml';
    const CONFIG_PATH_TYPES = 'search/types';
    const CONFIG_PATH_TYPE_KEYS = 'search/types/%s';
    const CONFIG_PATH_TYPE_VALUES = 'search/types/%s/%s';
    const CONFIG_PATH_TYPE_FIELD_TYPE = 'search/types/%s/fields/%s';

    const CACHE_KEY = 'mp_searchcore_config_types';

    protected $_config;

    /**
     * Search Types
     *
     * @return array
     */
    public function getTypes()
    {
        $cache = $this->getCommon()->getCache();

        if (!($cachedValue = $cache->getPreparedHtml(self::CACHE_KEY))){

            $config = $this->_getConfig();
            $types = $this->getCommon()->getConfig()->getArrayFromPath(self::CONFIG_PATH_TYPES, $config);
            $cache->savePreparedHtml(self::CACHE_KEY, serialize($types));

        } else {

            try {
                $types = unserialize($cachedValue);
            } catch (Exception $e){
                $this->getCommon()->getException()->logException($e);
                $types = array();
            }
        }

        return $types;
    }

    protected function _getConfig()
    {
        if (!$this->_config){
            $config = Mage::getConfig()->loadModulesConfiguration(self::CONFIG_FILE);
            $this->_config = $config;
        }

        return $this->_config;
    }

    /**
     * Get default field processor
     *
     * @return Magpleasure_Searchcore_Model_Field_Default
     */
    public function getDefaultFieldProcessor()
    {
        return Mage::getSingleton('searchcore/field_default');
    }

    /**
     * Type Object
     *
     * @param string $type
     * @return Varien_Object
     */
    public function getTypeConfig($type)
    {
        $config = $this->_getConfig();
        $keys = $this->getCommon()->getConfig()->getArrayFromPath(sprintf(self::CONFIG_PATH_TYPE_KEYS, $type), $config);

        $data = array();
        foreach ($keys as $key){

            $value = $this
                ->getCommon()
                ->getConfig()
                ->getValueFromPath(
                    sprintf(
                        self::CONFIG_PATH_TYPE_VALUES,
                        $type,
                        $key
                    ),
                    $config
                )
            ;

            if ($value){

                $data[$key] = $value;

            } else {

                $array = $this
                    ->getCommon()
                    ->getConfig()
                    ->getArrayFromPath(
                        sprintf(
                            self::CONFIG_PATH_TYPE_VALUES,
                            $type,
                            $key
                        ),
                        $config
                    )
                ;

                if ($array && count($array)){

                    if ($key == 'fields') {
                        $fields = array();
                        foreach ($array as $fieldName){

                            $defaultType = 'default';
                            $definedType = $this
                                ->getCommon()
                                ->getConfig()
                                ->getValueFromPath(
                                    sprintf(
                                        self::CONFIG_PATH_TYPE_FIELD_TYPE,
                                        $type,
                                        $fieldName
                                    ),
                                    $config
                                )
                            ;

                            /** @var Magpleasure_Searchcore_Model_Field_Default $fieldModel */
                            $fieldModel = Mage::getModel(
                                'searchcore/field_'.($definedType ? $definedType : $defaultType)
                            );
                            $fieldModel->setKey($fieldName);
                            $fields[] = $fieldModel;
                        }
                        $data[$key] = $fields;

                    } elseif ($key = 'getters') {
                        $getters = array();
                        foreach ($array as $fieldName){
                            $getters[] = $fieldName;
                        }
                        $data[$key] = $getters;
                    } else {
                        $data[$key] = $array;
                    }
                }
            }
        }

        return $data;
    }

}