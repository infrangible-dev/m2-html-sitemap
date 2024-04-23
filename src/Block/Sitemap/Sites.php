<?php

declare(strict_types=1);

namespace Infrangible\HtmlSitemap\Block\Sitemap;

use Infrangible\Core\Helper\Cms;
use Infrangible\Core\Helper\Stores;
use Magento\Cms\Model\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Sites
    extends Template
{
    /** @var Cms */
    protected $cmsHelper;

    /** @var \Magento\Cms\Helper\Page */
    protected $cmsPageHelper;

    /** @var Stores */
    protected $storeHelper;

    public function __construct(
        Template\Context $context,
        Cms $cmsHelper,
        \Magento\Cms\Helper\Page $cmsPageHelper,
        Stores $storeHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->cmsHelper = $cmsHelper;
        $this->cmsPageHelper = $cmsPageHelper;
        $this->storeHelper = $storeHelper;
    }

    /**
     * @return Page[]
     */
    public function getPages(): array
    {
        $cmsPageCollection = $this->cmsHelper->getCmsPageCollection();

        $cmsPageCollection->addFieldToSelect('*');
        $cmsPageCollection->addFieldToFilter('is_active', 1);

        try {
            $store = $this->storeHelper->getStore();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->error($exception);

            return [];
        }

        $pages = [];

        /** @var Page $page */
        foreach ($cmsPageCollection->getItems() as $page) {
            $identifier = $page->getIdentifier();

            if (!in_array($identifier, ['no-route', 'home', 'enable-cookies'])
                && $page->checkIdentifier(
                    $identifier,
                    $store->getId()
                )) {
                $pages[] = $page;
            }
        }

        return $pages;
    }

    public function getPageUrl(Page $page): string
    {
        return $this->cmsPageHelper->getPageUrl($page->getId());
    }
}
