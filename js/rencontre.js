/* Rencontre */
var emot=["",":-)",":-(",":-d",":-D",";;)","8-)",":-/",":-3",":-r",":-p",":-*",":-K",":-O",":-S",":-J","B-)"],veille,vue,scrute,moi='',toi='',img='',data,image=[],rs=1;
/* fonctions classiques */
function f_min(f,x,y,z){c=0;d=document.forms[x][y];e=document.forms[x][z];for(v=0;v<e.length;v++){if(d.options[v].value==f)c=v;if(e.options[v].value<f)e.options[v].disabled=true;else e.options[v].disabled=false;}if(f>e.options[e.selectedIndex].value)e.selectedIndex=c;}
function f_max(f,x,y,z){c=0;d=document.forms[x][z];e=document.forms[x][y];for(v=0;v<e.length;v++){if(d.options[v].value==f)c=v;if(e.options[v].value>f)e.options[v].disabled=true;else e.options[v].disabled=false;}if(f<e.options[e.selectedIndex].value)e.selectedIndex=c;}
function f_onglet(f){document.getElementById('portraitTable'+last).style.display='none';document.getElementById('portraitTable'+f).style.display='table';document.getElementById('portraitOnglet'+last).className='portraitOnglet';document.getElementById('portraitOnglet'+f).className='portraitOnglet rencTab';last=f;}last=0;
function f_vignette(f){ff="../wp-content/uploads/portrait/"+Math.floor((f)/10000)+"/"+f+"-grande.jpg?r="+Math.random();document.getElementById('portraitGrande').src=ff;}
function f_vignette_change(f){f_vignette(f);document.getElementById('changePhoto').innerHTML=''}
function f_supp_photo(f){document.getElementById('changePhoto').innerHTML=rencobjet.supp_photo+'&nbsp;<a href="javascript:void(0)" class="rencSupp" onClick="document.forms[\'portraitChange\'].elements[\'a1\'].value=\'suppImg\';document.forms[\'portraitChange\'].elements[\'a2\'].value=\''+f+'\';document.forms[\'portraitChange\'].elements[\'page\'].value=\'change\';document.forms[\'portraitChange\'].submit();" title="'+rencobjet.supp_la_photo+'">';}
function f_plus_photo(f){document.getElementById('changePhoto').innerHTML='<input type="file" name="plusPhoto" size="18"><br />'+rencobjet.ajouter_photo+'<a href="javascript:void(0)" class="rencPlus" onClick="document.forms[\'portraitChange\'].elements[\'a1\'].value=\'plusImg\';document.forms[\'portraitChange\'].elements[\'a2\'].value=\''+f+'\';document.forms[\'portraitChange\'].elements[\'page\'].value=\'change\';document.forms[\'portraitChange\'].submit();" title="'+rencobjet.ajouter_photo+'">';}
function f_sauv_profil(f){document.forms['portraitChange'].elements['a1'].value='sauvProfil';document.forms['portraitChange'].elements['a2'].value=f;document.forms['portraitChange'].elements['page'].value='change';document.forms['portraitChange'].submit();}
function f_fantome(){document.getElementById('rencFantome').style.display='none';document.cookie="rencfantome=oui";}
function f_mod_nouveau(f){a=0;b=document.forms['formNouveau'];if(b.elements['sex'].value=='') a++;if(b.elements['jour'].value=='') a++;if(b.elements['mois'].value=='') a++;if(b.elements['annee'].value=='') a++;if(b.elements['pays'].value=='') a++;if(b.elements['taille'].value=='') a++;if(b.elements['poids'].value=='') a++;if(b.elements['zsex'].value=='') a++;if(b.elements['zageMin'].value=='') a++;if(b.elements['zageMax'].value=='') a++;if(b.elements['zrelation'].value=='') a++;if(b.elements['ville'].value=='') a++;if(a==0){b.elements['nouveau'].value=f;b.submit();}else document.getElementById('rencAlert').innerHTML=a+'&nbsp;'+rencobjet.champs_incomplets;}
function f_fin(f){if (confirm(rencobjet.conf_supp_compte)) {document.forms['formFin'].elements['page'].value='fin';document.forms['formFin'].submit();}}
function f_trouve(){document.forms['formTrouve'].elements['page'].value='liste';document.forms['formTrouve'].submit();}
/* fonctions avec appel Ajax */
function f_region_select(f,g,x){jQuery(document).ready(function(){jQuery('#'+x).empty();jQuery.post(g,{'action':'regionBDD','pays':f},function(r){jQuery('#'+x).append(r);});});}
function f_ajax_sourire(f,g){jQuery(document).ready(function(){jQuery.post(g,{'action':'sourire','to':f},function(r){jQuery('#infoChange').append(r);window.setTimeout('document.getElementById("infoChange").innerHTML=""',3000);});});}
function f_voir_msg(f,g,h){jQuery(document).ready(function(){jQuery.post(g,{'action':'voirMsg','msg':f,'alias':h},function(r){jQuery('#rencMsg').empty();jQuery('#rencMsg').append(r.substring(0,r.length-1));});});}
function f_supp_msg(f,g,h){jQuery(document).ready(function(){jQuery.post(g,{'action':'suppMsg','msg':f,'alias':h},function(r){jQuery('#rencMsg').empty();jQuery('#rencMsg').append(r.substring(0,r.length-1));});});}
function f_boite_envoi(f,g){jQuery(document).ready(function(){jQuery.post(g,{'action':'boiteEnvoi','alias':f},function(r){jQuery('#rencMsg').empty();jQuery('#rencMsg').append(r.substring(0,r.length-1));});});}
function f_boite_reception(f,g){jQuery(document).ready(function(){jQuery.post(g,{'action':'boiteReception','alias':f},function(r){jQuery('#rencMsg').empty();jQuery('#rencMsg').append(r.substring(0,r.length-1));});});}
function f_nouveau(f,g){var a=0;var b=document.forms['formNouveau'];if(b.elements['pseudo'].value.length<3)a++;if(b.elements['pass1'].value.length<6) a++;if(b.elements['pass2'].value.length<6) a++;if(b.elements['sex'].value=='') a++;if(b.elements['jour'].value=='')a++;if(b.elements['mois'].value=='')a++;if(b.elements['annee'].value=='')a++;if(b.elements['pays'].value=='') a++;if(b.elements['taille'].value=='')a++;if(b.elements['poids'].value=='')a++;if(b.elements['zsex'].value=='')a++;if(b.elements['zageMin'].value=='') a++;if(b.elements['zageMax'].value=='')a++;if(b.elements['zrelation'].value=='')a++;if(b.elements['ville'].value=='')a++;jQuery(document).ready(function(){jQuery.post(g,{'action':'pseudo','name':b.elements['pseudo'].value},function(r){scroll(0,0);if(r.substring(0,r.length-1)!='ok'){document.getElementById('rencAlert').innerHTML=rencobjet.mauvais_pseudo;a=99;return;};if(a==0){if(b.elements['pass1'].value!=b.elements['pass2'].value)document.getElementById('rencAlert').innerHTML=rencobjet.nouv_pass_diff;else{document.getElementById('buttonPass').style.visibility="hidden";jQuery(document).ready(function(){jQuery.post(g,{'action':'iniPass','id':f,'nouv':b.elements['pass1'].value,'pseudo':b.elements['pseudo'].value},function(r){b.elements['pass1'].value='';b.elements['pass2'].value='';b.elements['nouveau'].value=f;b.submit();});});}}else if(a!=99)document.getElementById('rencAlert').innerHTML=a+rencobjet.champs_incomplets;});});}
function f_password(f0,f1,f2,f,g){document.getElementById('rencAlert1').innerHTML='';if(f1!=f2){document.getElementById('rencAlert1').innerHTML=rencobjet.nouv_pass_diff;window.setTimeout('document.getElementById("rencAlert1").innerHTML=""',3000);}else{document.getElementById('buttonPass').style.visibility="hidden";jQuery(document).ready(function(){jQuery.post(g,{'action':'testPass','id':f,'pass':f0,'nouv':f1},function(r){if(r.substring(0,r.length-1)=='ok'){d=document.forms['formPass'];d.elements['page'].value='password';d.elements['id'].value=f;d.submit();}else{document.getElementById('rencAlert1').innerHTML=rencobjet.pass_init_faux +r;window.setTimeout('document.getElementById("rencAlert1").innerHTML=""',3000);document.getElementById('buttonPass').style.visibility="visible";}});});}}
/* Tchat */
function f_bip(){at={"mp3":"audio/mpeg","mp4":"audio/mp4","ogg":"audio/ogg","wav":"audio/wav"};am=["bip.ogg","bip.mp3"];bip=document.createElement('audio');if (bip.canPlayType){for (i=0;i<am.length;i++){sl=document.createElement('source');sl.setAttribute('src','../wp-content/plugins/rencontre/js/'+am[i]);if (am[i].match(/\.(\w+)$/i))sl.setAttribute('type',at[RegExp.$1]);bip.appendChild(sl);}bip.load();bip.playclip=function(){bip.pause();bip.currentTime=0;bip.play();};return bip;}else return;};var cd=f_bip();
function f_emot(f){for(v=1;v<emot.length;v++){f=f.replace(emot[v],"<img src='../wp-content/plugins/rencontre/images/"+v+".gif' alt='' />");};return f;}
function f_tchat_veille(){jQuery(document).ready(function(){jQuery.post(rencobjet.ajaxchat,{'tchat':'tchatVeille','fm':rencobjet.mid},function(r,s){if(r){clearInterval(veille);f_tchat_dem(rencobjet.mid,r);}});});}
function f_tchat(f,t,g,p){a=document.getElementById('rencTchat');a.innerHTML="";b=document.createElement("div");b.className="top";b.innerHTML="Tchat";a.appendChild(b);b0=document.createElement("div");b0.className="cam";b0.title="webcam";b0.onclick=function(){if(moi!=''){moi='';f_camOff();}else webcam(f,t);};b1=document.createElement("span");b1.innerHTML="X";b1.onclick=function(){f_tchat_fin(f,t,g);};b.appendChild(b1);b.appendChild(b0);c=document.createElement("div");c.id="contenu";a.appendChild(c);d=document.createElement("div");d.className="emot";a.appendChild(d);i=document.createElement("input");i.value=rencobjet.ecrire_appuyer;i.id="inTchat";i.disabled=true;i.onfocus=function(){if(this.className!="actif"){this.className="actif";this.style.color="#222";this.value="";}};i.onkeypress=function(e){if (e.keyCode==13&&this.value){f_tchat_envoi(f,t,this.value,g);this.value="";}};a.appendChild(i);for (v=1;v<17;v++){d1=document.createElement("img");d1.src="../wp-content/plugins/rencontre/images/"+v+".gif";d1.alt=v;	d1.onclick=function(){a=document.getElementById('inTchat');if(a.className!="actif"){a.className="actif";a.style.color="#222";a.value="";} if(!a.disabled){a.value+=emot[this.alt];a.focus();}};d.appendChild(d1);};a.style.visibility="visible";clearInterval(veille);if(p==1){c2=document.createElement("div");c2.className="az";c2.innerHTML=rencobjet.tchat_attendre;c.appendChild(c2);f_tchat_debut(f,t,g)}else if(p==0){scrute=setInterval(function(){f_tchat_scrute(f,t,g);},2023);};}
function f_tchat_debut(f,t,g){jQuery(document).ready(function(){jQuery.post(g,{'tchat':'tchatDebut','fm':f,'to':t},function(r){scrute=setInterval(function(){f_tchat_scrute(f,t,g);},2023);});});}
function f_tchat_scrute(f,t,g){jQuery(document).ready(function(){if(document.getElementById('inTchat')){jQuery.post(g,{'tchat':'tchatScrute','fm':f,'to':t},function(r){if(r=='::'+f+'::')f_tchat_off();else if(r){if(document.getElementById('inTchat').disabled==true)f_tchat_on(); f_tchat_actualise("",r,f,t);}});}});}
function f_tchat_dem(f,t){a=document.getElementById('rencTchat');a.innerHTML="";b=document.createElement("div");b.className="top";b.innerHTML="Tchat";a.appendChild(b);b1=document.createElement("span");b1.innerHTML="X";b1.onclick=function(){f_tchat_fin(f,t,rencobjet.ajaxchat);};b.appendChild(b1);c=document.createElement("div");c.id="contenu";c.innerHTML=rencobjet.demande_tchat+'&nbsp;:&nbsp;';a.appendChild(c);jQuery(document).ready(function(){jQuery.post(rencobjet.wpajax,{'action':'miniPortrait2','id':t},function(r){c.innerHTML+=r.substring(0,r.length-1);c1=document.createElement("div");c1.className="button right";c1.innerHTML=rencobjet.ignorer;c1.onclick=function(){f_tchat_fin(f,t,rencobjet.ajaxchat);};c.appendChild(c1);c2=document.createElement("div");c2.className="button right";c2.innerHTML=rencobjet.accepter;c2.onclick=function(){f_tchat_ok(f,t,rencobjet.ajaxchat);};c.appendChild(c2);a.style.visibility="visible";});});}
function f_tchat_ok(f,t,g){jQuery(document).ready(function(){jQuery.post(g,{'tchat':'tchatOk','fm':f,'to':t},function(r){f_tchat(f,t,g,0);document.getElementById('inTchat').disabled=false;});});}
function f_tchat_on(){document.getElementById('inTchat').disabled=false;c2=document.createElement("div");c2.className="az";c2.innerHTML="Demande de Tchat accept&eacute;e. Vous pouvez commencer.";document.getElementById('contenu').appendChild(c2);}
function f_tchat_off(){c2=document.createElement("div");c2.className="az";c2.innerHTML=rencobjet.ferme_fenetre;document.getElementById('contenu').appendChild(c2);clearInterval(scrute);document.getElementById('inTchat').disabled=true;c.scrollTop=c.scrollHeight;}
function f_tchat_envoi(f,t,h,g){jQuery(document).ready(function(){jQuery.post(g,{'tchat':'tchatEnvoi','fm':f,'to':t,'msg':h},function(r){f_tchat_actualise(h,r,f,t);});});}
function f_tchat_actualise(h,r,f,t){u=navigator.userAgent.toLowerCase();sm=u.indexOf("android")>-1;c=document.getElementById('contenu');h=f_emot(h);r=f_emot(r);if(r){r1=r.split('['+t+']');if(r1!=null){for(v=0;v<r1.length;v++){if(r1[v].length>0 && r1[v]!="-"){c2=document.createElement("div");c2.className="to";c2.innerHTML=r1[v];if(sm)c.insertBefore(c2,c.firstChild);else c.appendChild(c2);cd.playclip();}}}};if(h){c1=document.createElement("div");c1.className="fm";c1.innerHTML=h;if(sm)c.insertBefore(c1,c.firstChild);else c.appendChild(c1);};if(!sm)c.scrollTop=c.scrollHeight;}
function f_tchat_fin(f,t,g){jQuery(document).ready(function(){clearInterval(scrute);jQuery.post(g,{'tchat':'tchatFin','fm':f,'to':t},function(r){veille=setInterval('f_tchat_veille();',5111);});});a=document.getElementById('rencTchat');a.innerHTML="";a.style.visibility="hidden";if(moi!=''){moi='';f_camOff();}}
/* Webcam */
function webcam(f,t){moi=f+"-"+t;toi=t+"-"+f;sw=rencobjet.ajaxchat.substr(0,rencobjet.ajaxchat.length-19)+"cam.swf";var cible=document.getElementById('rencCam2');cible.style.visibility="visible";var source='<object id="rencCamObj" type="application/x-shockwave-flash" data="'+sw+'" width="300" height="225"><param name="movie" value="'+sw+'" /><param name="allowScriptAccess" value="always" /></object>';cible.innerHTML=source;var run=3;stream_on();(_register=function(){var cam=document.getElementById('rencCamObj');if(cam&&cam.capture!==undefined){webcam.capture=function(x){return cam.capture(x);};webcam.turnOff=function(){return cam.turnOff();};webcam.onSave=function(x){saveData(x)};}else if (run==0) {cam.parentNode.removeChild(cam);cible.style.visibility="hidden";document.getElementById('rencCam').style.visibility="visible";}else{run--;window.setTimeout(_register, 1000);}})();}
function f_camOk(f) {so=document.getElementById('rencCamObj');de2=document.getElementById('rencCam2');so.width=160;so.height=120;de2.style.width="160px";de2.style.height="120px";de2.style.bottom="245px";document.getElementById('rencCam').style.visibility="visible";webcam.capture();}
function f_camOff(f) {so=document.getElementById('rencCamObj');de=document.getElementById('rencCam');de2=document.getElementById('rencCam2');ig=document.getElementById('rencCamImg');clearInterval(vue);if(so!=null)so.turnOff();de.removeChild(ig);de.style.visibility="hidden";de2.style.width="300px";de2.style.height="225px";de2.style.bottom="10px";de2.style.visibility="hidden";}
function saveData(data){if(rs==1){rs=0;s="tchat=cam&id="+moi+"&image="+data;a=new XMLHttpRequest();a.open("POST",rencobjet.ajaxchat,true);a.setRequestHeader('Content-Type',"application/x-www-form-urlencoded; charset=UTF-8");a.setRequestHeader("Content-length",s.length);a.onreadystatechange=function(){if(a.readyState==4)rs=1;};a.send(s);}}function stream_cam(){document.getElementById('rencCamImg').src='../wp-content/uploads/tchat/cam'+toi+'.jpg?'+new Date().getTime();}
function stream_on() {b2=document.createElement("img");b2.id="rencCamImg";b2.src="";document.getElementById('rencCam').appendChild(b2);vue=setInterval('stream_cam();',1000);}
//
window.setTimeout('document.getElementById("infoChange").innerHTML=""',3000);
jQuery(document).ready(function(){if(rencobjet.tchaton==1)veille=setInterval('f_tchat_veille();',5111);});

