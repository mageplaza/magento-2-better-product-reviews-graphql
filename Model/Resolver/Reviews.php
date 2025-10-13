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

namespace Mageplaza\BetterProductReviewsGraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Collection;
use Mageplaza\BetterProductReviews\Model\ResourceModel\Review\CollectionFactory;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\Query\Filter;

/**
 * Class Reviews
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver
 */
class Reviews implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Filter
     */
    protected $filterQuery;

    /**
     * @var CollectionFactory
     */
    protected $reviewCollection;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Posts constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionFactory $reviewCollection
     * @param Product $product
     * @param Data $_helperData
     * @param Filter $filterQuery
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $reviewCollection,
        Product $product,
        Data $_helperData,
        Filter $filterQuery
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reviewCollection      = $reviewCollection;
        $this->_product              = $product;
        $this->filterQuery           = $filterQuery;
        $this->_helperData           = $_helperData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validateArgs($args);
        $searchCriteria = $this->searchCriteriaBuilder->build('reviews', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        if (!$this->_helperData->isEnabled()) {
            return [
                'total_count' => 0,
                'items'       => [],
                'pageInfo'    => [
                    'pageSize'        => $args['pageSize'],
                    'currentPage'     => $args['currentPage'],
                    'hasNextPage'     => false,
                    'hasPreviousPage' => false,
                    'startPage'       => 1,
                    'endPage'         => 1,
                ]
            ];
        }

        switch ($args['action']) {
            case 'get_all_review':
                $collection = null;
                break;
            case 'get_view_review':
                $collection = $this->getViewReview($args);
                break;
            case 'get_by_productId':
                $collection = $this->getByProductId($args);
                break;
            case 'get_by_productSku':
                $collection = $this->getByProductSku($args);
                break;
            case 'get_by_customerId':
                $collection = $this->getByCustomerId($args);
                break;
            default:
                throw new GraphQlInputException(__('No find your function'));
        }

        if (isset($collection)) {
            $defaultDirection = $this->_helperData->getReviewListingConfig('sorting/default_sort_direction');

            switch ($args['sortType']) {
                case 'newest':
                    $collection->setDateOrder($defaultDirection);
                    break;
                case 'high_rating':
                    $collection->setVotingOrder($defaultDirection);
                    break;
                case 'helpfulness':
                    $collection->setHelpfulnessOrder($defaultDirection);
                    break;
                case 'date':
                    if ($args['fromDate'] && $args['toDate']) {
                        $collection->setDateByRange([$args['fromDate'], $args['toDate']], $defaultDirection);
                    } else {
                        throw new GraphQlInputException(__('Require fromDate and toDate for filter by date'));
                    }
                    break;
            }
        }

        $searchResult = $this->filterQuery->getResult($searchCriteria, $collection);

        //possible division by 0
        if ($searchCriteria->getPageSize()) {
            $maxPages = ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize());
        } else {
            $maxPages = 0;
        }

        $currentPage = $searchCriteria->getCurrentPage();
        if ($searchCriteria->getCurrentPage() > $maxPages && $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$currentPage, $maxPages]
                )
            );
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $searchResult->getItemsSearchResult(),
            'pageInfo'    => [
                'pageSize'        => $args['pageSize'],
                'currentPage'     => $args['currentPage'],
                'hasNextPage'     => $currentPage < $maxPages,
                'hasPreviousPage' => $currentPage > 1,
                'startPage'       => 1,
                'endPage'         => $maxPages,
            ]
        ];
    }

    /**
     * @param $args
     *
     * @return Collection
     * @throws GraphQlInputException
     */
    protected function getViewReview($args) : Collection
    {
        if (!isset($args['reviewId'])) {
            throw new GraphQlInputException(__('reviewId value is not null'));
        }
        $collection = $this->getReviewCollection();
        $collection->addFieldToFilter('main_table.review_id', $args['reviewId']);

        return $collection;
    }

    /**
     * @param $args
     *
     * @return Collection
     * @throws GraphQlInputException
     */
    protected function getByProductId($args) : Collection
    {
        if (!isset($args['productId'])) {
            throw new GraphQlInputException(__('productId value is not null'));
        }
        $product = $this->_product->load($args['productId']);

        if (!$product->getId()) {
            throw new GraphQlInputException(__('No element found matching the given condition.'));
        }

        $collection = $this->getReviewCollection();
        $collection->addFieldToFilter('main_table.entity_pk_value', $args['productId']);

        return $collection;
    }

    /**
     * @param $args
     *
     * @return Collection
     * @throws GraphQlInputException
     */
    protected function getByProductSku($args) : Collection
    {
        if (!isset($args['productSku'])) {
            throw new GraphQlInputException(__('Action value is not null'));
        }
        $product = $this->_product->loadByAttribute('sku', $args['productSku']);

        if (!$product) {
            throw new GraphQlInputException(__('No element found matching the given condition.'));
        }

        $collection = $this->getReviewCollection();
        $collection->addFieldToFilter('main_table.entity_pk_value', $product->getId());

        return $collection;
    }

    /**
     * @param $args
     *
     * @return Collection
     * @throws GraphQlInputException
     */
    protected function getByCustomerId($args) : Collection
    {
        if (!isset($args['customerId'])) {
            throw new GraphQlInputException(__('customerId value is not null'));
        }

        $collection = $this->getReviewCollection();

        if ($args['customerId'] === 0) {
            $collection->addFieldToFilter('detail.customer_id', ['null' => true]);
        } else {
            $collection->addFieldToFilter('detail.customer_id', $args['customerId']);
        }

        return $collection;
    }

    /**
     * @return Collection
     */
    protected function getReviewCollection() : Collection
    {
        $collection = $this->reviewCollection->create()->addReviewDetailTable()->addAverageVotingTable();
        if ($this->_helperData->getReviewListingConfig('store_owner_answer')) {
            $collection->addReviewReplyTable();
        }

        return $collection;
    }

    /**
     * @param array $args
     *
     * @throws GraphQlInputException
     */
    protected function validateArgs(array $args)
    {
        if (!isset($args['action'])) {
            throw new GraphQlInputException(__('Action value is not null'));
        }

        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if ($args['action'] !== 'get_all_review' && !isset($args['sortType'])) {
            throw new GraphQlInputException(__('sortType value is not null'));
        }

        $sortTypes = ['newest', 'high_rating', 'helpfulness', 'date'];
        if (!in_array($args['sortType'], $sortTypes)) {
            throw new GraphQlInputException(__('sortType value is not valid.'));
        }

        if ($args['sortType'] === 'date') {
            if (empty($args['fromDate']) || empty($args['toDate'])) {
                throw new GraphQlInputException(__('Require fromDate and toDate when sortType is date.'));
            }

            $datePattern = '/^\d{4}-\d{2}-\d{2}$/';
            if (!preg_match($datePattern, $args['fromDate'])) {
                throw new GraphQlInputException(__('fromDate format is invalid. Expected format: YYYY-MM-DD'));
            }
            if (!preg_match($datePattern, $args['toDate'])) {
                throw new GraphQlInputException(__('toDate format is invalid. Expected format: YYYY-MM-DD'));
            }
        }
    }
}
