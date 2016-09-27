<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Data_Attribute extends Mage_Core_Helper_Abstract
{
    protected function _trimValues(array $input)
    {
        $output = array();
        foreach ($input as $value){
            $output[] = trim($value);
        }
        return $output;
    }


    protected function _applyTags($data, Magpleasure_Blog_Model_Post $post)
    {
        $changed = false;

        $data['add_tag'] = isset($data['add_tag']) ? 1 : 0;
        $data['remove_tag'] = isset($data['remove_tag']) ? 1 : 0;
        $object = new Varien_Object($data);

        if ($object->getAddTag() || $object->getRemoveTag()) {

            $origTags = $tags = $post->getTags();

            # Prepare Tag Arrays
            $removeTags = explode(",", $object->getRemoveTagValues());
            $addTags = explode(",", $object->getAddTagValues());
            $tags = explode(",", $tags);

            # Trim values
            $removeTags = $this->_trimValues($removeTags);
            $addTags = $this->_trimValues($addTags);
            $tags = $this->_trimValues($tags);

            if ($object->getRemoveTag()) {
                foreach ($removeTags as $tag) {
                    if (($index = array_search(trim($tag), $tags)) !== false) {
                        unset($tags[$index]);
                    }
                }
            }

            if ($object->getAddTag()) {

                $tags = array_merge($tags, $addTags);
                $tags = array_unique($tags);
            }

            $tags = implode(", ", $tags);

            $changed = ($origTags != $tags);
            $post->setTags($tags);
        }

        return $changed;
    }

    protected function _applyStore($data, Magpleasure_Blog_Model_Post $post)
    {
        $changed = false;

        $data['add_store'] = isset($data['add_store']) ? 1 : 0;
        $data['remove_store'] = isset($data['remove_store']) ? 1 : 0;
        $object = new Varien_Object($data);

        if ($object->getAddStore() || $object->getRemoveStore()) {

            $origStores = $stores = $post->getStores();

            if ($object->getRemoveStore()) {
                foreach ($object->getRemoveStoreValues() as $storeId) {
                    if (($index = array_search($storeId, $stores)) !== false) {
                        unset($stores[$index]);
                    }
                }
            }

            if ($object->getAddStore()) {
                $stores = array_merge($stores, $object->getAddStoreValues());
                $stores = array_unique($stores);
            }

            $changed = ($origStores != $stores);
            $post->setStores($stores);
        }

        return $changed;
    }

    protected function _applyCategories($data, Magpleasure_Blog_Model_Post $post)
    {
        $changed = false;
        $data['add_category'] = isset($data['add_category']) ? 1 : 0;
        $data['remove_category'] = isset($data['remove_category']) ? 1 : 0;
        $object = new Varien_Object($data);

        if ($object->getAddCategory() || $object->getRemoveCategory()){

            $origCategories = $categories = $post->getCategories();
            if ($object->getRemoveCategory() && $object->getRemoveCategoryValues()) {
                foreach ($object->getRemoveCategoryValues() as $storeId) {
                    if (($index = array_search($storeId, $categories)) !== false) {
                        unset($categories[$index]);
                    }
                }
            }

            if ($object->getAddCategory() && $object->getAddCategoryValues()) {
                $categories = array_merge($categories, $object->getAddCategoryValues());
                $categories = array_unique($categories);
            }

            $changed = ($origCategories != $categories);
            $post->setCategories($categories);
        }

        return $changed;
    }
    
    public function apply(array $data, $postIds)
    {
        if ($postIds){
            $postIds = explode(",", $postIds);
        }

        # Validate if we have posts to update
        if (!count($postIds)){
            Mage::throwException("There are no posts defined.");
        }

        $result = array(
            'success' => 0,
            'skip' => 0,
            'errors' => array(),
        );

        foreach ($postIds as $postId) {

            $post = Mage::getModel('mpblog/post')->load($postId);

            if (!$post->getId()){
                Mage::throwException("Post with id %s wasn't found.", $postId);
            }

            try {
                $categoriesChanged = $this->_applyCategories($data, $post);
                $tagsChanged = $this->_applyTags($data, $post);
                $storesChanged = $this->_applyStore($data, $post);


                if ($categoriesChanged || $storesChanged || $tagsChanged){
                    $post->save();
                    $result['success'] ++ ;
                } else {

                    $result['skip'] ++ ;
                }

            } catch (Exception $e){

                Mage::logException($e);
                $result['errors'][] = $e->getMessage() ;
            }
        }

        return $result;
    }

}