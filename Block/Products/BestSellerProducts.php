<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mesto\Custom\Block\Products;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;

class BestSellerProducts extends AbstractProduct
{
    protected $_bestSellersCollectionFactory;
	protected $urlHelper;

    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        HttpContext $httpContext,
		UrlHelper $urlHelper,
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        array $data = []
    )
    {
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
		$this->httpContext               = $httpContext;
		$this->urlHelper = $urlHelper;

        parent::__construct($context, $data);
    }

    /**
     * Get Product Collection of Best-Seller Products
     */
    public function getProductCollection()
    {
        $productIds  = [];
        $bestSellers = $this->_bestSellersCollectionFactory->create()
            ->setPeriod('month');

        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
			->addIdFilter($productIds)
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->getStoreId())
            ->setPageSize($this->getProductsCount())
			->setCurPage(1)
			->addAttributeToFilter('status', '1');
			
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        return $collection;
    }
	
	/**
     * Get Block Title
     */
    public function getBlockTitle()
    {
        if ($this->hasData('title')) {
            return $this->getData('title');
        }

        return '';
    }

	/**
     * Get Block Description
     */
    public function getDescription()
    {
        if ($this->hasData('description')) {
            return $this->getData('description');
        }

        return '';
    }

	/**
     * Get Product Count is displayed
     */
    public function getProductsCount()
    {
        if ($this->hasData('products_count')) {
            return $this->getData('products_count');
        }
        return 5;
    }
	
	/**
     * Get Store Id
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

	/**
     * Retrieve add to cart params
     */
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
}