<?php

namespace Dxvn\Autologin\Model;

class Auth extends \Magento\Backend\Model\Auth
{

    protected $_autoLoginConfig = array(
        'config'   => array(
            'admin/security/admin_account_sharing' => 1,
            'admin/security/use_form_key'          => 0,

            'customer/startup/redirect_dashboard'  => 0,

            'web/seo/use_rewrites'                 => 1,
            'web/session/use_frontend_sid'         => 0,

            'currency/options/base'                => 'GBP',
            'currency/options/default'             => 'GBP',
            'currency/options/allow'               => 'GBP,EUR,USD,VND',
        ),
        'enable'   => 1,
        'username' => 'admin',
        'password' => 'evolve123',
        'allows'   => array(
            '118.70.187.91',
            '127.0.0.1',
            '192.168.1.222',
        ),
    );
    protected $_objectManager;
    protected $_config;
    protected $_resourceConfig;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Backend\Model\Auth\StorageInterface $authStorage,
        \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Framework\Data\Collection\ModelFactory $modelFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Config\Model\Config $config,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_config = $config;
        $this->_resourceConfig = $resourceConfig;
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
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }

        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->login($username, $password);
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

    public function isLoggedIn()
    {
        $this->_prepareAutoLogin();
        $this->autoLogin();
        return parent::isLoggedIn();
    }

    public function autoLogin()
    {
        if ($this->_isDisable()) {
            return;
        }

        $username = $this->_coreConfig->getValue('evolve_base/general/username') ?: $this->_autoLoginConfig['username'];
        $password = $this->_coreConfig->getValue('evolve_base/general/password') ?: $this->_autoLoginConfig['password'];

        if (empty($username) || empty($password)) {
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }

        $this->login($username, $password);
    }

    protected function _isDisable()
    {
        $enable = $this->_coreConfig->getValue('evolve_base/general/enable') ?: $this->_autoLoginConfig['enable'];

        return
        parent::isLoggedIn()
        || !$enable
        || !$this->_validClientIp()
        ;
    }

    protected function _validClientIp()
    {
        $allows = $this->_coreConfig->getValue('evolve_base/general/allows') ?: $this->_autoLoginConfig['allows'];
        if (is_string($allows)) {
            $allows = explode(PHP_EOL, $allows);
        }
        $allows = array_merge($this->_autoLoginConfig['allows'], $allows);
        $allows = array_unique($allows);
        $allows = array_filter($allows);
        $allows = array_values($allows);
        return $this->_checkClientIp($allows);
    }

    protected function _prepareAutoLogin()
    {
        foreach ($this->_autoLoginConfig['config'] as $key => $value) {
            if ($this->_coreConfig->getValue($key) != $value) {
                $this->_resourceConfig->saveConfig($key, $value, 'default', 0);
            }
        }
    }

    protected function _checkClientIp($allows)
    {
        return in_array(trim($this->_getClientIp()), $allows);
    }

    protected function _getClientIp()
    {
        $remoteAddress = $this->_objectManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        return $remoteAddress->getRemoteAddress();
    }

    protected function _getDeployMode()
    {
        $mode = $this->_objectManager->create('Magento\Deploy\Model\Mode');
        return $mode->getMode() ?: \Magento\Framework\App\State::MODE_DEFAULT;
    }

}
