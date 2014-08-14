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
     * @param mixed $type
     */
    public function setType()
    {
        $type = $this->getRequest()->getParam('type', $this->getModel()->getType());
        $this->_type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }


    public function _prepareForm(){
        $id = $this->getRequest()->getParam('id',null);
        $type = $this->_type;

        $helper = Mage::helper('zolagobanner');
        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("banner/vendor/save"));

        //Common edit banner fields
        $general = $form->addFieldset("general", array(
            "legend" => $helper->__("General")
        ));

        $general->addField("name", "text", array(
            "name" => "name",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Name')
        ));

        $general->addField("campaign_id", "select", array(
            "name" => "campaign_id",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Campaign'),
            "values" => Mage::getSingleton('zolagobanner/banner_campaign')->toOptionHash()
        ));
        $general->addField("type", "hidden", array(
            "name" => "type"
        ));
        //--Common edit banner fields
        $data = $this->getTypeConfiguration($type);

        //Additional banner fields depends on type
        $this->_completeForm($form, $data);

        $values = $this->getModel()->getData();
        $values = array_merge($values, array('show' => $data->show_as, 'type' => $type ));
        $form->setValues($values);
        $this->setForm($form);
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
                    Mage::getSingleton('zolagobanner/banner_type')->getTypCodeByTitle($typesConfigType->title) == $type
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
            "legend" => $helper->__("Content")
        ));

        $bannerContent->addField("show", "hidden", array(
            "name" => "show"
        ));
        switch ($data->show_as) {
            case Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE:
                $picturesNumber = $data->pictures_number;

                if ($picturesNumber > 0) {
                    $pictureUrlRequired = (isset($data->picture_can_be_empty) && $data->picture_can_be_empty == 1) ? FALSE : TRUE;
                    foreach ($data->picture as $n => $picture) {
                        $bannerContent->addField("image_" . $n, "image", array(
                            "name" => "image[" . $n . "]",
                            "class" => "form-control",
                            "required" => true,
                            "label" => $picture->picture_label
                        ));
                        $bannerContent->addField("image_url_" . $n, "text", array(
                            "name" => "image_url[" . $n . "]",
                            "class" => "form-control",
                            "required" => $pictureUrlRequired,
                            "label" => $picture->picture_label . ": url"
                        ));
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
                            "label" => $caption->caption_label . ": url"
                        ));
                        $captionOptions = array(
                            "name" => "caption_text[" . $n . "]",
                            "class" => "form-control",
                            "required" => $captionUrlRequired,
                            "label" => $caption->caption_label . ": text"
                        );
                        $captionMaxSymbols = (isset($data->caption_max_symbols) && $data->caption_max_symbols > 0) ? $data->caption_max_symbols : FALSE;

                        if ($captionMaxSymbols) {
                            $afterElementHtml = '<p class="nm"><span class="glyphicon glyphicon-exclamation-sign"></span> '  . 'Max length is ' . $captionMaxSymbols . '</p>';
                            $captionOptions = array_merge($captionOptions,
                                array(
                                    'maxlength' => $captionMaxSymbols,
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
}