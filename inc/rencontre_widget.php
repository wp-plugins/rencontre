<?php
//
class RencontreWidget extends WP_widget
	{
	//var $drap; var $drapNom;
 	function __construct()
		{
		parent::__construct(
			'rencontre-widget', // Nom en BDD : widget_nom (table wp_options - colonne option_name)
			'Rencontre', // Name (nom en admin sur le widget)
			array( 'description' => __('Widget pour integrer le site de rencontre', 'rencontre'), ) // Description en admin sur le widget
			);
		}
	//
	function widget($arguments, $data) // Partie Site
		{
		$langue = ((WPLANG)?WPLANG:get_locale());
		if(current_user_can("administrator")) return;
		wp_enqueue_style('rencontre', plugins_url('rencontre/css/rencontre.css'));
		wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre.js?r='.rand()));
		$this->op = get_option('rencontre_options');
		$options = get_option('rencontre_options');
		$limit = $options['limit'];
		global $user_ID; global $current_user; global $wpdb;
		global $drap; global $drapNom;
		get_currentuserinfo();
		$mid=$current_user->ID; // Mon id
		$upl = wp_upload_dir();
		$r = $upl['basedir'].'/portrait';if(!is_dir($r)) mkdir($r);
		$q = $wpdb->get_results("SELECT c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' ");
		$drap=''; $drapNom='';
		foreach($q as $r)
			{
			$drap[$r->c_liste_iso] = $r->c_liste_valeur;
			$drapNom[$r->c_liste_iso] = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_iso='".$r->c_liste_iso."' and c_liste_lang='".substr($langue,0,2)."' ");
			}
		if ($_POST['nouveau']==$mid) RencontreWidget::f_nouveauMembre($mid); 
		// *****************************************************************************************************************
		// 0. Partie menu
		$alias = $wpdb->get_var("SELECT user_login FROM ".$wpdb->prefix."users WHERE ID='".$mid."'");
		require(dirname (__FILE__) . '/../lang/rencontre-js-lang.php');
		$lang += array('mid'=>$current_user->ID,'ajaxchat'=>plugins_url('rencontre/inc/rencontre_tchat.php'),'wpajax'=>admin_url('admin-ajax.php'),'tchaton'=>$options['tchat']);
		wp_localize_script('rencontre', 'rencobjet', $lang);
		$fantome = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' and CHAR_LENGTH(t_titre)>4 and CHAR_LENGTH(t_annonce)>30 ");
		?>
		
		<div id="widgRenc">
			<div id="rencTchat"></div><div id="rencCam"></div><div id="rencCam2"></div>
			<div class="rencMenu pleineBox">
				<form name='rencMenu' method='post' action=''>
					<input type='hidden' name='page' value='' /><input type='hidden' name='id' value='' />
					<div class="rencBox">
						<ul>
							<a href="<?php echo $options['home']; ?>"><li><?php _e('Ma page d\'accueil','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Ma fiche','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='change';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li <?php if(!$fantome) echo 'class="boutonred"'; ?>><?php _e('Modifier mon profil','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='msg';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Messagerie','rencontre'); echo RencontreWidget::f_count_inbox($alias); ?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='trouve';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Recherche','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='compte';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Mon compte','rencontre');?></li></a>
							<span style="position:relative;">
							<?php if($options['facebook'])
								{
								$fb = $options['facebook'];
								if (get_locale()!="fr_FR")
									{
									if (strpos($fb,'locale=')===false) $fb = str_replace('.php?','.php?locale=en_US&',$fb);
									else $fb = str_replace('fr_FR','en_US',$fb);
									}
								echo $fb;
								} ?>
								
							</span>
						</ul>
					</div>
				</form>
				<div class="rencBonjour"><?php _e('Bonjour','rencontre'); echo '&nbsp;'.($current_user->user_login); ?></div>
			</div>
		<?php 
		//
		// 1. Nouveau visiteur
		if (strstr($_SESSION['rencontre'],'nouveau'))
			{
			$q = $wpdb->get_var("SELECT S.id FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_prison S WHERE U.ID='".$mid."' and S.c_mail=U.user_email");
			if ($q)
				{ ?>
			<div class="pleineBox">
				<div class="rencBox">
					<div class="rencNouveau">
						<h3><?php _e('Votre adresse mail est actuellement en quarantaine. D&eacute;sol&eacute;','rencontre'); ?>&nbsp;</h3>
					</div>
				</div>
			</div>
				<?php }
			else { ?>
			<div class="pleineBox">
				<div class="rencBox">
					<div class="rencNouveau">
						<h3><?php _e('Bonjour','rencontre'); echo '&nbsp;'.($current_user->user_login); echo ", ".__('bienvenue sur le site','rencontre').'&nbsp;'; bloginfo( 'name' ); ?></h3>
						<p>
						<?php _e('Vous pourrez acc&eacute;der &agrave; l\'ensemble des possibilit&eacute;s offertes par le site dans quelques minutes.','rencontre'); ?>
						<?php _e('Avant cela, vous devez fournir quelques &eacute;l&eacute;ments demand&eacute;s ci-dessous.','rencontre'); ?>
						</p>
						<p>
						<?php _e('Nous tenons &agrave; vous pr&eacute;ciser que nous n\'utilisons pas vos donn&eacute;es personnelles en dehors de ce site.','rencontre'); ?>
						<?php _e('La suppression de votre compte, de votre part ou de la notre, entraine l\'effacement de l\'ensemble des donn&eacute;es vous concernants.','rencontre'); ?>
						<?php _e('Ceci est &eacute;galement valable pour les mails que vous avez envoy&eacute;s aux autres membres ainsi que ceux qu\'ils vous ont envoy&eacute;s.','rencontre'); ?>
						</p>
						<p>
						<?php _e('Nous vous souhaitons de belles rencontres fructueuses.','rencontre'); ?>
						</p>
						<div id="rencAlert"></div>
						<form name="formNouveau" method='post' action=''>
						<input type='hidden' name='nouveau' value='' />
						<label><?php _e('Changer de pseudo (apr&egrave;s, ce ne sera plus possible)','rencontre');?></label>&nbsp;:&nbsp;
						<input name="pseudo" type="text" size="12" value="<?php echo $current_user->user_login; ?>"> 
						<table>
							<tr>
								<th colspan="2"><?php _e('Nouveau mot de passe (minimum 6)','rencontre');?></th>
								<th colspan="2"><?php _e('Nouveau mot de passe (confirmation)','rencontre');?></th>
							</tr>
							<tr>
								<td colspan="2">
									<input name="pass1" type="password" size="12">
								</td>
								<td colspan="2">
									<input name="pass2" type="password" size="12">
								</td>
							</tr>
							<tr>
								<td colspan="4" style="font-style:italic;padding-top:0;">
									<?php _e('Le changement du mot de passe est obligatoire pour cette premi&egrave;re connexion','rencontre');?>
								</td>
							</tr>
							<tr>
								<th><?php _e('Je suis','rencontre');?></th>
								<th><?php _e('N&eacute; le','rencontre');?></th>
								<th><?php _e('Ma ville','rencontre');?></th>
								<th><?php _e('Mon pays','rencontre');?></th>
							</tr>
							<tr>
								<td>
									<select name="sex" size=2>
										<option value="0"><?php _e('Homme','rencontre');?></option>
										<option value="1"><?php _e('Femme','rencontre');?></option>
									</select>
								</td>
								<td>
									<select name="jour" size=6>
										<?php for ($v=1;$v<32;++$v) {echo '<option value="'.$v.'">'.$v.'</option>';}?>
										
									</select>
									<select name="mois" size=6>
										<?php for ($v=1;$v<13;++$v) {echo '<option value="'.$v.'">'.$v.'</option>';}?>
										
									</select>
									<select name="annee" size=6>
										<?php $y=(date('Y')); for ($v=($y-99);$v<($y-18);++$v) {echo '<option value="'.$v.'">'.$v.'</option>';}?>
										
									</select>
								</td>
								<td>
									<input name="ville" type="text" size="12">
								</td>
								<td>
									<select name="pays" size=6 onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect1');">
										<?php RencontreWidget::f_pays(); ?>
										
									</select>
								</td>
							</tr>
							<tr>
								<th><?php _e('Ma taille','rencontre');?></th>
								<th><?php _e('Mon poids','rencontre');?></th>
								<th></th>
								<th><?php _e('Ma r&eacute;gion','rencontre');?></th>
							</tr>
							<tr>
								<td>
									<select name="taille" size=6>
										<?php for ($v=140;$v<220;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}?>
										
									</select>
								</td>
								<td>
									<select name="poids" size=6>
										<?php for ($v=40;$v<140;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}?>
										
									</select>
								</td>
								<td></td>
								<td>
									<select id="regionSelect1" size=6 name="region">
										<?php RencontreWidget::f_regionBDD(); ?>
										
									</select>
								</td>
							</tr>
							<tr>
								<th><?php _e('Je cherche','rencontre');?></th>
								<th><?php _e('Age min/max','rencontre');?></th>
								<th><?php _e('Pour','rencontre');?></th>
								<th></th>
							</tr>
							<tr>
								<td>
									<select name="zsex" size=2>
										<option value="0"><?php _e('Homme','rencontre');?></option>
										<option value="1"><?php _e('Femme','rencontre');?></option>
									</select>
								</td>
								<td>
									<select name="zageMin" size=6 onChange="f_min(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
										<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
										
									</select>
									<select name="zageMax" size=6 onChange="f_max(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
										<?php for ($v=18;$v<100;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
										
									</select>
								</td>
								<td>
									<select name="zrelation" size=3>
										<option value="0"><?php _e('Relation s&eacute;rieuse','rencontre');?></option>
										<option value="1"><?php _e('Relation libre','rencontre');?></option>
										<option value="2"><?php _e('Amiti&eacute;','rencontre');?></option>
									</select>
								</td>
								<td>
									<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_nouveau(<?php echo $mid; ?>,'<?php echo admin_url('admin-ajax.php'); ?>')"><?php _e('Envoi','rencontre');?></a></div>
								</td>
							</tr>
						</table>
						</form>
					</div>
				</div>
			</div>
			<?php }
			}
		//
		// 2. Partie portrait
		if (strstr($_SESSION['rencontre'],'portrait') && $_POST["id"])
			{
			$id =strip_tags($_POST["id"]);
			$l = RencontreWidget::f_enLigne($id); // true : en ligne - false : hors ligne
			if (strstr($_SESSION['rencontre'],'bloque')) RencontreWidget::f_bloque($id,$mid);
			if ($mid!=$id)
				{
				RencontreWidget::f_visite($id,$mid); // visite du profil - enregistrement sur ID
				$bl = RencontreWidget::f_etat_bloque($id,$mid); // je l ai bloque ? - lecture de MID
				}
			global $wpdb;
			$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_region, R.c_ville, R.d_naissance, R.i_taille, R.i_poids, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, R.i_photo, P.t_titre, P.t_annonce, P.t_profil, P.t_action FROM ".$wpdb->prefix . "users U, ".$wpdb->prefix . "rencontre_users R, ".$wpdb->prefix . "rencontre_users_profil P WHERE R.user_id=".$id." and R.user_id=P.user_id and R.user_id=U.ID");
			$bl1= RencontreWidget::f_etat_bloque1($id,$mid,$s->t_action); // je suis bloque ?
			echo $before_widget ."\n"; ?>
			<div class="rencPortrait">
				<div class="petiteBox left">
					<div class="rencBox calign">
						<img id="portraitGrande" src="<?php if(($s->i_photo)!=0) echo $upl['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.(($s->ID)*10).'-grande'; else echo plugins_url('rencontre/images').'/no-photo600'; ?>.jpg" alt="" />
						<div>
						<?php for ($v=0;$v<4;++$v)
							{
							if ($s->i_photo>=($s->ID)*10+$v)
								{
								echo '<a class="zoombox zgallery1" href="'.$upl['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.(($s->ID)*10+$v).'.jpg"><img onMouseOver="f_vignette('.(($s->ID)*10+$v).')" class="portraitMini" src="'.$upl['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.(($s->ID)*10+$v).'-mini.jpg?r='.rand().'" alt="" /></a>'."\n";
								echo '<img style="display:none;" src="'.$upl['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.(($s->ID)*10+$v).'-grande.jpg?r='.rand().'" />';
								echo '<img style="display:none;" src="'.$upl['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.(($s->ID)*10+$v).'.jpg?r='.rand().'" />'."\n";
								}
							else { ?><img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="" />
							<?php echo "\n"; } } ?>
							
						</div>
					</div>
				</div>
				<div class="grandeBox right">
					<div class="rencBox">
						<?php
						if($s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />';
						echo ($l)?'<span class="rencInline2">'.__('en ligne','rencontre').'</span>':'<span class="rencOutline2">'.__('hors-ligne','rencontre').'</span>';  ?>

						<div class="grid_10">
							<h3><?php echo $s->display_name; if($bl) echo '<span style="font-weight:bold;color:red;text-transform:uppercase;">&nbsp;'.__('(bloqu&eacute;)','rencontre').'</span>'; ?></h3>
							<div class="ville"><?php echo $s->c_ville; if($s->c_region) echo ' <em>('.$s->c_region.')</em>'; ?></div>
							<div class="renc1"><?php echo Rencontre::f_age($s->d_naissance).'&nbsp;'.__('ans','rencontre'); ?>&nbsp;&nbsp;-&nbsp;&nbsp;<?php echo $s->i_taille; ?> cm&nbsp;&nbsp;-&nbsp;&nbsp;<?php echo $s->i_poids; ?> kg</div>
							<div class="titre"><?php echo stripslashes($s->t_titre); ?></div>
						</div>
						<p><?php echo stripslashes($s->t_annonce); ?></p>
						<div class="abso225">
							<?php echo __('Je cherche','rencontre').'&nbsp;'.(($s->i_zsex==1)?__('une femme','rencontre'):__('un homme','rencontre'));
							echo '&nbsp;'.__('entre','rencontre').'&nbsp;'.$s->i_zage_min.'&nbsp;'.__('et','rencontre').'&nbsp;'.$s->i_zage_max.'&nbsp;'.__('ans','rencontre');
							echo '&nbsp;'.__('pour','rencontre').'&nbsp;'.(($s->i_zrelation==0)?__('Relation s&eacute;rieuse','rencontre'):''.(($s->i_zrelation==1)?__('Relation libre','rencontre'):__('Amiti&eacute;','rencontre'))); ?>
						</div>
					</div>
					<?php if ($id!=$mid) { ?>
					
					<div class="rencBox">
						<ul>
							<?php if (!$bl1) { ?><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='ecrire';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Envoyer un mail','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='sourire';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Lui sourire','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='demcont';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Demander un contact','rencontre');?></li></a>
							<?php } else echo '<li class="rencLiOff">'.__('Envoyer un mail','rencontre').'</li><li class="rencLiOff">'.__('Lui sourire','rencontre').'</li><li class="rencLiOff">'.__('Demander un contact','rencontre').'</li>'; ?>
							<?php if ($l && !$bl1 && $options['tchat']==1) echo '<a href="javascript:void(0)" onClick="f_tchat('.$mid.','.$id.',\''.plugins_url('rencontre/inc/rencontre_tchat.php').'\',1)"><li>'.__('Tchater','rencontre').'</li></a>';
							else if ($options['tchat']==1) echo '<li class="rencLiOff">'.__('Tchater','rencontre').'</li>'; ?>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='bloque';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php echo (!$bl)?__('Bloquer','rencontre'):__('D&eacute;bloquer','rencontre'); ?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='signale';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();" title="<?php _e('Signaler un faux profil ou un contenu inadapt&eacute;','rencontre'); ?>"><li><?php _e('Signaler','rencontre'); ?></li></a>
							<?php if ($bl1) echo '<span style="position:absolute;left:80px;top:12px;font-size:120%;color:red;">'.__('Vous &ecirc;tes bloqu&eacute; !','rencontre').'</span>'; ?>
						</ul>
					</div>
					<?php if (strstr($_SESSION['rencontre'],'sourire') || strstr($_SESSION['rencontre'],'signale') || strstr($_SESSION['rencontre'],'demcont') || $_POST["sujet"]) { ?><div id="infoChange">
						<div class="rencBox">
							<em>
								<?php
								if (strstr($_SESSION['rencontre'],'sourire')) {RencontreWidget::f_sourire(strip_tags($_POST["id"]),$mid);}
								else if (strstr($_SESSION['rencontre'],'signale')) {RencontreWidget::f_signal(strip_tags($_POST["id"]),$mid);}
								else if (strstr($_SESSION['rencontre'],'demcont')) {RencontreWidget::f_demcont(strip_tags($_POST["id"]),$mid);}
								else if ($_POST["sujet"]!="" && $_POST["sujet"]!=$_SESSION["sujet"]) {echo "Message envoy&eacute; "; $_SESSION["sujet"]=$_POST["sujet"]; RencontreWidget::f_envoiMsg($alias);}
								?>
							</em>
						</div>
					</div><?php } ?>

					<?php } ?>
					
					<div class="rencBox">
						<div class="br"></div>
					<?php
					$profil = json_decode($s->t_profil,true);
					if ($profil) foreach($profil as $h)
						{
						$q = $wpdb->get_row("SELECT c_categ, c_label, t_valeur, i_type FROM ".$wpdb->prefix."rencontre_profil WHERE id=".$h['i']." AND i_poids<5 AND c_lang='".substr($langue,0,2)."'");
						if ($q)
							{
							if ($q->i_type<3) $out[$q->c_categ][$q->c_label] = $h['v'];
							else
								{
								$val = json_decode($q->t_valeur);
								if ($q->i_type==3) $out[$q->c_categ][$q->c_label] = $val[$h['v']];
								elseif ($q->i_type==4) 
									{
									$tmp="";
									foreach ($h['v'] as $pv) { $tmp.=$val[$pv].", "; }
									$out[$q->c_categ][$q->c_label] = substr($tmp, 0, -2);
									}
								}
							}
						}
					$c=0; ?>
					
					<script type="text/javascript" src="<?php echo get_bloginfo('template_directory'); ?>/js/zoombox.js"></script>
					<script type="text/javascript">(function($){$('a.zoombox').zoombox();})(jQuery);</script>
					<?php
					$out1="";$out2="";
					if ($out) foreach ($out as $hk=>$h)
						{
						$out1.='<span class="portraitOnglet '.(($c==0)?'rencTab':'').'" id="portraitOnglet'.$c.'" onclick="javascript:f_onglet('.$c.');">'.$hk.'</span>';
						$out2.='<table '.(($c==0)?'style="display:table;" ':'').'id="portraitTable'.$c.'" border="0">';
						foreach ($h as $hk1=>$h1)
							{
							$out2.='<tr><td>'.$hk1.'</td><td>'.$h1.'</td></tr>';
							}
						$out2.='</table>';
						++$c;
						}
					echo $out1.$out2;
					?>
					
					</div>
				</div>
			</div>
		<?php }
		//
		// 3. Partie Changement du portrait
		if (strstr($_SESSION['rencontre'],'change'))
			{
			global $wpdb;
			// recuperation de la table profil : $in[]
			$q = $wpdb->get_results("SELECT P.id, P.c_categ, P.c_label, P.t_valeur, P.i_type FROM ".$wpdb->prefix."rencontre_profil P WHERE P.c_lang='".substr($langue,0,2)."' AND P.i_poids<5 ORDER BY P.c_categ");
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
				if ($_POST["a1"]=="suppImg") RencontreWidget::suppImg(strip_tags($_POST["a2"]),$mid,$upl);
				if ($_POST["a1"]=="plusImg") RencontreWidget::plusImg(strip_tags($_POST["a2"]),$mid,$upl);
				}
			if ($_POST["a1"]=="sauvProfil") RencontreWidget::sauvProfil($in,$mid); 
			$_SESSION['a1'] = $_POST["a1"]; $_SESSION['a2'] = $_POST["a2"];
			$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_ville, R.i_photo, P.t_titre, P.t_annonce, P.t_profil FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$mid." and R.user_id=P.user_id and R.user_id=U.ID ");
			echo $before_widget ."\n"; ?>
			<h3><?php _e('Modifier mon profil','rencontre');?></h3>
			<div class="rencPortrait">
				<form name='portraitChange' method='post' enctype="multipart/form-data" action=''>
					<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='page' value='' />
					<div class="petiteBox portraitPhoto left">
						<div class="rencBox">
							<div id="changePhoto"></div>
							<img id="portraitGrande" src="<?php if(($s->i_photo)!=0) echo $upl['baseurl'].'/portrait/'.floor($mid/1000).'/'.($mid*10).'-grande.jpg?r='.rand(); else echo plugins_url('rencontre/images').'/no-photo600.jpg'; ?>" alt="" />
							<div>
							<?php for ($v=0;$v<4;++$v)
								{
								if ($s->i_photo>=$mid*10+$v)
									{
									echo '<a href="javascript:void(0)" onClick="f_supp_photo('.($mid*10+$v).')"><img onMouseOver="f_vignette_change('.($mid*10+$v).')" class="portraitMini" src="'.$upl['baseurl'].'/portrait/'.floor($mid/1000).'/'.($mid*10+$v).'-mini.jpg?r='.rand().'" alt="'.__('Cliquer pour supprimer','rencontre').'" title="'.__('Cliquer pour supprimer','rencontre').'" /></a>'."\n";
									echo '<img style="display:none;" src="'.$upl['baseurl'].'/portrait/'.floor($mid/1000).'/'.($mid*10+$v).'-grande.jpg?r='.rand().'" />'."\n";
									}
								else { ?><a href="javascript:void(0)" onClick="f_plus_photo(<?php echo $s->i_photo; ?>)"><img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php _e('Cliquer pour ajouter une photo','rencontre'); ?>" title="<?php _e('Cliquer pour ajouter une photo','rencontre'); ?>" /></a>
								<?php } } ?>
							</div>
						</div>
					</div>
					<div class="grandeBox right">
						<div class="rencBox">
							<?php if($s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />'; ?>

							<div class="grid_10">
								<h3><?php echo $s->display_name; ?></h3>
								<div class="ville"><?php echo $s->c_ville; ?></div>
								<label><?php _e('Mon accroche','rencontre');?></label><br />
								<input type="text" name="titre" value="<?php echo stripslashes($s->t_titre); ?>" /><br /><br />
								<label><?php _e('Mon annonce','rencontre');?></label><br />
								<textarea name="annonce" rows="10" style="width:95%;"><?php echo stripslashes($s->t_annonce); ?></textarea>
							</div>
						</div>
					</div>
					<div id="portraitSauv"><span onClick="f_sauv_profil(<?php echo $mid; ?>)"><?php _e('Sauvegarde du profil','rencontre');?></span></div>
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
						if($in)
							{
							foreach ($in as $r=>$r1)
								{
								if ($d!=$r1[1]) // nouvel onglet
									{
									if ($d!="") $out2.='</table>'."\n";
									$out1.='<span class="portraitOnglet '.(($c==0)?'rencTab':'').'" id="portraitOnglet'.$c.'" onclick="javascript:f_onglet('.$c.');">'.$r1[1].'</span>'."\n";
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
							}
						$out2.='</table>'."\n";
						echo $out1.$out2;
						?>
						
							<em id="infoChange"><?php if ($_POST["a1"]=="sauvProfil") _e('Effectu&eacute;e','rencontre'); ?>&nbsp;</em>
						</div>
					</div>
				</form>
			</div>
			<?php }
		//
		// 4. Partie Mon Accueil
		if (strstr($_SESSION['rencontre'],'accueil'))
			{
			global $wpdb;
			$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_ville, R.d_naissance, R.i_zsex, i_zage_min, i_zage_max, R.i_zrelation, R.i_photo, P.t_action FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$mid." and R.user_id=P.user_id and R.user_id=U.ID");
			$action = json_decode($s->t_action,true);
			$zsex=$s->i_zsex; // pour mini-portrait
			$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$s->i_zage_min));
			$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$s->i_zage_max));
			?>
			
			<div class="petiteBox right">
				<?php  if (strstr($_SESSION['rencontre'],'password')) { ?><div id="infoChange">
					<div class="rencBox"><em><?php _e('Mot de passe chang&eacute; !','rencontre'); ?></em></div>
				</div><?php } ?>
				
				<div class="rencBox">
					<?php if($s->i_photo!=0) echo '<img src="'.$upl['baseurl'].'/portrait/'.floor(($mid)/1000).'/'.($mid*10).'-mini.jpg" class="maPhoto" alt="'.$s->display_name.'"/>';
					else echo '<img class="maPhoto" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />';
					echo ($current_user->user_login);
					if($s->c_pays!="") echo '<img class="monFlag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />'; ?>
					<div class="monAge"><?php _e('Age','rencontre'); echo '&nbsp;:&nbsp;'.Rencontre::f_age($s->d_naissance).'&nbsp;'; _e('ans','rencontre'); ?></div>
					<div class="maVille"><?php _e('Ville','rencontre'); echo '&nbsp;:&nbsp;'.$s->c_ville; ?></div>
					<div id="tauxProfil"></div>
					<div class="maRecherche"><?php _e('Je recherche','rencontre');?><em> <?php echo (($s->i_zsex==1)?__('une femme','rencontre'):__('un homme','rencontre')).'</em>&nbsp;'.__('pour','rencontre').'&nbsp;<em>'.(($s->i_zrelation==0)?__('Relation s&eacute;rieuse','rencontre'):''.(($s->i_zrelation==1)?__('Relation libre','rencontre'):__('Amiti&eacute;','rencontre'))).'</em>'; ?></div>
					<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='change';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><?php _e('Modifier mon profil','rencontre');?></a></div>
					<div class="mesSourire"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='sourireIn';document.forms['rencMenu'].submit();"><?php _e('Sourire','rencontre'); echo '&nbsp;:&nbsp;'.((count($action['sourireIn'])>49)?'>50':count($action['sourireIn'])); ?></a></div>
					<div class="mesSourire"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='visite';document.forms['rencMenu'].submit();"><?php _e('Regard','rencontre'); echo '&nbsp;:&nbsp;'.((count($action['visite'])>49)?'>50':count($action['visite'])); ?></a></div>
					<div class="mesSourire"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='contactIn';document.forms['rencMenu'].submit();"><?php _e('Demandes de contact','rencontre'); echo '&nbsp;:&nbsp;'.((count($action['contactIn'])>49)?'>50':count($action['contactIn'])); ?></a></div>
				</div>
				<div class="rencBox">
					<div class="rencItem"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='sourireOut';document.forms['rencMenu'].submit();"><?php _e('A qui j\'ai souri ?','rencontre');?></a></div>
					<div class="rencItem"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='contactOut';document.forms['rencMenu'].submit();"><?php _e('A qui j\'ai demand&eacute; un contact ?','rencontre');?></a></div>
					<div class="rencItem"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='bloque';document.forms['rencMenu'].submit();"><?php _e('Qui j\'ai bloqu&eacute; ?','rencontre');?></a></div>
				</div>
				<div class="rencBox">
					<h3><?php _e('Recherche rapide','rencontre');?></h3>
					<form name='formMonAccueil' method='post' action=''>
						<input type='hidden' name='page' value='' /><input type='hidden' name='sex' value='<?php echo $s->i_zsex; ?>' />
						<div class="rencItem"><?php _e('Age','rencontre');?>&nbsp;<span><?php _e('de','rencontre');?>&nbsp;
							<select name="ageMin" onChange="f_min(this.options[this.selectedIndex].value,'formMonAccueil','ageMin','ageMax');">
								<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
								
							</select>
							</span>
							<span>&nbsp;<?php _e('&agrave;','rencontre');?>&nbsp;
							<select name="ageMax" onChange="f_max(this.options[this.selectedIndex].value,'formMonAccueil','ageMin','ageMax');">
								<?php for ($v=18;$v<98;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
								
								<option value="99" selected>99&nbsp;<?php _e('ans','rencontre');?></option>
							</select>
							</span>
						</div>
						<div class="rencItem"><?php _e('Pays','rencontre');?>&nbsp;:
							<select name="pays" onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect1');">
								<?php RencontreWidget::f_pays(); ?>
								
							</select>
						</div>
						<div class="rencItem"><?php _e('R&eacute;gion','rencontre');?>&nbsp;:
							<select id="regionSelect1" name="region">
								<?php RencontreWidget::f_regionBDD(); ?>
								
							</select>
						</div>
						<div class="button"><a href="javascript:void(0)" onClick="document.forms['formMonAccueil'].elements['page'].value='cherche';document.forms['formMonAccueil'].submit();"><?php _e('Cherche','rencontre');?></a></div>
						<div class="clear"></div>
					</form>
				</div>
			</div>
		<?php }
		//
		// 5. Partie mini portrait
		if (strstr($_SESSION['rencontre'],'mini'))
			{
			global $wpdb;
			if (!$zsex)
				{
				$q = $wpdb->get_row("SELECT i_zsex, i_zage_min, i_zage_max FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$mid."'");
				$zsex=$q->i_zsex;
				$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$q->i_zage_min));
				$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$q->i_zage_max));
				}
			?>
			
			<div class="grandeBox left">
			<?php $q = $wpdb->get_results("SELECT DISTINCT(R.user_id) FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=P.user_id AND R.i_sex=".$zsex." AND R.d_naissance>'".$zmax."' AND R.d_naissance<'".$zmin."'".(($options['onlyphoto'])?" AND R.i_photo>0 ":" ")."AND CHAR_LENGTH(P.t_titre)>4 AND CHAR_LENGTH(P.t_annonce)>30 ORDER BY RAND() LIMIT 8"); ?>
			
				<div class="rencBox">
					<h3><?php _e('Portraits s&eacute;l&eacute;ctionn&eacute;s','rencontre');?></h3>
						<?php foreach ($q as $r)
							{
							RencontreWidget::f_miniPortrait($r->user_id);
							} 
						?>
							
					<div class="clear"></div>
				</div><!-- .rencBox -->
			<?php if ($options['anniv']==1)
				{
				$q = $wpdb->get_results("SELECT user_id FROM ".$wpdb->prefix."rencontre_users WHERE d_naissance LIKE '%".date('m-d')."' AND i_sex=".$zsex." AND d_naissance>'".$zmax."' AND d_naissance<'".$zmin."' LIMIT 4"); ?>
			
				<div class="rencBox">
					<h3><?php _e('Anniversaires du jour','rencontre');?></h3>
						<?php foreach ($q as $r)
							{
							RencontreWidget::f_miniPortrait($r->user_id);
							} 
						?>
							
					<div class="clear"></div>
				</div><!-- .rencBox -->
			<?php } ?>
			<?php if ($options['ligne']==1)
				{
				$tab=''; $d=$upl['basedir'].'/session/';
				if ($dh=opendir($d)){while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..' && (filemtime($d.$file)>time()-180)) $tab.="'".basename($file, ".txt")."',"; }closedir($dh);}
				$q = $wpdb->get_results("SELECT user_id FROM ".$wpdb->prefix."rencontre_users WHERE user_id IN (".substr($tab,0,-1).") AND i_sex=".$zsex." LIMIT 16"); // AND d_naissance>'".$zmax."' AND d_naissance<'".$zmin."' ?>
				<div class="rencBox">
					<h3><?php _e('Actuellement en ligne','rencontre');?></h3>
						<?php foreach ($q as $r)
							{
							RencontreWidget::f_miniPortrait($r->user_id);
							} 
						?>
							
					<div class="clear"></div>
				</div><!-- .rencBox -->
			<?php } ?>
			<?php $q = $wpdb->get_results("SELECT DISTINCT(R.user_id) FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=U.ID AND R.user_id=P.user_id AND R.i_sex=".$zsex.(($options['onlyphoto'])?" AND R.i_photo>0 ":" ")."AND CHAR_LENGTH(P.t_titre)>4 AND CHAR_LENGTH(P.t_annonce)>30 ORDER BY U.ID DESC LIMIT 12"); ?>
			
				<div class="rencBox">
					<h3><?php _e('Nouveaux inscrits','rencontre');?></h3>
						<?php foreach ($q as $r)
							{
							RencontreWidget::f_miniPortrait($r->user_id);
							} 
						?>
							
					<div class="clear"></div>
				</div><!-- .rencBox -->
			</div><!-- .grandeBox .left -->
		<?php }
		//
		// 6. Partie recherche rapide
		if (strstr($_SESSION['rencontre'],'cherche'))
			{
			$pagine = (isset($_POST['pagine'])?$_POST['pagine']:0);
			$suiv = 1;
			?> 
			
			<div class="grandeBox left">
			<form name='rencPagine' method='post' action=''>
				<input type='hidden' name='page' value='cherche' />
				<input type='hidden' name='pays' value='<?php echo $_POST['pays']; ?>' />
				<input type='hidden' name='region' value='<?php echo $_POST['region']; ?>' />
				<input type='hidden' name='sex' value='<?php echo $_POST['sex']; ?>' />
				<input type='hidden' name='ageMin' value='<?php echo $_POST['ageMin']; ?>' />
				<input type='hidden' name='ageMax' value='<?php echo $_POST['ageMax']; ?>' />
				<input type='hidden' name='id' value='<?php echo $_POST['id']; ?>' />
				<input type='hidden' name='pagine' value='<?php echo $pagine; ?>' />
			</form>
			<?php global $wpdb;
			if ($_POST['sex']!=NULL)
				{
				$s="SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce FROM ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R WHERE P.user_id=R.user_id";
				if ($_POST['region']) $s.=" and R.c_region LIKE '".addslashes($wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_POST['region'])."'"))."'";
				if ($_POST['pays']) $s.=" and R.c_pays='".$_POST['pays']."'";
				$s.=" and R.i_sex='".strip_tags($_POST['sex'])."'";
				$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_POST['ageMin']));
				$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_POST['ageMax']));
				$s.=" and R.d_naissance<'".$zmin."'";
				$s.=" and R.d_naissance>'".$zmax."'";
				$s.=" and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30".(($options['onlyphoto'])?" and R.i_photo>0":"");
				$s.=" ORDER BY R.user_id DESC LIMIT ".($pagine*$limit).", ".($limit+1); // LIMIT indice du premier, nombre de resultat
				$q = $wpdb->get_results($s);
				if($wpdb->num_rows<=$limit) $suiv=0;
				else array_pop($q); // supp le dernier ($limit+1) qui sert a savoir si page suivante
				}
			else if ($_POST['id']=='sourireOut')
				{
				echo '<h3 style="text-align:center;">'.__('J\'ai souri &agrave;','rencontre').'&nbsp;...</h3>';
				$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
				$action= json_decode($q,true);
				$q = ''; $c = 0; $n = 0; $suiv = 0;
				if ($action['sourireOut'])
					{
					krsort($action['sourireOut']);
					foreach ($action['sourireOut'] as $r)
						{
						++$n;
						if($c<=$limit)
							{
							$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id");
							if($q[$c] && $n>$pagine*$limit)
								{
								if($c<$limit) $q[$c]->date=$r['d'];
								else {$suiv=1;array_pop($q);}
								++$c;
								}
							}
						}
					}
				}
			else if ($_POST['id']=='sourireIn')
				{
				echo '<h3 style="text-align:center;">'.__('J\'ai re&ccedil;u un sourire de','rencontre').'&nbsp;...</h3>';
				$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
				$action= json_decode($q,true);
				$q = ''; $c = 0; $n = 0; $suiv = 0;
				if ($action['sourireIn'])
					{
					krsort($action['sourireIn']);
					foreach ($action['sourireIn'] as $r)
						{
						++$n;
						if($c<=$limit)
							{
							$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id");
							if($q[$c] && $n>$pagine*$limit)
								{
								if($c<$limit) $q[$c]->date=$r['d'];
								else {$suiv=1;array_pop($q);}
								++$c;
								}
							}
						}
					}
				}
			else if ($_POST['id']=='contactOut')
				{
				echo '<h3 style="text-align:center;">'.__('J\'ai demand&eacute; un contact &agrave;','rencontre').'&nbsp;...</h3>';
				$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
				$action= json_decode($q,true);
				$q = ''; $c = 0; $n = 0; $suiv = 0;
				if ($action['contactOut'])
					{
					krsort($action['contactOut']);
					foreach ($action['contactOut'] as $r)
						{
						$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id");
						if($q[$c] && $n>$pagine*$limit)
							{
							if($c<$limit) $q[$c]->date=$r['d'];
							else {$suiv=1;array_pop($q);}
							++$c;
							}
						}
					}
				}
			else if ($_POST['id']=='contactIn')
				{
				echo '<h3 style="text-align:center;">'.__('J\'ai eu une demande de contact de','rencontre').'&nbsp;...</h3>';
				$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
				$action= json_decode($q,true);
				$q = '';$c = 0; $n = 0; $suiv = 0;
				if ($action['contactIn'])
					{
					krsort($action['contactIn']);
					foreach ($action['contactIn'] as $r)
						{
						$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id");
						if($q[$c] && $n>$pagine*$limit)
							{
							if($c<$limit) $q[$c]->date=$r['d'];
							else {$suiv=1;array_pop($q);}
							++$c;
							}
						}
					}
				}
			else if ($_POST['id']=='visite')
				{
				echo '<h3 style="text-align:center;">'.__('J\'ai &eacute;t&eacute; regard&eacute; par','rencontre').'&nbsp;...</h3>';
				$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
				$action= json_decode($q,true);
				$q = ''; $c = 0; $n = 0; $suiv = 0;
				if ($action['visite'])
					{
					krsort($action['visite']);
					foreach ($action['visite'] as $r)
						{
						$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id");
						if($q[$c] && $n>$pagine*$limit)
							{
							if($c<$limit) $q[$c]->date=$r['d'];
							else {$suiv=1;array_pop($q);}
							++$c;
							}
						}
					}
				}
			else if ($_POST['id']=='bloque')
				{
				echo '<h3 style="text-align:center;">'.__('J\'ai bloqu&eacute;','rencontre').'&nbsp;...</h3>';
				$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
				$action= json_decode($q,true);
				$q = ''; $c = 0; $n = 0; $suiv = 0;
				if ($action['bloque'])
					{
					krsort($action['bloque']);
					foreach ($action['bloque'] as $r)
						{
						$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id");
						if($q[$c] && $n>$pagine*$limit)
							{
							if($c<$limit) $q[$c]->date=$r['d'];
							else {$suiv=1;array_pop($q);}
							++$c;
							}
						}
					}
				}
			if($q) foreach($q as $r)
				{
				$bl1=RencontreWidget::f_etat_bloque1($r->user_id,$mid,$r->t_action); // je suis bloque ?
				?>
				<div class="rencBox">
				<?php if ($r->date) echo '<div class="rencDate">'.__('Le','rencontre').'&nbsp;'.substr($r->date,8,2).'.'.substr($r->date,5,2).'.'.substr($r->date,0,4).'</div>'; ?>
					<?php RencontreWidget::f_miniPortrait($r->user_id); ?>
					<div class="maxiBox right rel">
							<?php echo stripslashes($r->t_annonce); ?>
						<div style="height:40px;"></div>
						<div class="abso225">
							<?php echo __('Je cherche','rencontre').'&nbsp;'.(($r->i_zsex==1)?__('une femme','rencontre'):__('un homme','rencontre')).'<br />';
							echo '&nbsp;'.__('entre','rencontre').'&nbsp;'.$r->i_zage_min.'&nbsp;'.__('et','rencontre').'&nbsp;'.$r->i_zage_max.'&nbsp;'.__('ans','rencontre').'<br />';
							echo __('pour','rencontre').'&nbsp;'.(($r->i_zrelation==0)?__('Relation s&eacute;rieuse','rencontre'):''.(($r->i_zrelation==1)?__('Relation libre','rencontre'):__('Amiti&eacute;','rencontre'))); ?>
						</div>
						<div class="abso135">
							<?php if (!$bl1) { ?>
							<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='ecrire';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Envoyer un mail','rencontre');?></a></div>
							<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='sourire';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Lui sourire','rencontre');?></a></div>
							<?php } else echo '<div class="button right rencLiOff">'.__('Envoyer un mail','rencontre').'</div><div class="button right rencLiOff">'.__('Lui sourire','rencontre').'</div>'; ?>
							<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Profil','rencontre');?></a></div>
						</div>
					</div><!-- .grandeBox .right -->
					<div class="clear"></div>
				</div>
				<?php }
				if($pagine||$suiv)
					{
					echo '<div class="rencPagine">';
					if(($pagine+0)>0) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=parseInt(document.forms['rencPagine'].elements['pagine'].value)-1;document.forms['rencPagine'].submit();\">".__('Page pr&eacute;c&eacute;dente','rencontre')."</a>";
					for($v=max(0, $pagine-4); $v<$pagine; ++$v)
						{
						echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value='".$v."';document.forms['rencPagine'].submit();\">".$v."</a>";
						}
					echo "<span>".$pagine."</span>";
					if($suiv) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=parseInt(document.forms['rencPagine'].elements['pagine'].value)+1;document.forms['rencPagine'].submit();\">".__('Page suivante','rencontre')."</a>";
					echo '</div>';
					}
				?>
			</div>
		<?php }
		//
		// 7. Partie recherche plus
		if (strstr($_SESSION['rencontre'],'trouve'))
			{
			?> 
			
			<div class="grandeBox left">
				<div id="rencTrouve">
				<?php RencontreWidget::f_cherchePlus($mid); ?>
				
				</div><!-- #rencTrouve -->
			</div><!-- .grandeBox .left -->
		<?php }
		//
		// 8. Messagerie
		if (strstr($_SESSION['rencontre'],'msg'))
			{ ?>
			
			<div class="grandeBox left">
				<div class="rencBox">
					<form name="formEcrire" method='post' action=''>
					<input type='hidden' name='page' value='' /><input type='hidden' name='id' value='' /><input type='hidden' name='msg' value='' />
					<div id="rencMsg">
					<?php RencontreWidget::f_boiteReception($alias); ?>
					</div>
					</form>
				</div><!-- .rencBox -->
			</div><!-- .grandeBox .left -->

		<?php }
		//
		// 9. Envoi message
		if (strstr($_SESSION['rencontre'],'ecrire'))
			{ 
			global $wpdb;
			$q = $wpdb->get_row("SELECT U.user_login, R.i_photo FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE U.ID='".strip_tags($_POST["id"])."' and R.user_id=U.ID");
			?>
			
			<div class="grandeBox left">
				<div class="rencBox">
					<h3><?php _e('Envoyer un message &agrave;','rencontre'); echo '&nbsp;'.$q->user_login; ?></h3>
					<div id="rencMsg">
					<form name="formEcrire" method='post' action=''>
					<input type='hidden' name='page' value='' /><input type='hidden' name='id' value='' /><input type='hidden' name='msg' value='' />
					<?php if($q->i_photo!=0) echo '<img class="tete" src="'.$upl['baseurl'].'/portrait/'.floor(($q->i_photo)/10000).'/'.(floor(($q->i_photo)/10)*10).'-mini.jpg" alt="" />';
					else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />'; ?>
						<label><?php _e('Sujet','rencontre');?>&nbsp;:</label><input name="sujet" type="text" /><br />
						<label><?php _e('Message','rencontre');?>&nbsp;:</label><textarea name="contenu" rows="8"></textarea><br />
						<div class="button"><a href="javascript:void(0)" onClick="document.forms['formEcrire'].elements['page'].value='portrait';document.forms['formEcrire'].elements['id'].value='<?php echo $_POST["id"]; ?>'<?php if($_POST["msg"]) echo ';document.forms[\'formEcrire\'].elements[\'msg\'].value=\''.strip_tags($_POST["msg"]).'\''; ?>;document.forms['formEcrire'].submit();"><?php _e('Envoi','rencontre');?></a></div>
						<div class="clear"></div>
					</form>
					</div>
				</div><!-- .rencBox -->
			</div><!-- .grandeBox .left -->

		<?php }
		//
		// 10. Compte
		if (strstr($_SESSION['rencontre'],'compte'))
			{
			?> 
			
			<div class="grandeBox left">
				<div class="rencCompte rencBox">
				<?php RencontreWidget::f_compte($mid); ?>
				</div>
			</div>
		<?php } ?>
		<?php if(!$fantome && !$_COOKIE["rencfantome"] && !current_user_can("administrator")) { ?>
			<div id="rencFantome">
				<div class="rencFantome">
				<?php _e('Votre profil est vide. Vous &ecirc;tes invisible des autres membres. Pour b&eacute;n&eacute;ficier des avantages du site, merci de modifier votre profil.','rencontre');?>
				<span onClick="f_fantome();"><?php _e('Fermer','rencontre');?></span>
				</div>
			</div><?php } ?>
			
			<div style="clear:both;">&nbsp;</div>
		</div><!-- #widgRenc -->
		<?php echo $after_widget;
		}
	//
	function update($content_new, $content_old)
		{
		$content_new['titre'] = esc_attr($content_new['titre']);
		return $content_new;
		}
	//
	function form($data) // partie ADMIN
		{
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		?>
		<p>
		<label for="<?php echo $this->get_field_id('titre'); ?>">Titre :</label><br />
		<input value="<?php echo $data['titre']; ?>" name="<?php echo $this->get_field_name('titre'); ?>" id="<?php echo $this->get_field_id('titre'); ?>" type="text" />
		</p>
		<?php
		}
	//
	static function suppImg($im,$mid,$upl)
		{
		// entree : nom de la photo (id * 10 + 1 ou 2 ou 3...)
		$r = $upl['basedir'].'/portrait/'.floor($im/10000).'/';
		if (file_exists($r.$im.'.jpg')) unlink($r.$im.'.jpg');
		if (file_exists($r.$im.'-mini.jpg')) unlink($r.$im.'-mini.jpg');
		if (file_exists($r.$im.'-grande.jpg')) unlink($r.$im.'-grande.jpg');
		if (file_exists($r.$im.'-libre.jpg')) unlink($r.$im.'-libre.jpg');
		global $wpdb;
		$q = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$mid."'");
		if (floor($q/10)*10==$q) $p=0; // plus de photo
		else $p=$q-1;
		$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>$p), array('user_id'=>$mid));
		$c=0;
		for ($v=$im; $v<$q; ++$v)
			{
			rename($r.($v+1).'.jpg', $r.$v.'.jpg');
			rename($r.($v+1).'-mini.jpg', $r.$v.'-mini.jpg');
			rename($r.($v+1).'-grande.jpg', $r.$v.'-grande.jpg');
			rename($r.($v+1).'-libre.jpg', $r.$v.'-libre.jpg');
			}
		}
	//
	static function plusImg($nim,$mid,$upl)
		{
		// entree : $s->i_photo (id * 10 + nombre de photo)
		if ($nim==0) $p=$mid*10; // premiere photo
		else $p=$nim+1;
		$r = $upl['basedir'].'/tmp/';
		if(!is_dir($r)) mkdir($r);
		$cible = $r . basename($_FILES['plusPhoto']['tmp_name']);
		if (move_uploaded_file($_FILES['plusPhoto']['tmp_name'], $cible)) 
			{
			RencontreWidget::f_photo($p,$cible,$upl);
			global $wpdb;
			$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>$p), array('user_id'=>$mid));
			if (file_exists($cible)) unlink($cible);
			}
		else echo "rate";
		}
	//
	static function sauvProfil($in,$id)
		{
		// entree : Sauvegarde du profil
		// sortie bdd : [{"i":10,"v":"Sur une ile deserte avec mon amoureux."},{"i":35,"v":0},{"i":53,"v":[0,4,6]}]
		$u = "";
		if($in)
			{
			foreach ($in as $r=>$r1) 
				{
				switch ($r1[0])
					{
					case 1: if ($_POST['text'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['text'.$r]))).'"},'; break;
					case 2: if ($_POST['area'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['area'.$r]))).'"},'; break;
					case 3: if ($_POST['select'.$r]>0) $u.='{"i":'.$r.',"v":'.(strip_tags($_POST['select'.$r]-1)).'},'; break;
					case 4: if (!empty($_POST['check'.$r])) {$u.='{"i":'.$r.',"v":['; foreach ($_POST['check'.$r] as $r2) { $u.=$r2.',';} $u=substr($u, 0, -1).']},';} break;
					}
				}
			}
		global $wpdb;
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('d_modif'=>date("Y-m-d H:i:s"),'t_titre'=>strip_tags(stripslashes($_POST['titre'])),'t_annonce'=>strip_tags(stripslashes($_POST['annonce'])),'t_profil'=>'['.substr($u, 0, -1).']'), array('user_id'=>$id));
		$_post=null;
		}
	//
	static function f_photo($im,$rim,$upl)
		{
		// im : user_id *10 + numero de photo a partir de 0
		$options = get_option('rencontre_options');
		$r = $upl['basedir'].'/portrait/'.floor($im/10000);
		if(!is_dir($r)) mkdir($r);
		$sim = getimagesize($rim);
		$wim=$sim[0]; $him=$sim[1];
		if ($sim[1]/$sim[0]>.75) {if ($sim[1]>480) { $wim=($sim[0]/$sim[1]*480); $him=480; }} // verticale
		else { if ($sim[0]>640) { $him=($sim[1]/$sim[0]*640); $wim=640; }} // horizontale
		if ($sim[1]/$sim[0]>1) { $himi=($sim[1]-$sim[0])/8; $wimi=0; $carre=$sim[0];} // position pour decoupe carre
		else {$wimi=($sim[0]-$sim[1])/2; $himi=0;$carre=$sim[1];} 
		if ($sim[1]/$sim[0]>(108/141)) { $hi4=($sim[1]-($sim[0])*108/141)/4; $wi4=0; $wf4=$sim[0]; $hf4=$wf4*108/141;} // verticale ou leger horizontale
		else {$wi4=($sim[0]-($sim[1]*141/108))/2; $hi4=0; $hf4=$sim[1]; $wf4=$hf4*141/108;} // tres horizontale
		if ($sim[2]==2 || $sim[2]==3)
			{
			if ($sim[2]==2) $in = imagecreatefromjpeg($rim); // jpg
			if ($sim[2]==3) $in = imagecreatefrompng($rim); // png
			$out1 = imagecreatetruecolor ($wim, $him); // max : 640x480
			$out2 = imagecreatetruecolor (60, 60);
			$out3 = imagecreatetruecolor (250, 250);
			$out4 = imagecreatetruecolor (141, 108);
			imagecopyresampled ($out1, $in, 0, 0, 0, 0, $wim, $him, $sim[0], $sim[1]); 
			imagecopyresampled ($out2, $in, 0, 0, $wimi, $himi, 60, 60, $carre, $carre); 
			imagecopyresampled ($out3, $in, 0, 0, $wimi, $himi, 250, 250, $carre, $carre); 
			imagecopyresampled ($out4, $in, 0, 0, $wi4, $hi4, 141, 108, $wf4, $hf4);
			// imagecopyresampled(sortie, entree, position sur sortie X, Y, position entree X, Y, larg haut sur sortie, larg haut sur entree)
			imagejpeg(RencontreWidget::f_imcopyright($out1,$options['imcopyright']), $r."/".$im.".jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out2,$options['imcopyright']), $r."/".$im."-mini.jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out3,$options['imcopyright']), $r."/".$im."-grande.jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out4,$options['imcopyright']), $r."/".$im."-libre.jpg", 75);
			imagedestroy($in); imagedestroy($out1); imagedestroy($out2); imagedestroy($out3); imagedestroy($out4);
			}
		}
	//
	static function f_imcopyright($imc,$right)
		{
		if ($right)
			{
			$sx = imagesx($imc);
			$sy = imagesy($imc);
			$Text=site_url();
			if(current_user_can("administrator")) $Font="../wp-content/plugins/rencontre/inc/arial.ttf";
			else $Font="wp-content/plugins/rencontre/inc/arial.ttf";
			$FontColor = ImageColorAllocate ($imc,255,255,255);
			$FontShadow = ImageColorAllocate ($imc,0,0,0);
			$Rotation = 30;
			/* Make a copy image */
			$OriginalImage = ImageCreateTrueColor($sx,$sy);
			ImageCopy ($OriginalImage,$imc,0,0,0,0,$sx,$sy);
			/* Iterate to get the size up */
			$FontSize=1;
			do
				{
				$FontSize *= 1.1;
				$Box = @ImageTTFBBox($FontSize,0,$Font,$Text);
				$TextWidth = abs($Box[4] - $Box[0]);
				$TextHeight = abs($Box[5] - $Box[1]);
				}
			while ($TextWidth < $sx*0.75);
			/*  Awkward maths to get the origin of the text in the right place */
			$x = $sx/2 - cos(deg2rad($Rotation))*$TextWidth/2;
			$y = $sy/2 + sin(deg2rad($Rotation))*$TextWidth/2 + cos(deg2rad($Rotation))*$TextHeight/2;
			/* Make shadow text first followed by solid text */
			ImageTTFText ($imc,$FontSize,$Rotation,$x+4,$y+4,$FontShadow,$Font,$Text);
			ImageTTFText ($imc,$FontSize,$Rotation,$x,$y,$FontColor,$Font,$Text);
			/* merge original image into version with text to show image through text */
			ImageCopyMerge ($imc,$OriginalImage,0,0,0,0,$sx,$sy,85);
			}
		return $imc;
		}
	//
	static function f_pays($f=1)
		{
		$langue = ((WPLANG)?WPLANG:get_locale());
		echo '<option value="">- '.__('Indiff&eacute;rent','rencontre').' -</option>';
		global $wpdb;
		$q = $wpdb->get_results("SELECT c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_lang='".substr($langue,0,2)."' ");
		foreach($q as $r)
			{
			echo '<option value="'.$r->c_liste_iso.'"'.(($r->c_liste_valeur=="France" && $f==1)?' selected':'').(($r->c_liste_iso==$f)?' selected':'').'>'.$r->c_liste_valeur.'</option>';
			}
		}
	//
	static function f_regionBDD($f=1,$g='FR')
		{
		// Regions francaises par defaut
		// Copie de la version pour ajax dans rencontre.php
		echo '<option value="">- '.__('Indiff&eacute;rent','rencontre').' -</option>';
		if ($f)
			{
			global $wpdb; 
			$q = $wpdb->get_results("SELECT id, c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$g."' and c_liste_categ='r'");
			foreach($q as $r)
				{
				echo '<option value="'.$r->id.'"'.(($r->c_liste_valeur==$f)?' selected':'').'>'.$r->c_liste_valeur.'</option>';
				}
			}
		}
	//
	static function f_miniPortrait($f)
		{
		// entree : id
		// sortie : code HTML avec le mini portrait
		$upl = wp_upload_dir(); 
		global $wpdb; global $drap; global $drapNom;
		$s = $wpdb->get_row("SELECT U.display_name, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$f." and R.user_id=P.user_id and R.user_id=U.ID");
		?>
		
				<div class="miniPortrait miniBox">
					<?php echo (RencontreWidget::f_enLigne($f))?'<span class="rencInline">'.__('en ligne','rencontre').'</span>':'<span class="rencOutline">'.__('hors-ligne','rencontre').'</span>'; ?>
					
					<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $f; ?>';document.forms['rencMenu'].submit();">
					<?php if ($s->i_photo!=0) echo '<img class="tete" src="'.$upl['baseurl'].'/portrait/'.floor(($f)/1000).'/'.($f*10).'-mini.jpg" alt="'.$s->display_name.'" />';
					else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />'; ?>
					
					</a>
					<div>
						<h3><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $f; ?>';document.forms['rencMenu'].submit();"><?php echo $s->display_name; ?></a></h3>
						<div class="monAge"><?php echo Rencontre::f_age($s->d_naissance).'&nbsp;'; _e('ans','rencontre');?></div>
						<div class="maVille"><?php echo $s->c_ville; ?></div>
					</div>
					<p><?php echo stripslashes($s->t_titre); ?></p>
					<?php 
					if($s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />'; ?>
					
				</div><!-- .miniPortrait -->
		<?php
		}
	//
	static function f_miniPortrait2($f)
		{
		// miniPortrait2 : pour la fenetre du TCHAT
		// entree : id
		// sortie : code HTML avec le mini portrait
		$upl = wp_upload_dir();
		$langue = ((WPLANG)?WPLANG:get_locale());
		global $wpdb;
		$s = $wpdb->get_row("SELECT U.display_name, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$f." and R.user_id=P.user_id and R.user_id=U.ID");
		$drap1 = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' and c_liste_iso='".$s->c_pays."' ");
		$drapNom1 = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_iso='".$s->c_pays."' and c_liste_lang='".substr($langue,0,2)."' ");
		?>
		
				<div class="miniPortrait miniBox">
					<?php if ($s->i_photo!=0) echo '<img class="tete" src="'.$upl['baseurl'].'/portrait/'.floor(($f)/1000).'/'.($f*10).'-mini.jpg" alt="'.$s->display_name.'" />';
					else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />'; ?>
					
					<div>
						<h3><?php echo $s->display_name; ?></h3>
						<div class="monAge"><?php echo Rencontre::f_age($s->d_naissance).'&nbsp;'; _e('ans','rencontre');?></div>
						<div class="maVille"><?php echo $s->c_ville; ?></div>
					</div>
					<p><?php echo stripslashes($s->t_titre); ?></p>
					<?php 
					if($s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap1.'" alt="'.$drapNom1.'" title="'.$drapNom1.'" />'; ?>
					
				</div><!-- .miniPortrait -->
		<?php
		}
	//
	static function f_enLigne($f)
		{
		$upl = wp_upload_dir(); 
		if (is_file($upl['basedir'].'/session/'.$f.'.txt') && time()-filemtime($upl['basedir'].'/session/'.$f.'.txt')<180) return true;
		else return false;
		}
	//
	static function f_count_inbox($f)
		{
		// Message dans ma boite ?
		global $wpdb;
		$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$f."' and M.read=0 and M.deleted=0");
		if ($n) return '<span>'.$n.'</span>';
		else return;
		}
	//
	static function f_boiteReception($f) // retour AJAX
		{
		// entree : alias
		global $wpdb;
		$q = $wpdb->get_results("SELECT M.id, M.subject, M.content, M.sender, M.date, M.read FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$f."' and M.deleted!=1 ORDER BY M.date DESC");
		$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$f."' and M.read=0 and M.deleted=0");
		?>
			
			<h3><?php _e('Boite de r&eacute;ception','rencontre');?>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onClick="f_boite_envoi('<? echo $f; ?>','<?php echo admin_url('admin-ajax.php'); ?>');"><?php _e('Messages envoy&eacute;s','rencontre');?></a></h3>
			<h4><?php _e('Vous avez','rencontre'); echo '&nbsp;'.count($q).'&nbsp;'; _e('message','rencontre'); echo ((count($q)>1)?'s':'').' ('.$n.'&nbsp;'.__('non lu','rencontre');?>)</h4>
			<table><tr><th></th><th style="width:20%;"><?php _e('Emetteur','rencontre');?></th><th style="width:50%;"><?php _e('Sujet','rencontre');?></th><th style="width:25%;"><?php _e('Date','rencontre');?></th><th></th></tr>
			<?php foreach ($q as $r)
				{
				if ($r->read==1) echo '<tr><td></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.$r->sender.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td><a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\');">&nbsp;</a></td></tr>'."\n";
				else if ($r->read==2) echo '<tr><td><img src="'.plugins_url('rencontre/images/reponse.png').'" alt="" /></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.$r->sender.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td><a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\');">&nbsp;</a></td></tr>'."\n";
				else echo '<tr style="font-weight:bold;"><td></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.$r->sender.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td><a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\');">&nbsp;</a></td></tr>'."\n";
				} ?>
				
			</table>
		<?php }
	//
	static function f_boiteEnvoi($f) // retour AJAX
		{
		// entree : alias
		global $wpdb;
		$q = $wpdb->get_results("SELECT M.id, M.subject, M.content, M.recipient, M.date, M.read, M.deleted FROM ".$wpdb->prefix."rencontre_msg M WHERE M.sender='".$f."' and M.deleted!=2 ORDER BY M.date DESC");
		 ?>

			<h3><a href="javascript:void(0)" onClick="f_boite_reception('<? echo $f; ?>','<?php echo admin_url('admin-ajax.php'); ?>');"><?php _e('Boite de r&eacute;ception','rencontre');?></a>&nbsp;&nbsp;&nbsp;<?php _e('Messages envoy&eacute;s','rencontre');?></h3>
			<table><tr><th></th><th style="width:20%;"><?php _e('Destinataire','rencontre');?></th><th style="width:50%;"><?php _e('Sujet','rencontre');?></th><th style="width:25%;"><?php _e('Date','rencontre');?></th><th></th></tr>
			<?php foreach ($q as $r)
				{
				if ($r->read==1) echo '<tr><td><img src="'.plugins_url('rencontre/images/oeil.png').'" alt="" /></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.$r->recipient.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td>'.(($r->deleted==1)?'<a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\');">&nbsp;</a>':'').'</td></tr>'."\n";
				else if ($r->read==2) echo '<tr><td><img src="'.plugins_url('rencontre/images/retour.png').'" alt="" /></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.$r->recipient.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td>'.(($r->deleted==1)?'<a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\');">&nbsp;</a>':'').'</td></tr>'."\n";
				else echo '<tr style="font-weight:bold;"><td></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.$r->recipient.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td>'.(($r->deleted==1)?'<a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\');">&nbsp;</a>':'').'</td></tr>'."\n";
				} ?>
				
			</table>
		<?php }
	//
	static function f_voirMsg($f,$a) // retour AJAX
		{
		// entree : $f = id message - $a = alias
		global $wpdb;
		$upl = wp_upload_dir(); 
		$q = $wpdb->get_row("SELECT M.subject, M.content, M.sender, M.recipient, M.date, M.read FROM ".$wpdb->prefix."rencontre_msg M WHERE M.id='".$f."' ");
		if ($q)
			{
			$id = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."users WHERE user_login='".$q->sender."'");
			$p = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$id."'");
			if($p!=0) echo '<img class="tete" src="'.$upl['baseurl'].'/portrait/'.floor(($p)/10000).'/'.(floor(($p)/10)*10).'-mini.jpg" alt="" />';
			else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$q->sender.'" title="'.$q->sender.'" />'; ?>
			<h3><a href="javascript:void(0)" onClick="f_boite_reception('<? echo $a; ?>','<?php echo admin_url('admin-ajax.php'); ?>');"><?php _e('Boite de r&eacute;ception','rencontre');?></a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onClick="f_boite_envoi('<? echo $a; ?>','<?php echo admin_url('admin-ajax.php'); ?>');"><?php _e('Messages envoy&eacute;s','rencontre');?></a></h3>
			<h3><?php _e('Message','rencontre');?></h3>
			<div style="width:87%;">
				<div class="left"><?php _e('De','rencontre'); echo '&nbsp;:&nbsp;'.$q->sender; ?></div>
				<div class="right"><?php _e('Date','rencontre'); echo '&nbsp;:&nbsp;'.$q->date; ?></div>
			</div>
			<div class="clear"><?php _e('A','rencontre'); echo '&nbsp;:&nbsp;'.$q->recipient; ?></div>
			
			<h4><?php echo stripslashes($q->subject); ?></h4>
			<div class="rencBox"><?php echo stripslashes($q->content); ?></div>
			<div class="button"><a href="javascript:void(0)" onClick="document.forms['formEcrire'].elements['page'].value='ecrire';document.forms['formEcrire'].elements['id'].value='<?php echo $id; ?>';document.forms['formEcrire'].elements['msg'].value='<?php echo $f; ?>';document.forms['formEcrire'].submit();"><?php _e('R&eacute;pondre','rencontre');?></a></div>
			<div class="clear"></div>
		<?php if ($q->read==0 && $q->sender!=$a) $wpdb->update($wpdb->prefix.'rencontre_msg', array('read'=>1), array('id'=>$f));
			}
		}
	//
	static function f_suppMsg($f,$a) // retour AJAX
		{
		// entree : $f = id message - $a:alias
		// Destinataire supp en premier => deleted=1
		// Emetteur peut faire supp ensuite : suppression en base
		global $wpdb;
		$q = $wpdb->get_row("SELECT M.sender, M.deleted FROM ".$wpdb->prefix."rencontre_msg M WHERE M.id='".$f."' ");
		if ($q->deleted==1 && $q->sender==$a) $wpdb->delete($wpdb->prefix.'rencontre_msg', array('id'=>$f)); // suppression du serveur car destinataire a supp egalement
		else $wpdb->update($wpdb->prefix.'rencontre_msg', array('deleted'=>1), array('id'=>$f));
		RencontreWidget::f_boiteReception($a);
		}
	//
	static function f_envoiMsg($f)
		{
		// entree : mon alias
		global $wpdb;
		$a = $wpdb->get_var("SELECT user_login FROM ".$wpdb->prefix."users WHERE ID='".strip_tags($_POST["id"])."'");
		$wpdb->insert($wpdb->prefix.'rencontre_msg', array('subject'=>strip_tags($_POST["sujet"]), 'content'=>strip_tags($_POST["contenu"]), 'sender'=>$f, 'recipient'=>$a, 'date'=>date('Y-m-d H:i:s'), 'read'=>0, 'deleted'=>0));
		if ($_POST["msg"]) $wpdb->update($wpdb->prefix.'rencontre_msg', array('read'=>2), array('id'=>strip_tags($_POST["msg"]))); // repondu
		// memo pour mail CRON
		if (!is_dir(dirname(__FILE__).'/cron_liste/')) mkdir(dirname(__FILE__).'/cron_liste/');
		if (!file_exists(dirname(__FILE__).'/cron_liste/'.strip_tags($_POST["id"]).'.txt')){ $t=fopen(dirname(__FILE__).'/cron_liste/'.strip_tags($_POST["id"]).'.txt', 'w'); fclose($t); }
		}
	//
	static function f_cherchePlus($f)
		{
		// formulaire de la recherche plus
		global $wpdb;
		$sex = $wpdb->get_var("SELECT i_zsex FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$f."'");
		if (!strstr($_SESSION['rencontre'],'liste')) // nouvelle recherche
			{
			?>
		
					<div class="rencBox">
						<h3><?php _e('Recherche','rencontre'); ?></h3>
						<form id="formTrouve" name='formTrouve' method='post' action=''>
							<input type='hidden' name='page' value='' />
							<input type='hidden' name='zsex' value='<?php echo $sex; ?>' />
							<table>
							<tr>
								<td><?php _e('Age','rencontre');?>&nbsp;:&nbsp;</td>
								<td><span><?php _e('de','rencontre');?>&nbsp;
									<select name="ageMin" onChange="f_min(this.options[this.selectedIndex].value,'formTrouve','ageMin','ageMax');">
										<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
										
									</select>
									</span>
									<span>&nbsp;<?php _e('&agrave;','rencontre');?>&nbsp;
									<select name="ageMax" onChange="f_max(this.options[this.selectedIndex].value,'formTrouve','ageMin','ageMax');">
										<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
										
										<option value="99" selected>99&nbsp;<?php _e('ans','rencontre');?></option>
									</select>
									</span>
								</td>
							</tr>
							<tr>
								<td><?php _e('Taille','rencontre');?>&nbsp;:&nbsp;</td>
								<td><span><?php _e('de','rencontre');?>&nbsp;
									<select name="tailleMin" onChange="f_min(this.options[this.selectedIndex].value,'formTrouve','tailleMin','tailleMax');">
										<?php for ($v=140;$v<220;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}?>
										
									</select>
									</span>
									<span>&nbsp;<?php _e('&agrave;','rencontre');?>&nbsp;
									<select name="tailleMax" onChange="f_max(this.options[this.selectedIndex].value,'formTrouve','tailleMin','tailleMax');">
										<?php for ($v=140;$v<220;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}?>
										
										<option value="220" selected>220&nbsp;<?php _e('cm','rencontre');?></option>
									</select>
									</span>
								</td>
							</tr>
							<tr>
								<td><?php _e('Poids','rencontre');?>&nbsp;:&nbsp;</td>
								<td><span><?php _e('de','rencontre');?>&nbsp;
									<select name="poidsMin" onChange="f_min(this.options[this.selectedIndex].value,'formTrouve','poidsMin','poidsMax');">
										<option value="140" selected>40&nbsp;<?php _e('kg','rencontre');?></option>
										<?php for ($v=41;$v<140;++$v) {echo '<option value="'.($v+100).'">'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}?>
										
									</select>
									</span>
									<span>&nbsp;<?php _e('&agrave;','rencontre');?>&nbsp;
									<select name="poidsMax" onChange="f_max(this.options[this.selectedIndex].value,'formTrouve','poidsMin','poidsMax');">
										<?php for ($v=40;$v<140;++$v) {echo '<option value="'.($v+100).'">'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}?>
										
										<option value="240" selected>140&nbsp;<?php _e('kg','rencontre');?></option>
									</select>
									</span>
								</td>
							</tr>
							<tr>
								<td><?php _e('Pays','rencontre');?>&nbsp;:</td>
								<td><select name="pays" onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect2');">
									<?php RencontreWidget::f_pays(1); ?>
									
									</select>
								</td>
							</tr>
							<tr>
								<td><?php _e('R&eacute;gion','rencontre');?>&nbsp;:</td>
								<td><select id="regionSelect2" name="region">
									<?php RencontreWidget::f_regionBDD(1); ?>
									
									</select>
								</td>
							</tr>
							<tr>
								<td><?php _e('Ville','rencontre');?>&nbsp;:</td>
								<td><input type="text" name="ville" /></td>
							</tr>
							<tr>
								<td><?php _e('Uniquement avec photo','rencontre');?>&nbsp;</td>
								<td><input type="checkbox" name="photo" value="0" /></td>
							</tr>
							<tr>
								<td><?php _e('Affinit&eacute; avec mon profil','rencontre');?>&nbsp;</td>
								<td><input type="checkbox" name="profil" value="0" disabled/></td>
							</tr>
							<tr>
								<td><?php _e('Mot dans l\'annonce','rencontre');?>&nbsp;:</td>
								<td><input type="text" name="mot" /></td>
							</tr>
							<tr>
								<td><?php _e('Pseudo','rencontre');?>&nbsp;:</td>
								<td><input type="text" name="pseudo" /></td>
							</tr>
							<tr><td></td>
								<td>
									<div class="button"><a href="javascript:void(0)" onClick="f_trouve();"><?php _e('Cherche','rencontre');?></a></div>
								</td>
							</tr>
							</table>
						</form>
					</div>
			<?php }
		else RencontreWidget::f_trouver();
		}
	//
	static function f_trouver()
		{
		// Resultat de la recherche plus
		global $wpdb;
		$options = get_option('rencontre_options');
		$limit = $options['limit'];
		$pagine = (isset($_POST['pagine'])?$_POST['pagine']:0);
		$suiv = 1;
		?> 
		
		<form name='rencPagine' method='post' action=''>
			<input type='hidden' name='page' value='liste' />
			<input type='hidden' name='pays' value='<?php echo $_POST['pays']; ?>' />
			<input type='hidden' name='region' value='<?php echo $_POST['region']; ?>' />
			<input type='hidden' name='ville' value='<?php echo $_POST['ville']; ?>' />
			<input type='hidden' name='pseudo' value='<?php echo $_POST['pseudo']; ?>' />
			<input type='hidden' name='zsex' value='<?php echo $_POST['zsex']; ?>' />
			<input type='hidden' name='ageMin' value='<?php echo $_POST['ageMin']; ?>' />
			<input type='hidden' name='ageMax' value='<?php echo $_POST['ageMax']; ?>' />
			<input type='hidden' name='tailleMin' value='<?php echo $_POST['tailleMin']; ?>' />
			<input type='hidden' name='tailleMax' value='<?php echo $_POST['tailleMax']; ?>' />
			<input type='hidden' name='poidsMin' value='<?php echo $_POST['poidsMin']; ?>' />
			<input type='hidden' name='poidsMax' value='<?php echo $_POST['poidsMax']; ?>' />
			<input type='hidden' name='mot' value='<?php echo $_POST['mot']; ?>' />
			<input type='hidden' name='photo' value='<?php echo $_POST['photo']; ?>' />
			<input type='hidden' name='id' value='<?php echo $_POST['id']; ?>' />
			<input type='hidden' name='pagine' value='<?php echo $pagine; ?>' />
		</form>
		<?php
		if ($_POST['pseudo']) $s="SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce FROM ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."users U WHERE U.user_login LIKE '%".strip_tags($_POST['pseudo'])."%' and R.i_sex=".strip_tags($_POST['zsex'])." and U.ID=R.user_id and P.user_id=R.user_id";
		else
			{
			$s="SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce FROM ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R WHERE P.user_id=R.user_id and R.i_sex=".strip_tags($_POST['zsex']);
			if ($_POST['ville']) $s.=" and R.c_ville LIKE '".strip_tags($_POST['ville'])."'";
			if ($_POST['ageMin']>18) {$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-strip_tags($_POST['ageMin']))); $s.=" and R.d_naissance<'".$zmin."'";}
			if ($_POST['ageMax']<99) {$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-strip_tags($_POST['ageMax'])));  $s.=" and R.d_naissance>'".$zmax."'";}
			if ($_POST['tailleMin']>140) $s.=" and R.i_taille>='".strip_tags($_POST['tailleMin'])."'";
			if ($_POST['tailleMax']<220) $s.=" and R.i_taille<='".strip_tags($_POST['tailleMax'])."'";
			if ($_POST['poidsMin']>140) $s.=" and R.i_poids>='".(strip_tags($_POST['poidsMin'])-100)."'";
			if ($_POST['poidsMax']<240) $s.=" and R.i_poids<='".(strip_tags($_POST['poidsMax'])-100)."'";
			if ($_POST['pays']) $s.=" and R.c_pays='".$_POST['pays']."'";
			if ($_POST['region']) $s.=" and R.c_region LIKE '".addslashes($wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".$_POST['region']."'"))."'";
			if ($_POST['mot']) $s.=" and (P.t_annonce LIKE '%".$_POST['mot']."%' or P.t_titre LIKE '%".strip_tags($_POST['mot'])."%')";
			if ($_POST['photo']=="0") $s.=" and R.i_photo>0";
			}
		$s.=" ORDER BY R.user_id DESC LIMIT ".($pagine*$limit).", ".($limit+1); // LIMIT indice du premier, nombre de resultat
		$q = $wpdb->get_results($s);
		if($wpdb->num_rows<=$limit) $suiv=0;
		else array_pop($q); // supp le dernier ($limit+1) qui sert a savoir si page suivante
		foreach($q as $r)
			{ ?>
			<div class="rencBox">
				<?php RencontreWidget::f_miniPortrait($r->user_id); ?>
				<div class="maxiBox right rel">
					<?php echo stripslashes($r->t_annonce); ?>
					<div style="height:38px;"></div>
					<div class="abso225">
						<?php echo __('Je cherche','rencontre').'&nbsp;'.(($r->i_zsex==1)?__('une femme','rencontre'):__('un homme','rencontre')).'<br />';
						echo '&nbsp;'.__('entre','rencontre').'&nbsp;'.$r->i_zage_min.'&nbsp;'.__('et','rencontre').'&nbsp;'.$r->i_zage_max.'&nbsp;'.__('ans','rencontre').'<br />';
						echo __('pour','rencontre').'&nbsp;'.(($r->i_zrelation==0)?__('Relation s&eacute;rieuse','rencontre'):''.(($r->i_zrelation==1)?__('Relation libre','rencontre'):__('Amiti&eacute;','rencontre'))); ?>
					</div>
					<div class="abso135">
						<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='ecrire';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Envoyer un mail','rencontre');?></a></div>
						<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='sourire1';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Lui sourire','rencontre');?></a></div>
						<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Profil','rencontre');?></a></div>
					</div>
				</div><!-- .grandeBox .right -->
				<div class="clear"></div>
			</div>
		<?php }
		if($pagine||$suiv)
			{
			echo '<div class="rencPagine">';
			if(($pagine+0)>0) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=parseInt(document.forms['rencPagine'].elements['pagine'].value)-1;document.forms['rencPagine'].submit();\">".__('Page pr&eacute;c&eacute;dente','rencontre')."</a>";
			for($v=max(0, $pagine-4); $v<$pagine; ++$v)
				{
				echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value='".$v."';document.forms['rencPagine'].submit();\">".$v."</a>";
				}
			echo "<span>".$pagine."</span>";
			if($suiv) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=parseInt(document.forms['rencPagine'].elements['pagine'].value)+1;document.forms['rencPagine'].submit();\">".__('Page suivante','rencontre')."</a>";
			echo '</div>';
			}

		}
	//
	static function f_nouveauMembre($f)
		{
		// entree : mon alias
		$nais = $_POST['annee'].'-'.((strlen($_POST['mois'])<2)?'0'.$_POST['mois']:$_POST['mois']).'-'.((strlen($_POST['jour'])<2)?'0'.$_POST['jour']:$_POST['jour']);
		global $wpdb;
		wp_set_current_user($f, $_POST['pseudo']);
	//	wp_set_auth_cookie($f); // deja envoye en ajax en validation du formulaire
		do_action('wp_login', $_POST['pseudo']); // connexion
		$region=$wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_POST['region'])."'");
		if($_POST['a1']!='update')
			{
			$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$f)); // suppression si existe deja
			$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f)); // suppression si existe deja
			$wpdb->insert($wpdb->prefix.'rencontre_users', array(
				'user_id'=>$f,
				'c_ip'=>$_SERVER['REMOTE_ADDR'],
				'c_pays'=>$_POST['pays'],
				'c_region'=>$region,
				'c_ville'=>strip_tags($_POST['ville']),
				'i_sex'=>strip_tags($_POST['sex']),
				'd_naissance'=>strip_tags($nais),
				'i_taille'=>strip_tags($_POST['taille']),
				'i_poids'=>strip_tags($_POST['poids']),
				'i_zsex'=>strip_tags($_POST['zsex']),
				'i_zage_min'=>strip_tags($_POST['zageMin']),
				'i_zage_max'=>strip_tags($_POST['zageMax']),
				'i_zrelation'=>strip_tags($_POST['zrelation']),
				'i_photo'=>0));
			$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f,'d_modif'=>date("Y-m-d H:i:s")));
			$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$f)); // suppression si existe deja
			}
		else
			{
			$wpdb->update($wpdb->prefix.'rencontre_users', array(
				'c_pays'=>$_POST['pays'],
				'c_region'=>$region,
				'c_ville'=>strip_tags($_POST['ville']),
				'i_sex'=>strip_tags($_POST['sex']),
				'd_naissance'=>strip_tags($nais),
				'i_taille'=>strip_tags($_POST['taille']),
				'i_poids'=>strip_tags($_POST['poids']),
				'i_zsex'=>strip_tags($_POST['zsex']),
				'i_zage_min'=>strip_tags($_POST['zageMin']),
				'i_zage_max'=>strip_tags($_POST['zageMax']),
				'i_zrelation'=>strip_tags($_POST['zrelation'])), 
				array('user_id'=>$f));
			}
		}
	//
	static function f_changePass($f)
		{
		wp_set_password($_POST['pass1'],$f); // changement MdP
		wp_clear_auth_cookie();
		wp_set_auth_cookie($f); // cookie pour rester connecte
		}

	//
	static function f_compte($mid)
		{
		// Fenetre de modification du compte
		global $wpdb;
		$q = $wpdb->get_row("SELECT U.user_email, R.c_pays, R.c_region, R.c_ville, R.i_sex, R.d_naissance, R.i_taille, R.i_poids, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation FROM ".$wpdb->prefix . "users U, ".$wpdb->prefix . "rencontre_users R WHERE U.ID=".$mid." and U.ID=R.user_id");
		list($Y, $m, $j) = explode('-', $q->d_naissance);
		?>
			<div id="rencAlert1"></div>
			<h2><?php _e('Changement du mot de passe','rencontre');?></h2>
			<form name="formPass" method='post' action=''>
			<input type='hidden' name='page' value='' /><input type='hidden' name='id' value='' />
			<table>
				<tr>
					<th><?php _e('Ancien','rencontre');?></th>
					<th><?php _e('Nouveau','rencontre');?></th>
					<th><?php _e('Retaper le nouveau','rencontre');?></th>
					<th></th>
				</tr>
				<tr>
					<td>
						<input name="pass0" type="password" size="9">
					</td>
					<td>
						<input name="pass1" type="password" size="9">
					</td>
					<td>
						<input name="pass2" type="password" size="9">
					</td>
					<td>
						<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_password(document.forms['formPass'].elements['pass0'].value,document.forms['formPass'].elements['pass1'].value,document.forms['formPass'].elements['pass2'].value,<?php echo $mid; ?>,'<?php echo admin_url('admin-ajax.php'); ?>')"><?php _e('Change','rencontre');?></a></div>
					</td>
				</tr>
			</table>
			</form>
			<div id="rencAlert"></div>
			<h2><?php _e('Mon compte','rencontre'); ?></h2>
			<form name="formNouveau" method='post' action=''>
			<input type='hidden' name='nouveau' value='' /><input type='hidden' name='a1' value='' />
			<table>
				<tr>
					<th><?php _e('Je suis','rencontre');?></th>
					<th><?php _e('N&eacute; le','rencontre');?></th>
				</tr>
				<tr>
					<td>
						<select name="sex" size=2>
							<option value="0"<?php echo ($q->i_sex==0)?' selected':''; ?>><?php _e('Homme','rencontre');?></option>
							<option value="1"<?php echo ($q->i_sex==1)?' selected':''; ?>><?php _e('Femme','rencontre');?></option>
						</select>
					</td>
					<td>
						<select name="jour" size=6>
							<?php for ($v=1;$v<32;++$v) {echo '<option value="'.$v.'"'.(($v==$j)?' selected':'').'>'.$v.'</option>';}?>
							
						</select>
						<select name="mois" size=6>
							<?php for ($v=1;$v<13;++$v) {echo '<option value="'.$v.'"'.(($v==$m)?' selected':'').'>'.$v.'</option>';}?>
							
						</select>
						<select name="annee" size=6>
							<?php $y=(date('Y')); for ($v=($y-99);$v<($y-18);++$v) {echo '<option value="'.$v.'"'.(($v==$Y)?' selected':'').'>'.$v.'</option>';}?>
							
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e('Mon pays','rencontre');?></th>
					<th><?php _e('Ma r&eacute;gion','rencontre');?></th>
				</tr>
				<tr>
					<td>
						<select name="pays" size=6 onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect2');">
							<?php RencontreWidget::f_pays($q->c_pays); ?>
							
						</select>
					</td>
					<td>
						<select id="regionSelect2" size=6 name="region">
							<?php if($q->c_region) RencontreWidget::f_regionBDD($q->c_region,$q->c_pays); else RencontreWidget::f_regionBDD(1,$q->c_pays); ?>
							
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e('Ma ville','rencontre');?></th>
					<th><?php // _e('Mon email','rencontre');?></th>
				</tr>
				<tr>
					<td>
						<input name="ville" type="text" size="18" value="<?php echo $q->c_ville; ?>">
					</td>
					<td>
						<input style="display:none;" name="email" type="text" size="18" value="<?php // echo $q->user_email; ?>">
					</td>
				</tr>
				<tr>
					<th><?php _e('Ma taille','rencontre');?></th>
					<th><?php _e('Mon poids','rencontre');?></th>
				</tr>
				<tr>
					<td>
						<select name="taille" size=6>
							<?php for ($v=140;$v<220;++$v) {echo '<option value="'.$v.'"'.(($v==$q->i_taille)?' selected':'').'>'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}?>
							
						</select>
					</td>
					<td>
						<select name="poids" size=6>
							<?php for ($v=40;$v<140;++$v) {echo '<option value="'.$v.'"'.(($v==$q->i_poids)?' selected':'').'>'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}?>
							
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e('Je cherche','rencontre');?></th>
					<th><?php _e('Age min/max','rencontre');?></th>
				</tr>
				<tr>
					<td>
						<select name="zsex" size=2>
							<option value="0"<?php echo ($q->i_zsex==0)?' selected':''; ?>><?php _e('Homme','rencontre');?></option>
							<option value="1"<?php echo ($q->i_zsex==1)?' selected':''; ?>><?php _e('Femme','rencontre');?></option>
						</select>
					</td>
					<td>
						<select name="zageMin" size=6 onChange="f_min(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
							<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'"'.(($v==$q->i_zage_min)?' selected':'').'>'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
							
						</select>
						<select name="zageMax" size=6 onChange="f_max(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
							<?php for ($v=18;$v<100;++$v) {echo '<option value="'.$v.'"'.(($v==$q->i_zage_max)?' selected':'').'>'.$v.'&nbsp;'.__('ans','rencontre').'</option>';}?>
							
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e('Pour','rencontre');?></th>
					<th></th>
				</tr>
				<tr>
					<td>
						<select name="zrelation" size=3>
							<option value="0"<?php echo ($q->i_zrelation==0)?' selected':''; ?>><?php _e('Relation s&eacute;rieuse','rencontre');?></option>
							<option value="1"<?php echo ($q->i_zrelation==1)?' selected':''; ?>><?php _e('Relation libre','rencontre');?></option>
							<option value="2"<?php echo ($q->i_zrelation==2)?' selected':''; ?>><?php _e('Amiti&eacute;','rencontre');?></option>
						</select>
					</td>
					<td>
						<div class="button"><a href="javascript:void(0)" onClick="document.forms['formNouveau'].elements['a1'].value='update';f_mod_nouveau(<?php echo $mid; ?>)"><?php _e('Sauvegarde','rencontre');?></a></div>
					</td>
				</tr>
			</table>
			</form>
			<h2><?php _e('Suppression du compte','rencontre');?></h2>
			<form name="formFin" method='post' action=''>
			<input type='hidden' name='page' value='' /><input type='hidden' name='id' value='' />
			<table><tr><th style="text-align:left;">
				<?php _e('Cette action provoquera la suppression compl&egrave;te de votre compte et de tout ce qui vous concerne de notre serveur. Nous ne conservons aucun historique des comptes supprim&eacute;s.','rencontre');?>
				</th></tr>
				<tr><td>
				<strong><?php _e('Attention, cette action est irr&eacute;versible !','rencontre');?></strong>
				<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_fin(document.forms['formFin'].elements['id'].value,<?php echo $mid; ?>)"><?php _e('Supprimer le compte','rencontre');?></a></div>
			</td></tr></table>
			</form>
		<?php }
	//
	static function f_sourire($f,$mid)
		{
		// envoi un sourire a ID=$f
		global $wpdb;
		// 1. mon compte : sourireOut
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
		$action= json_decode($q,true);
		$c = count($action['sourireOut']);
		if ($c) { foreach ($action['sourireOut'] as $r) { if ($r['i']==$f) {_e('sourire d&eacute;j&agrave; envoy&eacute;','rencontre'); return; } } } // deja souri
		$action['sourireOut'][$c]['i'] = ($f+0);
		$action['sourireOut'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$mid));
		// 2. son compte : sourireIn
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$action= json_decode($q,true);
		$c = count($action['sourireIn']);
		$action['sourireIn'][$c]['i'] = ($mid+0);
		$action['sourireIn'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		_e('sourire envoy&eacute;','rencontre');
//	[	{"a":"sourireIn","v":[{"i":10,"d":"2013-12-15"},{"i":32,"d":"2013-12-15"}]},
//		{"a":"sourireOut","v":[{"i":15,"d":"2013-12-15"},{"i":28,"d":"2013-12-15"},{"i":41,"d":"2013-12-15"}]},
//		{"a":"contactIn","v":[{"i":8,"d":"2013-12-15"}]},
//		{"a":"contactOut","v":[{"i":17,"d":"2013-12-15"},{"i":18,"d":"2013-12-15"},{"i":19,"d":"2013-12-15"}]},
//		{"a":"visite","v":[{"i":25,"d":"2013-12-15"}]},
//		{"a":"bloque","v":[{"i":50,"d":"2013-12-15"},{"i":51,"d":"2013-12-15"}]}
//	]
//
		}
	//
	static function f_demcont($f,$mid)
		{
		// demander un contact a ID=$f
		global $wpdb;
		// 1. mon compte : contactOut
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
		$action= json_decode($q,true);
		$c = count($action['contactOut']);
		if ($c) { foreach ($action['contactOut'] as $r) { if ($r['i']==$f) {_e('contact d&eacute;j&agrave; demand&eacute;','rencontre'); return; } } } // deja demande
		$action['contactOut'][$c]['i'] = ($f+0);
		$action['contactOut'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$mid));
		// 2. son compte : contactIn
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$action= json_decode($q,true);
		$c = count($action['contactIn']);
		$action['contactIn'][$c]['i'] = ($mid+0);
		$action['contactIn'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		// memo pour mail CRON
		if (!is_dir(dirname(__FILE__).'/cron_liste/')) mkdir(dirname(__FILE__).'/cron_liste/');
		if (!file_exists(dirname(__FILE__).'/cron_liste/'.$f.'.txt')){ $t=fopen(dirname(__FILE__).'/cron_liste/'.$f.'.txt', 'w'); fclose($t); }
		_e('demande de contact envoy&eacute;e','rencontre');
		}
	//
	static function f_signal($f,$mid)
		{
		// envoi un signalement sur ID=$f
		global $wpdb;
		// 1. mon compte : sourireOut
		$q = $wpdb->get_var("SELECT t_signal FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$signal= json_decode($q,true);
		$c = count($signal);
		if ($c) { foreach ($signal as $r) { if ($r['i']==$mid) {_e('Signalement d&eacute;j&agrave; &eacute;ffectu&eacute;','rencontre'); return; } } } // deja signale par mid
		$signal[$c]['i'] = ($mid+0);
		$signal[$c]['d'] = date("Y-m-d");
		$out = json_encode($signal);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_signal'=>$out), array('user_id'=>$f));
		_e('Merci pour votre signalement','rencontre');
		}
	//
	static function f_bloque($f,$mid)
		{
		// bloque ou debloque ID=$f
		global $wpdb;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
		$action= json_decode($q,true);
		$c = count($action['bloque']); $c1=0;
		if ($c) {foreach ($action['bloque'] as $r)
			{
			if ($r['i']==$f) // deja bloque : on debloque
				{
				unset($action['bloque'][$c1]['i']);unset($action['bloque'][$c1]['d']);
				$action['bloque']=array_filter($action['bloque']);
				$out = json_encode($action);
				$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$mid));
				return;
				}
			++$c1;
			}}
		// pas bloque : on bloque
		$action['bloque'][$c]['i'] = ($f+0);
		$action['bloque'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$mid));
		}
	//
	static function f_etat_bloque($f,$mid)
		{
		// regarde si un membre est bloque
		global $wpdb;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
		$action= json_decode($q,true);
		$c = count($action['bloque']); if ($c) {foreach ($action['bloque'] as $r){if ($r['i']==$f) return true; }} // est bloque
		else return false;
		}
	//
	static function f_etat_bloque1($f,$mid,$action=0)
		{
		// regarde si un membre m a bloque
		if ($action==0)
			{
			global $wpdb;
			$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
			$action= json_decode($q,true);
			}
		$c = count($action['bloque']); if ($c) {foreach ($action['bloque'] as $r){if ($r['i']==$mid) return true; }} // est bloque
		else return false;
		}
	//
	static function f_visite($f,$mid)
		{
		// id : MID visite F - sauvegarde chez F
		global $wpdb;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$action= json_decode($q,true);
		$c = count($action['visite']);
		if ($c>60) RencontreWidget::f_menage_action($f,$action);
		if ($c) {foreach ($action['visite'] as $r) { if ($r['i']==$mid) return; }}
		// pas encore vu
		$action['visite'][$c]['i'] = ($mid+0);
		$action['visite'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		}
	//
	static function f_menage_action($f,$action)
		{
		// fait le menage dans le json action - limite a 50 elements par item
		$a = array("sourireIn","sourireOut","contactIn","contactOut","demcont","visite","bloque");
		for ($v=0; $v<count($a); ++$v)
			{
			$c = count($action[$a[$v]]);
			for ($w=0; $w<$c-50; ++$w) 
				{
				unset($action[$a[$v]][$w]['i']); 
				unset($action[$a[$v]][$w]['d']);
				}
			if($action[$a[$v]]) $action[$a[$v]]=array_filter($action[$a[$v]]);
			}
		$out = json_encode($action);
		global $wpdb;
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		}
	//
	} // CLASSE
//
