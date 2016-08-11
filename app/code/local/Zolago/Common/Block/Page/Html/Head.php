<?php

class Zolago_Common_Block_Page_Html_Head extends Mage_Page_Block_Html_Head {

    // time to live block cache in seconds
    const BLOCK_CACHE_TTL = 3600;
    /**
     * Add locale skin_js depend on current lang
     *
     * @param string $type
     * @param array $name
     */
    public function addLocaleJs($type = 'skin_js', $name = array()) {
        $selectedLang = Mage::app()->getLocale()->getLocaleCode();
        if (isset($name[$selectedLang]) && $name[$selectedLang]) {
            $this->addItem($type, $name[$selectedLang]);
        }
    }

	public function addLocaleJsLast($type = 'skin_js', $name = array()) {
		$selectedLang = Mage::app()->getLocale()->getLocaleCode();
		if (isset($name[$selectedLang]) && $name[$selectedLang]) {
			$this->addItemLast($type, $name[$selectedLang]);
		}
	}

	public function addJsLast($type = 'skin_js',$name) {
		$this->addItemLast($type, $name);
	}

	public function addItemLast($type, $name, $params=null, $if=null, $cond=null)
	{
		if ($type==='skin_css' && empty($params)) {
			$params = 'media="all"';
		}
		$this->_data['itemsLast'][$type.'/'.$name] = array(
			'type'   => $type,
			'name'   => $name,
			'params' => $params,
			'if'     => $if,
			'cond'   => $cond,
		);
		return $this;
	}

	public function removeItemLast($type, $name)
	{
		unset($this->_data['itemsLast'][$type.'/'.$name]);
		return $this;
	}

	public function getCssJsLastHtml()
	{
		$html   = '';
		if(isset($this->_data['itemsLast'])) {
			// separate items by types
			$lines = array();
			foreach ($this->_data['itemsLast'] as $item) {
				if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
					continue;
				}
				$if = !empty($item['if']) ? $item['if'] : '';
				$params = !empty($item['params']) ? $item['params'] : '';
				switch ($item['type']) {
					case 'js':        // js/*.js
					case 'skin_js':   // skin/*/*.js
					case 'js_css':    // js/*.css
					case 'skin_css':  // skin/*/*.css
						$lines[$if][$item['type']][$params][$item['name']] = $item['name'];
						break;
					default:
						$this->_separateOtherHtmlHeadElements($lines, $if, $item['type'], $params, $item['name'], $item);
						break;
				}
			}

			// prepare HTML
			$html = '';
			foreach ($lines as $if => $items) {
				if (empty($items)) {
					continue;
				}
				if (!empty($if)) {
					$html .= '<!--[if ' . $if . ']>' . "\n";
				}

				// static and skin css
				$html .= $this->_prepareStaticAndSkinElements('<link rel="stylesheet" type="text/css" href="%s"%s />' . "\n",
					empty($items['js_css']) ? array() : $items['js_css'],
					empty($items['skin_css']) ? array() : $items['skin_css'],
					null
				);

				// static and skin javascripts
				$html .= $this->_prepareStaticAndSkinElements('<script type="text/javascript" src="%s"%s></script>' . "\n",
					empty($items['js']) ? array() : $items['js'],
					empty($items['skin_js']) ? array() : $items['skin_js'],
					null
				);

				// other stuff
				if (!empty($items['other'])) {
					$html .= $this->_prepareOtherHtmlHeadElements($items['other']) . "\n";
				}

				if (!empty($if)) {
					$html .= '<![endif]-->' . "\n";
				}
			}
		}
		return $html;
	}
}