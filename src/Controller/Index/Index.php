<?php

declare(strict_types=1);

namespace Infrangible\HtmlSitemap\Controller\Index;

use Infrangible\Core\Helper\Stores;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Theme\Block\Html\Breadcrumbs;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Index
    extends Action
{
    /** @var Stores */
    protected $storeHelper;

    public function __construct(Context $context, Stores $storeHelper)
    {
        parent::__construct($context);

        $this->storeHelper = $storeHelper;
    }

    public function execute(): ResultInterface
    {
        $update = $this->_view->getLayout()->getUpdate();

        $update->addHandle('default');

        $this->_view->addActionLayoutHandles();

        $this->_view->loadLayoutUpdates();

        $this->_view->generateLayoutXml()->generateLayoutBlocks();

        $pageConfig = $this->_view->getPage()->getConfig();

        $pageConfig->getTitle()->set(__('Sitemap'));
        $pageConfig->setDescription(__('Sitemap Tree: Categories - Products'));

        /** @var Breadcrumbs $breadcrumbs */
        $breadcrumbs = $this->_view->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home', [
                          'label' => __('Home'),
                          'title' => __('Go to Home Page'),
                          'link'  => $this->storeHelper->getWebUrl()
                      ]
            );

            $breadcrumbs->addCrumb(
                'search', ['label' => __('Sitemap'), 'title' => __('Sitemap')]
            );
        }

        return $this->_view->getPage();
    }
}
