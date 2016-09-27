<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Networks extends Varien_Object
{
    protected $_networks;

    public function toOptionArray()
    {
        $result = array();
        foreach ($this->_networks as $network) {
            $result[] = array('value' => $network['value'], 'label' => $network['label'] );
        }
        return $result;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _isEnabled($network)
    {
        if (in_array($network, $this->_helper()->getSocialNetworks())){
            return true;
        }
        return false;
    }

    /**
     * Enabled Networks
     *
     * @return array
     */
    public function getNetworks()
    {
        $networks = array();
        foreach ($this->_networks as $data){
            if ($this->_isEnabled($data['value'])){
                $networks[] = new Varien_Object($data);
            }
        }
        return $networks;
    }

    protected function _construct()
    {
        $this->_networks = array(
            array(
                'value'=>'twitter',
                'label'=>$this->_helper()->__('Twitter'),
                'is_template' => false,
                'url' => 'http://twitter.com/?status={title} : {url}',
                'style' => 'background-position:-343px -55px;',
            ),
            array(
                'value'=>'facebook',
                'label'=>$this->_helper()->__('Facebook'),
                'is_template' => false,
                'url' => 'http://www.facebook.com/share.php?u={url}',
                'style' => 'background-position:-343px -1px;',
            ),
            array(
                'value'=>'vkontakte',
                'label'=>$this->_helper()->__('VKontakte'),
                'is_template' => false,
                'url' => 'http://vkontakte.ru/share.php?url={url}',
                'style' => 'background-position: -1px -90px;',
            ),
            array(
                'value'=>'odnoklassniki',
                'label'=>$this->_helper()->__('Odnoklassniki'),
                'is_template' => false,
                'url' => 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st.comments={description}&st._surl={url}',
                'style' => 'background-position: -19px -91px;',
            ),
            array(
                'value'=>'blogger',
                'label'=>$this->_helper()->__('Blogger'),
                'is_template' => false,
                'url' => 'http://blogger.com/blog-this.g?t={description}&n={title}&u={url}',
                'style' => 'background-position: -37px -90px;',
            ),
            array(
                'value'=>'pinterest',
                'label'=>$this->_helper()->__('Pinterest'),
                'is_template' => false,
                'url' => 'http://pinterest.com/pin/create/button/?url={url}&media={image}&description={title}',
                'style' => 'background-position: -55px -90px;',
                'image' => true,
            ),
            array(
                'value'=>'tumblr',
                'label'=>$this->_helper()->__('Tumblr'),
                'is_template' => false,
                'url' => 'http://www.tumblr.com/share/link?url={url}&name={title}&description={description}',
                'style' => 'background-position: -91px -90px;',
            ),
            array(
                'value'=>'digg',
                'label'=>$this->_helper()->__('Digg'),
                'is_template' => false,
                'url' => 'http://digg.com/submit?phase=2&url={url}',
                'style' => 'background-position:-235px -1px;',
            ),
            array(
                'value'=>'delicious',
                'label'=>$this->_helper()->__('Del.icio.us'),
                'is_template' => false,
                'url' => 'http://del.icio.us/post?url={url}',
                'style' => 'background-position:-199px -1px;',
            ),
            array(
                'value'=>'stumbleupon',
                'label'=>$this->_helper()->__('StumbleUpon'),
                'is_template' => false,
                'url' => 'http://www.stumbleupon.com/submit?url={url}&title={title}',
                'style' => 'background-position:-217px -55px;',
            ),
            array(
                'value'=>'slashdot',
                'label'=>$this->_helper()->__('Slashdot'),
                'is_template' => false,
                'url' => 'http://slashdot.org/slashdot-it.pl?op=basic&url={url}',
                'style' => 'background-position: -145px -55px;',
            ),
            array(
                'value'=>'reddit',
                'label'=>$this->_helper()->__('Reddit'),
                'is_template' => false,
                'url' => 'http://reddit.com/submit?url={url}&title={title}',
                'style' => 'background-position:-55px -55px;',
            ),
            array(
                'value'=>'linkedin',
                'label'=>$this->_helper()->__('LinkedIn'),
                'is_template' => false,
                'url' => 'http://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}',
                'style' => 'background-position: -1px -37px;',
            ),
            array(
                'value'=>'google',
                'label'=>$this->_helper()->__('Google Bookmarks'),
                'is_template' => false,
                'url' => 'https://www.google.com/bookmarks/mark?op=add&bkmk={url}&title={title}',
                'style' => 'background-position:-91px -19px;',
            ),
            array(
                'value'=>'googleplus',
                'label'=>$this->_helper()->__('Google Plus'),
                'is_template' => true,
            ),
        );
        parent::_construct();
    }
}

