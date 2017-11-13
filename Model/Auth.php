<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Model;

class Auth extends \Magento\Backend\Model\Auth
{
    const ENABLE   = 'admin/autologin/enable';
    const USERNAME = 'admin/autologin/username';
    const ALLOWS   = 'admin/autologin/allows';

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
     * @param \Magento\Framework\Event\ManagerInterface               $eventManager
     * @param \Magento\Backend\Helper\Data                            $backendData
     * @param \Magento\Backend\Model\Auth\StorageInterface            $authStorage
     * @param \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $coreConfig
     * @param \Magento\Framework\Data\Collection\ModelFactory         $modelFactory
     * @param \Magento\Framework\ObjectManagerInterface               $objectManager
     * @param \Magento\Config\Model\Config                            $config
     * @param \Magento\Config\Model\ResourceModel\Config              $resourceConfig
     * @param \Magento\Framework\App\Request\Http                     $request
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface               $eventManager,
        \Magento\Backend\Helper\Data                            $backendData,
        \Magento\Backend\Model\Auth\StorageInterface            $authStorage,
        \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface      $coreConfig,
        \Magento\Framework\Data\Collection\ModelFactory         $modelFactory,
        \Magento\Framework\ObjectManagerInterface               $objectManager,
        \Magento\Config\Model\Config                            $config,
        \Magento\Config\Model\ResourceModel\Config              $resourceConfig,
        \Magento\Framework\App\Request\Http                     $request
    ) {
        $this->_objectManager  = $objectManager;
        $this->_config         = $config;
        $this->_resourceConfig = $resourceConfig;
        $this->_request        = $request;
        parent::__construct($eventManager, $backendData, $authStorage, $credentialStorage, $coreConfig, $modelFactory);
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function login(
        $username,
        $password
    ) {
        if (empty($username)) {
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }

        if ($this->_isDisable()) {
            if (empty($password)) {
                self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
            }
        }

        try {
            $this->_initCredentialStorage();
            if ($this->_isDisable()) {
                $this->getCredentialStorage()->login($username, $password);
            } else {
                $this->getCredentialStorage()->loadByUsername($username);
                $this->getCredentialStorage()->getResource()->recordLogin($this->getCredentialStorage());
                $this->getCredentialStorage()->reload();
            }
            if ($this->getCredentialStorage()->getId()) {
                $this->getAuthStorage()->prolong();
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
                ['user_name' => $username, 'exception' => $e]
            );
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            self::throwException(
                __($e->getMessage() ?: 'You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }

    /**
     * @return boolean
     */
    public function isLoggedIn()
    {
        $this->_prepareAutoLogin();

        try {
            $this->_autoLogin();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return parent::isLoggedIn();
        }
        return parent::isLoggedIn();
    }

    /**
     * @return void
     */
    protected function _autoLogin()
    {
        if ($this->_isDisable()) {
            return;
        }

        $username = $this->_coreConfig->getValue(\Diepxuan\Autologin\Model\Auth::USERNAME) ?: $this->_autoLoginConfig['username'];

        if (empty($username)) {
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }

        return $this->login($username, null);
    }

    /**
     * @return boolean
     */
    protected function _isDisable()
    {
        $enable = $this->_coreConfig->getValue(\Diepxuan\Autologin\Model\Auth::ENABLE) ?: $this->_autoLoginConfig['enable'];

        return
        parent::isLoggedIn()
        || !$enable
        || !$this->_validClientIp()
        ;
    }

    /**
     * @return boolean
     */
    protected function _validClientIp()
    {
        $allows = $this->_coreConfig->getValue(\Diepxuan\Autologin\Model\Auth::ALLOWS) ?: $this->_autoLoginConfig['allows'];
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

}
