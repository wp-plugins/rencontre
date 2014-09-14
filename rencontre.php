<?php
/*
Plugin Name: Rencontre
Author: Jacques Malgrange
Plugin URI: http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html
Description: A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. No third party.
Version: 1.2
Author URI: http://www.boiteasite.fr
*/

// **********************************************************************************
// INSTALLATION DU PLUGIN - Creation des tables en BDD
// **********************************************************************************
register_activation_hook ( __FILE__, 'creation_table');
require('inc/rencontre_filter.php' );
		// **** PATCH V1.2 : langue pour les pays *****************************************
			add_action('admin_notices','patch12');
			function patch12()
				{
				global $wpdb;
				$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
				if(!$n) echo '<div class="update-nag"><p>Plugin <strong>Rencontre</strong> - Patch V1.2 : '.__('Vous devez re-installer les pays','rencontre').'</p></div>';
				$n = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='pays' ");
				if($n) echo '<div class="update-nag"><p>Plugin <strong>Rencontre</strong> - Patch V1.2 : '.__('Vous devez d&eacute;sactiver puis r&eacute;activer le plugin','rencontre').'</p></div>';
				}
		// ************************************************************************************
function creation_table()
	{
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // pour utiliser dbDelta()
	global $wpdb;
	//
	if(!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if(!empty($wpdb->collate)) $charset_collate .= " COLLATE $wpdb->collate";
	$nom = $wpdb->prefix . 'rencontre_profil';
	if($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'")!=$nom)
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
	if($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`user_id` bigint(20) unsigned NOT NULL,
			`c_ip` varchar(50) NOT NULL,
			`c_pays` varchar(50) NOT NULL,
			`c_region` varchar(50) NOT NULL,
			`c_ville` varchar(50) NOT NULL,
			`i_sex` tinyint NOT NULL,
			`d_naissance` date NOT NULL,
			`i_taille` tinyint unsigned NOT NULL,
			`i_poids` tinyint unsigned NOT NULL,
			`i_zsex` tinyint NOT NULL,
			`i_zage_min` tinyint unsigned NOT NULL,
			`i_zage_max` tinyint unsigned NOT NULL,
			`i_zrelation` tinyint NOT NULL,
			`i_photo` bigint(20) unsigned NOT NULL,
			FOREIGN KEY (`user_id`) REFERENCES ".$wpdb->prefix . "users(`ID`) ON DELETE CASCADE
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_users_profil';
	if($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'")!=$nom)
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
	if($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'")!=$nom)
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
		// **** PATCH V1.2 : langue pour les pays *****************************************
			$sql = $wpdb->get_var('SELECT c_liste_categ FROM '.$wpdb->prefix.'rencontre_liste WHERE c_liste_categ="Pays"');
			if($sql) // update
				{
				$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."rencontre_liste");
				$sql1 = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'rencontre_liste');
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste DROP COLUMN i_liste_lien ");
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_iso` varchar(2) NOT NULL");
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_lang` varchar(2) NOT NULL");
				}
		// ************************************************************************************
	$nom = $wpdb->prefix . 'rencontre_msg';
	if($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'")!=$nom)
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
	if($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'")!=$nom)
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
require(dirname (__FILE__) . '/inc/base.php');
new Rencontre();
class Rencontre
	{
	var $options = array();
	private $class_name = 'rencontre';
	private $width      = '100%';
	private $height     = '200px';
	private $version = '1.2';
	function __construct()
		{
		$this->get_options();
		load_plugin_textdomain('rencontre', false, dirname(plugin_basename( __FILE__ )).'/lang/'); // language
		add_action('admin_menu', array(&$this, 'admin_menu_link')); // Menu admin
		add_action('widgets_init', array(&$this, 'widget')); // WIDGET
		add_action('admin_print_scripts', array($this, 'adminCSS')); // CSS pour le bouton du menu
		}
	//
	function get_options()
		{
		if (!$options = get_option('rencontre_options'))
			{
			$options = array('facebook'=>'','fblog'=>'','home'=>'','limit'=>20,'tchat'=>0,'hcron'=>4,'mailmois'=>0,'textmail'=>'','mailanniv'=>0,'textanniv'=>'','qmail'=>25,'npa'=>12,'jlibre'=>3,'anniv'=>1,'ligne'=>1,'mailsupp'=>1,'onlyphoto'=>1,'imcopyright'=>1);
			update_option('rencontre_options', $options);
			}
		$this->options = get_option('rencontre_options'); 
		}
	//
	function admin_menu_link()
		{
		add_menu_page('Rencontre', 'Rencontre', 'manage_options', basename(__FILE__), array(&$this, 'menu_general'), 'div'); // ajoute un menu Rencontre (et son premier sous-menu)
		add_submenu_page('rencontre.php', __('Rencontre - G&eacute;n&eacute;ral','rencontre'), __('G&eacute;n&eacute;ral','rencontre'), 'manage_options', 'rencontre.php', array(&$this, 'menu_general') ); // repete le premier sous-menu (pour changer le nom)
		add_submenu_page('rencontre.php', __('Rencontre - Membres','rencontre'), __('Membres','rencontre'), 'manage_options', 'membres', array(&$this, 'menu_membres') );
		add_submenu_page('rencontre.php', __('Rencontre - Prison','rencontre'), __('Prison','rencontre'), 'manage_options', 'prison', array(&$this, 'menu_prison') );
		add_submenu_page('rencontre.php', __('Rencontre - Profil','rencontre'), __('Profil','rencontre'), 'manage_options', 'profil', array(&$this, 'menu_profil') );
		add_submenu_page('rencontre.php', __('Rencontre - Pays','rencontre'), __('Pays','rencontre'), 'manage_options', 'pays', array(&$this, 'menu_pays') );
		}
	//
	function update_rencontre_options($f)
		{
		$options = get_option('rencontre_options');
		if ($f['facebook']) $options['facebook'] = stripslashes($f['facebook']);
		if ($f['fblog']) $options['fblog'] = $f['fblog'];
		if ($f['home']) $options['home'] = $f['home']; else $options['home'] = "";
		if ($f['limit']) $options['limit'] = $f['limit']; else $options['limit'] = 20;
		if ($f['jlibre']) $options['jlibre'] = $f['jlibre']; else $options['jlibre'] = 0;
		if ($f['prison']) $options['prison'] = $f['prison'];
		if ($f['tchat']) $options['tchat'] = 1; else $options['tchat'] = 0;
		if ($f['hcron']) $options['hcron'] = $f['hcron'];
		if ($f['mailmois']) $options['mailmois'] = 1; else $options['mailmois'] = 0;
		if ($f['textmail']) $options['textmail'] = $f['textmail'];
		if ($f['mailanniv']) $options['mailanniv'] = 1; else $options['mailanniv'] = 0;
		if ($f['textanniv']) $options['textanniv'] = $f['textanniv'];
		if ($f['qmail']) $options['qmail'] = $f['qmail'];
		if ($f['npa']) $options['npa'] = $f['npa'];
		if ($f['imcopyright']) $options['imcopyright'] = 1; else $options['imcopyright'] = 0;
		if ($f['anniv']) $options['anniv'] = 1; else $options['anniv'] = 0;
		if ($f['ligne']) $options['ligne'] = 1; else $options['ligne'] = 0;
		if ($f['mailsupp']) $options['mailsupp'] = 1; else $options['mailsupp'] = 0;
		if ($f['onlyphoto']) $options['onlyphoto'] = 1; else $options['onlyphoto'] = 0;
		update_option('rencontre_options',$options);
		}
	//
	function menu_general()
		{
		if ($_POST['facebook'] || $_POST['npa']) { Rencontre::update_rencontre_options($_POST); }
		$options = get_option('rencontre_options');
		?>
		<div class='wrap'>
			<div class='icon32' id='icon-options-general'><br/></div>
			<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $this->version; ?></span></h2>
			<h2><?php _e('G&eacute;n&eacute;ral', 'rencontre'); ?></h2>
			<form method="post" name="rencontre_options" action="admin.php?page=rencontre.php">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label><?php _e('Framework pour le bouton J\'aime de Facebook', 'rencontre'); ?></label></th>
						<td><textarea  name="facebook"><?php echo $options['facebook']; ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('AppID pour connexion par Facebook (vide si pas install&eacute;)', 'rencontre'); ?></label></th>
						<td><input type="text" class="regular-text" name="fblog" value="<?php echo $options['fblog']; ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label><?php _e('Page o&ugrave; est install&eacute; le plugin', 'rencontre'); ?></label></th>
						<td>
							<select name="home">
								<option value="" <?php echo ($options['home']?'':'selected'); ?>>Index</option>
								<?php $pages = get_pages(); $tmp = '';
								foreach($pages as $page) { $tmp .= '<option value="'.get_page_link($page->ID).'" '.($options['home']==get_page_link($page->ID)?'selected':'').'>'.$page->post_title.'</option>'; }
								echo $tmp; ?>

							</select>
						</td>
					</tr>
				
					<tr valign="top">
						<th scope="row"><label><?php _e('Nombre de portrait en page d\'accueil non connect&eacute;', 'rencontre'); ?></label></th>
						<td><input type="text" class="regular-text" name="npa" value="<?php echo $options['npa']; ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Nombre de jours d\'attente avant pr&eacute;sence en page d\'accueil', 'rencontre'); ?></label></th>
						<td><input type="text" class="regular-text" name="jlibre" value="<?php echo $options['jlibre']; ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Dur&eacute;e de la prison en jour (compte supprim&eacute;)', 'rencontre'); ?></label></th>
						<td><input type="text" class="regular-text" name="prison" value="<?php echo $options['prison']; ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Nombre max de r&eacute;sultats par recherche', 'rencontre'); ?></label></th>
						<td><input type="text" class="regular-text" name="limit" value="<?php echo $options['limit']; ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Anniversaires du jour', 'rencontre'); ?></label></th>
						<td><input type="checkbox" name="anniv" value="1" <?php if ($options['anniv'])echo 'checked'; ?>></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Profils actuellement en ligne', 'rencontre'); ?></label></th>
						<td><input type="checkbox" name="ligne" value="1" <?php if ($options['ligne'])echo 'checked'; ?>></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Activer le chat', 'rencontre'); ?></label></th>
						<td><input type="checkbox" name="tchat" value="1" <?php if ($options['tchat'])echo 'checked'; ?>></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Afficher un copyright discret sur les photos', 'rencontre'); ?></label></th>
						<td><input type="checkbox" name="imcopyright" value="1" <?php if ($options['imcopyright'])echo 'checked'; ?>></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Membres sans photo moins visibles', 'rencontre'); ?></label></th>
						<td><input type="checkbox" name="onlyphoto" value="1" <?php if ($options['onlyphoto'])echo 'checked'; ?>></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Envoyer un mail &agrave; l\'utilisateur dont le compte est supprim&eacute;', 'rencontre'); ?></label></th>
						<td><input type="checkbox" name="mailsupp" value="1" <?php if ($options['mailsupp'])echo 'checked'; ?>></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Envoi automatique d\'un mail mensuel de synth&egrave;se aux membres (r&eacute;parti sur le mois)', 'rencontre'); ?></label></th>
						<td>
							<input type="checkbox" name="mailmois" value="1" <?php if ($options['mailmois'])echo 'checked'; ?>>
							<?php 
							$d2 = dirname(__FILE__).'/inc/rencontre_cron.txt';
							if (file_exists($d2)) echo "<p style='color:#D54E21;'>".__('Maximum cette semaine', 'rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".file_get_contents($d2)."</span>&nbsp;".__('mail/j', 'rencontre')."<br />(".__('envoy&eacute;s lors de la maintenance', 'rencontre').")</p>";
							?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Heure des t&acirc;ches de maintenance (heures creuses)', 'rencontre'); ?></label></th>
						<td>
							<select name="hcron">
								<?php for ($v=0;$v<24;++$v) {echo '<option value="'.$v.'" '.(($options['hcron']==$v)?'selected':'').'>&nbsp;'.$v.__('heures','rencontre').'</option>';} ?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Texte d\'introduction pour le mail mensuel (Apr&egrave;s bonjour login - Avant les sourires et demandes de contact)', 'rencontre'); ?></label></th>
						<td><textarea name="textmail"><?php echo stripslashes($options['textmail']); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Envoi automatique d\'un mail de bon anniversaire aux membres', 'rencontre'); ?></label></th>
						<td><input type="checkbox" name="mailanniv" value="1" <?php if ($options['mailanniv'])echo 'checked'; ?>></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Texte complet pour le mail de bon anniversaire (Apr&egrave;s bonjour login)', 'rencontre'); ?></label></th>
						<td><textarea name="textanniv"><?php echo stripslashes($options['textanniv']); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Nombre max de mail envoy&eacute;s par heure', 'rencontre'); ?></label></th>
						<td>
							<input type="text" class="regular-text" name="qmail" value="<?php echo $options['qmail']; ?>" />
							<?php 
							$d2 = dirname(__FILE__).'/inc/rencontre_cronListe.txt';
							if (file_exists($d2)) echo "<p style='color:#D54E21;'>".__('Maximum cette semaine', 'rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".file_get_contents($d2)."</span>&nbsp;".__('mail/h', 'rencontre')."<br />(".__('hors p&eacute;riode de maintenance', 'rencontre').")</p>";
							?>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Sauvegarde','rencontre') ?>" />
				</p>
			</form>
		</div>
		<?php
		}
	function menu_membres()
		{
		wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
		wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
		require(dirname (__FILE__) . '/lang/rencontre-js-admin-lang.php');
		wp_localize_script('rencontre', 'rencobjet', $lang);
		$options = get_option('rencontre_options');
		$upl = wp_upload_dir();
		$langue = ((WPLANG)?WPLANG:get_locale());
		global $wpdb;
		$q = $wpdb->get_results("SELECT c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' ");
		$drap = ''; $drapNom ='';
		foreach($q as $r)
			{
			$drap[$r->c_liste_iso] = $r->c_liste_valeur;
			$drapNom[$r->c_liste_iso] = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_iso='".$r->c_liste_iso."' and c_liste_lang='".substr($langue,0,2)."' ");
			}
		?>
		<div class='wrap'>
			<div class='icon32' id='icon-options-general'><br/></div>
			<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $this->version; ?></span></h2>
			<h2><?php _e('Membres', 'rencontre'); ?></h2>
			<?php 
			$nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users");
			$np = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=P.user_id AND R.i_photo>0 AND CHAR_LENGTH(P.t_titre)>4 AND CHAR_LENGTH(P.t_annonce)>30");
			echo "<p style='color:#D54E21;'>".__('Nombre de membres inscrits','rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".$nm."</span></p>";
			echo "<p style='color:#D54E21;'>".__('Nombre de membres avec profil et photo','rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".$np."</span></p>";
			//get_option('admin_email'); // mail admin (options general)
			if (!$_GET["id"]) {
			if ($_POST["a1"] && $_POST["a2"]) 
				{
				f_userSupp($_POST["a1"],$_POST["a2"],1);
				if ($options['mailsupp'])
					{
					$q = $wpdb->get_var("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID='".$_POST["a1"]."'");
					$objet  = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES).' - '.__('Suppression du compte','rencontre');
					$message  = __('Votre compte a &eacute;t&eacute; supprim&eacute;','rencontre');
					@wp_mail($q, $objet, $message);
					}
				}
			$tri="";
				if (isset($_GET['tri']))
					{
					if ($_GET['tri']=='id') $tri='ORDER BY R.user_id ASC';
					else if ($_GET['tri']=='Rid') $tri='ORDER BY R.user_id DESC';
					else if ($_GET['tri']=='pseudo') $tri='ORDER BY U.user_login ASC';
					else if ($_GET['tri']=='Rpseudo') $tri='ORDER BY U.user_login DESC';
					else if ($_GET['tri']=='age') $tri='ORDER BY R.d_naissance DESC';
					else if ($_GET['tri']=='Rage') $tri='ORDER BY R.d_naissance ASC';
					else if ($_GET['tri']=='pays') $tri='ORDER BY R.c_pays ASC';
					else if ($_GET['tri']=='Rpays') $tri='ORDER BY R.c_pays DESC';
					else if ($_GET['tri']=='modif') $tri='ORDER BY P.d_modif ASC';
					else if ($_GET['tri']=='Rmodif') $tri='ORDER BY P.d_modif DESC';
					else if ($_GET['tri']=='ip') $tri='ORDER BY R.c_ip ASC';
					else if ($_GET['tri']=='Rip') $tri='ORDER BY R.c_ip DESC';
					else if ($_GET['tri']=='signal') $tri='ORDER BY length(P.t_signal) DESC';
					}
				else $tri='ORDER BY P.d_modif DESC';
				$pagenum = isset($_GET['pagenum'])?absint($_GET['pagenum']):1;
				$limit = 100;
				$q = $wpdb->get_results("SELECT U.ID, U.user_login, R.c_ip, R.c_pays, R.c_region, R.c_ville, R.d_naissance, R.i_taille, R.i_poids, R.i_sex, R.i_zage_min, R.i_zage_max, R.i_zrelation, R.i_photo, P.d_modif, P.t_titre, P.t_annonce
					FROM ".$wpdb->prefix . "users U, ".$wpdb->prefix . "rencontre_users R, ".$wpdb->prefix . "rencontre_users_profil P 
					WHERE R.user_id=P.user_id and R.user_id=U.ID ".$tri." 
					LIMIT ".(($pagenum-1)*$limit).",".$limit);
				$total = $wpdb->get_var("SELECT COUNT(user_id) FROM ".$wpdb->prefix . "rencontre_users");
				$page_links = paginate_links(array('base'=>add_query_arg('pagenum','%#%'),'format'=>'','prev_text'=>'&laquo;','next_text'=>'&raquo;','total'=>ceil($total/$limit),'current'=>$pagenum,'mid_size'=>5));
				if ($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
			?>
			<form name='listUser' method='post' action=''><input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' />
			<table class="membre">
				<tr>
					<td><a href="admin.php?page=membres&tri=<?php if ($_GET['tri']=='id') echo 'R'; ?>id" title="<?php _e('Trier','rencontre'); ?>">ID</a></td>
					<td><?php _e('Photo','rencontre');?></td>
					<td><a href="admin.php?page=membres&tri=<?php if ($_GET['tri']=='pseudo') echo 'R'; ?>pseudo" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Pseudo','rencontre');?></a></td>
					<td><?php _e('Sex','rencontre');?></td>
					<td><a href="admin.php?page=membres&tri=<?php if ($_GET['tri']=='age') echo 'R'; ?>age" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Age','rencontre');?><a></td>
					<td><?php _e('Taille','rencontre');?></td>
					<td><?php _e('Poids','rencontre');?></td>
					<td><?php _e('Recherche','rencontre');?></td>
					<td><?php _e('Accroche','rencontre');?></td>
					<td><a href="admin.php?page=membres&tri=<?php if ($_GET['tri']=='pays') echo 'R'; ?>pays" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Pays','rencontre');?></a></td>
					<td><a href="admin.php?page=membres&tri=<?php if ($_GET['tri']=='modif') echo 'R'; ?>modif" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Annonce (modif)','rencontre');?></a></td>
					<td><a href="admin.php?page=membres&tri=<?php if ($_GET['tri']=='ip') echo 'R'; ?>ip" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Adresse IP','rencontre');?></a></td>
					<td><a href="admin.php?page=membres&tri=signal" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Signalement','rencontre');?></a></td>
					<td><?php _e('Supp','rencontre');?></td>
				</tr>
			<?php
			$data = wp_parse_args($data, $defaut);
			$categ="";
			foreach($q as $s)
				{
				$q = $wpdb->get_var("SELECT t_signal FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$s->ID."'"); $signal=json_decode($q,true);
				echo '<tr>';
				echo '<td><a href="admin.php?page=membres&id='.$s->ID.'" title="'.__('Voir','rencontre').'">'.$s->ID.'</a></td>';
				echo '<td><a href="admin.php?page=membres&id='.$s->ID.'" title="'.__('Voir','rencontre').'"><img class="tete" src="'.($s->i_photo!=0?get_bloginfo('url').'/wp-content/uploads/portrait/'.floor(($s->ID)/1000).'/'.(($s->ID)*10).'-mini.jpg" alt="" /></a></td>':plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" /></td>');
				echo '<td>'.$s->user_login.'</td>';
				echo '<td>'.(($s->i_sex==0)?__('Homme','rencontre').'</td>':__('Femme','rencontre').'</td>');
				echo '<td>'.$this->f_age($s->d_naissance).'</td>';
				echo '<td>'.$s->i_taille.' cm</td>';
				echo '<td>'.$s->i_poids.' kg</td>';
				if ($s->i_zrelation==0) echo '<td>'.__('Relation s&eacute;rieuse','rencontre'); elseif ($s->i_zrelation==1) echo '<td>'.__('Relation libre','rencontre'); elseif ($s->i_zrelation==2) echo '<td>'.__('Amiti&eacute;','rencontre');
				else echo '<td>'.$s->i_zrelation;
				echo '<br />'.$s->i_zage_min.' '. __('&agrave;','rencontre').' '.$s->i_zage_max.'</td>';
				echo '<td>'.$s->t_titre.'</td>';
				if(isset($drapNom[$s->c_pays]) && $s->c_pays!="") echo '<td><img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />';
				else echo '<td>'.$s->c_pays;
				echo '<br />'.$s->c_region.'<br />'.$s->c_ville.'</td>';
				echo '<td>'.$s->d_modif.'</td>';
				if (function_exists('geoip_detect_get_info_from_ip')) // PLUGIN GEOIP-DETECT
					{
					$geoip = geoip_detect_get_info_from_ip($s->c_ip);
					$ipays = $drap[$geoip->country_code];
					}
				else $ipays=null;
				echo '<td>'.$s->c_ip.(($ipays)?'<br/><img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$ipays.'" alt="'.$geoip->country_name.'" title="'.$geoip->country_name.'" />':'').'</td>';
				echo '<td>'.((count($signal))?count($signal):'').'</td>';
				echo '<td><a href="javascript:void(0)" class="rencSupp" onClick="f_fin('.$s->ID.',\''.$s->user_login.'\')" title="'.__('Supprimer','rencontre').'"></a></td>';
				echo '</tr>';
				}
			?>
			</table>
			</form>
			<?php } 
			else {
			$id = $_GET["id"];
			$q = $wpdb->get_results("SELECT P.id, P.c_categ, P.c_label, P.t_valeur, P.i_type FROM ".$wpdb->prefix."rencontre_profil P WHERE P.c_lang='".substr(WPLANG,0,2)."' ORDER BY P.c_categ");
			$in = '';
			foreach ($q as $r)
				{
				$in[$r->id][0] = $r->i_type;
				$in[$r->id][1] = $r->c_categ;
				$in[$r->id][2] = $r->c_label;
				$in[$r->id][3] = $r->t_valeur;
				$c++;
				}
			if (!($_SESSION['a1']==$_POST["a1"] && $_SESSION['a2']==$_POST["a2"]))
				{
				if ($_POST["a1"]=="suppImg") RencontreWidget::suppImg($_POST["a2"],$id,$upl);
				if ($_POST["a1"]=="plusImg") RencontreWidget::plusImg($_POST["a2"],$id,$upl);
				}
			if ($_POST["a1"]=="sauvProfil") RencontreWidget::sauvProfil($in,$id); 
			$_SESSION['a1'] = $_POST["a1"]; $_SESSION['a2'] = $_POST["a2"];
			$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_ville, R.i_photo, P.t_titre, P.t_annonce, P.t_profil FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$id." and R.user_id=P.user_id and R.user_id=U.ID");
			echo $before_widget ."\n"; ?>
			<h3><?php _e('Modifier un profil','rencontre');?></h3>
			<div class="bouton"><a href="javascript:void(0)" onclick="javascript:history.back();"><?php _e('Page pr&eacute;c&eacute;dente','rencontre');?></a></div>
			<div class="bouton"><a href="<?php echo admin_url(); ?>admin.php?page=membres"><?php _e('Retour Membres','rencontre');?></a></div>
			<div class="rencPortrait">
				<form name='portraitChange' method='post' enctype="multipart/form-data" action=''>
					<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='page' value='' />
					<div id="portraitSauv"><span onClick="f_sauv_profil(<?php echo $mid; ?>)"><?php _e('Sauvegarde du profil','rencontre');?></span></div>
					<div class="petiteBox portraitPhoto left">
						<div class="rencBox">
							<div id="changePhoto"></div>
							<img id="portraitGrande" src="<?php if(($s->i_photo)!=0) echo $upl['baseurl'].'/portrait/'.floor($id/1000).'/'.($id*10).'-grande.jpg?r='.rand(); else echo plugins_url('rencontre/images').'/no-photo600.jpg'; ?>" width=250 height=250 alt="" />
							<div>
							<?php for ($v=0;$v<4;++$v)
								{
								if ($s->i_photo>=$id*10+$v)
									{
									echo '<a href="javascript:void(0)" onClick="f_supp_photo('.($id*10+$v).')"><img onMouseOver="f_vignette_change('.($id*10+$v).')" class="portraitMini" src="'.$upl['baseurl'].'/portrait/'.floor($id/1000).'/'.($id*10+$v).'-mini.jpg?r='.rand().'" alt="'.__('Cliquer pour supprimer','rencontre').'" title="'.__('Cliquer pour supprimer','rencontre').'" /></a>'."\n";
									echo '<img style="display:none;" src="'.$upl['baseurl'].'/portrait/'.floor($id/1000).'/'.($id*10+$v).'-grande.jpg?r='.rand().'" />'."\n";
									}
								else { ?><a href="javascript:void(0)" onClick="f_plus_photo(<?php echo $s->i_photo; ?>)"><img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php _e('Cliquer pour ajouter une photo','rencontre'); ?>" title="<?php _e('Cliquer pour ajouter une photo','rencontre'); ?>" /></a>
								<?php } } ?>
							</div>
						</div>
					</div>
					<div class="grandeBox right">
						<div class="rencBox">
							<?php
							if($s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />'; ?>

							<div class="grid_10">
								<h3><?php echo $s->display_name; ?></h3>
								<div class="ville"><?php echo $s->c_ville; ?></div>
								<label><?php _e('Mon accroche','rencontre');?></label><br />
								<input type="text" name="titre" value="<?php echo $s->t_titre; ?>" /><br /><br />
								<label><?php _e('Mon annonce','rencontre');?></label><br />
								<textarea name="annonce" rows="10" style="width:95%;"><?php echo $s->t_annonce; ?></textarea>
							</div>
						</div>
					</div>
					<div class="pleineBox portraitProfil clear">
						<div class="rencBox">
							<div class="br"></div>
						<?php
						$profil = json_decode($s->t_profil,true);
						$out = '';
						if ($profil) foreach ($profil as $r)
							{
							$out[$r['i']] = $r['v'];
							}
						$out1="";$out2=""; $c=0; $d="";
						foreach ($in as $r=>$r1)
							{
							if ($d!=$r1[1]) // nouvel onglet
								{
								if ($d!="") $out2.='</table>'."\n";
								$out1.='<span class="portraitOnglet" id="portraitOnglet'.$c.'" '.(($c==0)?'style="background-color:#e5d4ac;" ':'').' onclick="javascript:f_onglet('.$c.');">'.$r1[1].'</span>'."\n";
								$out2.='<table '.(($c==0)?'style="display:table;" ':'').'id="portraitTable'.$c.'" border="0">'."\n";
								++$c;
								}
							switch ($r1[0])
								{
								case 1: $out2.='<tr><td>'.$r1[2].'</td><td><input type="text" name="text'.$r.'" value="'.$out[$r].'" /></td></tr>'."\n"; break;
								case 2: $out2.='<tr><td>'.$r1[2].'</td><td><textarea name="area'.$r.'" rows="4" cols="50">'.$out[$r].'</textarea></td></tr>'."\n"; break;
								case 3: $out2.='<tr><td>'.$r1[2].'</td><td><select name="select'.$r.'"><option value="0">&nbsp;</option>'; $list = json_decode($r1[3]); $c1=0;
									foreach ($list as $r2) { $out2.='<option value="'.($c1+1).'"'.(($c1===$out[$r])?' selected':'').'>'.$r2.'</option>'; ++$c1;}$out2.='</select></td></tr>'."\n"; break;
								case 4: $out2.='<tr><td>'.$r1[2].'</td><td>'; $list = json_decode($r1[3]); $c1=0; if ($out[$r]) $c3=" ".implode(" ",$out[$r])." "; else $c3="";
									foreach ($list as $r2) { $out2.=$r2.' : <input type="checkbox" name="check'.$r.'[]" value="'.$c1.'" '.((strstr($c3, " ".$c1." ")!=false)?'checked':'').' />'; ++$c1;}$out2.='</td></tr>'."\n"; break;
								}
							$d=$r1[1];
							}
						$out2.='</table>'."\n";
						echo $out1.$out2;
						?>
						
							<em id="infoChange"><?php if ($_POST["a1"]=="sauvProfil") _e('Effectu&eacute;e','rencontre'); ?>&nbsp;</em>
						</div>
					</div>
				</form>
			</div>
			
			<?php } ?>
		</div>
		<?php
		if ($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
		}
	//
	function menu_prison()
		{
		wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
		wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
		require(dirname (__FILE__) . '/lang/rencontre-js-admin-lang.php');
		wp_localize_script('rencontre', 'rencobjet', $lang);
		$options = get_option('rencontre_options');
		$upl = wp_upload_dir(); 
		global $wpdb;
		?>
		<div class='wrap'>
			<div class='icon32' id='icon-options-general'><br/></div>
			<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $this->version; ?></span></h2>
			<h2><?php _e('Prison', 'rencontre'); ?></h2>
			<?php 
			if ($_POST["a1"]) 
				{
				f_userPrison($_POST["a1"]);
				}
			$tri="";
				if (isset($_GET['tri']))
					{
					if ($_GET['tri']=='date') $tri='ORDER BY Q.d_prison ASC';
					else if ($_GET['tri']=='Rdate') $tri='ORDER BY Q.d_prison DESC';
					else if ($_GET['tri']=='mail') $tri='ORDER BY Q.c_mail ASC';
					else if ($_GET['tri']=='Rmail') $tri='ORDER BY Q.c_mail DESC';
					else if ($_GET['tri']=='ip') $tri='ORDER BY Q.c_ip ASC';
					else if ($_GET['tri']=='Rip') $tri='ORDER BY R.d_naissance DESC';
					}
				$pagenum = isset($_GET['pagenum'])?absint($_GET['pagenum']):1;
				$limit = 100;
				$q = $wpdb->get_results("SELECT Q.id, Q.d_prison, Q.c_mail, Q.c_ip FROM ".$wpdb->prefix."rencontre_prison Q ".$tri." LIMIT ".(($pagenum-1)*$limit).",".$limit);
				$total = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix . "rencontre_prison");
				$page_links = paginate_links(array('base'=>add_query_arg('pagenum','%#%'),'format'=>'','prev_text'=>'&laquo;','next_text'=>'&raquo;','total'=>ceil($total/$limit),'current'=>$pagenum,'mid_size'=>5));
				if ($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
			?>
			<form name='listPrison' method='post' action=''><input type='hidden' name='a1' value='' />
			<table class="prison">
				<tr>
					<td><a href="admin.php?page=prison&tri=<?php if ($_GET['tri']=='date') echo 'R'; ?>date" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Date','rencontre');?></a></td>
					<td><a href="admin.php?page=prison&tri=<?php if ($_GET['tri']=='mail') echo 'R'; ?>mail" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Adresse mail','rencontre');?></a></td>
					<td><a href="admin.php?page=prison&tri=<?php if ($_GET['tri']=='ip') echo 'R'; ?>ip" title="<?php _e('Trier','rencontre'); ?>"><?php _e('Adresse IP','rencontre');?><a></td>
					<td><?php _e('Fin','rencontre');?></td>
				</tr>
			<?php
			$data = wp_parse_args($data, $defaut);
			$categ="";
			foreach($q as $s)
				{
				echo '<tr>';
				echo '<td>'.$s->d_prison.'</td>';
				echo '<td>'.$s->c_mail.'</td>';
				echo '<td>'.$s->c_ip.'</td>';
				echo '<td><a href="javascript:void(0)" class="rencSupp" onClick="f_liberte('.$s->id.')" title="'.__('Lib&eacute;rer','rencontre').'"></a></td>';
				echo '</tr>';
				}
			?>
			</table>
			</form>
		</div>
		<?php
		}
	//
	function menu_profil()
		{
		wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
		wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
		require(dirname (__FILE__) . '/lang/rencontre-js-admin-lang.php');
		wp_localize_script('rencontre', 'rencobjet', $lang);
		global $wpdb;
		$loc = substr(get_locale(),0,2); $loc2 = $loc."&";
		$q2 = $wpdb->get_var("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$loc."' ");
		if(!$q2) {$loc = "en"; $loc2 = "en&";}
		if (!($_SESSION['a2']==$_POST["a2"] && $_SESSION['a4']==$_POST["a4"]) || $_POST["a6"]!='')
			{
			if ($_POST["a1"]=="supp") profil_supp($_POST["a2"],$_POST["a3"],$_POST["a4"]);
			else if ($_POST["a1"]=="edit") profil_edit($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
			else if ($_POST["a1"]=="plus") profil_plus($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"]);
			else if ($_POST["a1"]=="langplus") profil_langplus($loc,$_POST["a4"]);
			else if ($_POST["a1"]=="langsupp") profil_langsupp($_POST["a4"]);
			else if ($_POST["a1"]=="synchro") synchronise();
			else if ($_POST["a1"]=="profil") profil_defaut();
			else if ($_POST["a1"]=="pays") liste_defaut();
			}
		$_SESSION['a2'] = $_POST["a2"]; $_SESSION['a4'] = $_POST["a4"];
		$q2 = $wpdb->get_results("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang!='".$loc."' GROUP BY c_lang ");
		if($q2!=null) foreach($q2 as $r2) { $loc2 .= $r2->c_lang."&"; }
		?>
		<div class='wrap'>
			<form name='menu_profil' method='post' action=''>
				<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='a3' value='' />
				<input type='hidden' name='a4' value='' /><input type='hidden' name='a5' value='' /><input type='hidden' name='a6' value='' />
			</form>
			<div class='icon32' id='icon-options-general'><br/></div>
			<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $this->version; ?></span></h2>
			<h2><?php _e('Profil', 'rencontre'); ?></h2>
			<?php $n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_profil");
			if($n==0)
				{
				echo "<p>".__('Il ne semble pas y avoir de profil. Vous pouvez charger les profils par d&eacute;faut si vous le souhaitez.', 'rencontre')."</p>";
				echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_profil\"].elements[\"a1\"].value=\"profil\";document.forms[\"menu_profil\"].elements[\"a2\"].value=\"profil\";document.forms[\"menu_profil\"].submit();'>". __('Charger profils', 'rencontre')."</a>";
				}
			$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
			if($n==0)
				{
				echo "<p>".__('La table des pays est vide. Vous pouvez charger les pays et r&eacute;gions par d&eacute;faut si vous le souhaitez.', 'rencontre')."</p>";
				echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_profil\"].elements[\"a1\"].value=\"pays\";document.forms[\"menu_profil\"].elements[\"a2\"].value=\"pays\";document.forms[\"menu_profil\"].submit();'>". __('Charger pays', 'rencontre')."</a>";
				}
			if(file_exists(dirname(__FILE__).'/inc/rencontre_synchronise.txt')) { ?>
			<p>
				<a href='javascript:void(0)' class='button-primary' onClick='f_synchronise();'><?php _e('Mettre &agrave; jour le profil des membres', 'rencontre'); ?></a>
				&nbsp;:&nbsp;<span style="color:red;font-weight:700;"><?php _e('Vous avez fait des modifications. Pensez &agrave; mettre &agrave; jour lorsque vous aurez termin&eacute;.', 'rencontre'); ?></span>
			</p><?php } ?>
			
			<p><?php _e('Vous pouvez cr&eacute;er, renommer et supprimer les diff&eacute;rents items du profil.', 'rencontre'); ?></p>
			<p>
				<?php _e('Attention, ce n\'est pas sans cons&eacute;quences. Les changements seront appliqu&eacute;s sur les profils des membres ce qui peut choquer. Prudence !', 'rencontre'); ?>&nbsp;
			</p>
			<h3><?php _e('Langue de r&eacute;f&eacute;rence', 'rencontre'); echo ' : <span style="color:#700;">'.$loc.'</span> --- ' . __('Autres', 'rencontre').'&nbsp;:&nbsp;';
			$ls = '';
			foreach($q2 as $r2)
				{
				if($r2->c_lang!=$loc)
					{
					$ls .= '<option value="'.$r2->c_lang.'">'.$r2->c_lang.'</option>';
					echo '<span style="color:#700;">' . $r2->c_lang . '</span>&nbsp;-&nbsp;';
					}
				}
			?></h3>
			<ul>
				<li>
					<label><?php _e('Ajouter une langue (2 lettres minuscules conformes au code du pays)', 'rencontre'); ?>&nbsp;</label>&nbsp;
					<input type="text" name="langplus" maxlength="2" size="2" />
					<a href='javascript:void(0)' class='button-primary' onClick='f_langplus();'><?php _e('Ajouter une langue', 'rencontre'); ?></a>
				</li>
				<li>
					<label><?php _e('Supprimer une langue et tout le contenu associ&eacute;', 'rencontre'); ?>&nbsp;</label>&nbsp;
					<select id="langsupp">
						<?php echo $ls; ?>
					</select>
					<a href='javascript:void(0)' class='button-primary' onClick='f_langsupp();'><?php _e('Supprimer une langue', 'rencontre'); ?></a>
				</li>
			</ul>
			<br />
			<div id="edit_profil"></div>
			<div style='margin:8px 12px 12px;'>
				<a href='javascript:void(0)' class='rencPlus' onClick='f_plus(0,"c_categ","","<?php echo $loc2; ?>");' title='Ajouter une cat&eacute;gorie'></a>
				<span style='font-style:italic;'><?php _e('Ajouter une cat&eacute;gorie','rencontre');?></span>
			</div>
			<?php
			$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$loc."' ORDER BY c_categ");
			$categ="";
			foreach($q as $r)
				{
				$q1 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$r->id."' and c_lang!='".$loc."' ORDER BY c_lang"); // multilangue
				if ($categ!=$r->c_categ) // nouvelle categorie
					{
				// CATEGORIE
					$categ = $r->c_categ;
					$a4 = $r->c_lang . '=' . $r->c_categ . '&';
					$out = '<div style="margin:-15px 0 10px 37px;color:#777;">';
					foreach($q1 as $r1)
						{
						$out .= $r1->c_lang.' : '.$r1->c_categ. ' -- ';
						$a4 .= $r1->c_lang . '=' . $r1->c_categ . '&';
						}
					echo '<h3>';
					echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'c_categ\',\''.urlencode($a4).'\',\'\');" title="'.__('Modifier le nom','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'c_categ\',0);" title="'.__('Supprimer toute la cat&eacute;gorie','rencontre').'"></a>';
					echo $categ.'</h3>';
					echo $out . '</div>';
				// LABEL
					echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus('.$r->id.',\'c_label\',\'\',\''.$loc2.'\');" title="'.__('Ajouter une valeur &agrave; cette cat&eacute;gorie','rencontre').'"></a>';
					echo '<span style="font-style:italic;">'.__('Ajouter une valeur &agrave; cette cat&eacute;gorie','rencontre').'</span><br /><br />';
					}
				$out = '';
				$a4 = $r->c_lang . '=' . $r->c_label . '&';
				foreach($q1 as $r1)
					{
					$out .= '<span style="margin:0 0 0 37px;color:#777;">'.$r1->c_lang.' : '.$r1->c_label. '</span><br />';
					$a4 .= $r1->c_lang . '=' . $r1->c_label . '&';
					}
				echo '<div class="rencLabel">';
				echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'c_label\',\''.urlencode($a4).'\','.$r->i_type.');" title="'.__('Modifier le nom ou le type','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'c_label\',0);" title="'.__('Supprimer','rencontre').'"></a>';
				echo $r->c_label . '<br />';
				echo $out . '</div><div style="height:5px;"></div>';
				// VALEUR
				switch($r->i_type)
					{
					case 1 :
					echo '<div class="rencValeur rencType">'.__('Une ligne de texte (TEXT)','rencontre').'</div>'."\r\n";
					break;
					case 2 :
					echo '<div class="rencValeur rencType">'.__('Grande zone de texte (TEXTAREA)','rencontre').'</div>'."\r\n";
					break;
					case 3 :
					echo '<div class="rencValeur">';
					echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus('.$r->id.',\'t_valeur\',\'\',\''.$loc2.'\');" title="'.__('Ajouter une valeur','rencontre').'"></a>';
					echo '<span class="rencType">'.__('Liste &agrave; choix unique (SELECT)','rencontre').'</span>';
					$s = json_decode($r->t_valeur);
					$s1=Array(); $s2=Array(); foreach($q1 as $r1) { $s1[] = json_decode($r1->t_valeur); $s2[] = $r1->c_lang; }
					$c=0;
					foreach($s as $ss)
						{
						$a4 = $r->c_lang . '=' . $ss. '&';
						$t = '';
						for($v=0; $v<count($s1); ++$v)
							{
							$a4 .= $s2[$v] . '=' . $s1[$v][$c] . '&';
							$t .= ($v!=0?'<br />':''). '<span style="margin:0 0 0 37px;color:#777;">'.$s2[$v].' : '.$s1[$v][$c]. '</span>';
							}
						echo '<br /><a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'t_valeur\',\''.urlencode($a4).'\','.$c.');" title="'.__('Modifier','rencontre').'"></a>';
						echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'t_valeur\','.$c.');" title="'.__('Supprimer','rencontre').'"></a>';
						echo $ss . '<br />';
						echo $t . "\r\n";
						++$c;
						}
					echo '</div>'."\r\n";
					break;
					case 4 :
					echo '<div class="rencValeur">';
					echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus('.$r->id.',\'t_valeur\',\'\',\''.$loc2.'\');" title="'.__('Ajouter une valeur','rencontre').'"></a>';
					echo '<span class="rencType">'.__('Liste &agrave; choix multiples (CHECKBOX)','rencontre').'</span>';
					$s = json_decode($r->t_valeur);
					$s1=Array(); $s2=Array(); foreach($q1 as $r1) { $s1[] = json_decode($r1->t_valeur); $s2[] = $r1->c_lang; }
					$c=0;
					foreach($s as $ss)
						{
						$a4 = $r->c_lang . '=' . $ss. '&';
						$t = '';
						for($v=0; $v<count($s1); ++$v)
							{
							$a4 .= $s2[$v] . '=' . $s1[$v][$c] . '&';
							$t .= ($v!=0?'<br />':''). '<span style="margin:0 0 0 37px;color:#777;">'.$s2[$v].' : '.$s1[$v][$c]. '</span>';
							}
						echo '<br /><a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'t_valeur\',\''.urlencode($a4).'\','.$c.');" title="'.__('Modifier','rencontre').'"></a>';
						echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'t_valeur\','.$c.');" title="'.__('Supprimer','rencontre').'"></a>';
						echo $ss . '<br />';
						echo $t . "\r\n";
						++$c;
						}
					echo '</div>'."\r\n";
					break;
					}
				?>
				<br style="clear:left;"/>
				<?php
				}
			?>
		</div>
		<?php
		}
	//
	function menu_pays()
		{
		wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
		wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
		require(dirname (__FILE__) . '/lang/rencontre-js-admin-lang.php');
		wp_localize_script('rencontre', 'rencobjet', $lang);
		global $wpdb;
		$q = $wpdb->get_results("SELECT c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' ");
		$drap = ''; $drapNom ='';
		foreach($q as $r)
			{
			$drap[$r->c_liste_iso] = $r->c_liste_valeur;
			$drapNom[$r->c_liste_iso] = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_iso='".$r->c_liste_iso."' and c_liste_lang='".substr($langue,0,2)."' ");
			}
		$loc = substr(get_locale(),0,2); $loc2 = $loc."&";
		$q2 = $wpdb->get_var("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".$loc."' ");
		if(!$q2) {$loc = "en"; $loc2 = "en&";}
		if (!($_SESSION['a2']==$_POST["a2"] && $_SESSION['a4']==$_POST["a4"]) || $_POST["a6"]!='')
			{
			if ($_POST["a1"]=="supp") liste_supp($_POST["a2"],$_POST["a3"],$_POST["a4"]);
			else if ($_POST["a1"]=="edit") liste_edit($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
			else if ($_POST["a1"]=="plus") liste_plus($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
			else if ($_POST["a1"]=="langplus") liste_langplus($loc,$_POST["a4"]);
			else if ($_POST["a1"]=="langsupp") liste_langsupp($_POST["a4"]);
			else if ($_POST["a1"]=="pays") liste_defaut();
			}
		$_SESSION['a2'] = $_POST["a2"]; $_SESSION['a4'] = $_POST["a4"];
		$q2 = $wpdb->get_results("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang!='".$loc."' and c_liste_lang!='' GROUP BY c_liste_lang ");
		if($q2!=null) foreach($q2 as $r2) { $loc2 .= $r2->c_liste_lang."&"; }
		?>
		<div class='wrap'>
			<form name='menu_liste' method='post' action=''>
				<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='a3' value='' />
				<input type='hidden' name='a4' value='' /><input type='hidden' name='a5' value='' /><input type='hidden' name='a6' value='' />
			</form>
			<div class='icon32' id='icon-options-general'><br/></div>
			<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $this->version; ?></span></h2>
			<h2><?php _e('Pays et R&eacute;gions', 'rencontre'); ?></h2>
			<?php $n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
			if($n==0)
				{
				echo "<p>".__('La table des pays est vide. Vous pouvez charger les pays et r&eacute;gions par d&eacute;faut si vous le souhaitez.', 'rencontre')."</p>";
				echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_liste\"].elements[\"a1\"].value=\"pays\";document.forms[\"menu_liste\"].elements[\"a2\"].value=\"pays\";document.forms[\"menu_liste\"].submit();'>". __('Charger pays', 'rencontre')."</a>";
				} ?>
			
			<p><?php _e('Vous pouvez cr&eacute;er, renommer et supprimer les pays et r&eacute;gions.', 'rencontre'); ?></p>
			<h3><?php _e('Langue de r&eacute;f&eacute;rence', 'rencontre'); echo ' : <span style="color:#700;">'.$loc.'</span> --- ' . __('Autres', 'rencontre').'&nbsp;:&nbsp;';
			$ls = '';
			foreach($q2 as $r2)
				{
				if($r2->c_liste_lang!=$loc)
					{
					$ls .= '<option value="'.$r2->c_liste_lang.'">'.$r2->c_liste_lang.'</option>';
					echo '<span style="color:#700;">' . $r2->c_liste_lang . '</span>&nbsp;-&nbsp;';
					}
				}
			?></h3>
			<ul>
				<li>
					<label><?php _e('Ajouter une langue (2 lettres minuscules conformes au code du pays)', 'rencontre'); ?>&nbsp;</label>&nbsp;
					<input type="text" name="langplus" maxlength="2" size="2" />
					<a href='javascript:void(0)' class='button-primary' onClick='f_liste_langplus();'><?php _e('Ajouter une langue', 'rencontre'); ?></a>
				</li>
				<li>
					<label><?php _e('Supprimer une langue et tout le contenu associ&eacute;', 'rencontre'); ?>&nbsp;</label>&nbsp;
					<select id="langsupp">
						<?php echo $ls; ?>
					</select>
					<a href='javascript:void(0)' class='button-primary' onClick='f_liste_langsupp();'><?php _e('Supprimer une langue', 'rencontre'); ?></a>
				</li>
			</ul>
			<br />
			<div id="edit_liste"></div>
			<div style='margin:8px 12px 12px;'>
				<a href='javascript:void(0)' class='rencPlus' onClick='f_liste_plus(0,"p","","<?php echo $loc2; ?>");' title='Ajouter un pays'></a>
				<span style='font-style:italic;'><?php _e('Ajouter un pays','rencontre');?></span>
			</div>
			<?php
			$q = $wpdb->get_results("SELECT c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' GROUP BY c_liste_iso"); // liste des codes ISO
			foreach($q as $r)
				{
				$q1 = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$r->c_liste_iso."' and c_liste_categ='p' ORDER BY c_liste_lang");
				$out = ''; $out1 = '';
				$a4 = $r->c_lang . '=' . $r->c_label . '&';
				foreach($q1 as $r1)
					{
					if($r1->c_liste_lang==$loc) $out1 = $r1->c_liste_valeur;
					else $out .= '<span style="margin:0 0 0 37px;color:#777;">'.$r1->c_liste_lang.' : '.$r1->c_liste_valeur. '</span><br />';
					$a4 .= $r1->c_liste_lang . '=' . $r1->c_liste_valeur . '&';
					}
				echo '<div class="rencLabel">';
				echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_liste_edit(\''.$r->c_liste_iso.'\',\'p\',\''.urlencode($a4).'\');" title="'.__('Modifier le nom ou le type','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_liste_supp(\''.$r->c_liste_iso.'\',\'p\',0);" title="'.__('Supprimer','rencontre').'"></a>';
				echo $out1.'&nbsp;('.$r->c_liste_iso.')<br />';
				if(isset($drap[$r->c_liste_iso])) echo '<img style="position:absolute;width:30px;height:20px;" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$r->c_liste_iso].'" />';
				echo $out . '</div><div style="height:5px;"></div>';
				echo '<div class="rencValeur">';
				echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_liste_plus(\''.$r->c_liste_iso.'\',\'r\',\'\',\''.$loc2.'\');" title="'.__('Ajouter une valeur','rencontre').'"></a>';
				echo '<span class="rencType">'.__('R&eacute;gions','rencontre').'</span>';
				$q2 = $wpdb->get_results("SELECT id, c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$r->c_liste_iso."' and c_liste_categ='r' ");
				foreach($q2 as $r2)
					{
					echo '<br /><a href="javascript:void(0)" class="rencEdit" onClick="f_liste_edit('.$r2->id.',\'r\',\''.$r2->c_liste_valeur.'\');" title="'.__('Modifier','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_liste_supp('.$r2->id.',\'r\',0);" title="'.__('Supprimer','rencontre').'"></a>';
					echo '<span style="margin:0 0 0 5px;color:#777;">'.$r2->c_liste_valeur. '</span>' . "\r\n";
					}
				echo '</div><br style="clear:left;"/>'."\r\n";
				}
			?>
		</div>
		<?php
		}
	//
	function widget()
		{
		global $current_user;
		if (array_shift($current_user->roles)=="subscriber" && !$_POST['nouveau']) $_SESSION['rencontre']='nouveau';
		else if (!$_SESSION['rencontre'] || !$_POST['page']) $_SESSION['rencontre']='mini,accueil,menu';
		else if (!$_POST['page']) $_SESSION['rencontre']='mini,accueil,menu';
		else if ($_POST['page']=='portrait') $_SESSION['rencontre']='portrait,menu';
		else if ($_POST['page']=='sourire') $_SESSION['rencontre']='portrait,menu,sourire';
		else if ($_POST['page']=='demcont') $_SESSION['rencontre']='portrait,menu,demcont';
		else if ($_POST['page']=='signale') $_SESSION['rencontre']='portrait,menu,signale';
		else if ($_POST['page']=='bloque') $_SESSION['rencontre']='portrait,menu,bloque';
		else if ($_POST['page']=='change') $_SESSION['rencontre']='change,menu';
		else if ($_POST['page']=='cherche') $_SESSION['rencontre']='cherche,accueil,menu';
		else if ($_POST['page']=='trouve') $_SESSION['rencontre']='trouve,accueil,menu';
		else if ($_POST['page']=='liste') $_SESSION['rencontre']='trouve,liste,accueil,menu';
		else if ($_POST['page']=='msg') $_SESSION['rencontre']='msg,accueil,menu';
		else if ($_POST['page']=='ecrire') $_SESSION['rencontre']='ecrire,accueil,menu';
		else if ($_POST['page']=='compte') $_SESSION['rencontre']='compte,accueil,menu';
		else if ($_POST['page']=='password') $_SESSION['rencontre']='mini,accueil,menu,password';
		else if ($_POST['page']=='fin')
			{
			f_userSupp($current_user->ID,$current_user->user_login,0);
			if ($options['mailsupp'])
				{
				$q = $wpdb->get_var("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID='".$current_user->ID."'");
				$objet  = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES).' - '.__('Suppression du compte','rencontre');
				$message  = __('Votre compte a &eacute;t&eacute; supprim&eacute;','rencontre');
				@wp_mail($q, $objet, $message);
				}
			}
		require(dirname (__FILE__) . '/inc/rencontre_widget.php');
		if ($_POST['nouveau'] && $_POST['pass1']) RencontreWidget::f_changePass($current_user->ID); 
		if (is_user_logged_in()) register_widget("RencontreWidget"); // class
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
		list($annee, $mois, $jour) = split('[-.]', $naiss);
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$age = $today['annee'] - $annee;
		if ($today['mois'] <= $mois) {if ($mois == $today['mois']) {if ($jour > $today['jour'])$age--;}else$age--;}
		return $age;
		}
	//
	static function f_ficheLibre() // Creation du fichier HTML de presentation des membres en libre acces pour la page d accueil
		{
		if (!file_exists(plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html'))
			{
			$options = get_option('rencontre_options');
			$out = '<link rel="stylesheet" type="text/css" href="'.plugins_url('rencontre/css/rencontre.css').'" media="all" />'."\r\n";
		//	$out .= '<script type="text/javascript" src="'.plugins_url('rencontre/js/rencontre-libre.js').'"></script>'."\r\n"; // Zoom automatique sur chaque personne
			$out .= '<div id="widgRenc">'."\r\n";
			$upl = wp_upload_dir(); 
			if (!is_dir($upl['basedir'].'/portrait/')) mkdir($upl['basedir'].'/portrait/');
			if (!is_dir($upl['basedir'].'/portrait/libre/')) mkdir($upl['basedir'].'/portrait/libre/');
			global $wpdb;
			$langue = ((WPLANG)?WPLANG:get_locale());
			$q = $wpdb->get_results("SELECT c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' ");
			$drap=''; $drapNom='';
			foreach($q as $r)
				{
				$drap[$r->c_liste_iso] = $r->c_liste_valeur;
				$drapNom[$r->c_liste_iso] = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_iso='".$r->c_liste_iso."' and c_liste_lang='".substr($langue,0,2)."' ");
				}
			$q = $wpdb->get_results("SELECT U.ID, U.display_name, U.user_registered, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre, P.t_annonce
				FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
				WHERE R.i_photo!=0 and R.user_id=P.user_id and R.user_id=U.ID and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".$options['jlibre']." and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30
				ORDER BY U.user_registered DESC
				LIMIT ".$options['npa']);
			$c = 0;
			if($q) foreach($q as $r)
				{ 
				$a = substr(stripslashes($r->t_annonce),0,180);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $a, $m);
				$a = str_replace(array($m[0]), array(''), $a);
				$a = str_replace(', ', ',', $a); $a = str_replace(',', ', ', $a);
				$a = strtr($a, "0123456789#(){[]}", ".................");
				$a = substr($a,0,150).'...';
				$b = stripslashes($r->t_titre);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $b, $m);
				$b = str_replace(array($m[0]), array(''), $b);
				$b = str_replace(', ', ',', $b); $b = str_replace(',', ', ', $b);
				$b = strtr($b, "0123456789#(){[]}", ".................");
				$out.='<div class="rencBox" style="float:left;width:31.32%;padding:1px;margin:0.5%;max-height:109px;overflow:hidden;">';
				$out.='<div class="miniPortrait miniBox"><a href="wp-login.php?action=register">';
				if ($r->i_photo!=0)
					{
					@copy($upl['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.(($r->ID)*10).'-mini.jpg', $upl['basedir'].'/portrait/libre/'.($r->ID*10).'-mini.jpg');
					@copy($upl['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.(($r->ID)*10).'-libre.jpg', $upl['basedir'].'/portrait/libre/'.($r->ID*10).'-libre.jpg');
					$out.='<img id="tete'.$c.'" class="tete" onMouseOver="f_tete_zoom(this,\''.$upl['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg\');" onMouseOut="f_tete_normal(this,\''.$upl['baseurl'].'/portrait/libre/'.($r->ID*10).'-mini.jpg\');" src="'.$upl['baseurl'].'/portrait/libre/'.($r->ID*10).'-mini.jpg" alt="'.$r->display_name.'" />';
					$out.='<img style="display:none;" src="'.$upl['baseurl'].'/portrait/libre/'.($r->ID*10).'-libre.jpg" />';
					}
				else $out.='<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$r->display_name.'" />';
				$out.='<div><h3>'.$r->display_name.'</h3>';
				$out.='<div class="monAge">'.Rencontre::f_age($r->d_naissance).'&nbsp;'.__('ans','rencontre').'</div>';
				$out.='<div class="maVille">'.$r->c_ville.'</div></div>';
				$out.='<p style="width:100%;">'.$b.'</p>';
				$out.='</a></div><div style="font-size:0.8em;padding:2px 2px 0 4px;">'.$a; 
				$pays = strtr(utf8_decode($r->c_pays), 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
				$pays = strtr($pays, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ ', 'aaaaaaceeeeiiiinooooouuuuyy_');
				$pays = str_replace("'", "", $pays);
				$cpays = str_replace("'", "&#39;", $r->c_pays);
			//	if ($r->c_pays!="") $out.='<img class="flag" style="position:absolute;bottom:5px;right:5px;" src="'.plugins_url('rencontre/images/drapeaux/').$pays.'.png" alt="'.$cpays.'" title="'.$cpays.'" />';
				if($r->c_pays!="") $out.='<img class="flag" style="position:absolute;bottom:5px;right:5px;" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$r->c_pays].'" alt="'.$drapNom[$r->c_pays].'" title="'.$drapNom[$r->c_pays].'" />';
				$out.='</div><div class="clear"></div></div>'."\r\n";
				++$c;
				}
			$out.="\r\n".'<div class="clear">&nbsp;</div></div><!-- #widgRenc -->'."\r\n";
			$t = fopen(plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html', 'w');
			if ($t) { fwrite($t,$out); fclose($t); }
			echo $out;
			}
		else include(plugin_dir_path( __FILE__ ).'/cache/cache_portraits_accueil.html');
		}
	//
	static function f_loginFB() // connexion via Facebook
		{
		if (!is_user_logged_in())
			{
			$options = get_option('rencontre_options');
			if (strlen($options['fblog'])>2)
				{
			?>
			
			<form action="" name="reload"></form>
			<script>
			function checkLoginState(){FB.getLoginStatus(function(r){logfb(r);});}
			function logfb(r){if (r.status==='connected'){FB.api('/me', function(r){jQuery(document).ready(function(){jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',{'action':'fbok','fb':r},function(re){document.forms['reload'].submit();});});});}}
			window.fbAsyncInit=function(){FB.init({appId:'<?php echo $options['fblog']; ?>',cookie:true,xfbml:true,version:'v2.0'});FB.logout();};
			(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="http://connect.facebook.net/<?php if(WPLANG) echo WPLANG; else echo "fr_FR"; ?>/sdk.js";fjs.parentNode.insertBefore(js,fjs);}(document,'script','facebook-jssdk'));
			</script>
			<?php if (!is_user_logged_in()) echo '<fb:login-button scope="public_profile,email" onlogin="checkLoginState();" data-auto-logout-link="true"></fb:login-button>'; ?>
			<?php
				}
			}
		}
	//
	} // FIN DE LA CLASSE
// *****************************************************************************************
//
?>