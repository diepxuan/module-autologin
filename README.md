Autologin module for Magento 2
==================
[![Latest version](https://img.shields.io/badge/latest-1.0.6-green.svg)](https://github.com/diepxuan/module-autologin)
[![Packagist](https://img.shields.io/badge/packagist-1.0.6-green.svg)](https://packagist.org/packages/diepxuan/module-autologin)
[![Magento 2](https://img.shields.io/badge/Magento-%3E=2.1-blue.svg)](https://github.com/magento/magento2)
[![PHP >= 5.5.22](https://img.shields.io/badge/PHP-%3E=5.6.5-blue.svg)](https://packagist.org/packages/diepxuan/module-autologin)

- **Auto-login:** Provides automatic logins for administrators.


Autologin
--------------

This extension amend the default authentication of Magento, it use for developer, whom do not want use time for login to administrators


Installation
------------

### Step 1 : Download Magento 2 Autologin Extension

The easiest way to install the extension is to use [Composer](https://getcomposer.org/)

Run the following commands:

```
composer require diepxuan/module-autologin
bin/magento module:enable Diepxuan_Autologin
bin/magento setup:upgrade && bin/magento setup:static-content:deploy
```

### Step 2: General configuration

`Login to Magento admin > Stores > Configuration > ADVANCED > Admin > Auto Login > Enable Autologin in Admin > Choose Yes to enable the module.`

After you finish configuring, save and clear the cache.
Run the following command:
   
```
php bin/magento cache:clean
```
