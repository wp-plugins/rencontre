<?php
// *****************************************
// **** ONGLET GENERAL
// *****************************************
function f_exportCsv()
	{
	// Export CSV de la base des membres
	if(!is_admin()) die;
	global $wpdb; global $rencDiv;
	$q = $wpdb->get_results("SELECT
			U.ID,
			U.user_login,
			U.user_pass,
			U.user_email,
			U.user_registered,
			R.c_ip,
			R.c_pays,
			R.c_region,
			R.c_ville,
			R.i_sex,
			R.d_naissance,
			R.i_taille,
			R.i_poids,
			R.i_zsex,
			R.i_zage_min,
			R.i_zage_max,
			R.i_zrelation,
			R.i_photo,
			P.t_titre,
			P.t_annonce
		FROM 
			".$wpdb->prefix."users U,
			".$wpdb->prefix."rencontre_users R,
			".$wpdb->prefix."rencontre_users_profil P
		WHERE 
			U.ID=R.user_id and 
			R.user_id=P.user_id
		");
	$rd = mt_rand();
	$d = $rencDiv['basedir'].'/tmp/';
	if (!is_dir($d)) mkdir($d);
	if (is_dir($d.'photo_export/'))
		{
		array_map('unlink', glob($d."photo_export/*.*"));
		}
	else mkdir($d.'photo_export/');
	$t=fopen($d.'index.php', 'w'); fclose($t);
	$t = fopen($d.$rd.'export_rencontre.csv', 'w');
	fputcsv($t, array('user_login','user_pass (MD5)','user_email','user_registered (AAAA-MM-DD HH:MM:SS)','c_ip','c_pays (2 letters ISO)','c_region','c_ville','i_sex (girl, men)','d_naissance (AAAA-MM-DD)','i_taille','i_poids','i_zsex (girl, men)','i_zage_min','i_zage_max','i_zrelation (open, friendly, serious)','i_photo','t_titre','t_annonce'));
	foreach($q as $r)
		{
		fputcsv($t, array(
			"'".$r->user_login."'",
			"'".$r->user_pass."'",
			"'".$r->user_email."'",
			"'".$r->user_registered."'",
			"'".$r->c_ip."'",
			"'".$r->c_pays."'",
			"'".$r->c_region."'",
			"'".$r->c_ville."'",
			"'".(($r->i_sex)?'girl':'men')."'",
			"'".$r->d_naissance."'",
			"'".$r->i_taille."'",
			"'".$r->i_poids."'",
			"'".(($r->i_zsex)?'girl':'men')."'",
			"'".$r->i_zage_min."'",
			"'".$r->i_zage_max."'",
			"'".(($r->i_zrelation)?(($r->i_zrelation==1)?'open':'friendly'):'serious')."'",
			"'".(($r->i_photo)?(($r->ID)*10).'.jpg':'0')."'",
			"'".$r->t_titre."'",
			"'".$r->t_annonce."'"
			),chr(9));
		if($r->i_photo) @copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.(($r->ID)*10).'.jpg', $rencDiv['basedir'].'/tmp/photo_export/'.($r->ID*10).'.jpg');
		}
	fclose($t);
	echo $rd;
	}
function f_importCsv()
	{
	// Import CSV de la base des membres
		// 0 : login
		// 1 : pass MD5
		// 2 : email
		// 3 : user_registered (AAAA-MM-JJ HH:MM:SS)
		// 4 : IP
		// 5 : Pays 2 lettres MAJ
		// 6 : Region
		// 7 : Ville
		// 8 : sex (men / girl)
		// 9 : date naissance AAAA-MM-JJ
		// 10 : taille
		// 11 : poids
		// 12 : sex recherche (men / girl)
		// 13 : age min recherche
		// 14 : age max recherche
		// 15 : type de relation recherche (open / friendly / serious)
		// 16 : fichier photo (ou 0)
		// 17 : titre
		// 18 : Annonce
	if(!is_admin()) die;
	if(isset($_POST['cas']) && $_POST['cas']=='2') // premier passage
		{
		global $wpdb; global $rencDiv;
		$d=$rencDiv['basedir'].'/tmp/import_rencontre.csv';
		$p=0;
		if(is_dir($rencDiv['basedir'].'/tmp/photo_import/'))
			{
			$p=1;
			chmod($rencDiv['basedir'].'/tmp/photo_import/',0777);
			}
		ini_set('auto_detect_line_endings',TRUE); // cas des Mac
		$t=fopen($d,'r');
		$c=0; $c1=0;
		while(($a=fgetcsv($t,3000,"\t"))!==FALSE)
			{
			foreach($a as $k=>$r)
				{
				if(substr($r,0,1)=="'") $a[$k]=substr($r,1,-1); // suppression des guillemets
				}
			if($c) $q = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."users WHERE user_login='".$a[0]."' OR user_email='".$a[2]."' ");
			if($c && !$q && preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",$a[2])) // pas la premiere ligne - pas de doublon
				{
				$wpdb->insert($wpdb->prefix.'users',array(
					'user_login'=>str_replace("'","",$a[0]),
					'user_pass'=>$a[1],
					'user_nicename'=>$a[0],
					'user_email'=>$a[2],
					'user_registered'=>$a[3],
					'user_status'=>0,
					'display_name'=>$a[0]
					));
				$id=$wpdb->insert_id;
				$wpdb->insert($wpdb->prefix.'rencontre_users',array(
					'user_id'=>$id,
					'c_ip'=>$a[4],
					'c_pays'=>$a[5],
					'c_region'=>$a[6],
					'c_ville'=>$a[7],
					'i_sex'=>(($a[8]=='men')?0:1),
					'd_naissance'=>$a[9],
					'i_taille'=>(($a[10])?$a[10]:170),
					'i_poids'=>(($a[11])?$a[11]:65),
					'i_zsex'=>(($a[12]=='girl')?1:0),
					'i_zage_min'=>(($a[13])?$a[13]:18),
					'i_zage_max'=>(($a[14])?$a[14]:99),
					'i_zrelation'=>(($a[15]=='serious')?0:(($a[15]=='open')?1:2)), // ( serious (0) / open (1) / friendly (2))
					'i_photo'=>0
					));
				$wpdb->insert($wpdb->prefix.'rencontre_users_profil',array(
					'user_id'=>$id,
					'd_modif'=>date("Y-m-d H:i:s"),
					't_titre'=>$a[17],
					't_annonce'=>$a[18],
					't_profil'=>'[]'
					));
				if($p && strlen($a[16])>3 && file_exists($rencDiv['basedir'].'/tmp/photo_import/'.$a[16]))
					{
					$t1=fopen($rencDiv['basedir'].'/tmp/photo_import/'.$id.'.txt', 'w+');
					fwrite($t1,$a[16],40);
					fclose($t1);
					++$c1;
					}
				}
			++$c;
			}
		ini_set('auto_detect_line_endings',FALSE); // Mac
		fclose($t);
		@unlink(dirname(__FILE__).'/../cache/cache_portraits_accueil.html');
		echo (($c1)?$c1:999999);
		}
	else if(isset($_POST['cas']) && $_POST['cas']=='1')
		{
		global $wpdb; global $rencDiv;
		$p=(is_dir($rencDiv['basedir'].'/tmp/photo_import/')?$rencDiv['basedir'].'/tmp/photo_import/':0);
		if(!is_dir($rencDiv['basedir'].'/portrait/')) @mkdir($rencDiv['basedir'].'/portrait/');
		$tab='';
		if ($p && $dh=opendir($p))
			{
			$c=0;
			while (($file=readdir($dh))!==false)
				{
				$ext=explode('.',$file);
				$ext=$ext[count($ext)-1];
				if ($ext=='txt' && $file!='.' && $file!='..')
					{
					$t=fopen($p.$file, 'r'); $img=fread($t,filesize($p.$file)); fclose($t);
					RencontreWidget::f_photo(intval(substr($file,0,-4).'0'),$p.$img);
					$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>substr($file,0,-4).'0'), array('user_id'=>substr($file,0,-4)));
					unlink($p.$file);
					++$c;
					if($c>24) break;
					}
				}
			closedir($dh);
			}
		echo $c;
		return;
		}
	}
// *****************************************
// **** ONGLET PROFIL
// *****************************************
function profil_supp($a2,$a3,$a4)
	{
	// a2 : ID - a3 : colonne - a4 :
	if(!is_admin()) die;
	global $wpdb;
	if ($a3=="c_categ") 
		{
		$q = $wpdb->get_var("SELECT c_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."'");
		$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_profil WHERE c_categ='".$q."'");
		$t=fopen(dirname(__FILE__).'/rencontre_synchronise.txt', 'a'); fclose($t); // info modif (vide)
		}
	else if ($a3=="c_label")
		{
		$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."'");
		$t=fopen(dirname(__FILE__).'/rencontre_synchronise.txt', 'a'); fclose($t); // info modif (vide)
		}
	else if ($a3=="t_valeur") 
		{
		$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."'");
		foreach($q as $qr)
			{
			$r =  json_decode($qr->t_valeur);
			unset($r[$a4]);
			$s = '['; foreach ($r as $rr) {$s .='"'. $rr . '",';} $s = substr($s,0,-1) . "]";	$s = str_replace("'", "&#39;", $s);
			$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET t_valeur='".$s."', i_poids='".(($qr->i_poids<5)?($qr->i_poids+5):($qr->i_poids))."' WHERE id='".$a2."' AND c_lang='".$qr->c_lang."' ");
			}
		$t=fopen(dirname(__FILE__).'/rencontre_synchronise.txt', 'a'); fwrite($t,'{"id":'.$a2.',"key":'.$a4.'},'); fclose($t); // info modif (id & key)
		}
	}
//
function profil_edit($a2,$a3,$a4,$a5,$a6)
	{
	// a2 : ID - a3 : colonne - a4 : valeur colonne - a5 : position (select ou check) - a6 : type
	if(!is_admin()) die;
	global $wpdb;
	$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
	$b4 = explode('&',substr($a4, 0, -1));
	$c4 = Array();
	foreach($b4 as $b)
		{
		$t=explode('=',$b);
		if ($t)
			{
			$c4[] = array('a'=>$t[0], 'b'=>$t[1]);
			}
		}
	if ($a3=="c_categ")
		{
		$q = $wpdb->get_results("SELECT c_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
		foreach($q as $qr)
			{
			for($v=0;$v<count($c4);++$v)
				{
				$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET c_categ='".$c4[$v]['b']."' WHERE c_categ='".$qr->c_categ."' AND c_lang='".$c4[$v]['a']."' ");
				}
			}
		}
	else if ($a3=="c_label")
		{
		if ($a6==1 || $a6==2)
			{
			$q = $wpdb->get_var("SELECT i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
			for($v=0;$v<count($c4);++$v)
				{
				$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET c_label='".$c4[$v]['b']."', t_valeur='' , i_type='".$a6."', i_poids='".(($q<5)?($q+5):$q)."' WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
				}
			}
		elseif ($a6==3 || $a6==4)
			{
			$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
			foreach($q as $qr)
				{
				for($v=0;$v<count($c4);++$v)
					{
					if($c4[$v]['a']==$qr->c_lang)
						{
						$a = $qr->t_valeur;
						if ($a=="") $a = '["*** '. __('TO CHANGE','rencontre').' ***"]';
						$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET c_label='".$c4[$v]['b']."', t_valeur='".$a."', i_type='".$a6."', i_poids='".(($q->i_poids<5)?($q->i_poids+5):$q->i_poids)."' WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
						}
					}
				}
			}
		$t=fopen(dirname(__FILE__).'/rencontre_synchronise.txt', 'a'); fwrite($t,'{"id":'.$a2.',"key":-1},'); fclose($t); // info modif (suppression de cet id)
		}
	else if ($a3=="t_valeur") 
		{
		$q = $wpdb->get_results("SELECT t_valeur, c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
		foreach($q as $qr)
			{
			$r =  json_decode($qr->t_valeur);
			for($v=0;$v<count($c4);++$v)
				{
				if($c4[$v]['a']==$qr->c_lang)
					{
					$r[$a5] = $c4[$v]['b']; // a5 : indice
					$s = '['; foreach ($r as $rr) {$s .='"'. $rr . '",';} $s = substr($s,0,-1) . "]"; $s = str_replace("'", "&#39;", $s);
					$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET t_valeur='".$s."' WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
					}
				}
			}
		}
	}
//
function profil_plus($a2,$a3,$a4,$a5)
	{
	// a5 : langues separees par &
	if(!is_admin()) die;
	global $wpdb;
	$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
	$b4 = explode('&',substr($a4, 0, -1));
	$c4 = Array();
	foreach($b4 as $b)
		{
		$t=explode('=',$b);
		if ($t)
			{
			$c4[] = array('a'=>$t[0], 'b'=>$t[1]);
			}
		}
	if ($a3=="c_categ")
		{
		for($v=0;$v<count($c4);++$v)
			{
			if($v==0)
				{
				$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$c4[$v]['b']."','*** ". __('TO CHANGE','rencontre')." ***','',1,0,'".$c4[$v]['a']."')");
				$lastid = $wpdb->insert_id;
				}
			else $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$lastid."','".$c4[$v]['b']."','*** ". __('TO CHANGE','rencontre')." ***','',1,0,'".$c4[$v]['a']."')");
			}
		}
	else if ($a3=="c_label") 
		{
		for($v=0;$v<count($c4);++$v)
			{
			$q = $wpdb->get_var("SELECT c_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
			if($v==0)
				{
				$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$q."','".$c4[$v]['b']."','',1,0,'".$c4[$v]['a']."')");
				$lastid = $wpdb->insert_id;
				}
			else $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$lastid."','".$q."','".$c4[$v]['b']."','',1,0,'".$c4[$v]['a']."')");
			}
		}
	else if ($a3=="t_valeur") 
		{
		for($v=0;$v<count($c4);++$v)
			{
			$q = $wpdb->get_var("SELECT t_valeur FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
			$s = substr($q,0,-1) . ",\"" . $c4[$v]['b'] . "\"]"; $s = str_replace("'", "&#39;", $s);
			$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET t_valeur='".$s."' WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
			}
		}
	}
//
function profil_langplus($loc,$a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$q = $wpdb->get_var("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$a4."' ");
	if (!$q)
		{
		$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$loc."' ORDER BY id");
		if(!$q) $q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='en' ORDER BY id");
		foreach($q as $r)
			{
			if($r->t_valeur=='') $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$r->id."','?','?','','".$r->i_type."','".$r->i_poids."','".$a4."')");
			else
				{
				$s='['; for($v=0;$v<count(json_decode($r->t_valeur));++$v) {$s.='"?",';} $s = str_replace("'", "&#39;", $s);
				$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$r->id."','?','?','".substr($s,0,-1)."]"."','".$r->i_type."','".$r->i_poids."','".$a4."')");
				}
			}
		}
	}
//
function profil_langsupp($a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$a4."'");
	}
//
function profil_defaut()
	{
	// chargement des profils par defaut
	if(!is_admin()) die;
	$f = file_get_contents(plugin_dir_path( __FILE__ ).'rencontre_profil_defaut.txt');
	global $wpdb;
	$wpdb->query('INSERT INTO '.$wpdb->prefix.'rencontre_profil (id, c_categ, c_label, t_valeur, i_type, i_poids, c_lang) VALUES '.$f);
	$g = $wpdb->get_var("SELECT MAX(id) FROM ".$wpdb->prefix."rencontre_profil");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_profil AUTO_INCREMENT = ".$g);
	}
//
function liste_defaut()
	{
	// chargement des pays et regions par defaut
	if(!is_admin()) die;
	$f = file_get_contents(plugin_dir_path( __FILE__ ).'rencontre_liste_defaut.txt');
	global $wpdb;
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste AUTO_INCREMENT = 1");
	$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ, c_liste_valeur, c_liste_iso, c_liste_lang) VALUES ".$f);
		// **** PATCH V1.2 : langue pour les pays *****************************************
			$q = $wpdb->get_results("SELECT user_id, c_pays FROM ".$wpdb->prefix."rencontre_users");
			foreach($q as $r)
				{
				if(strlen($r->c_pays)>2)
					{
					$iso = $wpdb->get_var("SELECT c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_valeur='".$r->c_pays."' ");
					if($iso) $wpdb->update($wpdb->prefix.'rencontre_users', array('c_pays'=>$iso), array('user_id'=>$r->user_id));
					else $wpdb->update($wpdb->prefix.'rencontre_users', array('c_pays'=>'CI'), array('user_id'=>$r->user_id)); // et pourquoi pas !
					}
				}
		// ************************************************************************************
	}
//
function synchronise()
	{
	// Sur le compte de chaque utilisateur (rencontre_users_profil) : supprime les ID inexistants dans la colonne t_profil.
	if(!is_admin()) die;
	if(file_exists(dirname(__FILE__).'/rencontre_synchronise.txt'))
		{
		$sync = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.txt'); // format : {"id":31,"key":2},{"id":31,"key":2},
		if(strlen($sync)>5) $sync=json_decode('['.substr($sync,0,-1).']',true); else $sync=null;
		global $wpdb;
		$q = $wpdb->get_results("SELECT user_id, t_profil FROM ".$wpdb->prefix."rencontre_users_profil WHERE CHAR_LENGTH(t_profil)>5");
		$q1 = $wpdb->get_results("SELECT DISTINCT(id) FROM ".$wpdb->prefix."rencontre_profil");
		$t=',';
		foreach($q1 as $r1) { $t.=$r1->id.","; } // liste des id de profil existants : $t = ",1,2,4,5,12,15,"
		foreach($q as $r)
			{ // boucle users
			$profil = json_decode($r->t_profil,true); $b=0;
			if($profil) foreach($profil as $k2=>$r2)
				{ // boucle profil users
				// 1. suppression id inexistantes
				if(strpos($t,",".$r2['i'].",")===false) {unset($profil[$k2]); $b=1;}
				// 2. remise en ordre des options et checkbox (sinon decallage)
				if($sync) foreach($sync as $k3=>$r3)
					{
					if($r2['i']==$r3['id'])
						{
						if(is_array($r2['v'])) $r2['v'] = json_encode($r2['v']);
						if($r3['key']==-1) {unset($profil[$k2]); $b=1;} // le type a change - suppression
						else if(strpos($r2['v'],"]")===false) // cas 1, 2 et 3
							{
							if($r2['v']>$r3['key']) {$profil[$k2]['v']--; $b=1;}
							else if($r2['v']==$r3['key']) {unset($profil[$k2]); $b=1;}
							}
						else // cas 4 checkbox
							{
							$u = explode(",",substr($r2['v'],1,-1)); $v = "[";
							foreach($u as $k4=>$r4)
								{
								if($r4>$r3['key']) {$v.=($r4-1).','; $b=1;}
								else if($r4==$r3['key']) {$b=1;}
								else if($r4<$r3['key']) {$v.=($r4).','; $b=1;}
								}
							$profil[$k2]['v'] = substr($v,0,-1)."]";
							}
						}
					}
				}
			if($b==1)
				{
				$profil2=array(); foreach ($profil as $k=>$r2) { $profil2[]=$r2; } // reorder pour eviter apparition de key dans le JSON
				$c= json_encode($profil2);
				$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_profil'=>$c), array('user_id'=>$r->user_id));
				}
			}
		$q = $wpdb->get_results("SELECT id, i_poids, c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE i_poids>4 ");
		foreach($q as $r)
			{
			$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET i_poids='".($r->i_poids-5)."' WHERE id='".$r->id."' AND c_lang='".$r->c_lang."' ");
			}
		unlink(dirname(__FILE__).'/rencontre_synchronise.txt');
		}
	}
//
// *****************************************
// **** ONGLET REGION
// *****************************************
function liste_edit($a2,$a3,$a4,$a5,$a6)
	{
	// a2 : iso/id - a3 : colonne - a4 : valeur colonne - a5 : position (select ou check) - a6 : type
	if(!is_admin()) die;
	global $wpdb;
	if ($a3=="p")
		{
		$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
		$b4 = explode('&',substr($a4, 0, -1));
		foreach($b4 as $b)
			{
			$t=explode('=',$b);
			if ($t) $wpdb->update($wpdb->prefix.'rencontre_liste', array('c_liste_valeur'=>ucwords(stripslashes($t[1]))), array('c_liste_iso'=>$a2, 'c_liste_lang'=>$t[0]));
			}
		}
	else if ($a3=="r") $wpdb->update($wpdb->prefix.'rencontre_liste', array('c_liste_valeur'=>ucwords(stripslashes($a4))), array('id'=>$a2));
	}
//
function liste_supp($a2,$a3,$a4)
	{
	// a2 : ID - a3 : colonne - a4 :
	if(!is_admin()) die;
	global $wpdb;
	if ($a3=="p") $wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$a2."' ");
	else if ($a3=="r") $wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_liste WHERE id='".$a2."' and c_liste_categ='r' ");
	}
//
function liste_plus($a2,$a3,$a4,$a5,$a6)
	{
	// a5 : langues separees par &
	if(!is_admin()) die;
	global $wpdb;
	if ($a3=="p" && strlen($a5)==2)
		{
		$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
		$b4 = explode('&',substr($a4, 0, -1));
		foreach($b4 as $b)
			{
			$t=explode('=',$b);
			if ($t) $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('p','".ucwords($t[1])."','".$a5."','".$t[0]."')");
			}
		$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('d','".$a6."','".$a5."','')");
		}
	else if ($a3=="r") $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('r','".ucwords(urldecode($a4))."','".$a2."','')");
	}
//
function liste_langplus($loc,$a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$q = $wpdb->get_var("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".$a4."' ");
	if (!$q)
		{
		$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_lang='".$loc."' ORDER BY id");
		if(!$q) $q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_lang='en' ORDER BY id");
		foreach($q as $r)
			{
			$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('p','?','".$r->c_liste_iso."','".$a4."')");
			}
		}
	}
//
function liste_langsupp($a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".$a4."'");
	}
//
// *****************************************
// **** AUTRES
// *****************************************
function f_userPrison($f)
	{
	// $f : id table rencontre_prison
	if (!is_admin()) exit;
	global $wpdb;
	$wpdb->delete($wpdb->prefix.'rencontre_prison', array('id'=>$f));
	}
function sauvProfilAdm($in,$id)
	{
	// Copie de la fonction dans rencontre_widget avec POST au lieu de GET
	// entree : Sauvegarde du profil
	// sortie bdd : [{"i":10,"v":"Sur une ile deserte avec mon amoureux."},{"i":35,"v":0},{"i":53,"v":[0,4,6]}]
	$u = "";
	if($in) foreach ($in as $r=>$r1) 
		{
		switch ($r1[0])
			{
			case 1: if ($_POST['text'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['text'.$r]))).'"},'; break;
			case 2: if ($_POST['area'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['area'.$r]))).'"},'; break;
			case 3: if ($_POST['select'.$r]>0) $u.='{"i":'.$r.',"v":'.(strip_tags($_POST['select'.$r]-1)).'},'; break;
			case 4: if (!empty($_POST['check'.$r])) {$u.='{"i":'.$r.',"v":['; foreach ($_POST['check'.$r] as $r2) { $u.=$r2.',';} $u=substr($u, 0, -1).']},';} break;
			}
		}
	global $wpdb;
	$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('d_modif'=>date("Y-m-d H:i:s"),'t_titre'=>strip_tags(stripslashes($_POST['titre'])),'t_annonce'=>strip_tags(stripslashes($_POST['annonce'])),'t_profil'=>'['.substr($u, 0, -1).']'), array('user_id'=>$id));
	}
//
//
?>