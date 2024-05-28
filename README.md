Autologin module for Magento 2
==================
[![Packagist](https://img.shields.io/packagist/v/diepxuan/module-autologin)](https://packagist.org/packages/diepxuan/module-autologin)
[![Magento 2](https://img.shields.io/badge/Magento-%3E=2.4-blue.svg)](https://github.com/magento/magento2)
[![Downloads](https://img.shields.io/packagist/dt/diepxuan/module-autologin)](https://packagist.org/packages/diepxuan/module-autologin)
[![License](https://img.shields.io/packagist/l/diepxuan/module-autologin)](https://packagist.org/packages/diepxuan/module-autologin)

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
