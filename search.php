<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
$m_per_page = m_get_config('media_per_page');
if (($value[0] == 'Search' || $value[0] == 'Quick_Search') && isset($value[1],$value[2])) {
	if (!$value[3]) $value[3] = 1;
	if (!is_numeric($value[3]))
    die('<b>Page not exist</b>');
	$limit = ($value[3]-1)*$m_per_page;
	$fields = "m_id, m_title, m_singer, m_cat, m_type, m_img, m_viewed, m_downloaded, IF(m_lyric = '' OR m_lyric IS NULL,0,1) m_lyric";
	
	$kw = strtolower(utf8_to_ascii(urldecode($value[2])));
	$s_type = $value[1];
	$value[2] = urldecode($value[2]);
	$q = '';
	if ($s_type == 1) {
		if ($value[0] == 'Search') {
			$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_title_ascii LIKE '%".$kw."%' OR m_lyric LIKE '%".$kw."%' ORDER BY m_title ASC, m_title_ascii ASC LIMIT ".$limit.",".$m_per_page;
			$tt = m_get_tt("m_title LIKE '%".$kw."%' OR m_lyric LIKE '%".$kw."%'");
		}
		else {
			if ($value[2] == "0-9") {
				$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_title_ascii RLIKE '^[0-9]' ORDER BY m_title ASC, m_title_ascii ASC LIMIT ".$limit.",".$m_per_page;
				$tt = m_get_tt("m_title_ascii RLIKE '^[0-9]'");
			}
			else {
				$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_title_ascii LIKE '".$value[2]."%' ORDER BY m_title ASC, m_title_ascii ASC LIMIT ".$limit.",".$m_per_page;
				$tt = m_get_tt("m_title_ascii LIKE '".$value[2]."%'");
			}
		}
	}
	elseif ($s_type == 2) {
		$q = "SELECT DISTINCT m_id FROM ".$tb_prefix."options_values WHERE option_value LIKE '%".$kw."%' ORDER BY m_id ASC";
			$q = $mysql->query($q);
			$a = array();
			$a[] = 0;
			while ($rz = $mysql->fetch_array($q))
				$a[] = $rz['m_id'];
			$k = implode(",", $a);
		$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_id IN (".$k.") ORDER BY m_title ASC, m_title_ascii ASC LIMIT ".$limit.",".$m_per_page;
		$tt = $mysql->fetch_array($mysql->query("SELECT COUNT(DISTINCT m_id) FROM ".$tb_prefix."options_values WHERE option_value LIKE '%".$kw."%'"));
		$tt = $tt[0];
	}
	elseif ($s_type == 3) {
		$q = "SELECT album_name,album_img,album_id,album_singer FROM ".$tb_prefix."album WHERE album_name_ascii LIKE '%".$kw."%' ORDER BY album_name ASC LIMIT ".$limit.",".$m_per_page;
		$tt = $mysql->fetch_array($mysql->query("SELECT COUNT(album_id) FROM ".$tb_prefix."album WHERE album_name_ascii LIKE '%".$kw."%'"));
		$tt = $tt[0];
	}
	if ($q) $q = $mysql->query($q);
	
	if ($mysql->num_rows($q)) {
		$cat_tit = 'Search results';
		
		if ($s_type == 1)
			$file = 'search_song';
		elseif ($s_type == 2)
			$file = 'search_song';
		elseif ($s_type == 3)
			$file = 'search_album';
			
		$z = $tpl->get_tpl($file);
		$t['row'] = $tpl->get_block_from_str($z,'list_row',1);
		$t['begin_tag'] = $tpl->get_block_from_str($z,'begin_tag',1);
		$t['end_tag'] = $tpl->get_block_from_str($z,'end_tag',1);
	
		$html = '';
		while ($r = $mysql->fetch_array($q)) {
			static $i = 0;
			$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
			$apr = 3;
			if ($t['begin_tag'] && fmod($i,$apr) == 0) $html .= "<tr><td colspan=3 align=center><!-- BOX box_mod('mod_ads".floor(($i+3)/3)."') --></td></tr>".$t['begin_tag'];
			if ($s_type == 1) {
				$lyric = ($r['m_lyric'])?"<img src='{TPL_LINK}/img/media/ok.gif'>":'';
				$cat_t2 = array();
				$qx = $mysql->query("SELECT cat_name FROM ".$tb_prefix."cat WHERE sub_id = 1 AND cat_id IN (".$r['m_cat'].")");
				while ($cat_t = $mysql->fetch_array($qx))
					$cat_t2[] = $cat_t['cat_name'];
				$r['cat_name'] = implode(" / ",$cat_t2);
				if ($cat_t = $mysql->fetch_array($mysql->query("SELECT option_value FROM ".$tb_prefix."options_values WHERE option_id = 1 AND m_id = '".$r['m_id']."'")))
					$r['m_year'] = " (".$cat_t['option_value'].")";
				$singer = m_get_data('SINGER',$r['m_singer']);
				switch ($r['m_type']) {
					case 1 : $media_type = 'music'; break;
					case 2 : $media_type = 'flash'; break;
					case 3 : $media_type = 'movie'; break;
				}
				$media_type = "<img src='{TPL_LINK}/img/media/type/".$media_type.".gif'>";
				$media_pic = "<img width='120px' alt='".addslashes($r['m_title']." (".$r['cat_name'].")")."' src='".$r['m_img']."'>";
				$html .= $tpl->assign_vars($t['row'],
					array(
						'song.CLASS' => $class,
						'song.PIC' => $media_pic,
						'song.TYPE' => $media_type,
						'song.ID' => $r['m_id'],
						'song.URL' => 'Play,'.$r['m_id'].','.name_on_bar($r['m_title'],1),
						'song.TITLE' => $r['m_title'],
						'song.TITLE2' => $r['m_year'],
						'song.CAT' => $r['cat_name'],
						'song.VIEWED' => $r['m_viewed'],
						'song.DOWNLOADED' => $r['m_downloaded'],
						'song.LYRIC' => $lyric,
						'singer.NAME' => $singer,
						'singer.URL' => 'Singer,'.$r['m_singer'],
					)
				);
			}
			elseif ($s_type == 2) {
				$cat_t2 = array();
				$qx = $mysql->query("SELECT cat_name FROM ".$tb_prefix."cat WHERE sub_id = 1 AND cat_id IN (".$r['m_cat'].")");
				while ($cat_t = $mysql->fetch_array($qx))
					$cat_t2[] = $cat_t['cat_name'];
				$r['cat_name'] = implode(" / ",$cat_t2);
				if ($cat_t = $mysql->fetch_array($mysql->query("SELECT option_value FROM ".$tb_prefix."options_values WHERE option_id = 1 AND m_id = '".$r['m_id']."'")))
					$r['m_year'] = " (".$cat_t['option_value'].")";
				$media_pic = "<img width='120px' alt='".addslashes($r['m_title']." (".$r['cat_name'].")")."' src='".$r['m_img']."'>";
				$html .= $tpl->assign_vars($t['row'],
					array(
						'song.CLASS' => $class,
						'song.PIC' => $media_pic,
						'song.ID' => $r['m_id'],
						'song.URL' => 'Play,'.$r['m_id'].','.name_on_bar($r['m_title'],1),
						'song.TITLE' => $r['m_title'],
						'song.TITLE2' => $r['m_year'],
						'song.CAT' => $r['cat_name'],
						'song.VIEWED' => $r['m_viewed'],
						'song.DOWNLOADED' => $r['m_downloaded'],
						'song.LYRIC' => $lyric,
						'singer.NAME' => $singer,
						'singer.URL' => 'Singer,'.$r['m_singer'],
					)
				);
			}
			elseif ($s_type == 3) {
				$album_img = m_get_img('Album',$r['album_img']);
				$html .= $tpl->assign_vars($t['row'],
					array(
						'album.CLASS'	=>	$class,
						'album.URL'		=>	'Album,'.$r['album_id'],
						'album.IMG'		=>	$album_img,
						'album.NAME'	=>	$r['album_name'],
						'singer.NAME'	=>	m_get_data('SINGER',$r['album_singer']),
						'singer.URL'	=>	'Singer,'.$r['album_singer'],
					)
				);
			}
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
				'VIEW_PAGES' => m_viewpages($tt,$m_per_page,$value[3]),
			)
		);
		
		$z = $tpl->assign_blocks_content($z,array(
				'list'	=>	$html,
			)
		);
		
		//$tpl->parse_tpl($z);
		$gvns = $z;
	}
	else $gvns = "<center><b>No movies/collections found.</b></center>";
}
?>