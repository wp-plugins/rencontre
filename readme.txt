=== Plugin Name ===
Contributors: jacques malgrange
Donate link: http://www.boiteasite.fr/
Tags: date, dating, meet, love, chat, webcam, rencontres
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. No third party.

== Description ==

This WordPress plugin allows you to create a real dating website with Wordpress. It is simple to install and administer. The features are as follows:

* Login Required to access functionality ;
* Home unconnected with overview of the latest registered members ;
* Private messaging between members ;
* Extended profiles ;
* Private Members chat with webcam ;
* Sending smiles and contact requests ;
* Advanced Search ;
* Reporting of non-compliant member profiles ;
* Connecting with a FaceBook account ;
* Sending regular emails to members in accordance with the quota server ;
* Using the wp_users table for members to benefit of WordPress functions ;
* Daily cleaning to maintain the level of performance ;
* Low resource ;
* Multilingual ;
* Easy administration with filtering members ;
* Standalone, not depend on other services ;

available languages : FR, US, ES (thanks to Sanjay Gandhi).

== Installation ==

*Install and Activate*

1. Unzip the downloaded rencontre zip file
2. Upload the `rencontre` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate Rencontre from Plugins page

*Implement*

Method 1 : In your theme :

In your theme folder, in the page used for the dating part (index.php if you want to use the home page), add the content :
`&lt;?php if(is_user_logged_in()) {
$renc=new RencontreWidget; $renc->widget(0,0);} ?&gt;`

Method 2 : With a Widget :

* Create a widget area that uses the entire width of the page if it doesn't exist. See register_sidebar in 'functions.php' or in WP Support.
* Active this area in the page of your theme :
`&lt;?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('my-area-name')) : endif; ?&gt;`
* In admin panel, move the 'rencontre' widget to 'my-area-name' area.

For visitors not connected, you can view thumbnails and small profile of the last registered members using the shortcode [rencontre_libre] or php
`&lt;?php if(!is_user_logged_in()) Rencontre::f_ficheLibre(); ?&gt;`

You need to add the WP connection link. You can add this in the header to have the WP and the Facebook connections links :
`&lt;?php Rencontre::f_loginFB(); 
	wp_loginout(home_url()); if (!is_user_logged_in()) { ?&gt;
		&lt;a href="wp-login.php?action=register"&gt;&lt;?php _e('Register'); ?&gt;&lt;/a&gt;
&lt;?php } ?&gt;`

When ready, go to admin panel and load the countries, load the profiles, set all parameters of the plugin and don't forget to save.


More details in french [here](http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html).

== Frequently Asked Questions ==

= Useful plugins to work with Rencontre =
* WP Better Emails : Adds a customizable good looking HTML template to all WP default plain/text emails ;
* GeoIP Detect : Add IP country flag in Rencontre members admin tab - Facilitates the detection of fake profile.

= Conditions to appear in un-logged homepage =
* Wait few days (parameter in admin) ;
* Have a photo on my profile ;
* Have an attention-catcher and an ad with more than 30 characters.

= How to personalize style =
The default style file is located in `rencontre/css/rencontre.css`.
You simply need to copy lines to be modified in the css file of your theme. And you can as well add other lines.
To overwrite default css file, add `#widgRenc` (and space) at the beginning of every new line.
Example :
`in rencontre.css :
.rencTab {background-color:#e8e5ce;}
in your css file :
#widgRenc .rencTab {background-color:#aaa; padding:1px;}`

More details in french [here](http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html).

== Screenshots ==

1. The home page of a connected member.
2. Visitor's home page when not connected.
3. Administration members.
4. Administration of available profiles.
5. Private webcam chat.

== Changelog ==

= 1.2 =
14/09/2014 :
* Multilingual Countries with Admin panel to add or change countries and regions
* Fix HTML format in e-mail
* Fix some bugs
* Add some translations in Admin part

= 1.1 =
19/06/2014 :
* Email sending : optimization and improvement.
* Emails translation.
* Fixed some bug...

22/06/2014 : Update 1
* Fix Facebook connect bug.

24/07/2014 : Update 2
* Add Spanish language (thanks to Sanjay Gandhi).
* Fixed some bug...

31/07/2014 : Update 3
* Memory of the search.
* Update installation page in readme file.

11/08/2014 : Update 4
* Limit number of result in search.

16/08/2014 : Update 5
* Fix Deletion of the Admin account by cron schedule.
* Add CSS clear in fiche libre.
* Input default CSS file in fiche libre.

23/08/2014 : Update 6
* Remove auto-zoom in fiche libre (unconnected).
* Fix CSS in fiche libre.
* Fix bug if no WPLANG in wp-config.php.
* Add my homepage setup in admin.

01/09/2014 : Update 7
* Fix default CSS.

04/09/2014 : Update 8
* auto close chat if inactif.
* Fix warning php opendir.

= 1.0 =
09/06/2014 - First stable version.
