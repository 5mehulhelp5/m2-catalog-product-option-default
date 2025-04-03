<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionDefault\Plugin\Checkout\Model;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Cart
{
    /** @var Stores */
    protected $storeHelper;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var Variables */
    protected $variables;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Stores $storeHelper,
        Variables $variables
    ) {
        $this->productRepository = $productRepository;
        $this->storeHelper = $storeHelper;
        $this->variables = $variables;
    }

    /**
     * @throws LocalizedException
     * @noinspection PhpUnusedParameterInspection
     */
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null): array
    {
        if (is_array($requestInfo)) {
            $product = $this->getProduct($productInfo);

            foreach ($product->getOptions() as $option) {
                if ($option->getType() === 'drop_down') {
                    /** @var Value $optionValue */
                    foreach ($option->getValues() as $optionValue) {
                        if ($optionValue->getData('default')) {
                            $add = false;

                            if (! array_key_exists(
                                'options',
                                $requestInfo
                            )) {
                                $add = true;
                            } else {
                                if (! array_key_exists(
                                    $option->getId(),
                                    $requestInfo[ 'options' ]
                                )) {
                                    $add = true;
                                } else {
                                    $selectedValue = $requestInfo[ 'options' ][ $option->getId() ];

                                    if ($this->variables->isEmpty($selectedValue)) {
                                        $add = true;
                                    }
                                }
                            }

                            if ($add) {
                                $requestInfo[ 'options' ][ $option->getId() ] = $optionValue->getOptionTypeId();
                            }
                        }
                    }
                }
            }
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * @param Product|int|string $productInfo
     *
     * @throws LocalizedException
     */
    protected function getProduct($productInfo): Product
    {
        if ($productInfo instanceof Product) {
            $product = $productInfo;
            if (! $product->getId()) {
                throw new LocalizedException(
                    __("The product wasn't found. Verify the product and try again.")
                );
            }
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->storeHelper->getStore()->getId();
            try {
                $product = $this->productRepository->getById(
                    $productInfo,
                    false,
                    $storeId
                );
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(
                    __("The product wasn't found. Verify the product and try again."),
                    $e
                );
            }
        } else {
            throw new LocalizedException(
                __("The product wasn't found. Verify the product and try again.")
            );
        }

        $currentWebsiteId = $this->storeHelper->getStore()->getWebsiteId();

        if (! is_array($product->getWebsiteIds()) || ! in_array(
                $currentWebsiteId,
                $product->getWebsiteIds()
            )) {
            throw new LocalizedException(
                __("The product wasn't found. Verify the product and try again.")
            );
        }

        return $product;
    }
}
