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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\BetterProductReviews\Helper\Data;

/**
 * Class GetConfig
 * @package Mageplaza\BetterProductReviewsGraphQl\Model\Resolver
 */
class GetConfig implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Get constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $store   = $context->getExtensionAttributes()->getStore();
        $storeId = (int) $store->getId();

        if (!$this->helper->isEnabled($storeId)) {
            throw new GraphQlInputException(__('The Better Product Reviews module is disabled.'));
        }

        /** @var array $storeConfigs */
        $storeConfigs = $this->helper->getConfigValue(Data::CONFIG_MODULE_PATH, $storeId);

        if (!is_array($storeConfigs)) {
            return [];
        }

        if (isset($storeConfigs['general']['email']) && is_string($storeConfigs['general']['email'])) {
            $emailDecoded = json_decode($storeConfigs['general']['email'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($emailDecoded)) {
                $emails = [];
                foreach ($emailDecoded as $item) {
                    $emails[] = [
                        'send'     => isset($item['send']) ? (bool)$item['send'] : false,
                        'sender'   => $item['sender'] ?? '',
                        'template' => $item['template'] ?? ''
                    ];
                }
                $storeConfigs['general']['email'] = $emails;
            }
        }

        if (isset($storeConfigs['write_review']['customer_group'])) {
            $storeConfigs['write_review']['customer_group'] = $this->csvToArray($storeConfigs['write_review']['customer_group']);
        }

        if (isset($storeConfigs['write_review']['order_status'])) {
            $storeConfigs['write_review']['order_status'] = $this->csvToArray($storeConfigs['write_review']['order_status']);
        }

        if (isset($storeConfigs['review_listing']['sorting']['type'])) {
            $storeConfigs['review_listing']['sorting']['type'] = $this->csvToArray($storeConfigs['review_listing']['sorting']['type']);
        }

        if (isset($storeConfigs['order']['exclude'])) {
            $storeConfigs['order']['exclude'] = $this->csvToArray($storeConfigs['order']['exclude']);
        }
        if (isset($storeConfigs['order']['include'])) {
            $storeConfigs['order']['include'] = $this->csvToArray($storeConfigs['order']['include']);
        }

        return $storeConfigs;
    }

    /**
     * Convert comma-separated string to array
     *
     * @param string|array|null $value
     * @return array
     */
    private function csvToArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            return array_map('trim', explode(',', $value));
        }

        return [];
    }
}
