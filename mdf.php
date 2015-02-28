<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");

if ($value[0] == 'Play' && is_numeric($value[1]) && $value[2]) {
	if (!$isLoggedIn && m_get_config('must_login_to_play')) {
		die("<b><center>Login to view movie trailers</center></b>");
	}
	$zr_url = strtolower($value[2]);
  $zext = explode('.',$zr_url);
  $zext = $zext[count($zext)-1];
  $zext = explode('?',$zext);
  $zext = $zext[0];
	if (m_get_config('must_login_to_play'))
	 {
	 $userid = $_SESSION['user_id'];
	 if ($userid)
	 {
	     $khongyeu = $mysql->query("SELECT * FROM ".$tb_prefix."user WHERE user_id IN (".$userid.")");
	             while ($z3rol0ve = $mysql->fetch_array($khongyeu)) {
	 $uractivated = $z3rol0ve['user_activated'];
	 }
	     if ($isLoggedIn && !$uractivated) {
	die("<b><center>Have to active to view movie trailers</center></b>");
	     }
	 }
	 }
	$q = $mysql->query("SELECT * FROM ".$tb_prefix."data WHERE m_id = '$value[1]'");
	if (!$mysql->num_rows($q)) {
		die("<center><b>No movies found.</b></center>");
	}
	$r = $mysql->fetch_array($q);
	if (name_on_bar($r['m_title']) <> str_replace ( '.html', '', $value[2]))
    die("<center><b>No movies found.</b></center>"); 
	$mysql->query("UPDATE ".$tb_prefix."data SET m_viewed = m_viewed + 1, m_viewed_month = m_viewed_month + 1 WHERE m_id = '$value[1]'");
	$gvns = m_play($r);
	$cat_tit = $r['m_title'];
    $slimit = 10;
if ($r['m_album'] <> 0) {
    $zer = $r['m_album'];
    $main = $tpl->get_tpl('relate_album');
    $t['row'] = $tpl->get_block_from_str($main,'list_row',1);
    $t['begin_tag'] = $tpl->get_block_from_str($main,'begin_tag',1);
		$t['end_tag'] = $tpl->get_block_from_str($main,'end_tag',1);
    
    $html = '';
    $q = $mysql->query("SELECT m_id, m_title, m_cat, m_img, m_title_ascii, m_type, m_viewed, m_album, m_downloaded, IF(m_lyric = '' OR m_lyric IS NULL,0,1) m_lyric FROM ".$tb_prefix."data WHERE m_album = '".$zer."'  ORDER BY RAND() LIMIT ".$slimit);
    while ($rz = $mysql->fetch_array($q)) {
        static $i = 0;
        $class = (fmod($i,2) == 0)?'m_list':'m_list_2';
        $m_id = $rz['m_id'];
				$cat_t2 = array();
				$qx = $mysql->query("SELECT cat_name FROM ".$tb_prefix."cat WHERE sub_id = 1 AND cat_id IN (".$rz['m_cat'].")");
				while ($cat_t = $mysql->fetch_array($qx))
					$cat_t2[] = $cat_t['cat_name'];
				$rz['cat_name'] = implode(" / ",$cat_t2);
				if ($cat_t = $mysql->fetch_array($mysql->query("SELECT option_value FROM ".$tb_prefix."options_values WHERE option_id = 1 AND m_id = '".$rz['m_id']."'")))
					$rz['m_year'] = " (".$cat_t['option_value'].")";
        
        $lyric = ($rz['m_lyric'])?"<img src='{TPL_LINK}/img/media/ok.gif'>":'';
        
        switch ($rz['m_type']) {
            case 1 : $media_type = 'wma'; break;
            case 2 : $media_type = 'flash'; break;
            case 3 : $media_type = 'movie'; break;
            case 4 : $media_type = 'mp3'; break;
            case 5 : $media_type = 'flv'; break;
        }
        $apr = 3;
        $media_type = "<img src='{TPL_LINK}/img/media/type/$media_type.gif' border='0'>";
        $media_pic = "<img width='120px' alt='".addslashes($rz['m_title']." (".$rz['cat_name'].")")."' src='".$rz['m_img']."'>";
				if ($t['begin_tag'] && fmod($i,$apr) == 0) $html .= $t['begin_tag'];
        $html .= $tpl->assign_vars($t['row'],
            array(
                'song.CLASS' => $class,
                'song.PIC' => $media_pic,
                'song.TYPE' => $media_type,
                'song.ID' => $rz['m_id'],
                'song.URL' => 'Play,'.$rz['m_id'].','.name_on_bar($rz['m_title'],1),
                'song.TITLE' => $rz['m_title'],
                'song.TITLE2' => $rz['m_year'],
								'song.CAT' => $rz['cat_name'],
                'song.VIEWED' => $rz['m_viewed'],
                'song.DOWNLOADED' => $rz['m_downloaded'],
                'song.LYRIC' => $lyric,
                'album.URL' => 'Album,'.$rz['m_album'],
            )
        );
        if ($t['end_tag'] && fmod($i,$apr) == $apr - 1) $html .= $t['end_tag'];
        $i++;
    }
    $class = (fmod($i,2) == 0)?'m_list':'m_list_2';
    if ($t['end_tag'] && fmod($i,$apr) != $apr - 1) $html .= $t['end_tag'];
    $main = $tpl->assign_vars($main,
            array(
                'CLASS' => $class,
                'album.URL' => 'Album,'.$r['m_album'],
            )
        );
    $main = $tpl->assign_blocks_content($main,array(
            'list'    =>    $html,
        )
    );
    
    //$tpl->parse_tpl($main);
    $gvns .= $main;
}

if ($r['m_singer']) {
    $zer = $r['m_singer'];
    $main = $tpl->get_tpl('relate_singer');
    $t['row'] = $tpl->get_block_from_str($main,'list_row',1);

    $html = '';
    $q = $mysql->query("SELECT m_id, m_title, m_title_ascii, m_type, m_viewed, m_downloaded, m_singer, IF(m_lyric = '' OR m_lyric IS NULL,0,1) m_lyric FROM ".$tb_prefix."data WHERE m_singer = '".$zer."' ORDER BY RAND() LIMIT ".$slimit);
    while ($rz = $mysql->fetch_array($q)) {
        static $i = 0;
        $class = (fmod($i,2) == 0)?'m_list':'m_list_2';
        $m_id = $rz['m_id'];
        
        $lyric = ($rz['m_lyric'])?"<img src='{TPL_LINK}/img/media/ok.gif'>":'';
        
        switch ($rz['m_type']) {
            case 1 : $media_type = 'wma'; break;
            case 2 : $media_type = 'flash'; break;
            case 3 : $media_type = 'movie'; break;
            case 4 : $media_type = 'mp3'; break;
            case 5 : $media_type = 'flv'; break;
        }
        $media_type = "<img src='{TPL_LINK}/img/media/type/$media_type.gif' border='0'>";
        $html .= $tpl->assign_vars($t['row'],
            array(
                'song.CLASS' => $class,
                'song.TYPE' => $media_type,
                'song.ID' => $rz['m_id'],
                'song.URL' => 'Play,'.$rz['m_id'].','.name_on_bar($rz['m_title'],1),
                'song.TITLE' => $rz['m_title'],
                'song.VIEWED' => $rz['m_viewed'],
                'song.DOWNLOADED' => $rz['m_downloaded'],
                'song.LYRIC' => $lyric,
                'singer.URL' => 'Singer,'.$rz['m_singer'],
            )
        );
        $i++;
    }
    $class = (fmod($i,2) == 0)?'m_list':'m_list_2';
    $main = $tpl->assign_vars($main,
            array(
                'CLASS' => $class,
                'singer.URL' => 'Singer,'.$r['m_singer'],
            )
        );
    $main = $tpl->assign_blocks_content($main,array(
            'list'    =>    $html,
        )
    );
    
    //$tpl->parse_tpl($main);
    $gvns .= $main;
}
}
?>