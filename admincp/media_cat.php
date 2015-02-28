<?
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");

$edit_url = 'index.php?act=cat&mode=edit';

$inp_arr = array(
		'name'	=> array(
			'table'	=>	'cat_name',
			'name'	=>	'Genre name',
			'type'	=>	'free'
		),
		'order'	=> array(
			'table'	=>	'cat_order',
			'name'	=>	'Order',
			'type'	=>	'number',
			'can_be_empty'	=>	true,
		),
		'sub'	=> array(
			'table'	=>	'sub_id',
			'name'	=>	'Genre level',
			'type'	=>	'function::acp_maincat::number'
		),
		'img'	=>	array(
			'table'	=>	'cat_img',
			'name'	=>	'Link of picture',
			'type'	=>	'free',
			'can_be_empty'	=>	true,
		),		'info'	=>	array(
			'table'	=>	'cat_info',
			'name'	=>	'Description',
			'type'	=>	'text',
			'can_be_empty'	=>	true,
		),
	);
##################################################
# ADD MEDIA CAT
##################################################
if ($mode == 'add') {
	acp_check_permission('add_cat');
	if ($_POST['submit']) {
		$error_arr = array();
		$error_arr = $form->checkForm($inp_arr);
		if (!$error_arr) {
			
			$sql = $form->createSQL(array('INSERT',$tb_prefix.'cat'),$inp_arr);
			eval('$mysql->query("'.$sql.'");');
			echo "Add successfull <meta http-equiv='refresh' content='0;url=$link'>";
			exit();
		}
	}
	$warn = $form->getWarnString($error_arr);

	$form->createForm('Add genre',$inp_arr,$error_arr);
}
##################################################
# EDIT MEDIA CAT
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
        ?><form method="post">Do you want to delete ?<input value="Yes" name=submit type=submit class=submit></form><?
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
    acp_check_permission('edit_cat');
    /*### End Addon Songs ###*/	
	acp_check_permission('edit_cat');
	
	if ($cat_id) {
		if (!$_POST['submit']) {
			$q = $mysql->query("SELECT * FROM ".$tb_prefix."cat WHERE cat_id = '$cat_id'");
			$r = $mysql->fetch_array($q);
			
			foreach ($inp_arr as $key=>$arr) $$key = $r[$arr['table']];
		}
		else {
			$error_arr = array();
			$error_arr = $form->checkForm($inp_arr);
			if (!$error_arr) {
				$sql = $form->createSQL(array('UPDATE',$tb_prefix.'cat','cat_id','cat_id'),$inp_arr);
				eval('$mysql->query("'.$sql.'");');
				echo "Edit successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
				exit();
			}
		}
		$warn = $form->getWarnString($error_arr);
		$form->createForm('Edit genre',$inp_arr,$error_arr);
##################################################
# START LIST SONGS OF CAT
##################################################
        $m_per_page = 30;
        if (!$pg) $pg = 1;
        $search = strtolower(utf8_to_ascii(urldecode($_GET['search'])));
        $extra = (($search)?"m_title_ascii LIKE '%".$search."%' ":'');
        if (m_get_data('CAT',$cat_id,'sub_id')) {
        $q = $mysql->query("SELECT * FROM ".$tb_prefix."data WHERE m_cat='".$cat_id."' ".(($extra)?"AND ".$extra." ":'')."ORDER BY m_id DESC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
        $tt = m_get_tt("m_cat = '".$cat_id."'".(($extra)?"AND ".$extra." ":''));
        }
        else
        {
        $tim_sub_cat = $mysql->query("SELECT cat_id FROM ".$tb_prefix."cat WHERE sub_id ='".$cat_id."' ORDER BY cat_order, cat_name ASC");
            while ($find_subcat = $mysql->fetch_array($tim_sub_cat)) {
                if (!$allsubcatid)
                    $allsubcatid = $find_subcat['cat_id'];
                else
                    $allsubcatid = $allsubcatid.','.$find_subcat['cat_id'];
            }
        $q = $mysql->query("SELECT * FROM ".$tb_prefix."data WHERE m_cat IN (".$allsubcatid.") ".(($extra)?"AND ".$extra." ":'')."ORDER BY m_id DESC LIMIT ".(($pg-1)*$m_per_page).",".$m_per_page);
        $tt = m_get_tt("m_cat IN (".$allsubcatid.")".(($extra)?"AND ".$extra." ":''));
        }

        echo "<a href=".$link."><b>All movies in this genre</b></a><br>";
        if ($mysql->num_rows($q)) {
            if ($search) {
                $link2 = preg_replace("#&search=(.*)#si","",$link);
            }
            else $link2 = $link;
            echo "Movies ID to <b>edit</b>: <input id=m_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_id=\"+document.getElementById(\"m_id\").value;' value=Edit><br>";
            echo "Movies ID to <b>delete</b>: <input id=m_del_id size=20> <input type=button onclick='window.location.href = \"".$link."&m_del_id=\"+document.getElementById(\"m_del_id\").value;' value=Delete><br>";
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
        else echo "Not found";
##################################################
# END LIST SONG OF CAT
##################################################
	}
	else {
		if ($_POST['sbm']) {
			$z = array_keys($_POST);
			$q = $mysql->query("SELECT cat_id FROM ".$tb_prefix."cat");
			for ($i=0;$i<$mysql->num_rows($q);$i++) {
				$id = split('o',$z[$i]);
				$od = ${$z[$i]};
				$mysql->query("UPDATE ".$tb_prefix."cat SET cat_order = '$od' WHERE cat_id = '".$id[1]."'");
			}
		}
		echo "<script>function check_del(id) {".
		"if (confirm('Do you want to delete this genre ?')) location='?act=cat&mode=del&cat_id='+id;".
		"return false;}</script>";
		echo "<table width=90% align=center cellpadding=2 cellspacing=0 class=border><form method=post>";
		echo "<tr><td align=center class=title width=5%>Order</td><td class=title style='border-right:0'>Genre name</td></tr>";
		$cat_query = $mysql->query("SELECT * FROM ".$tb_prefix."cat WHERE (sub_id IS NULL OR sub_id = 0) ORDER BY cat_order, cat_name ASC");
		while ($cat = $mysql->fetch_array($cat_query)) {
			echo "<tr align=center><td colspan=2 class=cat_title>".$cat['cat_title']."</td></tr>";
			$iz = $cat['cat_order'];
			echo "<tr><td align=center class=fr><input onclick=this.select() type=text name='o".$cat['cat_id']."' value=$iz size=2 style='text-align:center'></td><td class=fr_2><a href=# onclick=check_del(".$cat['cat_id'].")>Delete</a> - <a href='$link&cat_id=".$cat['cat_id']."'><b>".$cat['cat_name']."</b></a></td></tr>";
			$sub_query = $mysql->query("SELECT * FROM ".$tb_prefix."cat WHERE sub_id = '".$cat['cat_id']."' ORDER BY cat_order, cat_name ASC");
			if ($mysql->num_rows($sub_query)) echo "<tr><td class=fr_2>&nbsp;</td><td class=fr><table width=100% cellpadding=2 cellspacing=0 class=border>";
			while ($sub = $mysql->fetch_array($sub_query)) {
				$s_o = $sub['cat_order'];
				echo "<tr><td align=center class=fr width=5%><input onclick=this.select() type=text name='o".$sub['cat_id']."' value=$s_o size=2 style='text-align:center'></td><td class=fr_2><a href=# onclick=check_del(".$sub['cat_id'].")>Delete</a> - <a href='$link&cat_id=".$sub['cat_id']."'><b>".$sub['cat_name']."</b></a></td></tr>";
			}
			if ($mysql->num_rows($sub_query)) echo "</table></td></tr>";
		}
		echo '<tr><td colspan="2" align="center"><input type="submit" name="sbm" class=submit value="Edit order"></td></tr>';
		echo '</form></table>';
	}
	
}
##################################################
# DELETE MEDIA CAT
##################################################
if ($mode == 'del') {
	acp_check_permission('del_cat');
	if ($cat_id) {
		if ($_POST['submit']) {
			$mysql->query("DELETE FROM ".$tb_prefix."cat WHERE cat_id = '".$cat_id."'");
			echo "Delete successfull <meta http-equiv='refresh' content='0;url=".$edit_url."'>";
			exit();
		}
		?>
		<form method="post">Do you want to delete this genre ?<br><input value="Yes" name=submit type=submit class=submit></form>
<?
	}
}
?>