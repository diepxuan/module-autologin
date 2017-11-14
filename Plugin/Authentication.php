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
     * @param \Psr\Log\LoggerInterface       $logger
     * @param \Diepxuan\Autologin\Model\Auth $auth
     */
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
     * @param \Magento\Framework\App\ActionInterface $proceed
     * @return void
     */
    public function beforeDispatch(\Magento\Framework\App\ActionInterface $proceed)
    {
        if (!$this->getAuth()->isLoggedIn()) {
            $this->getLogger()->info('Autologin/Authentication::failed');
            return;
        }
        $this->getLogger()->info('Autologin/Authentication::successful');
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Backend\Model\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

}
