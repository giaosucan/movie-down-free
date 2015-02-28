<?
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");

$edit_url = 'index.php?act=option&mode=edit';

$inp_arr = array(
		'option'	=> array(
			'table'	=>	'option_name',
			'name'	=>	'Option name',
			'type'	=>	'free'
		),
		'order'	=> array(
			'table'	=>	'option_order',
			'name'	=>	'Order',
			'type'	=>	'number',
			'can_be_empty'	=>	true,
		),
	);
##################################################
# ADD OPTION
##################################################
if ($mode == 'add') {
	if ($level == 2 && !$mod_permission['add_option']) echo 'Private area';
	else {
		if ($_POST['submit']) {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$sql = $form->createSQL(array('INSERT',$tb_prefix.'options'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Add successfull <meta http-equiv='refresh' content='0;url=$link'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
	
		$form->createForm('Add option',$inp_arr,$error_arr);
	}
}
##################################################
# EDIT OPTION
##################################################
if ($mode == 'edit') {
	if ($option_del_id) {
		acp_check_permission('del_option');
		if ($_POST['submit']) {
			$mysql->query("DELETE FROM ".$tb_prefix."options WHERE option_id = '".$option_del_id."'");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
			exit();
		}
		?>
		<form method="post">Do you want to delete this option ?<br><input value="Yes" name=submit type=submit class=submit></form>
		<?
	}
	elseif ($_POST['do']) {
		$arr = $_POST['checkbox'];
		if (!count($arr)) die('Error');
		if ($_POST['selected_option'] == 'del') {
			acp_check_permission('del_option');
			$in_sql = implode(',',$arr);
			$mysql->query("DELETE FROM ".$tb_prefix."options WHERE option_id IN (".$in_sql.")");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
		}
	}
	elseif ($option_id) {
		acp_check_permission('edit_option');
		if (!$_POST['submit']) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."options WHERE option_id = '$option_id'");
			$r = $mysql->fetch_array($q);
			
			foreach ($inp_arr as $key=>$arr) $$key = $r[$arr['table']];
		}
		else {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$sql = $form->createSQL(array('UPDATE',$tb_prefix.'options','option_id','option_id'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
		$form->createForm('Edit option',$inp_arr,$error_arr);
	}
	else {
		acp_check_permission('edit_option');
		$m_per_page = 30;
		if (!$pg) $pg = 1;
		
		$q = $mysql->query("SELECT * FROM ".$tb_prefix."options ORDER BY option_order ASC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
		$tt = $mysql->fetch_array($mysql->query("SELECT COUNT(option_id) FROM ".$tb_prefix."options"));
		$tt = $tt[0];
		if ($tt) {
			echo "Option ID to <b>Edit</b>: <input id=option_id size=20> <input type=button onclick='window.location.href = \"".$link."&option_id=\"+document.getElementById(\"option_id\").value;' value=Edit><br>";
			echo "Option ID to <b>Delete</b>: <input id=option_del_id size=20> <input type=button onclick='window.location.href = \"".$link."&option_del_id=\"+document.getElementById(\"option_del_id\").value;' value=Delete><br>";
			
			echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form name=media_list method=post action=$link onSubmit=\"return check_checkbox();\">";
			echo "<tr align=center><td width=3%><input class=checkbox type=checkbox name=chkall id=chkall onclick=docheck(document.media_list.chkall.checked,0) value=checkall></td><td class=title width=60%>Option name</td><td class=title>Order</td></tr>";
			while ($r = $mysql->fetch_array($q)) {
				$id = $r['option_id'];
				$singer = $r['option_name'];
				$img = $r['option_order'];
				echo "<tr><td><input class=checkbox type=checkbox id=checkbox onclick=docheckone() name=checkbox[] value=$id></td><td class=fr><b><a href=?act=option&mode=edit&option_id=".$id.">".$singer."</a></b></td><td class=fr_2 align=center>".$img."</td></tr>";
			}
			echo "<tr><td colspan=3>".admin_viewpages($tt,$m_per_page,$pg)."</td></tr>";
			echo '<tr><td colspan=3 align="center">Do with selected options : '.
				'<select name=selected_option><option value=del>Delete</option>'.
				'<input type="submit" name="do" class=submit value="Do it"></td></tr>';
			echo '</form></table>';
		}
		else echo "No options found";
	}
}
?>