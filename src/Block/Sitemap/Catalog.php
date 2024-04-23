<?php

declare(strict_types=1);

namespace Infrangible\HtmlSitemap\Block\Sitemap;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Category;
use Infrangible\Core\Helper\Product;
use Infrangible\Core\Helper\Stores;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Catalog
    extends Template
{
    /** @var Variables */
    protected $variables;

    /** @var Stores */
    protected $storeHelper;

    /** @var Category */
    protected $categoryHelper;

    /** @var Product */
    protected $productHelper;

    /** @var int|null */
    private $parentId = null;

    /** @var int */
    private $level = 0;

    public function __construct(
        Template\Context $context,
        Variables $variables,
        Stores $storeHelper,
        Category $categoryHelper,
        Product $productHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->variables = $variables;
        $this->storeHelper = $storeHelper;
        $this->categoryHelper = $categoryHelper;
        $this->productHelper = $productHelper;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @param int|null $parentId
     */
    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function renderCategories(?int $parentId = null, int $level = 0): string
    {
        return $this->createCategoriesBlock($parentId, $level)->toHtml();
    }

    public function createCategoriesBlock(?int $parentId = null, int $level = 0): Catalog
    {
        /** @var Catalog $block */
        $block = $this->_layout->getBlock('html_sitemap_catalog_category');

        if (!$this->variables->isEmpty($parentId)) {
            $block->setParentId($parentId);
        }

        $block->setLevel($level);

        return $block;
    }

    /**
     * @return \Magento\Catalog\Model\Category[]
     */
    public function getCategories(): array
    {
        $parentId = $this->getParentId();

        if ($this->variables->isEmpty($parentId)) {
            try {
                $store = $this->storeHelper->getStore();

                $parentId = $store->getRootCategoryId();
            } catch (NoSuchEntityException $exception) {
                $this->_logger->error($exception);

                return [];
            }
        }

        try {
            $categoryCollection = $this->categoryHelper->getCategoryCollection();

            $categoryCollection->addAttributeToSelect('*');
            $categoryCollection->addFieldToFilter('parent_id', $parentId);
            $categoryCollection->addOrder('position', 'ASC');

            return $categoryCollection->getItems();
        } catch (LocalizedException $exception) {
        }

        return [];
    }

    /**
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getProducts(\Magento\Catalog\Model\Category $category): array
    {
        $productCollection = $this->productHelper->getProductCollection();

        $productCollection->addAttributeToSelect('*');
        $productCollection->addCategoryFilter($category);

        return $productCollection->getItems();
    }
}
