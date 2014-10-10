<?php
// Filtres / Action : General
add_filter('show_admin_bar' , 'f_admin_bar'); // Visualisation barre admin
add_action('init', 'prevent_admin_access', 0); // bloque acces au tableau de bord
add_action('init', 'f_inLine', 1); // session
add_action('wp_logout', 'f_outLine'); // session
add_filter('random_password', 'f_length_pass'); function f_length_pass($pass) {$pass = substr($pass,0,3); return $pass;}
add_action('admin_bar_menu', 'f_admin_menu', 999);
add_shortcode('rencontre_libre', 'f_shortcode_libre'); function f_shortcode_libre() {Rencontre::f_ficheLibre();} // shortcode : [rencontre_libre]
if (isset($_COOKIE['lang']) && strlen($_COOKIE['lang'])==5) add_filter('locale', 'set_locale2'); function set_locale2() { return $_COOKIE['lang']; }
// Mail
add_filter ('retrieve_password_message', 'retrieve_password_message2', 10, 2);
// AJAX
add_action('wp_ajax_regionBDD', 'f_regionBDD'); // AJAX - retour des regions dans le select
add_action('wp_ajax_sourire', 'f_sourire'); function f_sourire() {}
add_action('wp_ajax_voirMsg', 'f_voirMsg'); function f_voirMsg() {RencontreWidget::f_voirMsg($_POST['msg'],$_POST['alias']);}
add_action('wp_ajax_suppMsg', 'f_suppMsg'); function f_suppMsg() {RencontreWidget::f_suppMsg($_POST['msg'],$_POST['alias']);}
add_action('wp_ajax_boiteEnvoi', 'f_boiteEnvoi'); function f_boiteEnvoi() {RencontreWidget::f_boiteEnvoi($_POST['alias']);}
add_action('wp_ajax_boiteReception', 'f_boiteReception'); function f_boiteReception() {RencontreWidget::f_boiteReception($_POST['alias']);}
add_action('wp_ajax_pseudo', 'f_pseudo');
add_action('wp_ajax_iniPass', 'f_iniPass'); // premiere connexion - changement mot de passe initial et pseudo
add_action('wp_ajax_testPass', 'f_testPass'); // changement du mot de passe
add_action('wp_ajax_fbok', 'f_fbok'); add_action('wp_ajax_nopriv_fbok', 'f_fbok'); // connexion via FB
add_action('wp_ajax_miniPortrait2', 'f_miniPortrait2'); function f_miniPortrait2() {RencontreWidget::f_miniPortrait2($_POST['id']);}
add_action('wp_ajax_iso', 'f_iso'); // Test si le code ISO est libre (Partie ADMIN)
add_action('wp_ajax_drap', 'f_drap'); // SELECT avec la liste des fichiers drapeaux (Partie ADMIN)
// CRON
add_action('plugins_loaded', 'f_cron');
function f_cron()
	{
	$d = dirname(__FILE__).'/rencontre_cron.txt';
	$d1 = dirname(__FILE__).'/rencontre_cronOn.txt';
	$d2 = dirname(__FILE__).'/rencontre_cronListe.txt'; if (!file_exists($d2)) {$t=@fopen($d2,'w'); @fwrite($t,'0'); @fclose($t);}
	$d3 = dirname(__FILE__).'/rencontre_cronListeOn.txt';
	global $rencOpt;
	$t = time(); $hcron = $rencOpt['hcron']+0;
	$u1 = date("G",$t-3600*$hcron);
	if (!file_exists($d) || (date("j",filemtime($d))!=date("j",$t) && $u1<12) && $t>filemtime($d)+7200 ) // !existe ou (jour different et dans les 12 heures qui suivent hcron et plus de 2 heures apres precedent)
		{
		if (!file_exists($d1) || $t>filemtime($d1)+120)
			{
			$t=fopen($d1, 'w'); fclose($t); // CRON une seule fois
			f_cron_on();
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
function f_cron_on()
	{
	// NETTOYAGE QUOTIDIEN
	global $wpdb; global $rencOpt; global $rencDiv;
	$bn = get_bloginfo('name');
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
	// 4. Suppression fichiers anciens dans UPLOADS/SESSION/ et UPLOADS/TCHAT/
	if (!is_dir($rencDiv['basedir'].'/session/')) mkdir($rencDiv['basedir'].'/session/');
	else
		{
		$tab=''; $d=$rencDiv['basedir'].'/session/';
		if ($dh=opendir($d))
			{
			while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..') $tab[]=$d.$file; }
			closedir($dh);
			if ($tab!='') foreach ($tab as $r){if (filemtime($r)<time()-86400) unlink($r);} // 24 heures
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
			if ($tab!='') foreach ($tab as $r){if (filemtime($r)<time()-86400) unlink($r);}
			}
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
	// 7 Mail mensuel vers les membres et nettoyage des comptes actions (suppression comptes inexistants)
	$cm = 0; // compteur de mail
	$j = floor((floor(time()/86400)/60 - floor(floor(time()/86400)/60)) * 60 +.00001);
	$j1 = ($j>29)?$j-30:$j+30;
	$s1 = "";
	$max = floor(max(0, $rencOpt['qmail']*.8));
	$q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, P.t_action 
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P 
		WHERE (SECOND(U.user_registered)='".$j."' OR SECOND(U.user_registered)='".$j1."') AND U.ID=P.user_id ");
	$ct=0;
	if ($q) foreach($q as $r)
		{
		++$ct;
		$action= json_decode($r->t_action,true);
		if ($rencOpt['mailmois'] && $ct<=$max)
			{
			$s = "<div style='text-align:left;margin:5px 5px 5px 10px;'>".__('Bonjour','rencontre')."&nbsp;".$r->user_login.","."\r\n";
			if ($rencOpt['textmail'] && strlen($rencOpt['textmail'])>10) $s .= "<br />".nl2br(stripslashes($rencOpt['textmail']))."\r\n";
			$s .= "<br />".__('Votre profil a &eacute;t&eacute; visit&eacute;','rencontre')."&nbsp;".count($action['visite'])."&nbsp;".__('fois','rencontre')."\r\n";
			if (isset($action['sourireIn']) && count($action['sourireIn']))
				{
				$t = "<br />".__('Vous avez re&ccedil;u un sourire de','rencontre')."<table><tr>";
				$c = 0;
				for ($v=0; $v<count($action['sourireIn']);++$v)
					{
					$q1 = $wpdb->get_var("SELECT U.user_login FROM ".$wpdb->prefix."users U WHERE ID='".$action['sourireIn'][$v]['i']."'");
					if ($q1)
						{
						++$c;
						if (file_exists($rencDiv['basedir']."/portrait/".floor($action['sourireIn'][$v]['i']/1000)."/".($action['sourireIn'][$v]['i']*10)."-mini.jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($action['sourireIn'][$v]['i']/1000)."/".($action['sourireIn'][$v]['i']*10)."-mini.jpg";
						else $u = plugins_url('rencontre/images/no-photo60.jpg');
						$s .= $t . "<td><img src='".$u."' alt=''/><br />".substr($q1,0,10)."</td>"."\r\n";
						if ($c/6==floor($c/6)) $s .="</tr><tr>";
						$t = "";
						}
					}
				if ($t=="") $s .= "</tr></table>"."\r\n";
				}
			if (isset($action['contactIn']) && count($action['contactIn']))
				{
				$t = "<br />".__('Vous avez re&ccedil;u une demande de contact de','rencontre')."<table><tr>";
				$c = 0;
				for ($v=0; $v<count($action['contactIn']);++$v)
					{
					$q1 = $wpdb->get_var("SELECT U.user_login FROM ".$wpdb->prefix."users U WHERE ID='".$action['contactIn'][$v]['i']."'");
					if ($q1)
						{
						++$c;
						if (file_exists($rencDiv['basedir']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".($action['contactIn'][$v]['i']*10)."-mini.jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".($action['contactIn'][$v]['i']*10)."-mini.jpg";
						else $u = plugins_url('rencontre/images/no-photo60.jpg');
						$s .= $t . "<td><img src='".$u."' alt=''/><br />".substr($q1,0,10)."</td>"."\r\n";
						if ($c/6==floor($c/6)) $s .="</tr><tr>";
						$t = "";
						}
					}
				if ($t=="") $s .= "</tr></table>"."\r\n";
				}
			$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$r->user_login."' and M.read=0 and M.deleted=0");
			if ($n) $s .= "<br />".__('Vous avez','rencontre')."&nbsp;".$n.(($n>1)?__('messages','rencontre'):__('message','rencontre'))."&nbsp;".__('dans votre boite de r&eacute;ception.','rencontre');
			$s .= "<br />".__("N'h&eacute;sitez pas &agrave; nous faire part de vos remarques.",'rencontre')."<br /><br />".__('Cordialement,','rencontre')."<br />".$bn."</div>";
			$s1 .= $s;
			$he = '';
			if(!has_filter('wp_mail') && !has_filter('wp_mail_content_type'))
				{
				$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
				$he[] = 'Content-type: text/html';
				$s = '<html><head></head><body>' . $s . '</body></html>';
				}
			@wp_mail($r->user_email, $bn, $s, $he);
			++$cm;
			if (file_exists(dirname(__FILE__).'/cron_liste/'.$r->ID.'.txt')) @unlink(dirname(__FILE__).'/cron_liste/'.$r->ID.'.txt');
			}
		// *********** Nettoyage des comptes action *********
		$ac = array("sourireIn","sourireOut","contactIn","contactOut","visite","bloque");
		$x = 0;
		for ($v=0; $v<count($ac); ++$v)
			{
			if(isset($action[$ac[$v]]))
				{
				$c = count($action[$ac[$v]]);
				for ($w=0; $w<$c; ++$w)
					{
					$q1 = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$action[$ac[$v]][$w]['i']."' "); // compte suprime ?
					if(!$q1)
						{
						if(!$x) $x = 1;
						unset($action[$ac[$v]][$w]['i']); 
						unset($action[$ac[$v]][$w]['d']);
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
	// 8. anniversaire du jour
	if ($rencOpt['mailanniv'])
		{
		$q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, R.user_id FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE d_naissance LIKE '%".date('m-d')."' AND U.ID=R.user_id LIMIT 5 ");
		foreach($q as $r)
			{
			$s = "<div style='text-align:left;margin:5px 5px 5px 10px;'>".__('Bonjour','rencontre')." ".$r->user_login.","."\r\n";
			if ($rencOpt['textanniv'] && strlen($rencOpt['textanniv'])>10) $s .= "<br />".nl2br(stripslashes($rencOpt['textanniv']))."\r\n";
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
	// 9. Efface une fois par semaine les statistiques du nombre de mail par heure
	if (date("N")=="1")  // le lundi
		{
		$t=@fopen(dirname(__FILE__).'/rencontre_cronListe.txt','w'); @fwrite($t,'0'); @fclose($t);
		$t=@fopen(dirname(__FILE__).'/rencontre_cron.txt','w'); @fwrite($t,$cm); @fclose($t);
		}
	//
	if (date("N")!="1")$t=@fopen(dirname(__FILE__).'/rencontre_cron.txt', 'w'); @fwrite($t,max((file_get_contents(dirname(__FILE__).'/rencontre_cron.txt')+0),$cm)); @fclose($t);
	@unlink(dirname(__FILE__).'/rencontre_cronOn.txt');
	clearstatcache();
	}
//
function f_cron_liste($d2)
	{
	// USERS separes en 20 groupes G : (ID + G) / 20 INTEGER
	global $wpdb; global $rencOpt; global $rencDiv;
	$max = floor(max(0, $rencOpt['qmail']*.8));
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
			$lis .= $r[1].",";
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
			$s = "<div style='text-align:left;margin:5px 5px 5px 10px;color:#000;'>".__('Bonjour','rencontre')."&nbsp;".$r->user_login.","."\r\n";
			if (count($action['contactIn']))
				{
				$b = 1;
				$s .= "<br />".__('Vous avez re&ccedil;u une demande de contact de','rencontre')."<table><tr>";
				$v = count($action['contactIn'])-1;
				$q1 = $wpdb->get_var("SELECT U.user_login FROM ".$wpdb->prefix."users U WHERE ID='".$action['contactIn'][$v]['i']."'");
				if ($q1)
					{
					if (file_exists($rencDiv['basedir']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".($action['contactIn'][$v]['i']*10)."-mini.jpg")) $u = $rencDiv['baseurl']."/portrait/".floor($action['contactIn'][$v]['i']/1000)."/".($action['contactIn'][$v]['i']*10)."-mini.jpg";
					else $u = plugins_url('rencontre/images/no-photo60.jpg');
					$s .= "<td>".$q1."</td><td><img src='".$u."' alt=''/></td>"."\r\n";
					}
				$s .= "</tr></table>"."\r\n";
				}
			$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$r->user_login."' and M.read=0 and M.deleted=0");
			if ($n)
				{
				$b = 1;
				$s .= "<br />".__('Vous avez','rencontre')."&nbsp;".$n."&nbsp;".(($n>1)?__('messages','rencontre'):__('message','rencontre'))."&nbsp;".__('dans votre boite de r&eacute;ception.','rencontre');
				}
			$s .= "<br /><br />".__('Cordialement,','rencontre')."<br />".$bn."</div>";
			if($b)
				{
				$he = '';
				if(!has_filter('wp_mail') && !has_filter('wp_mail_content_type'))
					{
					$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
					$he[] = 'Content-type: text/html';
					$s = '<html><head></head><body>' . $s . '</body></html>';
					}
				@wp_mail($r->user_email, $bn." - ".__('Un membre vous contacte','rencontre'), $s, $he);
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
		'href'=>admin_url('admin.php?page=membres'),
		'meta'=>array('class'=>'rencontre',
		'title'=>'Rencontre'));
	$wp_admin_bar->add_node($args);
	}
//
function f_inLine()
	{
	if (is_user_logged_in())
		{
		if (!session_id()) session_start();
		global $current_user; global $rencDiv;
		if (!is_dir($rencDiv['basedir'].'/tchat/')) mkdir($rencDiv['basedir'].'/tchat/');
		if (!is_dir($rencDiv['basedir'].'/session/')) mkdir($rencDiv['basedir'].'/session/');
		$t = fopen($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt', 'w') or die();
		fclose($t);
		}
	}
//
function f_outLine()
	{
	global $current_user; global $rencDiv;
	if (file_exists($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt')) unlink($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt');
	session_destroy();
	}
//
function prevent_admin_access()
	{
	global $rencDiv;
	$a=strtolower($_SERVER['REQUEST_URI']);
	if (strpos($a,'/wp-admin')!==false && strpos($a,'admin-ajax.php')==false && !current_user_can("administrator")) { wp_redirect($rencDiv['siteurl']); exit; }
	}
function f_admin_bar($content) { return (current_user_can("administrator")) ? $content : false; }
function f_regionBDD()
	{ 
	echo '<option value="">- '.__('Indiff&eacute;rent','rencontre').' -</option>';
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
	if (!$q) echo 'ok';
	else echo 'nok';
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
	$message = __('Une personne a demand&eacute; un nouveau mot de passe pour ce compte.','rencontre')."<br />";
	$message .= network_site_url()."<br /><br />";
	$message .= sprintf(__('Login : %s','rencontre'), $u->user_login)."<br /><br />";
	$message .= __('Le mot de passe a &eacute;t&eacute; chang&eacute;. Vous pouvez maintenant vous connecter et le changer &agrave; nouveau depuis votre interface si vous le souhaitez.','rencontre')."<br /><br />";
	$message .= __('Nouveau mot de passe : ','rencontre').$p;
	return $message;
	}
//
function f_iniPass()
	{
	wp_set_password($_POST['nouv'],$_POST['id']);
	global $wpdb;
	$wpdb->update($wpdb->prefix.'users', array(
		'user_login'=>strip_tags($_POST['pseudo']),
		'user_nicename'=>strip_tags($_POST['pseudo']),
		'display_name'=>strip_tags($_POST['pseudo'])), 
		array('ID'=>strip_tags($_POST['id'])));
	wp_set_auth_cookie($_POST['id']);
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
// Partie ADMIN
//
?>