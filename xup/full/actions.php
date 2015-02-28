<?
// the file of actions of FULL version

// all actions are JsHttpRequest backends

if(!function_exists('dolphin_handler')) die('dolphin not found');

ajax_start_transfer();

$fz=array(); /* filez :) */
$f=false;

if(!empty($_REQUEST['file'])) $fz[]=$f=clean($_REQUEST['file']);
if(!empty($_REQUEST['fullpath'])) $fz[]=$f=clean($_REQUEST['fullpath']);
if(!empty($_REQUEST['items']))
{
	foreach($_REQUEST['items'] as $v) $fz[]=clean($v['fullpath']);
	$f=$fz[0];
}
set_magic_quotes_runtime(0);

/*
if(get_magic_quotes_gpc()) ...
*/

if(@$_REQUEST['act'] == 'filelist' || $_REQUEST['act'] == 'rename')
{
	$av_ext = array();
	
	if(@$dh = opendir(ROOT.'/f/iconz'))
	{
		while(false !== ($nm = readdir($dh)))
		{
			if(substr($nm, 0, 5) == '16-f-') $av_ext[substr($nm, 5, -4)] = true;
		}
		closedir($dh);
	}
}

switch(@$_REQUEST['act'])
{
case 'filelist':
	if(!read_directory())
	{
		$_RESULT = array ( 'error' => true, 'reason' => reason(), 'dir' => $_SESSION['DIR'], 'stop' => false );
		if(realpath($_SESSION['DIR'])==realpath(HOMEDIR)) $_RESULT['stop']=true;
		break;
	}
	
	$sz=$fsizes ? array_sum($fsizes) : 0;
	
	$items=array(); // items = { 0: { name, icon, type[, ... (data)] }, ... }
	
	if(!empty($drives)) // My computer is opened
	{
		$items[] = array(
		  'name' => lang('Home directory'),
		  'icon' => (HOMEDIR==lang('My computer') ? 'mycomp' : 'folder'),
		  'type' => tDIR,
		  'fullpath' => HOMEDIR,
		);
		
		if(!empty($_ENV['windir']))
			$items[] = array(
			   'name' => lang('Windows directory'),
			   'icon' => 'folder',
			   'type' => tDIR,
			   'fullpath' => $_ENV['windir'],
			);
		
		if(!empty($_ENV['ProgramFiles']))
			$items[] = array(
			   'name' => lang('Program files'),
			   'icon' => 'folder',
			   'type' => tDIR,
			   'fullpath' => $_ENV['ProgramFiles'],
			);
		
		
		foreach($drives as $v)
			$items[] = array(
			  'name' => (!empty($v['label']) ? $v['label'] : $descr[$v['type']] ).' ('.strtoupper(substr($v['name'],0,2)).')',
			  'icon' => $v['type'],
			  'type' => tDRIVE,
			  'fullpath' => strtoupper($v['name']),
			  'descr' => $descr[$v['type']],
			  'free' => $v['type']=='hdd' && ($s = disk_free_space($v['name']))!==false ? show_size(true,true,$s) : false,
			  'total' => $v['type']=='hdd' && ($s = disk_total_space($v['name']))!==false ? show_size(true,true,$s) : false,
			  'fs' => $v['fs'],
			);
	}else
	{	
		foreach($dirs as $v)
			$items[] = array(
			  'name' => basename($v),
			  'icon' => 'folder',
			  'type' => tDIR,
			  'fullpath' => $v,
			);

		foreach($files as $v)
			$items[] = array(
			  'name' => basename($v),
			  'icon' => 'f-'./*extension*/ (isset($av_ext[$ext=strtolower((($p = strrpos($v, '.')) !== false) ? substr($v, $p+1) : '')]) ? $ext : ''),
			  'type' => tFILE,
			  'fullpath' => $v,
			);
	}
	
	if(strlen($_SESSION['DIR'])<=3 && strlen($_SESSION['DIR'])>1 && $_SESSION['DIR'][1]==':')
	{
		$req = array( 'type' => tDRIVE, 'icon' => 'unknown' );
		
		foreach (get_logical_drives() as $v)
		{
			if(strtolower($v['name'][0]) == strtolower($_SESSION['DIR'][0]))
			{
				$req['icon'] = $v['type'];
				$req['name']=(!empty($v['label']) ? $v['label'] : $descr[$v['type']] ).' ('.strtoupper(substr($v['name'],0,2)).')';
				$req['fs'] = $v['fs'];
			}
		}
	}else $req = array ( 'type' => tDIR );
	
	$_RESULT=array(
	'items' => $items,
	/*'tmp' => serialize($items),*/
	'dir' => !empty($req['name']) ? $req['name'] : basename($_SESSION['DIR']),
	'DIR' => $_SESSION['DIR'],
	'stats' => stats(false),
	'size' => show_size(true,true,$sz),
	'info' => get_info($_SESSION['DIR'],$req),
	'type' => (!empty($req['type']) && empty($drives) ? $req['type'] : (empty($drives) ? tDIR : tMYCOMP)),
	'up' => $up,
	);
	
	break;
case 'info':	
	$_RESULT = get_info($f,$_REQUEST);
	break;
case 'delete':
	@set_time_limit(0);
	$err=false;
	foreach($fz as $v) if(!d_remove($v)) $err=true;
	if($err) $_RESULT = array( 'success' => false, 'reason' => reason(), 'f' => $f );
	else $_RESULT = array( 'success' => true );
	
	break;
case 'rename':
	$f = clean($_REQUEST['old']['fullpath']);
	if(empty($_REQUEST['new'])) $_REQUEST['new'] = basename($f);
	$newname = clean(dirname($f).'/'.$_REQUEST['new']);
	if(!d_rename($f,$newname)) $_RESULT = array( 'success' => false, 'reason' => reason(), 'f' => $f );
	else $_RESULT = array( 'success' => true, 'new' => array(
			  'name' => basename($newname),
			  'icon' => d_is_dir($newname) ? 'folder' : 'f-'.(isset($av_ext[$ext=strtolower((($p = strrpos($newname, '.')) !== false) ? substr($newname, $p+1) : '')]) ? $ext : ''),
			  'type' => d_is_dir($newname) ? tDIR : tFILE,
			  'fullpath' => $newname,
			) );
	
	break;
case 'mkdir':
	$f=clean($_SESSION['DIR'].'/'.$_REQUEST['name']);
	if(!d_mkdir($f)) $_RESULT = array('success' => false, 'reason' => reason());
	else $_RESULT = array('success' => true);
	
	break;
case 'mkfile':
	$f=clean($_SESSION['DIR'].'/'.$_REQUEST['name']);
	if(file_exists($f) && $_REQUEST['confirm']==0)
	{
		$_RESULT = array('exists' => true);
	}else if(file_exists($f) && $_REQUEST['confirm'] == 1 || !file_exists($f))
	{
		$_RESULT = array('success' => d_file_put_contents($f,''), 'reason' => reason());
	}else $_RESULT = array('success' => false, 'reason' => 'unknown situation');
	
	break;
case 'download_get_href':
	$_RESULT = array( 'href' => 'system/download.php?file='.rawurlencode($f).'&'.session_name().'='.session_id() );
	break;
case 'copy':
case 'cut':
	$_REQUEST['files']=$fz;
	/*if(empty($_REQUEST['files']))
	{
		light_message('::Please choose one or more files you want to '.$_REQUEST['act'].'.::');
		break;
	}*/
	
	$_SESSION['copy']=$_SESSION['cut']=array();
	foreach($_REQUEST['files'] as $k=>$v) $_SESSION[$_REQUEST['act']][]=clean($v);
	
	$_RESULT = sizeof($_REQUEST['files'])>0 ? true : false;
	break;
case 'paste':
	@set_time_limit(0);
	function full_print($param){ echo $param; }
	
	$_RESULT = paste('full_print');
	break;
case 'advanced_paste':
	//echo 'blyat!';
	$res = advanced_paste($_SESSION['DIR'], 'copy', $_SESSION['copy'], $bytes);
	
	$_RESULT = array('state' => $res, 'bytes' => show_size(false,true,@$_SESSION['adv_bytes']+=$bytes) );
	
	if($res==FINISHED_COPY || empty($_SESSION['copy']))
	{
		$_SESSION['copy']=false;
		$_SESSION['adv_bytes']=0;
		$_SESSION['copy_cache']=null;
		$_RESULT['state'] = FINISHED_COPY;
	}
	break;
case 'cancel_copy':
	$_SESSION['copy']=$_SESSION['cut']=array();
	$_RESULT = true;
	break;
case 'upload':
	$_RESULT = upload_files(clean($_REQUEST['DIR']));
	if(!$_RESULT) echo 'Could not upload files.'.reason();
	break;
case 'dirsize':
	@set_time_limit(0);
	if($_REQUEST['nolimit']=='true') define('NOLIMIT', true);
	$_RESULT = show_size($f);
	break;
case 'save-file':
	$f = clean(urldecode($_REQUEST['filename_encoded']));
	
	if(!d_file_put_contents($f, $_REQUEST['content']))
	{
		echo 'Cannot edit file.'.reason();
		$_RESULT = false;
	}else $_RESULT = true;
	break;
case 'exec':
	$_RESULT = exec_command($_REQUEST['cmd']);
	break;
case 'get_rights':
	$_RESULT = d_get_rights($f);
	break;
case 'set_rights':
	$_RESULT = true;
	foreach($fz as $v)
	{
		$func = d_is_dir($v) && $_REQUEST['recursive']=='true' ? 'd_chmod_recursive' : 'd_chmod';
		if(!$func($v, $_REQUEST['mod'])) $_RESULT=false;
	}
	
	if(!$_RESULT) echo 'Could not change file rights.'.reason();
	break;
case 'zip':
	$_RESULT = add_to_zip($fz);
	if(!$_RESULT) echo 'Could not add to zip.'.reason();
	break;
case 'unzip':
	$_RESULT = unzip_files(array($f), $_REQUEST['mode']);
	if(!$_RESULT) echo 'Could not extract files.'.reason();
	break;
case 'update':
	$_REQUEST['act'] = 'download-new'; /* for correct work of update_dolphin() */
	$_RESULT = update_dolphin(create_function('$cmd','return true;'))===true;
	break;
case 'ping':
	$_RESULT = 'pong';
	break;
case 'handletab':
	$cmd = ltrim($_REQUEST['cmd']);
	
	$parts = explode(' ', $cmd);
	
	if(sizeof($parts) <= 1) /* autocomplete command */
	{
		list($dirs, $exts) = get_path_dirs();
		foreach($exts as $k=>$v) $exts[$k] = trim(strtolower($v));
		$tmp = strtolower($cmd);
		$l = strlen($cmd);
		$found = array();
		
		foreach($dirs as $d)
		{
			if( !@$dh = opendir($d) ) continue;
			
			while( ($f = readdir($dh)) !== false)
			{
				if($f=='.'&&$f=='..') continue;
				
				if(strtolower(substr($f, 0, $l)) == $tmp && is_file($d.'/'.$f))
				{
					$good = is_callable('is_executable') ? is_executable($d.'/'.$f) : false; /* for *nix */
					$ext = '';
					
					if(sizeof($exts) > 1) /* for Windows */
					{
						$good = false;
						foreach($exts as $v)
						{
							if(empty($v)) continue; /* in Windows file without extension is not executable */
							if( strlen($f)>strlen($v) && strtolower(substr($f, -strlen($v))) == $v)
							{
								//echo $f.' ';
								$good = true;
								$ext = $v;
								break;
							}
						}
					}
					
					if($good)
					{
						if($ext) $f = substr($f, 0, -strlen($ext));
					
						//echo $f.' ';
							
						$found[]=$f;
					}
				}
			}
			
			closedir($dh);
		}
		
		if(sizeof($found) == 0)
		{
			$_RESULT['cmd'] = $cmd;
		}else if(sizeof($found) == 1)
		{
			$_RESULT['cmd'] = $found[0];
		}else /* if sizeof($found) > 1 */
		{
			/* find the longest string, equal for all */
			for($good = true, $i = $l; $good; )
			{
				$i++;
				$tmp = substr($found[0], 0, $i);
				
				foreach($found as $v)
				{
					if(substr($v, 0, $i) != $tmp)
					{
						$good = false;
						break;
					}
				}
			}
			
			$i--;
			
			if($i == $l) /* means that TAB was pressed again */
			{
				$_RESULT['output'] = '';
				if(sizeof($found) < 30)
				{
					foreach($found as $v) $_RESULT['output'] .= $v.' ';
				}else
				{
					for($j=0;$j<29;$j++) $_RESULT['output'] .= $found[$j].' ';
					$_RESULT['output'] .='...';
				}
			}
			
			$_RESULT['cmd'] = substr($found[0], 0, $i);
		}
	}else
	{
		$ignorecase = file_exists(dirname(__FILE__).'/'.strtoupper(basename(__FILE__)));
		$tmp = $ignorecase ? strtolower($parts[sizeof($parts)-1]) : $parts[sizeof($parts)-1];
		$l = strlen($tmp);
		$found = array();
		
		if( !@$dh = opendir($_SESSION['DIR']) )
		{
			$_RESULT=false;
			die('Permission denied');
		}
		
		if($ignorecase)
		{
			while( ($f = readdir($dh)) !== false)
			{
				if($f=='.' || $f=='..') continue;
				if(strtolower(substr($f, 0, $l)) == $tmp) $found[]=$f;
			}
		}else
		{
			while( ($f = readdir($dh)) !== false)
			{
				if($f=='.' || $f=='..') continue;
				if(substr($f, 0, $l) == $tmp) $found[]=$f;
			}
		}
		
		closedir($dh);
		
		//print_r($found);
		
		if(sizeof($found) == 0)
		{
			$_RESULT['cmd'] = implode(' ', $parts);
		}else if(sizeof($found) == 1)
		{
			$parts[sizeof($parts)-1] = $found[0];
			$_RESULT['cmd'] = implode(' ', $parts);
		}else /* if sizeof($found) > 1 */
		{
			/* find the longest string, equal for all */
			for($good = true, $i = $l; $good; )
			{
				$i++;
				$tmp = substr($found[0], 0, $i);
				
				foreach($found as $v)
				{
					if(substr($v, 0, $i) != $tmp)
					{
						$good = false;
						break;
					}
				}
			}
			
			$i--;
			
			if($i == $l) /* means that TAB was pressed again */
			{
				$_RESULT['output'] = '';
				if(sizeof($found) < 30)
				{
					foreach($found as $v) $_RESULT['output'] .= $v.' ';
				}else
				{
					$_RESULT['output'] .= 'too many results';
				}
			}
			
			$parts[sizeof($parts)-1] = substr($found[0], 0, $i);
			$_RESULT['cmd'] = implode(' ', $parts);
		}
	}
	
	$_RESULT['dir'] = getcwd_short();
	break;
}
?>