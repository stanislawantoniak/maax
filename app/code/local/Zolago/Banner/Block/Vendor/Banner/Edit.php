<?php

class Zolago_Banner_Block_Vendor_Banner_Edit extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
    }

    public function _prepareLayout()
    {
        $this->_prepareGrid();
        $this->_prepareForm();
        parent::_prepareLayout();
    }

    public function _prepareForm(){
        $type = $this->getRequest()->getParam('type',null);

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
        //--Common edit banner fields

        //Additional banner fields depends on type
        $this->_completeForm($form, $type);

        $values = $this->getModel()->getData();
        $form->setValues($values);
        $this->setForm($form);
    }

    public function _completeForm(Zolago_Dropship_Model_Form $form, $type)
    {
        $helper = Mage::helper('zolagobanner');

        switch ($type) {
            case Zolago_Banner_Model_Banner_Type::BANNER_TYPE_SLIDER:
                $content = $form->addFieldset("banner_content", array(
                    "legend" => $helper->__("Banner Content Type")
                ));
                $content->addField("slider_type", "select", array(
                    "name" => "slider_type",
                    "class" => "form-control",
                    "required" => true,
                    "label" => $helper->__('Slider Type'),
                    'values' => array('html' => 'HTML' , 'image' => 'Image')
                ));


                $sliderTypeHtml = $form->addFieldset("slider_type_html", array(
                    "legend" => $helper->__("Slider HTML"),
                    'attr_data' => array((object)array('name' => 'type', 'value' => 'html'), (object)array('name' => 'name', 'value' => 'slider_type'))
                ));
                $sliderTypeHtml->addField("html", "textarea", array(
                    "name" => "html",
                    "class" => "form-control",
                    "required" => false,
                    "label" => $helper->__('HTML')
                ));

                $sliderTypeImage = $form->addFieldset("slider_type_image", array(
                    "legend" => $helper->__("Slider Content"),
                    'class' => 'hidden',
                    'attr_data' => array((object)array('name' => 'type', 'value' => 'image'), (object)array('name' => 'name', 'value' => 'slider_type'))
                ));
//                $sliderTypeImage->addField("add_slider", "link", array(
//                    "name" => "add_slider",
//                    "label" => $helper->__("ADD"),
//                    "href" => "",
//                    'inside'  => '<span class="glyphicon glyphicon-plus"></span>',
//                ));
                $sliderTypeImage->addField("slider_image_desktop", "image", array(
                    "name" => "slider[0][image_desktop]",
                    "class" => "form-control",
                    "required" => false,
                    "label" => $helper->__('Slider Image Desktop')
                ));
                $sliderTypeImage->addField("slider_image_mobile", "image", array(
                    "name" => "slider[0][image_mobile]",
                    "class" => "form-control",
                    "required" => false,
                    "label" => $helper->__('Slider Image Mobile')
                ));
                $sliderTypeImage->addField("slider_link_url", "text", array(
                    "name" => "slider[0][link_url][0]",
                    "class" => "form-control",
                    "required" => true,
                    "label" => $helper->__('Caption Url')
                ));

                $sliderTypeImage->addField("slider_link_text", "text", array(
                    "name" => "slider[0][link_text][0]",
                    "class" => "form-control",
                    "required" => true,
                    'wrapper_class' => 'col-md-9',
                    "label" => $helper->__('Caption Text')
                ));

                break;
            case Zolago_Banner_Model_Banner_Type::BANNER_TYPE_BOX:
                echo '2';
                break;
            case Zolago_Banner_Model_Banner_Type::BANNER_TYPE_INSPIRATION:
                echo '3';
                break;
        }
        return $form;
    }

    public function _prepareGrid() {
        $design = Mage::getDesign();
        $design->setArea("adminhtml");

        $design->setArea("frontend");
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