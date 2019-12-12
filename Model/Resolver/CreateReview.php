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

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviews\Model\Config\Source\System\CustomerRestriction;

/**
 * Class CreateReview
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver
 */
class CreateReview implements ResolverInterface
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
     * CreateReview constructor.
     *
     * @param RatingFactory $ratingFactory
     * @param Product $productModel
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Data $helperData
     * @param OrderFactory $orderFactory
     * @param ReviewFactory $reviewFactory
     */
    public function __construct(
        RatingFactory $ratingFactory,
        Product $productModel,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Data $helperData,
        OrderFactory $orderFactory,
        ReviewFactory $reviewFactory
    ) {
        $this->_rating                      = $ratingFactory;
        $this->_review                      = $reviewFactory;
        $this->_product                     = $productModel;
        $this->_orderFactory                = $orderFactory;
        $this->_helperData                  = $helperData;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return mixed|Value
     * @throws Exception
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $data      = $args['input'];
        $productId = $args['productId'];

        if (!$this->_product->load($productId)->getId()) {
            throw new GraphQlInputException(__('The Product does not exit.'));
        }

        $storeId    = isset($data['store_id']) ? $data['store_id'] : 1;
        $customerId = $this->isUserGuest($context->getUserId(), $productId);
        $avgValue   = isset($data['avg_value']) ? (int) $data['avg_value'] : 5;

        if ($customerId === false || $avgValue > 5 || $avgValue < 0) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $status  = isset($data['status_id']) ? $data['status_id'] : Review::STATUS_PENDING;
        $ratings = $this->getRatingCollection($storeId);
        $object  = $this->_review->create()->setData($data);
        $object->unsetData('review_id');

        if ($object->validate()) {
            $object->setEntityId($object->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                ->setEntityPkValue($productId)
                ->setStatusId($status)
                ->setCustomerId($customerId)
                ->setStoreId($storeId)
                ->setStores([$storeId])
                ->save();
            foreach ($ratings as $ratingId => $rating) {
                foreach ($rating->getOptions() as $option) {
                    if ((int) $option->getValue() === $avgValue) {
                        $this->_rating->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($object->getId())
                            ->setCustomerId($customerId)
                            ->addOptionVote($option->getId(), $productId);
                    }
                }
            }
            $object->aggregate();
            $collection = $object->getCollection();
            $collection->getSelect()->join(
                ['mp_detail' => $collection->getTable('review_detail')],
                'main_table.review_id = mp_detail.review_id',
                ['mp_bpr_images', 'mp_bpr_recommended_product', 'mp_bpr_verified_buyer', 'mp_bpr_helpful']
            )->join(
                ['mp_vote' => $collection->getTable('rating_option_vote')],
                'main_table.review_id = mp_vote.review_id',
                ['avg_value' => 'mp_vote.value']
            )->where('main_table.review_id = ?', $object->getId())->group('main_table.review_id');

            return $collection->getFirstItem();
        }

        return [];
    }

    /**
     * @param $storeId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getRatingCollection($storeId)
    {
        return $this->_rating->create()->getResourceCollection()->addEntityFilter(
            'product'
        )->setPositionOrder()->addRatingPerStoreName(
            $storeId
        )->setStoreFilter(
            $storeId
        )->setActiveFilter(
            true
        )->load()->addOptionToItems();
    }

    /**
     * @param $currentUserId
     * @param $productId
     *
     * @return bool|null
     * @throws LocalizedException
     */
    public function isUserGuest($currentUserId, $productId)
    {
        if ($this->_helperData->isEnabled() && $this->isEnableWrite($currentUserId, $productId)) {
            $mpGroupArray = explode(',', $this->_helperData->getWriteReviewConfig('customer_group'));
            try {
                $customerGroup = $this->_customerRepositoryInterface->getById($currentUserId)->getGroupId();
            } catch (NoSuchEntityException $exception) {
                $customerGroup = '0';
            }

            if (in_array($customerGroup, $mpGroupArray, true)) {
                return $currentUserId ?: null;
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
    public function isEnableWrite($customerId, $productId)
    {
        if ((int) $this->_helperData->getWriteReviewConfig('enabled') !== CustomerRestriction::PURCHASERS_ONLY) {
            return (bool) $this->_helperData->getWriteReviewConfig('enabled');
        }

        $result = false;

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
                    if ($productId == $item->getProductId()) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }
}
