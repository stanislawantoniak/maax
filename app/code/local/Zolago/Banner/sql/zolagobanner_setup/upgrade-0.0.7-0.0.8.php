<?php

$defaultTypesConfig = array(
    //Slider
    1 => array(
        'title' => 'Slider',
        'show_as' => Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE,
        'position_label' => "SLOT",
        'position_count' => 3,
        'pictures_number' => 2,
        'picture_can_be_empty' => 1,
        'picture' => array(
            1 => array(
                'picture_label' => 'Main image',
                'pictures_w' => 1174,
                'pictures_h' => 250
            ),
            2 => array(
                'picture_label' => 'Small screen image',
                'pictures_w' => 320,
                'pictures_h' => 316
            )
        ),
        'captions_number' => 2,
        'caption_max_symbols' => 30,
        'caption_can_be_empty' => 1,
        'caption' => array(
            1 => array('caption_label' => 'Top caption'),
            2 => array('caption_label' => 'Bottom caption')
        )
    ),
    //Box
    2 => array(
        'title' => 'Box',
        'show_as' => Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE,
        'position_label' => "POSITION",
        'position_count' => 4,
        'pictures_number' => 1,
        'picture_can_be_empty' => 0,
        'picture' => array(
            1 => array(
                'picture_label' => 'Box Image',
                'pictures_w' => 280,
                'pictures_h' => 323
            ),
        ),
        'captions_number' => 0,
        'caption_max_symbols' => 0
    ),
    //INSPIRATION
    3 => array(
        'title' => 'Inspiration',
        'show_as' => Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE,
        'position_label' => "POSITION",
        'position_count' => 8,
        'pictures_number' => 1,
        'picture_can_be_empty' => 0,
        'picture' => array(
            1 => array(
                'picture_label' => 'Inspiration image',
                'pictures_w' => 208,
                'pictures_h' => 312
            )
        ),
        'captions_number' => 2,
        'caption_max_symbols' => 25,
        'caption_can_be_empty' => 1,
        'caption' => array(
            1 => array('caption_label' => 'Title caption'),
            2 => array('caption_label' => 'Body caption')
        )
    ),
    //Landing page creative
    4 => array(
        'title' => 'Landing page creative',
        'show_as' => Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE,
        'pictures_number' => 2,
        'picture_can_be_empty' => 1,
        'picture' => array(
            1 => array(
                'picture_label' => 'Desktop image',
            ),
            2 => array(
                'picture_label' => 'Mobile image',
            )
        ),
        'captions_number' => 0,
        "caption_max_symbols" => 0,
        "only_for_local_vendor" => 1
    ),

    'no_image' => '/skin/frontend/base/default/images/no_banner_preview.jpeg',
    'image_html' => '/skin/frontend/base/default/images/banner_html_content.png',
    'campaign_expires' => 48
);
$jsonDefaultTypesConfig = Mage::helper('core')->jsonEncode($defaultTypesConfig);
$configModel = new Mage_Core_Model_Config();
$configModel->saveConfig('zolagobanner/config/zolagobannertypes', $jsonDefaultTypesConfig);