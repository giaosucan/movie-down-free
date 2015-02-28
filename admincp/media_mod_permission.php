<?php
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");

if ($level != 3) {
	echo "Private area.";
	exit();
}

$mod_permission = acp_get_mod_permission();

$permission_list = array(
	'add_cat'	=>	'Add genre',
	'edit_cat'	=>	'Manage genre',
	'del_cat'	=>	'Delete genre',
	'add_media'	=>	'Add movie',
	'edit_media'	=>	'Manage movie',
	'del_media'	=>	'Delete movie',
	'add_option'	=>	'Add option',
	'edit_option'	=>	'Manage option',
	'del_option'	=>	'Delete option',
	'add_singer'	=>	'Add singer',
	'edit_singer'	=>	'Manage singer',
	'del_singer'	=>	'Delete singer',
	'add_album'	=>	'Add collection',
	'edit_album'	=>	'Manage collection',
	'del_album'	=>	'Delete collection',
	'add_user'	=>	'Add user',
	'edit_user'	=>	'Manage user',
	'del_user'	=>	'Delete user',
	'add_link'	=>	'Add link',
	'edit_link'	=>	'Manage link',
	'del_link'	=>	'Delete link',
	'add_template'	=>	'Add theme',
	'edit_template'	=>	'Manage theme',
	'del_template'	=>	'Delete theme',
);

if (!$_POST['submit']) {
?>
<form method=post>
<table class=border cellpadding=2 cellspacing=0 width=95%>
<tr><td colspan=2 class=title align=center>Moderator permissions</td></tr>
<?php
foreach ($permission_list as $name => $desc) {
?>
<tr>
	<td class=fr width=30%><b><?=$desc?></b></td>
	<td class=fr_2><input type=radio class=checkbox value=1 name=<?=$name?><?=(($mod_permission[$name])?' checked':'')?>> Yes <input type=radio class=checkbox value=0 name=<?=$name?><?=((!$mod_permission[$name])?' checked':'')?>> No </td>
</tr>
<?php
}
?>
<tr><td class=fr colspan=2 align=center><input type=submit name=submit class=submit value=Submit></td></tr>
</table>
</form>
<?php
}
else {
	$per = '';
	foreach ($permission_list as $name => $desc) {
		$v = $_POST[$name];
		if ($v == '') $v = 0;
		$per .= $v;
	}
	$per = bindec($per);
	$mysql->query("UPDATE ".$tb_prefix."config SET config_value = '".$per."' WHERE config_name = 'mod_permission'");
	echo "Edit successfull <meta http-equiv='refresh' content='0;url=$link'>";
}
?>