<?php
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");

$edit_url = 'index.php?act=album&mode=edit';

$inp_arr = array(
		'album'	=> array(
			'table'	=>	'album_name',
			'name'	=>	'Collection name',
			'type'	=>	'free'
		),
		'picz'	=> array(
			'table'	=>	'album_img',
			'name'	=>	'Collection picture',
			'type'	=>	'file',
			'can_be_empty'	=> true,
		),
		'album_info'	=>	array(
			'table'	=>	'album_info',
			'name'	=>	'Description',
			'type'	=>	'text',
			'can_be_empty'	=>	true,
		),
		'album_ascii'	=>	array(
			'table'	=>	'album_name_ascii',
			'type'	=>	'hidden_value',
			'value'	=>	'',
			'change_on_update'	=>	true,
		),
	);
##################################################
# ADD ALBUM
##################################################
if ($mode == 'add') {
	acp_check_permission('add_album');
	if ($_POST['submit']) {
		$error_arr = array();
		$error_arr = $form->checkForm($inp_arr);
		if (!$error_arr) {
			if ($new_singer && $singer_type) {
				$singer = acp_quick_add_singer($new_singer,$singer_type);
			}
			$inp_arr['album_ascii']['value'] = strtolower(utf8_to_ascii($album));
			$newname = "col_".str_replace(" ", "_", $inp_arr['album_ascii']['value']);
			if ($_FILES['picz']['name']!="") {
				require("upload_functions.php");
				$upload = new FileUpload('en');
				$upload->setMaxFilesize(1500000);
				$upload->setRejectExtensions('psd, tif'); // leave blank or remove to accept all extensions (except .php)
				$upload->setAcceptableTypes('image'); // leave blank or remove to accept all files (except .php)
				$upload->setMaxImageSize(500,500); // width, height
				$upload->setOverwriteMode(1);
				// UPLOAD single file
				$filename = $upload->upload("picz", "../picz/", "{$newname}");
				if ($filename) {
					$picz = $filename;
				}
			}
			$sql = $form->createSQL(array('INSERT',$tb_prefix.'album'),$inp_arr);
			eval('$mysql->query("'.$sql.'");');
			echo "Add successfull <meta http-equiv='refresh' content='0;url=$link'>";
			exit();
		}
	}
	$warn = $form->getWarnString($error_arr);

	$form->createForm('Add collection',$inp_arr,$error_arr);
}
##################################################
# EDIT ALBUM
##################################################
if ($mode == 'edit') {
/*### Start Addon Songs ###*/
    if ($m_del_id) {
        acp_check_permission('del_media');
        if ($_POST['submit']) {
            $mysql->query("DELETE FROM ".$tb_prefix."data WHERE m_id = '".$m_del_id."'");
            echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
            exit();
        }
        ?><form method="post">Do you want to delete this collection ?<input value="Yes" name=submit type=submit class=submit></form><?
    }
    elseif ($_POST['go']) {
        $arr = $_POST['checkbox'];
        if (!count($arr)) die('Error');
        if ($_POST['selected_option'] == 'del') {
            acp_check_permission('del_media');
            
            $in_sql = implode(',',$arr);
            $mysql->query("DELETE FROM ".$tb_prefix."data WHERE m_id IN (".$in_sql.")");
            echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_del."'>";
        }
        
        acp_check_permission('edit_media');
        
        if ($_POST['selected_option'] == 'multi_edit') {
            $arr = implode(',',$arr);
            header("Location: ./?act=song_multi_edit&id=".$arr);
        }
        elseif ($_POST['selected_option'] == 'normal') {
            $in_sql = implode(',',$arr);
            $mysql->query("UPDATE ".$tb_prefix."data SET m_is_broken = 0 WHERE m_id IN (".$in_sql.")");
            echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_del."'>";
        }
        exit();
    }
    elseif ($m_id) {
        acp_check_permission('edit_media');
        header("Location: ./?act=song&mode=edit&m_id=".$m_id);
    }
    /*### End Addon Songs ###*/
	if ($album_del_id) {
		acp_check_permission('del_album');
		if ($_POST['submit']) {
				$mysql->query("DELETE FROM ".$tb_prefix."album WHERE album_id = '".$album_del_id."'");
				$mysql->query("UPDATE ".$tb_prefix."data SET m_album = '' WHERE m_album = '".$album_del_id."'");
				echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
		}
		?>
		<form method="post">Do you want to delete this collection ?<br><input value="Yes" name=submit type=submit class=submit></form>
		<?
	}
	elseif ($_POST['do']) {
		$arr = $_POST['checkbox'];
		if (!count($arr)) die('Error');
		if ($_POST['selected_option'] == 'del') {
			acp_check_permission('del_album');
			$in_sql = implode(',',$arr);
			$mysql->query("DELETE FROM ".$tb_prefix."album WHERE album_id IN (".$in_sql.")");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
		}
	}
	
	elseif ($album_id) {
		acp_check_permission('edit_album');
		if (!$_POST['submit']) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."album WHERE album_id = '".$album_id."'");
			$r = $mysql->fetch_array($q);
			
			foreach ($inp_arr as $key=>$arr) $$key = $r[$arr['table']];
		}
		else {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$inp_arr['album_ascii']['value'] = strtolower(utf8_to_ascii($album));
				$newname = "col_".str_replace(" ", "_", $inp_arr['album_ascii']['value']);
				if ($_FILES['picz']['name']!="") {
					require("upload_functions.php");
					$upload = new FileUpload('en');
					$upload->setMaxFilesize(1500000);
					$upload->setRejectExtensions('psd, tif'); // leave blank or remove to accept all extensions (except .php)
					$upload->setAcceptableTypes('image'); // leave blank or remove to accept all files (except .php)
					$upload->setMaxImageSize(500,500); // width, height
					$upload->setOverwriteMode(1);
					// UPLOAD single file
					$filename = $upload->upload("picz", "../picz/", "{$newname}");
					if ($filename) {
						$picz = $filename;
					} else {
						$picz = '';
						//echo $upload->getError();
					}
				} else $picz = $_POST["oldname"];
				if ($new_singer && $singer_type) {
					$singer = acp_quick_add_singer($new_singer,$singer_type);
				}
				$sql = $form->createSQL(array('UPDATE',$tb_prefix.'album','album_id','album_id'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
		$form->createForm('Edit collection',$inp_arr,$error_arr);
##################################################
# START LIST SONGS OF ALBUM
##################################################
        $m_per_page = 30;
        if (!$pg) $pg = 1;
        $search = strtolower(utf8_to_ascii(urldecode($_GET['search'])));
        $extra = (($search)?"m_title_ascii LIKE '%".$search."%' ":'');
        $q = $mysql->query("SELECT * FROM ".$tb_prefix."data WHERE m_album='".$album_id."' ".(($extra)?"AND ".$extra." ":'')."ORDER BY m_id ASC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
        $tt = m_get_tt("m_album = '".$album_id."'".(($extra)?"AND ".$extra." ":''));

        echo "<a href=".$link."><b>All movies in this collection</b></a><br>";
        if ($mysql->num_rows($q)) {
            if ($search) {
                $link2 = preg_replace("#&search=(.*)#si","",$link);
            }
            else $link2 = $link;
            echo "Movies ID to <b>Edit</b>: <input id=m_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_id=\"+document.getElementById(\"m_id\").value;' value=Edit><br>";
            echo "Movies ID to <b>Delete</b>: <input id=m_del_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_del_id=\"+document.getElementById(\"m_del_id\").value;' value=Delete><br>";
            echo "Search movie : <input id=search size=20 value=\"".$search."\"> <input type=button onclick='window.location.href = \"".$link2."&search=\"+document.getElementById(\"search\").value;' value=Search><br>";
            //echo '<b><font color=red>Ca SÄ© Trong NÆ°á»›c</font> - | - <font color="blue">Ca SÄ© NgoĂ i NÆ°á»›c</font> - | - Unknown</b><b><font color=red>Audio</font> - | - <font color="blue">Movie</font> - | - <font color="Green">Flash</font></b>';
            echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form name=media_list method=post action=$link onSubmit=\"return check_checkbox();\">";
            echo "<tr align=center><td width=3% class=title><input class=checkbox type=checkbox name=chkall id=chkall onclick=docheck(document.media_list.chkall.checked,0) value=checkall></td><td class=title width=40%>Title</td><td class=title>Type</td><td class=title>Genre</td><td class=title>Collection</td><td class=title>Description</td><td class=title>Broken</td><td class=title>Preview</td></tr>";
            while ($r = $mysql->fetch_array($q)) {
                $id = $r['m_id'];
                $title = $r['m_title'];
                $lyric ='';
                $ssinger = m_get_data('ALBUM',$r['m_album']);
                switch ($r['m_type']) {
                    case 1 : $file_type = '<b><font color=red>WMA</font></b>'; break;
                    case 2 : $file_type = '<b><font color=white>FLASH</font></b>'; break;
                    case 3 : $file_type = '<b><font color=green>MOVIE</font></b>'; break;
                    case 4 : $file_type = '<b><font color=orange>MP3</font></b>'; break;
                    case 5 : $file_type = '<b><font color=blue>FLV</font></b>'; break;
                    default : $file_type = ''; break;
                }
                if($r['m_lyric'])
                $lyric = '<img src=ok.gif border=0>';

                $showcat = m_get_data('CAT',$r['m_cat'],'sub_id');
                /*switch ($showcat) {
                    case 0 : break;
                    default :     $mshowcat = m_get_data('CAT',$showcat);
                        $sshowcat = m_get_data('CAT',$r['m_cat']); break;
                }*/

                $broken = ($r['m_is_broken'])?'<font color=red><b>X</b></font>':'';
                echo "<tr><td class=fr><input class=checkbox type=checkbox id=checkbox onclick=docheckone() name=checkbox[] value=$id></td><td class=fr><a href='$link&m_id=".$id."'><b>".$title."</b></a></td><td class=fr_2 align=center>".$file_type."</td><td class=fr_2 align=center><b>".$showcat."".$sshowcat."</b></td><td class=fr_2 align=center><b><a href=?act=album&mode=edit&album_id=".$r['m_album'].">".$ssinger."</a></b></td><td class=fr_2 align=center>".$lyric."</td><td class=fr_2 align=center>".$broken."</td><td class=fr_2 align=center><a href='../#/Play/".$id."/".$r['m_title']."' target=_blank ><img src=play.gif border=0></a></td></tr>";
            }
            echo "<tr><td colspan=7>".admin_viewpages($tt,$m_per_page,$pg)."</td></tr>";
            echo '<tr><td colspan=7 align="center">Do with selected movies : '.
                '<select name=selected_option><option value=multi_edit>Edit</option>
                <option value=del>Delete</option>
                <option value=normal>Broken</option></select>'.
                '<input type="submit" name="go" class=submit value="Do it"></td></tr>';
            echo '</form></table>';
        }
        else echo "No movies found";
##################################################
# END LIST SONGS OF ALBUM
##################################################
	}
	else {
		acp_check_permission('edit_album');
		$m_per_page = 30;
		if (!$pg) $pg = 1;
		
		$q = $mysql->query("SELECT * FROM ".$tb_prefix."album ORDER BY album_name ASC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
		$tt = $mysql->fetch_array($mysql->query("SELECT COUNT(album_id) FROM ".$tb_prefix."album"));
		$tt = $tt[0];
		if ($tt) {
			echo "Collection ID to <b>Edit</b>: <input id=album_id size=20> <input type=button onclick='window.location.href = \"".$link."&album_id=\"+document.getElementById(\"album_id\").value;' value=Edit><br>";
			echo "Collection ID to <b>Delete</b>: <input id=album_del_id size=20> <input type=button onclick='window.location.href = \"".$link."&album_del_id=\"+document.getElementById(\"album_del_id\").value;' value=Delete><br>";
			
			echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form name=media_list method=post action=$link onSubmit=\"return check_checkbox();\">";
			echo "<tr align=center><td width=3%><input class=checkbox type=checkbox name=chkall id=chkall onclick=docheck(document.media_list.chkall.checked,0) value=checkall></td><td class=title width=60%>Collection name</td><td class=title>Picture</td></tr>";
			while ($r = $mysql->fetch_array($q)) {
				$id = $r['album_id'];
				$album = $r['album_name'];
				$img = ($r['album_img'])? "<img src=../".$r['album_img']." width=50px>":'';
				echo "<tr><td><input class=checkbox type=checkbox id=checkbox onclick=docheckone() name=checkbox[] value=$id></td><td class=fr><b><a href=?act=album&mode=edit&album_id=".$id.">".$album."</a></b></td><td class=fr_2 align=center>".$img."</td></tr>";
			}
			echo "<tr><td colspan=3>".admin_viewpages($tt,$m_per_page,$pg)."</td></tr>";
			echo '<tr><td colspan=3 align="center">Do with selected colllections : '.
				'<select name=selected_option><option value=del>Delete</option>'.
				'<input type="submit" name="do" class=submit value="Do it"></td></tr>';
			echo '</form></table>';
		}
		else echo "No collections found";
	}
}
?>
