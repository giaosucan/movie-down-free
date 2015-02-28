<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
$m_per_page = m_get_config('media_per_page');

$fields = "m_id, m_title, m_cat, m_singer, m_type, m_img, m_viewed, m_downloaded, IF(m_lyric = '' OR m_lyric IS NULL,0,1) m_lyric";
$q = '';
if ($value[0] == 'List') {
	if (!$value[2]) $value[2] = 1;
	if (!is_numeric($value[2]))
    die('<b>Page not exist</b>');
	$page = $value[2];
	$limit = ($page-1)*$m_per_page;
	
	$check = $mysql->fetch_array($mysql->query("SELECT sub_id FROM ".$tb_prefix."cat WHERE cat_id = '$value[1]'"));
	if (!is_null($check['sub_id']) && $check['sub_id'] != 0) {
		$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_cat LIKE '%,".$value[1].",%' ORDER BY m_title ASC LIMIT ".$limit.",$m_per_page";
		$tt = m_get_tt("m_cat LIKE '%,".$value[1].",%'");
	}
	else {
		$list_q = $mysql->query("SELECT cat_id FROM ".$tb_prefix."cat WHERE sub_id = '$value[1]'");
		$in_sql = '';
		while ($list_r = $mysql->fetch_array($list_q)) $in_sql .= "'".$list_r['cat_id']."',";
		$in_sql = substr($in_sql,0,-1);
		if (!$in_sql) $in_sql = -1;
		$q = "SELECT ".$fields." FROM ".$tb_prefix."data WHERE m_cat IN ($in_sql) ORDER BY m_title ASC LIMIT ".$limit.",$m_per_page";
		$tt = m_get_tt("m_cat IN (".$in_sql.")");
	}
}
elseif (in_array($value[0],array('Top_Download','Top_Play'))) {
	if (!$value[1]) $value[1] = 1;
	if (!is_numeric($value[1]))
    die('<b>Page not exist</b>');
	$page = $value[1];
	$limit = ($page-1)*$m_per_page;
	
	if ($value[0] == 'Top_Download') $order = 'm_downloaded';
	elseif ($value[0] == 'Top_Play') $order = 'm_viewed';
	
	$q = "SELECT ".$fields." FROM ".$tb_prefix."data ORDER BY ".$order." DESC LIMIT ".$limit.",$m_per_page";
	$tt = m_get_tt();
}
elseif ($value[0] == 'Home') {
	if (!$value[1]) $value[1] = 1;
	if (!is_numeric($value[1]))
    die('<b>Page not exist</b>');
	$page = $value[1];
	$limit = ($page-1)*$m_per_page;
	
	$q = "SELECT ".$fields." FROM ".$tb_prefix."data ORDER BY m_id DESC LIMIT ".$limit.",$m_per_page";
	$tt = m_get_tt();
}
if ($q) $q = $mysql->query($q);
if ($mysql->num_rows($q)) {
	if ($value[0] == 'List') {
		$cat_tit = $mysql->fetch_array($mysql->query("SELECT cat_name, cat_info, cat_img FROM ".$tb_prefix."cat WHERE cat_id = '$value[1]'"));
		$cat_img = $cat_tit['cat_img'];
		$cat_info = $cat_tit['cat_info'];
		$cat_tit = $cat_tit['cat_name'];		
	}
	elseif ($value[0] == 'Home') $cat_tit = 'Newest movies';
	
	$main = $tpl->get_tpl('list');
	$t['row'] = $tpl->get_block_from_str($main,'list_row',1);
	$t['begin_tag'] = $tpl->get_block_from_str($main,'begin_tag',1);
	$t['end_tag'] = $tpl->get_block_from_str($main,'end_tag',1);
	
	$html = '';
	while ($r = $mysql->fetch_array($q)) {
		static $i = 0;
		$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
		$m_id = $r['m_id'];
		//$m_title = $r['m_title'];
		//$m_viewed = $r['m_viewed'];
		//$m_downloaded = $r['m_downloaded'];
		//$m_singer = $r['m_singer'];
		$cat_t2 = array();
		$qx = $mysql->query("SELECT cat_name FROM ".$tb_prefix."cat WHERE sub_id = 1 AND cat_id IN (".$r['m_cat'].")");
		while ($cat_t = $mysql->fetch_array($qx))
			$cat_t2[] = $cat_t['cat_name'];
		$r['cat_name'] = implode(" / ",$cat_t2);
		if ($cat_t = $mysql->fetch_array($mysql->query("SELECT option_value FROM ".$tb_prefix."options_values WHERE option_id = 1 AND m_id = '".$r['m_id']."'")))
			$r['m_year'] = " (".$cat_t['option_value'].")";
		
		$lyric = ($r['m_lyric'])?"<img src='{TPL_LINK}/img/media/ok.gif'>":'';
		
		$singer = m_get_data('SINGER',$r['m_singer']);
		switch ($r['m_type']) {
			case 1 : $media_type = 'music'; break;
			case 2 : $media_type = 'flash'; break;
			case 3 : $media_type = 'movie'; break;
		}
		$apr = 3;
		$media_type = "<img src='{TPL_LINK}/img/media/type/$media_type.gif'>";
		$media_pic = "<img width='120px' alt='".addslashes($r['m_title']." (".$r['cat_name'].")")."' src='".$r['m_img']."'>";
		if ($t['begin_tag'] && fmod($i,$apr) == 0) $html .= "<tr><td colspan=3 align=center><!-- BOX box_mod('mod_ads".floor(($i+3)/3)."') --></td></tr>".$t['begin_tag'];
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
		if ($t['end_tag'] && fmod($i,$apr) == $apr - 1) $html .= $t['end_tag'];
		$i++;
	}
	if ($t['end_tag'] && fmod($i,$apr) != $apr - 1) $html .= $t['end_tag'];
	$class = (fmod($i,2) == 0)?'m_list':'m_list_2';
	$main = $tpl->assign_vars($main,
		array(
			'CLASS' => $class,
			'CAT_TITLE' => $cat_tit,
			'CAT_IMG' => $cat_img,
			'CAT_INFO' => m_unhtmlchars($cat_info),
			'TOTAL'	=> $tt,
			'VIEW_PAGES' => m_viewpages($tt,$m_per_page,$page),
		)
	);
	
	if (!$cat_info) $main = $tpl->unset_block($main,array('cat_info'));
	$main = $tpl->assign_blocks_content($main,array(
			'list'	=>	$html,
		)
	);
	
	//$tpl->parse_tpl($main);
	$gvns = $main;
}
else $gvns = "<center><b>No movies found.</b></center>";

?>