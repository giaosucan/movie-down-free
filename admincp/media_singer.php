<?
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");

$edit_url = 'index.php?act=singer&mode=edit';

$inp_arr = array(
		'singer'	=> array(
			'table'	=>	'singer_name',
			'name'	=>	'Tên ca sỹ',
			'type'	=>	'free'
		),
		'img'	=> array(
			'table'	=>	'singer_img',
			'name'	=>	'Hình ca sỹ',
			'type'	=>	'free',
			'can_be_empty'	=> true,
		),
		'singer_type'	=>	array(
			'table'	=>	'singer_type',
			'name'	=>	'Loại',
			'type'	=>	'function::acp_singer_type::number',
		),
		'singer_info'	=>	array(
			'table'	=>	'singer_info',
			'name'	=>	'Thông tin',
			'type'	=>	'text',
			'can_be_empty'	=>	true,
		),
		'singer_name_ascii'	=>	array(
			'table'	=>	'singer_name_ascii',
			'type'	=>	'hidden_value',
			'value'	=>	'',
			'change_on_update'	=>	true,
		),
	);
##################################################
# ADD SINGER
##################################################
if ($mode == 'add') {
	if ($level == 2 && !$mod_permission['add_singer']) echo 'Bạn không được quyền vào trang này';
	else {
		if ($_POST['submit']) {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$inp_arr['singer_name_ascii']['value'] = strtolower(utf8_to_ascii($album));
				$sql = $form->createSQL(array('INSERT',$tb_prefix.'singer'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Đã thêm xong <meta http-equiv='refresh' content='0;url=$link'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
	
		$form->createForm('Thêm Ca sỹ',$inp_arr,$error_arr);
	}
}
##################################################
# EDIT SINGER
##################################################
if ($mode == 'edit') {
    /*### Start Addon Songs ###*/
    if ($m_del_id) {
        acp_check_permission('del_media');
        if ($_POST['submit']) {
            $mysql->query("DELETE FROM ".$tb_prefix."data WHERE m_id = '".$m_del_id."'");
            echo " Đã xoá xong <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
            exit();
        }
        ?><form method="post">Bạn có muốn xoá không ??????<input value="Có" name=submit type=submit class=submit></form><?
    }
    elseif ($_POST['go']) {
        $arr = $_POST['checkbox'];
        if (!count($arr)) die('Lỗi');
        if ($_POST['selected_option'] == 'del') {
            acp_check_permission('del_media');
            
            $in_sql = implode(',',$arr);
            $mysql->query("DELETE FROM ".$tb_prefix."data WHERE m_id IN (".$in_sql.")");
            echo "Đã xóa xong <meta http-equiv='refresh' content='0;url=".$edit_del."'>";
        }
        
        acp_check_permission('edit_media');
        
        if ($_POST['selected_option'] == 'multi_edit') {
            $arr = implode(',',$arr);
            header("Location: ./?act=song_multi_edit&id=".$arr);
        }
        elseif ($_POST['selected_option'] == 'normal') {
            $in_sql = implode(',',$arr);
            $mysql->query("UPDATE ".$tb_prefix."data SET m_is_broken = 0 WHERE m_id IN (".$in_sql.")");
            echo "Đã sử­a xong <meta http-equiv='refresh' content='0;url=".$edit_del."'>";
        }
        exit();
    }
    elseif ($m_id) {
        acp_check_permission('edit_media');
        header("Location: ./?act=song&mode=edit&m_id=".$m_id);
    }
    /*### End Addon Songs ###*/
	if ($singer_del_id) {
		acp_check_permission('del_singer');
		if ($_POST['submit']) {
			$mysql->query("DELETE FROM ".$tb_prefix."singer WHERE singer_id = '".$singer_del_id."'");
			echo "Đã xóa xong <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
			exit();
		}
		?>
		<form method="post">Bạn có muốn xóa không ??????<br><input value="Có" name=submit type=submit class=submit></form>
		<?
	}
	elseif ($_POST['do']) {
		$arr = $_POST['checkbox'];
		if (!count($arr)) die('Lỗi');
		if ($_POST['selected_option'] == 'del') {
			acp_check_permission('del_singer');
			$in_sql = implode(',',$arr);
			$mysql->query("DELETE FROM ".$tb_prefix."singer WHERE singer_id IN (".$in_sql.")");
			echo "Đã xóa xong <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
		}
	}
	elseif ($singer_id) {
		acp_check_permission('edit_singer');
		if (!$_POST['submit']) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."singer WHERE singer_id = '$singer_id'");
			$r = $mysql->fetch_array($q);
			
			foreach ($inp_arr as $key=>$arr) $$key = $r[$arr['table']];
		}
		else {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$inp_arr['singer_name_ascii']['value'] = strtolower(utf8_to_ascii($singer));

				$sql = $form->createSQL(array('UPDATE',$tb_prefix.'singer','singer_id','singer_id'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Đã sửa xong <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
		$form->createForm('Sửa ca sỹ',$inp_arr,$error_arr);
##################################################
# START LIST SONGS OF SINGER
##################################################
        $m_per_page = 30;
        if (!$pg) $pg = 1;
        $search = strtolower(utf8_to_ascii(urldecode($_GET['search'])));
        $extra = (($search)?"m_title_ascii LIKE '%".$search."%' ":'');
        $q = $mysql->query("SELECT * FROM ".$tb_prefix."data WHERE m_singer='".$singer_id."' ".(($extra)?"AND ".$extra." ":'')."ORDER BY m_id DESC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
        $tt = m_get_tt("m_singer = '".$singer_id."'".(($extra)?"AND ".$extra." ":''));

        echo "<a href=".$link."><b>Danh sách toàn bộ Media</b></a>";
        if ($mysql->num_rows($q)) {
            if ($search) {
                $link2 = preg_replace("#&search=(.*)#si","",$link);
            }
            else $link2 = $link;
            echo "ID của Media cần <b>sử­a</b>: <input id=m_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_id=\"+document.getElementById(\"m_id\").value;' value=Sửa>";
            echo "ID của Media cần <b>xóa</b>: <input id=m_del_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_del_id=\"+document.getElementById(\"m_del_id\").value;' value=Xóa>";
            echo "Tìm Media : <input id=search size=20 value=\"".$search."\"> <input type=button onclick='window.location.href = \"".$link2."&search=\"+document.getElementById(\"search\").value;' value=Tìm>";
            echo '<b><font color=red>Ca Sĩ Trong Nước</font> - | - <font color="blue">Ca Sĩ Ngoài Nước</font> - | - Unknown</b><b><font color=red>Audio</font> - | - <font color="blue">Movie</font> - | - <font color="Green">Flash</font></b>';
            echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form name=media_list method=post action=$link onSubmit=\"return check_checkbox();\">";
            echo "<tr align=center><td width=3% class=title><input class=checkbox type=checkbox name=chkall id=chkall onclick=docheck(document.media_list.chkall.checked,0) value=checkall></td><td class=title width=50%>Tên Media</td><td class=title>Type</td><td class=title width=20%>Genre</td><td class=title>Ca sĩ</td><td class=title>Lời</td><td class=title>Lời</td><td class=title>Play</td></tr>";
            while ($r = $mysql->fetch_array($q)) {
                $id = $r['m_id'];
                $title = $r['m_title'];
                $lyric ='';
                $ssinger = m_get_data('SINGER',$r['m_singer']);
                $zsinger_type = m_get_data('SINGER',$r['m_singer'],'singer_type');
                switch ($zsinger_type) {
                    case 1 : $zsinger = '<font color=red>'.$ssinger.'</font>'; break;
                    case 2 : $zsinger = '<font color=blue>'.$ssinger.'</font>'; break;
                    default : $zsinger = '<font color=black>'.$ssinger.'</font>'; break;
                }    
                switch ($r['m_type']) {
                    case 1 : $file_type = '<b><font color=red>WMA</font></b>'; break;
                    case 2 : $file_type = '<b><font color=white>FLASH</font></b>'; break;
                    case 3 : $file_type = '<b><font color=green>MOVIE</font></b>'; break;
                    case 4 : $file_type = '<b><font color=orange>MP3</font></b>'; break;
                    case 5 : $file_type = '<b><font color=blue>FLV</font></b>'; break;
                    default : $file_type = ''; break;
                }
                if($r['m_lyric'])
                $lyric = '<img src=/templates/black/img/media/ok.gif border=0>';

                $showcat = m_get_data('CAT',$r['m_cat'],'sub_id');
                switch ($showcat) {
                    case 0 : break;
                    default :     $mshowcat = m_get_data('CAT',$showcat);
                        $sshowcat = m_get_data('CAT',$r['m_cat']); break;
                }    


                $broken = ($r['m_is_broken'])?'<font color=red><b>X</b></font>':'';
                echo "<tr><td class=fr><input class=checkbox type=checkbox id=checkbox onclick=docheckone() name=checkbox[] value=$id></td><td class=fr><a href='$link&m_id=".$id."'><b>".$title."</b></a></td><td class=fr_2 align=center>".$file_type."</td><td class=fr_2 align=center><b>".$mshowcat."".$sshowcat."</b></td><td class=fr_2 align=center><b><a href=?act=singer&mode=edit&singer_id=".$r['m_singer'].">".$zsinger."</a></b></td><td class=fr_2 align=center>".$lyric."</td><td class=fr_2 align=center>".$broken."</td><td class=fr_2 align=center><a href='../#/Play/".$id."/".$r['m_title']."' target=_blank ><img src=templates/black/img/media/play.gif border=0></a></td></tr>";
            }
            echo "<tr><td colspan=7>".admin_viewpages($tt,$m_per_page,$pg)."</td></tr>";
            echo '<tr><td colspan=7 align="center">Vá»›i nhá»¯ng Media Ä‘Ă£ chá»n : '.
                '<select name=selected_option><option value=multi_edit>Sá»­a</option>
                <option value=del>XĂ³a</option>
                <option value=normal>ThĂ´i bĂ¡o Link há»ng</option></select>'.
                '<input type="submit" name="go" class=submit value="Thá»±c hiá»‡n"></td></tr>';
            echo '</form></table>';
        }
        else echo "KhĂ´ng cĂ³ Media nĂ o";
##################################################
# END LIST SONGS OF SINGER
##################################################
	}
	else {
		acp_check_permission('edit_singer');
		$m_per_page = 30;
		if (!$pg) $pg = 1;
		
		$q = $mysql->query("SELECT * FROM ".$tb_prefix."singer ORDER BY singer_name ASC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
		$tt = $mysql->fetch_array($mysql->query("SELECT COUNT(singer_id) FROM ".$tb_prefix."singer"));
		$tt = $tt[0];
		if ($tt) {
			echo "ID của ca sỹ cần <b>sửa</b>: <input id=singer_id size=20> <input type=button onclick='window.location.href = \"".$link."&singer_id=\"+document.getElementById(\"singer_id\").value;' value=Sửa><br><br>";
			echo "ID của ca sỹ cần <b>xóa</b>: <input id=singer_del_id size=20> <input type=button onclick='window.location.href = \"".$link."&singer_del_id=\"+document.getElementById(\"singer_del_id\").value;' value=Xóa><br><br>";
			
			echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form name=media_list method=post action=$link onSubmit=\"return check_checkbox();\">";
			echo "<tr align=center><td width=3%><input class=checkbox type=checkbox name=chkall id=chkall onclick=docheck(document.media_list.chkall.checked,0) value=checkall></td><td class=title width=60%>Tên Ca sỹ</td><td class=title>Ảnh</td></tr>";
			while ($r = $mysql->fetch_array($q)) {
				$id = $r['singer_id'];
				$singer = $r['singer_name'];
				$img = ($r['singer_img'])?"<img src=".$r['singer_img']." width=50 height=50>":'';
				echo "<tr><td><input class=checkbox type=checkbox id=checkbox onclick=docheckone() name=checkbox[] value=$id></td><td class=fr><b><a href=?act=singer&mode=edit&singer_id=".$id.">".$singer."</a></b></td><td class=fr_2 align=center>".$img."</td></tr>";
			}
			echo "<tr><td colspan=3>".admin_viewpages($tt,$m_per_page,$pg)."</td></tr>";
			echo '<tr><td colspan=3 align="center">Với những ca sỹ đã chọn : '.
				'<select name=selected_option><option value=del>Xóa</option>'.
				'<input type="submit" name="do" class=submit value="Thực hiện"></td></tr>';
			echo '</form></table>';
		}
		else echo "Không có ca sỹ nào";
	}
}
?>