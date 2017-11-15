<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Plugin;

class Authentication
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
     * Authenticate user
     * @param  \Magento\Backend\Model\Auth $auth
     *
     * @return void
     */
    public function beforeIsLoggedIn(
        \Magento\Backend\Model\Auth $auth
    ) {
        if (!$this->getAuth()->isLoggedIn()) {
            return;
        }
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
