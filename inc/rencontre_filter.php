<?php
// Filtres / Action : General
add_filter('show_admin_bar' , 'rencAdminBar'); // Visualisation barre admin
add_action('init', 'rencPreventAdminAccess', 0); // bloque acces au tableau de bord
add_action('init', 'rencInLine', 1); // session
add_action('wp_logout', 'rencOutLine'); // session
add_filter('random_password', 'f_length_pass'); function f_length_pass($pass) {$pass = substr($pass,0,3); return $pass;}
add_action('admin_bar_menu', 'f_admin_menu', 999);
add_shortcode('rencontre_libre', 'f_shortcode_rencontre_libre');
add_shortcode('rencontre', 'f_shortcode_rencontre');
function f_shortcode_rencontre_libre() {if(!is_user_logged_in()) return Rencontre::f_ficheLibre(0,1);} // shortcode : [rencontre_libre]
function f_shortcode_rencontre() {if(is_user_logged_in()) {$renc=new RencontreWidget; $renc->widget(0,0);}} // shortcode : [rencontre]
if (isset($_COOKIE['lang']) && strlen($_COOKIE['lang'])==5) add_filter('locale', 'set_locale2'); function set_locale2() { return $_COOKIE['lang']; }
// Mail
add_filter ('retrieve_password_message', 'retrieve_password_message2', 10, 2);
// AJAX
add_action('wp_ajax_regionBDD', 'f_regionBDD'); // AJAX - retour des regions dans le select
add_action('wp_ajax_sourire', 'f_sourire'); function f_sourire() {}
add_action('wp_ajax_voirMsg', 'f_voirMsg'); function f_voirMsg() {RencontreWidget::f_voirMsg($_POST['msg'],$_POST['alias'],(isset($_POST['ho'])?$_POST['ho']:false));}
add_action('wp_ajax_suppMsg', 'f_suppMsg'); function f_suppMsg() {RencontreWidget::f_suppMsg($_POST['msg'],$_POST['alias'],(isset($_POST['ho'])?$_POST['ho']:false));}
add_action('wp_ajax_boiteEnvoi', 'f_boiteEnvoi'); function f_boiteEnvoi() {RencontreWidget::f_boiteEnvoi($_POST['alias'],(isset($_POST['ho'])?$_POST['ho']:false));}
add_action('wp_ajax_boiteReception', 'f_boiteReception'); function f_boiteReception() {RencontreWidget::f_boiteReception($_POST['alias'],(isset($_POST['ho'])?$_POST['ho']:false));}
add_action('wp_ajax_pseudo', 'f_pseudo');
add_action('wp_ajax_iniPass', 'f_iniPass'); // premiere connexion - changement mot de passe initial et pseudo
add_action('wp_ajax_testPass', 'f_testPass'); // changement du mot de passe
add_action('wp_ajax_fbok', 'f_fbok'); add_action('wp_ajax_nopriv_fbok', 'f_fbok'); // connexion via FB
add_action('wp_ajax_miniPortrait2', 'f_miniPortrait2'); function f_miniPortrait2() {RencontreWidget::f_miniPortrait2($_POST['id']);}
if (is_admin())
	{
	add_action('wp_ajax_iso', 'f_iso'); // Test si le code ISO est libre (Partie ADMIN)
	add_action('wp_ajax_drap', 'f_drap'); // SELECT avec la liste des fichiers drapeaux (Partie ADMIN)
	add_action('wp_ajax_exportCsv', 'f_exportCsv'); // Export CSV (Partie ADMIN)
	add_action('wp_ajax_importCsv', 'f_importCsv'); // Import CSV (Partie ADMIN)
	}
// CRON
add_action('plugins_loaded', 'f_cron');
function f_cron()
	{
	if (function_exists('wpGeonames')) add_action('wp_ajax_city', 'f_city'); // ici pour le "plugins_loaded" - plugin WP-GeoNames
	$d = dirname(__FILE__).'/rencontre_cron.txt';
	$d1 = dirname(__FILE__).'/rencontre_cronOn.txt';
	$d2 = dirname(__FILE__).'/rencontre_cronListe.txt'; if (!file_exists($d2)) {$t=@fopen($d2,'w'); @fwrite($t,'0'); @fclose($t);}
	$d3 = dirname(__FILE__).'/rencontre_cronListeOn.txt';
	$d4 = dirname(__FILE__).'/rencontre_cronBis.txt';
	global $rencOpt;
	$t = time(); $hcron = $rencOpt['hcron']+0;
	$u1 = date("G",$t-3600*$hcron); // heure actuelle(UTC) - heure creuse (+24 si <0) ; ex il est 15h23Z (15), Hcreuse:4h (4) => $u = 15 - 4 = 11;
	// u1 progresse 21, 22, 23 puis 0 lorsqu'il est l'heure creuse (donc<12). Il reste alors 12 heures pour qu"un visiteur provoque le CRON.
	if (!file_exists($d) || (date("j",filemtime($d))!=date("j",$t) && $u1<12) && $t>filemtime($d)+7200) // !existe ou (jour different et dans les 12 heures qui suivent hcron et plus de 2 heures apres precedent)
		{
		if (!file_exists($d1) || $t>filemtime($d1)+120)
			{
			$t=fopen($d1, 'w'); fclose($t); // CRON une seule fois
			f_cron_on(0);
			}
		}
	else if (file_exists($d4) && $u1<12 && $t>filemtime($d)+3661)
		{
		if (!file_exists($d1) || $t>filemtime($d1)+120)
			{
			$t=fopen($d1, 'w'); fclose($t); // CRON BIS une seule fois, une heure apres CRON
			f_cron_on(1); // second passage (travail sur deux passages)
			}
		}
	else if ($t>filemtime($d)+3661 && $t>filemtime($d2)+3661 && $u1<23 && (!file_exists($d3) || $t>filemtime($d3)+120))
		{
		$t=fopen($d3, 'w'); fclose($t); // CRON LISTE une seule fois
		f_cron_liste($d2); // MSG ACTION
		}
	// else f_cron_on(); // mode force
	}
//
function set_html_content_type(){ return 'text/html'; }
//
function f_cron_on($cronBis=0)
	{
	// NETTOYAGE QUOTIDIEN
	global $wpdb; global $rencOpt; global $rencDiv;
	$bn = get_bloginfo('name');
	$s1 = ""; // (synthese admin)
	$cm = 0; // compteur de mail
	$style = '<style>span.mot1{color:red;} ';
	$style .= 'table.tab{display:block;padding:3px 0;border-top:1px dashed #888;border-bottom:1px dashed #888;text-align:center;} ';
	$style .= 'table.tab td{background-color:#faf7e5;padding:3px 0 0 3px;} ';
	$style .= 'p.mot2{font-weight:700;font-size:.9em;} ';
	$style .= 'div.mot3{color:#444;font-size:.9em;font-family:"DejaVu Sans",sans-serif;margin:0 3px;} ';
	$style .= 'table.tab a{text-decoration:none;color:#000;} table.tab br{line-height:0;}';
	$style .= 'div.box1{width:130px;height:108px;margin-right:3px;text-align:left;}</style>'."\r\n";
	$style .= '<style>div.box2{height:60px;overflow:hidden;white-space:nowrap;line-height:1.2em;} ';
	$style .= 'div.box1 img{float:right; width:60px;border-radius:3px;} ';
	$style .= 'div.nom{color:#ca3c08;font-weight:700;width:68px;overflow:hidden;font-size:.8em;} ';
	$style .= 'div.age,div.ville{color:#9f5824;font-size:0.7em;width:65px;overflow:hidden;line-height:1.15em;} ';
	$style .= 'div.box1 p{font-size:.7em;color:#000;height:3.6em;line-height:1.2em;overflow:hidden;margin-top:5px;} ';
	$style .= 'div.box1 img.drap{width:30px;height:20px;margin-left:4px;}</style>';
	if(!$cronBis)
		{
		// 1. Efface les _transient dans wp_option
		$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name like '\_transient\_namespace\_%' OR option_name like '\_transient\_timeout\_namespace\_%' ");
		// 2. Supprime le cache portraits page d'accueil. Remise a jour a la premiere visite (fiches libre)
		if (file_exists(plugin_dir_path( __FILE__ ).'../cache/cache_portraits_accueil.html')) @unlink(plugin_dir_path( __FILE__ ).'../cache/cache_portraits_accueil.html');
		// 3. Suppression des utilisateur sans compte rencontre
		$d = date("Y-m-d H:i:s", mktime(0,0,0,date("m"),date("d"),date("Y"))-100000); // ~30 heures
		$q = $wpdb->get_results("SELECT U.ID FROM ".$wpdb->prefix."users U LEFT OUTER JOIN ".$wpdb->prefix."rencontre_users R ON U.ID=R.user_id WHERE R.user_id IS NULL");
		if ($q) foreach($q as $r)
			{
			$s = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."users WHERE ID='".$r->ID."' and user_registered<'".$d."' ");
			if($s && !user_can($s,'edit_posts'))
				{
				$wpdb->delete($wpdb->prefix.'users', array('ID'=>$r->ID));
				$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$r->ID));
				}
			}
		// 4. Suppression fichiers anciens dans UPLOADS/SESSION/ et UPLOADS/TCHAT/ et des exports CSV UPLOADS/TMP
		if (!is_dir($rencDiv['basedir'].'/session/')) mkdir($rencDiv['basedir'].'/session/');
		else
			{
			$tab=''; $d=$rencDiv['basedir'].'/session/';
			if ($dh=opendir($d))
				{
				while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..') $tab[]=$d.$file; }
				closedir($dh);
				if ($tab!='') foreach ($tab as $r){if (filemtime($r)<time()-1296000) unlink($r);} // 15 jours
				}
			}
		if (!is_dir($rencDiv['basedir'].'/tchat/')) mkdir($rencDiv['basedir'].'/tchat/');
		else
			{
			$tab=''; $d=$rencDiv['basedir'].'/tchat/';
			if ($dh=opendir($d))
				{
				while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..') $tab[]=$d.$file; }
				closedir($dh);
				if ($tab!='') foreach ($tab as $r){if (filemtime($r)<time()-86400) unlink($r);} // 24 heures
				}
			}
		if (is_dir($rencDiv['basedir'].'/tmp/'))
			{
			$a=array();
			if ($h=opendir($rencDiv['basedir']."/tmp/"))
				{
				while (($file=readdir($h))!==false)
					{
					$ext=explode('.',$file);
					$ext=$ext[count($ext)-1];
					if ($ext=='csv' && $file!='.' && $file!='..' && strpos($file,"rencontre")!==false) $a[]=$rencDiv['basedir']."/tmp/".$file;
					}
				closedir($h);
				}
			// ************************
			if(is_array($a)) array_map('unlink', $a);
			}
		// 5. Suppression fichiers anciens dans UPLOADS/PORTRAIT/LIBRE/ : > 3 jours
		if (!is_dir($rencDiv['basedir'].'/portrait/libre/')) @mkdir($rencDiv['basedir'].'/portrait/libre/');
		else
			{
			$tab=''; $d=$rencDiv['basedir'].'/portrait/libre/';
			if ($dh=opendir($d))
				{
				while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..') $tab[]=$d.$file; }
				closedir($dh);
				if ($tab!='') foreach ($tab as $r){if (filemtime($r)<time()-288000) unlink($r);} // 80 heures
				}
			}
		// 6. Sortie de prison
		$free=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-$rencOpt['prison'], date("Y")));
		$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_prison WHERE d_prison<'".$free."' ");
		// 7. anniversaire du jour
		if ($rencOpt['mailanniv'])
			{
			$q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, R.user_id FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE d_naissance LIKE '%".date('m-d')."' AND U.ID=R.user_id LIMIT ".floor(max(0, $rencOpt['qmail']*.1)) );
			foreach($q as $r)
				{
				$s = "<div style='text-align:left;margin:5px 5px 5px 10px;'>".__('Hello','rencontre')." ".$r->user_login.","."\r\n";
				if ($rencOpt['textanniv'] && strlen($rencOpt['textanniv'])>10) $s .= "<br />".nl2br(stripslashes($rencOpt['textanniv']))."\r\n";
				$s .= "</div>\r\n";
				$he = '';
				if(!has_filter('wp_mail') && !has_filter('wp_mail_content_type'))
					{
					$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
					$he[] = 'Content-type: text/html';
					$s = '<html><head></head><body>' . $s . '</body></html>';
					}
				@wp_mail($r->user_email, $bn, $s, $he);
				++$cm;
				$s1 .= $s;
				}
			}
		// 8. Efface une fois par semaine les statistiques du nombre de mail par heure
		if (date("N")=="1")  // le lundi
			{
			$t=@fopen(dirname(__FILE__).'/rencontre_cronListe.txt','w'); @fwrite($t,'0'); @fclose($t);
			$t=@fopen(dirname(__FILE__).'/rencontre_cron.txt','w'); @fwrite($t,$cm); @fclose($t);
			}
		}
	// 9 Mail vers les membres et nettoyage des comptes actions (suppression comptes inexistants)
	$j = floor((floor(time()/86400)/60 - floor(floor(time()/86400)/60)) * 60 +.00001); // horloge de jour de 0 à 59 (temps unix) - ex : aujourd'hui -> 4
	if($rencOpt['mailmois']==2)
		{
		$j0 = floor(($j/15-floor($j/15)) * 15 + .00001); // horloge de jour de 0 a 14
		if(!$cronBis) // CRON (H)
			{
			$max = floor(max(0, $rencOpt['qmail']*.85)); // 85% du max - heure creuse - 15% restant pour inscription nouveaux membres et anniv
			$j1=$j0+15;
			}
		else // CRON BIS (H+1)
			{
			$max = floor(max(0, $rencOpt['qmail']*.95)); // 95% du max - heure creuse - 5% restant pour inscription nouveaux membres
			$j2=$j0+30; $j3=$j0+45;
			}
		}
	else if($rencOpt['mailmois']==3)
		{
		$j0 = floor(($j/7-floor($j/7)) * 7 + .00001); // horloge de jour de 0 a 6
		if(!$cronBis) // CRON (H)
			{
			$max = floor(max(0, $rencOpt['qmail']*.85)); // 85% du max - heure creuse - 15% restant pour inscription nouveaux membres et anniv
			$j1=$j0+7; $j2=$j0+14; $j3=$j0+21;
			}
		else // CRON BIS (H+1)
			{
			$max = floor(max(0, $rencOpt['qmail']*.95)); // 95% du max - heure creuse - 5% restant pour inscription nouveaux membres
			$j4=$j0+28; $j5=$j0+35; $j6=$j0+42; $j7=$j0+49; $j8=$j0+56;
			}
		}
	else
		{
		$jj = ($j>29)?$j-30:$j+30; // aujourd'hui : 34
		if(!$cronBis) $max = floor(max(0, $rencOpt['qmail']*.85)); // 85% du max - heure creuse - 15% restant pour inscription nouveaux membres et anniv
		else $max = floor(max(0, $rencOpt['qmail']*.95)); // 95% du max - heure creuse - 5% restant pour inscription nouveaux membres
		}
		// 9.1 selection des membres
	$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
	$drap=''; $drapNom='';
	foreach($q as $r)
		{
		if($r->c_liste_categ=='d') $drap[$r->c_liste_iso] = $r->c_liste_valeur;
		else if($r->c_liste_categ=='p')$drapNom[$r->c_liste_iso] = $r->c_liste_valeur;
		}
	$q=0;
	if(!$cronBis && $rencOpt['mailmois']==2) $q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation 
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R 
		WHERE SECOND(U.user_registered) IN (".$j0.",".$j1.") AND U.ID=P.user_id AND U.ID=R.user_id ORDER BY P.d_modif DESC LIMIT ".$max);
	else if($cronBis && $rencOpt['mailmois']==2) $q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation 
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R 
		WHERE SECOND(U.user_registered) IN (".$j2.",".$j3.") AND U.ID=P.user_id AND U.ID=R.user_id ORDER BY P.d_modif DESC LIMIT ".$max);
	else if(!$cronBis&& $rencOpt['mailmois']==3) $q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation 
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R 
		WHERE SECOND(U.user_registered) IN (".$j0.",".$j1.",".$j2.",".$j3.") AND U.ID=P.user_id AND U.ID=R.user_id ORDER BY P.d_modif DESC LIMIT ".$max);
	else if($cronBis && $rencOpt['mailmois']==3) $q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation 
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R 
		WHERE SECOND(U.user_registered) IN (".$j4.",".$j5.",".$j6.",".$j7.",".$j8.") AND U.ID=P.user_id AND U.ID=R.user_id ORDER BY P.d_modif DESC LIMIT ".$max);
	else if(!$cronBis) $q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation 
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R 
		WHERE SECOND(U.user_registered)='".$j."' AND U.ID=P.user_id AND U.ID=R.user_id ORDER BY P.d_modif DESC LIMIT ".$max);
	else if($cronBis) $q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation 
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R 
		WHERE SECOND(U.user_registered)='".$jj."' AND U.ID=P.user_id AND U.ID=R.user_id ORDER BY P.d_modif DESC LIMIT ".$max);
		// 9.2 boucle de mail
	$ct=0;
	if ($q) foreach($q as $r)
		{
		++$ct;
		$action= json_decode($r->t_action,true);
		if ($rencOpt['mailmois'] && $ct<=$max)
			{
			// BONJOUR
			$s = "<div style='text-align:left;margin:5px 5px 5px 10px;'>".__('Hello','rencontre')."&nbsp;".$r->user_login.","."\r\n";
			if ($rencOpt['textmail'] && strlen($rencOpt['textmail'])>10) $s .= "<br />".nl2br(stripslashes($rencOpt['textmail']))."\r\n";
			// NBR VISITES
			$s .= "<p class='mot2'>".__('Your profile has been visited','rencontre')."&nbsp;<span class='mot1'>".count($action['visite'])."&nbsp;".__('time','rencontre')."</span>.</p>";
			// PROPOSITIONS
			$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$r->i_zage_min));
			$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$r->i_zage_max));
			$q1 = $wpdb->get_results("SELECT U.ID, U.user_login, R.d_naissance, R.c_pays, R.c_ville, P.t_titre
					FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
					WHERE U.ID=R.user_id AND P.user_id=R.user_id AND R.i_sex='".$r->i_zsex."' AND R.i_zrelation='".$r->i_zrelation."' AND R.d_naissance<'".$zmin."' AND R.d_naissance>'".$zmax."' AND U.ID!='".$r->ID."'".(($rencOpt['onlyphoto'])?" AND R.i_photo>0 ":" ")."
					ORDER BY U.user_registered DESC LIMIT 4");
			if ($q1)
				{
				$s .= "<p class='mot2'>".__('Here\'s a selection of members that may interest you','rencontre')." :</p><table class='tab' cellspacing='7'><tr>";
				foreach($q1 as $r1)
					{
					if (file_exists($rencDiv['basedir']."/portrait/".floor($r1->ID/1000)."/".Rencontre::f_img(($r1->ID*10)."-mini").".jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($r1->ID/1000)."/".Rencontre::f_img(($r1->ID*10)."-mini").".jpg";
					else $u = plugins_url('rencontre/images/no-photo60.jpg');
					list($annee, $mois, $jour) = explode('-', $r1->d_naissance);
					$today['mois'] = date('n'); $today['jour'] = date('j'); $today['annee'] = date('Y');
					$age = $today['annee'] - $annee;
					if ($today['mois'] <= $mois) {if ($mois == $today['mois']) {if ($jour > $today['jour'])$age--;}else $age--;}
					$s .= "<td><a href='".esc_url(home_url('/'))."index.php?rencidfm=".$r1->ID."' target='_blank'>";
					$s .= "<div class='box1'><img src='".$u."' alt='".substr($r1->user_login,0,10)."'/><div class='box2'>";
					$s .= "<div class='nom'>".substr($r1->user_login,0,10)."</div><div class='age'>".$age."&nbsp;".__('years','rencontre')."</div><div class='ville'>".$r1->c_ville."</div></div>";
					$s .= "<p><img class='drap' src='".plugins_url('rencontre/images/drapeaux/').$drap[$r1->c_pays]."' />";
					$s .= substr($r1->t_titre,0,45)."</p></div>";
					$s .= "</a>"."\r\n"."</td>";
					}
				$s .= "</tr></table>"."\r\n";
				}
			// SOURIRES
			if (isset($action['sourireIn']) && count($action['sourireIn']))
				{
				$t = "<p class='mot2'>".__('You have received a smile from','rencontre')." :</p><table class='tab'><tr>";
				$c = 0;
				for ($v=0; $v<count($action['sourireIn']);++$v)
					{
					$q1 = $wpdb->get_var("SELECT U.user_login FROM ".$wpdb->prefix."users U WHERE ID='".$action['sourireIn'][$v]['i']."'");
					if ($q1)
						{
						++$c;
						if (file_exists($rencDiv['basedir']."/portrait/".floor($action['sourireIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['sourireIn'][$v]['i']*10)."-mini").".jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($action['sourireIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['sourireIn'][$v]['i']*10)."-mini").".jpg";
						else $u = plugins_url('rencontre/images/no-photo60.jpg');
						$s .= $t . "<td><a href='".esc_url(home_url('/'))."index.php?rencidfm=".$action['sourireIn'][$v]['i']."' target='_blank'><img src='".$u."' alt=''/><div class='mot3'>".substr($q1,0,10)."</div></a>"."\r\n"."</td>";
						if ($c/6==floor($c/6)) $s .="</tr><tr>";
						$t = "";
						}
					}
				if ($t=="") $s .= "</tr></table>"."\r\n";
				}
			// DEMANDES DE CONTACT
			if (isset($action['contactIn']) && count($action['contactIn']))
				{
				$t = "<p class='mot2'>".__('You have received a contact request from','rencontre')." :</p><table class='tab'><tr>";
				$c = 0;
				for ($v=0; $v<count($action['contactIn']);++$v)
					{
					$q1 = $wpdb->get_var("SELECT U.user_login FROM ".$wpdb->prefix."users U WHERE ID='".$action['contactIn'][$v]['i']."'");
					if ($q1)
						{
						++$c;
						if (file_exists($rencDiv['basedir']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['contactIn'][$v]['i']*10)."-mini").".jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['contactIn'][$v]['i']*10)."-mini").".jpg";
						else $u = plugins_url('rencontre/images/no-photo60.jpg');
						$s .= $t . "<td><a href='".esc_url(home_url('/'))."index.php?rencidfm=".$action['contactIn'][$v]['i']."' target='_blank'><img src='".$u."' alt=''/><div class='mot3'>".substr($q1,0,10)."</div></a>"."\r\n"."</td>";
						if ($c/6==floor($c/6)) $s .="</tr><tr>";
						$t = "";
						}
					}
				if ($t=="") $s .= "</tr></table>"."\r\n";
				}
			// MESSAGES
			$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$r->user_login."' and M.read=0 and M.deleted=0");
			if ($n) $s .= "<p class='mot2'>".__('You have','rencontre')."&nbsp;<span class='mot1'>".$n."&nbsp;".(($n>1)?__('messages','rencontre'):__('message','rencontre'))."</span>&nbsp;".__('in your inbox.','rencontre').'</p>';
			// MOT DE LA FIN
			$s .= "<p>".__("Do not hesitate to send us your comments.",'rencontre')."<br /><br />".__('Regards,','rencontre')."<br />".$bn."</p></div>"."\r\n";
			//
			$s1 .= $s;
			$he = '';
			if(!has_filter('wp_mail') && !has_filter('wp_mail_content_type'))
				{
				$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
				$he[] = 'Content-type: text/html';
				$s = '<html><head></head><body>'.$s.'</body></html>';
				}
			@wp_mail($r->user_email, $bn, $style.$s, $he);
			++$cm;
			if (file_exists(dirname(__FILE__).'/cron_liste/'.$r->ID.'.txt')) @unlink(dirname(__FILE__).'/cron_liste/'.$r->ID.'.txt');
			}
		// 9.3 *********** Nettoyage des comptes action *********
		$ac = array("sourireIn","sourireOut","contactIn","contactOut","visite","bloque");
		$x = 0;
		for ($v=0; $v<count($ac); ++$v)
			{
			if(isset($action[$ac[$v]]))
				{
				$c = count($action[$ac[$v]]);
				for ($w=0; $w<$c; ++$w)
					{
					if(isset($action[$ac[$v]][$w]['i']))
						{
						$q1 = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$action[$ac[$v]][$w]['i']."' "); // compte suprime ?
						if(!$q1)
							{
							if(!$x) $x = 1;
							unset($action[$ac[$v]][$w]['i']); 
							unset($action[$ac[$v]][$w]['d']);
							}
						}
					}
				if($action[$ac[$v]]) $action[$ac[$v]]=array_filter($action[$ac[$v]]);
				if($action[$ac[$v]]) $action[$ac[$v]] = array_splice($action[$ac[$v]], 0); // remise en ordre avec de nouvelles clefs
				}
			}
		if($x)
			{
			$out = json_encode($action);
			$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$r->ID));
			}
		// ***************************************************
		}
	//
	if (date("N")!="1")$t=@fopen(dirname(__FILE__).'/rencontre_cron.txt', 'w'); @fwrite($t,max((file_get_contents(dirname(__FILE__).'/rencontre_cron.txt')+0),$cm)); @fclose($t);
	if($cronBis) @unlink(dirname(__FILE__).'/rencontre_cronBis.txt'); // CRON BIS effectue
	else {$t=@fopen(dirname(__FILE__).'/rencontre_cronBis.txt', 'w'); @fclose($t);} // CRON BIS a faire
	@unlink(dirname(__FILE__).'/rencontre_cronOn.txt');
	clearstatcache();
	}
//
function f_cron_liste($d2)
	{
	// Envoi Mail Horaire en respectant quota
	global $wpdb; global $rencOpt; global $rencDiv;
	$max = floor(max(0, $rencOpt['qmail']*.8)); // 80% du max - 20% restant pour inscription nouveaux membres
	$u2 = file_get_contents($d2);
	$cm = 0; // compteur de mail
	// 1. listing des USERS en attente
	if ($dh = @opendir(dirname(__FILE__).'/cron_liste/'))
		{
		$bn = get_bloginfo('name');
		$lis = '(';
		$fi = Array();
		$c = 0;
		while (($file = readdir($dh))!==false)
			{
			$lid=explode('.',$file);
			if ($file!='.' && $file!='..')
				{
				$fi[$c][0] = filemtime(dirname(__FILE__).'/cron_liste/'.$file); // date - en premier pour le sort
				$fi[$c][1] = $lid[0]; // nom
				++$c;
				}
			}
		sort($fi); // les plus ancien en premier
		$c = 0;
		foreach ($fi as $r)
			{
			++$c;
			if($c>$max) break;
			if($r[1]) $lis .= $r[1].","; else --$c;
			}
		if (strlen($lis)>2) $lis = substr($lis,0,-1) . ')'; else $lis='(0)';
		closedir($dh);
		$q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action 
			FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P 
			WHERE U.ID IN ".$lis." AND U.ID=P.user_id LIMIT ".$max); // clause IN : WHERE U.ID IN ( 250, 220, 170 );
		$las = 0;
		if($q) foreach($q as $r)
			{
			$b = 0;
			$action= json_decode($r->t_action,true);
			$s = "<div style='text-align:left;margin:5px 5px 5px 10px;color:#000;'>".__('Hello','rencontre')."&nbsp;".$r->user_login.","."\r\n";
			if (count($action['contactIn']))
				{
				$b = 1;
				$s .= "<p>".__('You have received a contact request from','rencontre')."</p><table><tr>";
				$v = count($action['contactIn'])-1;
				$q1 = $wpdb->get_var("SELECT U.user_login FROM ".$wpdb->prefix."users U WHERE ID='".$action['contactIn'][$v]['i']."'");
				if ($q1)
					{
					if (file_exists($rencDiv['basedir']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['contactIn'][$v]['i']*10)."-mini").".jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['contactIn'][$v]['i']*10)."-mini").".jpg";
					else $u = plugins_url('rencontre/images/no-photo60.jpg');
					$s .= "<td><a href='".esc_url(home_url('/'))."index.php?rencidfm=".$action['contactIn'][$v]['i']."' target='_blank'><img src='".$u."' alt=''/><div style='color:#444;font-size:.9em;font-family:\"DejaVu Sans\",sans-serif;margin:0 3px;'>".substr($q1,0,10)."</div></a>"."\r\n"."</td>";
					}
				$s .= "</tr></table>"."\r\n";
				}
			if (count($action['sourireIn']))
				{
				$b = 1;
				$s .= "<p>".__('You have received a smile from','rencontre')."</p><table><tr>";
				$v = count($action['contactIn'])-1;
				$q1 = $wpdb->get_var("SELECT U.user_login FROM ".$wpdb->prefix."users U WHERE ID='".$action['sourireIn'][$v]['i']."'");
				if ($q1)
					{
					if (file_exists($rencDiv['basedir']."/portrait/".floor($action['sourireIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['sourireIn'][$v]['i']*10)."-mini").".jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($action['sourireIn'][$v]['i']/1000)."/".Rencontre::f_img(($action['sourireIn'][$v]['i']*10)."-mini").".jpg";
					else $u = plugins_url('rencontre/images/no-photo60.jpg');
					$s .= "<td><a href='".esc_url(home_url('/'))."index.php?rencidfm=".$action['sourireIn'][$v]['i']."' target='_blank'><img src='".$u."' alt=''/><div style='color:#444;font-size:.9em;font-family:\"DejaVu Sans\",sans-serif;margin:0 3px;'>".substr($q1,0,10)."</div></a>"."\r\n"."</td>";
					}
				$s .= "</tr></table>"."\r\n";
				}
			$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$r->user_login."' and M.read=0 and M.deleted=0");
			if ($n)
				{
				$b = 1;
				$s .= "<p>".__('You have','rencontre')."&nbsp;".$n."&nbsp;".(($n>1)?__('messages','rencontre'):__('message','rencontre'))."&nbsp;".__('in your inbox.','rencontre')."</p>";
				}
			$s .= "<br /><br />".__('Regards,','rencontre')."<br />".$bn."</div>";
			if($b)
				{
				$he = '';
				if(!has_filter('wp_mail') && !has_filter('wp_mail_content_type'))
					{
					$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
					$he[] = 'Content-type: text/html';
					$s = '<html><head></head><body>' . $s . '</body></html>';
					}
				@wp_mail($r->user_email, $bn." - ".__('A member contact you','rencontre'), $s, $he);
				++$cm;
				}
			$d = filemtime(dirname(__FILE__).'/cron_liste/'.$r->ID.'.txt');
			if($d>$las) $las = $d;
			@unlink(dirname(__FILE__).'/cron_liste/'.$r->ID.'.txt');
			}
		foreach ($fi as $r)
			{
			if($r[0]>$las) break;
			else if(file_exists(dirname(__FILE__).'/cron_liste/'.$r[1].".txt")) @unlink(dirname(__FILE__).'/cron_liste/'.$r[1].".txt");  // suppression non traite car ID inexistant
			}
		}
	$t=@fopen($d2,'w'); @fwrite($t,max(($u2+0),$cm)); @fclose($t);
	@unlink(dirname(__FILE__).'/rencontre_cronListeOn.txt');
	}
//
function f_admin_menu ($wp_admin_bar)
	{
	$args = array(
		'id'=>'rencontre',
		'title'=>'<img src="'.plugins_url('rencontre/images/rencontre.png').'" />',
		'href'=>admin_url('admin.php?page=rencmembers'),
		'meta'=>array('class'=>'rencontre',
		'title'=>'Rencontre'));
	$wp_admin_bar->add_node($args);
	}
//
function rencInLine()
	{
	if (is_user_logged_in())
		{
		if (!session_id()) session_start();
		global $current_user; global $rencDiv; global $wpdb; 
		if (!is_dir($rencDiv['basedir'].'/tchat/')) mkdir($rencDiv['basedir'].'/tchat/');
		if (!is_dir($rencDiv['basedir'].'/session/')) mkdir($rencDiv['basedir'].'/session/');
		$t = fopen($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt', 'w') or die();
		fclose($t);
		$wpdb->update($wpdb->prefix.'rencontre_users', array('d_session'=>date("Y-m-d H:i:s")), array('user_id'=>$current_user->ID));
		}
	}
//
function rencOutLine()
	{
	global $current_user; global $rencDiv;
	if (file_exists($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt')) unlink($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt');
	session_destroy();
	}
//
function rencPreventAdminAccess()
	{
	global $rencDiv;
	$a=strtolower($_SERVER['REQUEST_URI']);
	if (strpos($a,'/wp-admin')!==false && strpos($a,'admin-ajax.php')==false && !current_user_can("administrator")) { wp_redirect($rencDiv['siteurl']); exit; }
	}
function rencAdminBar($content) { return (current_user_can("administrator")) ? $content : false; }
function f_regionBDD()
	{ 
	echo '<option value="">- '.__('Immaterial','rencontre').' -</option>';
	global $wpdb; 
	$iso = strip_tags($_POST['pays']);
	$q = $wpdb->get_results("SELECT id, c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$iso."' and c_liste_categ='r' ");
	foreach($q as $r) { echo '<option value="'.$r->id.'">'.$r->c_liste_valeur.'</option>'; }
	}
//
function f_pseudo()
	{ // test si pseudo libre (premiere connexion)
	$user = wp_get_current_user();
	global $wpdb; 
	$q = $wpdb->get_var("SELECT U.ID FROM ".$wpdb->prefix."users U WHERE user_login='".strip_tags($_POST['name'])."' and user_email!='".$user->user_email."' ");
	if (!$q) echo true;
	else echo false; // already exist
	}
//
function f_testPass()
	{
	global $wpdb;
	$q = $wpdb->get_var("SELECT user_pass FROM ".$wpdb->prefix."users WHERE ID='".strip_tags($_POST['id'])."'");
	if (wp_check_password($_POST['pass'],$q,$_POST['id']))
		{
		wp_set_password($_POST['nouv'],$_POST['id']); // changement MdP
		wp_set_auth_cookie($_POST['id']); // cookie pour rester connecte
		echo 'ok';
		}
	else echo '';
	}
//
function retrieve_password_message2($old_message, $key)
	{
	// 1. changement du mot de passe
	$p = wp_generate_password(8, false);
	if (strpos($_POST['user_login'],'@')) $u = get_user_by('email',trim($_POST['user_login']));
	else $u = get_user_by('slug',$_POST['user_login']);
	wp_set_password($p,$u->id); // changement MdP
	// 2. mail
	$message = __('Someone requested a new password for this account.','rencontre')."<br />";
	$message .= network_site_url()."<br /><br />";
	$message .= sprintf(__('Login : %s','rencontre'), $u->user_login)."<br /><br />";
	$message .= __('The password has been changed. You can log in and change it from your interface if you want.','rencontre')."<br /><br />";
	$message .= __('New password :','rencontre').'&nbsp;'.$p;
	return $message;
	}
//
function f_iniPass()
	{
	global $wpdb;
	$wpdb->update($wpdb->prefix.'users', array(
		'user_login'=>strip_tags($_POST['pseudo']),
		'user_nicename'=>strip_tags($_POST['pseudo']),
		'display_name'=>strip_tags($_POST['pseudo'])), 
		array('ID'=>strip_tags($_POST['id'])));
	$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array('user_id'=>strip_tags($_POST['id']),'d_modif'=>date("Y-m-d H:i:s")));
	$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>strip_tags($_POST['id']))); // suppression si existe deja
	wp_logout();
	}
//
function f_fbok() // connexion via Facebook
	{
	if (!is_user_logged_in())
		{
		$m = $_POST['fb'];
		global $wpdb;
		$u = $wpdb->get_var("SELECT user_login FROM ".$wpdb->prefix."users WHERE user_email='".strip_tags($m['email'])."'");
		if (!$u) // adresse mail inconnue => creation user
			{
			$u = $m["first_name"].substr($m["id"],5,4);
			$pw = wp_generate_password($length=5, $include_standard_special_chars=false);
			$user_id = wp_create_user($u,$pw,$m['email']);
			}
	//	$user = get_userdatabylogin($u); // This pluggable function has been deprecated.
		$user = get_user_by('login',$u);
		wp_set_current_user($user->ID, $u);
		wp_set_auth_cookie($user->ID);
		do_action('wp_login', $u); // connexion
		}
	}
//
function f_iso()
	{
	if ($_POST && isset($_POST['iso']))
		{
		global $wpdb;
		$q = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$_POST['iso']."' and c_liste_categ='p' ");
		if(!$q) echo true;
		else echo false;
		}
	}
//
function f_drap()
	{
	if ($_POST && isset($_POST['action']) && $_POST['action']=='drap')
		{
		if ($dh=opendir(dirname(__FILE__).'/../images/drapeaux/'))
			{
			$tab='';
			while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..') $tab[]=$file; }
			closedir($dh);
			sort($tab);
			foreach($tab as $r) { echo "<option value='".$r."'>".$r."</option>"; }
			}
		}
	}
//
function f_city() // plugin WP-GeoNames
	{
	global $wpdb;
	$s = $wpdb->get_results("SELECT name, latitude, longitude FROM ".$wpdb->prefix."geonames WHERE country_code='".strip_tags($_POST["iso"])."' and feature_class='P' and name LIKE '".strip_tags($_POST["city"])."%' ORDER BY name LIMIT 10");
	foreach($s as $t)
		{
		echo '<div onClick=\'f_cityMap("'.$t->name.'","'.$t->latitude.'","'.$t->longitude.'",'.($_POST["ch"]?'1':'0').');\'>'.$t->name.'</div>';
		}
	}
//
if (!function_exists('wp_new_user_notification'))
	{
	function wp_new_user_notification($user_id, $plaintext_pass = '')
		{
		global $rencDiv;
		$user = get_userdata($user_id);
		$blogname = wp_specialchars_decode($rencDiv['blogname'], ENT_QUOTES);
		$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";
		@wp_mail($rencDiv['admin_email'], sprintf(__('[%s] New User Registration'), $blogname), $message);
		if ( empty($plaintext_pass) ) return;
		$message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
		@wp_mail($user->user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);
		}
	}
//
function f_userSupp($f,$a,$b)
	{
	$r = 'wp-content/uploads/portrait/'.floor($f/1000);
	for ($v=0; $v<6; $v++)
		{
		if (file_exists($r."/".Rencontre::f_img($f.$v).".jpg")) unlink($r."/".Rencontre::f_img($f.$v).".jpg");
		if (file_exists($r."/".Rencontre::f_img($f.$v."-mini").".jpg")) unlink($r."/".Rencontre::f_img($f.$v."-mini").".jpg");
		if (file_exists($r."/".Rencontre::f_img($f.$v."-grande").".jpg")) unlink($r."/".Rencontre::f_img($f.$v."-grande").".jpg");
		if (file_exists($r."/".Rencontre::f_img($f.$v."-libre").".jpg")) unlink($r."/".Rencontre::f_img($f.$v."-libre").".jpg");
		}
	if (!is_admin()) wp_logout();
	global $wpdb;
	if ($b) // prison
		{
		$q = $wpdb->get_row("SELECT U.user_email, R.c_ip FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE U.ID=".$f." and U.ID=R.user_id");
		$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_prison (d_prison,c_mail,c_ip) VALUES('".date('Y-m-d H:i:s')."','".$q->user_email."','".$q->c_ip."')");
		}
	$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f));
	$wpdb->delete($wpdb->prefix.'rencontre_msg', array('sender'=>$a));
	$wpdb->delete($wpdb->prefix.'rencontre_msg', array('recipient'=>$a));
	$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$f));
	$wpdb->delete($wpdb->prefix.'users', array('ID'=>$f));
	$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$f));
	if (!is_admin()) { wp_redirect(home_url()); exit; }
	}
//
//
// Partie ADMIN dans base.php
//
?>