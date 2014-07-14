<?php

class Orba_Common_Block_Page_Html_Head extends Mage_Page_Block_Html_Head {

    /**
     * Add CSS file to HEAD entity with "before" param
     *
     * @param string $name
     * @param string $params
     * @param string $before Only '-' works at the moment
     * @return Mage_Page_Block_Html_Head
     */
    public function addCssExtended($name, $params = "", $before) {
        $this->addItemExtended('skin_css', $name, $params, null, null, $before);
        return $this;
    }

    /**
     * Add JavaScript file to HEAD entity with "before" param
     *
     * @param string $name
     * @param string $params
     * @param string $before Only '-' works at the moment
     * @return Mage_Page_Block_Html_Head
     */
    public function addJsExtended($name, $params = "", $before) {
        $this->addItemExtended('js', $name, $params, null, null, $before);
        return $this;
    }

    /**
     * Add CSS file for Internet Explorer only to HEAD entity with "before" param
     *
     * @param string $name
     * @param string $params
     * @param string $before Only '-' works at the moment
     * @return Mage_Page_Block_Html_Head
     */
    public function addCssIeExtended($name, $params = "", $before) {
        $this->addItemExtended('skin_css', $name, $params, 'IE', null, $before);
        return $this;
    }

    /**
     * Add JavaScript file for Internet Explorer only to HEAD entity with "before" param
     *
     * @param string $name
     * @param string $params
     * @param string $before Only '-' works at the moment
     * @return Mage_Page_Block_Html_Head
     */
    public function addJsIeExtended($name, $params = "", $before) {
        $this->addItem('js', $name, $params, 'IE', null, $before);
        return $this;
    }

    /**
     * Add HEAD Item with "before" param
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @param string $before Only '-' works at the moment
     * @return Mage_Page_Block_Html_Head
     */
    public function addItemExtended($type, $name, $params = null, $if = null, $cond = null, $before = null) {
        if ($type === 'skin_css' && empty($params)) {
            $params = 'media="all"';
        }
        $data = array(
            'type' => $type,
            'name' => $name,
            'params' => $params,
            'if' => $if,
            'cond' => $cond,
        );
        if ($before == '-') {
            $this->_data['items'] = array_merge(array($type . '/' . $name => $data), $this->_data['items']);
        } else {
            $this->_data['items'][$type . '/' . $name] = $data;
        }
        return $this;
    }
    
    /**
     * Gets HEAD html with OrbaLib at the beginning 
     * 
     * @return string
     */
    public function getCssJsHtml() {
        $jslib = Mage::app()->getLayout()->createBlock('core/template')->setTemplate('orbacommon/js/script.phtml')->toHtml();
        return $jslib . parent::getCssJsHtml();
    }

}