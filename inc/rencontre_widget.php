<?php
//
class RencontreWidget extends WP_widget
	{
 	function __construct()
		{
		parent::__construct('rencontre-widget','Rencontre',array('description'=>__('Widget to integrate the dating website', 'rencontre'),));
		}
	//
	function widget($arguments, $data) // Partie Site
		{
		if(current_user_can("administrator")) return;
		wp_enqueue_style('rencontre', plugins_url('rencontre/css/rencontre.css'));
		wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre.js?r='.rand()));
		global $current_user; global $wpdb; global $rencBlock;
		global $drap; global $drapNom; global $rencOpt; global $rencDiv; global $rencidfm; global $rencCustom;
		$rencidfm = ((isset($_SESSION["rencidfm"])&&!isset($_GET["rencidfm"]))?$_SESSION["rencidfm"]:''); // lien direct vers la fiche d un membre depuis un mail
		$mid = $current_user->ID; // Mon id
		$rencBlock = (($current_user->user_status==1||$current_user->user_status==3)?1:0); // blocked
	if(!isset($rencOpt['imnb'])) $rencOpt['imnb']=4;
		$r = $rencDiv['basedir'].'/portrait';if(!is_dir($r)) mkdir($r);
		$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
		$drap=''; $drapNom='';
		foreach($q as $r)
			{
			if($r->c_liste_categ=='d') $drap[$r->c_liste_iso] = $r->c_liste_valeur;
			else if($r->c_liste_categ=='p')$drapNom[$r->c_liste_iso] = $r->c_liste_valeur;
			}
		if (isset($_POST['nouveau']) && isset($_POST['a1']) && $_POST['a1']==$mid) RencontreWidget::f_nouveauMembre($mid,strip_tags($_POST['nouveau']));
		// *****************************************************************************************************************
		// 0. Partie menu
		require(dirname (__FILE__) . '/../lang/rencontre-js-lang.php');
		$lang += array('mid'=>$current_user->ID,'ajaxchat'=>plugins_url('rencontre/inc/rencontre_tchat.php'),'wpajax'=>admin_url('admin-ajax.php'),'tchaton'=>$rencOpt['tchat']);
		wp_localize_script('rencontre', 'rencobjet', $lang);
		$fantome = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' and CHAR_LENGTH(t_titre)>4 and CHAR_LENGTH(t_annonce)>30 ");
		?>

		<div id="widgRenc" class="widgRenc">
			<script type="text/javascript">
				var siteUrl='<?php echo $rencDiv['siteurl']; ?>';
				<?php if(isset($_SESSION["tchat"]))
					{
					$a = $wpdb->get_var("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID=".$_SESSION["tchat"]);
					echo 'jQuery(document).ready(function(){f_tchat_veille('.$_SESSION["tchat"].',\''.$a.'\');});';
					}
				?>
			</script>
			<div id="rencTchat" class="rencTchat"></div>
			<?php
			$ho = false; if(has_filter('rencCamP', 'f_rencCamP')) $ho = apply_filters('rencCamP', $ho);
			if(!$ho) { ?><div id="rencCam" class="rencCam"></div><div id="rencCam2" class="rencCam2"></div><?php } ?>
			<div class="rencMenu pleineBox">
				<form name='rencMenu' method='get' action=''>
					<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
					<input type='hidden' name='page' value='' />
					<input type='hidden' name='id' value='' />
					<div class="rencBox">
						<ul>
							<a href="<?php if($rencOpt['home'])echo $rencOpt['home']; else {echo 'http://'.$_SERVER['HTTP_HOST']; $a=explode("?",$_SERVER['REQUEST_URI']); echo $a[0];} ?>"><li <?php echo (strstr($_SESSION['rencontre'],'mini')?'class="current"':''); ?>><?php _e('My homepage','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li <?php if (strstr($_SESSION['rencontre'],'portrait') && $_GET["id"]==$mid) echo 'class="current"'; ?>><?php _e('My card','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='change';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li <?php if (strstr($_SESSION['rencontre'],'change')) echo 'class="current"'; else if(!$fantome) echo 'class="boutonred"'; ?>><?php _e('Edit My Profile','rencontre');?></li></a>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='msg';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li <?php echo (strstr($_SESSION['rencontre'],'msg')?'class="current"':''); echo (strstr($_SESSION['rencontre'],'ecrire')?'class="current"':'');?>><?php _e('Messaging','rencontre'); echo RencontreWidget::f_count_inbox($current_user->user_login); ?></li></a>
							<?php
							$ho = false; if(has_filter('rencSearchP', 'f_rencSearchP')) $ho = apply_filters('rencSearchP', $ho);
							if(!$ho) { ?><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='trouve';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li <?php echo (strstr($_SESSION['rencontre'],'trouve')?'class="current"':''); echo (strstr($_SESSION['rencontre'],'cherche')?'class="current"':''); ?>><?php _e('Search','rencontre');?></li></a><?php }
							else echo '<li class="rencLiOff">'.__('Search','rencontre').'</li>';
							?>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='compte';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><li <?php echo (strstr($_SESSION['rencontre'],'compte')?'class="current"':''); ?>><?php _e('My Account','rencontre');?></li></a>
							<span class="rencFBlike">
							<?php if($rencOpt['facebook'])
								{
								$fb = $rencOpt['facebook'];
								if (get_locale()!="fr_FR")
									{
									if (strpos($fb,'locale=')===false) $fb = str_replace('.php?','.php?locale=en_US&',$fb);
									else $fb = str_replace('fr_FR','en_US',$fb);
									}
								echo $fb;
								} ?>
								
							</span>
							<div class="rencBonjour">
								<?php _e('Hello','rencontre'); echo '&nbsp;'.($current_user->user_login); ?>
							</div>
						</ul>
					</div>
				</form>
			</div>
			<?php if($rencBlock)
				{
				if(!isset($rencCustom['blocked']) || !isset($rencCustom['blockedText']) || $rencCustom['blockedText']=='') echo '<div class="rencBlock">'.__('Your account is blocked. You are invisible. Change your profile.','rencontre').'</div>';
				else echo stripslashes($rencCustom['blockedText']);
				}
		if(isset($_SESSION['rencontre']) && $_SESSION['rencontre']=='gate') self::rencGate(); // Entry screening
		//
		// 1. Nouveau visiteur
		else if(strstr($_SESSION['rencontre'],'nouveau'))
			{
			$q = $wpdb->get_var("SELECT S.id FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_prison S WHERE U.ID='".$mid."' and S.c_mail=U.user_email");
			if ($q)
				{ ?>
			<div class="pleineBox">
				<div class="rencBox">
					<div class="rencNouveau">
						<h3><?php
						if(!isset($rencCustom['jail']) || !isset($rencCustom['jailText']) || $rencCustom['jailText']=='') echo __('Your email address is currently in quarantine. Sorry','rencontre');
						else echo stripslashes($rencCustom['jailText']);
						?>&nbsp;</h3>
					</div>
				</div>
			</div>
				<?php
				}
			else if($_SESSION['rencontre']=='nouveau')
				{ ?>
			
			<div class="pleineBox">
				<div class="rencBox">
					<div class="rencNouveau">
						<h3><?php _e('Hello','rencontre'); echo '&nbsp;'.($current_user->user_login); echo ", ".__('welcome to the site','rencontre').'&nbsp;'; bloginfo( 'name' ); ?></h3>
						<p>
						<?php if(!isset($rencCustom['new']) || !isset($rencCustom['newText']) || $rencCustom['newText']=='') { ?>
						<?php _e('You will access all the possibilities offered by the site in few minutes.','rencontre'); ?>
						<?php _e('Before that, you need to provide some information requested below.','rencontre'); ?>
						</p>
						<p>
						<?php _e('We would like to inform you that we do not use your personal data outside of this site.','rencontre'); ?>
						<?php _e('Deleting your account on your part or ours, causes the deletion of all your data.','rencontre'); ?>
						<?php _e('This also applies to messages that you have sent to other members as well as those they have sent to you.','rencontre'); ?>
						</p>
						<p>
						<?php _e('We wish you nice encounters.','rencontre'); ?>
						<?php } else echo stripslashes($rencCustom['newText']); ?>
						</p>
						<div id="rencAlert"></div>
						<div class="rencEvol">
						<?php if(!isset($rencCustom['place'])) echo '<div class="rencEvol25">1 / 4</div>';
						else echo '<div class="rencEvol33">1 / 3</div>'; ?>
						<div style="clear:left;"></div></div>
						<form name="formNouveau" method='post' action=''>
						<input type='hidden' name='nouveau' value='1' />
						<input type='hidden' name='a1' value='' />
						<table>
							<tr>
								<?php
								echo '<th>'.__('I am','rencontre').'</th>';
								if(!isset($rencCustom['born'])) echo '<th>'.__('Born','rencontre').'</th>';
								if(!isset($rencCustom['size'])) echo '<th>'.__('My size','rencontre').'</th>';
								if(!isset($rencCustom['weight'])) echo '<th>'.__('My weight','rencontre').'</th>';
								?>
							</tr>
							<tr>
								<td>
									<select name="sex" size=2>
									<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) echo '<option value="'.$v.'">'.$rencOpt['iam'][$v].'</option>'; ?>
									</select>
								</td>
								<?php if(!isset($rencCustom['born'])) { ?>
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
								<?php } if(!isset($rencCustom['size'])) { ?>
								<td>
									<select name="taille" size=6>
										<?php for ($v=140;$v<220;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}?>
										
									</select>
								</td>
								<?php } if(!isset($rencCustom['weight'])) { ?>
								<td>
									<select name="poids" size=6>
										<?php for ($v=40;$v<140;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}?>
										
									</select>
								</td>
								<?php } ?>
							</tr>
						</table>
						<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_nouveau(<?php echo $mid; ?>,'<?php echo admin_url('admin-ajax.php'); ?>',0)"><?php _e('Send','rencontre');?></a></div>
						</form>
					</div>
				</div>
			</div>
			<?php
				}
			else if($_SESSION['rencontre']=='nouveau1')
				{ ?>
			
			<div class="pleineBox">
				<div class="rencBox">
					<div class="rencNouveau">
						<h3><?php _e('Hello','rencontre'); echo '&nbsp;'.($current_user->user_login); echo ", ".__('welcome to the site','rencontre').'&nbsp;'; bloginfo( 'name' ); ?></h3>
						<div id="rencAlert"></div>
						<div class="rencEvol"><div class="rencEvol50">2 / 4</div><div style="clear:left;"></div></div>
						<form name="formNouveau" method='post' action=''>
						<input type='hidden' name='nouveau' value='2' />
						<input type='hidden' name='a1' value='' />
						<table>
							<tr>
								<?php if(!isset($rencCustom['country'])) echo '<th>'.__('My country','rencontre').'</th>';
								if(!isset($rencCustom['region'])) echo '<th>'.__('My region','rencontre').'</th>'; ?>
								<th><?php _e('My city','rencontre');?></th>
							</tr>
							<tr>
							<?php if(!isset($rencCustom['country'])) { ?>
								<td>
									<select id="rencPays" name="pays" size=6 onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect1');">
										<?php RencontreWidget::f_pays($rencOpt['pays']); ?>
										
									</select>
								</td>
							<?php } if(!isset($rencCustom['region'])) { ?>
								<td>
									<select id="regionSelect1" size=6 name="region">
										<?php RencontreWidget::f_regionBDD(1,$rencOpt['pays']); ?>
										
									</select>
								</td>
							<?php } ?>
								<td>
									<input id="rencVille" name="ville" type="text" size="12" <?php
										if (function_exists('wpGeonames')) echo 'onkeyup="if(!rmap)f_cityMap(this.value,'.(isset($rencCustom['country'])?"'".$rencOpt['pays']."'":'document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].text').',\'0\',1);f_city(this.value,\''.admin_url('admin-ajax.php').'\',document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].value,0);"'; 
										else echo 'onkeyup="if(!rmap)f_cityMap(this.value,document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].text,\'0\',1);"'; 
										?> />
									<input id="gps" name="gps" type="hidden" />
									<div class="rencCity" id="rencCity"></div>
									<?php if(isset($rencOpt['map']) && $rencOpt['map']) echo '<div class="rencTMap" id="rencTMap">'.__('Adjust the location by moving / zooming the map.','rencontre').'<br />'.__('Clicking on the map will place the cursor.','rencontre').'<br /><br /><div class="button" onClick="f_cityOk();">'.__('Validate the position','rencontre').'</div></div>'; ?>
								</td>
							</tr>
						</table>
						<?php if(isset($rencOpt['map']) && $rencOpt['map']) echo '<div id="rencMap"></div>'; ?>
						<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_nouveau(<?php echo $mid; ?>,'<?php echo admin_url('admin-ajax.php'); ?>',1)"><?php _e('Send','rencontre');?></a></div>
						</form>
					</div>
				</div>
			</div>
			<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
			<?php
				}
			else if($_SESSION['rencontre']=='nouveau2')
				{ ?>
			
			<div class="pleineBox">
				<div class="rencBox">
					<div class="rencNouveau">
						<h3><?php _e('Hello','rencontre'); echo '&nbsp;'.($current_user->user_login); echo ", ".__('welcome to the site','rencontre').'&nbsp;'; bloginfo( 'name' ); ?></h3>
						<div id="rencAlert"></div>
						<div class="rencEvol">
						<?php if(!isset($rencCustom['place'])) echo '<div class="rencEvol75">3 / 4</div>';
						else echo '<div class="rencEvol66">2 / 3</div>'; ?>
						<div style="clear:left;"></div></div>
						<form name="formNouveau" method='post' action=''>
						<input type='hidden' name='nouveau' value='3' /><input type='hidden' name='a1' value='' />
						<table>
							<tr>
								<th><?php _e('I\'m looking for','rencontre');?></th>
								<?php if(!isset($rencCustom['born'])) echo '<th>'.__('Age min/max','rencontre').'</th>'; ?>
								<th><?php _e('For','rencontre');?></th>
							</tr>
							<tr>
								<td>
									<select name="zsex" size=2>
									<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) echo '<option value="'.$v.'">'.$rencOpt['iam'][$v].'</option>'; ?>
									</select>
								</td>
								<?php if(!isset($rencCustom['born'])) { ?>
								<td>
									<select name="zageMin" size=6 onChange="f_min(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
										<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
										
									</select>
									<select name="zageMax" size=6 onChange="f_max(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
										<?php for ($v=19;$v<100;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
										
									</select>
								</td>
								<?php } ?>
								<td>
									<select name="zrelation" size=3>
									<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) echo '<option value="'.$v.'">'.$rencOpt['for'][$v].'</option>'; ?>
									</select>
								</td>
							</tr>
						</table>
						<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_nouveau(<?php echo $mid; ?>,'<?php echo admin_url('admin-ajax.php'); ?>',2)"><?php _e('Send','rencontre');?></a></div>
						</form>
					</div>
				</div>
			</div>
			<?php
				}
			else if($_SESSION['rencontre']=='nouveau3')
				{ ?>
			
			<div class="pleineBox">
				<div class="rencBox">
					<div class="rencNouveau">
						<h3><?php _e('Hello','rencontre'); echo '&nbsp;'.($current_user->user_login); echo ", ".__('welcome to the site','rencontre').'&nbsp;'; bloginfo( 'name' ); ?></h3>
						<div id="rencAlert"></div>
						<div class="rencEvol">
						<?php if(!isset($rencCustom['place'])) echo '<div class="rencEvol100">4 / 4</div>';
						else echo '<div class="rencEvol100">3 / 3</div>'; ?>
						<div style="clear:left;"></div></div>
						<form name="formNouveau" method='post' action=''>
						<input type='hidden' name='nouveau' value='OK' /><input type='hidden' name='a1' value='' />
						<table>
							<tr>
								<th><?php _e('Change nickname (after, it will not be possible)','rencontre');?></th>
							<?php if(!isset($rencOpt['passw']) || !$rencOpt['passw']) echo '<th>'.__('New password (6 min)','rencontre').'</th><th>'.__('New password (again)','rencontre').'</th>'; ?>
							</tr>
							<tr>
								<td>
									<input name="pseudo" type="text" size="12" value="<?php echo $current_user->user_login; ?>" /> 
								</td>
							<?php if(!isset($rencOpt['passw']) || !$rencOpt['passw']) echo '<td><input name="pass1" type="password" size="12" /></td><td><input name="pass2" type="password" size="12" /></td>';
							else echo '<input name="pass1" type="hidden" value="aQwZsXeDc" /><input name="pass2" type="hidden" value="aQwZsXeDc" />'; ?>
							</tr>
						</table>
						<span><?php // _e('You will have to reconnect, do not worry.','rencontre');?></span>
						<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_nouveau(<?php echo $mid; ?>,'<?php echo admin_url('admin-ajax.php'); ?>',3)"><?php _e('Send','rencontre');?></a></div>
						</form>
					</div>
				</div>
			</div>
			<?php
				}
			}
		//
		// 2. Partie portrait
		else if ((strstr($_SESSION['rencontre'],'portrait') && $_GET["id"]) || $rencidfm)
			{
			$id = ($rencidfm)?$rencidfm:strip_tags($_GET["id"]);
			$rencidfm = 0; unset($_SESSION['rencidfm']); // RAZ du lien messagerie
			$line = RencontreWidget::f_enLigne($id); // true : en ligne - false : hors ligne
			$bl = false;
			if (strstr($_SESSION['rencontre'],'bloque')) RencontreWidget::f_bloque($id);
			if ($mid!=$id)
				{
				RencontreWidget::f_visite($id); // visite du profil - enregistrement sur ID
				$bl = RencontreWidget::f_etat_bloque($id); // je l ai bloque ? - lecture de MID
				}
			$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_region, R.c_ville, R.i_sex, R.d_naissance, R.i_taille, R.i_poids, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, R.i_photo, R.e_lat, R.e_lon, R.d_session, P.t_titre, P.t_annonce, P.t_profil, P.t_action FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$id." and R.user_id=P.user_id and R.user_id=U.ID and U.user_status=0");
			if($s)
				{
				$bl1= RencontreWidget::f_etat_bloque1($id,$s->t_action); // je suis bloque ?
				?>
			
			<div class="rencPortrait">
				<div class="petiteBox left">
					<div class="rencBox calign">
						<img id="portraitGrande" src="<?php if(($s->i_photo)!=0) echo $rencDiv['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.Rencontre::f_img((($s->ID)*10).'-grande'); else echo plugins_url('rencontre/images').'/no-photo600'; ?>.jpg" alt="" />
						<div class="rencBlocimg">
						<?php $ho = false; if(has_filter('rencNbPhotoExtP', 'f_rencNbPhotoExtP')) $ho = apply_filters('rencNbPhotoExtP', $id);
						for ($v=0;$v<($ho!==false?min($ho,$rencOpt['imnb']):$rencOpt['imnb']);++$v)
							{
							if ($s->i_photo>=($s->ID)*10+$v)
								{
								echo '<a class="rencZoombox zgallery1" href="'.$rencDiv['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.Rencontre::f_img((($s->ID)*10+$v)).'.jpg"><img onMouseOver="f_vignette('.(($s->ID)*10+$v).',\''.Rencontre::f_img((($s->ID)*10+$v).'-grande').'\')" class="portraitMini" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.Rencontre::f_img((($s->ID)*10+$v).'-mini').'.jpg?r='.rand().'" alt="" /></a>'."\n";
								echo '<img style="display:none;" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.Rencontre::f_img((($s->ID)*10+$v).'-grande').'.jpg?r='.rand().'" />';
								echo '<img style="display:none;" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($s->ID)/1000).'/'.Rencontre::f_img((($s->ID)*10+$v)).'.jpg?r='.rand().'" />'."\n";
								}
							else { ?><img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="" />
							<?php echo "\n"; } } ?>
							
						</div>
					</div>
				</div>
				<div class="grandeBox right">
					<div class="rencBox">
						<?php
						if($s->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place'])) echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />';
						echo ($line)?'<span class="rencInline">'.__('online','rencontre').'</span>':'<span class="rencOutline">'.__('offline','rencontre').'</span>';  ?>

						<div class="grid_10">
							<h3><?php echo $s->display_name; if($bl) echo '<span style="font-weight:bold;color:red;text-transform:uppercase;">&nbsp;'.__('(blocked)','rencontre').'</span>'; ?></h3>
							<?php if(!isset($rencCustom['place']))
							{
							echo '<div class="ville">'.$s->c_ville;
							if($s->c_region && !isset($rencCustom['region'])) echo ' <em>('.$s->c_region.')</em>';
							echo ' &nbsp;'; RencontreWidget::f_distance($s->e_lat,$s->e_lon);
							echo '</div>';
							} ?>
							<div class="renc1 firstMaj">
							<?php if(isset($rencOpt['iam'][$s->i_sex])) echo $rencOpt['iam'][$s->i_sex];
							echo ((!isset($rencCustom['born']) && strpos($s->d_naissance,'0000')===false)?'&nbsp;&nbsp;-&nbsp;&nbsp;'.Rencontre::f_age($s->d_naissance).'&nbsp;'.__('years','rencontre'):'');
							echo (!isset($rencCustom['size'])?'&nbsp;&nbsp;-&nbsp;&nbsp;'.$s->i_taille.' '.__('cm','rencontre'):'');
							echo (!isset($rencCustom['weight'])?'&nbsp;&nbsp;-&nbsp;&nbsp;'.$s->i_poids.' '.__('kg','rencontre'):''); ?>
							</div>
							<div class="titre"><?php echo stripslashes($s->t_titre); ?></div>
						</div>
						<p><?php echo stripslashes($s->t_annonce); ?></p>
							<?php $ho = false; if(has_filter('rencAstro2P', 'f_rencAstro2P') && !isset($rencCustom['born'])) $ho = apply_filters('rencAstro2P', $s->d_naissance);
							if($ho) echo $ho;
							else echo '<div>&nbsp;</div>'; ?>
						<div class="abso225 firstMaj">
							<?php echo __('I\'m looking for','rencontre').'&nbsp;';
							if(isset($rencOpt['iam'][$s->i_zsex])) echo $rencOpt['iam'][$s->i_zsex];
							if($s->i_zsex==$s->i_sex && !isset($rencCustom['sex'])) echo '&nbsp;'.__('gay','rencontre');
							if(!isset($rencCustom['born']) && $s->i_zage_min) echo '&nbsp;'.__('between','rencontre').'&nbsp;'.$s->i_zage_min.'&nbsp;'.__('and','rencontre').'&nbsp;'.$s->i_zage_max.'&nbsp;'.__('years','rencontre');
							if(isset($rencOpt['for'][$s->i_zrelation])) echo '&nbsp;'.__('for','rencontre').'&nbsp;'.$rencOpt['for'][$s->i_zrelation]; ?>
						</div>
						<?php if(isset($s->d_session) && $mid!=$id) echo '<div class="rencDate" style="text-transform:capitalize;width:auto;position:absolute;right:5px;bottom:0;">'.__('online','rencontre').'&nbsp;:&nbsp;'.substr($s->d_session,8,2).'.'.substr($s->d_session,5,2).'.'.substr($s->d_session,0,4).'</div>'; ?>
					</div>
					<?php if ($id!=$mid) { ?>
					
					<div class="rencBox">
						<ul>
							<?php if (!$bl1)
							{
							$ho = false; if(has_filter('rencSendP', 'f_rencSendP')) $ho = apply_filters('rencSendP', $ho);
							if(!$ho && !$rencBlock) { ?><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='ecrire';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Send a message','rencontre');?></li></a><?php }
							else echo '<li class="rencLiOff">'.__('Send a message','rencontre').'</li>';
							if(!isset($rencCustom['smile']))
								{
								$ho = false; if(has_filter('rencSmileP', 'f_rencSmileP')) $ho = apply_filters('rencSmileP', $ho);
								if(!$ho && !$rencBlock) { ?><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='sourire';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Smile','rencontre');?></li></a><?php }
								else echo '<li class="rencLiOff">'.__('Smile','rencontre').'</li>';
								}
							$ho = false; if(has_filter('rencContactReqP', 'f_rencContactReqP')) $ho = apply_filters('rencContactReqP', $ho);
							if(!$ho && !$rencBlock) { ?><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='demcont';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php _e('Ask for a contact','rencontre');?></li></a><?php }
							else echo '<li class="rencLiOff">'.__('Ask for a contact','rencontre').'</li>';
							}
							else echo '<li class="rencLiOff">'.__('Send a message','rencontre').'</li>'.(isset($rencCustom['smile'])?'<li class="rencLiOff">'.__('Smile','rencontre').'</li>':'').'<li class="rencLiOff">'.__('Ask for a contact','rencontre').'</li>'; ?>
							<?php 
							$ho = false; if(has_filter('rencChatP', 'f_rencChatP')) $ho = apply_filters('rencChatP', $id);
							if (!$ho && !$rencBlock && $line && !$bl1 && $rencOpt['tchat']==1) echo '<a href="javascript:void(0)" onClick="f_tchat('.$mid.','.$id.',\''.plugins_url('rencontre/inc/rencontre_tchat.php').'\',1,\''.$s->display_name.'\')"><li>'.__('Chat','rencontre').'</li></a>';
							else if ($rencOpt['tchat']==1) echo '<li class="rencLiOff">'.__('Chat','rencontre').'</li>'; 
							?>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='bloque';document.forms['rencMenu'].elements['id'].value='<?php echo $s->ID; ?>';document.forms['rencMenu'].submit();"><li><?php echo (!$bl)?__('Block','rencontre'):__('Unblock','rencontre'); ?></li></a>
							<?php if(!isset($rencCustom['report'])) echo '<a href="javascript:void(0)" onClick="document.forms[\'rencMenu\'].elements[\'page\'].value=\'signale\';document.forms[\'rencMenu\'].elements[\'id\'].value=\''.$s->ID.'\';document.forms[\'rencMenu\'].submit();" title="'.__('Report a fake profile or inappropriate content','rencontre').'"><li>'.__('Report','rencontre').'</li></a>';
							if ($bl1) echo '<span style="position:absolute;left:80px;top:12px;font-size:120%;color:red;">'.__('You are blocked !','rencontre').'</span>'; ?>
						</ul>
					</div>
					<?php if (strstr($_SESSION['rencontre'],'sourire') || strstr($_SESSION['rencontre'],'signale') || strstr($_SESSION['rencontre'],'demcont') || (isset($_GET["sujet"]) && $_GET["sujet"])) { ?><div id="infoChange">
						<div class="rencBox">
							<em>
								<?php
								if (strstr($_SESSION['rencontre'],'sourire')) {RencontreWidget::f_sourire(strip_tags($_GET["id"]));}
								else if (strstr($_SESSION['rencontre'],'signale')) {RencontreWidget::f_signal(strip_tags($_GET["id"]));}
								else if (strstr($_SESSION['rencontre'],'demcont')) {RencontreWidget::f_demcont(strip_tags($_GET["id"]));}
								?>
							</em>
						</div>
					</div><?php } 
						} ?>
					
					<div class="rencBox">
						<div class="br"></div>
					<?php
					$ho = false; if(has_filter('rencViewpP', 'f_rencViewpP')) $ho = apply_filters('rencViewpP', $ho);
					if(!$ho)
						{
						$profil = json_decode($s->t_profil,true);
						$out = '';
						if ($profil) foreach($profil as $h)
							{
							$q = $wpdb->get_row("SELECT c_categ, c_label, t_valeur, i_type FROM ".$wpdb->prefix."rencontre_profil WHERE id=".$h['i']." AND i_poids<5 AND c_lang='".substr($rencDiv['lang'],0,2)."'");
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
						
						<script type="text/javascript" src="<?php echo plugins_url('rencontre/js') ?>/zoombox-min.js"></script>
						<script type="text/javascript">(function($){$('a.rencZoombox').zoombox();})(jQuery);</script>
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
						}
						?>
					
					</div>
				</div>
			</div>
		<?php }
		}
		//
		// 3. Partie Changement du portrait
		else if (strstr($_SESSION['rencontre'],'change'))
			{
			// recuperation de la table profil : $in[]
			$q = $wpdb->get_results("SELECT P.id, P.c_categ, P.c_label, P.t_valeur, P.i_type FROM ".$wpdb->prefix."rencontre_profil P WHERE P.c_lang='".substr($rencDiv['lang'],0,2)."' AND P.i_poids<5 ORDER BY P.c_categ");
			$in = '';
			foreach ($q as $r)
				{
				$in[$r->id][0] = $r->i_type;
				$in[$r->id][1] = $r->c_categ;
				$in[$r->id][2] = $r->c_label;
				$in[$r->id][3] = $r->t_valeur;
				}
			if (isset($_POST["a1"]) && ((!isset($_SESSION['a1']) || !isset($_SESSION['a2'])) || !($_SESSION['a1']==$_POST["a1"] && $_SESSION['a2']==$_POST["a2"])))
				{
				if ($_POST["a1"]=="suppImg") RencontreWidget::suppImg(strip_tags($_POST["a2"]),$mid);
				if ($_POST["a1"]=="plusImg") RencontreWidget::plusImg(strip_tags($_POST["a2"]),$mid);
				if ($_POST["a1"]=="suppImgAll") RencontreWidget::suppImgAll($mid);
				}
			if (isset($_GET["a1"]) && $_GET["a1"]=="sauvProfil") RencontreWidget::sauvProfil($in,$mid); 
			if (isset($_POST["a1"]) && $_POST["a1"]=="suppImg")
				{
				$_SESSION['a1'] = $_POST["a1"];
				$_SESSION['a2'] = $_POST["a2"];
				}
			$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_ville, R.i_photo, P.t_titre, P.t_annonce, P.t_profil FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$mid." and R.user_id=P.user_id and R.user_id=U.ID ");
			?>
			
			<h3><?php _e('Edit My Profile','rencontre');?></h3>
			<div class="rencPortrait">
				<form name='portraitPhoto' method='post' enctype="multipart/form-data" action=''>
					<input type='hidden' name='a1' value='' />
					<input type='hidden' name='a2' value='' />
					<input type='hidden' name='page' value='' />
					<div class="petiteBox portraitPhoto left">
						<div class="rencBox">
							<img id="portraitGrande" src="<?php if(($s->i_photo)!=0) echo $rencDiv['baseurl'].'/portrait/'.floor($mid/1000).'/'.Rencontre::f_img(($mid*10).'-grande').'.jpg?r='.rand(); else echo plugins_url('rencontre/images').'/no-photo600.jpg'; ?>" alt="" />
							<div class="rencBlocimg">
							<?php for($v=$mid*10;$v<=$s->i_photo;++$v) // cleaning
								{
								if(!file_exists($rencDiv['basedir'].'/portrait/'.floor($mid/1000).'/'.Rencontre::f_img(($v).'-mini').'.jpg')) RencontreWidget::suppImg($v,$mid);
								}
							for ($v=0;$v<$rencOpt['imnb'];++$v)
								{
								if ($s->i_photo>=$mid*10+$v)
									{
									echo '<a href="javascript:void(0)" onClick="f_supp_photo('.($mid*10+$v).')"><img onMouseOver="f_vignette_change('.($mid*10+$v).',\''.Rencontre::f_img(($mid*10+$v).'-grande').'\')" class="portraitMini" src="'.$rencDiv['baseurl'].'/portrait/'.floor($mid/1000).'/'.Rencontre::f_img(($mid*10+$v).'-mini').'.jpg?r='.rand().'" alt="'.__('Click to delete','rencontre').'" title="'.__('Click to delete','rencontre').'" /></a>'."\n";
									echo '<img style="display:none;" src="'.$rencDiv['baseurl'].'/portrait/'.floor($mid/1000).'/'.Rencontre::f_img(($mid*10+$v).'-grande').'.jpg?r='.rand().'" />'."\n";
									}
								else { ?>
								<?php 
								$ho = false; if(has_filter('rencNbPhotoP', 'f_rencNbPhotoP')) $ho = apply_filters('rencNbPhotoP', $ho);
								if($ho===false || $v<$ho) { ?><a href="javascript:void(0)" onClick="f_plus_photo(<?php echo $s->i_photo; ?>)"><img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php _e('Click to add a photo','rencontre'); ?>" title="<?php _e('Click to add a photo','rencontre'); ?>" /></a><?php }
								else echo '<img class="portraitMini" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.__('You are limited','rencontre').'" title="'.__('You are limited','rencontre').'" />';
								?>
								<?php }
								} ?>
							</div>
							<div id="changePhoto"></div>
							<div class="rencInfo"><?php _e('Click the photo','rencontre');?></div>
							<div><a href="javascript:void(0)" onClick="f_suppAll_photo()"><?php _e('Delete all photos','rencontre');?></a></div>
						</div>
					</div>
				</form>
				<form name='portraitChange' method='get' action=''>
					<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
					<input type='hidden' name='page' value='' />
					<input type='hidden' name='a1' value='' />
					<input type='hidden' name='a2' value='' />
					<div class="grandeBox right">
						<em id="infoChange"><?php if (isset($_GET["a1"]) && $_GET["a1"]=="sauvProfil") { echo __('Done','rencontre').'&nbsp;'; } ?></em>
						<div class="rencBox">
							<?php if($s->c_pays!="" && !isset($rencCustom['country'])) echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />'; ?>

							<div class="grid_10">
								<h3><?php echo $s->display_name; ?></h3>
								<div class="ville"><?php echo $s->c_ville; ?></div>
								<label><?php _e('My attention-catcher','rencontre');?></label><br />
								<input type="text" name="titre" value="<?php echo stripslashes($s->t_titre); ?>" /><br /><br />
								<label><?php _e('My ad','rencontre');?></label><br />
								<textarea name="annonce" rows="10" style="width:95%;"><?php echo stripslashes($s->t_annonce); ?></textarea>
							</div>
						</div>
					</div>
					<div id="portraitSauv"><span onClick="f_sauv_profil(<?php echo $mid; ?>)"><?php _e('Save profile','rencontre');?></span></div>
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
									case 1: $out2.='<tr><td>'.$r1[2].'</td><td><input type="text" name="text'.$r.'" value="'.(isset($out[$r])?$out[$r]:'').'" /></td></tr>'."\n"; break;
									case 2: $out2.='<tr><td>'.$r1[2].'</td><td><textarea name="area'.$r.'" rows="4" cols="50">'.(isset($out[$r])?$out[$r]:'').'</textarea></td></tr>'."\n"; break;
									case 3: $out2.='<tr><td>'.$r1[2].'</td><td><select name="select'.$r.'"><option value="0">&nbsp;</option>'; $list = json_decode($r1[3]); $c1=0;
										foreach ($list as $r2) { $out2.='<option value="'.($c1+1).'"'.((isset($out[$r]) && $c1===$out[$r])?' selected':'').'>'.$r2.'</option>'; ++$c1;}$out2.='</select></td></tr>'."\n"; break;
									case 4: $out2.='<tr><td>'.$r1[2].'</td><td>'; $list = json_decode($r1[3]); $c1=0; if (isset($out[$r])) $c3=" ".implode(" ",$out[$r])." "; else $c3="";
										foreach ($list as $r2) { $out2.=$r2.' : <input type="checkbox" name="check'.$r.'[]" value="'.$c1.'" '.((strstr($c3, " ".$c1." ")!=false)?'checked':'').' />'; ++$c1;}$out2.='</td></tr>'."\n"; break;
									}
								$d=$r1[1];
								}
							}
						$out2.='</table>'."\n";
						echo $out1.$out2;
						?>
						
						</div>
					</div>
				</form>
			</div>
			<?php }
		//
		// 4. Partie Mon Accueil
		else
			{
			if (strstr($_SESSION['rencontre'],'accueil'))
				{
				$s = $wpdb->get_row("SELECT U.ID, U.display_name, R.c_pays, R.c_ville, R.i_sex, R.d_naissance, R.i_zsex, i_zage_min, i_zage_max, R.i_zrelation, R.i_photo, P.t_action FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$mid." and R.user_id=P.user_id and R.user_id=U.ID");
				$action = json_decode($s->t_action,true);
				$action['sourireIn']=(isset($action['sourireIn'])?$action['sourireIn']:null);
				$action['visite']=(isset($action['visite'])?$action['visite']:null);
				$action['contactIn']=(isset($action['contactIn'])?$action['contactIn']:null);
				$zsex=$s->i_zsex;
				$homo=(($s->i_sex==$s->i_zsex)?1:0); // seulement si genre sans custom
				if($s->i_zage_min) $zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$s->i_zage_min)); else $zmin=0;
				if($s->i_zage_max) $zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$s->i_zage_max)); else $zmax=0;
				?>
				
				<div class="petiteBox right">
					<?php  if (strstr($_SESSION['rencontre'],'password')) { ?><div id="infoChange">
						<div class="rencBox"><em><?php _e('Password changed !','rencontre'); ?></em></div>
					</div><?php } ?>
					
					<div class="rencBox">
						<?php if($s->i_photo!=0) echo '<img src="'.$rencDiv['baseurl'].'/portrait/'.floor(($mid)/1000).'/'.Rencontre::f_img(($mid*10).'-mini').'.jpg" class="maPhoto" alt="'.$s->display_name.'"/>';
						else echo '<img class="maPhoto" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />';
						echo ($current_user->user_login);
						if($s->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place'])) echo '<img class="monFlag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />';
						if(!isset($rencCustom['born'])) echo '<div class="monAge">'.__('Age','rencontre').'&nbsp;:&nbsp;'.Rencontre::f_age($s->d_naissance).'&nbsp;'.__('years','rencontre').'</div>';
						if(!isset($rencCustom['place'])) echo '<div class="maVille">'.__('City','rencontre').'&nbsp;:&nbsp;'.$s->c_ville.'</div>'; ?>
						<div id="tauxProfil"></div>
						<div class="maRecherche  firstMaj">
						<?php if(isset($rencOpt['iam'][$s->i_zsex])) echo __('I\'m looking for','rencontre').'&nbsp;<em>'.$rencOpt['iam'][$s->i_zsex].'</em>';
						if(isset($rencOpt['for'][$s->i_zrelation])) echo '&nbsp;'.__('for','rencontre').'&nbsp;<em>'.$rencOpt['for'][$s->i_zrelation].'</em>'; ?>
						</div>
						<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='change';document.forms['rencMenu'].elements['id'].value='<?php echo $mid; ?>';document.forms['rencMenu'].submit();"><?php _e('Edit My Profile','rencontre');?></a></div>
						<?php if(!isset($rencCustom['smile'])) { ?><div class="mesSourire"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='sourireIn';document.forms['rencMenu'].submit();"><?php _e('Smile','rencontre'); echo '&nbsp;:&nbsp;'.((count($action['sourireIn'])>49)?'>50':count($action['sourireIn'])); ?></a></div><?php } ?>
						<div class="mesSourire"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='visite';document.forms['rencMenu'].submit();"><?php _e('Look','rencontre'); echo '&nbsp;:&nbsp;'.((count($action['visite'])>49)?'>50':count($action['visite'])); ?></a></div>
						<div class="mesSourire"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='contactIn';document.forms['rencMenu'].submit();"><?php _e('Contact requests','rencontre'); echo '&nbsp;:&nbsp;'.((count($action['contactIn'])>49)?'>50':count($action['contactIn'])); ?></a></div>
					</div>
					<div class="rencBox">
						<?php if(!isset($rencCustom['smile'])) { ?><div class="rencItem"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='sourireOut';document.forms['rencMenu'].submit();"><?php _e('Who I smiled ?','rencontre');?></a></div><?php } ?>
						<div class="rencItem"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='contactOut';document.forms['rencMenu'].submit();"><?php _e('Who I asked for a contact ?','rencontre');?></a></div>
						<div class="rencItem"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='cherche';document.forms['rencMenu'].elements['id'].value='bloque';document.forms['rencMenu'].submit();"><?php _e('Who I\'ve blocked ?','rencontre');?></a></div>
					</div>
					<div class="rencBox">
						<h3><?php _e('Quick Search','rencontre');?></h3>
						<form name='formMonAccueil' method='get' action=''>
						<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
							<input type='hidden' name='page' value='' />
							<input type='hidden' name='zsex' value='<?php echo $zsex ?>' />
							<input type='hidden' name='homo' value='<?php echo $homo; ?>' />
							<input type='hidden' name='pagine' value='0' />
							<?php if(!isset($rencCustom['born'])) { ?>
							<div class="rencItem"><?php _e('Age','rencontre');?>&nbsp;<span><?php _e('from','rencontre');?>&nbsp;
								<select name="ageMin" onChange="f_min(this.options[this.selectedIndex].value,'formMonAccueil','ageMin','ageMax');">
									<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
									
								</select>
								</span>
								<span>&nbsp;<?php _e('to','rencontre');?>&nbsp;
								<select name="ageMax" onChange="f_max(this.options[this.selectedIndex].value,'formMonAccueil','ageMin','ageMax');">
									<?php for ($v=18;$v<98;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
									
									<option value="99" selected>99&nbsp;<?php _e('years','rencontre');?></option>
								</select>
								</span>
							</div>
							<?php } if(!isset($rencCustom['country'])) { ?>
							<div class="rencItem"><?php _e('Country','rencontre');?>&nbsp;:
								<select name="pays" onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect1');">
									<?php RencontreWidget::f_pays($rencOpt['pays']); ?>
									
								</select>
							</div>
							<?php } 
							if(!isset($rencCustom['place']) && !isset($rencCustom['region'])) {
							?>
							<div class="rencItem"><?php _e('Region','rencontre');?>&nbsp;:
								<select id="regionSelect1" name="region">
									<?php RencontreWidget::f_regionBDD(1,$rencOpt['pays']); ?>
									
								</select>
							</div>
							<?php }
							if(isset($rencCustom['relationQ']) && isset($rencOpt['for']))
								{
								echo '<div class="rencItem">'.__('Relation','rencontre').'&nbsp;:<select name="relation">';
								echo '<option value="">-</option>';
								for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) echo '<option value="'.$v.'">'.$rencOpt['for'][$v].'</option>';
								echo '</select></div>';
								}
							if(isset($rencCustom['profilQS1']))
								{
								$q = $wpdb->get_row("SELECT c_label, t_valeur FROM ".$wpdb->prefix."rencontre_profil WHERE id=".$rencCustom['profilQS1']." AND c_lang='".substr($rencDiv['lang'],0,2)."'");
								if($q)
									{
									echo '<div class="rencItem">'.$q->c_label.'&nbsp;:<select id="profilQS1" name="profilQS1">';
									$s = json_decode($q->t_valeur); $c=0;
									echo '<option value="">-</option>';
									foreach($s as $ss)
										{
										echo '<option value="'.$c.'">'.$ss.'</option>';
										++$c;
										}
									echo '</select></div>';
									}
								}
							if(isset($rencCustom['profilQS2']))
								{
								$q = $wpdb->get_row("SELECT c_label, t_valeur FROM ".$wpdb->prefix."rencontre_profil WHERE id=".$rencCustom['profilQS2']." AND c_lang='".substr($rencDiv['lang'],0,2)."'");
								if($q)
									{
									echo '<div class="rencItem">'.$q->c_label.'&nbsp;:<select id="profilQS2" name="profilQS2">';
									$s = json_decode($q->t_valeur); $c=0;
									echo '<option value="">-</option>';
									foreach($s as $ss)
										{
										echo '<option value="'.$c.'">'.$ss.'</option>';
										++$c;
										}
									echo '</select></div>';
									}
								} ?>
							<div class="button"><a href="javascript:void(0)" onClick="document.forms['formMonAccueil'].elements['page'].value='cherche';document.forms['formMonAccueil'].submit();"><?php _e('Find','rencontre');?></a></div>
							<div class="clear"></div>
						</form>
					</div>
				</div>
			<?php }
			//
			// 5. Partie mini portrait
			if (strstr($_SESSION['rencontre'],'mini')) // mini toujours avec accueil
				{
				if (!isset($zsex)) // bloc actuellement inutile (voir plus haut)
					{
					$q = $wpdb->get_row("SELECT i_sex, i_zsex, i_zage_min, i_zage_max FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$mid."'");
					$zsex=$q->i_zsex;
					$homo=(($s->i_sex==$s->i_zsex)?1:0);
					if($q->i_zage_min) $zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$q->i_zage_min)); else $zmin=0;
					if($q->i_zage_max) $zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$q->i_zage_max)); else $zmax=0;
					}
				?>
				
				<div class="grandeBox left">
				<?php $q = $wpdb->get_results("SELECT DISTINCT(R.user_id) 
					FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						R.user_id=U.ID 
						AND U.user_status=0 
						AND R.user_id=P.user_id 
						AND R.i_sex=".$zsex." 
						".(!isset($rencCustom['sex'])?"AND R.i_zsex".(($homo)?'='.$zsex:'!='.$zsex):"")." 
						".((!isset($rencCustom['born']) && $zmax && $zmin)?"AND R.d_naissance>'".$zmax."' AND R.d_naissance<'".$zmin."'":" ")."
						".(($rencOpt['onlyphoto'])?" AND R.i_photo>0 ":" ")."
						AND CHAR_LENGTH(P.t_titre)>4 
						AND CHAR_LENGTH(P.t_annonce)>30 
						AND R.user_id!=".$mid." 
					ORDER BY RAND() LIMIT 8"); ?>
				
					<div class="rencBox">
						<h3><?php _e('Selected portraits','rencontre');?></h3>
							<?php foreach ($q as $r)
								{
								RencontreWidget::f_miniPortrait($r->user_id);
								} 
							?>
								
						<div class="clear"></div>
					</div><!-- .rencBox -->
				<?php if ($rencOpt['anniv']==1 && !isset($rencCustom['born']))
					{
					$q = $wpdb->get_results("SELECT user_id 
						FROM ".$wpdb->prefix."rencontre_users 
						WHERE 
							d_naissance LIKE '%".date('m-d')."' 
							AND i_sex=".$zsex." 
							".(!isset($rencCustom['sex'])?"AND i_zsex".(($homo)?'='.$zsex:'!='.$zsex):"")." 
							".((!isset($rencCustom['born']) && $zmax && $zmin)?"AND d_naissance>'".$zmax."' AND d_naissance<'".$zmin."'":"")."
							AND user_id!=".$mid." 
						ORDER BY RAND() LIMIT 4"); ?>
				
					<div class="rencBox">
						<h3><?php _e('Today\'s birthday','rencontre');?></h3>
							<?php foreach ($q as $r)
								{
								RencontreWidget::f_miniPortrait($r->user_id);
								} 
							?>
								
						<div class="clear"></div>
					</div><!-- .rencBox -->
				<?php } ?>
				
				<?php $ho = false; if(has_filter('rencAddBox', 'f_rencAddBox')) $ho = apply_filters('rencAddBox', $mid); if($ho) echo $ho; ?>
				
				<?php if ($rencOpt['ligne']==1)
					{
					$tab=''; $d=$rencDiv['basedir'].'/session/';
					if ($dh=opendir($d)){while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..' && (filemtime($d.$file)>time()-180)) $tab.="'".basename($file, ".txt")."',"; }closedir($dh);}
					$q = $wpdb->get_results("SELECT user_id 
						FROM ".$wpdb->prefix."rencontre_users 
						WHERE 
							user_id IN (".substr($tab,0,-1).") 
							AND i_sex=".$zsex." 
							".(!isset($rencCustom['sex'])?"AND i_zsex".(($homo)?'='.$zsex:'!='.$zsex):"")." 
							AND user_id!=".$mid." 
						ORDER BY RAND() LIMIT 16"); // AND d_naissance>'".$zmax."' AND d_naissance<'".$zmin."' ?>
					<div class="rencBox">
						<form name='rencLine' method='get' action=''>
							<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
							<input type='hidden' name='page' value='cherche' />
							<input type='hidden' name='obj' value='enligne' />
							<input type='hidden' name='sex' value='<?php echo $zsex; ?>' />
							<input type='hidden' name='homo' value='<?php echo $homo; ?>' />
						</form>
						<h3>
							<?php if(isset($rencOpt['home'])) echo '<a href="javascript:void(0)" onClick="document.forms[\'rencLine\'].submit();">'. __('Online now','rencontre').'</a>';
							else echo __('Online now','rencontre'); ?>
						</h3>
							<?php foreach ($q as $r)
								{
								RencontreWidget::f_miniPortrait($r->user_id);
								} 
							?>
								
						<div class="clear"></div>
					</div><!-- .rencBox -->
				<?php } ?>
				<?php $q = $wpdb->get_results("SELECT R.user_id 
					FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						R.user_id=U.ID 
						AND U.user_status=0 
						AND R.user_id=P.user_id 
						".(!isset($rencCustom['sex'])?"AND R.i_zsex".(($homo)?'='.$zsex:'!='.$zsex):"")." 
						AND R.i_sex=".$zsex.(($rencOpt['onlyphoto'])?" 
						AND R.i_photo>0 ":" ")."
						AND CHAR_LENGTH(P.t_titre)>4 
						AND CHAR_LENGTH(P.t_annonce)>30 
						AND R.user_id!=".$mid." 
					ORDER BY U.ID DESC LIMIT 12"); ?>
				
					<div class="rencBox">
						<h3><?php _e('New entrants','rencontre');?></h3>
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
			if (strstr($_SESSION['rencontre'],'cherche')) // cherche toujours avec accueil
				{
				$q = false;
				$pagine = (isset($_GET['pagine'])?$_GET['pagine']:0);
				$suiv = 1;
				?> 
				<form name='rencPagine' method='get' action=''>
					<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
					<input type='hidden' name='page' value='cherche' />
					<input type='hidden' name='id' value='<?php echo (isset($_GET['id'])?$_GET['id']:''); ?>' />
					<input type='hidden' name='zsex' value='<?php echo (isset($_GET['zsex'])?$_GET['zsex']:''); ?>' />
					<input type='hidden' name='homo' value='<?php echo (isset($_GET['homo'])?$_GET['homo']:''); ?>' />
					<input type='hidden' name='pagine' value='<?php echo $pagine; ?>' />
					<input type='hidden' name='ageMin' value='<?php echo (isset($_GET['ageMin'])?$_GET['ageMin']:''); ?>' />
					<input type='hidden' name='ageMax' value='<?php echo (isset($_GET['ageMax'])?$_GET['ageMax']:''); ?>' />
					<input type='hidden' name='pays' value='<?php echo (isset($_GET['pays'])?$_GET['pays']:''); ?>' />
					<input type='hidden' name='region' value='<?php echo (isset($_GET['region'])?$_GET['region']:''); ?>' />
					<input type='hidden' name='obj' value='<?php echo (isset($_GET['obj'])?$_GET['obj']:''); ?>' />
					<input type='hidden' name='profilQS1' value='<?php echo (isset($_GET['profilQS1'])?$_GET['profilQS1']:''); ?>' />
					<input type='hidden' name='profilQS2' value='<?php echo (isset($_GET['profilQS2'])?$_GET['profilQS2']:''); ?>' />
					<input type='hidden' name='relation' value='<?php echo (isset($_GET['relation'])?$_GET['relation']:''); ?>' />
				</form>
				<div class="grandeBox left">
				<?php if (isset($_GET['zsex']) && $_GET['zsex']!='' && (!isset($_GET['obj']) || $_GET['obj']==''))
					{
					$s="SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, R.d_session, P.t_annonce, P.t_action
						FROM ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."users U 
						WHERE P.user_id=R.user_id 
							and U.ID=R.user_id 
							and U.user_status=0 
							and R.user_id!=".$mid;
					if(isset($_GET['region']) && $_GET['region']) $s.=" and R.c_region LIKE '".addslashes($wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_GET['region'])."'"))."'";
					if(isset($_GET['pays']) && $_GET['pays']) $s.=" and R.c_pays='".$_GET['pays']."'";
					$s.=" and R.i_sex='".strip_tags($_GET['zsex'])."'";
					if(!isset($rencCustom['sex'])) $s.=" and R.i_zsex".((strip_tags($_GET['homo']))?'=':'!=').strip_tags($_GET['zsex']);
					if(!isset($rencCustom['born']))
						{
						if(isset($_GET['ageMin']) && $_GET['ageMin']>18) // 18 & 99 => no limit
							{
							$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['ageMin']));
							$s.=" and R.d_naissance<'".$zmin."'";
							}
						if(isset($_GET['ageMax']) && $_GET['ageMax'] && $_GET['ageMax']<99)
							{
							$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['ageMax']));
							$s.=" and R.d_naissance>'".$zmax."'";
							}
						}
					if(isset($_GET['relation']) && isset($rencCustom['relationQ']) && $_GET['relation']!='') $s.=" and R.i_zrelation='".strip_tags($_GET['relation'])."'";
					if(isset($_GET['profilQS1']) && isset($rencCustom['profilQS1']) && $_GET['profilQS1']!='') $s.=" and P.t_profil LIKE '%{\"i\":".$rencCustom['profilQS1'].",\"v\":".$_GET['profilQS1']."}%'";
					if(isset($_GET['profilQS2']) && isset($rencCustom['profilQS2']) && $_GET['profilQS2']!='') $s.=" and P.t_profil LIKE '%{\"i\":".$rencCustom['profilQS2'].",\"v\":".$_GET['profilQS2']."}%'";
					if($rencOpt['onlyphoto']) $s.=" and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30 and R.i_photo>0";
					$s.=" ORDER BY R.d_session DESC, P.d_modif DESC LIMIT ".($pagine*$rencOpt['limit']).", ".($rencOpt['limit']+1); // LIMIT indice du premier, nombre de resultat
					$q = $wpdb->get_results($s);
					if($wpdb->num_rows<=$rencOpt['limit']) $suiv=0;
					else array_pop($q); // supp le dernier ($rencOpt['limit']+1) qui sert a savoir si page suivante
					}
				else if (isset($_GET['id']) && $_GET['id']=='sourireOut')
					{
					echo '<h3 style="text-align:center;">'.__('I smiled at','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
					$action= json_decode($q,true);
					$action['sourireOut']=(isset($action['sourireOut'])?$action['sourireOut']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if ($action['sourireOut'])
						{
						krsort($action['sourireOut']);
						foreach ($action['sourireOut'] as $r)
							{
							if($c<=$rencOpt['limit'])
								{
								$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id and P.user_id=U.ID and U.user_status=0");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*$rencOpt['limit'])
									{
									if($c<$rencOpt['limit']) $q[$c]->date=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if (isset($_GET['id']) && $_GET['id']=='sourireIn')
					{
					echo '<h3 style="text-align:center;">'.__('I got a smile from','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
					$action= json_decode($q,true);
					$action['sourireIn']=(isset($action['sourireIn'])?$action['sourireIn']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if ($action['sourireIn'])
						{
						krsort($action['sourireIn']);
						foreach ($action['sourireIn'] as $r)
							{
							if($c<=$rencOpt['limit'])
								{
								$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id and P.user_id=U.ID and U.user_status=0");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*$rencOpt['limit'])
									{
									if($c<$rencOpt['limit']) $q[$c]->date=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if (isset($_GET['id']) && $_GET['id']=='contactOut')
					{
					echo '<h3 style="text-align:center;">'.__('I asked a contact','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
					$action= json_decode($q,true);
					$action['contactOut']=(isset($action['contactOut'])?$action['contactOut']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if ($action['contactOut'])
						{
						krsort($action['contactOut']);
						foreach ($action['contactOut'] as $r)
							{
							if($c<=$rencOpt['limit'])
								{
								$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id and P.user_id=U.ID and U.user_status=0");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*$rencOpt['limit'])
									{
									if($c<$rencOpt['limit']) $q[$c]->date=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if (isset($_GET['id']) && $_GET['id']=='contactIn')
					{
					echo '<h3 style="text-align:center;">'.__('I had a contact request from','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
					$action= json_decode($q,true);
					$action['contactIn']=(isset($action['contactIn'])?$action['contactIn']:null);
					$q = '';$c = 0; $n = 0; $suiv = 0;
					if ($action['contactIn'])
						{
						krsort($action['contactIn']);
						foreach ($action['contactIn'] as $r)
							{
							if($c<=$rencOpt['limit'])
								{
								$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id and P.user_id=U.ID and U.user_status=0");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*$rencOpt['limit'])
									{
									if($c<$rencOpt['limit']) $q[$c]->date=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if (isset($_GET['id']) && $_GET['id']=='visite')
					{
					echo '<h3 style="text-align:center;">'.__('I was watched by','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
					$action= json_decode($q,true);
					$action['visite']=(isset($action['visite'])?$action['visite']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if ($action['visite'])
						{
						krsort($action['visite']);
						foreach ($action['visite'] as $r)
							{
							if($c<=$rencOpt['limit'])
								{
								$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action,U.user_status FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id and P.user_id=U.ID and U.user_status=0");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*$rencOpt['limit'])
									{
									if($c<$rencOpt['limit']) $q[$c]->date=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if (isset($_GET['id']) && $_GET['id']=='bloque')
					{
					echo '<h3 style="text-align:center;">'.__('I locked','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."'");
					$action= json_decode($q,true);
					$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if ($action['bloque'])
						{
						krsort($action['bloque']);
						foreach ($action['bloque'] as $r)
							{
							if($c<=$rencOpt['limit'])
								{
								$q[$c]=$wpdb->get_row("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U WHERE R.user_id='".$r['i']."' and P.user_id=R.user_id and P.user_id=U.ID and U.user_status=0");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*$rencOpt['limit'])
									{
									if($c<$rencOpt['limit']) $q[$c]->date=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if (isset($_GET['obj']) && $_GET['obj']=='enligne')
					{
					echo '<h3 style="text-align:center;">'.__('Online now','rencontre').'</h3>';
					$tab=''; $d=$rencDiv['basedir'].'/session/';
					if ($dh=opendir($d)){while (($file = readdir($dh))!==false) { if ($file!='.' && $file!='..' && (filemtime($d.$file)>time()-180)) $tab.="'".basename($file, ".txt")."',"; }closedir($dh);}
					$q = $wpdb->get_results("SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action 
						FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."users U 
						WHERE 
							R.user_id IN (".substr($tab,0,-1).") 
							AND R.i_sex='".strip_tags($_GET['sex'])."' 
							".(!isset($rencCustom['sex'])?"AND R.i_zsex".((strip_tags($_GET['homo']))?'=':'!=')."'".strip_tags($_GET['sex'])."'":"")." 
							AND R.user_id!=".$mid." 
							AND P.user_id=R.user_id 
							AND P.user_id=U.ID 
							AND U.user_status=0 
						LIMIT ".($pagine*$rencOpt['limit']).", ".($rencOpt['limit']+1)); // LIMIT indice du premier, nombre de resultat
					if($wpdb->num_rows<=$rencOpt['limit']) $suiv=0;
					else array_pop($q); // supp le dernier ($rencOpt['limit']+1) qui sert a savoir si page suivante
					}
				if($q) foreach($q as $r)
					{
					$bl1=RencontreWidget::f_etat_bloque1($r->user_id,$r->t_action); // je suis bloque ?
					?>
					<div class="rencBox">
					<?php if (isset($r->date)) echo '<div class="rencDate">'.__('The','rencontre').'&nbsp;'.substr($r->date,8,2).'.'.substr($r->date,5,2).'.'.substr($r->date,0,4).'</div>'; ?>
					<?php if (isset($r->d_session)) echo '<div class="rencDate" style="text-transform:capitalize">'.__('online','rencontre').'&nbsp;:&nbsp;'.substr($r->d_session,8,2).'.'.substr($r->d_session,5,2).'.'.substr($r->d_session,0,4).'</div>'; ?>
						<?php RencontreWidget::f_miniPortrait($r->user_id); ?>
						<div class="maxiBox right rel">
								<?php echo stripslashes($r->t_annonce); ?>
							<div style="height:45px;"></div>
							<div class="abso225  firstMaj">
								<?php if(isset($rencOpt['iam'][$r->i_zsex])) echo __('I\'m looking for','rencontre').'&nbsp;<span>'.$rencOpt['iam'][$r->i_zsex].'</span><br />';
								if(!isset($rencCustom['born']) && $r->i_zage_min) echo __('between','rencontre').'&nbsp;<span>'.$r->i_zage_min.'</span>&nbsp;'.__('and','rencontre').'&nbsp;<span>'.$r->i_zage_max.'</span>&nbsp;'.__('years','rencontre').'<br />';
								if(isset($rencOpt['for'][$r->i_zrelation])) echo __('for','rencontre').'&nbsp;<span>'.$rencOpt['for'][$r->i_zrelation].'</span>'; ?>
							</div>
							<div class="abso135">
								<?php if (!$bl1)
								{ ?>
								<?php
								$ho = false; if(has_filter('rencSendP', 'f_rencSendP')) $ho = apply_filters('rencSendP', $ho);
								if(!$ho && !$rencBlock){ ?><div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='ecrire';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Send a message','rencontre');?></a></div><?php }
								else echo '<div class="button right rencLiOff">'.__('Send a message','rencontre').'</div>';
								?>
								<?php if(!isset($rencCustom['smile']))
									{
									$ho = false; if(has_filter('rencSmileP', 'f_rencSmileP')) $ho = apply_filters('rencSmileP', $ho);
									if(!$ho && !$rencBlock){ ?><div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='sourire';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Smile','rencontre');?></a></div><?php }
									else echo '<div class="button right rencLiOff">'.__('Smile','rencontre').'</div>';
									}
								?>
								<?php 
								}
								else echo '<div class="button right rencLiOff">'.__('Send a message','rencontre').'</div><div class="button right rencLiOff">'.__('Smile','rencontre').'</div>'; ?>
								<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Profile','rencontre');?></a></div>
							</div>
						</div><!-- .grandeBox .right -->
						<div class="clear"></div>
					</div>
					<?php }
					if($pagine||$suiv)
						{
						echo '<div class="rencPagine">';
						if(($pagine+0)>0) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."-1;document.forms['rencPagine'].submit();\">".__('Previous page','rencontre')."</a>";
						for($v=max(0, $pagine-4); $v<$pagine; ++$v)
							{
							echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value='".$v."';document.forms['rencPagine'].submit();\">".$v."</a>";
							}
						echo "<span>".$pagine."</span>";
						if($suiv) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."+1;document.forms['rencPagine'].submit();\">".__('Next Page','rencontre')."</a>";
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
					<?php
					$ho = false; if(has_filter('rencSearchP', 'f_rencSearchP')) $ho = apply_filters('rencSearchP', $ho);
					if(!$ho) RencontreWidget::f_cherchePlus($mid); ?>
					
					</div><!-- #rencTrouve -->
				</div><!-- .grandeBox .left -->
			<?php }
			//
			// 8. Messagerie
			if (strstr($_SESSION['rencontre'],'msg') && !$rencBlock)
				{ 
				if (isset($_GET["sujet"]) && $_GET["sujet"]!="")
					{
					echo '<em id="rencSend" style="color:red">';
					$ho = false; if(has_filter('rencAnswerP', 'f_rencAnswerP')) $ho = apply_filters('rencAnswerP', $ho);
					if (!$ho && $current_user->user_status<2)
						{
						echo __('Message sent','rencontre')."&nbsp";
						RencontreWidget::f_envoiMsg($current_user->user_login);
						}
					else if($current_user->user_status>1) echo __('Not sent','rencontre').'.&nbsp;'.__('You are no longer allowed to send messages.','rencontre'); 
					else _e('Not sent','rencontre');
					echo '</em><script>window.setTimeout(\'document.getElementById("rencSend").innerHTML=""\',3000);</script>';
					}
				?>
				
				<div class="grandeBox left">
					<div class="rencBox">
						<form name="formEcrire" method='get' action=''>
						<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
						<input type='hidden' name='page' value='' />
						<input type='hidden' name='id' value='' />
						<input type='hidden' name='msg' value='' />
						<div id="rencMsg" class="rencMsg">
						<?php $hoAns = false; if(has_filter('rencAnswerP', 'f_rencAnswerP')) $hoAns = apply_filters('rencAnswerP', $hoAns); ?>
						<?php RencontreWidget::f_boiteReception($current_user->user_login,$hoAns); ?>
						</div>
						</form>
					</div><!-- .rencBox -->
				</div><!-- .grandeBox .left -->

			<?php }
			//
			// 9. Envoi message
			if (strstr($_SESSION['rencontre'],'ecrire') && !$rencBlock)
				{ 
				$q = $wpdb->get_row("SELECT U.user_login, R.i_photo FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE U.ID='".strip_tags($_GET["id"])."' and R.user_id=U.ID");
				?>
				
				<div class="grandeBox left">
					<div class="rencBox">
						<h3><?php _e('Send a message to','rencontre'); ?>
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $_GET["id"]; ?>';document.forms['rencMenu'].submit();">
							<?php echo '&nbsp;'.$q->user_login; ?>
							</a>
						</h3>
						<div id="rencMsg" class="rencMsg">
						<form name="formEcrire" method='get' action=''>
							<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
							<input type='hidden' name='page' value='' />
							<input type='hidden' name='id' value='' />
							<input type='hidden' name='msg' value='' />
							<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $_GET["id"]; ?>';document.forms['rencMenu'].submit();">
							<?php if($q->i_photo!=0) echo '<img class="tete" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($q->i_photo)/10000).'/'.Rencontre::f_img((floor(($q->i_photo)/10)*10).'-mini').'.jpg" alt="" />';
							else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />'; ?>
							</a>
							<?php if(isset($_GET['msg']) && $_GET['msg'])
								{
								$q = $wpdb->get_row("SELECT M.subject, M.content FROM ".$wpdb->prefix."rencontre_msg M, ".$wpdb->prefix."users U WHERE M.id='".strip_tags($_GET["msg"])."' and M.recipient=U.user_login and U.ID='".$mid."' ");
								if($q) echo '<label>'. __('Subject','rencontre').'&nbsp;:</label><input name="sujet" type="text" value="Re: '.stripslashes($q->subject).'" /><br />';
								else echo '<label>'. __('Subject','rencontre').'&nbsp;:</label><input name="sujet" type="text" /><br />';
								}
							else echo '<label>'. __('Subject','rencontre').'&nbsp;:</label><input name="sujet" type="text" /><br />';
								?>
							<label><?php _e('Message','rencontre');?>&nbsp;:</label><textarea name="contenu" rows="8"></textarea><br />
							<?php if(isset($_GET['msg']) && $_GET['msg'] && $q)
								{
								echo '<table style="margin:5px 10px;text-align:left;"><tr style="border:none;"><td style="font-weight:700;width:100px;border:none;">'.__('Subject','rencontre').'&nbsp;:&nbsp;</td><td>'.stripslashes($q->subject).'</td></tr>';
								echo '<tr><td style="font-weight:700;border:none;">'.__('Message','rencontre').'&nbsp;:&nbsp;</td><td style=";border:none;">'.stripslashes($q->content).'</td></tr></table>';
								} ?>
							<div class="button"><a href="javascript:void(0)" onClick="document.forms['formEcrire'].elements['page'].value='msg';document.forms['formEcrire'].elements['id'].value='<?php echo $_GET["id"]; ?>'<?php if(isset($_GET["msg"])) echo ';document.forms[\'formEcrire\'].elements[\'msg\'].value=\''.strip_tags($_GET["msg"]).'\''; ?>;document.forms['formEcrire'].submit();"><?php _e('Send','rencontre');?></a></div>
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
			<?php if(!$fantome && !isset($_COOKIE["rencfantome"]) && !current_user_can("administrator")) { ?>
				<div id="rencFantome">
					<div class="rencFantome">
					<?php if(!isset($rencCustom['empty']) || !isset($rencCustom['emptyText']) || $rencCustom['emptyText']=='') _e('Your profile is empty. To take advantage of the site and being more visible, thank you to complete it.','rencontre');
					else echo stripslashes($rencCustom['emptyText']); ?>
					<span onClick="f_fantome();"><?php _e('Close','rencontre');?></span>
					</div>
				</div><?php } ?>
			<?php
			} ?>
			<div style="clear:both;">&nbsp;</div>
		</div><!-- #widgRenc -->
		<?php
		}
	// *************** FUNCTION ********************
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
	static function suppImg($im,$id)
		{
		// entree : nom de la photo (id * 10 + 1 ou 2 ou 3...)
		global $rencDiv;
		$r = $rencDiv['basedir'].'/portrait/'.floor($im/10000).'/';
		if (file_exists($r.Rencontre::f_img($im).'.jpg')) unlink($r.Rencontre::f_img($im).'.jpg');
		if (file_exists($r.Rencontre::f_img($im.'-mini').'.jpg')) unlink($r.Rencontre::f_img($im.'-mini').'.jpg');
		if (file_exists($r.Rencontre::f_img($im.'-grande').'.jpg')) unlink($r.Rencontre::f_img($im.'-grande').'.jpg');
		if (file_exists($r.Rencontre::f_img($im.'-libre').'.jpg')) unlink($r.Rencontre::f_img($im.'-libre').'.jpg');
		global $wpdb;
		$q = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$id."'");
		if (floor($q/10)*10==$q) $p=0; // plus de photo
		else $p=$q-1;
		$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>$p), array('user_id'=>$id));
		$c=0;
		for ($v=$im; $v<$q; ++$v)
			{
			rename($r.Rencontre::f_img(($v+1)).'.jpg', $r.Rencontre::f_img($v).'.jpg');
			rename($r.Rencontre::f_img(($v+1).'-mini').'.jpg', $r.Rencontre::f_img($v.'-mini').'.jpg');
			rename($r.Rencontre::f_img(($v+1).'-grande').'.jpg', $r.Rencontre::f_img($v.'-grande').'.jpg');
			rename($r.Rencontre::f_img(($v+1).'-libre').'.jpg', $r.Rencontre::f_img($v.'-libre').'.jpg');
			}
		}
	//
	static function plusImg($nim,$id)
		{
		// entree : $s->i_photo (id * 10 + nombre de photo)
		global $rencDiv;
		if ($nim==0) $p=$id*10; // premiere photo
		else $p=$nim+1;
		$r = $rencDiv['basedir'].'/tmp/';
		if(!is_dir($r)) mkdir($r);
		$cible = $r . basename($_FILES['plusPhoto']['tmp_name']);
		if (move_uploaded_file($_FILES['plusPhoto']['tmp_name'], $cible)) 
			{
			RencontreWidget::f_photo($p,$cible);
			global $wpdb;
			$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>$p), array('user_id'=>$id));
			if (file_exists($cible)) unlink($cible);
			}
		else echo "rate";
		}
	//
	static function suppImgAll($id)
		{
		// entree : nom de la photo (id * 10 + 1 ou 2 ou 3...)
		global $rencDiv;
		$r = $rencDiv['basedir'].'/portrait/'.floor($id/1000).'/';
		for($v=0;$v<6;++$v)
			{
			if (file_exists($r.Rencontre::f_img($id.$v).'.jpg')) unlink($r.Rencontre::f_img($id.$v).'.jpg');
			if (file_exists($r.Rencontre::f_img($id.$v.'-mini').'.jpg')) unlink($r.Rencontre::f_img($id.$v.'-mini').'.jpg');
			if (file_exists($r.Rencontre::f_img($id.$v.'-grande').'.jpg')) unlink($r.Rencontre::f_img($id.$v.'-grande').'.jpg');
			if (file_exists($r.Rencontre::f_img($id.$v.'-libre').'.jpg')) unlink($r.Rencontre::f_img($id.$v.'-libre').'.jpg');
			}
		global $wpdb;
		$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>0), array('user_id'=>$id));
		}
	//
	static function sauvProfil($in,$id)
		{
		// entree : Sauvegarde du profil
		// sortie bdd : [{"i":10,"v":"Sur une ile deserte avec mon amoureux."},{"i":35,"v":0},{"i":53,"v":[0,4,6]}]
		$u = "";
		if($in) foreach ($in as $r=>$r1) 
			{
			switch ($r1[0])
				{
				case 1: if ($_GET['text'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_GET['text'.$r]))).'"},'; break;
				case 2: if ($_GET['area'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_GET['area'.$r]))).'"},'; break;
				case 3: if ($_GET['select'.$r]>0) $u.='{"i":'.$r.',"v":'.(strip_tags($_GET['select'.$r]-1)).'},'; break;
				case 4: if (!empty($_GET['check'.$r])) {$u.='{"i":'.$r.',"v":['; foreach ($_GET['check'.$r] as $r2) { $u.=$r2.',';} $u=substr($u, 0, -1).']},';} break;
				}
			}
		global $wpdb;
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('d_modif'=>date("Y-m-d H:i:s"),'t_titre'=>strip_tags(stripslashes($_GET['titre'])),'t_annonce'=>strip_tags(stripslashes($_GET['annonce'])),'t_profil'=>'['.substr($u, 0, -1).']'), array('user_id'=>$id));
		}
	//
	static function f_photo($im,$rim)
		{
		// im : user_id *10 + numero de photo a partir de 0
		global $rencOpt; global $rencDiv;
		$r = $rencDiv['basedir'].'/portrait/'.floor($im/10000);
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
			imagejpeg(RencontreWidget::f_imcopyright($out1,$rencOpt['imcopyright'],$rencOpt['txtcopyright']), $r."/".Rencontre::f_img($im).".jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out2,$rencOpt['imcopyright'],$rencOpt['txtcopyright']), $r."/".Rencontre::f_img($im."-mini").".jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out3,$rencOpt['imcopyright'],$rencOpt['txtcopyright']), $r."/".Rencontre::f_img($im."-grande").".jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out4,$rencOpt['imcopyright'],$rencOpt['txtcopyright']), $r."/".Rencontre::f_img($im."-libre").".jpg", 75);
			imagedestroy($in); imagedestroy($out1); imagedestroy($out2); imagedestroy($out3); imagedestroy($out4);
			}
		}
	//
	static function f_imcopyright($imc,$right,$txtc)
		{
		if ($right)
			{
			$sx = imagesx($imc);
			$sy = imagesy($imc);
			if($txtc=="") $Text=site_url();
			else $Text=$txtc;
			if(current_user_can("administrator")) $Font="../wp-content/plugins/rencontre/inc/arial.ttf";
			else $Font="wp-content/plugins/rencontre/inc/arial.ttf";
			$FontColor = imagecolorallocate($imc,255,255,255);
			$FontShadow = imagecolorallocate($imc,0,0,0);
			if($right=="2") $Rotation = -30;
			else $Rotation = 30;
			/* Make a copy image */
			$OriginalImage = imagecreatetruecolor($sx,$sy);
			imagecopy($OriginalImage,$imc,0,0,0,0,$sx,$sy);
			/* Iterate to get the size up */
			$FontSize=1;
			do
				{
				$FontSize *= 1.1;
				$Box = @imagettfbbox($FontSize,0,$Font,$Text);
				$TextWidth = abs($Box[4] - $Box[0]);
				$TextHeight = abs($Box[5] - $Box[1]);
				}
			while ($TextWidth < $sx*0.75);
			/*  Awkward maths to get the origin of the text in the right place */
			$x = $sx/2 - cos(deg2rad($Rotation))*$TextWidth/2;
			$y = $sy/2 + sin(deg2rad($Rotation))*$TextWidth/2 + cos(deg2rad($Rotation))*$TextHeight/2;
			/* Make shadow text first followed by solid text */
			imagettftext($imc,$FontSize,$Rotation,$x+4,$y+4,$FontShadow,$Font,$Text);
			imagettftext($imc,$FontSize,$Rotation,$x,$y,$FontColor,$Font,$Text);
			/* merge original image into version with text to show image through text */
			imagecopymerge($imc,$OriginalImage,0,0,0,0,$sx,$sy,85);
			}
		return $imc;
		}
	//
	static function f_pays($f='FR',$indif=0)
		{
		if(!$indif) echo '<option value="">- '.__('Immaterial','rencontre').' -</option>';
		global $wpdb; global $rencDiv;
		$q = $wpdb->get_results("SELECT c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."' ORDER BY c_liste_valeur");
		foreach($q as $r)
			{
			echo '<option value="'.$r->c_liste_iso.'"'.(($r->c_liste_iso==$f)?' selected':'').'>'.$r->c_liste_valeur.'</option>';
			}
		}
	//
	static function f_regionBDD($f=1,$g=0)
		{
		global $rencOpt;
		if(!$g) $g = (isset($rencOpt['pays'])?$rencOpt['pays']:'FR');
		// Regions francaises par defaut
		// Copie de la version pour ajax dans rencontre.php
		echo '<option value="">- '.__('Immaterial','rencontre').' -</option>';
		if ($f)
			{
			global $wpdb; 
			$q = $wpdb->get_results("SELECT id, c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$g."' and c_liste_categ='r' ORDER BY c_liste_valeur");
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
		global $wpdb; global $drap; global $drapNom; global $rencDiv; global $rencCustom;
		$ho = false; if(has_filter('rencHighlightP', 'f_rencHighlightP')) $ho = apply_filters('rencHighlightP', $f);
		$s = $wpdb->get_row("SELECT U.display_name, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE U.user_status=0 and R.user_id=".$f." and R.user_id=P.user_id and R.user_id=U.ID");
		if($s!=false) {
		?>
		
				<div class="miniPortrait miniBox <?php if($ho) echo 'highlight'; ?>">
					<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $f; ?>';document.forms['rencMenu'].submit();">
					<?php if ($s->i_photo!=0) echo '<img class="tete" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($f)/1000).'/'.Rencontre::f_img(($f*10).'-mini').'.jpg" alt="'.$s->display_name.'" />';
					else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />'; ?>
					
					</a>
					<div>
						<h3><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $f; ?>';document.forms['rencMenu'].submit();"><?php echo $s->display_name; ?></a></h3>
						<?php if(!isset($rencCustom['born']) && strpos($s->d_naissance,'0000')===false) echo '<div class="monAge">'.Rencontre::f_age($s->d_naissance).'&nbsp;'.__('years','rencontre').'</div>';
						if(!isset($rencCustom['place'])) echo '<div class="maVille">'.$s->c_ville.'</div>'; ?>
						
					</div>
					<div style="clear:both"></div>
					<?php if($s->c_pays!="" && !isset($rencCustom['country'])) echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap[$s->c_pays].'" alt="'.$drapNom[$s->c_pays].'" title="'.$drapNom[$s->c_pays].'" />'; ?>
					<p><?php echo stripslashes($s->t_titre); ?></p>
					<?php echo (RencontreWidget::f_enLigne($f))?'<span class="rencInL">'.__('online','rencontre').'</span>':'<span class="rencOutL">'.__('offline','rencontre').'</span>'; ?>

				</div><!-- .miniPortrait -->
		<?php }
		}
	//
	static function f_miniPortrait2($f)
		{
		// miniPortrait2 : pour la fenetre du TCHAT
		// entree : id
		// sortie : code HTML avec le mini portrait
		global $wpdb; global $rencDiv; global $rencCustom;
		$s = $wpdb->get_row("SELECT U.display_name, R.c_pays, R.c_ville, R.d_naissance, R.i_photo, P.t_titre FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=".$f." and R.user_id=P.user_id and R.user_id=U.ID");
		if($s)
			{
			$drap1 = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' and c_liste_iso='".$s->c_pays."' ");
			$drapNom1 = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_iso='".$s->c_pays."' and c_liste_lang='".substr($rencDiv['lang'],0,2)."' ");
			echo substr($s->display_name,0,20)."|"; // pour f_tchat_dem : permet d'afficher le pseudo - memoire JS dans la variable 'ps' - limitation a 20 caracteres
			?>
				<div class="miniPortrait miniBox">
					<?php if ($s->i_photo!=0) echo '<img class="tete" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($f)/1000).'/'.Rencontre::f_img(($f*10).'-mini').'.jpg" alt="'.$s->display_name.'" />';
					else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" />'; ?>
					
					<div>
						<h3><?php echo $s->display_name; ?></h3>
						<?php if(!isset($rencCustom['born']) && strpos($s->d_naissance,'0000')===false) echo '<div class="monAge">'.Rencontre::f_age($s->d_naissance).'&nbsp;'.__('years','rencontre').'</div>';
						if(!isset($rencCustom['place'])) echo '<div class="maVille">'.$s->c_ville.'</div>'; ?>
					</div>
					<p><?php echo stripslashes($s->t_titre); ?></p>
					<?php 
					if($s->c_pays!="" && !isset($rencCustom['country'])) echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$drap1.'" alt="'.$drapNom1.'" title="'.$drapNom1.'" />'; ?>
					
				</div><!-- .miniPortrait -->
			<?php
			}
		}
	//
	static function f_enLigne($f)
		{
		global $rencDiv;
		if (is_file($rencDiv['basedir'].'/session/'.$f.'.txt') && time()-filemtime($rencDiv['basedir'].'/session/'.$f.'.txt')<180) return true;
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
	static function f_boiteReception($f,$hoAns=false) // retour AJAX
		{
		// entree : alias
		global $wpdb;
		$q = $wpdb->get_results("SELECT M.id, M.subject, M.content, M.sender, M.date, M.read FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$f."' and M.deleted!=1 ORDER BY M.date DESC");
		$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$f."' and M.read=0 and M.deleted=0");
		?>
			<div class="rencMenu boto10">
				<ul>
					<li class="current"><?php _e('Inbox','rencontre');?></li>
					<a href="javascript:void(0)" onClick="f_boite_envoi('<? echo $f; ?>','<?php echo admin_url('admin-ajax.php'); ?>','<?php echo $hoAns; ?>');">
						<li><?php _e('Messages sent','rencontre');?></li>
					</a>
				</ul>
			</div>
			<h4><?php _e('You have','rencontre'); echo '&nbsp;'.count($q).'&nbsp;'; _e('message','rencontre'); echo ((count($q)>1)?'s':'').' ('.$n.'&nbsp;'.__('unread','rencontre');?>)</h4>
			<table><tr><th></th><th style="width:20%;"><?php _e('Sender','rencontre');?></th><th style="width:50%;"><?php _e('Subject','rencontre');?></th><th style="width:25%;"><?php _e('Date','rencontre');?></th><th></th></tr>
			<?php foreach ($q as $r)
				{
				if ($r->read==1) echo '<tr><td></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.$r->sender.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td><a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\',\''.$hoAns.'\');">&nbsp;</a></td></tr>'."\n";
				else if ($r->read==2) echo '<tr><td><img src="'.plugins_url('rencontre/images/reponse.png').'" alt="" /></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.$r->sender.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td><a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\',\''.$hoAns.'\');">&nbsp;</a></td></tr>'."\n";
				else echo '<tr class="unread"><td></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.$r->sender.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td><a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\',\''.$hoAns.'\');">&nbsp;</a></td></tr>'."\n";
				} ?>
				
			</table>
		<?php }
	//
	static function f_boiteEnvoi($f,$hoAns=false) // retour AJAX
		{
		// entree : alias
		global $wpdb;
		$q = $wpdb->get_results("SELECT M.id, M.subject, M.content, M.recipient, M.date, M.read, M.deleted FROM ".$wpdb->prefix."rencontre_msg M WHERE M.sender='".$f."' and M.deleted!=2 ORDER BY M.date DESC");
		 ?>
			<div class="rencMenu boto10">
				<ul>
					<a href="javascript:void(0)" onClick="f_boite_reception('<? echo $f; ?>','<?php echo admin_url('admin-ajax.php'); ?>','<?php echo $hoAns; ?>');">
						<li><?php _e('Inbox','rencontre');?></li>
					</a>
					<li class="current"><?php _e('Messages sent','rencontre');?></li>
				</ul>
			</div>
			<table><tr><th></th><th style="width:20%;"><?php _e('Receiver','rencontre');?></th><th style="width:50%;"><?php _e('Subject','rencontre');?></th><th style="width:25%;"><?php _e('Date','rencontre');?></th><th></th></tr>
			<?php foreach ($q as $r)
				{
				if ($r->read==1) echo '<tr><td><img src="'.plugins_url('rencontre/images/oeil.png').'" alt="" /></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.$r->recipient.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td>'.(($r->deleted==1)?'<a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\',\''.$hoAns.'\');">&nbsp;</a>':'').'</td></tr>'."\n";
				else if ($r->read==2) echo '<tr><td><img src="'.plugins_url('rencontre/images/retour.png').'" alt="" /></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.$r->recipient.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td>'.(($r->deleted==1)?'<a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\',\''.$hoAns.'\');">&nbsp;</a>':'').'</td></tr>'."\n";
				else echo '<tr class="unread"><td></td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.$r->recipient.'</td><td onClick="f_voir_msg('.$r->id.',\''.admin_url('admin-ajax.php').'\',\''.$f.'\',\''.$hoAns.'\');">'.stripslashes($r->subject).'</td><td>'.$r->date.'</td><td>'.(($r->deleted==1)?'<a class="rencSupp" href="javascript:void(0)" onClick="f_supp_msg('.$r->id.',\''.admin_url("admin-ajax.php").'\',\''.$f.'\',\''.$hoAns.'\');">&nbsp;</a>':'').'</td></tr>'."\n";
				} ?>
				
			</table>
		<?php }
	//
	static function f_voirMsg($f,$a,$hoAns=false) // retour AJAX
		{
		// entree : $f = id message - $a = mon alias
		global $wpdb; global $rencDiv;
		$q = $wpdb->get_row("SELECT M.subject, M.content, M.sender, M.recipient, M.date, M.read FROM ".$wpdb->prefix."rencontre_msg M WHERE M.id='".$f."' ");
		if ($q)
			{
			$id = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."users WHERE user_login='".($q->sender!=$a?$q->sender:$q->recipient)."'");
			$p = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$id."'");
			echo '<a href="javascript:void(0)" onClick="document.forms[\'rencMenu\'].elements[\'page\'].value=\'portrait\';document.forms[\'rencMenu\'].elements[\'id\'].value=\''. $id.'\';document.forms[\'rencMenu\'].submit();">';
			if($p!=0) echo '<img class="tete" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($p)/10000).'/'.Rencontre::f_img((floor(($p)/10)*10).'-mini').'.jpg" alt="" />';
			else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$q->sender.'" title="'.$q->sender.'" />';
			echo '</a>';?>
			<div class="rencMenu boto10">
				<ul>
					<a href="javascript:void(0)" onClick="f_boite_reception('<? echo $a; ?>','<?php echo admin_url('admin-ajax.php'); ?>','<?php echo $hoAns; ?>');">
						<li><?php _e('Inbox','rencontre');?></li>
					</a>
					<a href="javascript:void(0)" onClick="f_boite_envoi('<? echo $a; ?>','<?php echo admin_url('admin-ajax.php'); ?>','<?php echo $hoAns; ?>');">
						<li><?php _e('Messages sent','rencontre');?></li>
					</a>
				</ul>
			</div>
			<h3><?php _e('Message','rencontre');?></h3>
			<div style="width:87%;">
				<div class="left">
					<?php _e('From','rencontre'); echo '&nbsp;:&nbsp;';
					if($q->sender!=$a)
						{
						echo '<a href="javascript:void(0)" onClick="document.forms[\'rencMenu\'].elements[\'page\'].value=\'portrait\';document.forms[\'rencMenu\'].elements[\'id\'].value=\''. $id.'\';document.forms[\'rencMenu\'].submit();">';
						echo $q->sender; 
						echo '</a>';
						}
					else echo $q->sender;
					?>
					
				</div>
				<div class="right"><?php _e('Date','rencontre'); echo '&nbsp;:&nbsp;'.$q->date; ?></div>
			</div>
			<div class="clear"><?php _e('To','rencontre'); echo '&nbsp;:&nbsp;';
				if($q->sender==$a)
					{
					echo '<a href="javascript:void(0)" onClick="document.forms[\'rencMenu\'].elements[\'page\'].value=\'portrait\';document.forms[\'rencMenu\'].elements[\'id\'].value=\''. $id.'\';document.forms[\'rencMenu\'].submit();">';
					echo $q->recipient; 
					echo '</a>';
					}
				else echo $q->recipient;
				?>
				
			</div>
			<h4><?php echo stripslashes($q->subject); ?></h4>
			<div class="rencBox"><?php echo stripslashes($q->content); ?></div>
			<?php
			if(!$hoAns){ ?><div class="button"><a href="javascript:void(0)" onClick="document.forms['formEcrire'].elements['page'].value='ecrire';document.forms['formEcrire'].elements['id'].value='<?php echo $id; ?>';document.forms['formEcrire'].elements['msg'].value='<?php echo $f; ?>';document.forms['formEcrire'].submit();"><?php _e('Answer','rencontre');?></a></div><?php }
			else echo '<div class="button right rencLiOff">'.__('Answer','rencontre').'</div>';
			?>
			<div class="clear"></div>
		<?php if ($q->read==0 && $q->sender!=$a) $wpdb->update($wpdb->prefix.'rencontre_msg', array('read'=>1), array('id'=>$f));
			}
		}
	//
	static function f_suppMsg($f,$a,$hoAns=false) // retour AJAX
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
		$a = $wpdb->get_var("SELECT user_login FROM ".$wpdb->prefix."users WHERE ID='".strip_tags($_GET["id"])."'");
		$q = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_msg WHERE subject='".strip_tags($_GET["sujet"])."' and content='".strip_tags($_GET["contenu"])."' and sender='".$f."' and recipient='".$a."' ");
		if(!$q)
			{
			$wpdb->insert($wpdb->prefix.'rencontre_msg', array('subject'=>strip_tags($_GET["sujet"]), 'content'=>strip_tags($_GET["contenu"]), 'sender'=>$f, 'recipient'=>$a, 'date'=>date('Y-m-d H:i:s'), 'read'=>0, 'deleted'=>0));
			if ($_GET["msg"]) $wpdb->update($wpdb->prefix.'rencontre_msg', array('read'=>2), array('id'=>strip_tags($_GET["msg"]))); // repondu
			// memo pour mail CRON
			if (!is_dir(dirname(__FILE__).'/cron_liste/')) mkdir(dirname(__FILE__).'/cron_liste/');
			if (!file_exists(dirname(__FILE__).'/cron_liste/'.strip_tags($_GET["id"]).'.txt')){ $t=fopen(dirname(__FILE__).'/cron_liste/'.strip_tags($_GET["id"]).'.txt', 'w'); fclose($t); }
			}
		}
	//
	static function f_cherchePlus($f)
		{
		// formulaire de la recherche plus
		global $wpdb; global $rencOpt; global $rencCustom;
		$q = $wpdb->get_row("SELECT i_sex, i_zsex, e_lat, e_lon FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$f."'");
		if (!strstr($_SESSION['rencontre'],'liste')) // nouvelle recherche
			{
			?>
		
					<div class="rencBox">
						<h3><?php _e('Search','rencontre'); ?></h3>
						<form id="formTrouve" name='formTrouve' method='get' action=''>
							<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
							<input type='hidden' name='page' value='' />
							<input type='hidden' name='id' value='<?php echo $f; ?>' />
							<input type='hidden' name='zsex' value='<?php echo $q->i_zsex; ?>' />
							<input type='hidden' name='homo' value='<?php echo (($q->i_sex==$q->i_zsex)?1:0); ?>' />
							<input type='hidden' name='pagine' value='0' />
							<table>
							<?php if(!isset($rencCustom['born'])) { ?>
							<tr>
								<td><?php _e('Age','rencontre');?>&nbsp;:</td>
								<td colspan="2"><span><?php _e('from','rencontre');?>&nbsp;
									<select name="ageMin" onChange="f_min(this.options[this.selectedIndex].value,'formTrouve','ageMin','ageMax');">
										<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
										
									</select>
									</span>
									<span>&nbsp;<?php _e('to','rencontre');?>&nbsp;
									<select name="ageMax" onChange="f_max(this.options[this.selectedIndex].value,'formTrouve','ageMin','ageMax');">
										<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
										
										<option value="99" selected>99&nbsp;<?php _e('years','rencontre');?></option>
									</select>
									</span>
								</td>
							</tr>
							<?php } if(!isset($rencCustom['size'])) { ?>
							<tr>
								<td><?php _e('Size','rencontre');?>&nbsp;:</td>
								<td colspan="2"><span><?php _e('from','rencontre');?>&nbsp;
									<select name="tailleMin" onChange="f_min(this.options[this.selectedIndex].value,'formTrouve','tailleMin','tailleMax');">
										<?php for ($v=140;$v<220;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}?>
										
									</select>
									</span>
									<span>&nbsp;<?php _e('to','rencontre');?>&nbsp;
									<select name="tailleMax" onChange="f_max(this.options[this.selectedIndex].value,'formTrouve','tailleMin','tailleMax');">
										<?php for ($v=140;$v<220;++$v) {echo '<option value="'.$v.'">'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}?>
										
										<option value="220" selected>220&nbsp;<?php _e('cm','rencontre');?></option>
									</select>
									</span>
								</td>
							</tr>
							<?php }
							if(!isset($rencCustom['weight'])) { ?>
							<tr>
								<td><?php _e('Weight','rencontre');?>&nbsp;:</td>
								<td colspan="2"><span><?php _e('from','rencontre');?>&nbsp;
									<select name="poidsMin" onChange="f_min(this.options[this.selectedIndex].value,'formTrouve','poidsMin','poidsMax');">
										<option value="140" selected>40&nbsp;<?php _e('kg','rencontre');?></option>
										<?php for ($v=41;$v<140;++$v) {echo '<option value="'.($v+100).'">'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}?>
										
									</select>
									</span>
									<span>&nbsp;<?php _e('to','rencontre');?>&nbsp;
									<select name="poidsMax" onChange="f_max(this.options[this.selectedIndex].value,'formTrouve','poidsMin','poidsMax');">
										<?php for ($v=40;$v<140;++$v) {echo '<option value="'.($v+100).'">'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}?>
										
										<option value="240" selected>140&nbsp;<?php _e('kg','rencontre');?></option>
									</select>
									</span>
								</td>
							</tr>
							<?php }
							if(isset($rencCustom['sex'])) { ?>
							<tr>
								<td>!!!!!<?php _e('Gender','rencontre');?>&nbsp;:</td>
								<td colspan="2">
									<select name="z2sex">
									<option value="">-</option>
									<?php for($v=2;$v<count($rencOpt['iam']);++$v) echo '<option value="'.$v.'" '.($v==$q->i_zsex?'selected':'').'>'.$rencOpt['iam'][$v].'</option>'; ?>
									</select>
								</td>
							</tr>
							<?php }
							if(!isset($rencCustom['country'])) { ?>
							<tr>
								<td><?php _e('Country','rencontre');?>&nbsp;:</td>
								<td colspan="2"><select id="rencPays" name="pays" onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect2');">
									<?php RencontreWidget::f_pays($rencOpt['pays']); ?>
									
									</select>
								</td>
							</tr>
							<?php } 
							if(!isset($rencCustom['place'])) {
								if(!isset($rencCustom['region'])) {
							?>
							<tr>
								<td><?php _e('Region','rencontre');?>&nbsp;:</td>
								<td colspan="2"><select id="regionSelect2" name="region">
									<?php RencontreWidget::f_regionBDD(1,$rencOpt['pays']); ?>
									
									</select>
								</td>
							</tr>
							<?php }
							$ho = false; if(has_filter('rencMapP', 'f_rencMapP')) $ho = apply_filters('rencMapP', $ho);
							// With WP_GEONAMES plugin
							if (!$ho && $rencOpt['map'] && function_exists('wpGeonames') && (($q->e_lon!=0 && $q->e_lat!=0) || isset($rencCustom['country'])) && $rencOpt['pays']!='')
								{ ?>
							<tr  class="renctrMap">
								<td colspan="2"><?php _e('City','rencontre');?>&nbsp;:<br />
									<input id="rencVille" name="ville" type="text" size="12" value="" onkeyup="<?php echo 'if(!rmap)f_cityMap(this.value,'.(isset($rencCustom['country'])?"'".$rencOpt['pays']."'":'document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].text').',\'0\',1);'; ?>f_city(this.value,'<?php echo admin_url('admin-ajax.php'); ?>',<?php if(isset($rencCustom['country'])) echo "'".$rencOpt['pays']."'"; else echo "document.getElementById('rencPays').options[document.getElementById('rencPays').selectedIndex].value"; ?>,1);" />
									<input id="gps" name="gps" type="hidden" />
									<div class="rencCity" id="rencCity"></div>
									<div class="rencTMap" id="rencTMap">
										<?php _e('Adjust the location by moving / zooming the map.','rencontre');?><br />
										<?php _e('Clicking on the map will place the cursor.','rencontre');?><br /><br />
										<div class="button" onClick="f_cityOk();f_cityKm(document.getElementById('rencKm').value);"><?php _e('Validate the position','rencontre');?></div>
									</div>
								</td>
								<td rowspan=2>
									<div id="rencMap" style="display:block"></div>
								</td>
							</tr>
							<tr class="renctrMap">
								<td colspan="2"><?php _e('Max range (km)','rencontre');?>&nbsp;:<br />
									<input id="rencKm" name="km" type="text" size="5" value="60" onkeyup="f_cityKm(this.value);" />
								</td>
							</tr>
								<?php }
								// No WP_GEONAMES
								else if (!$ho && $rencOpt['map'] && (($q->e_lon!=0 && $q->e_lat!=0) || isset($rencCustom['country'])) && $rencOpt['pays']!='') 
								{ ?>
							<tr class="renctrMap">
								<td colspan="2"><?php _e('City','rencontre');?>&nbsp;:<br />
									<input id="rencVille" name="ville" type="text" size="12" value="" <?php
										echo 'onkeyup="if(!rmap)f_cityMap(this.value,'.(isset($rencCustom['country'])?"'".$rencOpt['pays']."'":'document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].text').',\'0\',1);"'; 
										?> />
									<input id="gps" name="gps" type="hidden" />
									<div class="rencCity" id="rencCity"></div>
									<div class="rencTMap" id="rencTMap">
										<?php _e('Adjust the location by moving / zooming the map.','rencontre');?><br />
										<?php _e('Clicking on the map will place the cursor.','rencontre');?><br /><br />
										<div class="button" onClick="f_cityOk();f_cityKm(document.getElementById('rencKm').value);"><?php _e('Validate the position','rencontre');?></div>
									</div>
								</td>
								<td rowspan=2>
									<div id="rencMap" style="display:block"></div>
								</td>
							</tr>
							<tr class="renctrMap">
								<td colspan="2"><?php _e('Max range (km)','rencontre');?>&nbsp;:<br />
									<input id="rencKm" name="km" type="text" size="5" value="60" onkeyup="f_cityKm(this.value);" />
								</td>
							</tr>
								<?php }
								else
								// NO MAP
								{ ?>
							<tr>
								<td><?php _e('City','rencontre');?>&nbsp;:</td>
								<td colspan="2">
									<input id="rencVille" name="ville" type="text" size="12" />
									<div style="text-align:right;float:right;color:#888;font-size:80%;padding-top:4px;"><?php if(!$ho && $rencOpt['map'] && $rencOpt['pays']!='') _e('Incomplete account: no GoogleMap','rencontre');?></div>
									<input id="gps" name="gps" type="hidden" />
									<input id="rencKm" name="km" type="hidden" />
								</td>
							</tr>
							<?php
								} 
							}?>
							<tr>
								<td><?php _e('Only with picture','rencontre');?>&nbsp;</td>
								<td colspan="2"><input type="checkbox" name="photo" value="1" /></td>
							</tr>
							<tr>
								<td><?php _e('Relation','rencontre');?>&nbsp;:</td>
								<td colspan="2">
									<select name="relation">
										<option value="" selected>-</option>
										<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) echo '<option value="'.$v.'">'.$rencOpt['for'][$v].'</option>'; ?>
									</select>
								</td>
							</tr>
							<?php $ho = false; if(has_filter('rencProfilSP', 'f_rencProfilSP')) $ho = apply_filters('rencProfilSP', $ho); if($ho) echo $ho; ?>
							<?php $ho = false; if(has_filter('rencProfilOkP', 'f_rencProfilOkP')) $ho = apply_filters('rencProfilOkP', $ho);
							if($ho){ ?>
							<tr>
								<td><?php _e('Affinity with my profile','rencontre');?>&nbsp;</td>
								<td colspan="2"><input type="checkbox" name="profil" value="1"></td>
							</tr>
							<?php } ?>
							<?php $ho = false; if(has_filter('rencAstroOkP', 'f_rencAstroOkP') && !isset($rencCustom['born'])) $ho = apply_filters('rencAstroOkP', $ho);
							if($ho){ ?>
							<tr>
								<td><?php _e('Astrological affinity','rencontre');?>&nbsp;</td>
								<td colspan="2"><input type="checkbox" name="astro" value="1"></td>
							</tr>
							<?php } ?>
							<tr>
								<td><?php _e('Word in the ad','rencontre');?>&nbsp;:</td>
								<td colspan="2"><input type="text" name="mot" /></td>
							</tr>
							<tr>
								<td><?php _e('Alias','rencontre');?>&nbsp;:</td>
								<td colspan="2"><input type="text" name="pseudo" /></td>
							</tr>
							<tr>
								<td></td>
								<td colspan="2">
									<div class="button"><a href="javascript:void(0)" onClick="f_trouve();"><?php _e('Find','rencontre');?></a></div>
								</td>
							</tr>
							</table>
						</form>
					</div>
				<?php if(!isset($rencCustom['place'])) echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
			}
		else RencontreWidget::f_trouver();
		}
	//
	static function f_trouver()
		{
		// Resultat de la recherche plus
		global $wpdb; global $rencOpt; global $rencDiv; global $rencBlock; global $rencCustom;
		$pagine = (isset($_GET['pagine'])?$_GET['pagine']:0);
		$suiv = 1;
		?> 
		
		<form name='rencPagine' method='get' action=''>
			<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
			<input type='hidden' name='page' value='liste' />
			<input type='hidden' name='pays' value='<?php echo (isset($_GET['pays'])?$_GET['pays']:''); ?>' />
			<input type='hidden' name='region' value='<?php echo (isset($_GET['region'])?$_GET['region']:''); ?>' />
			<input type='hidden' name='ville' value='<?php echo (isset($_GET['ville'])?$_GET['ville']:''); ?>' />
			<input type='hidden' name='gps' value='<?php echo (isset($_GET['gps'])?$_GET['gps']:''); ?>' />
			<input type='hidden' name='km' value='<?php echo (isset($_GET['km'])?$_GET['km']:''); ?>' />
			<input type='hidden' name='pseudo' value='<?php echo (isset($_GET['pseudo'])?$_GET['pseudo']:''); ?>' />
			<input type='hidden' name='zsex' value='<?php echo (isset($_GET['zsex'])?$_GET['zsex']:''); ?>' />
			<?php if(isset($_GET['z2sex'])) echo "<input type='hidden' name='z2sex' value='".$_GET['z2sex']."' />"; ?>
			<input type='hidden' name='homo' value='<?php echo (isset($_GET['homo'])?$_GET['homo']:''); ?>' />
			<input type='hidden' name='ageMin' value='<?php echo (isset($_GET['ageMin'])?$_GET['ageMin']:''); ?>' />
			<input type='hidden' name='ageMax' value='<?php echo (isset($_GET['ageMax'])?$_GET['ageMax']:''); ?>' />
			<input type='hidden' name='tailleMin' value='<?php echo (isset($_GET['tailleMin'])?$_GET['tailleMin']:''); ?>' />
			<input type='hidden' name='tailleMax' value='<?php echo (isset($_GET['tailleMax'])?$_GET['tailleMax']:''); ?>' />
			<input type='hidden' name='poidsMin' value='<?php echo (isset($_GET['poidsMin'])?$_GET['poidsMin']:''); ?>' />
			<input type='hidden' name='poidsMax' value='<?php echo (isset($_GET['poidsMax'])?$_GET['poidsMax']:''); ?>' />
			<input type='hidden' name='mot' value='<?php echo (isset($_GET['mot'])?$_GET['mot']:''); ?>' />
			<input type='hidden' name='photo' value='<?php echo (isset($_GET['photo'])?$_GET['photo']:''); ?>' />
			<input type='hidden' name='profil' value='<?php echo (isset($_GET['profil'])?$_GET['profil']:''); ?>' />
			<input type='hidden' name='astro' value='<?php echo (isset($_GET['astro'])?$_GET['astro']:''); ?>' />
			<input type='hidden' name='relation' value='<?php echo (isset($_GET['relation'])?$_GET['relation']:''); ?>' />
			<input type='hidden' name='id' value='<?php echo (isset($_GET['id'])?$_GET['id']:''); ?>' />
			<input type='hidden' name='pagine' value='<?php echo $pagine; ?>' />
			<?php $ho = false; if(has_filter('rencProfilSrP', 'f_rencProfilSrP')) $ho = apply_filters('rencProfilSrP', 1); if($ho) echo $ho; ?>
		</form>
		<?php
		$hoprofil = false; $hoastro = false;
		if ($_GET['pseudo']) $s="SELECT R.user_id, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, P.t_annonce, P.t_action 
			FROM ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."users U 
			WHERE U.user_login LIKE '%".strip_tags($_GET['pseudo'])."%' and R.i_sex=".strip_tags($_GET['zsex'])." and U.ID=R.user_id and P.user_id=R.user_id and U.user_status=0";
		else
			{
			$s="SELECT U.user_login, R.user_id, ".((isset($_GET['astro']) && strip_tags($_GET['astro']))?'R.d_naissance, ':'')."R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, R.i_photo, R.e_lat, R.e_lon, R.d_session, P.t_annonce, ".((isset($_GET['profil']) && strip_tags($_GET['profil']))?'P.t_profil, ':'')."P.t_action 
				FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users_profil P, ".$wpdb->prefix."rencontre_users R 
				WHERE 
					U.ID=R.user_id 
					and U.user_status=0 
					and P.user_id=R.user_id
					and R.user_id!=".strip_tags($_GET['id']);
			if(!isset($_GET['z2sex'])) $s.=" and R.i_zsex".((strip_tags($_GET['homo']))?'=':'!=').strip_tags($_GET['zsex']);
			if(!isset($_GET['z2sex']) || $_GET['z2sex']!="") $s.=" and R.i_sex=".(isset($_GET['z2sex'])?strip_tags($_GET['z2sex']):strip_tags($_GET['zsex']));
			if (isset($_GET['ageMin']) && $_GET['ageMin']>18) {$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-strip_tags($_GET['ageMin']))); $s.=" and R.d_naissance<'".$zmin."'";}
			if (isset($_GET['ageMax']) && $_GET['ageMax'] && $_GET['ageMax']<99) {$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-strip_tags($_GET['ageMax'])));  $s.=" and R.d_naissance>'".$zmax."'";}
			if (isset($_GET['tailleMin']) && $_GET['tailleMin']>140) $s.=" and R.i_taille>='".strip_tags($_GET['tailleMin'])."'";
			if (isset($_GET['tailleMax']) && $_GET['tailleMax'] && $_GET['tailleMax']<220) $s.=" and R.i_taille<='".strip_tags($_GET['tailleMax'])."'";
			if (isset($_GET['poidsMin']) && $_GET['poidsMin']>140) $s.=" and R.i_poids>='".(strip_tags($_GET['poidsMin'])-100)."'";
			if (isset($_GET['poidsMax']) && $_GET['poidsMax'] && $_GET['poidsMax']<240) $s.=" and R.i_poids<='".(strip_tags($_GET['poidsMax'])-100)."'";
			if (isset($_GET['gps']) && $_GET['gps'] && $_GET['km'])
				{
				$gps = explode('|',strip_tags($_GET['gps']));
				if(isset($gps[1]))
					{
					$dlat = (strip_tags($_GET['km']) / 1.852 / 60);
					$dlon = (strip_tags($_GET['km']) / 1.852 / 60 / cos($gps[0] * 0.0174533));
					$s.=" and ((R.e_lat<".($gps[0]+$dlat)." and R.e_lat>".($gps[0]-$dlat)." and R.e_lon<".($gps[1]+$dlon)." and R.e_lon>".($gps[1]-$dlon).")";
					if ($_GET['ville']) $s.=" or R.c_ville LIKE '".strip_tags($_GET['ville'])."'";
					$s .= ")";
					}
				}
			else if (isset($_GET['ville']) && $_GET['ville']) $s.=" and R.c_ville LIKE '".strip_tags($_GET['ville'])."'";
			if (isset($_GET['pays']) && $_GET['pays']) $s.=" and R.c_pays='".$_GET['pays']."'";
			if (isset($_GET['region']) && $_GET['region']) $s.=" and R.c_region LIKE '".addslashes($wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".$_GET['region']."'"))."'";
			if ($_GET['mot']) $s.=" and (P.t_annonce LIKE '%".$_GET['mot']."%' or P.t_titre LIKE '%".strip_tags($_GET['mot'])."%')";
			if (isset($_GET['photo']) && $_GET['photo']=='1') $s.=" and R.i_photo>0";
			if (isset($_GET['relation']) && $_GET['relation']!='') $s.=" and R.i_zrelation='".strip_tags($_GET['relation'])."'";
			if(isset($_GET['astro']) && $_GET['astro'] && has_filter('rencAstroOkP', 'f_rencAstroOkP')) $hoastro = apply_filters('rencAstroOkP', $hoastro);
			else if(isset($_GET['profil']) && $_GET['profil'] && has_filter('rencProfilOkP', 'f_rencProfilOkP')) $hoprofil = apply_filters('rencProfilOkP', $hoprofil);
			$ho = false; if(has_filter('rencProfilSrP', 'f_rencProfilSrP')) $ho = apply_filters('rencProfilSrP', 0); if($ho) $s.=$ho;
			}
		if(!$hoastro && !$hoprofil)
			{
			$s.=" ORDER BY R.d_session DESC, P.d_modif DESC LIMIT ".($pagine*$rencOpt['limit']).", ".($rencOpt['limit']+1); // LIMIT indice du premier, nombre de resultat
			$q = $wpdb->get_results($s);
			if($wpdb->num_rows<=$rencOpt['limit']) $suiv=0;
			else array_pop($q); // supp le dernier ($rencOpt['limit']+1) qui sert a savoir si page suivante
			}
		else
			{
			$q = array(); $c = 0; $suiv = 0;
			if($hoastro) $q1 = apply_filters('rencAstroP', $s); // full search - no pagination
			else if($hoprofil) $q1 = apply_filters('rencProfilP', $s);
			foreach($q1 as $r)
				{
				if($c>=($pagine*$rencOpt['limit'])+$rencOpt['limit'])
					{
					$suiv = 1;
					break;
					}
				else if($c>=($pagine*$rencOpt['limit'])) $q[] = $r;
				++$c;
				}
			}
		if(isset($gps[1]))
			{
			echo '<div id="rencMap2" style="display:block;"></div>'."\r\n";
			echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>'."\r\n";
			echo '<script type="text/javascript">var lat='.$gps[0].',lon='.$gps[1].',gps=[';
			if($q) foreach ($q as $k=>$r)
				{
				if($k) echo',';
				echo '['.$r->e_lat.','.$r->e_lon.',"'.$r->i_photo.'","'.$r->user_login.'","'.$r->user_id.'","'.(($r->i_photo)?Rencontre::f_img((($r->user_id)*10).'-mini'):0).'"]';
				}
			echo '];'."\r\n".'jQuery(document).ready(function(){f_mapCherche(gps,lat,lon,"'.$rencDiv['siteurl'].'");});</script>'."\r\n";
			}
		if($q) foreach($q as $r)
			{ 
			$bl1=RencontreWidget::f_etat_bloque1($r->user_id,$r->t_action); // je suis bloque ?
			?>
			<div class="rencBox">
				<?php if (isset($r->d_session)) echo '<div class="rencDate" style="text-transform:capitalize">'.__('online','rencontre').'&nbsp;:&nbsp;'.substr($r->d_session,8,2).'.'.substr($r->d_session,5,2).'.'.substr($r->d_session,0,4).'</div>'; ?>
				<?php RencontreWidget::f_miniPortrait($r->user_id); ?>
				<div class="maxiBox right rel">
					<p style="margin-top:-20px;">
					<?php echo stripslashes($r->t_annonce);
					if($hoastro && $r->score) echo '<div class="affinity">'.__('Astrological affinity','rencontre').' : <span>'.$r->score.' / 5</span><img style="margin:-5px 0 0 5px;" src="'.plugins_url($hoastro.'/img/astro'.$r->score.'.png').'" alt="astro" /></div>';
					else if($hoprofil && $r->score) echo '<div class="affinity">'.__('Affinity with my profile','rencontre').' : <span>'.$r->score.'</span>&nbsp;'.__('points','rencontre').'.</div>'; ?>
					</p>
					<div style="height:38px;"></div>
					<div class="abso225  firstMaj">
						<?php if(isset($rencOpt['iam'][$r->i_zsex])) echo __('I\'m looking for','rencontre').'&nbsp;<span>'.$rencOpt['iam'][$r->i_zsex].'</span><br />';
						if(!isset($rencCustom['born']) && $r->i_zage_min) echo __('between','rencontre').'&nbsp;<span>'.$r->i_zage_min.'</span>&nbsp;'.__('and','rencontre').'&nbsp;<span>'.$r->i_zage_max.'</span>&nbsp;'.__('years','rencontre').'<br />';
						if(isset($rencOpt['for'][$r->i_zrelation])) echo __('for','rencontre').'&nbsp;<span>'.$rencOpt['for'][$r->i_zrelation].'</span>'; ?>
					</div>
					<div class="abso135">
						<?php if (!$bl1)
						{ ?>
						<?php
						$ho = false; if(has_filter('rencSendP', 'f_rencSendP')) $ho = apply_filters('rencSendP', $ho);
						if(!$ho && !$rencBlock){ ?><div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='ecrire';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Send a message','rencontre');?></a></div><?php }
						else echo '<div class="button right rencLiOff">'.__('Send a message','rencontre').'</div>';
						?>
						<?php if(!isset($rencCustom['smile']))
							{
							$ho = false; if(has_filter('rencSmileP', 'f_rencSmileP')) $ho = apply_filters('rencSmileP', $ho);
							if(!$ho && !$rencBlock){ ?><div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='sourire';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Smile','rencontre');?></a></div><?php }
							else echo '<div class="button right rencLiOff">'.__('Smile','rencontre').'</div>';
							}
						?>
						<?php 
						}
						else echo '<div class="button right rencLiOff">'.__('Send a message','rencontre').'</div><div class="button right rencLiOff">'.__('Smile','rencontre').'</div>'; ?>
						<div class="button right"><a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['page'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $r->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Profile','rencontre');?></a></div>
					</div>
				</div><!-- .grandeBox .right -->
				<div class="clear"></div>
			</div>
		<?php }
		if($pagine||$suiv)
			{
			echo '<div class="rencPagine">';
			if(($pagine+0)>0) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."-1;document.forms['rencPagine'].submit();\">".__('Previous page','rencontre')."</a>";
			for($v=max(0, $pagine-4); $v<$pagine; ++$v)
				{
				echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value='".$v."';document.forms['rencPagine'].submit();\">".$v."</a>";
				}
			echo "<span>".$pagine."</span>";
			if($suiv) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."+1;document.forms['rencPagine'].submit();\">".__('Next Page','rencontre')."</a>";
			echo '</div>';
			}

		}
	//
	static function f_nouveauMembre($f,$g)
		{
		// $f : ID
		// $g : 1, 2, 3, OK
		global $wpdb; global $rencOpt;
		if($g=="1")
			{
			if(isset($_POST['annee']) && isset($_POST['mois']) && isset($_POST['jour'])) $nais = $_POST['annee'].'-'.((strlen($_POST['mois'])<2)?'0'.$_POST['mois']:$_POST['mois']).'-'.((strlen($_POST['jour'])<2)?'0'.$_POST['jour']:$_POST['jour']);
			$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$f)); // suppression si existe deja
			$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f)); // suppression si existe deja
			$wpdb->insert($wpdb->prefix.'rencontre_users', array(
				'user_id'=>$f,
				'c_ip'=>$_SERVER['REMOTE_ADDR'],
				'c_pays'=>$rencOpt['pays'], // default - custom no localisation
				'i_sex'=>strip_tags($_POST['sex']),
				'd_naissance'=>(isset($nais)?strip_tags($nais):''),
				'i_taille'=>(isset($_POST['taille'])?strip_tags($_POST['taille']):''),
				'i_poids'=>(isset($_POST['poids'])?strip_tags($_POST['poids']):''),
				'd_session'=>date("Y-m-d H:i:s"),
				'i_photo'=>0));
			$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f, 'd_modif'=>date("Y-m-d H:i:s"),));
			}
		else if($g=="2")
			{
			if(isset($_POST['region'])) $region=$wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_POST['region'])."'");
			if(isset($_POST['gps'])) $gps=explode("|",strip_tags($_POST['gps']."|0|0"));
			$wpdb->update($wpdb->prefix.'rencontre_users', array(
				'c_pays'=>((isset($_POST['pays']) && $_POST['pays'])?$_POST['pays']:$rencOpt['pays']),
				'c_region'=>(isset($region)?$region:''),
				'c_ville'=>(isset($_POST['ville'])?strip_tags($_POST['ville']):''),
				'e_lat'=>(isset($_POST['gps'])?round($gps[0],5):''),
				'e_lon'=>(isset($_POST['gps'])?round($gps[1],5):'')),
				array('user_id'=>$f));
			}
		else if($g=="3")
			{
			$wpdb->update($wpdb->prefix.'rencontre_users', array(
				'i_zsex'=>strip_tags($_POST['zsex']),
				'i_zage_min'=>(isset($_POST['zageMin'])?strip_tags($_POST['zageMin']):''),
				'i_zage_max'=>(isset($_POST['zageMax'])?strip_tags($_POST['zageMax']):''),
				'i_zrelation'=>strip_tags($_POST['zrelation'])),
				array('user_id'=>$f));
			}
		else if($g=='update')
			{
			if(isset($_POST['annee']) && isset($_POST['mois']) && isset($_POST['jour'])) $nais = $_POST['annee'].'-'.((strlen($_POST['mois'])<2)?'0'.$_POST['mois']:$_POST['mois']).'-'.((strlen($_POST['jour'])<2)?'0'.$_POST['jour']:$_POST['jour']);
			if(isset($_POST['region'])) $region=$wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_POST['region'])."'");
			if(isset($_POST['gps'])) $gps=explode("|",strip_tags($_POST['gps']."|0|0"));
			$wpdb->update($wpdb->prefix.'rencontre_users', array(
				'c_pays'=>(isset($_POST['pays'])?$_POST['pays']:$rencOpt['pays']),
				'c_region'=>(isset($region)?$region:''),
				'c_ville'=>(isset($_POST['ville'])?strip_tags($_POST['ville']):''),
				'e_lat'=>(isset($_POST['gps'])?round($gps[0],5):''),
				'e_lon'=>(isset($_POST['gps'])?round($gps[1],5):''),
				'i_sex'=>(isset($_POST['sex'])?strip_tags($_POST['sex']):''),
				'd_naissance'=>(isset($nais)?strip_tags($nais):''),
				'i_taille'=>(isset($_POST['taille'])?strip_tags($_POST['taille']):''),
				'i_poids'=>(isset($_POST['poids'])?strip_tags($_POST['poids']):''),
				'i_zsex'=>(isset($_POST['zsex'])?strip_tags($_POST['zsex']):''),
				'i_zage_min'=>(isset($_POST['zageMin'])?strip_tags($_POST['zageMin']):''),
				'i_zage_max'=>(isset($_POST['zageMax'])?strip_tags($_POST['zageMax']):''),
				'i_zrelation'=>(isset($_POST['zrelation'])?strip_tags($_POST['zrelation']):''), 
				'd_session'=>date("Y-m-d H:i:s")),
				array('user_id'=>$f));
			}
		}
	//
	static function f_changePass($f)
		{
		if($_POST['pass1']!="aQwZsXeDc") wp_set_password($_POST['pass1'],$f); // changement MdP
		wp_clear_auth_cookie();
		wp_set_auth_cookie($f); // cookie pour rester connecte
		}

	//
	static function f_compte($mid)
		{
		// Fenetre de modification du compte
		global $wpdb; global $rencOpt; global $drapNom; global $rencCustom;
		$q = $wpdb->get_row("SELECT U.user_email, U.user_login, R.c_pays, R.c_region, R.c_ville, R.i_sex, R.d_naissance, R.i_taille, R.i_poids, R.i_zsex, R.i_zage_min, R.i_zage_max, R.i_zrelation, R.e_lat, R.e_lon FROM ".$wpdb->prefix . "users U, ".$wpdb->prefix . "rencontre_users R WHERE U.ID=".$mid." and U.ID=R.user_id");
		list($Y, $m, $j) = explode('-', $q->d_naissance);
		?>
			<div id="rencAlert1"></div>
			<?php
			$ho = false; if(has_filter('rencCheckoutP', 'f_rencCheckoutP')) $ho = apply_filters('rencCheckoutP', 1);
			if($ho) echo $ho;
			?>
			<h2><?php _e('Change password','rencontre');?></h2>
			<form name="formPass" method='post' action=''>
			<input type='hidden' name='page' value='' />
			<input type='hidden' name='id' value='' />
			<table>
				<tr>
					<th><?php _e('Former','rencontre');?></th>
					<th><?php _e('New','rencontre');?></th>
					<th><?php _e('Retype the new','rencontre');?></th>
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
			<h2><?php _e('My Account','rencontre'); ?><span style="font-size:16px;font-weight:400;margin-left:10px;">(<?php echo $q->user_email; ?>)</span></h2>
			<form name="formNouveau" method='post' action=''>
			<input type='hidden' name='nouveau' value='update' />
			<input type='hidden' name='a1' value='' />
			<input type='hidden' name='pseudo' value='<?php echo $q->user_login; ?>' />
			<table style="border-bottom:none;margin-bottom:0;">
				<tr>
					<th><?php _e('I am','rencontre');?></th>
					<?php if(!isset($rencCustom['born'])) echo '<th>'.__('Born','rencontre').'</th>'; else echo '<th></th>'; ?>
				</tr>
				<tr>
					<td>
						<select name="sex" size=2>
						<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) echo '<option value="'.$v.'" '.(($q->i_sex==$v)?' selected':'').'>'.$rencOpt['iam'][$v].'</option>'; ?>
						</select>
					</td>
					<?php if(!isset($rencCustom['born'])) { ?>
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
					<?php } else echo '<td></td>'; ?>
				</tr>
				<?php if(!isset($rencCustom['place'])) { ?>
				<tr>
					<?php if(!isset($rencCustom['country'])) echo '<th>'.__('My country','rencontre').'</th>'; else echo '<th></th>'; 
					if(!isset($rencCustom['region'])) echo '<th>'.__('My region','rencontre').'</th>'; else echo '<th></th>'; ?>
				</tr>
				<tr>
					<?php if(!isset($rencCustom['country'])) { ?>
					<td>
						<select id="rencPays" name="pays" size=6 onChange="f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect2');">
							<?php RencontreWidget::f_pays($q->c_pays); ?>
							
						</select>
					</td>
					<?php } else echo '<td></td>';
					if(!isset($rencCustom['region'])) { ?>
					<td>
						<select id="regionSelect2" size=6 name="region">
							<?php if($q->c_region) RencontreWidget::f_regionBDD($q->c_region,$q->c_pays); else RencontreWidget::f_regionBDD(1,$q->c_pays); ?>
							
						</select>
					</td>
					<?php } else echo '<td></td>'; ?>
				</tr>
			</table>
			<table style="border-bottom:none;margin-bottom:0;border-top:none;margin-top:0;">
				<tr class="renctrMap">
					<th><?php _e('My city','rencontre');?></th>
					<?php if(isset($rencOpt['map']) && $rencOpt['map']) echo '<th></th>'; ?>
				</tr>
				<tr  class="renctrMap">
					<td>
						<input id="rencVille" name="ville" type="text" size="18" value="<?php echo $q->c_ville; ?>" <?php if (function_exists('wpGeonames')) echo 'onkeyup="f_city(this.value,\''.admin_url('admin-ajax.php').'\','.(!isset($rencCustom['country'])?'document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].value':'\''.$q->c_pays.'\'').',0);"'; ?> />
						<input id="gps" name="gps" type="hidden" value="<?php echo $q->e_lat.'|'.$q->e_lon; ?>" />
						<div class="rencCity" id="rencCity"></div>
						<?php if(isset($rencOpt['map']) && $rencOpt['map']) echo '<div class="rencTMap" id="rencTMap">'.__('Adjust the location by moving / zooming the map.','rencontre').'<br />'.__('Clicking on the map will place the cursor.','rencontre').'<br /><br /><div class="button" onClick="f_cityOk();">'.__('Validate the position','rencontre').'</div></div>'; ?>
					</td>
					<?php if(isset($rencOpt['map']) && $rencOpt['map']) echo '<td><div id="rencMap"></div></td>'; ?>
				</tr>
			<?php
			if(isset($rencOpt['map']) && $rencOpt['map'])
				{
				if($q->e_lat!=0 && $q->e_lon!=0) echo '<script type="text/javascript">jQuery(document).ready(function(){f_cityMap("'.$q->c_ville.'","'.$q->e_lat.'","'.$q->e_lon.'",1);});</script>';
				else echo '<script type="text/javascript">jQuery(document).ready(function(){f_cityMap("'.$q->c_ville.'","'.$drapNom[$q->c_pays].'","0",1);});</script>';
				}
			?>
			</table>
			<table>
			<?php }
				if(!isset($rencCustom['weight']) || !isset($rencCustom['size']))
					{
					$o = '';
					$o.='<tr>';
						$o.='<th>'.(!isset($rencCustom['size'])?__('My size','rencontre'):'').'</th>';
						$o.='<th>'.(!isset($rencCustom['weight'])?__('My weight','rencontre'):'').'</th>';
					$o.='</tr><tr>';
					if(!isset($rencCustom['size']))
						{
						$o.='<td><select name="taille" size=6>';
							for ($v=140;$v<220;++$v) {$o.='<option value="'.$v.'"'.(($v==$q->i_taille)?' selected':'').'>'.$v.'&nbsp;'.__('cm','rencontre').'</option>';}
						$o.='</select></td>';
						}
					else $o.='<td></td>';
					if(!isset($rencCustom['weight']))
						{
						$o.='<td><select name="poids" size=6>';
							for ($v=40;$v<140;++$v) {$o.= '<option value="'.$v.'"'.(($v==$q->i_poids)?' selected':'').'>'.$v.'&nbsp;'.__('kg','rencontre').'</option>';}
						$o .='</select></td>';
						}
					else $o.='<td></td>';
					$o.='</tr>';
					echo $o;
					} ?>
				<tr>
					<th><?php _e('I\'m looking for','rencontre');?></th>
					<?php if(!isset($rencCustom['born'])) echo '<th>'.__('Age min/max','rencontre').'</th>'; else echo '<th></th>'; ?>
				</tr>
				<tr>
					<td>
						<select name="zsex" size=2>
						<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) echo '<option value="'.$v.'" '.(($q->i_zsex==$v)?' selected':'').'>'.$rencOpt['iam'][$v].'</option>'; ?>
						</select>
					</td>
					<?php if(!isset($rencCustom['born'])) { ?>
					<td>
						<select name="zageMin" size=6 onChange="f_min(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
							<?php for ($v=18;$v<99;++$v) {echo '<option value="'.$v.'"'.(($v==$q->i_zage_min)?' selected':'').'>'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
							
						</select>
						<select name="zageMax" size=6 onChange="f_max(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');">
							<?php for ($v=19;$v<100;++$v) {echo '<option value="'.$v.'"'.(($v==$q->i_zage_max)?' selected':'').'>'.$v.'&nbsp;'.__('years','rencontre').'</option>';}?>
							
						</select>
					</td>
					<?php } else echo '<td></td>'; ?>
				</tr>
				<tr>
					<th><?php _e('For','rencontre');?></th>
					<th></th>
				</tr>
				<tr>
					<td>
						<select name="zrelation" size=3>
						<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) echo '<option value="'.$v.'" '.(($q->i_zrelation==$v)?' selected':'').'>'.$rencOpt['for'][$v].'</option>'; ?>
						</select>
					</td>
					<td>
						<div class="button"><a href="javascript:void(0)" onClick="f_mod_nouveau(<?php echo $mid; ?>)"><?php _e('Save','rencontre');?></a></div>
					</td>
				</tr>
			</table>
			</form>
			<h2><?php _e('Account deletion','rencontre');?></h2>
			<form name="formFin" method='post' action=''>
			<input type='hidden' name='page' value='' />
			<input type='hidden' name='id' value='' />
			<table><tr><th style="text-align:left;">
				<?php _e('This action will result in the complete deletion of your account and everything about you from our server. We do not keep historical accounts.','rencontre');?>
				</th></tr>
				<tr><td>
				<strong><?php _e('Please note that this action is irreversible !','rencontre');?></strong>
				<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="f_fin(document.forms['formFin'].elements['id'].value,<?php echo $mid; ?>)"><?php _e('Delete Account','rencontre');?></a></div>
			</td></tr></table>
			</form>
			<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<?php }
	//
	static function f_sourire($f)
		{
		// envoi un sourire a ID=$f
		global $wpdb; global $current_user; global $rencOpt;
		// 1. mon compte : sourireOut
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."'");
		$action= json_decode($q,true);
		$action['sourireOut']=(isset($action['sourireOut'])?$action['sourireOut']:null);
		$c = count($action['sourireOut']);
		if ($c) { foreach ($action['sourireOut'] as $r) { if ($r['i']==$f) {_e('Smile already sent','rencontre'); return; } } } // deja souri
		$action['sourireOut'][$c]['i'] = ($f+0);
		$action['sourireOut'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
		// 2. son compte : sourireIn
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$action= json_decode($q,true);
		$action['sourireIn']=(isset($action['sourireIn'])?$action['sourireIn']:null);
		$c = count($action['sourireIn']);
		$action['sourireIn'][$c]['i'] = ($current_user->ID+0);
		$action['sourireIn'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		// memo pour mail CRON
		if (!is_dir(dirname(__FILE__).'/cron_liste/')) mkdir(dirname(__FILE__).'/cron_liste/');
		if (!file_exists(dirname(__FILE__).'/cron_liste/'.$f.'.txt') && isset($rencOpt['mailsmile']) && $rencOpt['mailsmile']){ $t=fopen(dirname(__FILE__).'/cron_liste/'.$f.'.txt', 'w'); fclose($t); }
		_e('Smile sent','rencontre');
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
	static function f_demcont($f)
		{
		// demander un contact a ID=$f
		global $wpdb; global $current_user;
		// 1. mon compte : contactOut
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."'");
		$action= json_decode($q,true);
		$action['contactOut']=(isset($action['contactOut'])?$action['contactOut']:null);
		$c = count($action['contactOut']);
		if ($c) { foreach ($action['contactOut'] as $r) { if ($r['i']==$f) {_e('Contact already requested','rencontre'); return; } } } // deja demande
		$action['contactOut'][$c]['i'] = ($f+0);
		$action['contactOut'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
		// 2. son compte : contactIn
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$action= json_decode($q,true);
		$action['contactIn']=(isset($action['contactIn'])?$action['contactIn']:null);
		$c = count($action['contactIn']);
		$action['contactIn'][$c]['i'] = ($current_user->ID+0);
		$action['contactIn'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		// memo pour mail CRON
		if (!is_dir(dirname(__FILE__).'/cron_liste/')) mkdir(dirname(__FILE__).'/cron_liste/');
		if (!file_exists(dirname(__FILE__).'/cron_liste/'.$f.'.txt')){ $t=fopen(dirname(__FILE__).'/cron_liste/'.$f.'.txt', 'w'); fclose($t); }
		_e('Contact request sent','rencontre');
		}
	//
	static function f_signal($f)
		{
		// envoi un signalement sur ID=$f
		global $wpdb; global $current_user;
		// 1. mon compte : sourireOut
		$q = $wpdb->get_var("SELECT t_signal FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$signal= json_decode($q,true);
		$c = count($signal);
		if ($c) { foreach ($signal as $r) { if ($r['i']==$current_user->ID) {_e('Reporting already done','rencontre'); return; } } } // deja signale par mid
		$signal[$c]['i'] = ($current_user->ID+0);
		$signal[$c]['d'] = date("Y-m-d");
		$out = json_encode($signal);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_signal'=>$out), array('user_id'=>$f));
		_e('Thank you for your report','rencontre');
		}
	//
	static function f_bloque($f)
		{
		// bloque ou debloque ID=$f
		global $wpdb; global $current_user;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."'");
		$action= json_decode($q,true);
		$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
		$c = count($action['bloque']); $c1=0;
		if ($c) {foreach ($action['bloque'] as $r)
			{
			if ($r['i']==$f) // deja bloque : on debloque
				{
				unset($action['bloque'][$c1]['i']);unset($action['bloque'][$c1]['d']);
				$action['bloque']=array_filter($action['bloque']);
				$out = json_encode($action);
				$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
				return;
				}
			++$c1;
			}}
		// pas bloque : on bloque
		$action['bloque'][$c]['i'] = ($f+0);
		$action['bloque'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
		}
	//
	static function f_etat_bloque($f)
		{
		// regarde si un membre est bloque
		global $wpdb; global $current_user;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."'");
		$action= json_decode($q,true);
		$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
		$c = count($action['bloque']); if ($c) {foreach ($action['bloque'] as $r){if ($r['i']==$f) return true; }} // est bloque
		else return false;
		}
	//
	static function f_etat_bloque1($f,$action=0)
		{
		// regarde si un membre m a bloque
		global $current_user;
		if ($action==0)
			{
			global $wpdb;
			$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
			$action= json_decode($q,true);
			}
		$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
		$c = count($action['bloque']); if ($c) {foreach ($action['bloque'] as $r){if ($r['i']==$current_user->ID) return true; }} // est bloque
		else return false;
		}
	//
	static function f_visite($f)
		{
		// id : MID visite F - sauvegarde chez F
		global $wpdb; global $current_user;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."'");
		$action= json_decode($q,true);
		$action['visite']=(isset($action['visite'])?$action['visite']:null);
		$c = count($action['visite']);
		if ($c>60) RencontreWidget::f_menage_action($f,$action);
		if ($c) {foreach ($action['visite'] as $r) { if ($r['i']==$current_user->ID) return; }}
		// pas encore vu
		$action['visite'][$c]['i'] = ($current_user->ID+0);
		$action['visite'][$c]['d'] = date("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		}
	//
	static function f_distance($lat,$lon)
		{
		// distance from me
		global $wpdb; global $current_user;
		$q = $wpdb->get_row("SELECT e_lat, e_lon FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$current_user->ID."'");
		if($q->e_lat!=0 && $q->e_lat!=0 && $lat!=0 && $lon!=0)
			{
			$d = (floor(sqrt(pow(($q->e_lat-$lat)*60*1.852,2)+pow(($q->e_lon-$lon)*60*1.852*cos(($lat+$q->e_lat) / 2 * 0.0174533),2))));
			echo '<em>('.$d.' km '.__('from my position','rencontre').')</em>';
			}
		return;
		}

	static function f_menage_action($f,$action)
		{
		// fait le menage dans le json action - limite a 50 elements par item
		$a = array("sourireIn","sourireOut","contactIn","contactOut","visite","bloque");
		for ($v=0; $v<count($a); ++$v)
			{
			$c = count($action[$a[$v]]);
			for ($w=0; $w<$c-50; ++$w) 
				{
				unset($action[$a[$v]][$w]['i']);
				unset($action[$a[$v]][$w]['d']);
				}
			if($action[$a[$v]]) $action[$a[$v]]=array_filter($action[$a[$v]]);
			if($action[$a[$v]]) $action[$a[$v]]=array_splice($action[$a[$v]],0); // remise en ordre avec de nouvelles clefs
			}
		$out = json_encode($action);
		global $wpdb;
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		}
	static function rencGate()
		{
		// Entry screening
		$ho = false; if(has_filter('rencCheckoutP', 'f_rencCheckoutP')) $ho = apply_filters('rencCheckoutP', $ho);
		echo $ho;
		}
	//
	} // CLASSE
//
