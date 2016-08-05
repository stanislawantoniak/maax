<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Form_Element_Autocomplete extends Varien_Data_Form_Element_Text
{
    public function getTagsUrl()
    {
        /** @var Mage_Adminhtml_Model_Url $urlModel  */
        $urlModel = Mage::getSingleton('adminhtml/url');

        $params = array();
        $storeId = Mage::app()->getRequest()->getParam('store');
        if ($storeId !== null){
            $params['store'] = $storeId;
        }

        return $urlModel->getUrl('adminhtml/mpblog_post/tags', $params);
    }

    public function getHtml()
    {
        $tagsUrl = $this->getTagsUrl();
        $script = '
        <script type="text/javascript">
                    (function($){
                        $(document).ready(function(e){
                            $("#tags").autocomplete("'.$tagsUrl.'", {
                                width: 436,
                                max: 12,
                                highlight: false,
                                multiple: true,
                                multipleSeparator: ", ",
                                scroll: true,
                                scrollHeight: 300
                            });
                        });
                    })(jQuery);
        </script>
        ';
        return parent::getHtml().$script;
    }
}