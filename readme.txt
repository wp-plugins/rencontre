=== Plugin Name ===
Contributors: jacques malgrange
Donate link: http://www.boiteasite.fr/
Tags: date, meet, love, chat, webcam, rencontres
Requires at least: 3.0.1
Tested up to: 3.7
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. Non third party.

== Description ==

This WordPress plugin allows you to create a real dating website with Wordpress. It is simple to install and administer. The features are as follows :

* Login Required to access functionality
* Home unconnected with overview of the latest registered members
* Private messaging between members
* Extended profiles
* Private Members chat with webcam
* Sending smiles and contact requests
* Advanced Search
* Reporting of non-compliant member profiles
* Connecting with a FaceBook account
* Sending regular emails to members in accordance with the quota server
* Using the wp_users table for members to benefit of WordPress functions
* Daily cleaning to maintain the level of performance
* Low resource
* Multilingual
* Easy administration with filtering members

== Installation ==

*Install and Activate*

1. Unzip the downloaded rencontre zip file
2. Upload the `rencontre` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate Rencontre from Plugins page

*Implement*

The plugin works with a widget that uses the entire width of the page. You must create this widget area if it does not exist.

Members connect and register with the WordPress connection system. wp_loginout and register link should be in the theme.

For visitors not connected, you can view thumbnails and small profile of the last registered members using the shortcode [rencontre_libre] or php `<?php if(!is_user_logged_in()) Rencontre::f_ficheLibre(); ?>`

== Frequently Asked Questions ==

More details in french [here](http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html).

== Screenshots ==

1. The home page of a connected member.
2. Visitor's home page when not connected.
3. Administration members.
4. Administration of available profiles.

== Changelog ==

= 1.0 =
* 09/06/2014 - First stable version.
