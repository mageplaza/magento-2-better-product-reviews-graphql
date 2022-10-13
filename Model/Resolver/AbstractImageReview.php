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

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviews\Helper\Image;

/**
 * Class AbstractImageReview
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver
 */
abstract class AbstractImageReview implements ResolverInterface
{

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var CreateReview
     */
    protected $_createReview;

    /**
     * AbstractImageReview constructor.
     *
     * @param Data $helperData
     * @param Image $imageHelper
     * @param CreateReview $createReview
     */
    public function __construct(
        Data $helperData,
        Image $imageHelper,
        CreateReview $createReview
    ) {
        $this->_helperData   = $helperData;
        $this->_imageHelper  = $imageHelper;
        $this->_createReview = $createReview;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {

        $data = &$args['input'];

        if (!isset($data['storeId'])) {
            $data['storeId'] = 0;
        }
        if (!$this->_helperData->isEnabled($data['storeId'])) {
            throw new GraphQlNoSuchEntityException(__("The Better Product Reviews is disabled"));
        }
        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            if (!$args['reviewId'] && !$args['position']) {
                throw new GraphQlInputException(__('"input" value should be specified'));
            }
        }
        $customerId = $context->getUserId() ?: null;
        if ($this->_createReview->isUserGuest($customerId, $this->getProductId($args['reviewId'])) === false) {
            $noticeMessage = $this->_helperData->getWriteReviewConfig('notice_message') ?? 'The current customer isn\'t authorized.';
            throw new GraphQlAuthorizationException(__($noticeMessage));
        }

        return $this->processImage($args);
    }

    /**
     * @param $args
     *
     * @return string
     */
    protected function processImage($args)
    {
        return '';
    }

    /**
     * @param $reviewId
     *
     * @return array|mixed|null
     * @throws GraphQlInputException
     */
    protected function getProductId($reviewId)
    {
        try {
            return $this->_imageHelper->getReviewObj($reviewId)->getData('entity_pk_value');
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
