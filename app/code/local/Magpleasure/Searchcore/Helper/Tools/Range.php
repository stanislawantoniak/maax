<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

/** Ignore Words Dictionary */
class Magpleasure_Searchcore_Helper_Tools_Range
{
    /**
     * @var array
     */
    protected $_weights = array(
        array('value' => 0.35, 'method' => '_lastUpdateScore'),
        array('value' => 0.3, 'method' => '_frequencyScore'),
        array('value' => 0.25, 'method' => '_distanceScore'),
        array('value' => 0.1, 'method' => '_locationScore'),
    );

    /**
     * @param array $rows
     * @param array $wordIds
     *
     * @return array
     */
    public function getScoredList(array &$rows, array &$wordIds)
    {
        $totalScores = array();

        Varien_Profiler::start("mp::searchcore::prepare_score");

        if (count($rows)) {

            foreach ($rows as $row) {
                $totalScores[$row['index_id']] = 0;
            }

            foreach ($this->_weights as $weight) {

                $weightMethod = $weight['method'];
                $weightValue = $weight['value'];

                $scores = $this->$weightMethod($rows, $wordIds);

                foreach ($scores as $indexId => $score) {

                    $totalScores[$indexId] += $weightValue * $score;
                }
            }
        }

        Varien_Profiler::stop("mp::searchcore::prepare_score");

        return $totalScores;
    }

    /**
     * @param       $rows
     * @param array $wordIds
     *
     * @return array
     */
    protected function _frequencyScore($rows, array $wordIds)
    {
        $frequencyScore = array();
        foreach ($rows as $row) {
            if (isset($frequencyScore[$row['index_id']])) {
                $frequencyScore[$row['index_id']]++;
            } else {
                $frequencyScore[$row['index_id']] = 1;
            }
        }

        $this->_normalizeScores($frequencyScore);

        return $frequencyScore;
    }

    /**
     * Normalize Score Array
     *
     * @param      $scores
     * @param bool $smallIsBetter
     */
    protected function _normalizeScores(&$scores, $smallIsBetter = false)
    {
        $vSmall = 0.00001;

        if ($smallIsBetter) {
            $minScore = min(array_values($scores));
            foreach ($scores as $key => &$value) {
                $value = (float)$minScore / max($vSmall, $value);
            }
        } else {
            $maxScore = max(array_values($scores));
            $maxScore = ($maxScore == 0) ? $vSmall : $maxScore;
            foreach ($scores as $key => &$value) {

                $value = (float)$value / $maxScore;
            }
        }
    }

    /**
     * @param       $rows
     * @param array $wordIds
     *
     * @return array
     */
    protected function _locationScore($rows, array $wordIds)
    {
        $wordCount = count($wordIds);
        $locationScore = array();
        foreach ($rows as $row) {
            $locationScore[$row['index_id']] = 1000000;
        }

        foreach ($rows as $row) {

            $location = 0;
            for ($i = 1; $i <= $wordCount; $i++) {
                $location += $row[(string)$i];
            }

            if ($location < $locationScore[$row['index_id']]) {
                $locationScore[$row['index_id']] = $location;
            }
        }

        $this->_normalizeScores($locationScore, true);

        return $locationScore;
    }

    /**
     * @param       $rows
     * @param array $wordIds
     *
     * @return array
     */
    protected function _distanceScore($rows, array $wordIds)
    {
        $wordCount = count($wordIds);
        $distanceScore = array();

        # If only one word in request, any result win
        if ($wordCount <= 1) {
            foreach ($rows as $row) {
                $distanceScore[$row['index_id']] = 1;
            }

            return $distanceScore;
        }

        foreach ($rows as $row) {
            $distanceScore[$row['index_id']] = 1000000;
        }

        foreach ($rows as $row) {
            $distance = 0;
            for ($i = 1; $i < $wordCount; $i++) {
                $distance += abs($row[(string)$i] - $row[(string)($i + 1)]);
            }

            if ($distance < $distanceScore[$row['index_id']]) {
                $distanceScore[$row['index_id']] = $distance;
            }
        }

        $this->_normalizeScores($distanceScore, true);

        return $distanceScore;
    }

    /**
     * @param       $rows
     * @param array $wordIds
     *
     * @return array
     */
    protected function _lastUpdateScore($rows, array $wordIds)
    {
        $lastUpdateScore = array();
        $currentTime = time();
        foreach ($rows as $row) {
            $lastUpdateScore[$row['index_id']] = $currentTime - strtotime($row['updated_at']);
        }
        $this->_normalizeScores($lastUpdateScore, true);

        return $lastUpdateScore;
    }
}
