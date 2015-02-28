<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
$q = "SELECT * FROM ".$tb_prefix."mod ORDER BY mod_name DESC";

if ($q) $q = $mysql->query($q);
if ($mysql->num_rows($q)) {
if ($value[0] == 'Mod_About') {
	$cat_tit = 'About Us';	
	$main = $tpl->get_tpl('mod_about');
	$t['row'] = $tpl->get_block_from_str($main,'list_row',1);
	
	$html = '';
	while ($r = $mysql->fetch_array($q)) {
		if ($r['mod_name']=='mod_about')
		$html .= $tpl->assign_vars($t['row'],
			array(
				'mod.INFO' => m_unhtmlchars($r['mod_value']),
			)
		);
	}
}
elseif ($value[0] == 'Mod_How') {
	$cat_tit = 'How to download';	
	$main = $tpl->get_tpl('mod_about');
	$t['row'] = $tpl->get_block_from_str($main,'list_row',1);
	
	$html = '';
	while ($r = $mysql->fetch_array($q)) {
		if ($r['mod_name']=='mod_how')
		$html .= $tpl->assign_vars($t['row'],
			array(
				'mod.INFO' => m_unhtmlchars($r['mod_value']),
			)
		);
	}
}
elseif ($value[0] == 'Mod_Adv') {
	$cat_tit = 'For advertising';	
	$main = $tpl->get_tpl('mod_about');
	$t['row'] = $tpl->get_block_from_str($main,'list_row',1);
	
	$html = '';
	while ($r = $mysql->fetch_array($q)) {
		if ($r['mod_name']=='mod_adv')
		$html .= $tpl->assign_vars($t['row'],
			array(
				'mod.INFO' => m_unhtmlchars($r['mod_value']),
			)
		);
	}
}

	$main = $tpl->assign_vars($main,
		array(
			'TITLE' => $cat_tit,
		)
	);
	
	$main = $tpl->assign_blocks_content($main,array(
			'list'	=>	$html,
		)
	);

	//$tpl->parse_tpl($main);
	$gvns = $main;
}

else $gvns = "<center><b>No page exist.</b></center>";

?>