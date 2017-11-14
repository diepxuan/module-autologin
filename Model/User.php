<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Model;

use Magento\Framework\Exception\AuthenticationException;

/**
 * Admin user model
 *
 * @api
 * @method string getLogdate()
 * @method \Magento\User\Model\User setLogdate(string $value)
 * @method int getLognum()
 * @method \Magento\User\Model\User setLognum(int $value)
 * @method int getReloadAclFlag()
 * @method \Magento\User\Model\User setReloadAclFlag(int $value)
 * @method string getExtra()
 * @method \Magento\User\Model\User setExtra(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @api
 * @since 100.0.2
 * @see   \Magento\User\Model\User
 */
class User extends \Magento\User\Model\User
{

    /**
     * Authenticate user name and password and save loaded record
     *
     * @param string $username
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see \Magento\User\Model\User::authenticate($username, $password)
     */
    public function autoAuthenticate($username)
    {
        $config = $this->_config->isSetFlag('admin/security/use_case_sensitive_login');
        $result = false;

        try {
            $this->_eventManager->dispatch(
                'admin_user_authenticate_before',
                ['username' => $username, 'user' => $this]
            );
            $this->loadByUsername($username);
            $sensitive = $config ? $username == $this->getUsername() : true;
            if ($sensitive && $this->getId()) {
                $result = $this->autoVerifyIdentity();
            }

            $this->_eventManager->dispatch(
                'admin_user_authenticate_after',
                ['username' => $username, 'password' => $this->getPassword(), 'user' => $this, 'result' => $result]
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
     *
     * @see \Magento\User\Model\User::verifyIdentity($username, $password)
     */
    public function autoVerifyIdentity()
    {
        if ($this->getIsActive() != '1') {
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }
        if (!$this->hasAssigned2Role($this->getId())) {
            throw new AuthenticationException(__('You need more permissions to access this.'));
        }
        return true;
    }

    /**
     * Login user
     *
     * @param   string $username
     * @return  $this
     *
     * @see \Magento\User\Model\User::login($username, $password)
     */
    public function autoLogin($username)
    {
        if ($this->autoAuthenticate($username)) {
            $this->getResource()->recordLogin($this);
        }
        return $this;
    }
}
