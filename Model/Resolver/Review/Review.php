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

namespace Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Review;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Collection;
use Mageplaza\BetterProductReviews\Model\ResourceModel\Review\CollectionFactory;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\Query\Filter;

/**
 * Class Review
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Post
 */
class Review implements ResolverInterface
{
    /**
     * @var ProductModel
     */
    protected $_product;

    /**
     * @var CollectionFactory
     */
    protected $reviewCollection;

    /**
     * @var Data
     */
    protected $_helperData;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Filter
     */
    protected $filterQuery;

    /**
     * Product constructor.
     *
     * @param ProductModel $product
     * @param Data $_helperData
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Filter $filterQuery
     * @param CollectionFactory $reviewCollection
     */
    public function __construct(
        ProductModel $product,
        Data $_helperData,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Filter $filterQuery,
        CollectionFactory $reviewCollection
    ) {
        $this->_product              = $product;
        $this->reviewCollection      = $reviewCollection;
        $this->_helperData           = $_helperData;
        $this->filterQuery           = $filterQuery;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $productId      = $value['entity_id'];
        $searchCriteria = $this->searchCriteriaBuilder->build('reviews', $args);
        $searchCriteria->setCurrentPage(1);
        $searchCriteria->setPageSize(10);

        $collection = $this->getReviewCollection();
        $collection->addFieldToFilter('main_table.entity_pk_value', $productId);

        $searchResult = $this->filterQuery->getResult($searchCriteria, $collection);

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $searchResult->getItemsSearchResult()
        ];
    }

    /**
     * @return Collection
     */
    protected function getReviewCollection(): Collection
    {
        $collection = $this->reviewCollection->create()->addReviewDetailTable()->addAverageVotingTable();
        if ($this->_helperData->getReviewListingConfig('store_owner_answer')) {
            $collection->addReviewReplyTable();
        }

        return $collection;
    }
}
