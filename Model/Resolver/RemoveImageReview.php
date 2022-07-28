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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviews\Helper\Image;
use Mageplaza\BetterProductReviews\Model\Config\Source\System\CustomerRestriction;

/**
 * Class RemoveImageReview
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver
 */
class RemoveImageReview implements ResolverInterface
{
    /**
     * @var RatingFactory
     */
    protected $_rating;

    /**
     * @var ReviewFactory
     */
    protected $_review;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * CreateReview constructor.
     *
     * @param RatingFactory $ratingFactory
     * @param Product $productModel
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param OrderFactory $orderFactory
     * @param ReviewFactory $reviewFactory
     * @param Image $imageHelper
     */
    public function __construct(
        RatingFactory $ratingFactory,
        Product $productModel,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Data $helperData,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        ReviewFactory $reviewFactory,
        Image $imageHelper
    ) {
        $this->_rating                      = $ratingFactory;
        $this->_review                      = $reviewFactory;
        $this->_product                     = $productModel;
        $this->_orderFactory                = $orderFactory;
        $this->storeManager                 = $storeManager;
        $this->_helperData                  = $helperData;
        $this->_imageHelper                 = $imageHelper;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['reviewId']) || empty($args['reviewId'])) {
            throw new GraphQlInputException(__('"reviewId" value should be specified'));
        }
        if (!isset($args['position']) || empty($args['position'])) {
            throw new GraphQlInputException(__('"position" value should be specified'));
        }
        $reviewId   = $args['reviewId'];
        $position   = $args['position'];
        $customerId = $context->getUserId() ?: null;
        try {
            $review = $this->_imageHelper->removeImage($reviewId, $position);
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }

        return $review;
    }

    /**
     * @param string $currentUserId
     * @param $productId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isUserGuest($currentUserId, $productId): bool
    {
        if ($this->_helperData->isEnabled() && $this->isEnableWrite($currentUserId, $productId)) {
            $allowCustomerGroup = explode(',', $this->_helperData->getWriteReviewConfig('customer_group'));
            try {
                $customerGroup = $this->_customerRepositoryInterface->getById($currentUserId)->getGroupId();
            } catch (NoSuchEntityException $exception) {
                $customerGroup = '0';
            }

            if (in_array($customerGroup, $allowCustomerGroup, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $customerId
     * @param $productId
     *
     * @return bool
     */
    protected function checkIsBuyer($customerId, $productId): bool
    {
        if ($customerId) {
            $orders = $this->_orderFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('state', Order::STATE_COMPLETE);
            foreach ($orders as $order) {
                /**
                 * @var Order $order
                 */
                foreach ($order->getAllVisibleItems() as $item) {
                    /** @var Item $item */
                    if ($productId === (int) $item->getProductId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $customerId
     * @param $productId
     *
     * @return bool
     */
    public function isEnableWrite($customerId, $productId): bool
    {
        if ((int) $this->_helperData->getWriteReviewConfig('enabled') !== CustomerRestriction::PURCHASERS_ONLY) {
            return (bool) $this->_helperData->getWriteReviewConfig('enabled');
        }

        return $this->checkIsBuyer($customerId, $productId);
    }
}
