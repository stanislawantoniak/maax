<?php

class Zolago_Banner_Block_Vendor_Banner_Edit extends Mage_Core_Block_Template
{
    private $_type;

    protected function _construct()
    {
        parent::_construct();
    }

    public function _prepareLayout()
    {
        $this->_prepareForm();
        parent::_prepareLayout();
    }


    /**
     *
     */
    public function setType()
    {
        $type = $this->getRequest()->getParam('type', $this->getModel()->getType());
        $this->_type = $type;
    }

    /**
     * Get banner type code
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get banner type label
     * @return string
     */
    public function getTypeLabel()
    {
        return ucfirst(str_replace("_", " ", $this->_type));
    }

    /**
     * @return Zolago_Campaign_Model_Campaign
     */
    public function getCampaign() {
        return Mage::getModel("zolagocampaign/campaign")->load($this->getCampaignId());
    }

    public function getCampaignId(){
        $campaignId = $this->getRequest()->getParam('campaign_id', $this->getModel()->getCampaignId());
        return $campaignId;
    }

    public function getCampaignName(){
        $campaignId = $this->getRequest()->getParam('campaign_id', $this->getModel()->getCampaignId());
        $campaignModel = Mage::getModel('zolagocampaign/campaign')->load($campaignId);
        return $campaignModel->getName();
    }
    public function _prepareForm(){
        $id = $this->getRequest()->getParam('id',null);

        $type = $this->_type;
        $campaignId = $this->getCampaignId();

        $helper = Mage::helper('zolagobanner');
        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("banner/vendor/save", array("_secure" => true)));

        //Common edit banner fields
        $general = $form->addFieldset("general", array(
            "legend" => $helper->__("General"),
            "icon_class" => "icon-cog"
        ));

        $general->addField("name", "text", array(
            "name" => "name",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Name'),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-6"
        ));

        if(!empty($campaignId)){

        }
        $general->addField(
            "campaign_id", "hidden",
            array(
                 "name"     => "campaign_id",
                 "required" => true)
        );
        $general->addField("type", "hidden", array(
            "name" => "type"
        ));

        $data = $this->getTypeConfiguration($type);

        //Additional banner fields depends on type
        $this->_completeForm($form, $data);



        $values = $this->getModel()->getData();

        $contentData = $this->getModel()->getResource()->getBannerContent($id);

        $contentValues = $this->_prepareContentDataToSet($contentData);

        if(!empty($data)){
            $values = array_merge($values, array('show' => $data->show_as, 'type' => $type  ));
        }

        $values = array_merge($values, array('campaign_id'=> $campaignId));
        $values = array_merge($values, $contentValues);

        $form->setValues($values);
        $this->setForm($form);
    }

    private function _prepareContentDataToSet($contentData){
        $data = array();

        if($contentData){
            if($contentData['show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE){
                $image = unserialize($contentData['image']);
                $caption = unserialize($contentData['caption']);

                if(!empty($image)){
                    foreach($image as $n => $imageItem){
                        $data['image_'.$n] = isset($imageItem['path']) ? $imageItem['path'] : '';
                        $data['image_url_'.$n] = isset($imageItem['url']) ? $imageItem['url'] : '';
                    }
                }
                if(!empty($caption)){
                    foreach($caption as $n => $captionItem){
                        $data['caption_text_'.$n] = isset($captionItem['text']) ? $captionItem['text'] : '';
                        $data['caption_url_'.$n] = isset($captionItem['url']) ? $captionItem['url'] : '';
                    }
                }
            }
            if($contentData['show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_HTML){
                $data['banner_html'] = $contentData['html'];
            }
        }

        return $data;

    }

    public function getTypeConfiguration()
    {
        $type = $this->_type;
        $config = array();
        if (!empty($type)) {
            //fetch config
            $configPath = Zolago_Banner_Model_Banner_Type::BANNER_TYPES_CONFIG;
            $configValue = Mage::getStoreConfig($configPath);
            $typesConfig = json_decode($configValue);

            foreach ($typesConfig as $typesConfigType) {
                if (
                    is_object($typesConfigType) && Mage::getSingleton('zolagobanner/banner_type')->getTypCodeByTitle($typesConfigType->title) == $type
                ) {
                    $config = $typesConfigType;
                }
            }
        }
        return $config;
    }

    public function _completeForm(Zolago_Dropship_Model_Form $form, $data)
    {
        $helper = Mage::helper('zolagobanner');

        $bannerContent = $form->addFieldset("banner_content", array(
            "legend" => $helper->__("Content"),
            "icon_class" => "icon-picture"
        ));

        $bannerContent->addField("show", "hidden", array(
            "name" => "show"
        ));
        $bannerContent->addType("thumb", "Zolago_Banner_Varien_Data_Form_Element_Thumbnail");
        switch ($data->show_as) {
            case Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE:
                $picturesNumber = $data->pictures_number;

                $url = $this->getCampaign()->getWebsiteUrl();

                if ($picturesNumber > 0) {
                    // Note: picture_can_be_empty should be read like: picture_url_can_be_empty
                    $pictureUrlRequired = (isset($data->picture_can_be_empty) && $data->picture_can_be_empty == 1) ? FALSE : TRUE;
                    foreach ($data->picture as $n => $picture) {
                        $pictureW = (isset($picture->pictures_w) && !empty($picture->pictures_w)) ? $picture->pictures_w : "-";
                        $pictureH = (isset($picture->pictures_h) && !empty($picture->pictures_h)) ? $picture->pictures_h : "-";


                        $imageOptions = array(
                            "name" => "image[" . $n . "]",
                            "class" => "form-control",
                            "required" => true,
                            "data_attribute" => array('restrictw' => $pictureW, 'restricth' => $pictureH),
                            "label" => $helper->__($picture->picture_label),
                            "label_wrapper_class" => "col-md-3",
                            "wrapper_class" => "col-md-6"
                        );
                        if ((isset($picture->pictures_w) && !empty($picture->pictures_w))
                            && (isset($picture->pictures_h) && !empty($picture->pictures_h))
                        ) {
                            $afterImageElementHtml = "<p class='help-block-message align-left'>" .
                                $helper->__("NOTE! Image must have a width %spx and height %spx", $pictureW, $pictureH) .
                                "</p>";
                            $imageOptions = array_merge($imageOptions,
                                array(
                                    'after_element_html' => $afterImageElementHtml
                                )
                            );
                        }

                        $bannerContent->addField("image_" . $n, "thumb", $imageOptions);

                        if(!isset($data->banner_no_link)){
                            $bannerContent->addField("image_url_" . $n, "text", array(
                                "name" => "image_url[" . $n . "]",
                                "class" => "form-control",
                                "required" => $pictureUrlRequired,
                                "label" => $helper->__($picture->picture_label . ": url"),
                                "label_wrapper_class" => "col-md-3",
                                "wrapper_class" => "col-md-6",
                                "input_group_addon" => $url
                            ));
                        }

                    }
                    unset($n);
                }

                $captionsNumber = $data->captions_number;

                if ($captionsNumber > 0) {

                    $captionUrlRequired = (isset($data->caption_can_be_empty) && $data->caption_can_be_empty == 1) ? FALSE : TRUE;
                    foreach ($data->caption as $n => $caption) {
                        $bannerContent->addField("caption_url_" . $n, "text", array(
                            "name" => "caption_url[" . $n . "]",
                            "class" => "form-control",
                            "required" => $captionUrlRequired,
                            "label" => $helper->__($caption->caption_label . ": url"),
                            "label_wrapper_class" => "col-md-3",
                            "wrapper_class" => "col-md-6",
                            "input_group_addon" => $url
                        ));
                        $captionOptions = array(
                            "name" => "caption_text[" . $n . "]",
                            "class" => "form-control",
                            "required" => $captionUrlRequired,
                            "label" => $helper->__($caption->caption_label . ": text"),
                            "label_wrapper_class" => "col-md-3",
                            "wrapper_class" => "col-md-6"
                        );
                        $captionMaxSymbols = (isset($data->caption_max_symbols) && $data->caption_max_symbols > 0) ? $data->caption_max_symbols : FALSE;

                        if ($captionMaxSymbols) {
                            $afterElementHtml = "<p class='help-block-message align-left'>" .
                                $helper->__('Max length is %s', $captionMaxSymbols) .
                                "</p>";
                            $captionOptions = array_merge($captionOptions,
                                array(
                                    'after_element_html' => $afterElementHtml
                                )
                            );
                        }
                        $bannerContent->addField("caption_text_" . $n, "text", $captionOptions);
                        unset($captionOptions);
                    }
                    unset($n);

                }
                break;
            case Zolago_Banner_Model_Banner_Show::BANNER_SHOW_HTML:
                $bannerContent->addField("banner_html", "textarea", array(
                    "name" => "banner_html",
                    "class" => "form-control",
                    "required" => true,
                    "label" => $helper->__('HTML')
                ));
                break;
        }
        return $form;
    }

    /**
     * @return Zolago_Banner_Model_Banner
     */
    public function getModel()
    {
        if (!Mage::registry("current_banner")) {
            Mage::register("current_banner", Mage::getModel("zolagobanner/banner"));
        }
        return Mage::registry("current_banner");
    }

    public function isModelNew()
    {
        return $this->getModel()->isObjectNew();
    }

    /**
     * Get current vendor from udropship session
     * @return mixed|Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {
        return Mage::getSingleton('udropship/session')->getVendor();
    }
}