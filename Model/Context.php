<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Model;

/**
 * Autologin model
 *
 * @api
 * @see   \Diepxuan\Autologin\Model\Context
 */
class Context
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

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

    public function __construct(
        \Magento\Framework\ObjectManagerInterface  $objectManager,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Request\Http        $request,
        \Psr\Log\LoggerInterface                   $logger
    ) {
        $this->_objectManager  = $objectManager;
        $this->_resourceConfig = $resourceConfig;
        $this->_request        = $request;
        $this->_logger         = $logger;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @return \Magento\Config\Model\ResourceModel\Config
     */
    public function getResourceConfig()
    {
        return $this->_resourceConfig;
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

}
