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
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Filter\SearchResultFactory;

/**
 * Class Topic
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver\Post
 */
class Product implements ResolverInterface
{
    /**
     * @var ProductModel
     */
    protected $_product;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * Product constructor.
     *
     * @param ProductModel $product
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        ProductModel $product,
        SearchResultFactory $searchResultFactory
    ) {
        $this->_product              = $product;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $productId = $value['entity_pk_value'];
        $product = $this->_product->load($productId);
        $listArray = [];
        $listArray[$productId]          = $product->getData();
        $listArray[$productId]['model'] = $product;

        $searchResult = $this->searchResultFactory->create(1, $listArray);

        return $searchResult->getItemsSearchResult()[$productId];
    }
}
