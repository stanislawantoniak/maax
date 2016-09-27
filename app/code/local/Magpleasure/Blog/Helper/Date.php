<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Date extends Mage_Core_Helper_Abstract
{
    const DATE_TIME_PASSED = 'passed';
    const DATE_TIME_DIRECT = 'direct';

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Retrieves global timezone
     * @return string
     */
    public function getTimezone()
    {
        return Mage::app()->getStore()->getConfig('general/locale/timezone');
    }

    /**
     * Retrieves global timezone offset in seconds
     *
     * @param boolean $isMysql If true retrieves mysql formmatted offset (+00:00) in hours
     * @return int
     */
    public function getTimeZoneOffset($isMysql = false)
    {
        $date = new Zend_Date();
        $date->setTimezone($this->getTimezone());
        if ($isMysql){
            $offsetInt = -$date->getGmtOffset();
            $offset = ($offsetInt >= 0 ? '+' : '-').sprintf( '%02.0f', round( abs($offsetInt/3600) )).':'.( sprintf('%02.0f', abs( round( ( abs( $offsetInt ) - round( abs( $offsetInt / 3600 )  ) * 3600 ) / 60 ) ) ) );
            return $offset;
        } else {
            return $date->getGmtOffset();
        }
    }


    /**
     * Process Date
     *
     * @param Zend_Date $date
     * @return Zend_Date
     */
    protected function _processTimezone(Zend_Date $date)
    {
        $date->subSecond($this->getTimezoneOffset());
        return $date;
    }

    public function renderTime($datetime, $missTimezone = false)
    {
        if ($datetime instanceof Zend_Date){
            $date = $datetime;
        } else {
            $date = new Zend_Date($datetime, Zend_Date::ISO_8601, Mage::app()->getLocale()->getLocaleCode());
        }

        if (!$missTimezone){
            $date = $this->_processTimezone($date);
        }
        return $date->toString(Zend_Date::TIME_SHORT);
    }

    public function getHumanizedDate(Zend_Date $date)
    {
        $nowDate = new Zend_Date();
        $timestamp = $nowDate->getTimestamp() - $date->getTimestamp();

        if ($date->isToday() || ($timestamp <= 0)){
            return $this->_helper()->__("Today");
        } elseif ($date->isYesterday()) {
            return $this->_helper()->__("Yesterday");
        } else {

            # Nice correction
            $days = round( $timestamp / (3600 * 24) );
            $months = round( $timestamp / (3600 * 24 * 30) );
            $years = round( $timestamp / (3600 * 24 * 30 * 12) );

            if ($days < 30){

                if ($days == 1){
                    return $this->_helper()->__("%s days ago", $days);
                } else {
                    return $this->_helper()->__("%s days ago", $days);
                }

            } elseif ($months < 12) {

                if ($months == 1){
                    return $this->_helper()->__("%s month ago", $months);
                } else {
                    return $this->_helper()->__("%s months ago", $months);
                }

            } else {

                if ($years == 1){
                    return $this->_helper()->__("%s years ago", $years);
                } else {
                    return $this->_helper()->__("%s years ago", $years);
                }
            }
        }
    }

    public function renderDate($datetime, $missTimezone = false, $forceDirect = false)
    {
        if ($datetime instanceof Zend_Date){
            $date = $datetime;
        } else {
            $date = new Zend_Date($datetime, Zend_Date::ISO_8601, Mage::app()->getLocale()->getLocaleCode());
        }

        if (!$missTimezone){
            $date = $this->_processTimezone($date);
        }

        if ($forceDirect || ($this->_helper()->getDateFormat() == self::DATE_TIME_DIRECT)){
            return $date->toString(Zend_Date::DATE_LONG);
        } else {

            return $this->getHumanizedDate($date);
        }
    }
}