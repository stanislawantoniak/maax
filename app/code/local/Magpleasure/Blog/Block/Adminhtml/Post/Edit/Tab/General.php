<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Post_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_values;

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _getValues()
    {
        if (Mage::getSingleton('adminhtml/session')->getPostData()) {
            $this->_values = Mage::getSingleton('adminhtml/session')->getPostData();
            Mage::getSingleton('adminhtml/session')->getPostData(null);
        } elseif (Mage::registry('current_post')) {
            $this->_values = Mage::registry('current_post')->getData();
        }

        # Correct timezone for Published At
        $publishedAt = isset($this->_values['published_at']) ? $this->_values['published_at'] : null;
        if ($publishedAt){

            try {
                $publishedAt = new Zend_Date($publishedAt, Zend_Date::ISO_8601);
            } catch (Exception $e){
                $publishedAt = new Zend_Date($publishedAt, Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            }

            $publishedAt->subSecond($this->_helper()->getTimezoneOffset());
            $this->_values['published_at'] = $publishedAt->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        $currentDate = new Zend_Date();
        $currentDate->subSecond($this->_helper()->getTimezoneOffset());
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $this->_values['current_date'] = $currentDate->toString($format);

        if (!isset($this->_values['status'])){
            $this->_values['status'] = Magpleasure_Blog_Model_Post::STATUS_HIDDEN;
        }

        $this->_values['current_status'] = $this->_values['status'];

        return $this->_values;
    }

    protected function _isNew()
    {
        return !(Mage::registry('current_post') && Mage::registry('current_post')->getId());
    }

    protected function _getGenerateUrlKeyButtonHtml()
    {
        /** @var $button Mage_Adminhtml_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');

        if ($button){
            $button->addData(array(
                'label' => $this->_helper()->__("Update"),
                'title' => $this->_helper()->__("Update"),
                'onclick' => "Transliteration.transliterate($('title').value.replace('/',' '), 'url_key'); return false;",
                'style'   => 'display: none;',
                'id'    => 'generate_permalink'

            ));
            return $button->toHtml()."
            <script type=\"text/javascript\">
            $('title').observe('blur', function(e){
                if (!$('url_key').value){
                    Transliteration.transliterate($('title').value.replace('/',' '), 'url_key');
                    $('generate_permalink').style.display = 'inline';
                }
            });
            </script>
            ";
        }
        return "";
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => $this->_helper()->__('Content')));

        $fieldset->addField('title', 'text', array(
            'label' => $this->_helper()->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
            'style'  => 'min-width: 36em; width: 100%;',
        ));

        $fieldset->addField('url_key', 'text', array(
            'label' => $this->_helper()->__('URL Key'),
            'class' => 'validate-identifier',
            'required' => true,
            'name' => 'url_key',
            'style' => 'display: inline; width: 29em;',
            'note' => $this->_helper()->__('Relative to Website Base URL'),
            'after_element_html' => $this->_isNew() ? $this->_getGenerateUrlKeyButtonHtml() : "",
        ));


        $fieldset->addField('current_status', 'hidden',
            array(
                'name'      => 'current_status',
            ));

        $fieldset->addType('mp_short_editor', 'Magpleasure_Blog_Block_Adminhtml_Widget_Form_Short');
        $fieldset->addType('mp_full_editor', 'Magpleasure_Blog_Block_Adminhtml_Widget_Form_Wysiwyg');

        $values = $this->_getValues();
        $fieldset->addField('short_content', 'mp_short_editor', array(
            'name'      => 'short_content',
            'label'     => $this->_helper()->__('Short Content'),
            'title'     => $this->_helper()->__('Short Content'),
            'style'     => 'min-width: 36em; width: 100%;',
            'required'  =>  false,
            'display_short_content' => isset($values['display_short_content']) ? $values['display_short_content'] : null,
        ));

        $fieldset->addField('full_content', 'mp_full_editor', array(
            'name'      => 'full_content',
            'label'     => $this->_helper()->__('Content'),
            'title'     => $this->_helper()->__('Content'),
            'style'     => 'min-width: 36em; width: 100%;',
            'required'  => true,
            'min_height'=> 300,
        ));

        $fieldset->addType('tags', 'Magpleasure_Common_Block_System_Entity_Form_Element_Ajax_Tags');
        $fieldset->addField('tags', 'tags', array(
            'label' => $this->_helper()->__('Tags'),
            'required' => false,
            'name' => 'tags',
            'style'     => 'width: 567px;',
            'note'  => $this->_helper()->__("Please enter Tags separated by comma"),
            'data_source' => array(
                'filter_field' => 'name',
                'sort_field' => 'name',
                'sort_direction' => 'ASC',
                'entity_id_pattern' => "{{name}}",
                'entity_label_pattern' => "{{name}}",
                'model' => 'mpblog/tag',
            ),
        ));



        $fieldset = $form->addFieldset(
            'blog_publish_status',
            array(
                'legend' => $this->_helper()->__('Publish Status')
            )
        );

        /** @var Magpleasure_Blog_Model_Post $post  */
        $post = Mage::getSingleton('mpblog/post');
        $fieldset->addField('status', 'select',
            array(
                'name'      => 'status',
                'label'     => $this->_helper()->__('Status'),
                'values'    => $post->toOptionArray(),
                'onchange'  => "changePostStatus(this);",
            ));

        $values = new Varien_Object($this->_getValues());
        $_isNew = !$values->getCreatedAt();

        $fieldset->addField('user_define_publish', 'hidden', array(
            'name'  => 'user_define_publish',
            'default' => '0',
        ));

        $statusEnabled = Magpleasure_Blog_Model_Post::STATUS_ENABLED;
        $statusScheduled = Magpleasure_Blog_Model_Post::STATUS_SCHEDULED;
        $message = $this->_helper()->__("Post is scheduled. Do you want to publish it right now?");

        $imagePath = $this->getSkinUrl('images/grid-cal.gif');
        $showLabel = $this->_helper()->__("Define Publish Time");
        $cancelLabel = $this->_helper()->__("Publish right after Save");

        $showStyle = $values->getUserDefinePublish() ? 'display: none;' : 'display: inline;';
        $cancelStyle = $values->getUserDefinePublish() ? 'display: inline;' : 'display: none;';

        $html = "
        <a  href=\"#\"
            onclick=\"definePublishDate(); return false;\"
            id=\"published_at_show_button\"
            style=\"{$showStyle}\">{$showLabel}</a>

        <a  href=\"#\"
            onclick=\"publishRightNow(); return false;\"
            id=\"published_at_cancel_button\"
            style=\"{$cancelStyle}\">{$cancelLabel}</a>

        <script type=\"text/javascript\">
        //<![CDATA[

            $('published_at_trig').style.display = 'none';
            $('published_at_trig').src = '{$imagePath}';

            var definePublishDate = function(){

                Effect.Appear('published_at_trig', {duration: 0.5});
                Effect.Appear('published_at', {duration: 0.5, afterFinish: function(e){
                    $('published_at_show_button').style.display = 'none';
                    $('published_at_cancel_button').style.display = 'inline';
                    $('user_define_publish').value = '1';
                }});
            };

            var publishRightNow = function(){

                Effect.Fade('published_at_trig', {duration: 0.3});
                Effect.Fade('published_at', {duration: 0.3, afterFinish: function(e){
                    $('published_at_show_button').style.display = 'inline';
                    $('published_at_cancel_button').style.display = 'none';
                    $('user_define_publish').value = '0';

                    $('status').value = '{$statusEnabled}';
                }});


            };

            var changePostStatus = function(el){
                var currentStatus = $('current_status').value;
                var selectedStatus = $('status').value;
                var statusEnabled = '{$statusEnabled}';
                var statusScheduled = '{$statusScheduled}';
                var newStatus = $(el).value;

                if ((currentStatus == statusScheduled) && (newStatus == statusEnabled)){
                    if (confirm('{$message}')){
                        $('published_at').value = $('current_date').value;
                    } else {
                        $(el).value = statusScheduled;
                    }
                }

                if (selectedStatus == statusScheduled){
                    $('notify_on_enable').parentNode.parentNode.style.display = '';
                    $('published_at').addClassName('required-entry');
                } else {
                    $('notify_on_enable').parentNode.parentNode.style.display = 'none';
                    $('published_at').removeClassName('required-entry');
                }
            };

            document.observe('dom:loaded', function(){
                changePostStatus($('status'));
            });

        //]]>
        </script>
        ";

        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('current_date', 'hidden', array(
            'name'      => 'current_date',
            'time'      => true,
        ));

        $fieldset->addField('published_at', 'date', array(
            'label'  => $this->_helper()->__('Publish Date'),
            'title'  => $this->_helper()->__('Publish Date'),
            'name'      => 'published_at',
            'time'      => true,
            'style'     => !$_isNew ? 'width: 110px !important; display: inline;' : 'width: 110px !important; display: none;',
            'image'     => !$_isNew ? $imagePath : null,
            'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
            'format'       => $outputFormat,
            'after_element_html' => $_isNew ? $html : '',
        ));

        $fieldset->addField('notify_on_enable', 'select', array(
            'label'     => $this->_helper()->__("Send Notification On Enabling"),
            'required'  => false,
            'name'      => 'notify_on_enable',
            'options'   => $this->_helper()
                ->getCommon()
                ->getArrays()
                ->valueLabelToParams(
                    Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
                ),
        ));

        $form->setValues($this->_getValues());
        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("Content");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_helper()->__("Content");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}