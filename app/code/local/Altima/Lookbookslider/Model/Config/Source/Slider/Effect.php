<?php
/*
 * none
 * fade
 * fadeout
 * scrollHorz
 * scrollVert
 * tileSlide
 * tileBlind add to js Tile
 * shuffle
 */
class Altima_Lookbookslider_Model_Config_Source_Slider_Effect extends Mage_Core_Model_Abstract
{
    const ALL = 'all';
    const NONE = 'none';
    const FADE = 'fade';
    const FADE_OUT = 'fadeout';
    const FLIP = 'flipHorz';
    const FLIP_VERT = 'flipVert';
    const SCROLL_HORZ = 'scrollHorz';
    const SCROLL_LEFT = 'scrollLeft';
    const SCROLL_RIGHT = 'scrollRight';
    const SCROLL_VERT = 'scrollVert';
    const SCROLL_UP = 'scrollUp';
    const SCROLL_DOWN = 'scrollDown';
    const COVER = 'cover';
    const TILE_SLIDE = 'tileSlide';
    const TILE_SLIDE_HORZ = 'tileSlideHorz';
    const TILE_BLIND = 'tileBlind';
    const TILE_BLIND_HORZ = 'tileBlindHorz';
    const SHUFFLE = 'shuffle';
    const SHUFFLE_REVERT = 'shuffle_revert';
    const SLIDE_LEFT = 'slideLeft';
    const SLIDE_RIGHT = 'slideRight';
    const SLIDE_TOP = 'slideTop';
    const SLIDE_BOTTOM = 'slideBottom';
    const SLIDE_LEFT_TOP = 'slideLeftTop';
    const SLIDE_LEFT_BOTTOM = 'slideLeftBottom';
    const SLIDE_RIGHT_TOP = 'slideRightTop';
    const SLIDE_RIGHT_BOTTOM = 'slideRightBottom';
    
   
    
    static public function getAllOptions()
    {
        return array(
                array( 'value'=> self::ALL , 'label' => 'All effects'),
                array( 'value'=> self::NONE , 'label' => 'None effects'),
                array( 'value'=> self::FADE , 'label' => 'Fade'),
                array( 'value'=> self::FADE_OUT , 'label' => 'Fade Out'),
                array( 'value'=> self::FLIP , 'label' => 'Flip horz'),
                array( 'value'=> self::FLIP_VERT , 'label' => 'Flip vert'),
                array( 'value'=> self::SCROLL_HORZ , 'label' => 'Scroll horz'),
                array( 'value'=> self::SCROLL_LEFT , 'label' => 'Scroll left'),
                array( 'value'=> self::SCROLL_RIGHT , 'label' => 'Scroll right'),
                array( 'value'=> self::SCROLL_VERT , 'label' => 'Scroll vert'),
                array( 'value'=> self::SCROLL_UP , 'label' => 'Scroll up'),
                array( 'value'=> self::SCROLL_DOWN , 'label' => 'Scroll down'),
                array( 'value'=> self::COVER , 'label' => 'Cover'),
                array( 'value'=> self::TILE_SLIDE , 'label' => 'Tile slide'),
                array( 'value'=> self::TILE_SLIDE_HORZ , 'label' => 'Tile slide horz'),
                array( 'value'=> self::TILE_BLIND , 'label' => 'Tile blind'),
                array( 'value'=> self::TILE_BLIND_HORZ , 'label' => 'Tile blind horz'),
                array( 'value'=> self::SHUFFLE , 'label' => 'Shuffle'),
                array( 'value'=> self::SHUFFLE_REVERT , 'label' => 'Shuffle revert'),
                array( 'value'=> self::SLIDE_LEFT , 'label' => 'Slide left'),
                array( 'value'=> self::SLIDE_RIGHT , 'label' => 'Slide right'),
                array( 'value'=> self::SLIDE_TOP , 'label' => 'Slide top'),
                array( 'value'=> self::SLIDE_BOTTOM , 'label' => 'Slide bottom'),
                array( 'value'=> self::SLIDE_LEFT_TOP , 'label' => 'Slide left top'),
                array( 'value'=> self::SLIDE_LEFT_BOTTOM , 'label' => 'Slide left bottom'),
                array( 'value'=> self::SLIDE_RIGHT_TOP , 'label' => 'Slide right top'),
                array( 'value'=> self::SLIDE_RIGHT_BOTTOM , 'label' => 'Slide right bottom'),
            );
    }
}
