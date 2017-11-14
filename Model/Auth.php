<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Model;

/**
 * Backend Auth model
 *
 * @api
 * @since 100.0.2
 * @see   \Magento\Backend\Model\Auth
 */
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

    public function __construct(
        \Magento\Framework\Event\ManagerInterface               $eventManager,
        \Magento\Backend\Helper\Data                            $backendData,
        \Magento\Backend\Model\Auth\StorageInterface            $authStorage,
        \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface      $coreConfig,
        \Magento\Framework\Data\Collection\ModelFactory         $modelFactory,
        \Diepxuan\Autologin\Model\Context                       $context
    ) {
        $this->_context = $context;
        $this->_logger  = $context->getLogger();

        return parent::__construct($eventManager, $backendData, $authStorage, $credentialStorage, $coreConfig, $modelFactory);
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
            return parent::isLoggedIn();
        }

        return parent::isLoggedIn();
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
            $this->getLogger()->info('Autologin/Authentication:: ' . get_class($this->getCredentialStorage()));
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
        if (parent::isLoggedIn()) {
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
