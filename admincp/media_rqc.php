<?
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");
$edit_url = 'index.php?act=rqc&mode=edit';
$inp_arr = array(
		'name'	=> array(
			'table'	=>	'rqc_web',
			'name'	=>	'Title',
			'type'	=>	'free'
		),
		'url'	=> array(
			'table'	=>	'rqc_url',
			'name'	=>	'Link or code',
			'type'	=>	'text'
		),
		'logo'	=> array(
			'table'	=>	'rqc_img',
			'name'	=>	'Logo',
			'type'	=>	'free',
			'can_be_empty' => true,
		),
	);

##################################################
# ADD rqc
##################################################
if ($mode == 'add') {
	acp_check_permission('add_link');
	if ($_POST['submit']) {
		$error_arr = array();
		$error_arr = $form->checkForm($inp_arr);
		if (!$error_arr) {
			$sql = $form->createSQL(array('INSERT',$tb_prefix.'rqc'),$inp_arr);
			eval('$mysql->query("'.$sql.'");');
			echo "Add successfull <meta http-equiv='refresh' content='0;url=$link'>";
			exit();
		}
	}
	$warn = $form->getWarnString($error_arr);
	$form->createForm('Add advertisement',$inp_arr,$error_arr);
}

##################################################
# EDIT rqc
##################################################
if ($mode == 'edit') {
	acp_check_permission('edit_link');
	if ($rqc_id) {
		if (!$_POST['submit']) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."rqc WHERE rqc_id = '$rqc_id'");
			$r = $mysql->fetch_array($q);
			foreach ($inp_arr as $key=>$arr) $$key = $r[$arr['table']];
		}
		else {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$sql = $form->createSQL(array('UPDATE',$tb_prefix.'rqc','rqc_id','rqc_id'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
		$form->createForm('Edit advertisement',$inp_arr,$error_arr);
	}
	else {
		echo "<script>function check_del(id) {".
		"if (confirm('Do you want to delete this ads ?')) location='?act=rqc&mode=del&rqc_id='+id;".
		"return false;}</script>";
		echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form method=post>";
		echo "<tr><td align=left width=40% class=title>Title</td><td class=title width=40%>Link/Code</td><td align=center class=title>Logo</td></tr>";
		$q = $mysql->query("SELECT * FROM ".$tb_prefix."rqc ORDER BY rqc_id ASC");
		while ($r = $mysql->fetch_array($q)) {
			$r['rqc_img'] = str_replace("picz/", "../picz/", $r['rqc_img']);
			if ($r['rqc_img'] == "") {
 				/*$r['rqc_url'] = m_unhtmlchars($r['rqc_url']);
				$r['rqc_url'] = str_replace("picz/", "../picz/", $r['rqc_url']);*/
				echo "<tr align=center class=fr><td><a href='?act=rqc&mode=del&rqc_id=".$r['rqc_id']."'>DEL</a> - <a href=\"$link&rqc_id=".$r['rqc_id']."\"><b>".$r['rqc_web']."</b></a></td><td class=fr_2>Empty</a></td><td class=fr>".$r['rqc_url']."</td></tr>";
			} else echo "<tr align=center class=fr><td><a href='?act=rqc&mode=del&rqc_id=".$r['rqc_id']."'>DEL</a> - <a href=\"$link&rqc_id=".$r['rqc_id']."\"><b>".$r['rqc_web']."</b></a></td><td class=fr_2><a href=\"".$r['rqc_url']."\" target=_blank><b>".$r['rqc_url']."</b></a></td><td class=fr><img src=\"".$r['rqc_img']."\" width=160 height=80></td></tr>";
		}
		echo '<tr><td colspan="2" align="center"><input type="submit" name="sbm" class=submit value="Edit order"></td></tr>';
		echo '</form></table>';
	}
}

##################################################
# DELETE rqc
##################################################
if ($mode == 'del') {
	acp_check_permission('del_link');
	if ($rqc_id) {
		if ($_POST['submit']) {
			$mysql->query("DELETE FROM ".$tb_prefix."rqc WHERE rqc_id = '".$rqc_id."'");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
			exit();
		}
		?>
		<form method="post">Do you want to delete this ads ?<br><input value="Yes" name=submit type=submit class=submit></form>
<?
	}
}
?>