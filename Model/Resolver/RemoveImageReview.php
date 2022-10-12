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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Review\Model\Review;

/**
 * Class RemoveImageReview
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver
 */
class RemoveImageReview extends AbstractImageReview implements ResolverInterface
{
    /**
     * @param array $args
     *
     * @return Review|string
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    protected function processImage($args)
    {
        if (!isset($args['position']) || empty($args['position'])) {
            throw new GraphQlInputException(__('"position" value should be specified'));
        }
        $reviewId   = $args['reviewId'];
        $position   = $args['position'];
        try {
            $review = $this->_imageHelper->removeImage($reviewId, $position);
        } catch (Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }

        return $review;
    }
}
