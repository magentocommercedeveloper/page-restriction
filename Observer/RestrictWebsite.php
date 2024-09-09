<?php

namespace Ssquare\Restrict\Observer;

use Magento\Customer\Model\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class RestrictWebsite implements ObserverInterface
{
    protected $_response;
    protected $_urlFactory;
    protected $_context;
    protected $_actionFlag;
    protected $_scopeConfig;

    /**
     * RestrictWebsite constructor.
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\App\ActionFlag $actionFlag,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_response = $response;
        $this->_urlFactory = $urlFactory;
        $this->_context = $context;
        $this->_actionFlag = $actionFlag;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $allowedRoutes = [];
        $request = $observer->getEvent()->getRequest();
        $isCustomerLoggedIn = $this->_context->getValue(Context::CONTEXT_AUTH);
        $actionFullName = strtolower($request->getFullActionName());

        // Fetch configuration values
        $restrictCategoryPage = $this->_scopeConfig->isSetFlag(
            'page_restrictions_section/page_restrictions_group/restrict_category_page',
            ScopeInterface::SCOPE_STORE
        );

        $restrictProductPage = $this->_scopeConfig->isSetFlag(
            'page_restrictions_section/page_restrictions_group/restrict_product_page',
            ScopeInterface::SCOPE_STORE
        );

        $restrictCheckoutPage = $this->_scopeConfig->isSetFlag(
            'page_restrictions_section/page_restrictions_group/restrict_checkout_page',
            ScopeInterface::SCOPE_STORE
        );

        $restrictCartPage = $this->_scopeConfig->isSetFlag(
            'page_restrictions_section/page_restrictions_group/restrict_cart_page',
            ScopeInterface::SCOPE_STORE
        );

        // Add routes based on configuration
        if ($restrictCategoryPage) {
            $allowedRoutes[] = 'catalog_category_view';
        }

        if ($restrictProductPage) {
            $allowedRoutes[] = 'catalog_product_view';
        }

        if ($restrictCheckoutPage) {
            $allowedRoutes[] = 'checkout_index_index';
        }

        if ($restrictCartPage) {
            $allowedRoutes[] = 'checkout_cart_index';
        }

        // Redirect to login if not logged in and attempting to access a restricted page
        if (!$isCustomerLoggedIn && in_array($actionFullName, $allowedRoutes)) {
            $this->_response->setRedirect($this->_urlFactory->create()->getUrl('customer/account/login'));
        }
    }
}
