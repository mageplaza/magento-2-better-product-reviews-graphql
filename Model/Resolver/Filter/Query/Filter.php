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
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\SearchResult;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\SearchResultFactory;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\DataProvider\Review;
use Magento\Review\Model\Review as ReviewModel;

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
     * Filter constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param Review $review
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Review $review
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->_review             = $review;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string $type
     *
     * @param null $collection
     *
     * @return SearchResult
     */
    public function getResult(
        SearchCriteriaInterface $searchCriteria,
        $collection = null
    ): SearchResult {
        $this->changeFieldInFiilters($searchCriteria);

        $list = $this->_review->getList($searchCriteria, $collection);

        $listArray = [];
        /** @var ReviewModel $item */
        foreach ($list->getItems() as $item) {
            $listArray[$item->getId()]          = $item->getData();
            $listArray[$item->getId()]['model'] = $item;
        }

        return $this->searchResultFactory->create($list->getTotalCount(), $listArray);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function changeFieldInFiilters(SearchCriteriaInterface $searchCriteria)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'entity_pk_value') {
                    $searchCriteria->getFilterGroups()[0]->getFilters()[0]->setField('main_table.entity_pk_value');
                }
            }
        }
    }
}
