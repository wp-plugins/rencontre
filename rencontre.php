<?php
/*
Plugin Name: Rencontre
Author: Jacques Malgrange
Plugin URI: http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html
Description: A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. No third party.
Version: 1.6
Author URI: http://www.boiteasite.fr
*/
$rencVersion = '1.6';
// **********************************************************************************
// INSTALLATION DU PLUGIN - Creation des tables en BDD
// **********************************************************************************
register_activation_hook ( __FILE__, 'rencontre_creation_table');
require('inc/rencontre_filter.php' );
		// **** PATCH V1.2 : langue pour les pays *****************************************
			add_action('admin_notices','patch12');
			function patch12()
				{
				global $wpdb;
				$n = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' ");
				if(!$n) // pas de pays ou table vide
					{
					$m = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
					if(!$m) echo '<div class="update-nag"><p>Plugin <strong>Rencontre</strong> - '.__('You have to install countries','rencontre').'</p></div>';
					else
						{
						$o = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users");
						if($o) echo '<div class="update-nag"><p>Plugin <strong>Rencontre</strong> - Patch V1.2 : '.__('You have to deactivate then reactivate the plugin','rencontre').'</p></div>';
						}
					}
				}
		// ************************************************************************************
function rencontre_creation_table()
	{
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // pour utiliser dbDelta()
	global $wpdb;
	//
	if(!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if(!empty($wpdb->collate)) $charset_collate .= " COLLATE $wpdb->collate";
	$nom = $wpdb->prefix . 'rencontre_profil';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` smallint unsigned NOT NULL auto_increment,
			`c_categ` varchar(50) NOT NULL,
			`c_label` varchar(100) NOT NULL,
			`t_valeur` text,
			`i_type` tinyint NOT NULL,
			`i_poids` tinyint NOT NULL,
			`c_lang` varchar(2) NOT NULL,
			INDEX (`id`)
			) $charset_collate;";
		dbDelta($sql); // necessite wp-admin/includes/upgrade.php
		}
	$nom = $wpdb->prefix . 'rencontre_users';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`user_id` bigint(20) unsigned NOT NULL,
			`c_ip` varchar(50) NOT NULL,
			`c_pays` varchar(50) NOT NULL,
			`c_region` varchar(50) NOT NULL,
			`c_ville` varchar(50) NOT NULL,
			`e_lat` decimal(10,5) NOT NULL,
			`e_lon` decimal(10,5) NOT NULL,
			`i_sex` tinyint NOT NULL,
			`d_naissance` date NOT NULL,
			`i_taille` tinyint unsigned NOT NULL,
			`i_poids` tinyint unsigned NOT NULL,
			`i_zsex` tinyint NOT NULL,
			`i_zage_min` tinyint unsigned NOT NULL,
			`i_zage_max` tinyint unsigned NOT NULL,
			`i_zrelation` tinyint NOT NULL,
			`i_photo` bigint(20) unsigned NOT NULL,
			`d_session` datetime NOT NULL,
			FOREIGN KEY (`user_id`) REFERENCES ".$wpdb->prefix . "users(`ID`) ON DELETE CASCADE
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_users_profil';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`user_id` bigint(20) unsigned NOT NULL,
			`d_modif` datetime NULL,
			`t_titre` tinytext,
			`t_annonce` text,
			`t_profil` text,
			`t_action` text,
			`t_signal` text,
			FOREIGN KEY (`user_id`) REFERENCES ".$wpdb->prefix . "users(`ID`) ON DELETE CASCADE
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_liste';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` smallint unsigned NOT NULL auto_increment,
			`c_liste_categ` varchar(50) NOT NULL,
			`c_liste_valeur` varchar(50) NOT NULL,
			`c_liste_iso` varchar(2) NOT NULL,
			`c_liste_lang` varchar(2) NOT NULL,
			PRIMARY KEY (`id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_msg';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` bigint(20) NOT NULL auto_increment,
			`subject` text NOT NULL,
			`content` text NOT NULL,
			`sender` varchar(60) NOT NULL,
			`recipient` varchar(60) NOT NULL,
			`date` datetime NOT NULL,
			`read` tinyint(1) NOT NULL,
			`deleted` tinyint(1) NOT NULL,
			PRIMARY KEY (`id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_prison';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` smallint unsigned NOT NULL auto_increment,
			`d_prison` datetime NOT NULL,
			`c_mail` varchar(100) NOT NULL,
			`c_ip` varchar(50) NOT NULL,
			PRIMARY KEY (`id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	//
	}
//
// **********************************************************************************
// CLASSE Rencontre
// **********************************************************************************
if(is_admin()) require(dirname(__FILE__).'/inc/base.php');
new Rencontre();
class Rencontre
	{
	function __construct()
		{
		// Variables globale Rencontre
		global $rencOpt; global $rencDiv; global $wpdb;
		$upl = wp_upload_dir();
		$rencOpt = get_option('rencontre_options');
		$rencDiv['basedir'] = $upl['basedir'];
		$rencDiv['baseurl'] = $upl['baseurl'];
		$rencDiv['blogname'] = get_option('blogname');
		$rencDiv['admin_email'] = get_option('admin_email');
		$rencDiv['siteurl'] = get_option('siteurl');
		$rencDiv['lang'] = ((defined('WPLANG')&&WPLANG)?WPLANG:get_locale());
		$q = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".substr($rencDiv['lang'],0,2)."' ");
		if(!$q) $rencDiv['lang'] = "en_US";
		if (!$rencOpt)
			{
			$rencOpt = array('facebook'=>'','fblog'=>'','home'=>'','pays'=>'','limit'=>20,'tchat'=>0,'map'=>0,'hcron'=>3,'mailmois'=>0,'textmail'=>'','mailanniv'=>0,'textanniv'=>'','qmail'=>25,'npa'=>12,'jlibre'=>3,'prison'=>30,'anniv'=>1,'ligne'=>1,'mailsupp'=>1,'onlyphoto'=>1,'imnb'=>4,'imcrypt'=>0,'imcopyright'=>1,'txtcopyright'=>'');
			update_option('rencontre_options', $rencOpt);
			}
		load_plugin_textdomain('rencontre', false, dirname(plugin_basename( __FILE__ )).'/lang/'); // language
		add_action('widgets_init', array($this, 'rencwidget')); // WIDGET
		if(is_admin())
			{
			add_action('admin_menu', array($this, 'admin_menu_link')); // Menu admin
			add_action('admin_print_scripts', array($this, 'adminCSS')); // CSS pour le bouton du menu
			}
		// ****************** V1.4 (GPS) ********************
		$q = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."rencontre_users");
		if(!isset($q->e_lat))
			{
			$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users 
				ADD `e_lat` decimal(10,5) NOT NULL,
				ADD `e_lon` decimal(10,5) NOT NULL,
				ADD `d_session` datetime NOT NULL");
			// **** PATCH V1.2 : langue pour les pays ***************************
			$sql = $wpdb->get_var('SELECT c_liste_categ FROM '.$wpdb->prefix.'rencontre_liste WHERE c_liste_categ="Pays"');
			if($sql) // update
				{
				$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."rencontre_liste");
				$sql1 = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'rencontre_liste');
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste DROP COLUMN i_liste_lien ");
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_iso` varchar(2) NOT NULL");
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_lang` varchar(2) NOT NULL");
				}
			// **********************************************************************
			}
		// *************************************************
		}
	//
	function admin_menu_link()
		{
		if(is_admin())
			{
			add_menu_page('Rencontre', 'Rencontre', 'manage_options', basename(__FILE__), array(&$this, 'menu_general'), 'div'); // ajoute un menu Rencontre (et son premier sous-menu)
			add_submenu_page('rencontre.php', __('Rencontre - General','rencontre'), __('General','rencontre'), 'manage_options', 'rencontre.php', array(&$this, 'menu_general') ); // repete le premier sous-menu (pour changer le nom)
			add_submenu_page('rencontre.php', __('Rencontre - Members','rencontre'), __('Members','rencontre'), 'manage_options', 'rencmembers', array(&$this, 'menu_membres') );
			add_submenu_page('rencontre.php', __('Rencontre - Jail','rencontre'), __('Jail','rencontre'), 'manage_options', 'rencjail', array(&$this, 'menu_prison') );
			add_submenu_page('rencontre.php', __('Rencontre - Profile','rencontre'), __('Profile','rencontre'), 'manage_options', 'rencprofile', array(&$this, 'menu_profil') );
			add_submenu_page('rencontre.php', __('Rencontre - Countries','rencontre'), __('Country','rencontre'), 'manage_options', 'renccountry', array(&$this, 'menu_pays') );
			}
		}
	//
	function menu_general() {rencMenuGeneral();} // base.php include if is_admin
	function menu_membres() {rencMenuMembres();}
	function menu_prison() {rencMenuPrison();}
	function menu_profil() {rencMenuProfil();}
	function menu_pays() {rencMenuPays();}
	//
	function rencwidget()
		{
		global $rencOpt;
		if (is_user_logged_in())
			{
			global $current_user; global $rencOpt; global $rencDiv; global $RencMid;
			get_currentuserinfo();
			$rol = $current_user->roles;
			$rencMid['id'] = $current_user->ID;
			$rencMid['login'] = $current_user->user_login;
			if (isset($_GET["rencidfm"]))
				{ // acces a la fiche d un membre depuis un lien email
				$_SESSION["rencidfm"] = preg_replace("/[^0-9]+/","",$_GET["rencidfm"]);
				echo "<script language='JavaScript'>document.location.href='".$rencOpt['home']."';</script>"; 
				}
			if (array_shift($rol)=="subscriber" && (!isset($_POST['nouveau']) || !$_POST['nouveau'])) $_SESSION['rencontre']='nouveau';
			else if (!isset($_SESSION['rencontre']) || ((!isset($_POST['page']) || !$_POST['page']) && (!isset($_GET['page']) || !$_GET['page']))) $_SESSION['rencontre']='mini,accueil,menu';
			else if (isset($_POST['page']) && $_POST['page']=='password') $_SESSION['rencontre']='mini,accueil,menu,password';
			else if (isset($_POST['page']) && $_POST['page']=='fin')
				{
				f_userSupp($current_user->ID,$current_user->user_login,0);
				if ($rencOpt['mailsupp'])
					{
					$q = $wpdb->get_var("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID='".$current_user->ID."'");
					$objet  = wp_specialchars_decode($rencDiv['blogname'], ENT_QUOTES).' - '.__('Account deletion','rencontre');
					$message  = __('Your account has been deleted','rencontre');
					@wp_mail($q, $objet, $message);
					}
				}
			else if (isset($_GET['page']) && $_GET['page']=='portrait') $_SESSION['rencontre']='portrait,menu';
			else if (isset($_GET['page']) && $_GET['page']=='sourire') $_SESSION['rencontre']='portrait,menu,sourire';
			else if (isset($_GET['page']) && $_GET['page']=='demcont') $_SESSION['rencontre']='portrait,menu,demcont';
			else if (isset($_GET['page']) && $_GET['page']=='signale') $_SESSION['rencontre']='portrait,menu,signale';
			else if (isset($_GET['page']) && $_GET['page']=='bloque') $_SESSION['rencontre']='portrait,menu,bloque';
			else if (isset($_GET['page']) && $_GET['page']=='change') $_SESSION['rencontre']='change,menu';
			else if (isset($_GET['page']) && $_GET['page']=='cherche') $_SESSION['rencontre']='cherche,accueil,menu';
			else if (isset($_GET['page']) && $_GET['page']=='trouve') $_SESSION['rencontre']='trouve,accueil,menu';
			else if (isset($_GET['page']) && $_GET['page']=='liste') $_SESSION['rencontre']='trouve,liste,accueil,menu';
			else if (isset($_GET['page']) && $_GET['page']=='msg') $_SESSION['rencontre']='msg,accueil,menu';
			else if (isset($_GET['page']) && $_GET['page']=='ecrire') $_SESSION['rencontre']='ecrire,accueil,menu';
			else if (isset($_GET['page']) && $_GET['page']=='compte') $_SESSION['rencontre']='compte,accueil,menu';
			$ho = false; if($_SESSION['rencontre']!='nouveau' && has_filter('rencGateP', 'f_rencGateP')) $ho = apply_filters('rencGateP', $ho);
			if($ho) $_SESSION['rencontre'] = 'gate';
			require(dirname (__FILE__) . '/inc/rencontre_widget.php');
			if (isset($_POST['nouveau']) && $_POST['nouveau'] && isset($_POST['pass1']) && $_POST['pass1']) RencontreWidget::f_changePass($current_user->ID);
			register_widget("RencontreWidget"); // class
			}
		else if (isset($_GET["rencidfm"])) echo "<script language='JavaScript'>document.location.href='".esc_url(home_url('/'))."wp-login.php?redirect_to=".$rencOpt['home']."?rencidfm=".$_GET["rencidfm"]."';</script>"; 
		}
	//
	function adminCSS()
		{
		echo '<style type="text/css">
			.toplevel_page_rencontre .wp-menu-image {background:transparent url('.plugin_dir_url(__FILE__).'/images/menu.png) no-repeat scroll 3px -30px;}
			.toplevel_page_rencontre:hover .wp-menu-image {background-position:3px 3px;}
			</style>';
		}
	//
	static function f_age($naiss=0) // transforme une date (TIME) en age
		{
		if ($naiss==0) return "vide";
		list($annee, $mois, $jour) = explode('-', $naiss);
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$age = $today['annee'] - $annee;
		if ($today['mois'] <= $mois) {if ($mois == $today['mois']) {if ($jour > $today['jour'])$age--;}else$age--;}
		return $age;
		}
	//
	static function f_ficheLibre($mix=0,$ret=0) // Creation du fichier HTML de presentation des membres en libre acces pour la page d accueil
		{
		if (!file_exists(plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html'))
			{
			$out = '<link rel="stylesheet" type="text/css" href="'.plugins_url('rencontre/css/rencontre.css').'" media="all" />'."\r\n";
		//	$out .= '<script type="text/javascript" src="'.plugins_url('rencontre/js/rencontre-libre.js').'"></script>'."\r\n"; // Zoom automatique sur chaque personne
			$out .= '<div id="widgRenc">'."\r\n";
			global $wpdb; global $rencOpt; global $rencDiv;
			if (!is_dir($rencDiv['basedir'].'/portrait/')) mkdir($rencDiv['basedir'].'/portrait/');
			if (!is_dir($rencDiv['basedir'].'/portrait/libre/')) mkdir($rencDiv['basedir'].'/portrait/libre/');
			$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
			$drap=''; $drapNom='';
			foreach($q as $r)
				{
				if($r->c_liste_categ=='d') $drap[$r->c_liste_iso] = $r->c_liste_valeur;
				else if($r->c_liste_categ=='p')$drapNom[$r->c_liste_iso] = $r->c_liste_valeur;
				}
			if ($mix) // repartition homogene hommes / femmes
				{
				$qh = $wpdb->get_results("SELECT U.ID, U.display_name, U.user_registered, R.i_sex, R.i_zsex, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre, P.t_annonce
					FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
					WHERE U.user_status=0 and R.i_photo!=0 and R.i_sex=0 and R.user_id=P.user_id and R.user_id=U.ID and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".$rencOpt['jlibre']." and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30
					ORDER BY U.user_registered DESC
					LIMIT ".$rencOpt['npa']);
				$qf = $wpdb->get_results("SELECT U.ID, U.display_name, U.user_registered, R.i_sex, R.i_zsex, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre, P.t_annonce
					FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
					WHERE U.user_status=0 and R.i_photo!=0 and R.i_sex=1 and R.user_id=P.user_id and R.user_id=U.ID and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".$rencOpt['jlibre']." and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30
					ORDER BY U.user_registered DESC
					LIMIT ".$rencOpt['npa']);
				reset($qh); reset($qf); $ch=0; $cf=0; $q=array(); $c=0;
				do
					{
					if(mt_rand(0,1) && $cf-$ch<5) // femme
						{
						if($cf==0 && $qf) {$q[]=current($qf); ++$cf; ++$c;}
						else if(next($qf)!==false) {$q[]=current($qf); ++$cf; ++$c;}
						else $ch=-10; // Fin
						}
					else if($ch-$cf<5) // homme
						{
						if($ch==0 && $qh) {$q[]=current($qh); ++$ch; ++$c;}
						else if(next($qh)!==false) {$q[]=current($qh); ++$ch; ++$c;}
						else $cf=-10; // Fin
						}
					}while(($ch+$cf)>-15 && $c<$rencOpt['npa']); // false = stop
				}
			else $q = $wpdb->get_results("SELECT U.ID, U.display_name, U.user_registered, R.i_sex, R.i_zsex, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre, P.t_annonce
					FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
					WHERE U.user_status=0 and R.i_photo!=0 and R.user_id=P.user_id and R.user_id=U.ID and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".$rencOpt['jlibre']." and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30
					ORDER BY U.user_registered DESC
					LIMIT ".$rencOpt['npa']);
			$c = 0;
			if($q) foreach($q as $r)
				{ 
				$a = substr(stripslashes($r->t_annonce),0,180);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $a, $m);
				$m[0] = (isset($m[0])?$m[0]:'');
				$a = str_replace(array($m[0]), array(''), $a);
				$a = str_replace(', ', ',', $a); $a = str_replace(',', ', ', $a);
				$a = strtr($a, "0123456789#(){[]}", ".................");
				$a = substr($a,0,150).'...';
				$b = stripslashes($r->t_titre);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $b, $m);
				$m[0] = (isset($m[0])?$m[0]:'');
				$b = str_replace(array($m[0]), array(''), $b);
				$b = str_replace(', ', ',', $b); $b = str_replace(',', ', ', $b);
				$b = strtr($b, "0123456789#(){[]}", ".................");
				$genre='girl';
				if($r->i_sex==0 && $r->i_zsex==1) $genre='men';
				else if($r->i_sex==1 && $r->i_zsex==1) $genre='gaygirl';
				else if($r->i_sex==0 && $r->i_zsex==0) $genre='gaymen';
				$out.='<div class="rencBox '.$genre.'" style="float:left;width:31.32%;padding:1px;margin:0.5%;max-height:109px;overflow:hidden;">';
				$out.='<div class="miniPortrait miniBox"><a href="wp-login.php?action=register">';
				if ($r->i_photo!=0)
					{
					copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.self::f_img((($r->ID)*10).'-mini').'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-mini.jpg');
					copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.self::f_img((($r->ID)*10).'-libre').'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-libre.jpg');
					$out.='<img id="tete'.$c.'" class="tete" onMouseOver="f_tete_zoom(this,\''.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg\');" onMouseOut="f_tete_normal(this,\''.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-mini.jpg\');" src="'.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-mini.jpg" alt="'.$r->display_name.'" />';
					$out.='<img style="display:none;" src="'.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg" />';
					}
				else $out.='<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$r->display_name.'" />';
				$out.='<div><h3>'.$r->display_name.'</h3>';
				$out.='<div class="monAge">'.Rencontre::f_age($r->d_naissance).'&nbsp;'.__('years','rencontre').'</div>';
				$out.='<div class="maVille">'.$r->c_ville.'</div></div>';
				$out.='<p style="width:100%;">'.$b.'</p>';
				$out.='</a></div><div style="font-size:0.8em;padding:2px 2px 0 4px;">'.$a; 
				$pays = strtr(utf8_decode($r->c_pays), 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
				$pays = strtr($pays, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ ', 'aaaaaaceeeeiiiinooooouuuuyy_');
				$pays = str_replace("'", "", $pays);
				$cpays = str_replace("'", "&#39;", $r->c_pays);
				if($r->c_pays!="") $out.='<img class="flag" style="position:absolute;bottom:5px;right:5px;" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$r->c_pays].'" alt="'.$drapNom[$r->c_pays].'" title="'.$drapNom[$r->c_pays].'" />';
				$out.='</div><div class="clear"></div></div>'."\r\n";
				++$c;
				}
			$out.="\r\n".'<div class="clear">&nbsp;</div></div><!-- #widgRenc -->'."\r\n";
			$t = fopen(plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html', 'w');
			if ($t) { fwrite($t,$out); fclose($t); }
			if(!$ret) echo $out;
			else return $out; // SHORTCODE
			}
		else if($ret) // SHORTCODE
			{
			$out = file_get_contents(plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html');
			return $out; 
			}
		else include(plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html');
		}
	//
	static function f_nbMembre($f=0) // Nombre de membres inscrits sur le site
		{
		global $wpdb;
		if($f=='girl') $nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users WHERE i_sex=1");
		else if($f=='men') $nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users WHERE i_sex=0");
		else if($f=='girlPhoto') $nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users WHERE i_sex=1 and i_photo!=0");
		else if($f=='menPhoto') $nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users WHERE i_sex=0 and i_photo!=0");
		else $nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users");
		return $nm;
		}
	//
	static function f_loginFB() // connexion via Facebook
		{
		if (!is_user_logged_in())
			{
			global $rencOpt; global $rencDiv;
			if (strlen($rencOpt['fblog'])>2)
				{
			?>
			
			<form action="" name="reload"></form>
			<script>
			function checkLoginState(){FB.getLoginStatus(function(r){logfb(r);});}
			function logfb(r){if (r.status==='connected'){FB.api('/me', function(r){jQuery(document).ready(function(){jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',{'action':'fbok','fb':r},function(re){document.forms['reload'].submit();});});});}}
			window.fbAsyncInit=function(){FB.init({appId:'<?php echo $rencOpt['fblog']; ?>',cookie:true,xfbml:true,version:'v2.0'});FB.logout();};
			(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="http://connect.facebook.net/<?php if($rencDiv['lang']) echo $rencDiv['lang']; else echo "fr_FR"; ?>/sdk.js";fjs.parentNode.insertBefore(js,fjs);}(document,'script','facebook-jssdk'));
			</script>
			<?php if (!is_user_logged_in()) echo '<fb:login-button scope="public_profile,email" onlogin="checkLoginState();" data-auto-logout-link="true"></fb:login-button>'; ?>
			<?php
				}
			}
		}
	//
	static function f_img($img,$f=0)
		{
		global $rencOpt;
		$ho = false;
		if(!$f && has_filter('rencImgP', 'f_rencImgP')) $ho = apply_filters('rencImgP',$img);
		if($ho) return $ho;
		if($f || (isset($rencOpt['imcode']) && $rencOpt['imcode']))
			{
			$t = md5($img);
			return substr($t,4,17) . 'z' . substr($t,25,6); // 'z' is used to know if it's encoded or not
			}
		else return $img;
		}
	//
	} // FIN DE LA CLASSE
// *****************************************************************************************
?>