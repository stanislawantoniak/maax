<?php
/**
 * adding tabs groups in admin interface
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tabs extends	
    Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs {

    protected $_sections = array();

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('zolagodropship/tabs.phtml');
        $this->addSection('settings',Mage::helper('zolagodropship')->__('Settings'),10);
    }  
    
    /**
     * add new section
     *
     * @param string $sectionId
     * @param string $title
     * @param int $order
     */
     public function addSection($sectionId,$title,$order = 0) {
         if (isset($this->_sections[$sectionId])) {
             Mage::throwException(sprintf('Section %s exists',$sectionId));
         }
         $this->_sections[$sectionId] = array(
             'order' => $order,
             'title' => $title,
             'tabs' => array(),
         );         
     }     
    /**
     * Add new tab (with assign to section)
     *   
     * @param   string $tabId
     * @param   array|Varien_Object $tab
     * @return  Mage_Adminhtml_Block_Widget_Tabs
     */
     public function addTab($tabId,$tab) {	
         $this->addTabToSection($tabId,'settings');
         return parent::addTab($tabId,$tab);
     }
                                         
    /**
     * assign tab to section
     * @param string $tabId
     * @param string $sectionId
     * @param int $order
     */
     public function addTabToSection($tabId,$sectionId,$order = 0) {
         // remove tab from another section
         foreach ($this->_sections as $id => $section) {
             unset($this->_sections[$id]['tabs'][$tabId]);
         }
         // add tab to new section
         if (!isset($this->_sections[$sectionId])) {
             $this->addSection($sectionId);
         }
         $this->_sections[$sectionId]['tabs'][$tabId] = $order;
     }
     
    /**
     * sort function for sections
     *
     * @param array $a
     * @param array $b
     * @return int
     */
     protected function sectionSort($a,$b) {
         if ($a['order'] == $b['order']) {
             return 0;
         }
         return ($a['order']<$b['order'])? -1:1;
     }
     
    /**
     * prepare display
     */

     protected function _beforeToHtml() {

        $id = Mage::app()->getRequest()->getParam('id', 0);

        $this->addTab('form_section', array(
            'label'     => Mage::helper('udropship')->__('Vendor Information'),
            'title'     => Mage::helper('udropship')->__('Vendor Information'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('preferences_section', array(
            'label'     => Mage::helper('udropship')->__('Preferences'),
            'title'     => Mage::helper('udropship')->__('Preferences'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_preferences', 'vendor.preferences.form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('custom_section', array(
            'label'     => Mage::helper('udropship')->__('Custom Data'),
            'title'     => Mage::helper('udropship')->__('Custom Data'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_custom', 'vendor.custom.form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('shipping_section', array(
            'label'     => Mage::helper('udropship')->__('Shipping methods'),
            'title'     => Mage::helper('udropship')->__('Shipping methods'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_shipping', 'vendor.shipping.grid')
                ->setVendorId($id)
                ->toHtml(),
        ));

        if ($id) {
            $this->addTab('products_section', array(
                'label'     => Mage::helper('udropship')->__('Associated Products'),
                'title'     => Mage::helper('udropship')->__('Associated Products'),
                'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_products', 'vendor.product.grid')
                    ->setVendorId($id)
                    ->toHtml(),
            ));
        }

        if(($tabId = $this->getRequest()->getParam('tab'))) {
            $this->setActiveTab($tabId);
        }

        Mage::dispatchEvent('udropship_adminhtml_vendor_tabs_after', array('block'=>$this, 'id'=>$id));


         // prepare sections and tabs order
         $sections = $this->_sections;         
         uasort($sections,array('Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tabs','sectionSort'));
         foreach ($sections as $id => $section) {
             asort($section['tabs']);             
         }
         $this->assign('sections',$sections);
        return Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
     }
}