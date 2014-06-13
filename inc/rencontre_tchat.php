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
	if ($tc=='tchatVeille')
		{
		if (!file_exists($d.$fm.'.txt')){ $t=fopen($d.$fm.'.txt', 'w'); fclose($t); } // init de mon txt
		if (filesize($d.$fm.'.txt')==0)
			{
			echo null;
			if (is_file('../../../uploads/session/'.$fm.'.txt') && time()-filemtime('../../../uploads/session/'.$fm.'.txt')<150)
				{ // confirme en ligne (180 secondes d inactivite max)
				$t = fopen('../../../uploads/session/'.$fm.'.txt', 'w');
				fclose($t);
				}
			}
		else { $t=fopen($d.$fm.'.txt', 'r'); $r=fread($t, 15); fclose($t); echo substr($r,1,(strpos($r,']')-1)); }
		
		}
	//
	else if ($tc=='tchatDebut') // mon ID dans les deux txt
		{
		if (filesize($d.$fm.'.txt')===0 && filesize($d.$to.'.txt')===0) {$t=fopen($d.$fm.'.txt', 'wb'); fwrite($t,'['.$fm.']',15); fclose($t); $t=fopen($d.$to.'.txt', 'wb'); fwrite($t,'['.$fm.']',15); fclose($t);}
		clearstatcache();
		}
	//
	else if ($tc=='tchatFin') // vide les deux txt
		{$t=fopen($d.$to.'.txt', 'w'); fclose($t); $t=fopen($d.$fm.'.txt', 'w'); fclose($t);clearstatcache();}
	//
	else if ($tc=='tchatOk') // accepte le tchat - mon ID dans txt du demandeur
		{$t=fopen($d.$to.'.txt', 'wb'); fwrite($t,'['.$fm.']-',15); fclose($t);clearstatcache();}
	//
	else if ($tc=='tchatScrute')
		{
		if (!file_exists($d.$fm.'.txt') || filesize($d.$fm.'.txt')===0) echo "::".$fm."::"; // fin du chat
		else if (filesize($d.$fm.'.txt')>strlen('[]'.$to))
			{
			$t=fopen($d.$fm.'.txt', 'r'); $r=fread($t, filesize($d.$fm.'.txt')); fclose($t); // lecture de ma boite
			if (substr($r,1,(strpos($r,']')-1))!=$fm) { $t=fopen($d.$fm.'.txt', 'w'); fwrite($t,'['.$to.']',15); fclose($t); echo stripslashes($r); }// vide ma boite
			else echo null;
			clearstatcache();
			}
		else echo null;
		}
	//
	else if ($tc=='tchatEnvoi')
		{
		if (!is_dir($d)) mkdir($d); $r = "";
		// toute la conversation dans tchat/f min - max.txt : ajout
		//$t = fopen($d.'f'.min($fm,$to)."-".max($fm,$to).'.txt', 'a+') or die(); fwrite($t,'['.$fm.']'.$ms); fclose($t);
		// ce message dans tchat/sonID.txt : ecrase precedent
		if (filemtime($d.$to.'.txt')>filemtime($d.$fm.'.txt')) // pas encore de reponse : deux messages de suite
			{
			$t=fopen($d.$to.'.txt', 'a+'); // envoi mon message en conservant le precedant
			fwrite($t,'['.$fm.']'.$ms);
			fclose($t);
			}
		else  // au moins une reponse
			{
			if (filesize($d.$fm.'.txt')>0)
				{
				$t=fopen($d.$fm.'.txt', 'r');$r=fread($t, filesize($d.$fm.'.txt')); fclose($t); // lecture de ma boite
				$t=fopen($d.$fm.'.txt', 'wb'); fwrite($t,'['.$to.']'); fclose($t); // vide ma boite
				}
			$t=fopen($d.$to.'.txt', 'wb'); // envoi mon message
			fwrite($t,'['.$fm.']'.$ms);
			fclose($t);
			}
		echo $r;
		clearstatcache();
		}
	else if ($tc=='cam')
		{
		// sauvegarde de l image de la webcam
		$im = str_replace(" ","+",strip_tags($_POST['image']));
		$im = substr($im, 1+strrpos($im, ','));
		if (strip_tags($_POST['id']))
			{
			file_put_contents($d."cam".strip_tags($_POST['id']).".jpg", base64_decode($im));
		//	file_put_contents($d."cam".strip_tags($_POST['id']).".txt", "data:image/jpeg;base64,".$im);
			clearstatcache();
			}
		}
	}
//else if ($_GET && $_GET['tchat']=='cam' && $_GET['id']) echo file_get_contents('web'.strip_tags($_GET['id']).'.txt');
else die;
//
?>