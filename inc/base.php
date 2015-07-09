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
		if($r->i_photo) @copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.Rencontre::f_img((($r->ID)*10)).'.jpg', $rencDiv['basedir'].'/tmp/photo_export/'.($r->ID*10).'.jpg');
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
			@chmod($rencDiv['basedir'].'/tmp/photo_import/',0777);
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
						$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET c_label='".$c4[$v]['b']."', t_valeur='".$a."', i_type='".$a6."', i_poids='".(($qr->i_poids<5)?($qr->i_poids+5):$qr->i_poids)."' WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
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
// **** TAB ADMIN
// *****************************************
function update_rencontre_options($f)
	{
	global $rencOpt;
	if (isset($f['facebook'])) $rencOpt['facebook'] = stripslashes($f['facebook']); else $rencOpt['facebook'] = '';
	if (isset($f['fblog'])) $rencOpt['fblog'] = $f['fblog']; else $rencOpt['fblog'] = '';
	if (isset($f['home'])) $rencOpt['home'] = $f['home']; else $rencOpt['home'] = "";
	if (isset($f['pays'])) $rencOpt['pays'] = $f['pays']; else $rencOpt['pays'] = "";
	if (isset($f['limit'])) $rencOpt['limit'] = $f['limit']; else $rencOpt['limit'] = 20;
	if (isset($f['jlibre'])) $rencOpt['jlibre'] = $f['jlibre']; else $rencOpt['jlibre'] = 0;
	if (isset($f['prison'])) $rencOpt['prison'] = $f['prison']; else $rencOpt['prison'] = 30;
	if (isset($f['tchat'])) $rencOpt['tchat'] = 1; else $rencOpt['tchat'] = 0;
	if (isset($f['map'])) $rencOpt['map'] = 1; else $rencOpt['map'] = 0;
	if (isset($f['hcron'])) $rencOpt['hcron'] = $f['hcron']; else $rencOpt['hcron'] = 3;
	if (isset($f['mailmois'])) $rencOpt['mailmois'] =  $f['mailmois']; else $rencOpt['mailmois'] = 0;
	if (isset($f['textmail'])) $rencOpt['textmail'] = $f['textmail']; else $rencOpt['textmail'] = '';
	if (isset($f['mailsmile'])) $rencOpt['mailsmile'] = 1; else $rencOpt['mailsmile'] = 0;
	if (isset($f['mailanniv'])) $rencOpt['mailanniv'] = 1; else $rencOpt['mailanniv'] = 0;
	if (isset($f['textanniv'])) $rencOpt['textanniv'] = $f['textanniv']; else $rencOpt['textanniv'] = '';
	if (isset($f['qmail'])) $rencOpt['qmail'] = $f['qmail']; else $rencOpt['qmail'] = 25;
	if (isset($f['npa'])) $rencOpt['npa'] = $f['npa']; else $rencOpt['npa'] = 12;
	if (isset($f['imnb'])) $rencOpt['imnb'] = $f['imnb']; else $rencOpt['imnb'] = 4;
	if (isset($f['imcode'])) $rencOpt['imcode'] = $f['imcode']; else $rencOpt['imcode'] = rencImEncoded();
	if (isset($f['imcopyright'])) $rencOpt['imcopyright'] = $f['imcopyright']; else $rencOpt['imcopyright'] = 0;
	if (isset($f['txtcopyright'])) $rencOpt['txtcopyright'] = stripslashes($f['txtcopyright']); else $rencOpt['txtcopyright'] = ""; 
	if (isset($f['anniv'])) $rencOpt['anniv'] = 1; else $rencOpt['anniv'] = 0;
	if (isset($f['ligne'])) $rencOpt['ligne'] = 1; else $rencOpt['ligne'] = 0;
	if (isset($f['mailsupp'])) $rencOpt['mailsupp'] = 1; else $rencOpt['mailsupp'] = 0;
	if (isset($f['onlyphoto'])) $rencOpt['onlyphoto'] = 1; else $rencOpt['onlyphoto'] = 0;
	update_option('rencontre_options',$rencOpt);
	}
//
function rencMenuGeneral()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	if (isset($_POST['facebook']) || isset($_POST['npa'])) update_rencontre_options($_POST);
	global $rencOpt; global $rencDiv; global $rencVersion;
	$a=array();
	if(!is_dir($rencDiv['basedir'].'/tmp/')) mkdir($rencDiv['basedir'].'/tmp/');
	if($h=opendir($rencDiv['basedir']."/tmp/"))
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
	?>
	<div class='wrap'>
		<div class='icon32' id='icon-options-general'><br/></div>
		<?php if(file_exists(dirname(__FILE__).'/rencontre_don.php')) include(dirname(__FILE__).'/rencontre_don.php'); ?>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('General', 'rencontre'); ?></h2>
		<form method="post" name="rencontre_options" action="admin.php?page=rencontre.php">
			<table class="form-table" style="max-width:600px;clear:none;">
				<tr valign="top">
					<th scope="row"><label><?php _e('Framework for the Facebook Like button', 'rencontre'); ?></label></th>
					<td><textarea  name="facebook"><?php echo $rencOpt['facebook']; ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('AppID for Facebook connection (empty if not installed)', 'rencontre'); ?></label></th>
					<td><input type="text" class="regular-text" name="fblog" value="<?php echo $rencOpt['fblog']; ?>" /></td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label><?php _e('Page where is settled the plugin', 'rencontre'); ?></label></th>
					<td>
						<select name="home">
							<option value="" <?php echo ($rencOpt['home']?'':'selected'); ?>>Index</option>
							<?php $pages = get_pages(); $tmp = '';
							foreach($pages as $page) { $tmp .= '<option value="'.get_page_link($page->ID).'" '.($rencOpt['home']==get_page_link($page->ID)?'selected':'').'>'.$page->post_title.'</option>'; }
							echo $tmp; ?>

						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Country selected by default', 'rencontre'); ?></label></th>
					<td>
						<select name="pays">
						<?php RencontreWidget::f_pays($rencOpt['pays']); ?>
						</select>
					</td>
				</tr>
			
				<tr valign="top">
					<th scope="row"><label><?php _e('Number of portrait homepage unconnected', 'rencontre'); ?></label></th>
					<td><input type="text" class="regular-text" name="npa" value="<?php echo $rencOpt['npa']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Number of days to wait before presence homepage', 'rencontre'); ?></label></th>
					<td><input type="text" class="regular-text" name="jlibre" value="<?php echo $rencOpt['jlibre']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Number of days in jail (deleted account)', 'rencontre'); ?></label></th>
					<td><input type="text" class="regular-text" name="prison" value="<?php echo $rencOpt['prison']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Max number of results per search', 'rencontre'); ?></label></th>
					<td><input type="text" class="regular-text" name="limit" value="<?php echo $rencOpt['limit']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Today\'s birthday', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="anniv" value="1" <?php if (isset($rencOpt['anniv'])&&$rencOpt['anniv'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Profiles currently online', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="ligne" value="1" <?php if (isset($rencOpt['ligne'])&&$rencOpt['ligne'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Enable chat', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="tchat" value="1" <?php if (isset($rencOpt['tchat'])&&$rencOpt['tchat'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Enable Google-Map', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="map" value="1" <?php if (isset($rencOpt['map'])&&$rencOpt['map'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Number of photos', 'rencontre'); ?></label></th>
					<td>
						<select name="imnb">
							<?php if(!isset($rencOpt['imnb']) || $rencOpt['imnb']<1 || $rencOpt['imnb']>8) $rencOpt['imnb']=4;
							for($v=1; $v<9; ++$v)
								{
								echo '<option value="'.$v.'"'.(($rencOpt['imnb']==$v)?' selected':'').'>'.$v.'</option>';
								} ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('View a discrete copyright on photos', 'rencontre'); ?></label></th>
					<td>
						<select name="imcopyright">
							<option value="0" <?php if (!$rencOpt['imcopyright'])echo 'selected'; ?>><?php _e('No', 'rencontre'); ?></option>
							<option value="1" <?php if ($rencOpt['imcopyright']==1)echo 'selected'; ?>><?php _e('Upwardly inclined', 'rencontre'); ?></option>
							<option value="2" <?php if ($rencOpt['imcopyright']==2)echo 'selected'; ?>><?php _e('Downwardly inclined', 'rencontre'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Copyright text on pictures. Empty => Site URL.', 'rencontre'); ?></label></th>
					<td><input type="text" name="txtcopyright" value="<?php echo $rencOpt['txtcopyright']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Members without photo less visible', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="onlyphoto" value="1" <?php if ($rencOpt['onlyphoto'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Send an email to the user whose account is deleted', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="mailsupp" value="1" <?php if ($rencOpt['mailsupp'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Automatic sending a summary email to members (shared daily)', 'rencontre'); ?></label></th>
					<td>
						<select name="mailmois">
							<option value="0" <?php if (!$rencOpt['mailmois'])echo 'selected'; ?>><?php _e('No', 'rencontre'); ?></option>
							<option value="1" <?php if ($rencOpt['mailmois']==1)echo 'selected'; ?>><?php _e('Monthly', 'rencontre'); ?></option>
							<option value="2" <?php if ($rencOpt['mailmois']==2)echo 'selected'; ?>><?php _e('Fortnightly', 'rencontre'); ?></option>
							<option value="3" <?php if ($rencOpt['mailmois']==3)echo 'selected'; ?>><?php _e('Weekly', 'rencontre'); ?></option>
						</select>
						<?php 
						$d2 = dirname(__FILE__).'/../inc/rencontre_cron.txt';
						if (file_exists($d2)) echo "<p style='color:#D54E21;'>".__('Up this week', 'rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".file_get_contents($d2)."</span>&nbsp;".__('mail/hour', 'rencontre')."<br />(".__('sent during maintenance', 'rencontre').")</p>";
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Hour maintenance tasks (off peak)', 'rencontre'); ?></label></th>
					<td>
						<select name="hcron">
							<?php for ($v=0;$v<24;++$v) {echo '<option value="'.$v.'" '.(($rencOpt['hcron']==$v)?'selected':'').'>&nbsp;'.$v.__('hours','rencontre').'</option>';} ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Introductory text for the monthly email (After hello login - Before the smiles and contact requests)', 'rencontre'); ?></label></th>
					<td><textarea name="textmail"><?php echo stripslashes($rencOpt['textmail']); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Also send an email for a smile', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="mailsmile" value="1" <?php if(isset($rencOpt['mailsmile']) && $rencOpt['mailsmile'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Automatically sending an email happy birthday members', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="mailanniv" value="1" <?php if ($rencOpt['mailanniv'])echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Full text for the birthday mail (After hello pseudo)', 'rencontre'); ?></label></th>
					<td><textarea name="textanniv"><?php echo stripslashes($rencOpt['textanniv']); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Max number of mails sent per hour', 'rencontre'); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="qmail" value="<?php echo $rencOpt['qmail']; ?>" />
						<?php 
						$d2 = dirname(__FILE__).'/../inc/rencontre_cronListe.txt';
						if (file_exists($d2)) echo "<p style='color:#D54E21;'>".__('Up this week', 'rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".file_get_contents($d2)."</span>&nbsp;".__('mail/hour', 'rencontre')."<br />(".__('except during maintenance', 'rencontre').")</p>";
						?>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
			</p>
		</form>
		<hr />
		<h2><?php _e('Export members in CSV','rencontre') ?></h2>
		<div>
			<a class="button-primary" href="javascript:void(0)" onclick="f_exportCsv();"><?php _e('Export in CSV','rencontre');?></a>
			<img id="waitCsv" src="<?php echo plugins_url('rencontre/images/loading.gif'); ?>" style="margin:0 0 -10px 20px;display:none;" />
			<a href="" style="display:none;margin:0 10px;" id="rencCsv" type='text/csv' >export_rencontre.csv</a>
			<div style="display:none;" id="photoCsv"><?php _e('Get back photos by FTP in wp-content/uploads/tmp/','rencontre') ?></div>
		</div>
		<hr />
		<h2><?php _e('Import members in CSV','rencontre') ?></h2>
		<p><?php _e('Put members photos in wp-content/uploads/tmp/photo_import/ by FTP before the start (right RW - no sub folder).','rencontre') ?></p>
		<p><?php _e('Make an export and look at it to get the right format (The first line is not treated).','rencontre') ?></p>
		<p><?php _e('In case of interruption during the import of photos, restart the procedure. Doubloons are killed.','rencontre') ?></p>
		<form name='rencCsv' action="<?php echo plugins_url('rencontre/inc/upload_csv.php'); ?>" method="post" enctype="multipart/form-data" target="uplFrame" onSubmit="startUpload();">
			<div>
				<label><?php _e('CSV File','rencontre') ?> : <label>
				<input name="fileCsv" type="file" />
				<img id="loadingCsv" src="<?php echo plugins_url('rencontre/images/loading.gif'); ?>" style="margin:0 0 -10px 20px;display:none;" />
			</div>
			<br />
			<div>
				<input type="submit" class="button-primary" name="submitCsv" value="<?php _e('Import in CSV','rencontre');?>" />
				<span id="impCsv1" style="margin:0 10px;display:none;"><?php _e('File loaded','rencontre');?></span>
				<span id="impCsv2" style="margin:0 10px;display:none;"><?php _e('Error !','rencontre');?></span>
				<span id="impCsv3" style="margin:0 10px;display:none;"><?php _e('Import data completed','rencontre');?></span>
				<span id="impCsv4" style="margin:0 10px;display:none;"><?php _e('Photos Import','rencontre');?> : </span>
				<span id="impCsv5" style="margin-left:-5px;"></span>
				<span id="impCsv6" style="margin:0 10px;display:none;"><?php _e('Import completed','rencontre');?></span>
			</div>
		</form>
		<iframe id="uplFrame" name="uplFrame" src="#" style="width:0;height:0;border:0px solid #fff;">
		</iframe>
		<hr />
		<h2><?php _e('Images names','rencontre') ?></h2>
		<p><?php _e('Be careful, all pictures of the members will have another name.','rencontre') ?>
		<form method="post" name="rencontre_code" action="admin.php?page=rencontre.php">
			<input id="rencCode" type="hidden" name="rencCode" value="" />
			<?php
			if(isset($_POST['rencCode'])) renc_encodeImg((($_POST['rencCode']=='code')?'1':'0'));
			$cod = rencImEncoded();
			if($cod===1) echo '<p style="color:green;">'. __('Images names are encoded','rencontre');
			else if($cod===0) echo '<p style="color:#D54E21;">'. __('Images names are not encoded','rencontre');
			else echo '<p style="color:red;">'.__('I don\'t know if it\'s encoded or not','rencontre');
			echo '.</p>';
			?>
			<input type="submit" class="button-primary" onclick="document.getElementById('rencCode').value='code';" value="<?php _e('Encode all images names','rencontre');?>" />
			<input type="submit" class="button-primary" onclick="document.getElementById('rencCode').value='back';" value="<?php _e('Decode all images names','rencontre');?>" />
		</form>
	</div>
	<?php
	}
function rencMenuMembres()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname (__FILE__) . '/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencOpt; global $rencDiv; global $rencVersion;
	$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
	$drap=''; $drapNom='';
	foreach($q as $r)
		{
		if($r->c_liste_categ=='d') $drap[$r->c_liste_iso] = $r->c_liste_valeur;
		else if($r->c_liste_categ=='p')$drapNom[$r->c_liste_iso] = $r->c_liste_valeur;
		}
	?>
	<div class='wrap'>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Members', 'rencontre'); ?></h2>
		<?php 
		$nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users");
		$np = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=P.user_id AND R.i_photo>0 AND CHAR_LENGTH(P.t_titre)>4 AND CHAR_LENGTH(P.t_annonce)>30");
		echo "<p style='color:#D54E21;'>".__('Number of registered members','rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".$nm."</span></p>";
		echo "<p style='color:#D54E21;'>".__('Number of members with profile and photo','rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".$np."</span></p>";
		?>
		<?php
		if (!isset($_GET["id"]))
			{ ?>
		<form name="rencPseu" method="post" action="">
			<label><?php _e('Alias or email', 'rencontre'); ?> : <label>
			<input type="text" name="pseu" />
			<input type="submit" class="button-primary" value="<?php _e('Find', 'rencontre'); ?>" />
		</form>
			<?php
			if (isset($_POST["a1"]) && $_POST["a1"] && $_POST["a2"]) 
				{
				if($_POST["a2"]=='b0' || $_POST["a2"]=='b1' || $_POST["a2"]=='m0' || $_POST["a2"]=='m1')
					{
					$st = $wpdb->get_var("SELECT user_status FROM ".$wpdb->prefix."users WHERE ID='".$_POST["a1"]."'");
					if($_POST["a2"]=='b1') $st = ($st>1?2:0);
					else if($_POST["a2"]=='b0') $st = ($st>1?3:1);
					else if($_POST["a2"]=='m1') $st = (($st==1||$st==3)?1:0);
					else if($_POST["a2"]=='m0') $st = (($st==1||$st==3)?3:2);
					$wpdb->update($wpdb->prefix.'users', array('user_status'=>$st), array('ID'=>$_POST["a1"]));
					}
				else
					{
					f_userSupp($_POST["a1"],$_POST["a2"],1);
					if ($rencOpt['mailsupp'])
						{
						$q = $wpdb->get_var("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID='".$_POST["a1"]."'");
						$objet  = wp_specialchars_decode($rencDiv['blogname'], ENT_QUOTES).' - '.__('Account deletion','rencontre');
						$message  = __('Your account has been deleted','rencontre');
						@wp_mail($q, $objet, $message);
						}
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
					else if ($_GET['tri']=='action') $tri='ORDER BY U.user_status DESC';
					}
				else $tri='ORDER BY P.d_modif DESC';
				if(isset($_POST['pseu']) && $_POST['pseu']!="") $tri = "and (U.user_login='".$_POST['pseu']."' or U.user_email='".$_POST['pseu']."') ".$tri;
				$pagenum = isset($_GET['pagenum'])?absint($_GET['pagenum']):1;
				$limit = 100;
				$ho = false; if(has_filter('rencMemP', 'f_rencMemP')) $ho = apply_filters('rencMemP', $ho); // ouput : array()
				$q = $wpdb->get_results("SELECT U.ID, U.user_login, U.display_name, R.c_ip, R.c_pays, R.c_region, R.c_ville, R.d_naissance, R.i_taille, R.i_poids, R.i_sex, R.i_zage_min, R.i_zage_max, R.i_zrelation, R.i_photo, P.d_modif, P.t_titre, P.t_annonce".($ho?', '.$ho[0].'':'')." 
					FROM (".$wpdb->prefix . "users U, ".$wpdb->prefix . "rencontre_users R, ".$wpdb->prefix . "rencontre_users_profil P) ".($ho?$ho[1]:'')." 
					WHERE R.user_id=P.user_id and R.user_id=U.ID ".$tri."
					LIMIT ".(($pagenum-1)*$limit).",".$limit);
				$total = $wpdb->get_var("SELECT COUNT(user_id) FROM ".$wpdb->prefix . "rencontre_users");
				$page_links = paginate_links(array('base'=>add_query_arg('pagenum','%#%'),'format'=>'','prev_text'=>'&laquo;','next_text'=>'&raquo;','total'=>ceil($total/$limit),'current'=>$pagenum,'mid_size'=>5));
				if ($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
			?>
			<form name='listUser' method='post' action=''><input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' />
			<table class="membre">
				<tr>
					<td><a href="admin.php?page=rencmembers&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='id') echo 'R'; ?>id" title="<?php _e('Sort','rencontre'); ?>">ID</a></td>
					<td><?php _e('Photo','rencontre');?></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='pseudo') echo 'R'; ?>pseudo" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Alias','rencontre');?></a></td>
					<td><?php _e('Sex','rencontre');?></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='age') echo 'R'; ?>age" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Age','rencontre');?><a></td>
					<td><?php _e('Size','rencontre');?></td>
					<td><?php _e('Weight','rencontre');?></td>
					<td><?php _e('Search','rencontre');?></td>
					<td><?php _e('Hang','rencontre');?></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='pays') echo 'R'; ?>pays" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Country','rencontre');?></a></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='modif') echo 'R'; ?>modif" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Ad (change)','rencontre');?></a></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='ip') echo 'R'; ?>ip" title="<?php _e('Sort','rencontre'); ?>"><?php _e('IP address','rencontre');?></a></td>
					<td><a href="admin.php?page=rencmembers&tri=signal" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Reporting','rencontre');?></a></td>
					<td><a href="admin.php?page=rencmembers&tri=action" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Action','rencontre');?></a></td>
					<td></td>
					<?php if($ho) echo '<td>'.$ho[2].'</td>'; ?>
				</tr>
			<?php
			$categ="";
			foreach($q as $s)
				{
				$q = $wpdb->get_row("SELECT P.t_signal, U.user_status FROM ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U  WHERE P.user_id='".$s->ID."' and P.user_id=U.ID");
				$signal = ($q?json_decode($q->t_signal,true):0);
				$block = ($q?(($q->user_status==1||$q->user_status==3)?1:0):0); // weight : 1
				$blockmail = ($q?(($q->user_status==2||$q->user_status==3)?1:0):0); // weight : 2
				echo '<tr>';
				echo '<td><a href="admin.php?page=rencmembers&id='.$s->ID.'" title="'.__('See','rencontre').'">'.$s->ID.'</a></td>';
				echo '<td><a href="admin.php?page=rencmembers&id='.$s->ID.'" title="'.__('See','rencontre').'"><img class="tete" src="'.($s->i_photo!=0?get_bloginfo('url').'/wp-content/uploads/portrait/'.floor(($s->ID)/1000).'/'.Rencontre::f_img((($s->ID)*10).'-mini').'.jpg" alt="" /></a></td>':plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" /></td>');
				echo '<td>'.$s->user_login.'</td>';
				echo '<td>'.(($s->i_sex==0)?__('Man','rencontre').'</td>':__('Woman','rencontre').'</td>');
				echo '<td>'.Rencontre::f_age($s->d_naissance).'</td>';
				echo '<td>'.$s->i_taille.' cm</td>';
				echo '<td>'.$s->i_poids.' kg</td>';
				if ($s->i_zrelation==0) echo '<td>'.__('Serious relationship','rencontre'); elseif ($s->i_zrelation==1) echo '<td>'.__('Open relationship','rencontre'); elseif ($s->i_zrelation==2) echo '<td>'.__('Friendship','rencontre');
				else echo '<td>'.$s->i_zrelation;
				echo '<br />'.$s->i_zage_min.' '. __('to','rencontre').' '.$s->i_zage_max.'</td>';
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
				echo '<td><a href="javascript:void(0)" class="rencBlock'.($block?'off':'on').'" onClick="f_block('.$s->ID.',\'b'.$block.'\')" title="'.($block?__('Unblock this member','rencontre'):__('Block this member','rencontre')).'"></a>';
				echo '<a href="javascript:void(0)" class="rencMail'.($blockmail?'off':'on').'" onClick="f_blockMail('.$s->ID.',\'m'.$blockmail.'\')" title="'.($blockmail?__('Allow sending message','rencontre'):__('Prohibit contact','rencontre')).'"></a>';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_fin('.$s->ID.',\''.$s->user_login.'\')" title="'.__('Remove','rencontre').'"></a>';
				echo '</td>';
				if($ho) echo '<td>'.(($s->$ho[3]!='')?$ho[4][$s->$ho[3]]:'').'</td>';
				echo '</tr>';
				}
			?>
			</table>
			</form>
		<?php
			if ($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
			}
		else
			{
			$id = $_GET["id"];
			$q = $wpdb->get_results("SELECT P.id, P.c_categ, P.c_label, P.t_valeur, P.i_type FROM ".$wpdb->prefix."rencontre_profil P WHERE P.c_lang='".substr($rencDiv['lang'],0,2)."' ORDER BY P.c_categ");
			$in = '';
			foreach ($q as $r)
				{
				$in[$r->id][0] = $r->i_type;
				$in[$r->id][1] = $r->c_categ;
				$in[$r->id][2] = $r->c_label;
				$in[$r->id][3] = $r->t_valeur;
				}
			if(!isset($_SESSION['a1'])) $_SESSION['a1'] = "0";
			if(!isset($_SESSION['a2'])) $_SESSION['a2'] = "0";
			if (isset($_POST["a1"]) && !($_SESSION['a1']==$_POST["a1"] && $_SESSION['a2']==$_POST["a2"]))
				{
				if ($_POST["a1"]=="suppImg") RencontreWidget::suppImg($_POST["a2"],$id);
				if ($_POST["a1"]=="plusImg") RencontreWidget::plusImg($_POST["a2"],$id);
				if ($_POST["a1"]=="suppImgAll") RencontreWidget::suppImgAll($id);
				}
			if (isset($_POST["a1"]))
				{
				if ($_POST["a1"]=="sauvProfil") sauvProfilAdm($in,$id);
				if ($_POST["a1"]=="suppImg")
					{
					$_SESSION['a1'] = $_POST["a1"];
					$_SESSION['a2'] = $_POST["a2"];
					}
				}
			$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_ville, R.i_photo, P.t_titre, P.t_annonce, P.t_profil FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$id." and R.user_id=P.user_id and R.user_id=U.ID");
			?>
			
			<h3><?php _e('Change My Profile','rencontre');?></h3>
			<div class="bouton"><a href="javascript:void(0)" onclick="javascript:history.back();"><?php _e('Previous page','rencontre');?></a></div>
			<div class="bouton"><a href="<?php echo admin_url(); ?>admin.php?page=rencmembers"><?php _e('Back Members','rencontre');?></a></div>
			<div class="rencPortrait">
				<form name='portraitChange' method='post' enctype="multipart/form-data" action=''>
					<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='page' value='' />
					<div id="portraitSauv"><span onClick="f_sauv_profil(<?php echo $id; ?>)"><?php _e('Save profile','rencontre');?></span></div>
					<div class="petiteBox portraitPhoto left">
						<div class="rencBox">
							<img id="portraitGrande" src="<?php if(($s->i_photo)!=0) echo $rencDiv['baseurl'].'/portrait/'.floor($id/1000).'/'.Rencontre::f_img(($id*10).'-grande').'.jpg?r='.rand(); else echo plugins_url('rencontre/images').'/no-photo600.jpg'; ?>" width=250 height=250 alt="" />
							<div class="rencBlocimg">
							<?php for ($v=0;$v<$rencOpt['imnb'];++$v)
								{
								if ($s->i_photo>=$id*10+$v)
									{
									echo '<a href="javascript:void(0)" onClick="f_supp_photo('.($id*10+$v).')"><img onMouseOver="f_vignette_change('.($id*10+$v).',\''.Rencontre::f_img(($id*10+$v).'-grande').'\')" class="portraitMini" src="'.$rencDiv['baseurl'].'/portrait/'.floor($id/1000).'/'.Rencontre::f_img(($id*10+$v).'-mini').'.jpg?r='.rand().'" alt="'.__('Click to delete','rencontre').'" title="'.__('Click to delete','rencontre').'" /></a>'."\n";
									echo '<img style="display:none;" src="'.$rencDiv['baseurl'].'/portrait/'.floor($id/1000).'/'.Rencontre::f_img(($id*10+$v).'-grande').'.jpg?r='.rand().'" />'."\n";
									}
								else { ?><a href="javascript:void(0)" onClick="f_plus_photo(<?php echo $s->i_photo; ?>)"><img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php _e('Click to add a photo','rencontre'); ?>" title="<?php _e('Click to add a photo','rencontre'); ?>" /></a>
								<?php } } ?>
							</div>
							<div id="changePhoto"></div>
							<div class="bouton"><a href="javascript:void(0)" onClick="f_suppAll_photo()"><?php _e('Delete all photos','rencontre');?></a></div>
						</div>
					</div>
					<div class="grandeBox right">
						<div class="rencBox">
							<?php
							if($s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />'; ?>

							<div class="grid_10">
								<h3><?php echo $s->display_name; ?></h3>
								<div class="ville"><?php echo $s->c_ville; ?></div>
								<label><?php _e('My attention-catcher','rencontre');?></label><br />
								<input type="text" name="titre" value="<?php echo $s->t_titre; ?>" /><br /><br />
								<label><?php _e('My ad','rencontre');?></label><br />
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
								case 1: $out2.='<tr><td>'.$r1[2].'</td><td><input type="text" name="text'.$r.'" value="'.(isset($out[$r])?$out[$r]:'').'" /></td></tr>'."\n"; break;
								case 2: $out2.='<tr><td>'.$r1[2].'</td><td><textarea name="area'.$r.'" rows="4" cols="50">'.(isset($out[$r])?$out[$r]:'').'</textarea></td></tr>'."\n"; break;
								case 3: $out2.='<tr><td>'.$r1[2].'</td><td><select name="select'.$r.'"><option value="0">&nbsp;</option>'; $list = json_decode($r1[3]); $c1=0;
									foreach ($list as $r2) { $out2.='<option value="'.($c1+1).'"'.((isset($out[$r]) && $c1===$out[$r])?' selected':'').'>'.$r2.'</option>'; ++$c1;}$out2.='</select></td></tr>'."\n"; break;
								case 4: $out2.='<tr><td>'.$r1[2].'</td><td>'; $list = json_decode($r1[3]); $c1=0; if(isset($out[$r])) $c3=" ".implode(" ",$out[$r])." "; else $c3="";
									foreach ($list as $r2) { $out2.=$r2.' : <input type="checkbox" name="check'.$r.'[]" value="'.$c1.'" '.((strstr($c3, " ".$c1." ")!=false)?'checked':'').' />'; ++$c1;}$out2.='</td></tr>'."\n"; break;
								}
							$d=$r1[1];
							}
						$out2.='</table>'."\n";
						echo $out1.$out2;
						?>
						
							<em id="infoChange"><?php if(isset($_POST["a1"]) && $_POST["a1"]=="sauvProfil") _e('Done','rencontre'); ?>&nbsp;</em>
						</div>
					</div>
				</form>
			</div>
		<?php } ?>
		
	</div>
	<?php
	}
//
function rencMenuPrison()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname (__FILE__) . '/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencOpt; global $rencDiv; global $rencVersion
	?>
	<div class='wrap'>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Jail', 'rencontre'); ?></h2>
		<?php 
		if (isset($_POST["a1"])) 
			{
			f_userPrison($_POST["a1"]);
			}
		$tri='ORDER BY Q.d_prison DESC';
		if (isset($_GET['tri']))
			{
			if ($_GET['tri']=='date') $tri='ORDER BY Q.d_prison ASC';
			else if ($_GET['tri']=='Rdate') $tri='ORDER BY Q.d_prison DESC';
			else if ($_GET['tri']=='mail') $tri='ORDER BY Q.c_mail ASC';
			else if ($_GET['tri']=='Rmail') $tri='ORDER BY Q.c_mail DESC';
			else if ($_GET['tri']=='ip') $tri='ORDER BY Q.c_ip ASC';
			else if ($_GET['tri']=='Rip') $tri='ORDER BY Q.c_ip DESC';
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
				<td><a href="admin.php?page=rencjail&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='date') echo 'R'; ?>date" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Date','rencontre');?></a></td>
				<td><a href="admin.php?page=rencjail&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='mail') echo 'R'; ?>mail" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Email address','rencontre');?></a></td>
				<td><a href="admin.php?page=rencjail&tri=<?php if (isset($_GET['tri']) && $_GET['tri']=='ip') echo 'R'; ?>ip" title="<?php _e('Sort','rencontre'); ?>"><?php _e('IP address','rencontre');?><a></td>
				<td><?php _e('End','rencontre');?></td>
			</tr>
		<?php
		$categ="";
		foreach($q as $s)
			{
			echo '<tr>';
			echo '<td>'.$s->d_prison.'</td>';
			echo '<td>'.$s->c_mail.'</td>';
			echo '<td>'.$s->c_ip.'</td>';
			echo '<td><a href="javascript:void(0)" class="rencSupp" onClick="f_liberte('.$s->id.')" title="'.__('Release','rencontre').'"></a></td>';
			echo '</tr>';
			}
		?>
		</table>
		</form>
	</div>
	<?php
	}
//
function rencMenuProfil()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname (__FILE__) . '/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencVersion;
	$loc = substr(get_locale(),0,2); $loc2 = $loc."&";
	$q2 = $wpdb->get_var("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$loc."' ");
	if(!$q2) {$loc = "en"; $loc2 = "en&";}
	if (isset($_POST["a1"]) && !($_SESSION['a2']==$_POST["a2"] && $_SESSION['a4']==$_POST["a4"]) || (isset($_POST["a6"]) && $_POST["a6"]!=''))
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
	if (isset($_POST["a1"]))
		{
		$_SESSION['a2'] = $_POST["a2"];
		$_SESSION['a4'] = $_POST["a4"];
		}
	$q2 = $wpdb->get_results("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang!='".$loc."' GROUP BY c_lang ");
	if($q2!=null) foreach($q2 as $r2) { $loc2 .= $r2->c_lang."&"; }
	?>
	<div class='wrap'>
		<form name='menu_profil' method='post' action=''>
			<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='a3' value='' />
			<input type='hidden' name='a4' value='' /><input type='hidden' name='a5' value='' /><input type='hidden' name='a6' value='' />
		</form>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Profile', 'rencontre'); ?></h2>
		<?php $n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_profil");
		if($n==0)
			{
			echo "<p>".__('It does not appear to be any profile. You can load the default profile if you wish.', 'rencontre')."</p>";
			echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_profil\"].elements[\"a1\"].value=\"profil\";document.forms[\"menu_profil\"].elements[\"a2\"].value=\"profil\";document.forms[\"menu_profil\"].submit();'>". __('Load profiles', 'rencontre')."</a>";
			}
		$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
		if($n==0)
			{
			echo "<p>".__('The country table is empty. You can load the countries and regions by default if you wish.', 'rencontre')."</p>";
			echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_profil\"].elements[\"a1\"].value=\"pays\";document.forms[\"menu_profil\"].elements[\"a2\"].value=\"pays\";document.forms[\"menu_profil\"].submit();'>". __('Load countries', 'rencontre')."</a>";
			}
		if(file_exists(dirname(__FILE__).'/rencontre_synchronise.txt')) { ?>
		<p>
			<a href='javascript:void(0)' class='button-primary' onClick='f_synchronise();'><?php _e('Update member profile', 'rencontre'); ?></a>
			&nbsp;:&nbsp;<span style="color:red;font-weight:700;"><?php _e('You have made changes. Remember to update when you\'re done.', 'rencontre'); ?></span>
		</p><?php } ?>
		
		<p><?php _e('You can create, rename and delete items from the profile.', 'rencontre'); ?></p>
		<p>
			<?php _e('Warning, this is not without consequences. The changes will be applied to the member profiles that can offend. Caution !', 'rencontre'); ?>&nbsp;
		</p>
		<h3><?php _e('Ref language', 'rencontre'); echo ' : <span style="color:#700;">'.$loc.'</span> --- ' . __('Other', 'rencontre').'&nbsp;:&nbsp;';
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
				<label><?php _e('Add Language (2 lowercase letters comply with country code)', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<input type="text" name="langplus" maxlength="2" size="2" />
				<a href='javascript:void(0)' class='button-primary' onClick='f_langplus();'><?php _e('Add a language', 'rencontre'); ?></a>
			</li>
			<li>
				<label><?php _e('Remove a language and all related content', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<select id="langsupp">
					<?php echo $ls; ?>
				</select>
				<a href='javascript:void(0)' class='button-primary' onClick='f_langsupp();'><?php _e('Remove a language', 'rencontre'); ?></a>
			</li>
		</ul>
		<br />
		<div id="edit_profil"></div>
		<div style='margin:8px 12px 12px;'>
			<a href='javascript:void(0)' class='rencPlus' onClick='f_plus(0,"c_categ","","<?php echo $loc2; ?>");' title='Ajouter une cat&eacute;gorie'></a>
			<span style='font-style:italic;'><?php _e('Add category','rencontre');?></span>
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
				echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'c_categ\',\''.urlencode($a4).'\',\'\');" title="'.__('Change the name','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'c_categ\',0);" title="'.__('Remove the category','rencontre').'"></a>';
				echo $categ.'</h3>';
				echo $out . '</div>';
			// LABEL
				echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus('.$r->id.',\'c_label\',\'\',\''.$loc2.'\');" title="'.__('Add value to this category','rencontre').'"></a>';
				echo '<span style="font-style:italic;">'.__('Add value to this category','rencontre').'</span><br /><br />';
				}
			$out = '';
			$a4 = $r->c_lang . '=' . $r->c_label . '&';
			foreach($q1 as $r1)
				{
				$out .= '<span style="margin:0 0 0 37px;color:#777;">'.$r1->c_lang.' : '.$r1->c_label. '</span><br />';
				$a4 .= $r1->c_lang . '=' . $r1->c_label . '&';
				}
			echo '<div class="rencLabel">';
			echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'c_label\',\''.urlencode($a4).'\','.$r->i_type.');" title="'.__('Change the name or type','rencontre').'"></a>';
			echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'c_label\',0);" title="'.__('Remove','rencontre').'"></a>';
			echo $r->c_label . '<br />';
			echo $out . '</div><div style="height:5px;"></div>';
			// VALEUR
			switch($r->i_type)
				{
				case 1 :
				echo '<div class="rencValeur rencType">'.__('A line of text (TEXT)','rencontre').'</div>'."\r\n";
				break;
				case 2 :
				echo '<div class="rencValeur rencType">'.__('Large text box (TEXTAREA)','rencontre').'</div>'."\r\n";
				break;
				case 3 :
				echo '<div class="rencValeur">';
				echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus('.$r->id.',\'t_valeur\',\'\',\''.$loc2.'\');" title="'.__('Add Value','rencontre').'"></a>';
				echo '<span class="rencType">'.__('Single choice list (SELECT)','rencontre').'</span>';
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
					echo '<br /><a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'t_valeur\',\''.urlencode($a4).'\','.$c.');" title="'.__('Change','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'t_valeur\','.$c.');" title="'.__('Remove','rencontre').'"></a>';
					echo $ss . '<br />';
					echo $t . "\r\n";
					++$c;
					}
				echo '</div>'."\r\n";
				break;
				case 4 :
				echo '<div class="rencValeur">';
				echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus('.$r->id.',\'t_valeur\',\'\',\''.$loc2.'\');" title="'.__('Add Value','rencontre').'"></a>';
				echo '<span class="rencType">'.__('Multiple choice list (CHECKBOX)','rencontre').'</span>';
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
					echo '<br /><a href="javascript:void(0)" class="rencEdit" onClick="f_edit('.$r->id.',\'t_valeur\',\''.urlencode($a4).'\','.$c.');" title="'.__('Change','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'t_valeur\','.$c.');" title="'.__('Remove','rencontre').'"></a>';
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
function rencMenuPays()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname (__FILE__) . '/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencDiv; global $rencVersion;
	$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
	$drap=''; $drapNom='';
	foreach($q as $r)
		{
		if($r->c_liste_categ=='d') $drap[$r->c_liste_iso] = $r->c_liste_valeur;
		else if($r->c_liste_categ=='p')$drapNom[$r->c_liste_iso] = $r->c_liste_valeur;
		}
	$loc = substr(get_locale(),0,2); $loc2 = $loc."&";
	$q2 = $wpdb->get_var("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".$loc."' ");
	if(!$q2) {$loc = "en"; $loc2 = "en&";}
	if (isset($_POST["a1"]) && !($_SESSION['a2']==$_POST["a2"] && $_SESSION['a4']==$_POST["a4"]) || (isset($_POST["a6"]) && $_POST["a6"]!=''))
		{
		if ($_POST["a1"]=="supp") liste_supp($_POST["a2"],$_POST["a3"],$_POST["a4"]);
		else if ($_POST["a1"]=="edit") liste_edit($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
		else if ($_POST["a1"]=="plus") liste_plus($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
		else if ($_POST["a1"]=="langplus") liste_langplus($loc,$_POST["a4"]);
		else if ($_POST["a1"]=="langsupp") liste_langsupp($_POST["a4"]);
		else if ($_POST["a1"]=="pays") liste_defaut();
		}
	if (isset($_POST["a1"]))
		{
		$_SESSION['a2'] = $_POST["a2"];
		$_SESSION['a4'] = $_POST["a4"];
		}
	$q2 = $wpdb->get_results("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang!='".$loc."' and c_liste_lang!='' GROUP BY c_liste_lang ");
	if($q2!=null) foreach($q2 as $r2) { $loc2 .= $r2->c_liste_lang."&"; }
	?>
	<div class='wrap'>
		<form name='menu_liste' method='post' action=''>
			<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='a3' value='' />
			<input type='hidden' name='a4' value='' /><input type='hidden' name='a5' value='' /><input type='hidden' name='a6' value='' />
		</form>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Countries and Regions', 'rencontre'); ?></h2>
		<?php $n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
		if($n==0)
			{
			echo "<p>".__('The country table is empty. You can load the countries and regions by default if you wish.', 'rencontre')."</p>";
			echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_liste\"].elements[\"a1\"].value=\"pays\";document.forms[\"menu_liste\"].elements[\"a2\"].value=\"pays\";document.forms[\"menu_liste\"].submit();'>". __('Load countries', 'rencontre')."</a>";
			} ?>
		
		<p><?php _e('You can create, rename and delete countries and regions.', 'rencontre'); ?></p>
		<h3><?php _e('Ref language', 'rencontre'); echo ' : <span style="color:#700;">'.$loc.'</span> --- ' . __('Other', 'rencontre').'&nbsp;:&nbsp;';
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
				<label><?php _e('Add Language (2 lowercase letters comply with country code)', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<input type="text" name="langplus" maxlength="2" size="2" />
				<a href='javascript:void(0)' class='button-primary' onClick='f_liste_langplus();'><?php _e('Add a language', 'rencontre'); ?></a>
			</li>
			<li>
				<label><?php _e('Remove a language and all related content', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<select id="langsupp">
					<?php echo $ls; ?>
				</select>
				<a href='javascript:void(0)' class='button-primary' onClick='f_liste_langsupp();'><?php _e('Remove a language', 'rencontre'); ?></a>
			</li>
		</ul>
		<br />
		<div id="edit_liste"></div>
		<div style='margin:8px 12px 12px;'>
			<a href='javascript:void(0)' class='rencPlus' onClick='f_liste_plus(0,"p","","<?php echo $loc2; ?>");' title='Ajouter un pays'></a>
			<span style='font-style:italic;'><?php _e('Add a country','rencontre');?></span>
		</div>
		<?php
		$q = $wpdb->get_results("SELECT c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' GROUP BY c_liste_iso"); // liste des codes ISO
		foreach($q as $r)
			{
			$q1 = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$r->c_liste_iso."' and c_liste_categ='p' ORDER BY c_liste_lang");
			$out = ''; $out1 = ''; $a4 = '';
			foreach($q1 as $r1)
				{
				if($r1->c_liste_lang==$loc) $out1 = $r1->c_liste_valeur;
				else $out .= '<span style="margin:0 0 0 37px;color:#777;">'.$r1->c_liste_lang.' : '.$r1->c_liste_valeur. '</span><br />';
				$a4 .= $r1->c_liste_lang . '=' . $r1->c_liste_valeur . '&';
				}
			echo '<div class="rencLabel">';
			echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_liste_edit(\''.$r->c_liste_iso.'\',\'p\',\''.urlencode($a4).'\');" title="'.__('Change the name or type','rencontre').'"></a>';
			echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_liste_supp(\''.$r->c_liste_iso.'\',\'p\',0);" title="'.__('Remove','rencontre').'"></a>';
			echo $out1.'&nbsp;('.$r->c_liste_iso.')<br />';
			if(isset($drap[$r->c_liste_iso])) echo '<img style="position:absolute;width:30px;height:20px;" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$r->c_liste_iso].'" />';
			echo $out . '</div><div style="height:5px;"></div>';
			echo '<div class="rencValeur">';
			echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_liste_plus(\''.$r->c_liste_iso.'\',\'r\',\'\',\''.$loc2.'\');" title="'.__('Add Value','rencontre').'"></a>';
			echo '<span class="rencType">'.__('Regions','rencontre').'</span>';
			$q2 = $wpdb->get_results("SELECT id, c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$r->c_liste_iso."' and c_liste_categ='r' ");
			foreach($q2 as $r2)
				{
				echo '<br /><a href="javascript:void(0)" class="rencEdit" onClick="f_liste_edit('.$r2->id.',\'r\',\''.$r2->c_liste_valeur.'\');" title="'.__('Change','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_liste_supp('.$r2->id.',\'r\',0);" title="'.__('Remove','rencontre').'"></a>';
				echo '<span style="margin:0 0 0 5px;color:#777;">'.$r2->c_liste_valeur. '</span>' . "\r\n";
				}
			echo '</div><br style="clear:left;"/>'."\r\n";
			}
		?>
	</div>
	<?php
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
function renc_encodeImg($f=1)
	{
	// Encode or Decode all img
	// $f = 1 => encode ; $f = 0 => decode
	global $rencDiv; global $rencOpt;
	if($f) // ENCODE
		{
		$a = renc_list_files($rencDiv['basedir'].'/portrait/');
		foreach($a as $r)
			{
			if(strpos($r,'z')===false) // allready encoded
				{
				$r0 = substr($r,0,strrpos($r,'/')+1); // folder
				$r1 = substr($r,strrpos($r,'/')+1,-4); // name
				$r2 = Rencontre::f_img($r1,1); // encoded name
				if(copy($rencDiv['basedir'].'/portrait/'.$r, $rencDiv['basedir'].'/portrait/'.$r0.$r2.'.jpg')) unlink($rencDiv['basedir'].'/portrait/'.$r);
				}
			}
		$rencOpt['imcode'] = 1;
		}
	else // DECODE
		{
		global $wpdb;
		$min = $wpdb->get_var("SELECT MIN(user_id) FROM ".$wpdb->prefix."rencontre_users");
		$max = $wpdb->get_var("SELECT MAX(user_id) FROM ".$wpdb->prefix."rencontre_users");
		for($v=$min; $v<=$max; ++$v)
			{
			$r0 = floor($v/1000).'/'; // folder
			if(file_exists($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img(($v*10),1).'.jpg'))
				{
				for($w=0;$w<10;++$w)
					{
					if(file_exists($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w),1).'.jpg'))
						{
						if(copy($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w),1).'.jpg', $rencDiv['basedir'].'/portrait/'.$r0.(($v*10)+$w).'.jpg'))
							unlink($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w),1).'.jpg');
						if(copy($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).'-mini',1).'.jpg', $rencDiv['basedir'].'/portrait/'.$r0.(($v*10)+$w).'-mini.jpg'))
							unlink($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).'-mini',1).'.jpg');
						if(copy($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).'-grande',1).'.jpg', $rencDiv['basedir'].'/portrait/'.$r0.(($v*10)+$w).'-grande.jpg'))
							unlink($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).'-grande',1).'.jpg');
						if(copy($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).'-libre',1).'.jpg', $rencDiv['basedir'].'/portrait/'.$r0.(($v*10)+$w).'-libre.jpg'))
							unlink($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).'-libre',1).'.jpg');
						}
					else break;
					}
				}
			$rencOpt['imcode'] = 0;
			}
		}
	$e = rencImEncoded();
	if($e!==false) $rencOpt['imcode'] = $e; // false = no photo (no member ?) to check
	update_rencontre_options($rencOpt);
	}
function rencImEncoded()
	{
	global $wpdb; global $rencDiv;
	$i = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."rencontre_users WHERE i_photo>0");
	if($i!==null && file_exists($rencDiv['basedir'].'/portrait/'.floor($i/1000).'/'.Rencontre::f_img(($i*10),1).'.jpg')) return 1;
	else if($i!==null && file_exists($rencDiv['basedir'].'/portrait/'.floor($i/1000).'/'.($i*10).'.jpg')) return 0;
	else return false; // no image, no member ?
	}
function renc_list_files($dir)
	{
	$root = scandir($dir);
	$result = array();
	foreach($root as $value)
		{
		if($value === '.' || $value === '..') {continue;}
		if(is_file($dir.'/'.$value)) {$result[]=$value; continue;}
		if(strpos($value,'libre')===false) foreach(renc_list_files($dir.'/'.$value) as $value2) $result[]=$value.'/'.$value2;
		}
	return $result;
	}
//
//
?>