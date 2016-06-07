<?php
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * More Extension admin block
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Moreextension extends Mage_Adminhtml_Block_Widget_Grid_Container{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct(){
		$this->_controller 		= 'adminhtml_moreextension';
		$this->_blockGroup 		= 'productcarousel';
		parent::__construct();
	}
    
    protected function _toHtml()
    {
        $html = '';
        try {
            $html = file_get_contents('http://www.magpassion.com/magento.html');;
        }
        catch (Exception $e) {$html = '<h3>Magento extensions by MagPassion</h3>';}
        
        return $html;
    }
}