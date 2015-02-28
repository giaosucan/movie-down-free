<?php
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");
if ($level != 3) {
	echo "Private area.";
	exit();
}
$error_arr = array();
//--------------FORUM------------------------
$config_arr = array(
	'announcement'	=>
		array(
			'name'	=>	'announcement',
			'desc'	=>	'Announcement',
			'type'	=>	'text',
		),
	'total_visit'	=>
		array(
			'name'	=>	'total_visit',
			'desc'	=>	'Total visitors',
			'type'	=>	'number',
		),
	'web_title'	=>
		array(
			'name'	=>	'web_title',
			'desc'	=>	'Website title',
			'type'	=>	'free',
		),
	'web_url'	=>
		array(
			'name'	=>	'web_url',
			'desc'	=>	'Website URL',
			'type'	=>	'free',
		),
	'web_email'	=>
		array(
			'name'	=>	'web_email',
			'desc'	=>	'Contact e-mail',
			'type'	=>	'free',
		),
	'download_salt'	=>
		array(
			'name'	=>	'download_salt',
			'desc'	=>	'Download code<br>Type what you want (^o^)',
			'type'	=>	'free',
		),
	'must_login_to_download'	=>
		array(
			'name'	=>	'must_login_to_download',
			'desc'	=>	'Have to login to view trailer',
			'type'	=>	'true_false',
		),
	'must_login_to_play'	=>
		array(
			'name'	=>	'must_login_to_play',
			'desc'	=>	'Have to login to view detail',
			'type'	=>	'true_false',
		),
	'media_per_page'	=>
		array(
			'name'	=>	'media_per_page',
			'desc'	=>	'Movies per page',
			'type'	=>	'free',
		),
	'intro_song'	=>
		array(
			'name'	=>	'intro_song',
			'desc'	=>	'Intro movie',
			'type'	=>	'free',
		),
	'intro_song_is_local'		=>
		array(
			'name'	=>	'intro_song_is_local',
			'desc'	=>	'Intro movie is local URL ?',
			'type'	=>	'true_false',
		),
  'must_login_to_rate'	=>
    array(
      'name'  =>  'must_login_to_rate',
      'desc'  =>  'Have to login to rate',
      'type'  =>  'true_false',
    ),
	'sendmailconfirm'		=>
    array(
    	'name'  =>  'sendmailconfirm',
    	'desc'  =>  'Send mail after registering',
     	'type'  =>  'true_false',
    ),
);

if ($submit && $_POST) {
	$list = array_keys($_POST);
	$ok = true;
	for ($i=0;$i<count($list);$i++) {
		$key = $list[$i];
		$vl = addslashes($_POST[$key]);
		if ($key == 'web_url') 
			if ($vl[strlen($vl)-1] == '/') $vl = substr($vl,0,-1);
		
		if ($key == 'announcement' && $vl == '<br>') $vl = '';
		if ($key == 'submit') continue;
		if (!array_key_exists($key,$config_arr)) continue;
		$arr = $config_arr[$r['config_name']];
		if ($check[0] == 'number' && !is_numeric($vl)) { $ok = false; $error_arr[] = $key; }
		if ($ok) $mysql->query("UPDATE ".$tb_prefix."config SET config_value = '".$vl."' WHERE config_name = '".$key."'");
	}
	if ($ok) {
		echo "Edit successfull <meta http-equiv='refresh' content='0;url=$link'>";
		exit();
	}
}

//--------------------------------------------
echo "<form method=post>".
	"<table class=border cellpadding=2 cellspacing=0 width=90%>".
	"<tr><td colspan=2 class=title align=center>Configures</td></tr>";
$q = $mysql->query("SELECT * FROM ".$tb_prefix."config ORDER BY config_name ASC");
while ($r = $mysql->fetch_array($q)) {
	if (!$submit && !count($error_arr)) $vl = stripslashes($r['config_value']);
	else $vl = stripslashes($_POST[$r['config_name']]);
	if (array_key_exists($r['config_name'],$config_arr)) {
		$arr = $config_arr[$r['config_name']];
		if (in_array($r['config_name'],$error_arr)) $symbol = "<font style='color:red'>*</font> ";
		else $symbol = '';
		echo "<tr><td class=fr><b>".$arr['desc']."</b> : </td><td class=fr_2>";
		if (!$arr['type'] || $arr['type'] == 'number' || $arr['type'] == 'free') echo "<input name=".$r['config_name']." size=50 value='".$vl."'>";
		//elseif ($arr['type'] == 'text') echo "<script language=\"JavaScript\" type=\"text/javascript\" src=\"../js/openwysiwyg/wysiwyg.js\"></script><textarea cols=60 rows=10 id=".$r['config_name']." name=".$r['config_name'].">".$vl."</textarea>"."<script language=\"JavaScript\">generate_wysiwyg('".$r['config_name']."');</script>";
		elseif ($arr['type'] == 'text') echo "
<a href='javascript: void(0);' style='display: none;' id=".$r['config_name']."Dis onclick=\"javascript: disableEditor('".$r['config_name']."','".$r['config_name']."', ".$r['config_name']."Editor);\">Default editor</a>
<b id=".$r['config_name']."DisB>Default editor</b>
&nbsp;&nbsp;
<a href='javascript: void(0);' id=".$r['config_name']."Enb onclick=\"javascript: enableEditor('".$r['config_name']."','".$r['config_name']."', ".$r['config_name']."Editor);\">WYSIWYG editor</a>
<b id=".$r['config_name']."EnbB style='display: none;'>WYSIWYG editor</b>
</div>
<textarea id='".$r['config_name']."' name='".$r['config_name']."' cols='70' rows='10'>".$vl."</textarea>
<div id=".$r['config_name']."Box style='display: none;'>
<textarea id=".$r['config_name']."Adv cols='70' rows='10'></textarea>
<script type='text/javascript'>
<!--
	if (isHTML_Editor) {
		var ".$r['config_name']."Editor = new InnovaEditor('".$r['config_name']."Editor');
		".$r['config_name']."Editor.width = 49*9;
		if (navigator.appName.indexOf('Microsoft')!=-1)
			".$r['config_name']."Editor.height = 7*20;
		else
			".$r['config_name']."Editor.height = 7*9;
		".$r['config_name']."Editor.features=[\"Bold\",\"Italic\",\"Underline\",\"Strikethrough\",\"|\",\"ForeColor\",\"|\",
				\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"JustifyFull\",\"|\",
				\"Numbering\",\"Bullets\",\"|\",\"Hyperlink\"];

		".$r['config_name']."Editor.mode = 'XHTMLBody';
		".$r['config_name']."Editor.useBR = true;
		".$r['config_name']."Editor.useTagSelector=false;
		".$r['config_name']."Editor.REPLACE(\"".$r['config_name']."Adv\");

		var reg = new RegExp(\"(;|^)".$r['config_name']."EditorEnabled=Y\",\"\");
		if (document.cookie.search(reg) != -1)
			document.getElementById('".$r['config_name']."Enb').onclick;
	}
-->
</script>
		";
		elseif ($arr['type'] == 'true_false')
			echo "<input type=radio name=".$r['config_name']." value=1".(($r['config_value'] == 1)?' checked':'')."> Yes <input type=radio name=".$r['config_name']." value=0".(($r['config_value'] == 0)?' checked':'')."> No";
		if ($arr['type'] == 'number' && in_array($r['config_name'],$error_arr)) echo " Require only number.";
		echo "</td></tr>";
	}
}
echo "<tr><td colspan=2 align=center><input class=submit name=submit type=submit value=Submit> <input type=reset class=submit value='Reset'></td></tr>";
echo "</table></form>";
?>