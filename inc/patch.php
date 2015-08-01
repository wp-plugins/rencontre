<?php
//
// V1.2 (COUNTRY MULTILANG)
//
$q = $wpdb->get_var('SELECT c_liste_categ FROM '.$wpdb->prefix.'rencontre_liste WHERE c_liste_categ="Pays"');
if($q)
	{
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."rencontre_liste"); // empty
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste DROP COLUMN i_liste_lien ");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_iso` varchar(2) NOT NULL");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_lang` varchar(2) NOT NULL");
	}
//
// V1.4 (GPS)
//
$q = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."rencontre_users");
if($q && !isset($q->e_lat))
	{
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users 
		ADD `e_lat` decimal(10,5) NOT NULL,
		ADD `e_lon` decimal(10,5) NOT NULL,
		ADD `d_session` datetime NOT NULL");
	}
//
// V1.7 (DUPLICATE ID)
//
$unique = $wpdb->get_results("SHOW INDEXES FROM ".$wpdb->prefix."rencontre_users WHERE Column_name='user_id' AND NOT Non_unique"); // unique => 1
if(!$unique)
	{
	$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_users ORDER BY user_id");
	$id = -1;
	if($q) foreach($q as $r)
		{
		if($r->user_id==$id) // Duplicate
			{
			$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$id));
			$wpdb->insert($wpdb->prefix.'rencontre_users', array(
				'user_id'=>$id,
				'c_ip'=>$r->c_ip,
				'c_pays'=>$r->c_pays,
				'c_region'=>$r->c_region,
				'c_ville'=>$r->c_ville,
				'e_lat'=>$r->e_lat,
				'e_lon'=>$r->e_lon,
				'i_sex'=>$r->i_sex,
				'd_naissance'=>$r->d_naissance,
				'i_taille'=>$r->i_taille,
				'i_poids'=>$r->i_poids,
				'i_zsex'=>$r->i_zsex,
				'i_zage_min'=>$r->i_zage_min,
				'i_zage_max'=>$r->i_zage_max,
				'i_zrelation'=>$r->i_zrelation,
				'i_photo'=>$r->i_photo,
				'd_session'=>$r->d_session
				));
			}
		$id = $r->user_id;
		}
	$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_users_profil ORDER BY user_id");
	$id = -1;
	if($q) foreach($q as $r)
		{
		if($r->user_id==$id) // Duplicate
			{
			$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$id));
			$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array(
				'user_id'=>$id,
				'd_modif'=>$r->d_modif,
				't_titre'=>$r->t_titre,
				't_annonce'=>$r->t_annonce,
				't_profil'=>$r->t_profil,
				't_action'=>$r->t_action,
				't_signal'=>$r->t_signal
				));
			}
		$id = $r->user_id;
		}
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users ADD UNIQUE(`user_id`)");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users_profil ADD UNIQUE(`user_id`)");
	}
//
// END PATCH - PATCH OFF
//
copy(dirname(__FILE__).'/patch.php', dirname(__FILE__).'/patch_off.php');
unlink(dirname(__FILE__).'/patch.php');
?>