<?php
if (!defined('IN_MEDIA_ADMIN')) die("Hacking attempt");
$menu_arr = array(
	'cat'	=>	array(
		'Genres',
		array(
			'edit'	=>	array('Manage genres','act=cat&mode=edit'),
			'add'	=>	array('Add genre','act=cat&mode=add'),
		),
	),
	'media'	=>	array(
		'Movies',
		array(
			'edit'	=>	array('Manage movies','act=song&mode=edit'),
			'edit_broken'	=>	array('Manage movies broken','act=song&mode=edit&show_broken=1'),
			'add'	=>	array('Add movie','act=song&mode=add'),
			//'add_multi'	=>	array('Add multi movies','act=song&mode=multi_add'),
		),
	),
	'options'	=>	array(
		'Movie options',
		array(
			'edit'	=>	array('Manage options','act=option&mode=edit'),
			'add'	=>	array('Add option','act=option&mode=add'),
		),
	),
	'album'	=>	array(
		'Collections',
		array(
			'edit'	=>	array('Manage collections','act=album&mode=edit'),
			'add'	=>	array('Add collection','act=album&mode=add'),
		),
	),
	'user'	=>	array(
		'Members',
		array(
			'edit'	=>	array('Manage users','act=user&mode=edit'),
			'add'	=>	array('Add user','act=user&mode=add'),
		),
	),
	'mod'	=>	array(
		'Modules',
		array(
			'edit'	=>	array('Manage modules','act=mod&mode=edit'),
			'add'	=>	array('Add module','act=mod&mode=add'),
		),
	),
	'link'	=>	array(
		'My partners',
		array(
			'edit'	=>	array('Manage partners','act=ads&mode=edit'),
			'add'	=>	array('Add partner','act=ads&mode=add'),
		),
	),
	'lqcao' => array(
	  'Left Advertisements',
	  array(
	   'edit' => array('Manage','act=lqc&mode=edit'),
	   'add' => array('Add','act=lqc&mode=add'),
	  ),
	 ),
	'rqcao' => array(
	  'Right Advertisements',
	  array(
	   'edit' => array('Manage','act=rqc&mode=edit'),
	   'add' => array('Add','act=rqc&mode=add'),
	  ),
	 ),
	'config'	=>	array(
		'Configures',
		array(
			'set_mod_permission'	=>	array('Moderator permission','act=mod_permission'),
			'config'	=>	array('Config','act=config'),
			'config_server'	=>	array('Config movie server','act=server'),
			'backup_data'	=>	array('Backup database','act=backup'),
			'backup_picz'	=>	array('Zip folder','act=ziper'),
		),
	)
);
if ($level == 2) {

	unset($menu_arr['config']);
	foreach ($menu_arr as $key => $v) {
		if (!$mod_permission['add_'.$key]) unset($menu_arr[$key][1]['add']);
		if (!$mod_permission['edit_'.$key]) unset($menu_arr[$key][1]['edit']);
		
		if ($key == 'media' && !$mod_permission['edit_'.$key]) unset($menu_arr[$key][1]['edit_broken']);
		if ($key == 'media' && !$mod_permission['add_'.$key]) unset($menu_arr[$key][1]['add_multi']);
		
		if (!$menu_arr[$key][1]) unset($menu_arr[$key]);
	}
}
echo "<div><a href='index.php?act=main'><b>Homepage</b></a> | <a href='logout.php'><b>Logout</b></a></div>";
foreach ($menu_arr as $key => $arr) {
	echo "<table cellpadding=2 cellspacing=0 width=100% class=border style='margin-bottom:5'>";
	echo "<tr><td class=title><b>".$arr[0]."</b></td></tr>";
	foreach ($arr[1] as $m_key => $m_val) {
		echo "<tr><td><a href=\"?".$m_val[1]."\">".$m_val[0]."</a></td></tr>";
	}
	echo "</table>";
}
echo "<div class=footer><b>googlevns@hotmail.com</b></div>";
?>