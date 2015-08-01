<?php
/*
Plugin Name: Rencontre
Author: Jacques Malgrange
Plugin URI: http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html
Description: A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. No third party.
Version: 1.7
Author URI: http://www.boiteasite.fr
*/
$rencVersion = '1.7';
// **********************************************************************************
// INSTALLATION DU PLUGIN - Creation des tables en BDD
// **********************************************************************************
register_activation_hook ( __FILE__, 'rencontre_creation_table');
require(dirname(__FILE__).'/inc/rencontre_filter.php');
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
			`user_id` bigint(20) unsigned UNIQUE NOT NULL,
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
			`user_id` bigint(20) unsigned UNIQUE NOT NULL,
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
		global $rencOpt; global $rencDiv; global $wpdb; global $rencCustom;
		load_plugin_textdomain('rencontre', false, dirname(plugin_basename( __FILE__ )).'/lang/'); // language
		$upl = wp_upload_dir();
		$rencOpt = get_option('rencontre_options');
		if(!isset($rencOpt['custom'])) $rencOpt['custom'] = ''; // update V1.7
		$rencDiv['basedir'] = $upl['basedir'];
		$rencDiv['baseurl'] = $upl['baseurl'];
		$rencDiv['blogname'] = get_option('blogname');
		$rencDiv['admin_email'] = get_option('admin_email');
		$rencDiv['siteurl'] = site_url();
		$rencDiv['lang'] = ((defined('WPLANG')&&WPLANG)?WPLANG:get_locale());
		$q = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".substr($rencDiv['lang'],0,2)."' ");
		if(!$q) $rencDiv['lang'] = "en_US";
		if (!$rencOpt)
			{
			$rencOpt = array('facebook'=>'','fblog'=>'','passw'=>0,'home'=>'','pays'=>'','limit'=>20,'tchat'=>0,'map'=>0,'hcron'=>3,'mailmois'=>0,'textmail'=>'','mailsmile'=>0,'mailanniv'=>0,'textanniv'=>'','qmail'=>25,'npa'=>12,'rlibre'=>0,'jlibre'=>3,'prison'=>30,'anniv'=>1,'ligne'=>1,'mailsupp'=>1,'onlyphoto'=>1,'imnb'=>4,'imcrypt'=>0,'imcopyright'=>1,'txtcopyright'=>'','custom'=>'');
			update_option('rencontre_options', $rencOpt);
			}
		if(isset($rencOpt['home']) && strpos($rencOpt['home'],'page_id')!==false) $rencOpt['page_id'] = substr($rencOpt['home'],strpos($rencOpt['home'],'page_id')+8);
		$rencCustom = json_decode($rencOpt['custom'],true);
		$rencOpt['for'][0] = __('Serious relationship','rencontre');
		$rencOpt['for'][1] = __('Open relationship','rencontre');
		$rencOpt['for'][2] = __('Friendship','rencontre');
		if(isset($rencCustom['relation']))
			{
			$c = 0;
			while(isset($rencCustom['relationL'.$c]))
				{
				$rencOpt['for'][$c+3] = $rencCustom['relationL'.$c];
				++$c;
				}
			}
		$rencOpt['iam'][0] = __('a man','rencontre');
		$rencOpt['iam'][1] = __('a woman','rencontre');
		if(isset($rencCustom['sex']))
			{
			$c = 0;
			while(isset($rencCustom['sexL'.$c]))
				{
				$rencOpt['iam'][$c+2] = $rencCustom['sexL'.$c];
				++$c;
				}
			}
		add_action('widgets_init', array($this, 'rencwidget')); // WIDGET
		if(is_admin())
			{
			add_action('admin_menu', array($this, 'admin_menu_link')); // Menu admin
			add_action('admin_print_scripts', array($this, 'adminCSS')); // CSS pour le bouton du menu
			if(file_exists(dirname(__FILE__).'/inc/patch.php')) include(dirname(__FILE__).'/inc/patch.php'); // VERSIONS PATCH - ONLY ONCE
			}
		}
	//
	function admin_menu_link()
		{
		if(is_admin())
			{
			add_menu_page('Rencontre', 'Rencontre', 'manage_options', basename(__FILE__), array(&$this, 'menu_general'), 'div'); // ajoute un menu Rencontre (et son premier sous-menu)
			add_submenu_page('rencontre.php', __('Rencontre - General','rencontre'), __('General','rencontre'), 'manage_options', 'rencontre.php', array(&$this, 'menu_general')); // repete le premier sous-menu (pour changer le nom)
			add_submenu_page('rencontre.php', __('Rencontre - Members','rencontre'), __('Members','rencontre'), 'manage_options', 'rencmembers', array(&$this, 'menu_membres'));
			add_submenu_page('rencontre.php', __('Rencontre - Jail','rencontre'), __('Jail','rencontre'), 'manage_options', 'rencjail', array(&$this, 'menu_prison'));
			add_submenu_page('rencontre.php', __('Rencontre - Profile','rencontre'), __('Profile','rencontre'), 'manage_options', 'rencprofile', array(&$this, 'menu_profil'));
			add_submenu_page('rencontre.php', __('Rencontre - Countries','rencontre'), __('Country','rencontre'), 'manage_options', 'renccountry', array(&$this, 'menu_pays'));
			add_submenu_page('rencontre.php', __('Rencontre - Custom','rencontre'), __('Custom','rencontre'), 'manage_options', 'renccustom', array(&$this, 'menu_custom'));
			}
		}
	//
	function menu_general() {rencMenuGeneral();} // base.php include if is_admin
	function menu_membres() {rencMenuMembres();}
	function menu_prison() {rencMenuPrison();}
	function menu_profil() {rencMenuProfil();}
	function menu_pays() {rencMenuPays();}
	function menu_custom() {rencMenuCustom();}
	//
	function rencwidget()
		{
		global $rencOpt;
		// Reload Unconnected HomePage every...
		$a = plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html';
		$t = time();
		if (isset($rencOpt['rlibre']) && $rencOpt['rlibre'] && file_exists($a) && $t>filemtime($a)+$rencOpt['rlibre']) unlink($a);
		//
		if (is_user_logged_in())
			{
			global $current_user; global $wpdb; global $rencDiv; global $RencMid; global $rencCustom;
			get_currentuserinfo();
			$rol = $current_user->roles;
			$rencMid['id'] = $current_user->ID;
			$rencMid['login'] = $current_user->user_login;
			if (isset($_GET["rencidfm"]))
				{ // acces a la fiche d un membre depuis un lien email
				$_SESSION["rencidfm"] = preg_replace("/[^0-9]+/","",$_GET["rencidfm"]);
				echo "<script language='JavaScript'>document.location.href='".$rencOpt['home']."';</script>"; 
				}
			if(count($rol) && (!isset($_POST['nouveau']) || $_POST['nouveau']!='OK'))
				{
				if(!isset($_POST['nouveau']))
					{
					$q = $wpdb->get_row("SELECT c_ville, i_zsex FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$rencMid['id']."'");
					if($q && $q->i_zsex) $_SESSION['rencontre']='nouveau3';
					else if(($q && $q->c_ville) || (isset($rencCustom['place']) && $q)) $_SESSION['rencontre']='nouveau2';
					else if($q) $_SESSION['rencontre']='nouveau1';
					else $_SESSION['rencontre']='nouveau';
					}
				else
					{
					if($_POST['nouveau']=='1' && isset($rencCustom['place'])) $_SESSION['rencontre']='nouveau2';
					else if($_POST['nouveau']=='1') $_SESSION['rencontre']='nouveau1';
					else if($_POST['nouveau']=='2') $_SESSION['rencontre']='nouveau2';
					else if($_POST['nouveau']=='3') $_SESSION['rencontre']='nouveau3';
					}
				}
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
		if ($naiss==0) return "-";
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
			$out .= '<div id="widgRenc" class="widgRenc ficheLibre">'."\r\n";
			global $wpdb; global $rencOpt; global $rencDiv; global $rencCustom;
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
					WHERE 
						U.user_status=0 
						and R.i_photo!=0 
						and R.i_sex=0 
						and R.user_id=P.user_id 
						and R.user_id=U.ID 
						and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".$rencOpt['jlibre']." 
						and CHAR_LENGTH(P.t_titre)>4 
						and CHAR_LENGTH(P.t_annonce)>30
					ORDER BY U.user_registered DESC
					LIMIT ".$rencOpt['npa']);
				$qf = $wpdb->get_results("SELECT U.ID, U.display_name, U.user_registered, R.i_sex, R.i_zsex, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre, P.t_annonce
					FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						U.user_status=0 
						and R.i_photo!=0 
						and R.i_sex=1 
						and R.user_id=P.user_id 
						and R.user_id=U.ID 
						and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".$rencOpt['jlibre']." 
						and CHAR_LENGTH(P.t_titre)>4 
						and CHAR_LENGTH(P.t_annonce)>30
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
					WHERE 
						U.user_status=0 
						and R.i_photo!=0 
						and R.user_id=P.user_id 
						and R.user_id=U.ID 
						and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".$rencOpt['jlibre']." 
						and CHAR_LENGTH(P.t_titre)>4 
						and CHAR_LENGTH(P.t_annonce)>30
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
				if(isset($rencCustom['librePhoto']))
					{
					$out.='<div class="rencBox photo '.$genre.'">';
					$out.='<a href="'.$rencDiv['siteurl'].'/wp-login.php?action=register"><div class="miniPortrait">';
					if ($r->i_photo!='0')
						{
						@copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.self::f_img((($r->ID)*10).'-libre').'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-libre.jpg');
						$out.='<img src="'.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg" />';
						}
					else $out.='<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" />';
					$out.='</div><div class="clear"></div></a></div>'."\r\n";
					}
				else
					{
					$out.='<div class="rencBox '.(!isset($rencCustom['libreAd'])?'ad ':'').$genre.'">';
					$out.='<a href="'.$rencDiv['siteurl'].'/wp-login.php?action=register"><div class="miniPortrait miniBox">';
					if ($r->i_photo!='0')
						{
						if(!file_exists($rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-mini.jpg')) @copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.self::f_img((($r->ID)*10).'-mini').'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-mini.jpg');
						if(!file_exists($rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-libre.jpg')) @copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.self::f_img((($r->ID)*10).'-libre').'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-libre.jpg');
						$out.='<img id="tete'.$c.'" class="tete" onMouseOver="f_tete_zoom(this,\''.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg\');" onMouseOut="f_tete_normal(this,\''.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-mini.jpg\');" src="'.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-mini.jpg" alt="'.$r->display_name.'" />';
						$out.='<img style="display:none;" src="'.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg" />';
						}
					else $out.='<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$r->display_name.'" />';
					$out.='<div><h3>'.$r->display_name.'</h3>';
					if(!isset($rencCustom['born'])) $out.='<div class="monAge">'.Rencontre::f_age($r->d_naissance).'&nbsp;'.__('years','rencontre').'</div>';
					if(!isset($rencCustom['place'])) $out.='<div class="maVille">'.$r->c_ville.'</div>';
					$out .= '</div><div style="clear:both"></div>';
					if($r->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place']))
						{
						$pays = strtr(utf8_decode($r->c_pays), 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
						$pays = strtr($pays, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ ', 'aaaaaaceeeeiiiinooooouuuuyy_');
						$pays = str_replace("'", "", $pays);
						$cpays = str_replace("'", "&#39;", $r->c_pays);
						$out.='<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$r->c_pays].'" alt="'.$drapNom[$r->c_pays].'" title="'.$drapNom[$r->c_pays].'" />';
						}
					$out.='<p>'.$b.'</p>';
					$out.='</div>';
					if(!isset($rencCustom['libreAd'])) $out.='<div class="rencAd">'.$a.'</div>'; 
					$out .= '<div class="clear"></div></a></div>'."\r\n";
					}
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
	static function f_rencontreSearch($ret=0) // SHORTCODE [rencontre_search]
		{
		global $wpdb; global $rencOpt; global $rencDiv; global $rencCustom;
		$o = "\r\n".'<script>function f_min(f,x,y,z){c=0;d=document.forms[x][y];e=document.forms[x][z];for(v=0;v<e.length;v++){if(d.options[v].value==f)c=v;if(e.options[v].value<=f)e.options[v].disabled=true;else e.options[v].disabled=false;}if(f>e.options[e.selectedIndex].value)e.selectedIndex=c;};function f_max(f,x,y,z){c=0;d=document.forms[x][z];e=document.forms[x][y];for(v=0;v<e.length;v++){if(d.options[v].value==f)c=v;if(e.options[v].value>=f)e.options[v].disabled=true;else e.options[v].disabled=false;}if(f<e.options[e.selectedIndex].value)e.selectedIndex=c;};</script>'."\r\n";
		$o .= '<div id="rencSearchLibre" class="rencSearchLibre"><form name="rencSearch" method="get" action="">'."\r\n";
		if(isset($rencOpt['page_id'])) $o .= '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />';
		$o .= '<input type="hidden" name="page" value="searchLibre" />'."\r\n";
		$o .= '<p class="rencSearchBloc"><label>'.__('I\'m looking for','rencontre').'&nbsp;</label><select name="zsex">';
		for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) $o .= '<option value="'.$v.'">'.$rencOpt['iam'][$v].'</option>';
		$o .= '</select></p>'."\r\n";
		if(!isset($rencCustom['born']))
			{
			$o .= '<p class="rencSearchBloc"><label>'.__('between','rencontre').'&nbsp;</label>';
			$o .= '<select name="zageMin" onChange="f_min(this.options[this.selectedIndex].value,\'rencSearch\',\'zageMin\',\'zageMax\');">';
			for($v=20;$v<91;$v+=5) $o .= '<option value="'.$v.'" '.($v==20?'selected':'').'>'.$v.'&nbsp;'.__('years','rencontre').'</option>';
			$o .= '</select><label>&nbsp;'.__('and','rencontre').'&nbsp;</label>';
			$o .= '<select name="zageMax" onChange="f_max(this.options[this.selectedIndex].value,\'rencSearch\',\'zageMin\',\'zageMax\');">';
			for($v=25;$v<96;$v+=5) $o .= '<option value="'.$v.'" '.($v==95?'selected':'').'>'.$v.'&nbsp;'.__('years','rencontre').'</option>';
			$o .= '</select></p>'."\r\n";
			}
		$o .= '<p class="rencSearchSubmit"><input type="submit" value="'.__('Search','rencontre').'" /></p>'."\r\n";
		$o .= '</form></div>'."\r\n";
		// RESULT
		if(isset($_GET['page']) && $_GET['page']=='searchLibre')
			{
			$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
			$drap=''; $drapNom='';
			foreach($q as $r)
				{
				if($r->c_liste_categ=='d') $drap[$r->c_liste_iso] = $r->c_liste_valeur;
				else if($r->c_liste_categ=='p')$drapNom[$r->c_liste_iso] = $r->c_liste_valeur;
				}
			$o .= '<div id="rencResultLibre" class="rencResultLibre">';
			$ses =date("Y-m-d H:i:s",mktime(0, 0, 0, date("m"), date("d"), date("Y")-2592000)); // 30 days
			$s = "SELECT U.ID, U.display_name, R.i_sex, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre
				FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
				WHERE 
					U.user_status=0 
					and R.user_id=U.ID 
					and R.user_id=P.user_id 
					and R.i_photo!=0 
					and R.i_sex=".$_GET['zsex']."
					and CHAR_LENGTH(P.t_titre)>4 
					and CHAR_LENGTH(P.t_annonce)>30
					and R.d_session>'".$ses."'";
			if(isset($_GET['zageMin']) && $_GET['zageMin']>18)
				{
				$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['zageMin']));
				$s.=" and R.d_naissance<'".$zmin."'";
				}
			if(isset($_GET['zageMax']) && $_GET['zageMax'] && $_GET['zageMax']<99)
				{
				$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['zageMax']));
				$s.=" and R.d_naissance>'".$zmax."'";
				}
			$s .= "ORDER BY CHAR_LENGTH(P.t_action) DESC LIMIT 6";			
			$q = $wpdb->get_results($s);
			$c = 0;
			foreach($q as $r)
				{
				$b = stripslashes($r->t_titre);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $b, $m);
				$m[0] = (isset($m[0])?$m[0]:'');
				$b = str_replace(array($m[0]), array(''), $b);
				$b = str_replace(', ', ',', $b); $b = str_replace(',', ', ', $b);
				$b = strtr($b, "0123456789#(){[]}", ".................");
				if(isset($rencCustom['librePhoto']))
					{
					$o.='<a href="wp-login.php?action=register"><div class="miniPortrait highlight">';
					if ($r->i_photo!=0)
						{
						copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.self::f_img((($r->ID)*10).'-libre').'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($r->ID*10).'-libre.jpg');
						$o.='<img src="'.$rencDiv['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg" />';
						}
					else $o.='<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" />';
					$o.='</div></a>'."\r\n";
					}
				else
					{
					$o .= '<a href="wp-login.php?action=register"><div class="miniPortrait highlight miniBox">';
					$o .= '<img id="tete'.$c.'" class="tete" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($r->ID)/1000).'/'.self::f_img((($r->ID)*10).'-mini').'.jpg" alt="'.$r->display_name.'" />';
					$o .= '<div><h3>'.$r->display_name.'</h3>';
					if(!isset($rencCustom['born'])) $o .= '<div class="monAge">'.Rencontre::f_age($r->d_naissance).'&nbsp;'.__('years','rencontre').'</div>';
					if(!isset($rencCustom['place'])) $o .= '<div class="maVille">'.$r->c_ville.'</div>';
					$o .= '</div><div style="clear:both"></div>';
					if($r->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place']))
						{
						$pays = strtr(utf8_decode($r->c_pays), 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
						$pays = strtr($pays, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ ', 'aaaaaaceeeeiiiinooooouuuuyy_');
						$pays = str_replace("'", "", $pays);
						$cpays = str_replace("'", "&#39;", $r->c_pays);
						$o .= '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$r->c_pays].'" alt="'.$drapNom[$r->c_pays].'" title="'.$drapNom[$r->c_pays].'" />';
						}
					$o .='<p>'.$b.'</p>';
					$o .= '</div></a>'."\r\n";
					}
				}

			$o .= '<div style="clear:both;"></div></div>'."\r\n";
			}
		if(!$ret) echo $o;
		else return $o; // SHORTCODE
		}
	//
	static function f_nbMembre($f='all') // Nombre de membres inscrits sur le site
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
	static function f_login($fb=false,$ret=false) // SHORTCODE [rencontre_login]
		{
		global $rencOpt;
		$o = '<div id="log">'."\r\n";
		if($fb=='fb') $o .= Rencontre::f_loginFB(1);
		$o .= wp_loginout(esc_url(home_url('?page_id='.(isset($rencOpt['page_id'])?$rencOpt['page_id']:''))),false)."\r\n";
		if(!is_user_logged_in()) $o .= '<a href="'.esc_url(home_url('wp-login.php?action=register')).'">'.__('Register').'</a>'."\r\n";
		$o .= '</div><!-- #log -->'."\r\n";
		if(!$ret) echo $o;
		else return $o; // SHORTCODE
		}
	//
	static function f_loginFB($ret=false) // connexion via Facebook
		{
		if (!is_user_logged_in())
			{
			global $rencOpt; global $rencDiv;
			if (strlen($rencOpt['fblog'])>2 ||1)
				{
				$o = '';
				$o .= '<form action="" name="reload"></form>'."\r\n";
				$o .= '<script>
function checkLoginState(){FB.getLoginStatus(function(r){logfb(r);});}
function logfb(r){if (r.status===\'connected\'){FB.api(\'/me\', function(r){jQuery(document).ready(function(){jQuery.post(\''.admin_url('admin-ajax.php').'\',{\'action\':\'fbok\',\'fb\':r},function(re){document.forms[\'reload\'].submit();});});});}}
window.fbAsyncInit=function(){FB.init({appId:\''.$rencOpt['fblog'].'\',cookie:true,xfbml:true,version:\'v2.0\'});FB.logout();};
(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="http://connect.facebook.net/'.($rencDiv['lang']?$rencDiv['lang']:"fr_FR").'/sdk.js";fjs.parentNode.insertBefore(js,fjs);}(document,\'script\',\'facebook-jssdk\'));
</script>'."\r\n";
				$o .= '<fb:login-button scope="public_profile,email" onlogin="checkLoginState();" data-auto-logout-link="true"></fb:login-button>'."\r\n";
				if(!$ret) echo $o;
				else return $o;
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
	} // END CLASS
// *****************************************************************************************
?>