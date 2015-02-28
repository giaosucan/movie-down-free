<?php
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");

$edit_url = 'index.php?act=song&mode=edit';

$inp_arr = array(
		'title'		=> array(
			'table'	=>	'm_title',
			'name'	=>	'Title',
			'type'	=>	'free',
		),
		'cat'		=> array(
			'table'	=>	'm_cat',
			'name'	=>	'Genre',
			'type'	=>	'function::acp_cat::free',
		),
		'option'		=> array(
			'table'	=>	'm_id',
			'name'	=>	'Options',
			'type'	=>	'function::acp_option_list::free',
			'can_be_empty'    =>  	true,
			'update_if_true'	=>		'1==0',
		),
		'album'		=> array(
			'table'	=>	'm_album',
			'name'	=>	'Collection',
			'type'	=>	'function::acp_album_list::number',
		),
		'new_album'    =>    array(
      'name'  =>  'New collection',
      'type'  =>  'function::acp_quick_add_album_form::free',
      'desc'  =>  'You can creat a new collection quickly if not exist.',
      'can_be_empty'    =>    true,
    ),
		'type_media'	=> array(
			'table'	=>	'm_type',
			'name'	=>	'Type',
			'type'	=>	'hidden_value',
			'change_on_update'	=>	true,
		),
		'picz'		=> array(
			'table'	=>	'm_img',
			'name'	=>	'Picture',
			'type'	=>	'file',
			'can_be_empty'	=>	true,
		),
		'url'		=> array(
			'table'	=>	'm_url',
			'name'	=>	'Trailer link',
			'type'	=>	'free',
			'can_be_empty'	=>	true,
		),
		'local_url'	=> array(
			'table'	=>	'm_is_local',
			'name'	=>	'Local URL',
			'type'	=>	'checkbox',
			'checked'	=>	false,
			'can_be_empty'	=>	true,
		),
		'lyric'			=> array(
			'table'		=>	'm_lyric',
			'name'		=>	'Description',
			'type'		=>	'text',
			'can_be_empty'	=>	true,
		),
		'linkr'			=> array(
			'table'		=>	'm_linkr',
			'name'		=>	'Rapid links',
			'type'		=>	'text',
			'can_be_empty'	=>	true,
		),
		'linkm'			=> array(
			'table'		=>	'm_linkm',
			'name'		=>	'Mega links',
			'type'		=>	'text',
			'can_be_empty'	=>	true,
		),
		'linko'			=> array(
			'table'		=>	'm_linko',
			'name'		=>	'Other links',
			'type'		=>	'text',
			'can_be_empty'	=>	true,
		),
		'date'		=>	array(
			'table'	=>	'm_date',
			'type'	=>	'hidden_value',
			'value'	=>	date("Y-m-d",NOW),
		),
		'poster'		=>	array(
			'table'	=>	'm_poster',
			'type'	=>	'hidden_value',
			'value'	=>	$_SESSION['admin_id'],
		),
		'title_ascii'	=>	array(
			'table'	=>	'm_title_ascii',
			'type'	=>	'hidden_value',
			'value'	=>	'',
			'change_on_update'	=>	true,
		),
);

##################################################
# ADD MEDIA
##################################################
if ($mode == 'add') {
	acp_check_permission('add_media');
	
	if ($_POST['submit']) {
		$error_arr = array();
		if (is_array($_POST['cat'])) $_POST['cat'] = "0,".implode(",", $_POST['cat']).",0";
		$error_arr = $form->checkForm($inp_arr);
		if (!$error_arr) {
			$inp_arr['title_ascii']['value'] = strtolower(utf8_to_ascii($title));
			$title = ucwords($title);
			$newname = "mov_".str_replace(" ", "_", $inp_arr['title_ascii']['value']);
			$newname = preg_replace("/[^A-Z^a-z^0-9^_]+/", "-", $newname);
			if ($_FILES['picz']['name']!="") {
				require("upload_functions.php");
				$picz = uptoload($_FILES['picz'], "{$newname}");
			}
			$inp_arr['type_media']['value'] = acp_type($url);
			if ($new_singer && $singer_type) {
				$singer = acp_quick_add_singer($new_singer,$singer_type);
			}
			if ($new_album) {
	      $album = acp_quick_add_album($new_album,$singer);
      }
			$sql = $form->createSQL(array('INSERT',$tb_prefix.'data'),$inp_arr);
			eval('$mysql->query("'.$sql.'");');
				if (is_array($_POST['options'])) {
					$q = $mysql->query("SELECT m_id FROM ".$tb_prefix."data WHERE m_title='".$title."' ORDER BY m_id DESC LIMIT 0,1");
					$r = $mysql->fetch_array($q);
					$m_id = $r['m_id'];
					$q = $mysql->query("SELECT * FROM ".$tb_prefix."options ORDER BY option_order ASC");
					$ii = 0;
					while ($r = $mysql->fetch_array($q)) {
						if ($mysql->fetch_array($mysql->query("SELECT * FROM ".$tb_prefix."options_values WHERE option_id=".$r['option_id']." AND m_id=".$m_id."")))
							$q2 = $mysql->query("UPDATE ".$tb_prefix."options_values SET option_value='".$_POST['options'][$ii]."' WHERE option_id=".$r['option_id']." AND m_id=".$m_id."");
						else $q2 = $mysql->query("INSERT INTO ".$tb_prefix."options_values VALUES (".$r['option_id'].", ".$m_id.", '".$_POST['options'][$ii]."')");
						$ii++;
					}
				}
			echo "Add successfull <meta http-equiv='refresh' content='0;url=$link'>";
			exit();
		}
	}
	$warn = $form->getWarnString($error_arr);

	$form->createForm('Add movie',$inp_arr,$error_arr);
}
elseif ($mode == 'multi_add') {
	acp_check_permission('add_media');
	include('media_multi_song.php');
}
##################################################
# EDIT MEDIA
##################################################
if ($mode == 'edit') {
	if ($m_del_id) {
		acp_check_permission('del_media');
		if ($_POST['submit']) {
			$mysql->query("DELETE FROM ".$tb_prefix."data WHERE m_id = '".$m_del_id."'");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
			exit();
		}
		?><form method="post">Do you want to delete this movie ?<br><input value="Yes" name=submit type=submit class=submit></form><?
	}
	elseif ($_POST['do']) {
		$arr = $_POST['checkbox'];
		if (!count($arr)) die('Error');
		if ($_POST['selected_option'] == 'del') {
			acp_check_permission('del_media');
			
			$in_sql = implode(',',$arr);
			$mysql->query("DELETE FROM ".$tb_prefix."data WHERE m_id IN (".$in_sql.")");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
		}
		
		acp_check_permission('edit_media');
		
		if ($_POST['selected_option'] == 'multi_edit') {
			$arr = implode(',',$arr);
			header("Location: ./?act=song_multi_edit&id=".$arr);
		}
		elseif ($_POST['selected_option'] == 'normal') {
			$in_sql = implode(',',$arr);
			$mysql->query("UPDATE ".$tb_prefix."data SET m_is_broken = 0 WHERE m_id IN (".$in_sql.")");
			echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
		}
		exit();
	}
	elseif ($m_id) {
		acp_check_permission('edit_media');
		
		if (!$_POST['submit']) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."data WHERE m_id = '$m_id'");
			if (!$mysql->num_rows($q)) {
				echo "No movies found.";
				exit();
			}
			$r = $mysql->fetch_array($q);
				
			foreach ($inp_arr as $key=>$arr) $$key = $r[$arr['table']];
			$inp_arr['local_url']['checked'] = $local_url;
		}
		else {
			$error_arr = array();
			if (is_array($_POST['cat'])) $_POST['cat'] = "0,".implode(",", $_POST['cat']).",0";
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$inp_arr['title_ascii']['value'] = strtolower(utf8_to_ascii($title));
				$title = ucwords($title);
				$newname = "mov_".str_replace(" ", "_", $inp_arr['title_ascii']['value']);
				$newname = preg_replace("/[^A-Z^a-z^0-9^_]+/", "-", $newname);
				if ($_FILES['picz']['name']!="") {
					require("upload_functions.php");
					$picz = uptoload($_FILES['picz'], "{$newname}");
				} else $picz = $_POST["oldname"];
				$inp_arr['type_media']['value'] = acp_type($url);
				
				if ($new_singer && $singer_type) {
					$singer = acp_quick_add_singer($new_singer,$singer_type);
				}
				if ($new_album) {
        	$album = acp_quick_add_album($new_album,$singer);
        }
				$sql = $form->createSQL(array('UPDATE',$tb_prefix.'data','m_id','m_id'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				if (is_array($_POST['options'])) {
					$q = $mysql->query("SELECT * FROM ".$tb_prefix."options ORDER BY option_order ASC");
					$ii = 0;
					while ($r = $mysql->fetch_array($q)) {
						if ($mysql->fetch_array($mysql->query("SELECT * FROM ".$tb_prefix."options_values WHERE option_id=".$r['option_id']." AND m_id=".$m_id."")))
							$q2 = $mysql->query("UPDATE ".$tb_prefix."options_values SET option_value='".$_POST['options'][$ii]."' WHERE option_id=".$r['option_id']." AND m_id=".$m_id."");
						else $q2 = $mysql->query("INSERT INTO ".$tb_prefix."options_values VALUES (".$r['option_id'].", ".$m_id.", '".$_POST['options'][$ii]."')");
						$ii++;
					}
				}
				echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
		$form->createForm('Edit movie',$inp_arr,$error_arr);
	}
	else {
		acp_check_permission('edit_media');
		
		$m_per_page = 30;
		if (!$pg) $pg = 1;
		$search = urldecode($_GET['search']);
		$extra = (($search)?"m_title_ascii LIKE '%".$search."%' ":'');
		if ($show_broken) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."data WHERE m_is_broken = 1 ".(($extra)?"AND ".$extra." ":'')."ORDER BY m_id DESC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
			$tt = m_get_tt('m_is_broken = 1 '.(($extra)?"AND ".$extra." ":''));
			echo "<a href=?act=song&mode=edit><b>List of movies</b></a><br><br>";
		}
		else {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."data ".(($extra)?"WHERE ".$extra." ":'')."ORDER BY m_id DESC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
			$tt = m_get_tt($extra);
			echo "<a href=".$link."&show_broken=1><b>List of movies is broken</b></a><br><br>";
		}
		if ($mysql->num_rows($q)) {
			if ($search) {
				$link2 = preg_replace("#&search=(.*)#si","",$link);
			}
			else $link2 = $link;
			echo "Movie ID to <b>Edit</b>: <input id=m_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_id=\"+document.getElementById(\"m_id\").value;' value=Edit><br>";
			echo "Movie ID to <b>Delete</b>: <input id=m_del_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_del_id=\"+document.getElementById(\"m_del_id\").value;' value=Delete><br>";
			echo "Search movie : <input id=search size=20 value=\"".$search."\"> <input type=button onclick='window.location.href = \"".$link2."&search=\"+document.getElementById(\"search\").value;' value=Search><br>";
			echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form name=media_list method=post action=$link onSubmit=\"return check_checkbox();\">";
			echo "<tr align=center><td width=3%><input class=checkbox type=checkbox name=chkall id=chkall onclick=docheck(document.media_list.chkall.checked,0) value=checkall></td><td class=title width=60%>Title</td><td class=title>Singer</td><td class=title>Broken</td></tr>";
			while ($r = $mysql->fetch_array($q)) {
				$id = $r['m_id'];
				$title = $r['m_title'];
				$singer = $r['m_singer'];
				$broken = ($r['m_is_broken'])?'<font color=red><b>X</b></font>':'';
				echo "<tr><td><input class=checkbox type=checkbox id=checkbox onclick=docheckone() name=checkbox[] value=$id></td><td class=fr><a href='$link&m_id=".$id."'><b>".$title."</b></a></td><td class=fr_2 align=center><b><a href=?act=singer&mode=edit&singer_id=".$singer.">".m_get_data('SINGER',$singer)."</a></b></td><td align=center>".$broken."</td></tr>";
			}
			echo "<tr><td colspan=3>".admin_viewpages($tt,$m_per_page,$pg)."</td></tr>";
			echo '<tr><td colspan=3 align="center">Do with selected movies : '.
				'<select name=selected_option><option value=multi_edit>Edit</option><option value=del>Delete</option><option value=normal>Broken</option></select>'.
				'<input type="submit" name="do" class=submit value="Do it"></td></tr>';
			echo '</form></table>';
		}
		else echo "No movies found.";
	}
}
?>