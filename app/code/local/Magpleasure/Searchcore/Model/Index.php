<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

/** @method getResource Magpleasure_Searchcore_Model_Resource_Index */
class Magpleasure_Searchcore_Model_Index extends Mage_Index_Model_Indexer_Abstract
{
    const EVENT_MATCH_RESULT_KEY = 'magpleasure_search_core_match_result';

    protected $_storeId;

    /**
     * @return mixed
     */
    public function getCollection()
    {
        $collection = Mage::getResourceModel('searchcore/index_collection');
        if ($this->getStoreId()){
            $collection->setStoreId($this->getStoreId());
        }
        return $collection;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_helper()->__("Amasty Search");
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_helper()->__('Rebuild Amasty Search Index');
    }

    /**
     * Load Abstract Model by few key fields
     *
     * @param array $data
     * @return Magpleasure_Common_Model_Resource_Abstract
     */
    public function loadByFewFields(array $data)
    {
        $this->getResource()->loadByFewFields($this, $data);
        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('searchcore/index');
        $this->_matchedEntities = $this->_getMatchedEntites();
    }

    protected function _getMatchedEntites()
    {
        $entities = array();

        foreach ($this->_helper()->getTypeList() as $typeCode){
            $entities[$typeCode] = array(Mage_Index_Model_Event::TYPE_SAVE, Mage_Index_Model_Event::TYPE_DELETE);
        }

        return $entities;
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

    protected function _isManualUpdate()
    {
        return $this->getResource()->isManualUpdateModeEnabled();
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
    }

    /**
     * Process event based on event state data
     *
     * @param Mage_Index_Model_Event $event
     * @return $this
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        /** @var Magpleasure_Common_Model_Abstract $dataObject */
        $dataObject = $event->getDataObject();
        $entityCode = $event->getEntity();

        $typeCode = $event->getType();
        $entity = $this->_helper()->getTypeByCode($entityCode);

        if ($dataObject && $entity){

            $config = $entity->getConfig();

            if ($typeCode == Mage_Index_Model_Event::TYPE_SAVE){

                $this->getResource()->reindexItem($dataObject, $config);

            } elseif ($typeCode == Mage_Index_Model_Event::TYPE_DELETE) {

                $this->getResource()->deleteIndex($dataObject, $config);
            }
        }

        return $this;
    }
}