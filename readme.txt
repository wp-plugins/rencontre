=== Rencontre ===
Contributors: jacques malgrange
Donate link: http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html
Tags: date, dating, meet, love, chat, webcam, rencontres
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 1.5
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
* Proximity search on GoogleMap ;
* Reporting of non-compliant member profiles ;
* Connecting with a FaceBook account ;
* Sending regular emails to members in accordance with the quota server ;
* Using the wp_users table for members to benefit of WordPress functions ;
* Daily cleaning to maintain the level of performance ;
* Low resource, optimized for shared web server ;
* Unlimited number of members ;
* Multilingual ;
* Easy administration with filtering members ;
* Import/Export members in CSV with photos ;
* Standalone, not depend on other services ;

= Internationalization =

Rencontre is currently available in :

* English (main language)
* French - thanks to me :)
* Spanish - thanks to Sanjay Gandhi
* Danish - thanks to [C-FR](http://www.C-FR.net/ "C-FR")
* Chinese - thanks to Lucien Huang
* Portuguese - thanks to Patricio Fernandes

If you have translated the plugin in your language or want to, please let me know on Support page.

== Installation ==

= Install and Activate =

1. Unzip the downloaded rencontre zip file
2. Upload the `rencontre` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate Rencontre from Plugins page

= Implement =

**Primo**

Method 1 : In your theme (recommended) :

In your theme folder, in the page used for the dating part (index.php if you want to use the home page - see F.A.Q.), add the content :
`&lt;?php if(is_user_logged_in()) {
$renc=new RencontreWidget; $renc->widget(0,0);} ?&gt;`

Method 2 : With a Widget :

* Create a widget area that uses the entire width of the page if it doesn't exist. See register_sidebar in 'functions.php' or in WP Support.
* Active this area in the page of your theme :
`&lt;?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('my-area-name')) : endif; ?&gt;`
* In admin panel, move the 'rencontre' widget to 'my-area-name' area.

**Secundo**

For visitors not connected, you can view thumbnails and small profile of the last registered members using the shortcode [rencontre_libre] or php
`&lt;?php if(!is_user_logged_in()) Rencontre::f_ficheLibre(); ?&gt;`
If you write f_ficheLibre(1), there will be as many men as women.

You can also get the number of members in base with this php and ('girl'), ('men'), ('girlPhoto'), ('menPhoto') or () for all :
`&lt;?php if(!is_user_logged_in()) echo Rencontre::f_nbMembre('girlPhoto'); ?&gt;`

**Tertio**

You need to add the WP connection link. You can add this in the header to have the WP and the Facebook connections links :
`&lt;?php Rencontre::f_loginFB(); 
	wp_loginout(home_url()); if (!is_user_logged_in()) { ?&gt;
		&lt;a href="wp-login.php?action=register"&gt;&lt;?php _e('Register'); ?&gt;&lt;/a&gt;
&lt;?php } ?&gt;`

**Quarto**

When ready, go to admin panel and load the countries, load the profiles, set all parameters of the plugin and don't forget to save.

In Settings / General, check the box 'Anyone can register' with role 'Subscriber'.

**Quinto**

Register as a member : Click Register, add login and email.

If you are localhost, you can't validate the email, but, in Admin panel / users, you can change the password of this new members.
Then, log in with this new login/password. Welcome to the dating part.

More details in french [here](http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html).

== Frequently Asked Questions ==

= What template file to use =
In WordPress default themes (2013, 2015...) you have to do that :
* copy page.php ;
* paste it with new name (ex : page-rencontre.php) ;
* add the code mentioned in installation para secundo and tertio at the best place, just after content div ;
* clean unneeded code ;
* in admin panel / page, create or change a page to set page-rencontre as template ;
* in admin panel / settings / reading, choose static page and the page you just changed.

If nothing happens, add `&lt;h1>*** HELLO ***&lt;/h1>`. If you don't see this title, you are not using the right template.

= Useful plugins to work with Rencontre =
* WP Better Emails : Adds a customizable good looking HTML template to all WP default plain/text emails ;
* GeoIP Detect : Add IP country flag in Rencontre members admin tab - Facilitates the detection of fake profile.
* WP GeoNames : Insert all or part of the global GeoNames database in your WordPress base - Suggest city to members.

= Conditions to appear in un-logged homepage =
* Wait few days (parameter in admin) ;
* Have a photo on my profile ;
* Have an attention-catcher and an ad with more than 30 characters ;
* Rencontre::f_ficheLibre() is on the right template.

= How to personalize style =
The default style file is located in `rencontre/css/rencontre.css`.
You simply need to copy lines to be modified in the css file of your theme. And you can as well add other lines.
To overwrite default css file, add `#widgRenc` (and space) at the beginning of every new line.
Example :
`in rencontre.css :
.rencTab {background-color:#e8e5ce;}
in your css file :
#widgRenc .rencTab {background-color:#aaa; padding:1px;}`

= How to show only the girls in un-logged homepage =
There are four categories differentiated by a different class CSS : girl, men, gaygirl and gaymen.
To see only the heterosexual girls, add in the CSS file of your theme :
` /* CSS */
 #widgRenc .rencBox.men,
 #widgRenc .rencBox.gaygirl,
 #widgRenc .rencBox.gaymen{display:none;}`

= How to set the plugin multilingual =
Add little flags in the header of your theme. On click, you create cookie with the right language. Then, the site changes language (back and front office) :
`&lt;div id="lang"&gt;
	&lt;a href="" title="Fran&ccedil;ais" onClick="javascript:document.cookie='lang=fr_FR'"&gt;
		&lt;img src="&lt;?php echo plugins_url('rencontre/images/drapeaux/France.png'); ?&gt;" alt="Fran&ccedil;ais" /&gt;
	&lt;/a&gt;
	&lt;a href="" title="English" onClick="javascript:document.cookie='lang=en_US'"&gt;
		&lt;img src="&lt;?php echo plugins_url('rencontre/images/drapeaux/Royaume-Uni.png'); ?&gt;" alt="English" /&gt;
	&lt;/a&gt;
&lt;/div&gt;`

= What to include with WP-GeoNames =
* Columns : minimum is name, latitude, longitude, country code, feature class & code.
* Type of data : only P (city).

It's better to limit the data size.

More details in french [here](http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html).

== Screenshots ==

1. The home page of a connected member.
2. Visitor's home page when not connected.
3. Administration members.
4. Administration of available profiles.
5. Private webcam chat.
6. Proximity search on GoogleMap.

== Changelog ==

= 1.5 =
16/03/2015 : Change main language from French to English

25/03/2015 : Update 1

* Fix pagination bug in search result after first page.
* Fix bug when change profil in admin.
* Search user in admin by Alias or E-mail.

= 1.4 =
06/12/2014 :

* Proximity search with GoogleMap.
* Improve separation between gay / heterosexual.
* Fix bug with Shortcode.
* Search result order by date of last connection.

23/12/2014 : Update 1 : Fix style bug.

07/01/2015 : Update 2 : Change the request to GET. Add custom text in image copyright.

03/02/2015 : Update 3

* Number of pictures configurable from one to eight.
* Add dropbox to pictures.
* Add previous msg when reply.
* Fix bug in msg with same subject.
* Fix some bugs.

04/02/2015 : Update 4 : Style select box, fix some bugs.

12/02/2015 : Update 5 : Display date of last connection.

14/02/2015 : Update 6 : Fix unsubscribe and subscribe bug.

22/02/2015 : Update 7 : Fix warning during installation.

= 1.3 =
15/10/2014 :

* Import/Export members in CSV with photos.
* Add Chinese language (thanks to Lucien Huang).
* Add pseudo in chat.
* Add code to get number of members in base.
* Fix some bugs.

20/10/2014 : Update 1 : Countries and profiles in Chinese language (thanks to Lucien Huang).

26/10/2014 : Update 2 : homogeneous distribution between men and women in un-logged homepage.

08/11/2014 : Update 3 : improves the automatic email sending (backlink, monthly/fortnightly/weekly options).

26/11/2014 : Update 4 : fix defect with smiles in search result.

30/11/2014 : Update 5 : translation correction.

01/12/2014 : Update 6 : suggest a city from the geonames database if plugin wp-geonames is installed.

= 1.2 =
14/09/2014 :

* Multilingual Countries with Admin panel to add or change countries and regions.
* Fix HTML format in e-mail.
* Fix some bugs.
* Add some translations in Admin part.

16/09/2014 : Update 1 : Fix conflict with Yop-Poll.

18/09/2014 : Update 2

* Add Danish language (thanks to C-FR.net).
* Add pagination in search result.
* Fix bug in country select (sort in all languages).
* Fix incompatibility with some servers for the small copyright on members photos.

21/09/2014 : Update 3 : Fix the country selected in -my account-.

26/09/2014 : Update 4

* Change quick search result when option Members without photo less visible disabled.
* Add option in Admin to set default country.
* Add class CSS "girl, men, gaygirl, gaymen" in unconnected overview list.
* Fix error in small copyright function (again).
* Fix Deletion of the Admin account (again).

28/09/2014 : Update 5 : Fix some bugs.

01/10/2014 : Update 6 : Add link to user profile in message tab.

07/10/2014 : Update 7 : Fix bug in search result.

08/10/2014 : Update 8 : Add multilingual hook.

= 1.1 =
19/06/2014 :

* Email sending : optimization and improvement.
* Emails translation.
* Fixed some bug...

22/06/2014 : Update 1 : Fix Facebook connect bug.

24/07/2014 : Update 2

* Add Spanish language (thanks to Sanjay Gandhi).
* Fixed some bug...

31/07/2014 : Update 3

* Memory of the search.
* Update installation page in readme file.

11/08/2014 : Update 4 : Limit number of result in search.

16/08/2014 : Update 5

* Fix Deletion of the Admin account by cron schedule.
* Add CSS clear in fiche libre.
* Input default CSS file in fiche libre.

23/08/2014 : Update 6

* Remove auto-zoom in fiche libre (unconnected).
* Fix CSS in fiche libre.
* Fix bug if no WPLANG in wp-config.php.
* Add my homepage setup in admin.

01/09/2014 : Update 7 : Fix default CSS.

04/09/2014 : Update 8

* auto close chat if inactif.
* Fix warning php opendir.

= 1.0 =
09/06/2014 - First stable version.
