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
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

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

    /**
     * @param \Magento\Framework\ObjectManagerInterface               $objectManager
     * @param \Magento\Config\Model\ResourceModel\Config              $resourceConfig
     * @param \Magento\Framework\App\Request\Http                     $request
     * @param \Psr\Log\LoggerInterface                                $logger
     * @param \Magento\Framework\Event\ManagerInterface               $eventManager
     * @param \Magento\Backend\Helper\Data                            $backendData
     * @param \Magento\Backend\Model\Auth                             $auth
     * @param \Magento\Backend\Model\Auth\StorageInterface            $authStorage
     * @param \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $coreConfig
     * @param \Magento\Framework\Data\Collection\ModelFactory         $modelFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface               $objectManager,
        \Magento\Config\Model\ResourceModel\Config              $resourceConfig,
        \Magento\Framework\App\Request\Http                     $request,
        \Psr\Log\LoggerInterface                                $logger,
        \Magento\Framework\Event\ManagerInterface               $eventManager,
        \Magento\Backend\Helper\Data                            $backendData,
        \Magento\Backend\Model\Auth                             $auth,
        \Magento\Backend\Model\Auth\StorageInterface            $authStorage,
        \Magento\Backend\Model\Auth\Credential\StorageInterface $credentialStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface      $coreConfig,
        \Magento\Framework\Data\Collection\ModelFactory         $modelFactory
    ) {
        $this->_objectManager     = $objectManager;
        $this->_resourceConfig    = $resourceConfig;
        $this->_request           = $request;
        $this->_logger            = $logger;
        $this->_eventManager      = $eventManager;
        $this->_backendData       = $backendData;
        $this->_auth              = $auth;
        $this->_authStorage       = $authStorage;
        $this->_credentialStorage = $credentialStorage;
        $this->_coreConfig        = $coreConfig;
        $this->_modelFactory      = $modelFactory;
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

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\Backend\Helper\Data
     */
    public function getBackendData()
    {
        return $this->_backendData;
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
     * Return auth.
     *
     * @return \Magento\Backend\Model\Auth
     * @codeCoverageIgnore
     */
    public function getAuth()
    {
        return $this->_auth;
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
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getCoreConfig()
    {
        return $this->_coreConfig;
    }

    /**
     * @return \Magento\Framework\Data\Collection\ModelFactory
     */
    public function getModelFactory()
    {
        return $this->_modelFactory;
    }

}
