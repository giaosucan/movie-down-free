<?
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");

$edit_url = 'index.php?act=mod&mode=edit';

$inp_arr = array(
		'name'	=> array(
			'table'	=>	'mod_name',
			'name'	=>	'Name',
			'type'	=>	'free',
		),
		'url'	=> array(
			'table'	=>	'mod_value',
			'name'	=>	'Code',
			'type'	=>	'text',
			'can_be_empty'	=> true,
		),
	);
##################################################
# ADD MOD
##################################################
if ($mode == 'add') {
	acp_check_permission('add_link');
	if ($_POST['submit']) {
		$error_arr = array();
		$error_arr = $form->checkForm($inp_arr);
		if (!$error_arr) {
			$sql = $form->createSQL(array('INSERT',$tb_prefix.'mod'),$inp_arr);
			eval('$mysql->query("'.$sql.'");');
			echo "Add successfull <meta http-equiv='refresh' content='0;url=$link'>";
			exit();
		}
	}
	$warn = $form->getWarnString($error_arr);

	$form->createForm('Add mod',$inp_arr,$error_arr);
}
##################################################
# EDIT MOD
##################################################
if ($mode == 'edit') {
	acp_check_permission('edit_link');
	if ($mod_name) {
		if (!$_POST['submit']) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."mod WHERE mod_name = '$mod_name'");
			$r = $mysql->fetch_array($q);
			foreach ($inp_arr as $key=>$arr) $$key = $r[$arr['table']];
		}
		else {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$sql = $form->createSQL(array('UPDATE',$tb_prefix.'mod','mod_name','mod_name'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
		$form->createForm('Edit mod',$inp_arr,$error_arr);
	}
	else {
		echo "<script>function check_del(id) {".
		"if (confirm('Do you want to delete this mod ?')) location='?act=mod&mode=del&mod_name='+id;".
		"return false;}</script>";
		echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form method=post>";
		echo "<tr><td align=left width=40% class=title>Name</td><td class=title width=40%>Code</td></tr>";
		$q = $mysql->query("SELECT * FROM ".$tb_prefix."mod ORDER BY mod_name ASC");
		while ($r = $mysql->fetch_array($q)) {
			//$r['mod_value'] = m_unhtmlchars($r['mod_value']);
			echo "<tr align=center class=fr><td><a href=?act=mod&mode=del&mod_name=".$r['mod_name'].">DEL</a> - <a href=\"$link&mod_name=".$r['mod_name']."\"><b>".$r['mod_name']."</b></a></td><td class=fr_2>".$r['mod_value']."</td></tr>";
		}
		echo '<tr><td colspan="2" align="center"><input type="submit" name="sbm" class=submit value="Edit order"></td></tr>';
		echo '</form></table>';
	}
	
}
##################################################
# DELETE MOD
##################################################
if ($mode == 'del') {
	acp_check_permission('del_link');
	if ($mod_name) {
		if ($_POST['submit']) {
			$mysql->query("DELETE FROM ".$tb_prefix."mod WHERE mod_name = '".$mod_name."'");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
			exit();
		}
		?>
		<form method="post">Do you want to delete this mod ?<br><input value="Yes" name=submit type=submit class=submit></form>
<?
	}
}
?>