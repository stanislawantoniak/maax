<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 24.07.2014
 */

class Zolago_Modago_Block_Review_Product_View_List extends Mage_Review_Block_Product_View_List
{
    /**
     * Reviews collection
     *
     * @var null
     */
    protected $_reviews = null;

    /**
     * Total divider for stars.
     *
     * Base number is 100% divided by this variable gives us total number of stars
     * @var int
     */
    protected $_total_divider = 20;

    protected function _construct()
    {
        parent::_construct();

        $collection = $this->getReviewsCollection();

        $pager = new Mage_Page_Block_Html_Pager();
        $pager->setCollection($collection)
            ->setShowPerPage(false)
            ->setShowAmounts(false)
            ->setUseContainer(false)
            ->setTemplate('page/html/pager-reviews.phtml');

        $this->setChild('product_review_list.toolbar', $pager);
    }

    /**
     * Returns reviews collection
     *
     * @return Mage_Review_Model_Resource_Review_Collection|null
     */
    public function getReviewsForProduct()
    {
        if(!$this->_reviews) {
            $this->_reviews = Mage::getModel('review/review')
                ->getResourceCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addEntityFilter('product', $this->getProductId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();
        }

        return $this->_reviews;
    }

    /**
     * Returns avarage rating for product
     *
     * @return float|int
     */
    public function getAvarageRating()
    {
        $reviews = $this->getReviewsForProduct();
        if(count($reviews) == 0) {
            return 0;
        }
        $avg = 0;
        /** @var $review Mage_Review_Model_Review */
        /** @var $vote Mage_Rating_Model_Rating_Option_Vote */
        $sum = 0;
        $subSum = 0;
        foreach ($reviews as $review) {
            $subSum = 0;
            foreach ($review->getRatingVotes() as $vote) {
                $subSum += $vote->getPercent();
            }
            if($subSum == 0) {
                continue;
            }
            $sum += count($review->getRatingVotes()) > 0 ? ceil($subSum / $this->_total_divider / count($review->getRatingVotes())) : 0;
        }

        return ceil($sum / count($reviews));
    }

    /**
     * [ KEY => RATING CODE
     *  [
     *      'sum' => sum of all ratings in review
     *      'avg' => avarage rating
     *      'count' => count of ratings in single review
     *  ]
     * ]
     * Raturns structure for ratings
     *
     * @return array
     */
    public function getRatingsAvarage()
    {
        $reviews = $this->getReviewsForProduct();

        $ratings = array();
        foreach ($reviews as $review) {
            foreach ($review->getRatingVotes() as $vote) {
                if(!array_key_exists($vote->getRatingCode(), $ratings)) {
                    $ratings[$vote->getRatingCode()] = array(
                        'count' => 0,
                        'sum' => 0
                    );
                }
                $ratings[$vote->getRatingCode()]['count']++;
                $ratings[$vote->getRatingCode()]['sum'] += $vote->getPercent();
            }
        }

        foreach ($ratings as $k => $v) {
            if($v['sum'] == 0) {
                $ratings[$k]['avg'] = 0;
            } else {
                $ratings[$k]['avg'] = ceil($v['sum'] / $this->_total_divider / $v['count']);
            }
        }

        return $ratings;
    }

    /**
     * Returns structure for rating strips.
     *
     * @return array
     */
    public function getStripsRatings()
    {
        $reviews = $this->getReviewsForProduct();

        $strips = array_fill(1, 5, 0);
        $subSum = 0;
        $avg = 0;
        foreach ($reviews as $review) {
            $subSum = 0;
            foreach ($review->getRatingVotes() as $vote) {
                $subSum += $vote->getPercent();
            }
            if($subSum == 0) {
                continue;
            }
             $avg = count($review->getRatingVotes()) > 0 ? ceil($subSum / $this->_total_divider / count($review->getRatingVotes())) : 0;
            $strips[$avg] += 100;
        }

        foreach ($strips as $k => $v) {
            $strips[$k] = count($reviews) > 0 ? ceil($v / count($reviews)) : 0;
        }

        return $strips;
    }

    /**
     * Returns avarage rating for single review.
     *
     * @param $review
     * @return float|int
     */
    public function getSingleReviewAvarage($review)
    {
        $avg = 0;
        $sum = 0;
        foreach ($review->getRatingVotes() as $vote) {
            $sum += $vote->getPercent();
        }

        $avg = count($review->getRatingVotes()) > 0 ? ceil($sum / $this->_total_divider / count($review->getRatingVotes())) : 0;

        return $avg;

    }
}