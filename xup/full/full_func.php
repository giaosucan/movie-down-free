<?
function get_info($f, $_REQUEST)
{
	global $descr;
	
	if($f == lang('My computer'))
	{
		$_RESULT = array(
		'name' => 'details',
		'filename' => $f,
		'dir' => false,
		'type' => lang('System folder'),
		);
	}else if(in_array($_REQUEST['type'],array(tDIR,tFILE)))
	{
		$ext=pathinfo($f); @$ext=strtolower($ext['extension']);
		$img=in_array($ext,array('jpeg','jpe','gif','png','jpg'));
		
		//echo $f;
		
		$_RESULT=array(
		'name' => 'details',
		'filename' => basename($f),
		'filename_encoded' => rawurlencode($f),
		'md5(filename)' => md5($f),
		'dir'  => d_is_dir($f),
		'type' => (d_is_dir($f) ? false : get_type($f)),
		'changed' => ((@$t=filemtime($f)) ? date('d F Y, H:i',$t) : false),
		'size' => ( (d_is_dir($f) && !SHOW_DIRSIZE) ? (/*no subdirectories*/!empty($GLOBALS['files']) && sizeof($GLOBALS['dirs'])==0 && !empty($GLOBALS['sz']) ? show_size(true,true,$GLOBALS['sz']) : false) : show_size($f)),
		'size_bytes' => d_filesize($f),
		'thumb' => ($img ? '<div style="padding-bottom: 10px;" align="center"><img src="system/preview.php?file='.rawurlencode($f).'&size=small" align="center"></div>' : false),
		'id3' => (($img && @$sz=getimagesize($f)) ? 'Dimensions: '.$sz[0].'x'.$sz[1] : ''),
		'owner' => get_owner($f),
		'group' => get_group($f),
		'rights' => d_get_rights($f, false),
		);
	}else if($_REQUEST['type']==tDRIVE)
	{
		$_RESULT=array(
		'name' => 'details',
		'filename' => !empty($_REQUEST['name']) ? $_REQUEST['name'] : $_SESSION['DIR'],
		'type' => $descr[$_REQUEST['icon']],
		'dir' => false,
		);
		if(!empty($_REQUEST['fs'])) $_RESULT['fs'] = $_REQUEST['fs'];
		if($s = disk_free_space($f)) $_RESULT['free']=show_size(true,true,$s);
		if($s = disk_total_space($f)) $_RESULT['total']=show_size(true,true,$s);
	}
	
	return $_RESULT;
}

// @@ description for drive types

$descr = array( 'diskette' => lang('Diskette 3.5'), 'removable' => lang('Removable device'), 'hdd' => lang('Local disk'), 'smb' => lang('Network drive'), 'cd' => lang('CD/DVD drive'), 'ramdrive' => lang('Ramdrive'), 'unknown' => '', );

/* @@ constants to set the type of item to draw */

// 0x - filesystem
define('tDIR',0);
define('tFILE',1);
define('tDRIVE',2);
define('tMYCOMP',3);
// 1x - special
define('tPANEL_ITEM',10);

/* MISC constants */
define('JS_MAX_ITEMS', 200); /* changing of this value will require JS recompilation */
?>