# Sisow Magento 2 Plug-in

This plug-in will install all the Sisow payment methods to  your Magento installation. 

For more information about the payment methods visit our website https://www.sisow.nl/betaalmethoden/ (dutch only).

## Getting Started

With the instructions below you can install our plug-in to your Magento 2.

## Installation

### Installing with Composer

In command line (SSH), navigate to the installation directory of your magento2 installation.

Enter the following commands:

```
composer require sisow/plg-magento2
php bin/magento setup:upgrade
php bin/magento cache:clean
```

The plugin is now installed, you can now go to the [configuration](#configuration)

### Installing without Composer

---
- Download the latest version from Github
- Connect to your Magento with (s)FTP
- Create the path 'app/code/Sisow/Payment' in the root of your Magento
- Upload the files from Github to 'app/code/Sisow/Payment'
---

In command line (SSH), navigate to the installation directory of your magento2 installation.

Enter the following commands:

```
php bin/magento setup:upgrade
php bin/magento cache:clean
```

### Configuration

Below you will find a quickstart for the configuration, for a full description please contact Sisow on support@sisow.nl.
1. Log into the Magento Admin
2. Go to *Stores* / *Configuration*
3. Go to *Sales* / *Payment Methods*
4. Scroll down to find the "Sisow" Settings
5. Enter under General Settings the Merchant ID and Merchant Key, these can be found in your Sisow portal --> https://www.sisow.nl/Sisow/Portal/Lgn.aspx
6. Save the settings
7. After this you can enable the Sisow payment method you like
8. Save the settings

## Changelog
5.4.0
- Updated to new Klarna API
- Changed default name Spraypay

5.3.3
- Display fee inc/excl fee correct

5.3.2
- Better fooman validation

5.3.1
- fix: sisow class not loaded by objectManager anymore
- Set correct version number in composer.json
- added changelog to readme

5.3.0
- Added payment method spraypay