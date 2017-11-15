<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Phrase;

/**
 * Backend Auth model
 *
 * @see   \Magento\Backend\Model\Auth
 */
class Auth
{
    const ENABLE   = 'admin/autologin/enable';
    const USERNAME = 'admin/autologin/username';
    const ALLOWS   = 'admin/autologin/allows';

    /**
     * @var array
     */
    protected $_autoLoginConfig = array(
        'config'   => array(
            'admin/security/admin_account_sharing'    => 1,
            'admin/security/use_case_sensitive_login' => 1,
            'admin/security/use_form_key'             => 0,
            'admin/captcha/enable'                    => 0,

            'customer/startup/redirect_dashboard'     => 0,

            'web/seo/use_rewrites'                    => 1,
            'web/session/use_frontend_sid'            => 0,
            'web/url/redirect_to_base'                => 1,
        ),
        'enable'   => 1,
        'username' => 'admin',
        'allows'   => array(
            '127.0.0.1',
        ),
    );

    /**
     * @var \Diepxuan\Autologin\Model\Context
     */
    protected $_context;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @var \Magento\Backend\Model\Auth\StorageInterface
     */
    protected $_authStorage;

    /**
     * @var \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    protected $_credentialStorage;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Magento\Framework\Data\Collection\ModelFactory
     */
    protected $_modelFactory;

    public function __construct(
        \Diepxuan\Autologin\Model\Context $context
    ) {
        $this->_context      = $context;
        $this->_logger       = $context->getLogger();
        $this->_request      = $context->getRequest();
        $this->_eventManager = $context->getEventManager();
        $this->_backendData  = $context->getBackendData();
        $this->_authStorage  = $context->getAuthStorage();
        $this->_coreConfig   = $context->getCoreConfig();
        $this->_modelFactory = $context->getModelFactory();
    }

    /**
     * Check if current user is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        try {
            $this->_autoAuthentication();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getAuthStorage()->isLoggedIn();
        }

        return $this->getAuthStorage()->isLoggedIn();
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function autoAuthentication()
    {
        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->autoLogin($this->getAdminUserName());
            if ($this->getCredentialStorage()->getId()) {
                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->getAuthStorage()->processLogin();

                $this->_eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $this->getCredentialStorage()]
                );
            }
            if (!$this->getAuthStorage()->getUser()) {
                self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
            }
        } catch (PluginAuthenticationException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $this->getAdminUserName(), 'exception' => $e]
            );
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $this->getAdminUserName(), 'exception' => $e]
            );
            self::throwException(
                __($e->getMessage() ?: 'You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }

    /**
     * @return boolean
     */
    public function isEnable()
    {
        return $this->_isEnable();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getAdminUserName()
    {
        $username = $this->_coreConfig->getValue(self::USERNAME) ?: $this->_autoLoginConfig['username'];

        if (empty($username)) {
            self::throwException(__('Autologin/Authentication | Your admin username was not found!'));
        }

        return $username;
    }

    /**
     * Perform logout process
     *
     * @return void
     */
    public function logout()
    {
        $this->getAuthStorage()->processLogout();
    }

    /**
     * Throws specific Backend Authentication \Exception
     *
     * @param \Magento\Framework\Phrase $msg
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @static
     */
    public static function throwException(Phrase $msg = null)
    {
        if ($msg === null) {
            $msg = __('Authentication error occurred.');
        }
        throw new AuthenticationException($msg);
    }

    /**
     * Return auth storage.
     * If auth storage was not defined outside - returns default object of auth storage
     *
     * @return \Magento\Backend\Model\Auth\StorageInterface
     * @codeCoverageIgnore
     */
    public function getAuthStorage()
    {
        return $this->_authStorage;
    }

    /**
     * Return current (successfully authenticated) user,
     * an instance of \Magento\Backend\Model\Auth\Credential\StorageInterface
     *
     * @return \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    public function getUser()
    {
        return $this->getAuthStorage()->getUser();
    }

    /**
     * Initialize credential storage from configuration
     *
     * @return void
     */
    protected function _initCredentialStorage()
    {
        $this->_credentialStorage = $this->_modelFactory->create(
            \Diepxuan\Autologin\Model\User::class
        );
    }

    /**
     * Return credential storage object
     *
     * @return null|\Magento\Backend\Model\Auth\Credential\StorageInterface
     * @codeCoverageIgnore
     */
    public function getCredentialStorage()
    {
        return $this->_credentialStorage;
    }

    /**
     * @return void
     */
    protected function _autoAuthentication()
    {
        if (!$this->isEnable()) {
            return;
        }

        return $this->autoAuthentication();
    }

    /**
     * @return boolean
     */
    protected function _isEnable()
    {
        if ($this->getAuthStorage()->isLoggedIn()) {
            return false;
        }

        $enable = $this->_coreConfig->getValue(self::ENABLE) || $this->_autoLoginConfig['enable'];
        if (!$enable) {
            return false;
        }

        if (!$this->_verifyClientIp()) {
            self::throwException(__('Autologin/Authentication | You IP address access denied!'));
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function _verifyClientIp()
    {
        $allows = $this->_coreConfig->getValue(\Diepxuan\Autologin\Model\Auth::ALLOWS) ?: $this->_autoLoginConfig['allows'];
        if (is_string($allows)) {
            $allows = explode(PHP_EOL, $allows);
        }
        $allows = array_unique($allows);
        $allows = array_filter($allows);
        $allows = array_values($allows);
        $allows = array_map('trim', $allows);
        return in_array($this->_context->getRequest()->getClientIp(), $allows);
    }

    /**
     * @return void
     */
    protected function _prepareAutoLogin()
    {
        foreach ($this->_autoLoginConfig['config'] as $key => $value) {
            if ($this->_coreConfig->getValue($key) != $value) {
                $this->_context->getResourceConfig()->saveConfig($key, $value, 'default', 0);
            }
        }
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

}
