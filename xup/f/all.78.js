var Render_Views={table:function(){
var T=this;
T._selected=[];
var _2=false;
var _3="";
T.get_selected_items=function(){
return T._selected;
};
var _4=function(_5){
return "<table width=\"100%\" height=\"100%\"><tr><td style=\"vertical-align: middle; text-align: center;\">"+_5+"</td></tr></table>";
};
T._tv_h=function(_6,ev){
switch(ev){
case "over":
I.im(_6.firstChild,"h","tv_lsep");
_6.className="h";
break;
case "out":
I.im(_6.firstChild,"","tv_lsep");
_6.className="";
break;
case "down":
I.im(_6.firstChild,"d","tv_lsep");
_6.className="d";
break;
}
};
T.is_tag=function(e,_9){
if(!e||(e.target||e.srcElement).nodeName.toLowerCase()!=_9.toLowerCase()){
return false;
}
return true;
};
T.is_inp=function(e){
return T.is_tag(e,"input");
};
var _b=false;
T.is_smpl_view=function(){
return _b;
};
T.draw=function(_c,_d){
var _e,_f;
var tmp=[];
var i=false;
var c=I.coords;
var w={"name":$("files").clientWidth-170,"size":50,"modified":100};
T.un_cl();
E.set_filtered_items(_c);
_e="<table width=\"100%\" cellspacing=0 cellpadding=0 border=0>"+"<tbody>";
for(var k in _c){
var i=_c[k];
var _k=(i.k?i.k:k);
var dbl=(i["type"]==0||i["type"]==2?"E.go2(E.path("+_k+"));":"E.edit_file_for_item("+_k+");");
var _17="R.cl(this,event);";
tmp[k]="<tr><td style=\"overflow: hidden;\"><div class=\"d16\"><span id=\"it"+_k+"\" class=\"item16\" onmousedown=\"if(!R.is_inp(event)){"+_17+";return false;}\" ondblclick=\"if(!R.is_inp(event)) {"+dbl+"; return false;};\" onmouseover=\"R.handle_over(event,this);\" onmouseout=\"R.handle_out(event,this);\"><img src=\"f/iconz/16-"+i["icon"]+".png\" width=16 height=16 border=0 class=\"i16\" align=\"absmiddle\">"+i["name"]+"</span></div></td><td>&nbsp;</td></tr>";
}
_f="</tbody></table>";
if(tmp.length==0){
_e=(_f="");
tmp=[_4((_3.length>0?"Result of filtering is empty.":"Directory is empty"))];
}
if(tmp.length>200&&!_d){
_b=true;
_e+="<tr><td><div align=\"center\" style=\"color: red; padding: 10px;\"><b>it make take a while to display all files in normal mode.<br><a href=\"#\" onclick=\"R.draw(E.get_filtered_items(),true);return false;\">click here to render in normal mode anyway</a></b></div><pre style=\"padding: 10px; padding-top: 0px;\">";
for(var k in _c){
var i=_c[k];
if(i["type"]==0||i["type"]==2){
tmp[k]="<a href=\"#\" onclick=\"E.go2(E.path("+(i.k?i.k:k)+"));\">"+i["name"]+"</a>/\n";
}else{
tmp[k]=i["name"]+"\n";
}
}
_f="</div></td></tr>"+_f;
}else{
_b=false;
}
$("files").innerHTML=_e+tmp.join("")+_f;
};
T.filter=function(s){
var i=E.get_global_items();
var n=[];
var l=s.length;
var j=-1;
_3=(s=s.toLowerCase());
if(l>0){
for(var k in i){
if(i[k]["name"]&&i[k]["name"].toLowerCase().indexOf(s)!=-1){
n[++j]=i[k];
n[j]["k"]=k;
}
}
T.draw(n);
}else{
T.draw(i);
}
};
T.is_selected=function(el){
for(var i=0;i<T._selected.length;i++){
if(T._selected[i]==el){
return i;
}
}
return false;
};
T.cl=function(el,e){
if(_b){
return false;
}
if(!e.ctrlKey){
T.un_cl();
}
var id=el.id.substr(2);
var i=E.get_global_items()[id];
i["id"]=id;
var num=T.is_selected(i);
if(e.ctrlKey&&num!==false){
T._selected.splice(num,1);
el.className="item16";
}else{
T._selected.push(i);
el.className="item16_h";
}
if(T._selected.length==0){
T.un_cl(true);
}else{
if(T._selected.length==1){
E.draw_menu_for_item(id);
}else{
E.draw_menu_for_items();
}
}
};
T.un_cl=function(_25){
if(_b){
return false;
}
if(_25){
L.draw(E.get_global_menu());
I.change_status(E.get_global_status());
}
for(var k=0;k<T._selected.length;k++){
$("it"+T._selected[k]["id"]).className="item16";
}
T._selected=[];
};
T.handle_over=function(e,obj){
if(_b){
return false;
}
_2=true;
};
T.handle_out=function(e,obj){
if(_b){
return false;
}
_2=false;
};
T.handle_down=function(e){
if(_b){
return false;
}
if(T.is_inp(e)||T.is_tag(e,"a")){
return true;
}
if(!_2){
T.un_cl(true);
}
return true;
};
}};
var Render=(R=new Render_Views["table"]());
var Engine=(E=new (function(){
var T=this;
var _2d=[];
var _2e=[];
var _2f=[];
var _30={};
var _31={"back":[],"fwd":[]};
var _up=false;
var _33,_34,_35,_36;
T.address=false;
T.copied=false;
T.op="copy";
T.get_global_items=function(){
return _2d;
};
T.get_filtered_items=function(){
return _2e;
};
T.set_filtered_items=function(_37){
_2e=_37;
};
T.get_global_menu=function(){
return _30;
};
T.get_global_status=function(){
return _2f;
};
var _38=false;
T.cancel_draw=function(){
if(!_38){
return;
}
clearTimeout(_38);
_38=false;
};
var _39=false;
T.go2=function(_3a,_3b){
D.qr("index.php?act=filelist",{DIR:_3a},function(res,err){
if(!res["error"]){
if(!_3b){
T.add_to_history(res["DIR"]);
}
_2d=res["items"];
_up=res["up"];
_30={0:"fsearch",1:"common",2:res["info"]};
if(!_3b||L._search_str==L._search_str_default){
L._search_str="";
}
R.filter(L._search_str);
L.draw(_30);
T.address=res["DIR"];
I.change_path(res["DIR"],res["dir"],res["type"]);
I.change_status(_2f=[["Objects",_2d.length],["Size",res["size"]],["Generation time",res["stats"]["seconds"]+"sec"]]);
I.disable_buttons();
_39=false;
if(err){
alert(err);
}
}else{
if(!_39){
_39=true;
alert("Could not change directory "+res["reason"]);
if(err){
alert(err);
}
if(!res["stop"]){
T.go2(res["dir"],true);
}
}
}
});
};
T.basename=function(_3e){
var p=_3e.split("/");
return p[p.length-1];
};
T.get_extension=function(_40){
var arr=_40.split(".");
if(!arr[1]){
return "";
}
for(var k in arr){
var ext=arr[k];
}
return ext;
};
T.path=function(k){
return _2d[k]["fullpath"];
};
T.draw_menu_for_item=function(_45){
if(_2d[_45]["type"]!=1&&_2d[_45]["type"]!=0){
return _33(_45);
}
T.cancel_draw();
_38=setTimeout(function(){
_33(_45);
},300);
};
T.draw_menu_for_items=function(){
L.draw({0:"operations",1:{name:"details",filename:"Selected:",selnum:R.get_selected_items().length}});
I.change_status([["Selected items",R.get_selected_items().length]]);
};
_33=function(_46,_47){
var i=_2d[_46];
if(i["type"]==1||i["type"]==0){
T.cancel_draw();
var dr=function(){
if(i["type"]==0){
_47["size"]=i["size"];
}
L.draw({0:"operations",1:_47});
I.change_status([["Name",_47["filename"]],["Type",_47["type"]],["Size",_47["size"]]]);
};
if(!_47){
D.qr("index.php?act=info",i,function(d,err){
_47=d;
dr();
});
}else{
dr();
}
}else{
if(i["type"]==2){
L.draw({0:"common",1:{name:"details",filename:i["name"],dir:false,type:i["descr"],free:i["free"],total:i["total"],fs:i["fs"]}});
}
}
};
T.delete_item=function(){
var i=R.get_selected_items()[0];
if(i["type"]!=0&&i["type"]!=1||!confirm("Do you really want to delete that "+(i["type"]==1?"file":"folder")+"?")){
return;
}
D.qr("index.php?act=delete",i,function(res,err){
if(res&&res["success"]){
T.refresh();
}else{
alert("The item "+i["name"]+" could not be deleted."+(res["reason"]||err));
}
},true,"deleting...");
};
T.delete_items=function(){
var _4f=R.get_selected_items();
for(var i=0;i<_4f.length;i++){
if(_4f[i]["type"]!=0&&_4f[i]["type"]!=1){
return;
}
}
if(!confirm("Do you really want to delete all "+_4f.length+" items?")){
return;
}
D.qr("index.php?act=delete",{"items":_4f},function(res,err){
if(res&&res["success"]){
T.refresh();
}else{
alert("Items could not be deleted."+(res["reason"]||err));
}
},true,"deleting...");
};
_36=function(el,i,e,_56){
var v=$("__vary");
e=e||window.event;
if(!v){
v=document.createElement("div");
v.className="norm";
v.style.visibility="hidden";
v.style.position="absolute";
v.style.whiteSpace="pre";
document.body.appendChild(v);
}
if(e&&e.keyCode==13||_56){
D.qr("index.php?act=rename",{"old":i,"new":el.value},function(res,err){
if(res["success"]){
_2d[i["id"]]=res["new"];
}else{
alert("The item "+res["f"]+" could not be renamed."+res["reason"]);
}
el.parentNode.removeChild(el);
R.draw(_2d);
R.cl($("it"+i["id"]),{});
});
el.onblur=function(){
};
return;
}
v.innerHTML=el.value;
el.style.width=(v.clientWidth-(-20))+"px";
};
T.rename_item=function(){
var i=R.get_selected_items()[0];
if(i["type"]!=0&&i["type"]!=1){
return;
}
var el=$("it"+i["id"]);
var nm=el.firstChild.nextSibling;
el.removeChild(nm);
var inp=document.createElement("input");
var s=function(e){
_36(inp,i,e);
};
var p={type:"text",value:i["name"],className:"norm rename_inp",onkeydown:s,onblur:function(){
_36(inp,i,null,true);
}};
for(var k in p){
inp[k]=p[k];
}
el.appendChild(inp);
s();
inp.select();
R.un_cl();
};
T.mkdir=function(){
var _62=prompt("Enter the new directory name:","NewFolder");
if(!_62){
return;
}
D.qr("index.php?act=mkdir",{name:_62},function(res,err){
if(res["success"]){
T.refresh();
}else{
alert("Could not create directory."+res["reason"]);
}
});
};
T.mkfile=function(){
var _65=prompt("Enter the new filename:","NewFile");
if(!_65){
return;
}
D.qr("index.php?act=mkfile",{name:_65,confirm:0},function(res,err){
if(res["exists"]){
if(confirm("The file already exists. Overwrite it?")){
D.qr("index.php?act=mkfile",{name:_65,confirm:1},function(r,e){
if(!r["success"]){
alert("Could not create file."+r["reason"]);
}else{
T.refresh();
}
});
}
return;
}
if(res["success"]){
T.refresh();
}else{
alert("Could not create file."+res["reason"]);
}
});
};
T.download_file=function(i){
var _6b;
if(typeof (i)==typeof (_6b)){
var i=R.get_selected_items()[0];
}
D.qr("index.php?act=download_get_href",i,function(res,err){
if(res){
window.location.href=res["href"];
}else{
alert("Could not get address to download file. This error cannot happen.");
}
},false,"downloading...");
};
T.copy_items=(T.copy_item=function(){
_34("copy");
});
T.cut_items=(T.cut_item=function(){
_34("cut");
});
_34=function(_6e){
D.qr("index.php?act="+_6e,{items:R.get_selected_items()},function(res,err){
if(!res){
alert("Could not "+_6e+" files.");
}else{
T.op=_6e;
T.copied=true;
R.un_cl(true);
}
});
};
T.paste_items=function(){
D.qr("index.php?act=paste",{},function(res,err){
if(!res){
alert(err);
}
T.copied=false;
T.refresh();
},true,T.op=="copy"?"copying...":"moving...");
};
T.cancel_advanced_paste=false;
T.advanced_paste=function(bt){
var _74=bt||"0 bytes";
D.qr("index.php?act=advanced_paste",{},function(res,err){
if(!res){
res={state:0};
}
if(res["state"]==0){
T.copied=false;
T.refresh();
}else{
if(!T.cancel_advanced_paste){
T.advanced_paste(res["bytes"]);
}else{
T.cancel_advanced_paste=false;
T.cancel_copy();
}
}
if(err){
alert(err);
}
},true,"Copying ("+_74+")... <b><u><a href=\"#\" onclick=\"E.cancel_advanced_paste=true;this.innerHTML='cancelling...';return false;\" style=\"color: green;\">cancel operation</a></u></b>");
};
T.cancel_copy=function(){
D.qr("index.php?act=cancel_copy",{},function(res,err){
T.copied=false;
T.refresh();
});
};
T.refresh=function(){
T.go2(T.address,true);
};
T.edit_file_for_item=function(k){
var _7a=_2d[k];
D.qr("index.php?act=info",_7a,function(res,err){
var img=res["thumb"]?true:false;
res["thumb"]=false;
_33(k,res);
if(res["size_bytes"]>=100*1024&&!img){
T.download_file(_7a);
}else{
try{
I.window_open("index.php?act=edit&file="+res["filename_encoded"]+(img?"&img=true":""),"edit"+res["md5(filename)"],640,480);
}
catch(e){
alert("Disable your popup blocker in order to edit files.");
}
}
},true,"opening...");
};
var _7e=false;
var _7f=false;
T.add_to_history=function(dir){
_31["back"].push(dir);
_31["fwd"]=[];
};
T.go_back=function(){
if(_31["back"].length<=1){
return false;
}
_31["fwd"].push(_31["back"].pop());
T.go2(_31["back"][_31["back"].length-1],true);
};
T.go_fwd=function(){
if(_31["fwd"].length==0){
return false;
}
var _81=_31["fwd"].pop();
_31["back"].push(_81);
T.go2(_81,true);
};
T.can_go_back=function(){
return _31["back"].length>1;
};
T.can_go_fwd=function(){
return _31["fwd"].length>0;
};
T.can_go_up=function(){
return _up!=false;
};
_35=function(){
var tmp="<table onclick=\"T.style.display='none'\"><tr><td>Back: ";
for(k in _31["back"]){
if(k!="copy"){
tmp+="<br>"+k+": "+_31["back"][k];
}
}
tmp+="</td><td>Fwd: ";
for(k in _31["fwd"]){
if(k!="copy"){
tmp+="<br>"+k+": "+_31["fwd"][k];
}
}
tmp+="</td></tr></table>";
I.dbg(tmp);
};
T.upload_files=function(){
D.qr("index.php?act=upload",{"form":$("upload_form"),"DIR":T.address},function(res,err){
setTimeout(function(){
I.show_upload();
T.refresh();
},100);
if(!res){
alert(err);
}
},true,"uploading...");
return true;
};
T.show_dir_size=function(_85){
var el=$("_dirsize");
var i,_88;
if(R.get_selected_items().length>0){
i=R.get_selected_items()[0];
_88=_2d[i["id"]]["fullpath"];
}else{
i=-1;
_88=T.address;
}
el.innerHTML="<i>loading, please wait...</i>";
D.qr("index.php?act=dirsize",{"file":_88,"nolimit":_85?"true":"false"},function(res,err){
if(res){
if(!_85&&res[0]=="&"){
res+=" <a href=\"javascript:E.show_dir_size(true);\" style=\"text-decoration: underline;\">recount w/o limits</a>";
}
el.innerHTML=res;
if(i!=-1){
_2d[i["id"]]["size"]=res;
}else{
_30[2]["size"]=res;
}
}else{
el.innerHTML="error: "+err;
}
});
};
T.chmod_item=function(){
var i=R.get_selected_items()[0];
D.qr("index.php?act=get_rights",i,function(res,err){
if(res){
var mod=prompt("Enter new file rights",res);
if(!mod){
return;
}
var _8f=i["type"]==0?confirm("CHMOD items recursively (chmod also subdirectories and files in subdirectories)?"):false;
D.qr("index.php?act=set_rights",{"fullpath":i["fullpath"],"mod":mod,"recursive":(_8f?"true":"false")},function(res,err){
if(err){
alert(err);
}else{
T.draw_menu_for_item(i.k||i.id);
}
});
}
});
};
T.chmod_items=function(){
var mod=prompt("Enter rights for items: ","777");
var _93=confirm("CHMOD items recursively (chmod also subdirectories and files in subdirectories)?");
if(!mod){
return;
}
D.qr("index.php?act=set_rights",{items:R.get_selected_items(),"mod":mod,"recursive":_93},function(res,err){
if(err){
alert(err);
}
E.refresh();
});
};
T.zip_items=(T.zip_item=function(){
D.qr("index.php?act=zip",{items:R.get_selected_items()},function(res,err){
if(err){
alert(err);
}
T.refresh();
});
});
T.unzip_item=function(_98){
var i=R.get_selected_items()[0];
D.qr("index.php?act=unzip",{"fullpath":i["fullpath"],"mode":_98},function(res,err){
if(err){
alert(err);
}
T.refresh();
});
};
T.run_update=function(){
D.qr("index.php?act=update",{},function(res,err){
if(!res){
if(confirm("Auto-update failed.\nDo you want to use advanced way to update Dolphin.php (version will be changed to light)?")){
window.location="index.php?version=light&DIR=.&act=download-new";
}
}else{
alert("Update successful!");
window.location.reload();
}
});
};
T.open_terminal=function(){
I.window_open("index.php?act=terminal","terminal",700,500);
};
})());
LeftMenu=(L=new (function(){
var T=this;
var _9f={};
var _a0={};
var _a1,_a2;
T.draw=function(_a3){
var i=0;
var tmp="",_a6="",_a7="",up="",_a9="";
_9f={};
for(var k in _a3){
i++;
if(!_a3[k]["name"]){
_a3[k]={name:_a3[k]};
}
_9f[i]=_a3[k]["name"];
var p=_a3[k];
switch(p["name"]){
default:
case "common":
_a6="Common";
_a7="";
if(E.copied){
_a7+=_a1("javascript:E.paste_items();","Paste items here","paste","Paste");
_a7+=_a1("javascript:E.cancel_copy();","Cancel "+E.op,"cancel","Cancel "+E.op);
}
_a7+=_a1("javascript:E.mkfile();","Create a file","mkdir","Create a file");
_a7+=_a1("javascript:E.mkdir();","Create a folder","mkdir","Create a directory");
_a7+=_a1("javascript:E.open_terminal();","Open terminal window to execute shell commands","rename","Open terminal");
_a7+=_a1("javascript:I.show_upload();","Upload files","upload","Upload files");
_a7+="<form enctype=\"multipart/form-data\" style=\"display:none; margin: 0px; padding: 0px;\" id=\"upload_form\"><div id=\"uploads_container\"></div><div align=\"right\"><a href=\"javascript:I._append_upload();\" style=\"text-decoration: underline;\">add more files...</a></div><input type=\"button\" style=\"font-size: 10px; width: 50px;\" onclick=\"E.upload_files();\" value=\"upload\" /></form>";
break;
case "fsearch":
_a6="Filename filter";
T._search_str_default="Enter part of filename...";
if(!T._search_str){
T._search_str=T._search_str_default;
}
_a7="<input type=text name=\"fsearch\" id=\"fsearch\" class=\"fsearch_g\" onkeyup=\"L._search_str=this.value;R.filter(this.value);\" onfocus=\"if(this.value=='"+T._search_str_default+"') this.value='';this.className='fsearch'\" onblur=\"this.className='fsearch_g';if(this.value=='') this.value='"+T._search_str_default+"';\" value=\""+T._search_str+"\">";
break;
case "operations":
var s=R._selected;
_a6="Tasks for files and folders";
if(!s[1]){
s=s[0];
if(s["type"]==1){
_a7=_a1("javascript:E.rename_item();","Set another name to current file","rename","Rename file");
_a7+=_a1("javascript:E.cut_item();","Move file to another place","cut","Cut file");
_a7+=_a1("javascript:E.copy_item();","Make a copy of file","copy","Copy file");
_a7+=_a1("javascript:E.download_file();","Download the selected file to your computer","upload","Download file");
_a7+=_a1("javascript:E.delete_item();","Remove the file from computer","delete","Delete file");
if(E.get_extension(s["fullpath"])=="zip"){
_a7+=_a1("javascript:E.unzip_item(&quot;extract_here&quot;);","Extract contents here","zip","Extract here");
var lon=E.basename(s["fullpath"]);
lon=lon.substr(0,lon.length-4);
var _ae=lon.length>12?lon.substr(0,9)+"...":lon;
_a7+=_a1("javascript:E.unzip_item(&quot;extract&quot;);","Extract to &quot;"+lon+"/&quot;","zip","Extract to &quot;"+_ae+"/&quot;");
}else{
_a7+=_a1("javascript:E.zip_item();","Add file to zip","zip","Add to zip");
}
_a7+=_a1("javascript:E.chmod_item();","Change rights of file","admin","CHMOD file");
}else{
_a7=_a1("javascript:E.rename_item();","Set another name to current directory","rename","Rename folder");
_a7+=_a1("javascript:E.cut_item();","Move directory to another place","cut","Cut folder");
_a7+=_a1("javascript:E.copy_item();","Make a copy of directory","copy","Copy folder");
_a7+=_a1("javascript:E.delete_item();","Remove the directory from computer","delete","Delete folder");
_a7+=_a1("javascript:E.zip_item();","Add directory to zip","zip","Add to zip");
_a7+=_a1("javascript:E.chmod_item();","Change rights of directory","admin","CHMOD dir");
}
}else{
_a7+=_a1("javascript:E.cut_items();","Move items to another place","cut","Cut items");
_a7+=_a1("javascript:E.copy_items();","Make copy of items","copy","Copy items");
_a7+=_a1("javascript:E.delete_items();","Remove the items from computer","delete","Delete items");
_a7+=_a1("javascript:E.zip_items();","Add items to zip","zip","Add to zip");
_a7+=_a1("javascript:E.chmod_items();","Change rights of items","admin","CHMOD items");
}
break;
case "details":
_a6="Details";
if(p["thumb"]){
_a7=p["thumb"];
}else{
_a7="";
}
_a7+="<b style=\""+(document.all&&!window.opera?"width: 170px;":"")+"overflow: hidden; display: block;\">"+p["filename"]+"</b>";
if(p["dir"]){
p["type"]="Directory";
}
if(p["type"]){
_a7+=p["type"]+"<br><br>";
}else{
_a7+="<br>";
}
if(p["selnum"]){
_a7+=p["selnum"]+" items<br><br>";
}
if(p["id3"]){
_a7+=p["id3"]+"<br><br>";
}
if(p["fs"]){
_a7+="Filesystem: "+p["fs"]+"<br><br>";
}
if(p["free"]){
_a7+="Free disk space: "+p["free"]+"<br><br>";
}
if(p["total"]){
_a7+="Total disk space: "+p["total"]+"<br><br>";
}
if(p["changed"]){
_a7+="Changed: "+p["changed"]+"<br><br>";
}
if(p["owner"]){
_a7+="Owner: "+p["owner"]+"<br><br>";
}
if(p["group"]){
_a7+="Group: "+p["group"]+"<br><br>";
}
if(p["rights"]){
_a7+="Rights: "+p["rights"]+"<br><br>";
}
if(p["size"]){
_a7+="Size: <span id=\"_dirsize\">"+p["size"]+"</span><br><br>";
}else{
if(p["dir"]){
_a7+="Size: <span id=\"_dirsize\"><a href=\"javascript:E.show_dir_size(false);\" style=\"text-decoration: underline;\">click to show size</a></span>"+"<br><br>";
}
}
_a7=_a7.substr(0,_a7.length-4);
if(_a7.substr(_a7.length,_a7.length-4)=="<br>"){
_a7=_a7.substr(0,_a7.length-4);
}
break;
case "long text":
_a6="phylosophy";
_a7="long text should be here";
break;
}
var up=_a0[p["name"]]?"l_darr":"l_uarr";
var _af=up=="l_uarr"?"":" style=\"display: none;\"";
tmp+="<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=0>\t\t<tr height=12><td colspan=4><img src=\"f/i/no.png\" width=1 height=1></td></tr> <!--spacer-->\t\t<tr height=23 id=\"h"+i+"\" style=\"color: #3f3d3d;\">\t<td width=12><img src=\"f/i/no.png\" width=1 height=1></td><td class=\"left_menu_head\" onmouseover=\"L._highlight("+i+",'over');\" onmouseout=\"L._highlight("+i+",'out');\" onclick=\"L._hide("+i+");\">"+_a6+"</td><td width=23 onmouseover=\"L._highlight("+i+",'over');\" onmouseout=\"L._highlight("+i+",'out');\" onclick=\"L._hide("+i+");\"><img src=\"f/i/no.png\" width=23 height=23 id=\"i"+i+"\" style=\"background: url('f/i/overall.78.png'); background-position: -"+I.coords[up][0]+"px -"+I.coords[up][1]+"px;\"></td><td width=12><img src=\"f/i/no.png\" width=12 height=1></td>\t</tr>\t\t</table><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" id=\"b"+i+"\" border=0"+_af+">\t<tr>\t<td width=12><img src=\"f/i/no.png\" width=12 height=1></td><td colspan=2 class=\"left_menu_body\">"+_a7+"</td><td width=12><img src=\"f/i/no.png\" width=12 height=1></td>\t</tr>\t</table>";
}
tmp+="<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=0>\t\t<tr height=12><td colspan=4><img src=\"f/i/no.png\" width=1 height=1></td></tr> <!--spacer-->\t\t</table>";
$("left_menu_div").innerHTML=tmp;
};
T._highlight=function(id,act){
var el=$("i"+id);
var obj=$("h"+id);
var _b4=act=="over"?"h":"";
if(_a0[_9f[id]]){
I.im(el,_b4,"l_darr");
}else{
I.im(el,_b4,"l_uarr");
}
if(act=="over"){
obj.style.color="#7e7c7c";
obj.style.cursor="pointer";
}else{
obj.style.color="#3f3d3d";
obj.style.cursor="default";
}
};
T._hide=function(id){
var el=$("b"+id);
var img=$("i"+id);
var _b8=_9f[id];
if(el.style.display!="none"){
_a2(el,0.3,false);
setTimeout(function(){
el.style.display="none";
},350);
I.im(img,"h","l_darr");
_a0[_b8]=_b8;
}else{
el.style.visibility="hidden";
el.style.display="";
_a2(el,0.3,true);
I.im(img,"h","l_uarr");
_a0[_b8]=null;
}
};
var _i=0;
_a1=function(_ba,_bb,_bc,_bd){
_i++;
var _be="background: url('f/i/menu_all.png') -"+I.coords["m_"+_bc][0]+"px -"+I.coords["m_"+_bc][1]+"px";
return "<div style=\"padding-top: 2px; padding-bottom: 2px;\"><a href=\""+_ba+"\" title=\""+_bb+"\" onmouseover=\"L._underl("+_i+",true);\" onmouseout=\"L._underl("+_i+",false);\"><img src=\"f/i/no.png\" width=16 height=16 style=\""+_be+"\" align=absmiddle border=0>&nbsp;&nbsp;<span id='u"+_i+"'>"+_bd+"</span></a></div>";
};
T._underl=function(id,_c0){
var el=$("u"+id);
if(_c0){
el.style.textDecoration="underline";
}else{
el.style.textDecoration="none";
}
};
_a2=function(el,_c3,_c4){
if(!_c3){
var _c3=0.3;
}
if(_c4==undefined){
var _c4=true;
}
if(el.runtimeStyle){
if(el.style.position!="absolute"&&!el.style.width&&!el.style.height){
el.style.width=el.offsetWidth;
el.style.height=el.offsetHeight;
}
el.runtimeStyle.filter="BlendTrans(Duration="+_c3+")";
if(_c4){
el.style.visibility="hidden";
}else{
el.style.visibility="visible";
}
el.filters["BlendTrans"].Apply();
if(!_c4){
el.style.visibility="hidden";
}else{
el.style.visibility="visible";
}
el.filters["BlendTrans"].Play();
return true;
}
if(el.style.opacity!=undefined){
var bit=-1/(_c3*40);
if(!_c4){
bit=-bit;
}
el.style.opacity=_c4?0:1;
el.style.visibility="visible";
var op=function(){
if((el.style.opacity>=1&&_c4)||(el.style.opacity<=0&&!_c4)){
return;
}
el.style.opacity-=bit;
setTimeout(op,25);
};
op();
return true;
}
return false;
};
})());
Interface=(I=new (function(){
var T=this;
T.coords={back:[0,0,82,30],back_disabled:[0,582,82,30],fwd:[0,90,47,30],fwd_disabled:[0,612,47,30],up:[48,90,29,30],up_disabled:[71,612,29,30],search:[0,180,66,30],dirs:[0,270,65,30],view:[0,360,37,30],close:[72,360,28,30],go:[0,450,85,22],l_uarr:[77,90,23,23],l_darr:[77,136,23,23],tv_sep:[64,360,8,20],tv_lsep:[94,450,6,20],tv_uarr:[68,439,9,5],tv_darr:[63,445,9,5],m_open:[0,0,16,16],m_mkdir:[0,16,16,16],m_upload:[0,32,16,16],m_rename:[0,48,16,16],m_cut:[0,64,16,16],m_copy:[0,80,16,16],m_delete:[0,96,16,16],m_control_panel:[0,112,16,16],m_admin:[0,128,16,16],m_paste:[0,160,16,16],m_cancel:[0,176,16,16],m_zip:[0,192,16,16]};
T.dbg=function(_c8){
var el=$("debug");
if(!el){
el=document.createElement("div");
el.id="debug";
document.body.appendChild(el);
}
el.innerHTML=_c8;
};
T.get_width=function(){
if(document.body.offsetWidth){
return document.body.offsetWidth;
}else{
if(window.innerWidth){
return window.innerWidth;
}else{
return false;
}
}
};
T.get_height=function(){
if(document.body.offsetHeight){
return document.body.offsetHeight;
}else{
if(window.innerHeight){
return window.innerHeight;
}else{
return false;
}
}
};
T.get_bounds=function(_ca){
var _cb=_ca.offsetLeft;
var top=_ca.offsetTop;
for(var _cd=_ca.offsetParent;_cd;_cd=_cd.offsetParent){
_cb+=_cd.offsetLeft;
top+=_cd.offsetTop;
}
return {left:_cb,top:top,width:_ca.offsetWidth,height:_ca.offsetHeight};
};
T.menu_hover=function(){
};
T.menu_out=function(){
};
T.change_path=function(_ce,dir,_d0){
var a=$("address_img");
var h=$("header_icon");
if(_d0==0){
a.style.backgroundPosition="-0px -516px";
h.style.backgroundPosition="-37px -360px";
}else{
if(_d0==2){
a.style.backgroundPosition="-0px -538px";
h.style.backgroundPosition="-37px -390px";
}else{
if(_d0==3){
a.style.backgroundPosition="-0px -560px";
h.style.backgroundPosition="-37px -420px";
}
}
}
$("name_of_folder").innerHTML=dir;
$("address").value=_ce;
};
T.change_address=function(_d3){
var p=$("address").value;
if(_d3){
p=E.address+"/"+_d3;
}
if(p==E.address){
E.refresh();
}else{
E.go2(p);
}
};
T.change_status=function(pr){
var el=$("status_str");
var tmp=[];
var j=0;
for(var k in pr){
var p=pr[k];
if(!p[1]){
continue;
}
tmp[j++]=p[0]+": "+p[1];
}
el.innerHTML=tmp.join("&nbsp;<img src=\"f/i/no.png\" width=8 height=27 border=0 style=\"padding: 0px; margin: 0px; background: url('f/i/overall.78.png'); background-position: -85px -516px;\" align=\"absmiddle\">&nbsp;");
};
T.im=function(obj,_dc,_dd){
if(!_dd){
var _dd=obj.id.substr(4);
}
var c=T.coords[_dd];
var _df=0;
if(_dc=="h"){
_df=-c[3];
}
if(_dc=="d"){
_df=-c[3]*2;
}
obj.style.backgroundPosition="-"+c[0]+"px -"+(c[1]-(obj.id.substr(obj.id.length-9,9)=="_disabled"?0:_df))+"px";
};
var _e0=function(msg,_e2){
return "<table"+(_e2?"":" width=\"100%\"")+" height=\"100%\"><tr><td style=\"vertical-align: middle; text-align: center;\">"+msg+"</td></tr></table>";
};
T.generate_panel=function(){
var el=$("panel");
var tmp="";
var _e5=T.coords;
var act={back:"E.go_back();",back_disabled:"return false;",fwd:"E.go_fwd();",fwd_disabled:"return false;",up:"I.change_address('..');",up_disabled:"return false;"};
var _e7={back:"Back",back_disabled:"Back",fwd:"Forward",fwd_disabled:"Forward",up:"Up",up_disabled:"Up",search:"Search",dirs:"Folders",view:"View"};
for(var k in act){
tmp+="<img id=\"btn_"+k+"\" src=\"f/i/no.png\" onmouseover=\"I.im(this,'h');\" onmouseout=\"I.im(this,'');\" onmousedown=\"I.im(this,'d');\" onmouseup=\"I.im(this,'h'); "+act[k]+" \" alt=\""+_e7[k]+"\" title=\""+_e7[k]+"\" style=\"background: url('f/i/overall.78.png'); background-position: -"+_e5[k][0]+"px -"+_e5[k][1]+"px;"+(k.substr(k.length-9,9)=="_disabled"?"display: none;":"")+"\" width=\""+_e5[k][2]+"\" height=\""+_e5[k][3]+"\" />";
}
el.innerHTML=tmp;
var el=$("upperpanel");
var _e9={Update:"Update&nbsp;to&nbsp;latest&nbsp;development&nbsp;version"};
tmp="<table width=\"100%\" cellspacing=0 cellpadding=0 border=0><tr height=2><td colspan=6></td></tr><tr height=18 class=\"menu\">";
for(var k in _e9){
tmp+="<td height=18 valign=middle onmouseover=\"this.className='menuelm_hover'\" onmouseout=\"this.className='';\" onmousedown=\"I.upperpanel('"+k+"',event,this);\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+_e9[k]+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
}
el.innerHTML=tmp+"<td width=\"100%\">&nbsp;</td></tr><tr height=4><td colspan=6></td></tr></table>";
};
T.upperpanel=function(_ea,e,obj){
var x=10,y=10;
if(_ea!="Update"){
var _ef=T.get_bounds(obj);
var el=$("debug");
if(!el){
return;
}
el.style.position="absolute";
el.style.top=(parseInt(_ef["top"])+parseInt(_ef["height"]))+"px";
el.style.left=_ef["left"]+"px";
el.innerHTML="menu";
}else{
if(!confirm("Check for newer version?")){
return;
}
D.qr("http://dolphin-php.org/"+"build-info/",{},function(res,err){
if(!res){
alert("Could not contact http://dolphin-php.org/.");
}else{
if(res==78){
alert("No new version available");
}else{
if(res<78){
alert("You have a newer version, than on a server :).");
}else{
if(confirm("New version ("+res+" build) is available.\nInstall it?")){
E.run_update();
}
}
}
}
});
}
};
T.resize=function(){
var ids={files:"main",left_menu_div:"left"};
var _f4=221,min=200;
if(!T.initval){
T.initval={"main":Math.max(T.get_height()-_f4,min),"left":Math.max(T.get_height()-_f4,min),"h":T.get_height()};
for(var k in ids){
$(k).style.height=T.initval[ids[k]]+"px";
}
}else{
var off=(T.initval.h-T.get_height());
$("files").style.height=($("main").style.height=Math.max(T.initval.main-off,min)+"px");
$("left_menu_div").style.height=($("left").style.height=Math.max(T.initval.left-off,min)+"px");
}
};
T.disable_buttons=function(){
for(var k in {fwd:"",back:"",up:""}){
if(!E["can_go_"+k]()){
$("btn_"+k).style.display="none";
$("btn_"+k+"_disabled").style.display="";
}else{
$("btn_"+k).style.display="";
$("btn_"+k+"_disabled").style.display="none";
}
}
};
T.show_upload=function(){
var el=$("upload_form");
if(el.style.display=="none"){
el.style.display="";
T._append_upload();
}else{
T._clear_uploads();
el.style.display="none";
}
};
var _fa=[];
T._append_upload=function(){
if(!T.i){
T.i=0;
}
var el=$("uploads_container");
var obj=document.createElement("input");
obj.type="file";
obj.className="upl";
obj.name="files["+(T.i++)+"]";
el.appendChild(obj);
_fa.push(obj);
obj=document.createElement("br");
el.appendChild(obj);
_fa.push(obj);
};
T._clear_uploads=function(){
var el=$("uploads_container");
for(var i=0;i<_fa.length;i++){
el.removeChild(_fa[i]);
}
_fa=[];
};
T.show_loading=function(_ff,text){
var d=$("loading");
var s=text||"loading...";
if(_ff==true&&s.indexOf("...")!=-1&&!$("loading_dots")){
s=s.replace("...","<span id=\"loading_dots\">...</span>");
interv=setInterval(function(){
var d=$("loading_dots");
if(!d){
return;
}
if(!this.cnt){
this.cnt=2;
}
if(cnt==1){
d.innerHTML="...";
}
if(cnt==2){
d.innerHTML="&nbsp;..";
}
if(cnt==3){
d.innerHTML=".&nbsp;.";
}
if(cnt==4){
d.innerHTML="..&nbsp;";
}
cnt++;
if(cnt>4){
cnt=1;
}
},600);
}else{
if(!_ff&&interv){
clearInterval(interv);
interv=null;
}
}
if(_ff){
s+=" <a href=\"#\" onclick=\"D.abort();return false;\" style=\"color: green;\"><b><u>abort</u></b></a>";
}
if(_ff==true){
d.innerHTML=s;
d.style.visibility="visible";
}else{
d.style.visibility="hidden";
d.innerHTML=s;
}
};
T.window_open=function(src,name,_106,_107){
return window.open(src,name,"width="+_106+",height= "+_107+",resizeable=0,menubar=0,location=0,scrollbars=1,toolbar=0,status=0,top="+(screen.height/2-_107/2)+",left="+(screen.width/2-_106/2));
};
T.handle_keydown=function(e){
var sel=R.get_selected_items();
var filt=E.get_filtered_items();
var _10b=E.get_filtered_items();
var t=e.srcElement||e.target;
if(R.is_smpl_view()||filt.length==0){
return true;
}
if(R.is_inp(e)){
if(e.keyCode!=38&&e.keyCode!=40){
return true;
}else{
t.blur(e);
}
}
switch(e.keyCode||e.charCode){
case 46:
if(sel.length>=1){
E["delete_item"+(sel.length>1?"s":"")]();
return false;
}else{
return true;
}
break;
case 113:
if(sel.length==1){
E.rename_item();
return false;
}else{
return true;
}
break;
case 38:
case 40:
if(filt.length!=E.get_global_items().length){
return false;
}
var id=sel[sel.length-1]?sel[sel.length-1].id:(filt[0]&&filt[0].k?filt[0].k:0);
var _10e=e.keyCode==38?1:-1;
var el=$("it"+id);
var _110=+id;
if(_10e==1){
if(id>0&&!$("it"+(id-1))){
var prev=id;
for(var k in filt){
if(filt[k]["k"]==id){
break;
}
prev=filt[k]["k"];
}
id=prev;
}else{
if(id>0){
id-=_10e;
}
}
}else{
if(id<_10b.length-1&&!$("it"+(id-(-1)))){
var _113=false,t=null;
for(var k in filt){
if(_113){
id=filt[k]["k"];
break;
}
if(filt[k]["k"]==id){
_113=true;
}
}
}else{
if(id<E.get_global_items().length-1){
id-=_10e;
}
}
}
if(id==_110){
return;
}
if(R.is_selected(_10b[id])||sel.length==2&&sel[0]==_10b[id]){
id=_110;
}
$("it"+id).onmousedown(e);
return false;
break;
case 13:
var el;
if(sel.length==1&&(el=$("it"+sel[0]["id"]))){
el.ondblclick(e);
}
return false;
break;
case 8:
T.change_address("..");
return false;
break;
case 67:
case 99:
if(!e.ctrlKey||sel.length==0){
break;
}
E.copy_item();
break;
case 88:
case 120:
if(!e.ctrlKey||sel.length==0){
break;
}
E.cut_item();
break;
case 86:
case 118:
if(!e.ctrlKey){
break;
}
E.paste_items();
break;
}
return true;
};
})());
function $(id){
return document.getElementById(id);
}
Dolphin=(D=new (function(){
var T=this;
var req=false;
T.abort=function(){
req.abort();
E.cancel_draw();
I.show_loading(false);
};
T.qr=function(addr,data,_119,_11a,text){
var _11c;
if(typeof (_11a)==typeof (_11c)){
var _11a=true;
}
I.show_loading(true,text);
E.cancel_draw();
var beg=(new Date()).getTime();
var r=new JsHttpRequest();
req=r;
r.onerror=function(msg){
I.show_loading(false,text);
if(msg.length>100){
msg=msg.substr(0,100)+"...";
}
if(r.status){
switch(r.status){
case 500:
msg="Internal server error";
break;
case 503:
case 502:
msg="The server is temporarily busy";
break;
case 404:
alert("AJAX request failed because of 404 error (Not Found). Please ensure, that Dolphin.php is installed properly.");
return false;
case 403:
alert("AJAX request failed because of 403 error (Permission denied). Please ensure, that you have set correct rights to PHP files.");
return false;
}
}
if(confirm("AJAX subrequest failed.\nThe technical reason: "+msg+"\n\nDo you want to send that request again?")){
T.qr(addr,data,_119,_11a);
}
};
r.onreadystatechange=function(){
if(r.readyState==4){
var time=Math.round(((new Date()).getTime()-beg)*1000)/1000000;
I.show_loading(false,text);
if(r.responseText!="--error-login-required"){
try{
_119(r.responseJS,r.responseText);
}
catch(e){
}
}else{
if(confirm("Session has expired, relogin required.\nDo you want to relogin now?")){
T.qr("index.php",{login:prompt("login:"),pass:prompt("password:"),"DIR":Engine.address},function(res,err){
T.qr(addr,data,_119,_11a);
});
}
}
var _123=Math.round(((new Date()).getTime()-beg)*1000)/1000000;
}
};
r.caching=!_11a;
r.open(null,addr,true);
r.send(data);
};
T.init=function(){
I.generate_panel();
T.resize();
if(interv){
clearInterval(interv);
interv=null;
}
$("loading").style.visibility="hidden";
$("very_main").style.visibility="visible";
I.change_address();
};
T.resize=function(){
I.resize();
};
var _124=false;
T.pingpong=function(){
T.qr("index.php?act=ping","ping",function(res,err){
if(res!="pong"&&!_124){
alert("PING-PONG request to server failed. Please check your internet connection.");
_124=true;
}else{
if(res=="pong"){
_124=false;
}
}
});
};
})());
function JsHttpRequest(){
var t=this;
t.onreadystatechange=null;
t.readyState=0;
t.responseText=null;
t.responseXML=null;
t.status=200;
t.statusText="OK";
t.responseJS=null;
t.caching=false;
t.loader=null;
t.session_name="PHPSESSID";
t._ldObj=null;
t._reqHeaders=[];
t._openArgs=null;
t._errors={inv_form_el:"Invalid FORM element detected: name=%, tag=%",must_be_single_el:"If used, <form> must be a single HTML element in the list.",js_invalid:"JavaScript code generated by backend is invalid!\n%",url_too_long:"Cannot use so long query with GET request (URL is larger than % bytes)",unk_loader:"Unknown loader: %",no_loaders:"No loaders registered at all, please check JsHttpRequest.LOADERS array",no_loader_matched:"Cannot find a loader which may process the request. Notices are:\n%",no_headers:"Method setRequestHeader() cannot work together with the % loader."};
t.abort=function(){
with(this){
if(_ldObj&&_ldObj.abort){
_ldObj.abort();
}
_cleanup();
if(readyState==0){
return;
}
if(readyState==1&&!_ldObj){
readyState=0;
return;
}
_changeReadyState(4,true);
}
};
t.open=function(_128,url,_12a,_12b,_12c){
with(this){
try{
if(document.location.search.match(new RegExp("[&?]"+session_name+"=([^&?]*)"))||document.cookie.match(new RegExp("(?:;|^)\\s*"+session_name+"=([^;]*)"))){
url+=(url.indexOf("?")>=0?"&":"?")+session_name+"="+this.escape(RegExp.$1);
}
}
catch(e){
}
_openArgs={method:(_128||"").toUpperCase(),url:url,asyncFlag:_12a,username:_12b!=null?_12b:"",password:_12c!=null?_12c:""};
_ldObj=null;
_changeReadyState(1,true);
return true;
}
};
t.send=function(_12d){
if(!this.readyState){
return;
}
this._changeReadyState(1,true);
this._ldObj=null;
var _12e=[];
var _12f=[];
if(!this._hash2query(_12d,null,_12e,_12f)){
return;
}
var hash=null;
if(this.caching&&!_12f.length){
hash=this._openArgs.username+":"+this._openArgs.password+"@"+this._openArgs.url+"|"+_12e+"#"+this._openArgs.method;
var _131=JsHttpRequest.CACHE[hash];
if(_131){
this._dataReady(_131[0],_131[1]);
return false;
}
}
var _132=(this.loader||"").toLowerCase();
if(_132&&!JsHttpRequest.LOADERS[_132]){
return this._error("unk_loader",_132);
}
var _133=[];
var lds=JsHttpRequest.LOADERS;
for(var _135 in lds){
var ldr=lds[_135].loader;
if(!ldr){
continue;
}
if(_132&&_135!=_132){
continue;
}
var _137=new ldr(this);
JsHttpRequest.extend(_137,this._openArgs);
JsHttpRequest.extend(_137,{queryText:_12e.join("&"),queryElem:_12f,id:(new Date().getTime())+""+JsHttpRequest.COUNT++,hash:hash,span:null});
var _138=_137.load();
if(!_138){
this._ldObj=_137;
JsHttpRequest.PENDING[_137.id]=this;
return true;
}
if(!_132){
_133[_133.length]="- "+_135.toUpperCase()+": "+this._l(_138);
}else{
return this._error(_138);
}
}
return _135?this._error("no_loader_matched",_133.join("\n")):this._error("no_loaders");
};
t.getAllResponseHeaders=function(){
with(this){
return _ldObj&&_ldObj.getAllResponseHeaders?_ldObj.getAllResponseHeaders():[];
}
};
t.getResponseHeader=function(_139){
with(this){
return _ldObj&&_ldObj.getResponseHeader?_ldObj.getResponseHeader():[];
}
};
t.setRequestHeader=function(_13a,_13b){
with(this){
_reqHeaders[_reqHeaders.length]=[_13a,_13b];
}
};
t._dataReady=function(text,js){
with(this){
if(caching&&_ldObj){
JsHttpRequest.CACHE[_ldObj.hash]=[text,js];
}
if(text!==null||js!==null){
status=4;
responseText=responseXML=text;
responseJS=js;
}else{
status=500;
responseText=responseXML=responseJS=null;
}
_changeReadyState(2);
_changeReadyState(3);
_changeReadyState(4);
_cleanup();
}
};
t._l=function(args){
var i=0,p=0,msg=this._errors[args[0]];
while((p=msg.indexOf("%",p))>=0){
var a=args[++i]+"";
msg=msg.substring(0,p)+a+msg.substring(p+1,msg.length);
p+=1+a.length;
}
return msg;
};
t._error=function(msg){
msg=this._l(typeof (msg)=="string"?arguments:msg);
msg="JsHttpRequest: "+msg;
if(t.onerror){
return t.onerror(msg);
}
if(!window.Error){
throw msg;
}else{
if((new Error(1,"test")).description=="test"){
throw new Error(1,msg);
}else{
throw new Error(msg);
}
}
};
t._hash2query=function(_144,_145,_146,_147){
if(_145==null){
_145="";
}
if(_144 instanceof Object){
var _148=false;
for(var k in _144){
var v=_144[k];
if(v instanceof Function){
continue;
}
var _14b=_145?_145+"["+this.escape(k)+"]":this.escape(k);
var _14c=v&&v.parentNode&&v.parentNode.appendChild&&v.tagName;
if(_14c){
var tn=v.tagName.toUpperCase();
if(tn=="FORM"){
_148=true;
}else{
if(tn=="INPUT"||tn=="TEXTAREA"||tn=="SELECT"){
}else{
return this._error("inv_form_el",(e.name||""),e.tagName);
}
}
_147[_147.length]={name:_14b,e:v};
}else{
if(v instanceof Object){
this._hash2query(v,_14b,_146,_147);
}else{
if(v===null){
continue;
}
_146[_146.length]=_14b+"="+this.escape(""+v);
}
}
if(_148&&_147.length>1){
return this._error("must_be_single_el");
}
}
}else{
_146[_146.length]=_144;
}
return true;
};
t._cleanup=function(){
var _14e=this._ldObj;
if(!_14e){
return;
}
JsHttpRequest.PENDING[_14e.id]=false;
var span=_14e.span;
if(!span){
return;
}
_14e.span=null;
var _150=function(){
span.parentNode.removeChild(span);
};
JsHttpRequest.setTimeout(_150,50);
};
t._changeReadyState=function(s,_152){
with(this){
if(_152){
status=statusText=responseJS=null;
responseText="";
}
readyState=s;
if(onreadystatechange){
onreadystatechange();
}
}
};
t.escape=function(s){
return escape(s).replace(new RegExp("\\+","g"),"%2B");
};
}
JsHttpRequest.COUNT=0;
JsHttpRequest.MAX_URL_LEN=2000;
JsHttpRequest.CACHE={};
JsHttpRequest.PENDING={};
JsHttpRequest.LOADERS={};
JsHttpRequest._dummy=function(){
};
JsHttpRequest.TIMEOUTS={s:window.setTimeout,c:window.clearTimeout};
JsHttpRequest.setTimeout=function(func,dt){
window.JsHttpRequest_tmp=JsHttpRequest.TIMEOUTS.s;
if(typeof (func)=="string"){
id=window.JsHttpRequest_tmp(func,dt);
}else{
var id=null;
var _157=function(){
func();
delete JsHttpRequest.TIMEOUTS[id];
};
id=window.JsHttpRequest_tmp(_157,dt);
JsHttpRequest.TIMEOUTS[id]=_157;
}
window.JsHttpRequest_tmp=null;
return id;
};
JsHttpRequest.clearTimeout=function(id){
window.JsHttpRequest_tmp=JsHttpRequest.TIMEOUTS.c;
delete JsHttpRequest.TIMEOUTS[id];
var r=window.JsHttpRequest_tmp(id);
window.JsHttpRequest_tmp=null;
return r;
};
JsHttpRequest.query=function(url,_15b,_15c,_15d){
var req=new this();
req.caching=!_15d;
req.onreadystatechange=function(){
if(req.readyState==4){
_15c(req.responseJS,req.responseText);
}
};
var _15f=null;
if(url.match(/^((\w+)\.)?(GET|POST)\s+(.*)/i)){
req.loader=RegExp.$2?RegExp.$2:null;
_15f=RegExp.$3;
url=RegExp.$4;
}
req.open(_15f,url,true);
req.send(_15b);
};
JsHttpRequest.dataReady=function(d){
var th=this.PENDING[d.id];
delete this.PENDING[d.id];
if(th){
th._dataReady(d.text,d.js);
}else{
if(th!==false){
throw "dataReady(): unknown pending id: "+d.id;
}
}
};
JsHttpRequest.extend=function(dest,src){
for(var k in src){
dest[k]=src[k];
}
};
JsHttpRequest.LOADERS.xml={loader:function(req){
JsHttpRequest.extend(req._errors,{xml_no:"Cannot use XMLHttpRequest or ActiveX loader: not supported",xml_no_diffdom:"Cannot use XMLHttpRequest to load data from different domain %",xml_no_headers:"Cannot use XMLHttpRequest loader or ActiveX loader, POST method: headers setting is not supported, needed to work with encodings correctly",xml_no_form_upl:"Cannot use XMLHttpRequest loader: direct form elements using and uploading are not implemented"});
this.load=function(){
if(this.queryElem.length){
return ["xml_no_form_upl"];
}
if(this.url.match(new RegExp("^([a-z]+)://([^\\/]+)(.*)","i"))){
if(RegExp.$2.toLowerCase()==document.location.hostname.toLowerCase()){
this.url=RegExp.$3;
}else{
return ["xml_no_diffdom",RegExp.$2];
}
}
var xr=null;
if(window.XMLHttpRequest){
try{
xr=new XMLHttpRequest();
}
catch(e){
}
}else{
if(window.ActiveXObject){
try{
xr=new ActiveXObject("Microsoft.XMLHTTP");
}
catch(e){
}
if(!xr){
try{
xr=new ActiveXObject("Msxml2.XMLHTTP");
}
catch(e){
}
}
}
}
if(!xr){
return ["xml_no"];
}
var _167=window.ActiveXObject||xr.setRequestHeader;
if(!this.method){
this.method=_167?"POST":"GET";
}
if(this.method=="GET"){
if(this.queryText){
this.url+=(this.url.indexOf("?")>=0?"&":"?")+this.queryText;
}
this.queryText="";
if(this.url.length>JsHttpRequest.MAX_URL_LEN){
return ["url_too_long",JsHttpRequest.MAX_URL_LEN];
}
}else{
if(this.method=="POST"&&!_167){
return ["xml_no_headers"];
}
}
this.url+=(this.url.indexOf("?")>=0?"&":"?")+"JsHttpRequest="+(req.caching?"0":this.id)+"-xml";
var id=this.id;
xr.onreadystatechange=function(){
if(xr.readyState!=4){
return;
}
xr.onreadystatechange=JsHttpRequest._dummy;
req.status=null;
try{
req.status=xr.status;
req.responseText=xr.responseText;
}
catch(e){
}
if(!req.status){
return;
}
try{
eval("JsHttpRequest._tmp = function(id) { var d = "+req.responseText+"; d.id = id; JsHttpRequest.dataReady(d); }");
}
catch(e){
return req._error("js_invalid",req.responseText);
}
JsHttpRequest._tmp(id);
JsHttpRequest._tmp=null;
};
xr.open(this.method,this.url,true,this.username,this.password);
if(_167){
for(var i=0;i<req._reqHeaders.length;i++){
xr.setRequestHeader(req._reqHeaders[i][0],req._reqHeaders[i][1]);
}
xr.setRequestHeader("Content-Type","application/octet-stream");
}
xr.send(this.queryText);
this.span=null;
this.xr=xr;
return null;
};
this.getAllResponseHeaders=function(){
return this.xr.getAllResponseHeaders();
};
this.getResponseHeader=function(_16a){
return this.xr.getResponseHeader(_16a);
};
this.abort=function(){
this.xr.abort();
this.xr=null;
};
}};
JsHttpRequest.LOADERS.script={loader:function(req){
JsHttpRequest.extend(req._errors,{script_only_get:"Cannot use SCRIPT loader: it supports only GET method",script_no_form:"Cannot use SCRIPT loader: direct form elements using and uploading are not implemented"});
this.load=function(){
if(this.queryText){
this.url+=(this.url.indexOf("?")>=0?"&":"?")+this.queryText;
}
this.url+=(this.url.indexOf("?")>=0?"&":"?")+"JsHttpRequest="+this.id+"-"+"script";
this.queryText="";
if(!this.method){
this.method="GET";
}
if(this.method!=="GET"){
return ["script_only_get"];
}
if(this.queryElem.length){
return ["script_no_form"];
}
if(this.url.length>JsHttpRequest.MAX_URL_LEN){
return ["url_too_long",JsHttpRequest.MAX_URL_LEN];
}
if(req._reqHeaders.length){
return ["no_headers","SCRIPT"];
}
var th=this,d=document,s=null,b=d.body;
if(!window.opera){
this.span=s=d.createElement("SCRIPT");
var _170=function(){
s.language="JavaScript";
if(s.setAttribute){
s.setAttribute("src",th.url);
}else{
s.src=th.url;
}
b.insertBefore(s,b.lastChild);
};
}else{
this.span=s=d.createElement("SPAN");
s.style.display="none";
b.insertBefore(s,b.lastChild);
s.innerHTML="Workaround for IE.<s"+"cript></"+"script>";
var _170=function(){
s=s.getElementsByTagName("SCRIPT")[0];
s.language="JavaScript";
if(s.setAttribute){
s.setAttribute("src",th.url);
}else{
s.src=th.url;
}
};
}
JsHttpRequest.setTimeout(_170,10);
return null;
};
}};
JsHttpRequest.LOADERS.form={loader:function(req){
JsHttpRequest.extend(req._errors,{form_el_not_belong:"Element \"%\" does not belong to any form!",form_el_belong_diff:"Element \"%\" belongs to a different form. All elements must belong to the same form!",form_el_inv_enctype:"Attribute \"enctype\" of the form must be \"%\" (for IE), \"%\" given."});
this.load=function(){
var th=this;
if(!th.method){
th.method="POST";
}
th.url+=(th.url.indexOf("?")>=0?"&":"?")+"JsHttpRequest="+th.id+"-"+"form";
if(req._reqHeaders.length){
return ["no_headers","FORM"];
}
if(th.method=="GET"){
if(th.queryText){
th.url+=(th.url.indexOf("?")>=0?"&":"?")+th.queryText;
}
if(th.url.length>JsHttpRequest.MAX_URL_LEN){
return ["url_too_long",JsHttpRequest.MAX_URL_LEN];
}
var p=th.url.split("?",2);
th.url=p[0];
th.queryText=p[1]||"";
}
var form=null;
var _175=false;
if(th.queryElem.length){
if(th.queryElem[0].e.tagName.toUpperCase()=="FORM"){
form=th.queryElem[0].e;
_175=true;
th.queryElem=[];
}else{
form=th.queryElem[0].e.form;
for(var i=0;i<th.queryElem.length;i++){
var e=th.queryElem[i].e;
if(!e.form){
return ["form_el_not_belong",e.name];
}
if(e.form!=form){
return ["form_el_belong_diff",e.name];
}
}
}
if(th.method=="POST"){
var need="multipart/form-data";
var _179=(form.attributes.encType&&form.attributes.encType.nodeValue)||(form.attributes.enctype&&form.attributes.enctype.value)||form.enctype;
if(_179!=need){
return ["form_el_inv_enctype",need,_179];
}
}
}
var d=form&&(form.ownerDocument||form.document)||document;
var _17b="jshr_i_"+th.id;
var s=th.span=d.createElement("DIV");
s.style.position="absolute";
s.style.visibility="hidden";
s.innerHTML=(form?"":"<form"+(th.method=="POST"?" enctype=\"multipart/form-data\" method=\"post\"":"")+"></form>")+"<iframe name=\""+_17b+"\" id=\""+_17b+"\" style=\"width:0px; height:0px; overflow:hidden; border:none\"></iframe>";
if(!form){
form=th.span.firstChild;
}
d.body.insertBefore(s,d.body.lastChild);
var _17d=function(e,attr){
var sv=[];
var form=e;
if(e.mergeAttributes){
var form=d.createElement("form");
form.mergeAttributes(e,false);
}
for(var i=0;i<attr.length;i++){
var k=attr[i][0],v=attr[i][1];
sv[sv.length]=[k,form.getAttribute(k)];
form.setAttribute(k,v);
}
if(e.mergeAttributes){
e.mergeAttributes(form,false);
}
return sv;
};
var _185=function(){
top.JsHttpRequestGlobal=JsHttpRequest;
var _186=[];
if(!_175){
for(var i=0,n=form.elements.length;i<n;i++){
_186[i]=form.elements[i].name;
form.elements[i].name="";
}
}
var qt=th.queryText.split("&");
for(var i=qt.length-1;i>=0;i--){
var pair=qt[i].split("=",2);
var e=d.createElement("INPUT");
e.type="hidden";
e.name=unescape(pair[0]);
e.value=pair[1]!=null?unescape(pair[1]):"";
form.appendChild(e);
}
for(var i=0;i<th.queryElem.length;i++){
th.queryElem[i].e.name=th.queryElem[i].name;
}
var sv=_17d(form,[["action",th.url],["method",th.method],["onsubmit",null],["target",_17b]]);
form.submit();
_17d(form,sv);
for(var i=0;i<qt.length;i++){
form.lastChild.parentNode.removeChild(form.lastChild);
}
if(!_175){
for(var i=0,n=form.elements.length;i<n;i++){
form.elements[i].name=_186[i];
}
}
};
JsHttpRequest.setTimeout(_185,100);
return null;
};
}};

