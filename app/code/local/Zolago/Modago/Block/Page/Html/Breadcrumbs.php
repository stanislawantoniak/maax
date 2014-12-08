<?php
/**
 * Zolago Modago Breadcrumbs
 *
 * @category   Zolago
 * @package    Zolago_Modago
 * @author     <victoria.sultanovska@convertica.pl>
 */
class Zolago_Modago_Block_Page_Html_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
    public function addCrumb($crumbName, $crumbInfo, $after = false)
    {
        $this->_prepareArray($crumbInfo, array('label', 'title', 'link', 'first', 'last', 'readonly', 'id'));
        if ((!isset($this->_crumbs[$crumbName])) || (!$this->_crumbs[$crumbName]['readonly'])) {
            $this->_crumbs[$crumbName] = $crumbInfo;
        }
        return $this;
    }
}