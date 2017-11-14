<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Plugin;

class Authentication
{
    const ENABLE   = 'admin/autologin/enable';
    const USERNAME = 'admin/autologin/username';
    const ALLOWS   = 'admin/autologin/allows';
    const SECURITY = 'admin/security/use_case_sensitive_login';

    /**
     * Controller actions that must be reachable without authentication
     *
     * @var array
     */
    const CONTROLLER_ACTIONS_OPEN = [
        'logout',
        'refresh', // captcha refresh
        'resetpassword',
        'forgotpassword',
        'resetpasswordpost',
    ];

    /**
     * @var array
     */
    protected $_autoLoginConfig = array(
        'config'   => array(
            'admin/security/admin_account_sharing' => 1,
            'admin/security/use_form_key'          => 0,

            'customer/startup/redirect_dashboard'  => 0,

            'web/seo/use_rewrites'                 => 1,
            'web/session/use_frontend_sid'         => 0,
            'web/url/redirect_to_base'             => 1,
        ),
        'enable'   => 0,
        'username' => 'admin',
        'allows'   => array(
            '127.0.0.1',
        ),
    );
    /**
     * @var \Magento\Backend\Model\Auth\StorageInterface
     */
    protected $_authStorage;

    /**
     * @var \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    protected $_credentialStorage;

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Magento\Framework\Data\Collection\ModelFactory
     */
    protected $_modelFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Config\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * @var \Diepxuan\Autologin\Model\Config\Source\AuthenticationUser
     */
    protected $_authenticationUser;

    /**
     * @var \Magento\Backend\App\BackendAppList
     */
    protected $_backendAppList;

    public function __construct(
        \Psr\Log\LoggerInterface       $logger,
        \Diepxuan\Autologin\Model\Auth $auth
    ) {
        $this->_logger = $logger;
        $this->_auth   = $auth;
    }

    /**
     * Authenticate user
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @return void
     */
    public function beforeDispatch(\Magento\Framework\App\ActionInterface $proceed)
    {
        // $loginUrl = $this->customerUrl->getLoginUrl();

        // if (!$this->customerSession->authenticate($loginUrl)) {
        //     $subject->getActionFlag()->set('', $subject::FLAG_NO_DISPATCH, true);
        // }
        // }

//     public function beforeDispatch(
        //         \Magento\Framework\Interception\InterceptorInterface $proceed,
        //         \Magento\Framework\App\RequestInterface              $request
        //     ) {
        if ($this->getAuth()->isLoggedIn()) {
            // $proceed->getActionFlag()->set('', $proceed::FLAG_NO_DISPATCH, false);
            $this->getLogger()->info('Autologin/Authentication::isLoggedIn');
            return;
        }
        // $proceed->getActionFlag()->set('', $proceed::FLAG_NO_DISPATCH, true);

        // $this->_request = $request;

        // if (!$this->isEnable()) {
        //     // $this->getAuthStorage()->refreshAcl();
        //     return;
        //     // return $proceed($this->_request);
        // }

        // if (!$this->validRequestedActionName($this->_request->getActionName())) {
        //     // $this->getAuthStorage()->refreshAcl();
        //     return;
        //     // $this->_request->setDispatched(true);
        //     // return $proceed($this->_request);
        // }

        // // if ($this->getAuthStorage()->getUser()) {
        // //     $this->getAuthStorage()->refreshAcl();
        // // }

        // if ($this->getAuthStorage()->isLoggedIn()) {
        //     // $this->getAuthStorage()->prolong();
        //     // $this->getLogger()->info('Autologin/Authentication::before');
        //     // $baseUrl = $this->_backendUrl->getUrl('adminhtml/dashboard');
        //     // $baseUrl = \Magento\Framework\App\Request\Http::getUrlNoScript($baseUrl);
        //     // return $this->_resultRedirectFactory->create()->setUrl($baseUrl);
        //     return;
        // }

        // $this->getLogger()->info('Autologin/Authentication::failed');

        // $this->autoLogin();

        // if ($this->getAuthStorage()->isLoggedIn()) {
        //     // $this->getLogger()->info('Autologin/Authentication::after');
        //     // $this->getLogger()->info('Autologin/Authentication::after');
        //     // die('hehe');
        //     // $baseUrl = $this->_backendUrl->getUrl('adminhtml/dashboard');
        //     // return $this->_resultRedirectFactory->create()->setUrl($baseUrl);
        // }

        // // $this->getLogger()->info('Autologin/Authentication::failed');
        // // die('hehe');

        // $this->getLogger()->info($request);

        // return $proceed($this->_request);

    }

    /**
     * @return boolean
     */
    public function isEnable()
    {
        return $this->_isEnable();
    }

    /**
     * @param  string $requestedActionName
     * @return boolean
     */
    public function validRequestedActionName($requestedActionName)
    {
        return $this->_validRequestedActionName($requestedActionName);
    }

    /**
     * Perform login process
     *
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function autoLogin()
    {
        try {
            /**
             * Init credentialStorage
             */
            $this->_initCredentialStorage();

            /**
             * Login user
             *
             * @see \Magento\User\Model\User::login($username, $password)
             */
            $this->_autoLogin();

            if ($this->getCredentialStorage()->getId()) {
                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->getAuthStorage()->processLogin();

                $this->getLogger()->info('Autologin/Authentication::backend_auth_user_login_success');

                $this->getEventManager()->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $this->getCredentialStorage()]
                );
            }

            if (!$this->getAuthStorage()->getUser()) {
                $this->getLogger()->info('Autologin/Authentication::failed');
                self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
            }
        } catch (\Magento\Framework\Exception\Plugin\AuthenticationException $e) {
            $this->getEventManager()->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $this->getLoginUsername(), 'exception' => $e]
            );
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getEventManager()->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $this->getLoginUsername(), 'exception' => $e]
            );
            self::throwException(
                __($e->getMessage() ?: 'You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }

    /**
     * Authenticate user name and password and save loaded record
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see \Magento\User\Model\User::authenticate($username, $password)
     */
    public function authenticate()
    {
        $sensitive = $this->_coreConfig->isSetFlag('admin/security/use_case_sensitive_login');
        $result    = false;
        try {
            $this->getEventManager()->dispatch(
                'admin_user_authenticate_before',
                [
                    'username' => $this->getLoginUsername(),
                    'user'     => $this->getCredentialStorage(),
                ]
            );
            $this->getCredentialStorage()->loadByUsername($this->getLoginUsername());

            $sensitive = $this->_coreConfig->getValue(self::SECURITY);
            $sensitive = $sensitive ? $this->getLoginUsername() == $this->getCredentialStorage()->getUsername() : true;
            if ($sensitive && $this->getCredentialStorage()->getId()) {
                $result = $this->verifyIdentity();
            }

            $this->getEventManager()->dispatch(
                'admin_user_authenticate_after',
                [
                    'username' => $this->getCredentialStorage()->getUsername(),
                    'password' => $this->getCredentialStorage()->getPassword(),
                    'user'     => $this->getCredentialStorage(),
                    'result'   => $result,
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) {
            $this->unsetData();
        }
        return $result;
    }

    /**
     * Ensure that provided password matches the current user password. Check if the current user account is active.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function verifyIdentity()
    {
        $result = true;
        if ($this->getCredentialStorage()->getIsActive() != '1') {
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }
        if (!$this->getCredentialStorage()->hasAssigned2Role($this->getCredentialStorage()->getId())) {
            throw new AuthenticationException(__('You need more permissions to access this.' . sprintf('(%)', $this->getCredentialStorage()->getUsername())));
        }
        return $result;
    }

    /**
     * Login user
     *
     * @return  \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    protected function _autoLogin()
    {

        $this->_prepareAutoLogin();

        /**
         * authenticate user
         *
         * @see \Magento\User\Model\User::authenticate($username, $password)
         */
        if ($this->authenticate()) {
            $this->getCredentialStorage()->getResource()->recordLogin($this->getCredentialStorage());
            $this->getCredentialStorage()->reload();
        }
        return $this->getCredentialStorage();
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getLoginUsername()
    {
        $username = $this->_coreConfig->getValue(self::USERNAME) ?: $this->_autoLoginConfig['username'];

        if (empty($username)) {
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }

        return $username;
    }

    /**
     * @return boolean
     */
    protected function _isEnable()
    {
        $enable = $this->_coreConfig->getValue(self::ENABLE) ?: $this->_autoLoginConfig['enable'];

        return !$this->getAuthStorage()->isLoggedIn()
        && $enable
        && $this->_validClientIp()
        ;
    }

    /**
     * @return boolean
     */
    protected function _validClientIp()
    {
        $allows = $this->_coreConfig->getValue(self::ALLOWS) ?: $this->_autoLoginConfig['allows'];
        if (is_string($allows)) {
            $allows = explode(PHP_EOL, $allows);
        }
        $allows = array_unique($allows);
        $allows = array_filter($allows);
        $allows = array_values($allows);
        $allows = array_map('trim', $allows);
        return $this->_checkClientIp($allows);
    }

    /**
     * @return void
     */
    protected function _prepareAutoLogin()
    {
        foreach ($this->_autoLoginConfig['config'] as $key => $value) {
            if ($this->_coreConfig->getValue($key) != $value) {
                $this->_resourceConfig->saveConfig($key, $value, 'default', 0);
            }
        }
    }

    /**
     * @param  array $allows
     * @return boolean
     */
    protected function _checkClientIp($allows)
    {
        return in_array($this->_getClientIp(), $allows);
    }

    /**
     * @return string
     */
    protected function _getClientIp()
    {
        return $this->_request->getClientIp();
    }

    /**
     * @param  string $requestedActionName
     * @return boolean
     */
    protected function _validRequestedActionName($requestedActionName)
    {
        return !in_array($requestedActionName, self::CONTROLLER_ACTIONS_OPEN);
    }

    /**
     * Initialize credential storage from configuration
     *
     * @return void
     */
    protected function _initCredentialStorage()
    {
        $this->_credentialStorage = $this->_modelFactory->create(
            \Magento\Backend\Model\Auth\Credential\StorageInterface::class
        );
    }

    /**
     * Throws specific Backend Authentication \Exception
     *
     * @param \Magento\Framework\Phrase $msg
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @static
     */
    public static function throwException(\Magento\Framework\Phrase $msg = null)
    {
        if ($msg === null) {
            $msg = __('Authentication error occurred.');
        }
        throw new \Magento\Framework\Exception\AuthenticationException($msg);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Config\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @return \Magento\Backend\Model\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    public function getCredentialStorage()
    {
        return $this->_credentialStorage;
    }

    /**
     * @return \Magento\Backend\Model\Auth\StorageInterface
     */
    public function getAuthStorage()
    {
        return $this->_authStorage;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\Backend\App\BackendAppList
     */
    public function getBackendAppList()
    {
        return $this->_backendAppList;
    }

}
