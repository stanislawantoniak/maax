<?php

/**
 * Class GH_FeedExport_Model_Feed_Generator_Action_Iterator
 */
class GH_FeedExport_Model_Feed_Generator_Action_Iterator
    extends Mirasvit_FeedExport_Model_Feed_Generator_Action_Iterator {


    public function process()
    {
        switch ($this->getType()) {
            case 'rule':
                $iteratorModel = Mage::getModel('feedexport/feed_generator_action_iterator_rule');
                break;

            case 'product':
            case 'category':
            case 'review':
                $iteratorModel = Mage::getModel('feedexport/feed_generator_action_iterator_entity');
                break;

            default:
                Mage::throwException(sprintf('Undefined iterator type %s', $this->getType()));
                break;
        }

        $iteratorModel
            ->setData($this->getData())
            ->setFeed($this->getFeed());

        if ($iteratorModel->init() === false) {
            $this->finish();
            return;
        }

        $collection = $iteratorModel->getCollection();
        $size       = $collection->getSize();
        $idx        = intval($this->getValue('idx'));
        $add        = intval($this->getValue('add'));

        if ($idx == 0) {
            $this->start();
            $iteratorModel->start();
        }

        $limit = intval($size / 100);
        if ($limit < 100) {
            $limit = 100;
        }

        $collection->getSelect()->limit($limit, $idx);
        $stmt       = $collection->getData();

        $result     = array();
        foreach($stmt as $row){
            $callbackResult = $iteratorModel->callback($row);
            if ($callbackResult !== null) {
                $result[] = $callbackResult;
                $add++;
            }
            $idx++;

            $this->setValue('idx', $idx)
                ->setValue('size', $size)
                ->setValue('add', $add);

            if (Mage::helper('feedexport')->getState()->isTimeout()) {
                break;
            }
        }

        $iteratorModel->save($result);


        if ($idx >= $size) {
            $iteratorModel->finish();
            $this->finish();
        }
    }

}