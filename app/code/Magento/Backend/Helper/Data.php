<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Helper;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends \Magento\Core\Helper\AbstractHelper
{
    const XML_PATH_USE_CUSTOM_ADMIN_URL         = 'admin/url/use_custom';
    const XML_PATH_USE_CUSTOM_ADMIN_PATH        = 'admin/url/use_custom_path';
    const XML_PATH_CUSTOM_ADMIN_PATH            = 'admin/url/custom_path';
    const XML_PATH_BACKEND_AREA_FRONTNAME       = 'default/backend/frontName';
    const BACKEND_AREA_CODE                     = 'adminhtml';

    const PARAM_BACKEND_FRONT_NAME              = 'backend.frontName';

    protected $_pageHelpUrl;

    /**
     * @var \Magento\Core\Model\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Core\Model\Config\Primary
     */
    protected $_primaryConfig;

    /**
     * @var string
     */
    protected $_defaultAreaFrontName;

    /**
     * Area front name
     * @var string
     */
    protected $_areaFrontName = null;

    /**
     * @var \Magento\Core\Model\RouterList
     */
    protected $_routerList;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Core\Model\AppProxy
     */
    protected $_app;

    /**
     * @var \Magento\Backend\Model\UrlProxy
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\Model\AuthProxy
     */
    protected $_auth;

    /**
     * Backend area front name
     *
     * @var string
     */
    protected $_backendFrontName;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\ConfigInterface $applicationConfig
     * @param \Magento\Core\Model\Config\Primary $primaryConfig
     * @param \Magento\Core\Model\RouterList $routerList
     * @param \Magento\Core\Model\AppProxy $app
     * @param \Magento\Backend\Model\UrlProxy $backendUrl
     * @param \Magento\Backend\Model\AuthProxy $auth
     * @param string $defaultAreaFrontName
     * @param string $backendFrontName
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\ConfigInterface $applicationConfig,
        \Magento\Core\Model\Config\Primary $primaryConfig,
        \Magento\Core\Model\RouterList $routerList,
        \Magento\Core\Model\AppProxy $app,
        \Magento\Backend\Model\UrlProxy $backendUrl,
        \Magento\Backend\Model\AuthProxy $auth,
        $defaultAreaFrontName,
        $backendFrontName
    ) {
        parent::__construct($context);
        $this->_coreData = $coreData;
        $this->_config = $applicationConfig;
        $this->_primaryConfig = $primaryConfig;
        $this->_defaultAreaFrontName = $defaultAreaFrontName;
        $this->_routerList = $routerList;
        $this->_app = $app;
        $this->_backendUrl = $backendUrl;
        $this->_auth = $auth;
        $this->_backendFrontName = $backendFrontName;
    }

    public function getPageHelpUrl()
    {
        if (!$this->_pageHelpUrl) {
            $this->setPageHelpUrl();
        }
        return $this->_pageHelpUrl;
    }

    public function setPageHelpUrl($url = null)
    {
        if (is_null($url)) {
            $request = $this->_app->getRequest();
            $frontModule = $request->getControllerModule();
            if (!$frontModule) {
                $frontName = $request->getModuleName();
                $router = $this->_routerList->getRouterByFrontName($frontName);

                $frontModule = $router->getModulesByFrontName($frontName);
                if (empty($frontModule) === false) {
                    $frontModule = $frontModule[0];
                } else {
                    $frontModule = null;
                }
            }
            $url = 'http://www.magentocommerce.com/gethelp/';
            $url.= $this->_app->getLocale()->getLocaleCode().'/';
            $url.= $frontModule.'/';
            $url.= $request->getControllerName().'/';
            $url.= $request->getActionName().'/';

            $this->_pageHelpUrl = $url;
        }
        $this->_pageHelpUrl = $url;

        return $this;
    }

    public function addPageHelpUrl($suffix)
    {
        $this->_pageHelpUrl = $this->getPageHelpUrl().$suffix;
        return $this;
    }

    public function getUrl($route = '', $params = array())
    {
        return $this->_backendUrl->getUrl($route, $params);
    }

    public function getCurrentUserId()
    {
        if ($this->_auth->getUser()) {
            return $this->_auth->getUser()->getId();
        }
        return false;
    }

    /**
     * Decode filter string
     *
     * @param string $filterString
     * @return array
     */
    public function prepareFilterString($filterString)
    {
        $data = array();
        $filterString = base64_decode($filterString);
        parse_str($filterString, $data);
        array_walk_recursive($data, array($this, 'decodeFilter'));
        return $data;
    }

    /**
     * Decode URL encoded filter value recursive callback method
     *
     * @param string $value
     */
    public function decodeFilter(&$value)
    {
        $value = rawurldecode($value);
    }

    /**
     * Generate unique token for reset password confirmation link
     *
     * @return string
     */
    public function generateResetPasswordLinkToken()
    {
        return $this->_coreData->uniqHash();
    }

    /**
     * Get backend start page URL
     *
     * @return string
     */
    public function getHomePageUrl()
    {
        return $this->_backendUrl->getRouteUrl('adminhtml');
    }

    /**
     * Return Backend area code
     *
     * @return string
     */
    public function getAreaCode()
    {
        return self::BACKEND_AREA_CODE;
    }

    /**
     * Return Backend area front name
     *
     * @return string
     */
    public function getAreaFrontName()
    {
        if (null === $this->_areaFrontName) {
            $isCustomPathUsed = (bool)(string)$this->_config->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_PATH, 'default');

            if ($isCustomPathUsed) {
                $this->_areaFrontName = (string)$this->_config->getValue(self::XML_PATH_CUSTOM_ADMIN_PATH, 'default');
            } elseif ($this->_backendFrontName) {
                $this->_areaFrontName = $this->_backendFrontName;
            } else {
                $this->_areaFrontName = $this->_defaultAreaFrontName;
            }
        }
        return $this->_areaFrontName;
    }

    /**
     * Invalidate cache of area front name
     *
     * @return \Magento\Backend\Helper\Data
     */
    public function clearAreaFrontName()
    {
        $this->_areaFrontName = null;
        return $this;
    }
}
