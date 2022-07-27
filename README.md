<p align="center">
  <img src="https://www.buckaroo.nl/media/3590/sisow_magento2.png" width="200px" position="center">
</p>

# Buckaroo Magento 2 Payments Plugin (former Sisow)

### Index
- [About](#about)
- [Installation](#installation)
- [Configuration](#configuration)
- [Contribute](#contribute)
- [Versioning](#versioning)
- [Additional information](#additional-information)
---

### About
Magento is an e-commerce platform owned by Adobe. There are two versions: Magento Open Source, the free, open source version written in PHP, and Magento Commerce, the paid cloud version. More than 250,000 merchants around the world use the Magento platform.

**Please note:** Buckaroo took over Sisow in 2021 and thereby also all of their plugins. This Magento 2 plugin is the former Sisow version and it contains a ready-to-sell payment gateway for Buckaroo. However new functionalities will only be added in [Buckaroo's main Magento 2 plugin](https://github.com/buckaroo-it/Magento2) and not in this specific plugin.

For more information about the former Sisow plugin and the migration, please visit:
https://www.buckaroo.nl/sisow

### Installation
We recommend you to install the Buckaroo Magento 2 (former Sisow) Payments plugin with composer. It is easy to install, update and maintain.

-   Download the latest version from Github
-   Connect to your Magento with (s)FTP
-   Create the path 'app/code/Sisow/Payment' in the root of your Magento
-   Upload the files from Github to 'app/code/Sisow/Payment'

In the command line (SSH), navigate to the installation directory of your magento2 installation.

**Enter the following commands:**

```
composer require sisow/plg-magento2
php bin/magento setup:upgrade
php bin/magento cache:clean
```

### Configuration

Below you will find a quickstart for the configuration, for a full description please contact Buckaroo Technical Support on  [support@buckaroo.nl](mailto:support@buckaroo.nl).

1.  Log into the Magento Admin.
2.  Go to  _Stores_  →  _Configuration_.
3.  Go to  _Sales_  →  _Payment Methods_.
4.  Scroll down to find the "Buckaroo" Settings.
5.  Enter under General Settings the Merchant ID and Merchant Key, these can be found in the Buckaroo Plaza →  [https://plaza.buckaroo.nl](https://plaza.buckaroo.nl)
6.  Save the settings.
7.  After this you can enable the Buckaroo payment methods in the plugin. Please keep in mind that you can only enable payment methods with a active subscription within your Buckaroo account.
8.  Save the settings.

### Contribute
We really appreciate it when developers contribute to improve the Buckaroo plugins.
If you want to contribute as well, then please follow our [Contribution Guidelines](CONTRIBUTING.md).

### Versioning 
<p align="left">
  <img src="https://www.buckaroo.nl/media/3480/magento_versioning.png" width="500px" position="center">
</p>

- **MAJOR:** Breaking changes that require additional testing/caution.
- **MINOR:** Changes that should not have a big impact.
- **PATCHES:** Bug and hotfixes only.


### Additional information
- **Support:** https://support.buckaroo.eu/contact
- **Contact:** [support@buckaroo.nl](mailto:support@buckaroo.nl) or [+31 (0)30 711 50 50](tel:+310307115050)
