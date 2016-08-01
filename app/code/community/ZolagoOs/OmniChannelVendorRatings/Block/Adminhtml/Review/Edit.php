<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Review_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'udratings';
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_review';

        $this->_updateButton('save', 'label', Mage::helper('review')->__('Save Review'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', Mage::helper('review')->__('Delete Review'));

        if( $this->getRequest()->getParam('vendorId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('zolagoosadmin/adminhtml_vendor/edit', array('id' => $this->getRequest()->getParam('vendorId', false))) .'\')' );
        }

        if( $this->getRequest()->getParam('customerId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/customer/edit', array('id' => $this->getRequest()->getParam('customerId', false))) .'\')' );
        }

        if( $this->getRequest()->getParam('ret', false) == 'pending' ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/pending') .'\')' );
            $this->_updateButton('delete', 'onclick', 'deleteConfirm(\'' . Mage::helper('review')->__('Are you sure you want to do this?') . '\', \'' . $this->getUrl('*/*/delete', array(
                $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                'ret'           => 'pending',
            )) .'\')' );
            Mage::register('ret', 'pending');
        }

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $reviewData = Mage::getModel('review/review')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('review_data', $reviewData);
        }

        $this->_formInitScripts[] = '
            var review = {
                updateRating: function() {
                        elements = [$("select_stores"), $("rating_detail").getElementsBySelector("input[type=\'radio\']")].flatten();
                        $(\'save_button\').disabled = true;
                        new Ajax.Updater("rating_detail", "'.$this->getUrl('zosratingsadmin/review/ratingItems', array('_current'=>true)).'", {parameters:Form.serializeElements(elements), evalScripts:true, onComplete:function(){ $(\'save_button\').disabled = false; } });
                    },
                updateRatingNa: function() {
                        elements = [$("select_stores"), $("rating_detail_na").getElementsBySelector("input[type=\'radio\']")].flatten();
                        $(\'save_button\').disabled = true;
                        new Ajax.Updater("rating_detail_na", "'.$this->getUrl('zosratingsadmin/review/ratingItemsNa', array('_current'=>true)).'", {parameters:Form.serializeElements(elements), evalScripts:true, onComplete:function(){ $(\'save_button\').disabled = false; } });
                    }
           }
           Event.observe(window, \'load\', function(){
                 Event.observe($("select_stores"), \'change\', review.updateRating);
           });
        ';
    }

    public function getHeaderText()
    {
        if( Mage::registry('review_data') && Mage::registry('review_data')->getId() ) {
            return Mage::helper('review')->__("Edit Review '%s'", $this->htmlEscape(Mage::registry('review_data')->getTitle()));
        } else {
            return Mage::helper('review')->__('New Review');
        }
    }
}
