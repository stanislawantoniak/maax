<?php

/**
 * Class Zolago_Solrsearch_Block_Catalog_Product_List_Pager
 */
class Zolago_Solrsearch_Block_Catalog_Product_List_Pager extends Mage_Page_Block_Html_Pager
{
    const DEFAULT_FIRST = 1;

    /**
     * Return array of pages in frame
     *
     * @return array
     */
    public function getFramePages()
    {
        $frame = array();

        $end = $this->getTotalNum();

        $limit = $this->getLimit();

        for ($i = 0; $i < ceil($end / $limit); $i++) {
            $frame[] = $i * $limit + 1;
        }

        //return array(1, 26, 52, 78);
        return $frame;
    }

    public function getFirstNum()
    {
        $request = Mage::app()->getRequest();
        $first = (int)$request->getParam("start", 1);

        if ($first >= 1) {
            if ($first >= $this->getTotalNum()) {
                $first = self::DEFAULT_FIRST;
            }
            return $first;
        }
        return self::DEFAULT_FIRST;
    }

    public function getLastNum()
    {
        return ($this->getFirstNum() - 1) + $this->getLimit();
    }

    public function getPreviousPageUrl()
    {
        $first = $this->getFirstNum();
        $limit = $this->getLimit();

        $prev = $first - $limit;
        if ($prev <= 0) {
            $prev = self::DEFAULT_FIRST;
        }
        return $this->getPageUrl($prev);
    }

    public function getNextPageUrl()
    {
        $next = $this->getFirstNum() + $this->getLimit();
        if ($next >= $this->getTotalNum()) {
            $next = self::DEFAULT_FIRST;
        }
        return $this->getPageUrl($next);
    }

    public function isFirstEnabled()
    {
        return ($this->getFirstNum() !== self::DEFAULT_FIRST);
    }

    public function isLastEnabled()
    {
        $next = $this->getFirstNum() + $this->getLimit();
        return ($next < $this->getTotalNum());
    }
}
