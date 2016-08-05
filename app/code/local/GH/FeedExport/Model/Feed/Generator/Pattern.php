<?php

/**
 * Class GH_FeedExport_Model_Feed_Generator_Pattern
 */
class GH_FeedExport_Model_Feed_Generator_Pattern
    extends Mirasvit_FeedExport_Model_Feed_Generator_Pattern {
    public function getPatternValue($content, $scope = null, $obj = null, $row = array())
    {
        preg_match_all('/{([^}]+)(\sparent|\sgrouped|\sconfigurable|\sbundle)?([^}]*)}/', $content, $matches);

        foreach ($matches[0] as $pattern) {
            $value = false;
            switch ($scope) {
                case 'product':
                    $model = Mage::getSingleton('feedexport/feed_generator_pattern_product')->setFeed($this->getFeed());
                    $value = $model->getValue($pattern, $obj, $row);
                    break;

                case 'category':
                    $model = Mage::getSingleton('feedexport/feed_generator_pattern_category')->setFeed($this->getFeed());
                    $value = $model->getValue($pattern, $obj);
                    break;

                case 'review':
                    $model = Mage::getSingleton('feedexport/feed_generator_pattern_review')->setFeed($this->getFeed());
                    $value = $model->getValue($pattern, $obj);
                    break;
            }

            if ($value === '' || $value === null || $value === false) {
                $model = Mage::getSingleton('feedexport/feed_generator_pattern_global')->setFeed($this->getFeed());
                $value = $model->getValue($pattern, $obj);
            }

            $value = Mage::helper('feedexport/string')->processString($value, $this->getFeed());

            if (!is_object($value)) {
                $content = str_replace($pattern, $value.'', $content);
            }
        }

        return $content;
    }

}