/*
* Rencontre
*/
var b6=0,b0='';
function f_edit(a2,a3,a4,a5){b6 = 0;
	document.forms["menu_profil"].elements["a1"].value="edit";
	document.forms["menu_profil"].elements["a2"].value=a2; // ID
	document.forms["menu_profil"].elements["a3"].value=a3; // 'c_categ', 'c_label', 't_valeur'
		// a4 on submit -  c_categ, c_label, (valeur concernée du JSON t_valeur pour select)('' pour checkbox) Format : fr=valeur&en=...& ***urlencode***
	document.forms["menu_profil"].elements["a5"].value=a5; // '', type (1 a 4), indice de la valeur concernée (select & checkbox)
		// a6 on submit (type)
	b0=decodeURIComponent((a4+'').replace(/\+/g, '%20'));
	a6=b0.substring(b0.search("=")+1,b0.search("&")); // valeur pour la langue principale
	e=document.getElementById("edit_profil");e.style.padding="25px";e.style.backgroundColor="green";e.innerHTML="";
	t=document.createElement("label");t.style.color="#000";t.style.fontSize="18px";t.style.marginRight="10px";t.innerHTML="Modifier la valeur : ";
	i=document.createElement("input");i.setAttribute('type','text');i.setAttribute('size','40');i.setAttribute('value',a6);i.style.marginRight="15px";
	s=document.createElement("input");s.setAttribute('type','button');s.setAttribute('value','Sauvegarde');s.setAttribute('onClick','f_Submit()'); // Appel fonction on submit
	e.appendChild(t);e.appendChild(i);
	if (a3=='c_label'){
		b6=1;
		i1=document.createElement("select");i1.setAttribute('name', 'b6');
		i2='<option value="1" '+((a5==1)?'selected':'')+'>TEXT</option>';
		i2+='<option value="2" '+((a5==2)?'selected':'')+'>TEXTAREA</option>';
		i2+='<option value="3" '+((a5==3)?'selected':'')+'>SELECT</option>';
		i2+='<option value="4" '+((a5==4)?'selected':'')+'>CHECKBOX</option>';
		i1.innerHTML+=i2;
		e.appendChild(i1);
	}
	e.appendChild(s);
	d=document.createElement("div");d.style.marginLeft="170px";
	a6=b0;
	while(a6.substring(a6.search("&")+1).length>1){
		a6=a6.substring(a6.search("&")+1);
		d0=document.createElement("label");d0.style.color="#222";d0.style.fontSize="14px";d0.style.marginRight="5px";d0.style.marginLeft="10px";d0.innerHTML=a6.substring(0,a6.search("="))+ ' :';
		d1=document.createElement("input");d1.setAttribute('type','text');d1.setAttribute('name',a6.substring(0,a6.search("=")));d1.setAttribute('size','25');d1.setAttribute('value',a6.substring(a6.search("=")+1,a6.search("&")));d1.style.marginRight="15px";
		d.appendChild(d0);d.appendChild(d1);
	}
	e.appendChild(d);
	window.scrollTo(0,0);
}
function f_supp(a2,a3,a4){
	if (confirm('Confirmer')){
		document.forms["menu_profil"].elements["a1"].value="supp";
		document.forms["menu_profil"].elements["a2"].value=a2;
		document.forms["menu_profil"].elements["a3"].value=a3;
		document.forms["menu_profil"].elements["a4"].value=a4;
		document.forms["menu_profil"].submit();
	}
}
function f_plus(a2,a3,a4,a5){
	b6=0;b0=a5;
	document.forms["menu_profil"].elements["a1"].value="plus";
	document.forms["menu_profil"].elements["a2"].value=a2; // ID
	document.forms["menu_profil"].elements["a3"].value=a3; // 'c_categ', 'c_label', 't_valeur'
		// a4 on submit
	document.forms["menu_profil"].elements["a5"].value=a5; // langues separees par & : fr&en
	e=document.getElementById("edit_profil");e.innerHTML="";e.style.padding="25px 10px";e.style.backgroundColor="green";
	t=document.createElement("label");t.style.color="#000";t.style.fontSize="18px";t.style.marginRight="5px";
	if (a3=='c_categ') t.innerHTML="Cat&eacute;gorie : ";else if (a3=='c_label') t.innerHTML="Valeur : ";else if (a3=='t_valeur') t.innerHTML="Nouvel item : ";
	i=document.createElement("input");i.setAttribute('type','text');i.setAttribute('size','20');i.setAttribute('value','');i.style.marginRight="15px";
	s=document.createElement("input");s.setAttribute('type','button');s.setAttribute('value','Ajoute');s.setAttribute('onClick','f_Submit()');
	e.appendChild(t);e.appendChild(i);e.appendChild(s);
	d=document.createElement("div");d.style.marginLeft="170px";
	a6=a5;
	while(a6.substring(a6.search("&")+1).length>1){
		a6=a6.substring(a6.search("&")+1);
		d0=document.createElement("label");d0.style.color="#222";d0.style.fontSize="14px";d0.style.marginRight="5px";d0.style.marginLeft="10px";d0.innerHTML=a6.substr(0,2)+ ' :';
		d1=document.createElement("input");d1.setAttribute('type','text');d1.setAttribute('name',a6.substr(0,2));d1.setAttribute('size','25');d1.setAttribute('value','');d1.style.marginRight="15px";
		d.appendChild(d0);d.appendChild(d1);
	}
	e.appendChild(d);
	window.scrollTo(0,0);
}
function f_synchronise(){
	document.forms["menu_profil"].elements["a1"].value="synchro";
	document.forms["menu_profil"].elements["a6"].value="1";
	document.forms["menu_profil"].submit();}
function f_Submit(){
	if(document.forms["menu_profil"].elements["a1"].value=="edit"){
		a6=b0.substring(0,b0.search("=")+1)+i.value+"&";
		while(b0.substring(b0.search("&")+1).length>1){
			b0=b0.substring(b0.search("&")+1);
			a6+=b0.substring(0,b0.search("=")+1)+document.getElementsByName(b0.substring(0,b0.search("=")))[0].value+"&";
		}
	}
	else if(document.forms["menu_profil"].elements["a1"].value=="plus"){
		a6=b0.substring(0,2)+"="+i.value+"&";
		while(b0.substring(b0.search("&")+1).length>1){
			b0=b0.substring(b0.search("&")+1);
			a6+=b0.substr(0,2)+"="+document.getElementsByName(b0.substr(0,2))[0].value+"&";
		}
	}
	a6=a6.replace(/"/g,"");
	document.forms["menu_profil"].elements["a4"].value=encodeURIComponent(a6);
	if (b6==1) document.forms["menu_profil"].elements["a6"].value=i1.value; // (edit c_label)
	document.forms["menu_profil"].submit();
}
function f_langplus(){
	document.forms["menu_profil"].elements["a1"].value="langplus";
	f=document.getElementsByName("langplus")[0].value;
	document.forms["menu_profil"].elements["a4"].value=f;
	if(/^[a-zA-Z]+$/.test(f))document.forms["menu_profil"].submit();
}
function f_langsupp(){
	document.forms["menu_profil"].elements["a1"].value="langsupp";
	f=document.getElementById("langsupp");
	document.forms["menu_profil"].elements["a4"].value=f.options[f.selectedIndex].value;
	if (confirm('Supprimer '+f.options[f.selectedIndex].value+' ?'))document.forms["menu_profil"].submit();
}
function f_vignette(f){
	ff="../wp-content/uploads/portrait/"+Math.floor((f)/10000)+"/"+f+"-grande.jpg?r="+Math.random();
	document.getElementById('portraitGrande').src=ff;
}
function f_vignette_change(f){
	f_vignette(f);
	document.getElementById('changePhoto').innerHTML='';
}
function f_supp_photo(f){
	document.getElementById('changePhoto').innerHTML = rencobjet.supp_photo+'<a href="javascript:void(0)" class="rencSupp" onClick="document.forms[\'portraitChange\'].elements[\'a1\'].value=\'suppImg\';document.forms[\'portraitChange\'].elements[\'a2\'].value=\''+f+'\';document.forms[\'portraitChange\'].elements[\'page\'].value=\'change\';document.forms[\'portraitChange\'].submit();" title="'+rencobjet.supp_la_photo+'">';
}
function f_plus_photo(f){
	document.getElementById('changePhoto').innerHTML = '<input type="file" name="plusPhoto" size="18"><br />'+rencobjet.ajouter_photo+'<a href="javascript:void(0)" class="rencPlus" onClick="document.forms[\'portraitChange\'].elements[\'a1\'].value=\'plusImg\';document.forms[\'portraitChange\'].elements[\'a2\'].value=\''+f+'\';document.forms[\'portraitChange\'].elements[\'page\'].value=\'change\';document.forms[\'portraitChange\'].submit();" title="'+rencobjet.ajouter_photo+'">';
}
function f_sauv_profil(f){
	document.forms['portraitChange'].elements['a1'].value='sauvProfil';
	document.forms['portraitChange'].elements['a2'].value=f;
	document.forms['portraitChange'].elements['page'].value='change';
	document.forms['portraitChange'].submit();
}
function f_onglet(f){
	document.getElementById('portraitTable'+last).style.display='none';
	document.getElementById('portraitTable'+f).style.display='table';
	document.getElementById('portraitOnglet'+last).style.backgroundColor='transparent';
	document.getElementById('portraitOnglet'+f).style.backgroundColor='#e5d4ac';last=f;
}
last=0;
function f_fin(f,g){
	if (confirm('Supprimer ce compte ?')){
		document.forms['listUser'].elements['a1'].value=f;
		document.forms['listUser'].elements['a2'].value=g;
		document.forms['listUser'].submit();
	}
}
function f_liberte(f){
	if (confirm('Libérer ce mail ?')){
		document.forms['listPrison'].elements['a1'].value=f;
		document.forms['listPrison'].submit();
	}
}
