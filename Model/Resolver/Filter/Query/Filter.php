<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterProductReviewsGraphQl
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\Query;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Review\Model\Review as ReviewModel;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\DataProvider\Review;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\SearchResult;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\SearchResultFactory;

/**
 * Retrieve filtered product data based off given search criteria in a format that GraphQL can interpret.
 */
class Filter
{
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var Review
     */
    private $_review;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Filter constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param Data $_helperData
     * @param Review $review
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Data $_helperData,
        Review $review
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->_review             = $review;
        $this->_helperData         = $_helperData;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @param null $collection
     *
     * @return SearchResult
     */
    public function getResult(
        SearchCriteriaInterface $searchCriteria,
        $collection = null
    ): SearchResult {
        $list = $this->_review->getList($searchCriteria, $collection);

        $listArray = [];
        /** @var ReviewModel $item */
        foreach ($list->getItems() as $item) {
            if (!$this->_helperData->getReviewListingConfig('store_owner_answer')) {
                $item->setData('reply_enabled', 0);
                $item->setData('reply_nickname', '');
                $item->setData('reply_content', '');
                $item->setData('reply_created_at', '');
            }
            $listArray[$item->getId()]          = $item->getData();
            $listArray[$item->getId()]['model'] = $item;
        }

        return $this->searchResultFactory->create($list->getTotalCount(), $listArray);
    }
}
