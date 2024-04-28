<?php

/**
 * Copyright © 2024 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Plugin\User\Model;

class User
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Diepxuan\Autologin\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @param \Psr\Log\LoggerInterface            $logger
     * @param \Diepxuan\Autologin\Model\Auth      $auth
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Psr\Log\LoggerInterface            $logger,
        \Diepxuan\Autologin\Model\Auth      $auth,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_logger  = $logger;
        $this->_auth    = $auth;
        $this->_request = $request;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @param bool $result
     * @return bool
     */
    public function afterVerifyIdentity(
        \Magento\User\Model\User $user,
        bool $result
    ): bool {
        if (!$result) {
            try {
                return $this->getAuth()->verifyIdentity($user, $result);
            } catch (\Exception $exception) {
                return $result;
            }
        }

        return $result;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Diepxuan\Autologin\Model\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
