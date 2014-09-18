<?php
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
						if ($a=="") $a = '["*** '. __('A MODIFIER','rencontre').' ***"]';
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
				$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$c4[$v]['b']."','*** ". __('A MODIFIER','rencontre')." ***','',1,0,'".$c4[$v]['a']."')");
				$lastid = $wpdb->insert_id;
				}
			else $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$lastid."','".$c4[$v]['b']."','*** ". __('A MODIFIER','rencontre')." ***','',1,0,'".$c4[$v]['a']."')");
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

function f_userSupp($f,$a,$b)
	{
	$r = 'wp-content/uploads/portrait/'.floor($f/1000);
	for ($v=0; $v<6; $v++)
		{
		if (file_exists($r."/".$f.$v.".jpg")) unlink($r."/".$f.$v.".jpg");
		if (file_exists($r."/".$f.$v."-mini.jpg")) unlink($r."/".$f.$v."-mini.jpg");
		if (file_exists($r."/".$f.$v."-grande.jpg")) unlink($r."/".$f.$v."-grande.jpg");
		if (file_exists($r."/".$f.$v."-libre.jpg")) unlink($r."/".$f.$v."-libre.jpg");
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
function f_userPrison($f)
	{
	// $f : id table rencontre_prison
	if (!is_admin()) exit;
	global $wpdb;
	$wpdb->delete($wpdb->prefix.'rencontre_prison', array('id'=>$f));
	}
//
?>