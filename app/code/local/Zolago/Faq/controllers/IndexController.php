<?php

require_once Mage::getModuleDir('controllers', "Inic_Faq") . DS . "IndexController.php";

class Zolago_Faq_IndexController extends Inic_Faq_IndexController
{
    public function preDispatch() {
        parent::preDispatch();
        Mage::dispatchEvent('faq_controller_index');
        return $this;
    }

    /**
     * Displays the current Category's FAQ list view
     */
    public function resultAction()
    {
        $keyword = $this->getRequest()->getParam('keyword');
        $keyword = preg_replace(
            array(
                // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu'),
            array("","","","","","","","",""),  $keyword );
        $this->getRequest()->setParam('keyword', $keyword);

        $this->loadLayout()->renderLayout();
    }
}
