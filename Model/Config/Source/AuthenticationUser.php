<?php
/**
 * Copyright © 2017 Dxvn, Inc. All rights reserved.
 * @author  Tran Ngoc Duc <caothu91@gmail.com>
 */

namespace Diepxuan\Autologin\Model\Config\Source;

class AuthenticationUser implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    private $_userCollection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var array
     */
    private $users;

    public function __construct(
        \Magento\User\Model\ResourceModel\User\Collection $userCollection,
        \Psr\Log\LoggerInterface                          $logger
    ) {
        $this->_userCollection = $userCollection;
        $this->_logger         = $logger;
        $this->users           = [];
    }

    /**
     * @return array
     */
    public function toArray($includeEmptyChoice = true)
    {
        if (!is_null($this->users) && !empty($this->users)) {
            return $this->users;
        }

        if ($includeEmptyChoice) {
            $this->users[''] = __('-- Default --');
        }

        $this->getCollection()->addFieldToFilter('is_active', true);
        $this->getCollection()->addOrder('username', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($this->getCollection() as $user) {
            $this->users[$user->getUsername()] = $user->getName() . ' (' . $user->getUsername() . ')';
        }

        return $this->users;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $users = [];
        foreach ($this->toArray() as $userName => $name) {
            $users[] = [
                'value' => $userName,
                'label' => $name,
            ];
        }
        return $users;
    }

    /**
     * @return \Magento\User\Model\ResourceModel\User\Collection
     */
    public function getCollection()
    {
        return $this->_userCollection;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }
}
