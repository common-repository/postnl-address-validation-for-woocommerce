=== PostNL Address Validation for WooCommerce ===
Contributors: lettowstudios, kbrandse
Tags: woocommerce, delivery, address validation, postnl
Requires at least: 6.0
Tested up to: 6.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.opensource.org/licenses/gpl-license.php

With the PostNL Address Validation for WooCommerce plug-in you can easily find a Dutch or International address in the check out. This way you always ship to the right address.

== Description ==
###This plugin has been deprecated.
###There is no longer support for this plugin and from August 1st 2024 this plugin will not be available anymore. Please refer to the PostNL for WooCommerce plug-in. With this plug-in it is also possible to validate Dutch address data while it’s entered.

With the PostNL Address Validation for WooCommerce plug-in you can easily find a Dutch or International address in the check out. This way you always ship to the right address.
The plug-in is not only available for customers of PostNL Parcels. Do you also want to use this plugin? Go to Request API Key.
Make sure you have an active API key for the Address Check Netherlands or Address Check International. You can request this here. See also Request API Key.


## Request API Key

To use the PostNL Address Validation for Woocommerce you need an API-Key.
If you only want to have Dutch addresses validated, you need an API key for Adrescheck Nederland.
If you also want to validate International addresses, you need an API key for Address Check International .

Do you already send packages with PostNL? Choose YES / NO

### I am not a PostNL customer
1. Go to request Address check API [click here](https://www.postnl.nl/zakelijke-oplossingen/slimme-dataoplossingen/adrescheck/abonnement/).
2. Choose ‘Nee’ and press ‘Neem contact op’

Fill in your details and indicate in comment that you use Woocommerce and whether you want to validate International or only Dutch addresses.
Then choose Volgende

Choose now for Beëindigen to complete the application.

###  I am a PostNL customer
1. Go to request Address check API [Click here](https://www.postnl.nl/zakelijke-oplossingen/slimme-dataoplossingen/adrescheck/abonnement/).

2. Choose Yes and select the desired product
   **The Netherlands** if you only want to validate Dutch addresses.
   **International** if you also want to validate foreign addresses.

3. Enter the e-mail address you use for your business PostNL account.

4. Choose ‘Direct aanvragen’
   If your e-mail address is recognized, you will now receive your API key.

###Letop
Please note that you will find your API key on My PostNL and go to manage.  Click here for the manual.

**Please note:** the API Key for the Postcode Check does not work for this plugin

You must have an API-Key for **Adrescheck Nederland** for Dutch addresses or **Adrescheck International** if you  want to validate both Dutch addresses  and international addresses.

Do you need help? Go to [https://developer.postnl.nl/](https://developer.postnl.nl/)
or contact Sales support by phone 0888683747 or email datasolutions@postnl.nl.

== Installation ==

## Automatic installation
Automatic installation is the easiest option as WordPress handles the file transfers itself.
To do an automatic install of PostNL Address Validation for WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "PostNL Address Validation for WooCommerce" and click Search Plugins.
You can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin.
Click yes and WordPress will automatically complete the installation.

## Manual installation via the WordPress interface =
1. Download the plugin zip file to your computer
2. Go to the WordPress admin panel menu Plugins > Add New
3. Choose upload
4. Upload the plugin zip file, the plugin will now be installed
5. After installation has finished, click the 'activate plugin' link

## Manual installation via FTP =
1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

## Setting up the plugin
1. Go to the menu `WooCommerce > Settings -> "PostNL Address Validation for Woocommerce"`.
2. Fill in your API Details.
3. Select National or International
3. The plugin is ready to be used!

== Testing ==
Testing the Plug-in
• We advise you to test the Extension.
• Go back to the webshop, place a product in your shopping cart.
• Complete the order,
• Enter an existing postal code and house number combination,
• This should now be retrieved, (Select address)
• If this goes well, the installation has been successfully completed.
• If you want to manual edit the address you can press ‘Edit Address’



== Frequently Asked Questions ==
= How do I get an API key? =
1. Go to request Address check API [click here](https://www.postnl.nl/zakelijke-oplossingen/slimme-dataoplossingen/adrescheck/abonnement/).

== Screenshots ==

  1. Validate Dutch addresses.
  2. Manual editing the address.
  3. Validate international addresses.
  4. Admin settings.

== Changelog ==
= 1.0.0 =
* First release.
= 1.0.3 =
* Minor bugfixes.
= 1.0.4 =
* Fix international address notation.
= 1.0.5 =
* Add State Selection
= 1.0.6 =
* Fix State Selection
= 1.0.7 =
* Add Notification for woo-postnl settings and bug fixes
= 1.0.8 =
* Resolve the double housenumber compatibility problem with the woo-postnl plugin.
= 1.0.9 =
* Add plugin deprication notification
== Upgrade Notice ==
