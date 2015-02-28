<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
# ALBUM INFO
if ($value[0] == 'Album' && is_numeric($value[1])) {
	if ($value[1] == 0) {
		ob_end_clean();
		$gvns = "<center><b>No collections found.</b></center>";
		exit();
	}
	$html = $tpl->get_tpl('album_info');
	$t['row'] = $tpl->get_block_from_str($html,'list_row',1);
	$t['begin_tag'] = $tpl->get_block_from_str($html,'begin_tag',1);
	$t['end_tag'] = $tpl->get_block_from_str($html,'end_tag',1);
	$r = $mysql->fetch_array($mysql->query("SELECT * FROM ".$tb_prefix."album WHERE album_id = ".$value[1]));
	$cat_tit = $r['album_name'];
	$album_img = m_get_img('Album',$r['album_img']);
	$html = $tpl->assign_vars($html,
		array(
			'album.ID'		=> $r['album_id'],
			'album.IMG'		=> $album_img,
			'album.NAME'	=> $r['album_name'],
			'album.INFO'	=> m_unhtmlchars($r['album_info']),
			'album.PLAY_URL'	=> 'Play_Album,'.$r['album_id'],
			'singer.URL'	=> 'Singer,'.$r['album_singer'],
			'singer.NAME'	=> m_get_data('SINGER',$r['album_singer']),
			
		)
	);
	$q = $mysql->query("SELECT m_id, m_title, m_type, m_img, m_cat, m_viewed, m_downloaded, IF(m_lyric = '' OR m_lyric IS NULL,0,1) m_lyric FROM ".$tb_prefix."data WHERE m_album = '".$value[1]."' ORDER BY m_viewed DESC");
	if ($mysql->num_rows($q)) {
		$list = '';
		while ($rz = $mysql->fetch_array($q)) {
			static $i = 0;
			$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
			$lyric = ($rz['m_lyric'])?"<img src='{TPL_LINK}/img/media/ok.gif'>":'';
			$cat_t2 = array();
			$qx = $mysql->query("SELECT cat_name FROM ".$tb_prefix."cat WHERE sub_id = 1 AND cat_id IN (".$rz['m_cat'].")");
			while ($cat_t = $mysql->fetch_array($qx))
				$cat_t2[] = $cat_t['cat_name'];
			$rz['cat_name'] = implode(" / ",$cat_t2);
			if ($cat_t = $mysql->fetch_array($mysql->query("SELECT option_value FROM ".$tb_prefix."options_values WHERE option_id = 1 AND m_id = '".$rz['m_id']."'")))
				$rz['m_year'] = " (".$cat_t['option_value'].")";
			switch ($rz['m_type']) {
				case 1 : $media_type = 'music'; break;
				case 2 : $media_type = 'flash'; break;
				case 3 : $media_type = 'movie'; break;
			}
			$apr = 3;
			$media_type = "<img src='{TPL_LINK}/img/media/type/$media_type.gif'>";
			$media_pic = "<img width='120px' alt='".addslashes($rz['m_title']." (".$rz['cat_name'].")")."' src='".$rz['m_img']."'>";
			if ($t['begin_tag'] && fmod($i,$apr) == 0) $list .= $t['begin_tag'];
			$list .= $tpl->assign_vars($t['row'],
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
				)
			);
			if ($t['end_tag'] && fmod($i,$apr) == $apr - 1) $list .= $t['end_tag'];
			$i++;
		}
		if ($t['end_tag'] && fmod($i,$apr) != $apr - 1) $list .= $t['end_tag'];
		$html = $tpl->assign_blocks_content($html,
			array(
				'list'	=>	$list,
			)
		);
	}
	else {
		$html = $tpl->unset_block($html,array('album_songs'));
	}
	//$tpl->parse_tpl($html);
	$gvns = $html;
}
# PLAY ALBUM
elseif ($value[0] == 'Play_Album' && is_numeric($value[1])) {
	if (!$isLoggedIn && m_get_config('must_login_to_play')) {
		$gvns = "<b><center>Have to login to view trailer</center></b>";
		exit();
	}
	$q = $mysql->query("SELECT * FROM ".$tb_prefix."album WHERE album_id = '$value[1]'");
	if (!$mysql->num_rows($q) || $value[1] == 0) {
		ob_end_clean();
		$gvns = "<center><b>No collections found.</b></center>";
		exit();
	}
	$r = $mysql->fetch_array($q);
	$mysql->query("UPDATE ".$tb_prefix."album SET album_viewed = album_viewed + 1 WHERE album_id = '$value[1]'");
	play_album($r);
}
# ALBUM LIST
elseif ($value[0] == 'List_Album') {
	$m_per_page = m_get_config('media_per_page');
	if (!$value[1]) $value[1] = 1;
	$limit = ($value[1]-1)*$m_per_page;
	
	$q = $mysql->query("SELECT * FROM ".$tb_prefix."album ORDER BY album_name ASC LIMIT ".$limit.",".$m_per_page);
	$tt = $mysql->fetch_array($mysql->query("SELECT COUNT(album_id) FROM ".$tb_prefix."album"));
	$tt = $tt[0];
	if ($mysql->num_rows($q)) {
		$cat_tit = 'List of collections';
		$z = $tpl->get_tpl('list_album');
		$t['row'] = $tpl->get_block_from_str($z,'list_row',1);
		$t['begin_tag'] = $tpl->get_block_from_str($z,'begin_tag',1);
		$t['end_tag'] = $tpl->get_block_from_str($z,'end_tag',1);
		
		$html = '';
		while ($r = $mysql->fetch_array($q)) {
			static $i = 0;
			$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
			$apr = 3;
			$singer = m_get_data('SINGER',$r['album_singer']);
			$album_img = m_get_img('Album',$r['album_img']);
			if ($t['begin_tag'] && fmod($i,$apr) == 0) $html .= $t['begin_tag'];
			$html .= $tpl->assign_vars($t['row'],
				array(
					'album.CLASS'	=>	$class,
					'album.IMG'		=>	$album_img,
					'album.URL'		=>	'Album,'.$r['album_id'],
					'album.NAME'	=>	$r['album_name'],
					'singer.NAME'	=>	$singer,
					'singer.URL'	=>	'Singer,'.$r['album_singer'],
					'album.VIEWED'	=>	$r['album_viewed'],
				)
			);
			if ($t['end_tag'] && fmod($i,$apr) == $apr - 1) $html .= $t['end_tag'];
			$i++;
		}
		if ($t['end_tag'] && fmod($i,$apr) != $apr - 1) $html .= $t['end_tag'];
		$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
		$z = $tpl->assign_vars($z,
			array(
				'CLASS' => $class,
				'CAT_TITLE' => $cat_tit,
				'TOTAL'	=> $tt,
				'VIEW_PAGES' => m_viewpages($tt,$m_per_page,$value[1]),
			)
		);
		
		$z = $tpl->assign_blocks_content($z,array(
				'list'	=>	$html,
			)
		);
		
		//$tpl->parse_tpl($z);
		$gvns = $z;
	}
	else $gvns = "<center><b>No collections found.</b></center";
}
?>