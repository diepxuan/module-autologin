Autologin module for Magento 2
==================
[![Latest version](https://img.shields.io/badge/latest-1.0.2-green.svg)](https://packagist.org/packages/diepxuan/module-autologin)
[![Packagist]((https://img.shields.io/badge/autologin-green.svg))](https://packagist.org/packages/diepxuan/module-autologin)
[![Magento 2](https://img.shields.io/badge/Magento-%3E=2.1-blue.svg)](https://github.com/magento/magento2/tree/2.1)
[![PHP >= 5.5.22](https://img.shields.io/badge/PHP-%3E=5.6.5-blue.svg)](https://packagist.org/packages/diepxuan/module-autologin)

- **Auto-login:** Provides automatic logins for administrators..


Autologin
--------------

This extension replaces the default search of Magento with a typo-tolerant, fast & relevant search experience backed by Algolia. It's based on [algoliasearch-client-php](https://github.com/algolia/algoliasearch-client-php), [autocomplete.js](https://github.com/algolia/autocomplete.js) and [instantsearch.js](https://github.com/algolia/instantsearch.js).


Installation
------------

The easiest way to install the extension is to use [Composer](https://getcomposer.org/)

Run the following commands:

- ```$ composer require diepxuan/module-autologin```
- ```$ bin/magento module:enable Diepxuan_Autologin```
- ```$ bin/magento setup:upgrade && bin/magento setup:static-content:deploy```
