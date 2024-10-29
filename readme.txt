=== Add-on Brevo for Gravity Forms ===
Author: WP connect
Author URI: https://wpconnect.co/
Contributors: wpconnectco, pskli, staurand
Tags: wpconnect, sendinblue, brevo, gravity forms, api, forms
Requires at least: 5.5
Tested up to: 6.5
Tested with Gravity Forms up to: 2.7.14
Requires PHP: 7.0
Stable tag: 2.3.0
License: GPLv2 or later

Connect the awesome WordPress Gravity Forms plugin to the relationship marketing platform Brevo (ex Sendinblue). With this game-changing plugin, you can link any Gravity Forms field with Brevo attributes. When validating the form, you generate a contact in the selected list(s) with all the correct attributes according to the information entered.


== Features == 

= Add and manage multiple feeds =
* Create as many forms as you want
* Set up an unlimited number of feeds for each form ([Pro version](https://wpconnect.co/gravity-forms-sendinblue-add-on/?utm_campaign=free-version&utm_source=wp-org&utm_content=link1))

= Customize your feeds swiftly =
* Map Gravity Forms fields with your Brevo attributes
* Select the destination list(s) for each of your feeds

= Enable double opt-in email =  
* Activate the double opt-in option
* Choose the fitting template for the confirmation email

Double op-tin only available with [Pro version](https://wpconnect.co/gravity-forms-sendinblue-add-on/?utm_campaign=free-version&utm_source=wp-org&utm_content=link2).

= Add conditions, actions & filters =
* Select which contacts will be added to your lists by creating simple – or advanced – conditions


== Free version ==
This is the free version of our [Gravity Forms to Brevo plugin](https://wpconnect.co/gravity-forms-sendinblue-add-on/?utm_campaign=free-version&utm_source=wp-org&utm_content=link3). 
**Gravity Forms to Brevo Free** allows you to create up to one feed for each form, map an Email field with a Brevo email attribute and one Consent field with a boolean attribute. You can also send new contacts to a single Brevo list. Free version does not include unlimited feeds creation, multiple fields mapping, several lists selection and double opt-in option.

If you need more, check out [Pro version](https://wpconnect.co/gravity-forms-sendinblue-add-on/?utm_campaign=free-version&utm_source=wp-org&utm_content=link4) to unlock all features.

[youtube https://www.youtube.com/watch?v=hUw9KFCkHMo]


== Installation ==

1. Upload plugin files to your plugins folder

2. Activate the plugin on your WordPress Back Office

3. Go to the Gravity Forms settings page (under Forms > Settings > Brevo)

4. Enter the information requested by the plugin: Brevo API key

5. Click Save Settings

6. Create your form then go to Settings > Brevo

7. Follow on-screen instructions for integrating with Brevo


== How does it work? How to use it? ==

1. Create a form with an email field (don’t forget the consent field)

2. Go to the Brevo tab of your form’s settings

3. Map your Brevo attributes with Gravity Forms form fields

4. Choose a Brevo list where the contacts should be added

5. Add Conditional logic if needed

6. Click on “Save settings”


== Frequently Asked Questions ==

= What is Brevo? =
Brevo is a powerful all-in-one marketing platform. Combining many powerful features, a competitive pricing, and a very good deliverability thanks to the proprietary Cloud-base infrastructure, Brevo managed to convince thousands of companies to use the platform for their newsletters, automatic emails or SMS. Brevo is available in 5 languages: English, Spanish, French, Italian, Brazilian.

= Why do I need a Brevo account? =
Gravity Forms to Brevo plugin uses Brevo’s API to send data. Creating an account on Brevo is free and takes less than 2 minutes. Once logged in your contact, you can get the API key [from this page](https://account.sendinblue.com/advanced/api).

= Do I have to pay to use the plugin and send emails? =
Our plugin is free for basic features. Pro version costs $29 including updates and support for one year. Brevo offers a free plan with 9000 emails/month.

= How do I synchronize my lists? =
You don't have to do anything, the synchronization is automatic. Make sure you have created your lists and Brevo attributes before linking them to your form fields. If you don\'t see them, wait for one minute. For performance reasons, your Brevo list(s) and attribute(s) are cached for one minute.

= How can I get support? =
If you need some assistance, open a ticket on the [Support](https://wordpress.org/support/plugin/addon-gravityforms-sendinblue-free/).


== Screenshots ==

1. Plugin Settings page

2. Enable switch & fields mapping

3. Lists & conditional logic


== Changelog ==

= 2.3.0 =
* Fix: error PHP undefined property
* Improvement: Remove license field

= 2.2.0 =
* WordPress 6.5 compatibility

= 2.1.0 =
* Feature: "emailBlacklisted" parameter when creating a contact
* Fix: error api not showing in metaboxe of entries Gravity Forms
* WordPress 6.4 compatibility

= 2.0.0 = 
* Improvement: Brevo (ex Sendinblue) name to Brevo
* WordPress 6.3 compatibility

= 1.1.0 = 
* Feature: Switch between the free version and the pro version
* Improvement: Sendinblue name to Brevo

= 1.0.3 =
* WordPress 6.2 compatibility

= 1.0.2 =
* Feature: Upgrade to Pro link

= 1.0.1 =
* Fix: Test to verify if Pro version is activated
* Fix: JavaScripts errors on other Gravity Forms add-ons

= 1.0 =
Initial release


== Support ==
If you need some assistance, open a ticket on the [Support](https://wordpress.org/support/plugin/addon-gravityforms-sendinblue-free/).

== Troubleshooting ==
If you generate a list while creating an unsaved feed, an error may pop up during the process. Save your feed before reloading the page and your list will appear. To avoid the inconvenience, create your list before your feed.
Make sure all your Brevo attributes are generated before liking them with Gravity Forms form fields. If you can’t see them, wait a minute: your lists and attributes are cached for optimal performance.