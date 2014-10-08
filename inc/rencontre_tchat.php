<?php
// ******************************************************************************************************************
// Fichier d'appel AJAX en boucle courte pour le tchat. Beaucoup plus rapide que le passage par admin-ajax.php
// ******************************************************************************************************************
//
if ($_POST && isset($_POST['tchat']))
	{
	$tc = strip_tags($_POST['tchat']);
	if (isset($_POST['fm'])) $fm = strip_tags($_POST['fm']);
	if (isset($_POST['to'])) $to = strip_tags($_POST['to']);
	if (isset($_POST['msg'])) $ms = strip_tags($_POST['msg']);
	$d = '../../../uploads/tchat/';
	// **************************
	if ($tc=='tchatVeille')
		{
		if (!file_exists($d.$fm.'.txt'))
			{  // init de mon txt
			$t=fopen($d.$fm.'.txt', 'w'); fclose($t);
			echo null;
			}
		else if (filesize($d.$fm.'.txt')==0)
			{
			echo null;
			if (is_file('../../../uploads/session/'.$fm.'.txt') && time()-filemtime('../../../uploads/session/'.$fm.'.txt')<150)
				{ // confirme en ligne (180 secondes d inactivite max. voir f_en_ligne() rencontre_widget.php)
				$t=fopen('../../../uploads/session/'.$fm.'.txt', 'w'); fclose($t);
				}
			}
		else if (time()-filemtime($d.$fm.'.txt')>150)
			{ // trop ancien
			echo null;
			$t=fopen($d.$fm.'.txt', 'w'); fclose($t);
			}
		else
			{
			$t=fopen($d.$fm.'.txt', 'r'); $r=fread($t, 15); fclose($t);
			$to=substr($r,1,(strpos($r,']')-1));
			echo $to;
			}
		}
	// **************************
	else if ($tc=='tchatDebut') // mon ID dans les deux txt
		{
		if (filesize($d.$fm.'.txt')===0 && filesize($d.$to.'.txt')===0)
			{
			$t=fopen($d.$fm.'.txt', 'wb'); fwrite($t,'['.$fm.']',15); fclose($t);
			$t=fopen($d.$to.'.txt', 'wb'); fwrite($t,'['.$fm.']',15); fclose($t);
			}
		clearstatcache();
		}
	// **************************
	else if ($tc=='tchatFin') // vide les deux txt
		{
		$t=fopen($d.$to.'.txt', 'w'); fclose($t);
		$t=fopen($d.$fm.'.txt', 'w'); fclose($t);
		clearstatcache();
		}
	// **************************
	else if ($tc=='tchatOk') // accepte le tchat - mon ID dans txt du demandeur
		{
		$t=fopen('../../../uploads/session/'.$fm.'.txt', 'w'); fclose($t);
		$t=fopen($d.$to.'.txt', 'wb'); fwrite($t,'['.$fm.']-',15); fclose($t);
		clearstatcache();
		}
	// **************************
	else if ($tc=='tchatScrute')
		{
// *** MOD 1
		if (time()-filemtime('../../../uploads/session/'.$fm.'.txt')>5)
			{
			$t=fopen('../../../uploads/session/'.$fm.'.txt', 'w'); fclose($t); // raffraichissement de ma session 5 sec
			}
		if (!file_exists($d.$fm.'.txt') || filesize($d.$fm.'.txt')===0) echo "::".$fm."::"; // fin du chat =>JS f_tchat_off()
		else if (time()-filemtime('../../../uploads/session/'.$to.'.txt')>15) // sa session >15 sec : fin sauf demande
			{
			$t=fopen($d.$fm.'.txt', 'r'); $r=fread($t, 15); fclose($t);
			$r=substr($r,1,(strpos($r,']')-1));
			if ($r!=$fm || time()-filemtime('../../../uploads/session/'.$to.'.txt')>180) // fin si session to > 180 (voir f_en_ligne() rencontre_widget.php)
				{
				echo "::".$fm."::"; // fin du chat sauf si demande en cours (fm dans mon fichier fm)
				$t=fopen($d.$fm.'.txt', 'w');fclose($t);
				}
			else echo null;
			clearstatcache();
			}
// *******
		else if (filesize($d.$fm.'.txt')>strlen('[]'.$to))
			{
			$t=fopen($d.$fm.'.txt', 'r'); $r=fread($t, filesize($d.$fm.'.txt')); fclose($t); // lecture de ma boite
			if (substr($r,1,(strpos($r,']')-1))!=$fm)
				{ // vide ma boite
				$t=fopen($d.$fm.'.txt', 'w'); fwrite($t,'['.$to.']',15); fclose($t);
				echo stripslashes($r);
				}
			else echo null;
			clearstatcache();
			}
		else echo null;
		}
	// **************************
	else if ($tc=='tchatEnvoi')
		{
		if (!is_dir($d)) mkdir($d); $r = "";
		if (filemtime($d.$to.'.txt')>filemtime($d.$fm.'.txt')) // pas encore de reponse : deux messages de suite
			{ // envoi mon message en conservant le precedant
			$t=fopen($d.$to.'.txt', 'a+'); fwrite($t,'['.$fm.']'.$ms); fclose($t);
			}
		else  // au moins une reponse
			{
			if (filesize($d.$fm.'.txt')>0)
				{
				$t=fopen($d.$fm.'.txt', 'r');$r=fread($t, filesize($d.$fm.'.txt')); fclose($t); // lecture de ma boite
				$t=fopen($d.$fm.'.txt', 'wb'); fwrite($t,'['.$to.']'); fclose($t); // vide ma boite
				}
			$t=fopen($d.$to.'.txt', 'wb'); fwrite($t,'['.$fm.']'.$ms); fclose($t); // envoi mon message
			}
		echo $r;
		clearstatcache();
		}
	// **************************
	else if ($tc=='cam')
		{
		// sauvegarde de l image de la webcam
		$im = str_replace(" ","+",strip_tags($_POST['image']));
		$im = substr($im, 1+strrpos($im, ','));
		if (strip_tags($_POST['id']))
			{
			file_put_contents($d."cam".strip_tags($_POST['id']).".jpg", base64_decode($im));
			clearstatcache();
			}
		}
	}
else die;
//
?>