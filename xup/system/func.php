<?
if($CFG['ftp'] && $CFG['ftp']['fix_rights']) define('FIX', true);
else define('FIX', false);

include(dirname(__FILE__).'/ftp.php');

if(!function_exists('ob_get_clean') /* this will mean PHP version less than 4.3 */)
{
	function ob_get_clean()
	{
		ob_end_clean();
		return ob_get_contents();
	}
	
	function file_get_contents($file)
	{
		if(!$fp = fopen($file, 'rb')) return false;
		$buf = fread($fp, filesize($file));
		fclose($file); /* please note, that closing file is essential (in case you want to read a lot of files) */
		
		return $buf;
	}
}

/**
 * Shows size of $f file or folder like "1023.53 Kb". If $format=false, it returns just filesize in bytes. If $size is not false, the function will just format $size value, but not count the size of directory again.
 *
 * @param string $f
 * @param bool $format
 * @return string/int
 */
function show_size($f,$format=true,$size=false)
{
	if($format || $size!==false)
	{
		if($size===false) $size=show_size($f,false);
		if(!empty($GLOBALS['TIMED_OUT'])) $p = '&gt;';
		else $p = '';
		if($size<=1024) return $p.$size.'&nbsp;bytes';
		else if($size<=1048576) return $p.round($size/1024,2).'&nbsp;Kb';
		else if($size<=1073741824) return $p.round($size/1048576,2).'&nbsp;Mb';
		else if($size<=1099511627776) return $p.round($size/1073741824,2).'&nbsp;Gb';
		else if($size<=1125899906842624) return $p.round($size/1099511627776,2).'&nbsp;Tb';
		else return $p.round($size/1125899906842624,2).'&nbsp;Pb';
	}else
	{
		if(d_is_file($f)) return sprintf("%u",d_filesize($f)); // for files the size of which is more than 2 Gb and less than 4 Gb
		$size=0;
		setreadable($f,true);
		$dh=opendir($f);
		while(($file=readdir($dh))!==false)
		{
			if($file=='.' || $file=='..') continue;
			// delete the next lines if you don't want any limits
			if(!defined('NOLIMIT') && array_sum(explode(' ',microtime()))-START_TIME>DIRSIZE_LIMIT)
			{
				$GLOBALS['TIMED_OUT'] = true;
				break;
			}
			
			if(d_is_file($f.'/'.$file)) $size+=sprintf("%u",d_filesize($f.'/'.$file));
			else $size+=show_size($f.'/'.$file,false);
		}
		closedir($dh);
		return $size+d_filesize($f); // +d_filesize($f) for *nix directories
	}
}

function d_get_rights($file, $oct=true)
{
	if(fileperms($file)) return get_rights($file,$oct);
	
	$f = abs_path($file);
	$t = d_filelist_fast(dirname($f));
	
	if(empty($t['rights'][$f])) return false;
	
	if(!$oct) return $t['rights'][$f];
	return sprintf('%03d',decoct(bindec(str_replace(array('-','r','w','x'),array('0','1','1','1'),substr($t['rights'][$f],1)))));
}

/* get permissions of file. If $oct is set to false,
   it will return the string like "drwxrwxrwx"
   instead of returning something like 777
*/
function get_rights($file, $oct=true)
{
	static $s='rwxrwxrwx',$t='upcudubu-ulusuwu';
	
	$p=fileperms($file);
	if(!$oct)
	{
		$m=sprintf('%09b',$p&511);
		for($i=0;$i<9;$i++)$m[$i]=$m[$i]=='0'?'-':$s[$i];
		
		return $t[($p&61440)>>12].$m;
	}else
	{
		return sprintf("%03d",decoct($p&511));
	}
}

/**
 * Gets owner of $file. Returns empty string on failure.
 *
 * @param string $file
 * @return string
 */
function get_owner($file)
{
	if(function_exists('fileowner') && function_exists('posix_getpwuid'))
	{
		@$tmp=posix_getpwuid(fileowner($file));
		return $tmp['name'];
	}
	return '';
}

/**
 * Gets group, which the $file owner belongs to. Returns empty string on failure.
 *
 * @param string $file
 * @return string
 */
function get_group($file)
{
	if(function_exists('filegroup') && function_exists('posix_getgrgid'))
	{
		@$tmp=posix_getgrgid(filegroup($file));
		return $tmp['name'];
	}
	return '';
}

// the function for abs_path
function glue_path($dir,$fname)
{ $dir=strtr(trim($dir),"\\","/");
  $name=strtr(trim($fname),"\\","/");
  if(@($fname[0]=='//')) return $fname;
  if(@($dir[strlen($dir)-1]=='/')) return $dir.$fname;
  return "$dir/$fname";
}

// Dmitry Koterov's function, the replacement of realpath() in PHP
function abs_path($name,$cur="")
{
  //if(file_exists($name)) return realpath($name);
  $name=strtr(trim($name),"\\","/");
  $Parts=explode("/",$name);
  $Path=($cur===""?!empty($_SESSION['DIR'])?$_SESSION['DIR']:getcwd():$cur);
  foreach($Parts as $i=>$s) if($s!=".") { 
    if(!$i && (strlen($s)>1&&$s[1]==":"||$s=="")) $Path=$s;
    else if($s=="..") {
      if(strlen($Path)>1 && $Path[1]==":" && strlen($Path)<=3 || $Path=="/" || $Path=="\\") continue;
      $p=dirname($Path); 
      if($p=="/"||$p=="\\"||$p==".") $Path=""; else $Path=$p;
    }
    else if($s!=="") $Path=glue_path($Path,$s);
  }
  // a little modification
  if(strlen($Path)==2 && $Path[1]==':') $Path.='/'; /* always write last "/" for windows disk name */
  if(empty($Path)) $Path = '/';
  
  if(preg_match('/^[A-Z]\:/i',getcwd()) && !preg_match('/^[A-Z]\:/i',$Path)) return strtoupper(substr(getcwd(),0,2)).$Path; // possibly Windows
  else if(strlen($Path)>=2 && $Path[0]!='/' && $Path[1]==':') $Path[0] = strtoupper($Path[0]);
  
  return str_replace("\\","/",$Path);
}

/**
 * Cleans path and checks if the $path is allowed
 *
 * @param string $path
 * @return string
 */
function clean($path)
{
	$r=abs_path($path);
	
	return $r;
	
	/*$h = abs_path(HOMEDIR);
	if(substr($h,0,strlen($h))!=substr($r,0,strlen($h))) return $h.'/'.basename($r);
	else return $r;
	*/
}

/**
 * Function returns string with image (directory or file) and filename.
 *
 * @param string $f
 * @param string $dir
 * @return string
 */
function show_file($f,$dir=false)
{
	if($dir===false) $dir=(@d_is_dir($f) ? 'dir' : 'file');
	return '<img src="images/'.$dir.'.png" width="16" height="16">&nbsp;<b>'.htmlspecialchars(basename($f)).'</b>';
}

/**
 * Function removes $dir directory, or $dir file.
 *
 * @param string $dir
 * @return bool
 */
function d_remove($dir)
{
	$dir = abs_path($dir);
	setwritable($dir);
	
	if(d_is_file($dir)) return d_unlink($dir);
	
	$t = d_filelist_fast($dir);

	foreach(array_merge($t['files'],$t['dirs']) as $v) d_remove($v);
	
	return d_rmdir($dir);
}

/**
 * Function safely creates folder
 *
 * @param string $dir
 * @param int $mode
 * @return bool
 */
function safe_mkdir($dir,$mode=0777)
{
	umask(0);
	return mkdir($dir,$mode);
}

/**
 * Copy not only files, but folders too. See copy() description for details
 *
 * @param string $src
 * @param string $dest
 * @return bool
 */
function d_copy($src,$dest)
{
	$src = abs_path($src);
	$dest = abs_path($dest);
	
	setwritable(dirname($dest),true);
	setwritable($src,true); /* we need to have read permissions! */
	
	if(d_is_file($src)) return (!FIX && @copy($src,$dest)) || @d_ftpcom('copy',$src,$dest);
	
	if(!d_is_dir($src)) return d_error('The file or directory, you want to copy, doesnt exist.');
	
	$dest.='/';
	$src .='/';
	if(substr($dest,0,strlen($src))==$src) return d_error('Cannot copy the directory inside itself.');
	
	if(!d_is_dir($dest) && !d_mkdir($dest)) return false;
	
	$t = d_filelist_fast($src);
	
	foreach(array_merge($t['files'],$t['dirs']) as $f) if(!d_copy($src.basename($f),$dest.basename($f))) $error=true;
	
	return !isset($error);
}

$__copy_cache = array('dirs' => array( /* 0 => array(from, to, offset) */), 'files' => array(), 'perms' => array(/* dir => perm */));

/* $fromlist - list with absolute paths of files and folders you want to copy.
   $tolist   - list of files you want to get after copying */
function advanced_copy_start($fromlist, $tolist)
{
	global $__copy_cache;
	foreach($fromlist as $k=>$v)
	{
		$__copy_cache[is_dir($v) ? 'dirs' : 'files'][] = array($v, $tolist[$k], 0);
	}
	return true;
}

function advanced_copy_cache($cache = false)
{
	if($cache === false) return $GLOBALS['__copy_cache'];
	else $GLOBALS['__copy_cache'] = $cache;
}

function have_time_to_copy()
{
	return (array_sum(explode(' ',microtime())) - START_TIME) < MAX_COPY_TIME;
}

function advanced_copy_cont()
{
	global $__copy_cache;
	
	$bytes = 0;
	
	//array_display($__copy_cache);
	
	while(sizeof($__copy_cache['files']) > 0 || sizeof($__copy_cache['dirs']) > 0)
	{
		while(sizeof($__copy_cache['files']) > 0)
		{
			$f = array_pop($__copy_cache['files']);
			
			//array_display($f);
			
			//echo 'copying '.$f[0].' to '.$f[1].' ...<br>';
			//flush();
			
			$dir = dirname($f[1]);
			
			if(!@$from = fopen($f[0], 'rb')) continue;
			if(!@$to = fopen($f[1], 'ab'))
			{
				$p = get_rights($dir);
				if(setwritable($dir) && (@$to = fopen($f[1], 'ab')) )
				{
					$__copy_cache['perms'][$dir] = $p;
				}else
				{
					fclose($from);
					continue;
				}
			}
			
			fseek($from, $f[2]);
			
			while(!feof($from))
			{
				if(have_time_to_copy())
				{
					$buf = fread($from, 65536);
					fputs($to, $buf);
					$bytes += strlen($buf);
				}else
				{
					$f[2] = ftell($from);
					$__copy_cache['files'][] = $f; /* return the last copied file to cache */
					
					fclose($from);
					fclose($to);
					
					return $bytes;
				}
			}
			
			fclose($from);
			fclose($to);
			
			if(!empty($__copy_cache['perms'][$dir]))
			{
				d_chmod($dir, $__copy_cache['perms'][$dir]);
				unset($__copy_cache['perms'][$dir]);
			}
		}
		
		if(sizeof($__copy_cache['dirs']) > 0)
		{
			$dir = array_shift($__copy_cache['dirs']);
			//array_display($dir);
			
			$res = d_filelist_fast($dir[0]);
			
			if(!$res) continue; /* TODO: ask user, whether to stop copying */
			if(!d_file_exists($dir[1]) && !d_mkdir($dir[1])) continue;
			
			//array_display($res);
			
			foreach($res['files'] as $k=>$v)
			{
				$__copy_cache['files'][] = array($v, $dir[1].'/'.basename($v), 0);
			}
			
			foreach($res['dirs'] as $k=>$v)
			{
				$__copy_cache['dirs'][] = array($v, $dir[1].'/'.basename($v), 0);
			}
		}
	}
	
	return $bytes;
}


$__d_fopen_data=array();
// the function is a replacement for standard fopen() function, but it does not support 'a' mode through FTP.
function d_fopen($file,$mod)
{
	global $__d_fopen_data;
	
	//echo ''.$mod;
	
	if(
	(strpos($mod,'w')!==false && ($GLOBALS['CFG']['ftp']['fix_rights']) || !@fopen($file,$mod)) || 
	(strpos($mod,'r')!==false && !@fopen($file,$mod))
	){
		if(!$tmp=get_tmp_dir()) return d_error('Could not get temp directory');
		$f = $tmp.'/'.uniqid(rand()).'.dolphin.tmp';
		//echo $f.'<br>';
		
		if(strpos($mod,'r')!==false)
		{
			if(!d_copy(abs_path($file),$f)) return d_error('Could not write the contents to temp file'); // if there is a memory limit, this won't work for big files...
		}
		
		if(!$fp=fopen($f,$mod)) d_error('System error. Something is wrong with d_copy() function.');
		$__d_fopen_data[$file.'***'.$f.'***'.$mod]=$fp;
		
		//echo 'all is ok for file "'.$file.'" ('.file_get_contents($f).')<br>';
		
		return $fp;
	}
	
	return fopen($file,$mod);
}

function d_fclose($fp)
{
	global $__d_fopen_data;
	
	if(!$key=array_search($fp,$__d_fopen_data)) return fclose($fp);
	
	//echo 'fclose<br>';
	
	list($file,$f,$mod) = explode('***',$key,3);
	
	fclose($fp);
	unset($GLOBALS['__d_fopen_data'][$key]);
	
	if(strpos($mod,'r')!==false) unlink($f);
	
	if(strpos($mod,'w')!==false)
	{
		if(!d_copy($f,$file)) return d_error('Could not write the contents to file.');
		unlink($f);
	}
	
	return true;
}

$__d_opendir_data=array();
// the replacement for opendir(). Please note that it DOES NOT RETURN THE NORMAL DIRECTORY RESOURSE!
function d_opendir($dir)
{
	global $__d_opendir_data;
	$dir = abs_path($dir);
	
	if(isset($__d_opendir_data[$dir])) return $dir;
	
	$dirs=$files=$fsizes=array();
	if(!d_filelist($dir,$dirs,$files,$fsizes)) return false;
	
	$items = array_merge(array('.','..'),$files,$dirs);
	foreach($items as $k=>$v) $items[$k] = basename($v);
	$items[0] = '.';
	$items[1] = '..';
	$__d_opendir_data[$dir] = array('items' => $items, 'position' => 0, 'last' => sizeof($items)-1);
	
	return $dir;
}

function d_readdir($dh)
{
	global $__d_opendir_data;
	if(!isset($__d_opendir_data[$dh])) return d_error('Invalid handle!');
	if($__d_opendir_data[$dh]['position'] > $__d_opendir_data[$dh]['last']) return false;
	
	return $__d_opendir_data[$dh]['items'][$__d_opendir_data[$dh]['position']++];
}

function d_closedir($dh)
{
	global $__d_opendir_data;
	unset($__d_opendir_data[$dh]);
	return true;
}

/**
 * @desc This function sends to the browser thumbnail of image $fullpath, and, if you want, you can point $width and $height for this thumbnail. default is 80x60. If $resample is set to TRUE, the quality resize algorithm will be used.
 *
 * @author Nasretdinov Yuriy <n a s r e t d i n o v [all-known-symbol] g m a i l . c o m>
 * @version 0.9.1
 * @param string $fullpath
 * @param int $width
 * @param int $height
 * @param bool $resample
 * @return bool
 */
function send_thumbnail($fullpath,$width=80,$height=60,$resample=-1)
{
		if(!is_readable($fullpath))
		{
			if(!$tmp=get_tmp_dir()) return d_error('Could not read file');
			
			$f=$tmp.'/'.uniqid('img_',rand()).'.'.basename($fullpath);
			
			//d_error('d_copy('.$fullpath.','.$f.')');
			
			if(!d_copy($fullpath,$f)) return d_error('Could not read file, could not copy');
			
			$fullpath = $f;
			//d_error($fullpath);
		}
		
        @$w = getimagesize($fullpath);
        if (!$w) return d_error('Could not get image size');
        if ( (@$lim = return_bytes(ini_get('memory_limit'))) > 0)
        {
                $sz = ( $w[0]*$w[1] + $width*$height ) * 5 + (function_exists('memory_get_usage') ? memory_get_usage() : 0); // approximate size of image in memory (yes, 5 bytes per pixel!!)
                if ($sz >= $lim) return d_error('Memory limit exceeded');
        }
       
        if ($w[0] <= $width && $w[1] <= $height)
        {
                header('Content-type: '.$w['mime']);
                readfile($fullpath);
                if(!empty($tmp)) unlink($fullpath);
                return true;
        }
       
        // output exif if possible
        /*
        if (function_exists('exif_thumbnail') && (@$img = exif_thumbnail($fullpath,$t1,$t2,$type)))
        {
                header('Content-type: '.image_type_to_mime_type($type));
                echo $img;
                return true;
        }
        */
       
        if (!function_exists('imagecreate') || !function_exists('imagecopyresized')) return d_error('GD not found: ither imagecreate() or imagecopyresized() do not exist.');

        $p = pathinfo($fullpath);
        @$ext = strtolower($p['extension']);
        if (in_array($ext,array('jpeg','jpe','jpg'))) $ext = 'jpeg';
       
        if (function_exists($func = 'imagecreatefrom'.$ext)) $src = $func($fullpath);
        else return d_error('Unsupported image type. Maybe, invalid extension?');
       
        header('Content-type: image/jpeg');
       
        //proportions
        $new_width = round(($height/$w[1])*$w[0]);
        $new_height = round(($width/$w[0])*$w[1]);
        if ($new_width>$width) $new_width = $width;
        if ($new_height>$height) $new_height = $height;
       
        if (!function_exists($cfunc = 'imagecreatetruecolor')) $cfunc='imagecreate';
        $thumb = $cfunc($new_width,$new_height);
       
        $func = (($resample===-1 && defined('USE_RESAMPLE') && USE_RESAMPLE) || $resample===true) && function_exists('imagecopyresampled') ? 'imagecopyresampled' : 'imagecopyresized';
       
        // optimisations for big images
        $c = 2;
        if ($func != 'imagecopyresized' && ($w[0] > $c*$new_width || $w[1] > $c*$new_height))
        {
                $thumb_c = $cfunc($c*$new_width,$c*$new_height);
                imagecopyresized($thumb_c,$src,0,0,0,0,$c*$new_width,$c*$new_height,$w[0],$w[1]);
                imagedestroy($src);
                $src = $thumb_c;
                list($w[0],$w[1]) = array($c*$new_width,$c*$new_height);
        }
       
        $func($thumb,$src,0,0,0,0,$new_width,$new_height,$w[0],$w[1]);

        imagedestroy($src);
        imagejpeg($thumb);
        imagedestroy($thumb);
       
        if(!empty($tmp)) unlink($fullpath);
        return true;
}

/**
 * The function authorizes you and shows login screen
 *
 */
function auth()
{
	global $CFG;
	
	$ajax = false;

	/* the workaround code for Safari's bug with <select> */
	
	if(!empty($_REQUEST['version_hid']) && $_REQUEST['version_hid']!=$_REQUEST['version'] && $_REQUEST['version_hid']=='full') $_REQUEST['version'] = 'full';
	
	if(!empty($_REQUEST['JsHttpRequest']))
	{
		$ajax = true;
		ajax_start_transfer();
		if(empty($_REQUEST['version'])) $_REQUEST['version']='full';
		else if($_REQUEST['version']!='full' /*light?*/) define('VER', 'full');
	}
	
	if(@$_REQUEST['login']==$CFG['login'] && @$_REQUEST['pass']==$CFG['password'])
	{
		$_SESSION['logined']=true;
		$_SESSION['login']=$CFG['login'];
		$_SESSION['pass']=$CFG['password'];
		switch(@$_REQUEST['version'])
		{
		default:
		case 'light':
			$_SESSION['version']='light';
			break;
		case 'full':
			$_SESSION['version']='full';
			break;
		case 'uploader':
			$_SESSION['version']='uploader';
			break;
		}
		
		if(!empty($_REQUEST['DIR'])) $_SESSION['DIR'] = clean($_REQUEST['DIR']);
		
		if(!$ajax)
		{
			header('location: index.php?DIR=.&'.SID);
			die();
		}else
		{
			return true; /* handle our query properly */
		}
	}
	
	if(empty($_SESSION['logined']) || $_SESSION['login']!=$CFG['login'] || $_SESSION['pass']!=$CFG['password'])
	{
		$_SESSION['savedreq'] = array($_REQUEST, $_GET, $_POST);
		
		if(!$ajax)
		{
			include(ROOT.'/system/login.html');
		}else
		{	
			echo '--error-login-required';
		}
		die();
	}
}

function ajax_start_transfer()
{
	if(!class_exists('JsHttpRequest'))
	{
		if(!preg_match('/khtml/i', $_SERVER['HTTP_USER_AGENT'])) ob_start('ob_gzhandler');

		include_once(ROOT.'/system/libs/JsHttpRequest.php');
		$GLOBALS['__JsHttpRequest_object'] = new JsHttpRequest(CHARSET);
		$GLOBALS['NOHANDLER']=true;
	}
}

/**
 * The function logs out and redirects you to the beginning
 *
 */
function logout()
{
	session_destroy();
	header('location: .');
}

/**
 * Function shows statistics (e.g. generation time)
 *
 */
function stats($echo=true)
{
	global $files,$dirs;
	
	if(!defined('FILES')) define('FILES',0);
	if(!defined('DIRS')) define('DIRS',0);
	
	if($echo)
	{
	 ?><div id="stats" align="center"><font color="#d0d0d0">gentime <b id="seconds"><?=round(array_sum(explode(' ',microtime()))-START_TIME,4)?></b>sec<?=(isset($GLOBALS['files']) ? '; <b id="objects">'.(FILES+DIRS).'</b> obj'.(FILES+DIRS!=sizeof($files)+sizeof($dirs) ? '; <b id="objects_current">'.(sizeof($files)+sizeof($dirs)).'</b> current obj' : '') : '')?><? if(defined('FTP_LINK')) echo '; <b>FTP</b> active'; ?></font><br>&lt;<a href="index.php?act=logout">log out</a><?if(VER=='light') echo '<span class="desktop"> | <a href="index.php?version=full&amp;DIR=.">switch to full version</a></span>'; if(VER=='full') echo ' | <a href="index.php?version=light&amp;DIR=.">switch to light version</a>';?>&gt;</div><?
	}else
	{
		return array(
		 'seconds' => round(array_sum(explode(' ',microtime()))-START_TIME,4),
		 'objects' => isset($GLOBALS['files']) ? FILES+DIRS : false,
		 );
	}
}

/**
 * The function returns an array with logical drives
 * it uses an external "volumes.exe" (compiled volumes.c)
 *
 */
function get_logical_drives()
{
	if(!getenv('COMSPEC')) return false;
	
	if(!exec(ROOT.'/full/volumes.exe',$data)) return false;
	
	static $types=array(
	2 => 'removable', // diskettes (A:\ and B:\), flash..
	3 => 'hdd',
	4 => 'smb', // yes, SMB mounted drive
	5 => 'cd',
	6 => 'ramdrive',
	);
	
	$drives=array();
	
	foreach($data as $v)
	{
		if(empty($v)) continue;
		
		list($name,$type,$fs,$label)=explode('|',trim($v),4);
		
		if($name[0]!='a' && $name[0]!='b') $type = (empty($types[$type]) ? 'unknown' : $types[$type]);
		else $type='diskette';
		
		$drives[] = array( 'name' => $name, 'type' => $type, 'label' => $label, 'fs' => $fs);
	}
	
	//echo '<pre>',!print_r($drives),'</pre>';
	
	return $drives;
}

/**
 * Reads all accessible files in current directory, and puts into global scope:
 * 
 * $dir   - directory name
 * $dirs  - sorted list of directories
 * $files - sorted list of files
 * $up    - either path to upper directory or FALSE if upper dir is not available
 * 
 * If parameteres $_GET['sort'] and $_GET['order'] specified, and $_GET['sort']=='name', it will sort the arrays in the specified order.
 * If $_GET['sort'] is not specified, the list will be natsorted in ascending order.
 *
 * @return bool
 */
function read_directory()
{
	global $dir,$dirs,$files,$up,$drives,$php_errormsg,$fsizes;
	
	if(empty($_SESSION['DIR'])) $_SESSION['DIR']=abs_path(HOMEDIR);
	
	if(!empty($_REQUEST['DIR']) && $_REQUEST['DIR'][0]=='~') $_REQUEST['DIR']=HOMEDIR.substr($_REQUEST['DIR'],1);
	if(!empty($_REQUEST['DIR']) && $_REQUEST['DIR'] == '.' && !empty($_SESSION['DIR'])) $_REQUEST['DIR']=$_SESSION['DIR']; // especially to save current directory state with refreshes in full version

	$dir=!empty($_REQUEST['dir']) ? clean($_SESSION['DIR'].'/'.$_REQUEST['dir']) : $_SESSION['DIR'];
	if(!empty($_REQUEST['DIR'])) $dir=clean($_REQUEST['DIR']);
	else $_REQUEST['DIR']=HOMEDIR;
	
	
	$c = lang('My computer');
	
	if(substr(strtolower($_REQUEST['DIR']),0,strlen($c))==strtolower($c) || (substr($_REQUEST['DIR'],-2)=='..' && abs_path(substr($_REQUEST['DIR'],0,-2))==abs_path($dir)))
	{
		$dirs=$files=array();
		if(@$drives = get_logical_drives())
		{
			foreach($drives as $v) $dirs[]=strtoupper($v['name']);
			
			$dir=$_SESSION['DIR']=$c;
			$up=false;
			
			define('DIRS',sizeof($dirs));
			define('FILES',sizeof($files));
			
			return true;
		}
	}
	
	global $fsizes;
	
	$dirs=$files=$fsizes=array();
	
	if(!($arr = d_filelist_fast($dir)))
	{
		define('FILES',0);
		define('DIRS',0);
		return d_error('Cannot get file list');
	}
	
	$dirs = $arr['dirs'];
	$files = $arr['files'];
	$fsizes = $arr['fsizes'];
	
	$_SESSION['DIR']=$dir;
	
	if(empty($_GET['sort'])){natsort($dirs);natsort($files);}
	else if($_GET['sort']=='name')
	{
		natsort($dirs);
		natsort($files);
		if(@$_GET['order']=='desc')
		{
			$dirs=array_reverse($dirs);
			$files=array_reverse($files);
		}
	}
	
	$up=$_SESSION['DIR'].'/..';
	if(abs_path($_SESSION['DIR']) == '/') $up = false;
	
	
	define('DIRS',sizeof($dirs));
	define('FILES',sizeof($files));
	
	return true;
}

/**
 * Dolphin.php chmod function - works like chmod() - but NOTE, THAT RIGHTS SHOULD BE DECIMAL (777 instead of 0777)!
 * 
 * uses FTP or SHELL to perform operations
 *
 * @param string $f
 * @param int $mod
 */
function d_chmod($f,$mod)
{
	$f = abs_path($f);
	umask(0);
	if(@chmod($f,octdec($mod))) return true;
	if(@exec('chmod '.escapeshellarg($mod).' '.escapeshellarg($f))) return true;
	return d_ftpcom('chmod', $f, false, $mod);
}

function d_chmod_recursive($dir,$mod)
{
	$list = d_filelist_all($dir);
	if(!$list) return d_error('Could not list of files for '.$dir.'. Probably, chmodding file?');
	
	extract($list);
	
	foreach(array_merge($files,$dirs,array($dir)) as $v) if(!d_chmod($v,$mod)) $err = true;
	
	return !isset($err);
}

function d_rename($from,$to)
{
	global $__perms;
	
	$from = abs_path($from);
	$to = abs_path($to);
	
	if(setwritable($from, true)) $__perms[$to] = $__perms[$from];
	
	return @rename($from,$to) || d_ftpcom('rename', $from, $to);
}

function d_unlink($file)
{
	$file = abs_path($file);
	setwritable($file);
	
	return @unlink($file) || d_ftpcom('unlink', $file);
}

function d_rmdir($file)
{
	$file = abs_path($file);
	setwritable($file);
	
	return @rmdir($file) || d_ftpcom('rmdir', $file);
}

$__perms = array();
/* $rem -- remember rights and return them at the end of script work? */
function setwritable($file, $rem = false)
{	
	if(is_writeable($file)) return true;
	if(set_rights($file,777,$rem)) return true;
	//if(d_filesize($file) <= 100*1024 && d_file_put_contents($file, d_file_get_contents($file,false),false)) return true;
	
	return false;
}

function setreadable($file, $rem = false)
{
	if(is_readable($file)) return true;
	if(set_rights($file,777,$rem)) return true;
	//if(d_filesize($file) < 100*1024 && d_file_get_contents($file)!==false) return true;
	
	return false;
}

function d_chmod_deep($file, $mod = 777)
{
	
}

function set_rights($file,$mod,$rem = false)
{
	if(!file_exists($file)) return false;
	
	global $__perms;
	$perm = get_rights($file);
	
	if(!d_chmod($file, $mod)) return false;
	else if(!$rem) return true;
	
	$__perms[$file] = $perm;
	
	return true;
}

function return_all_permissions()
{
	foreach($GLOBALS['__perms'] as $k=>$v) return_permissions($k);
	
	return true;
}

register_shutdown_function('return_all_permissions');

function return_permissions($file)
{
	global $__perms;
	
	if(empty($__perms[$file])) return true;
	
	return d_chmod($file, $__perms[$file]);
}

/* MOD should be decimal (e.g. 777 instead of 0777) */

function d_mkdir($file, $mod = 777)
{
	$file = abs_path($file);
	setwritable(dirname($file),true);
	
	if((!FIX && @safe_mkdir($file,octdec($mod))) || d_ftpcom('mkdir', $file))
	{
		d_ftplist('','flush');
		@d_chmod($file,$mod);
		
		return true;
	}
	
	return false;
}

// there are three functions for speed optimization (though they do not work very fast...)

function d_clearstatcache()
{
	clearstatcache();
	d_ftplist('','flush');
	return true;
}

function d_is_file($file)
{
	if(file_exists($file)) return is_file($file);
	if(abs_path($file)==abs_path($GLOBALS['CFG']['ftp']['dir'])) return false;
	if(!$info = d_ftplist(dirname($file))) return d_error('Cannot get filelist');
	return in_array(abs_path($file),$info['files']);
}

function d_is_dir($file)
{
	if(file_exists($file)) return is_dir($file);
	if(abs_path($file)==abs_path($GLOBALS['CFG']['ftp']['dir'])) return true; // for root directory
	//echo dirname($file)."\n";
	if(!$info = d_ftplist(dirname($file))) return d_error('Cannot get filelist');
	//array_display($info);
	//print_r($info);
	return in_array(abs_path($file),$info['dirs']);
}

function d_file_exists($file)
{
	if(file_exists($file)) return true;
	if(abs_path($file)==abs_path($GLOBALS['CFG']['ftp']['dir'])) return true; // for root directory
	if(!$info = d_ftplist(dirname($file))) return false;
	return in_array(abs_path($file),$info['dirs']) || in_array(abs_path($file),$info['files']);
}

// the function to log errors. If $error_text is set to 'all', the function returns an array of all error messages.
// If $error_text is not set, the function returns the last error message.
// Else it logs the error message and returns false.
//
// example of usage: if(false) return d_error('You have set "false" in "if" condition');
function d_error($error_text = 0)
{
	static $errors = array();
	
	if($error_text === 'all') return $errors;
	if($error_text === 0) return (sizeof($errors)>0 ? $errors[sizeof($errors)-1] : '');
	
	if(function_exists('debug_backtrace'))
	{
		$tr = debug_backtrace();
		$error_text = '['.$tr[1]['function'].'] '.$error_text;
	}
	
	$errors[]=$error_text;
	return false;
}

// gets the address of temporary directory
function get_tmp_dir()
{
	global $CFG;
	static $tmpdir = 0;
	
	if($tmpdir!==0) return $tmpdir;
	
	if(!empty($CFG['tmpdir']))
	{
		if(is_writable($CFG['tmpdir'])) return ($tmpdir=$CFG['tmpdir']);
		return ($tmpdir=d_error('Temp directory "'.$CFG['tmpdir'].'" (see config.php) is not writable!"'));
	}
	
	$temp_dirs = array(session_save_path(), ini_get('upload_tmp_dir'), HOMEDIR, dirname(HOMEDIR), dirname(dirname(HOMEDIR)));
	
	foreach($temp_dirs as $v) if(is_writable($v)) return ($tmpdir=$v);
	return ($tmpdir=d_error('Could not find any writable directories. Create a directory, writable for all users (with CHMOD 777) and set its\' value in config.php.'));
}

function d_file_get_contents($f,$setr=true) /* $setr is for setwritable/setreadable functions, as they use this function :) */
{
	if($setr) setreadable(abs_path($f), true);
	return false!==(@$cont=file_get_contents(abs_path($f))) ? $cont : d_ftpcom('get_contents',$f);
}

/* write $contents to _existing_ file $f */

function d_file_put_contents($f,$contents,$setw=true)
{
	$f = abs_path($f);
	if($setw) setwritable($f, true);
	
	if(!FIX && @$fp = fopen($f, 'wb'))
	{
		fputs($fp, $contents);
		fclose($fp);
		
		return true;
	}
	
	return d_ftpcom('put_contents',$f,false,$contents);
}

function d_filesize($f)
{
	$f = abs_path($f);
	if(file_exists($f) && ($sz = filesize($f))!==false) return $sz;
	
	$t = d_filelist_fast(dirname($f));
	
	if(!empty($t['fsizes'][$f])) return $t['fsizes'][$f];
	
	return false;
}

function d_filelist_fast($dir)
{
	setreadable($dir,true);
	if(!(@$dh=opendir($dir)) && !(@$ftp_list=d_ftplist($dir))) return false;
	
	if($dh)
	{
		$dirs = $files = $fsizes = array();
		/* chdir($dir); */
		
		while(false!==(@$file=readdir($dh)))
		{
			if($file=='.' || $file=='..') continue;
			if(is_dir($dir.'/'.$file)) $dirs[]=$dir.'/'.$file;
			else $files[]=$dir.'/'.$file;
			$fsizes[$dir.'/'.$file] = filesize($dir.'/'.$file);
		}
	
		closedir($dh);
	}else return $ftp_list;
	
	return array('dirs'=>$dirs,'files'=>$files,'fsizes'=>$fsizes);
}

/* list dirs + files + all subdirs + all other files */

function d_filelist_all($dir)
{
	setreadable($dir,true);
	if(!(@$dh=opendir($dir)) && !(@$ftp_list=d_ftplist($dir))) return false;
	
	if($dh)
	{
		$dirs = $files = $fsizes = array();
		/* chdir($dir); */
		
		while(false!==(@$file=readdir($dh)))
		{
			if($file=='.' || $file=='..') continue;
			if(is_dir($dir.'/'.$file)) $dirs[]=$dir.'/'.$file;
			else $files[]=$dir.'/'.$file;
			$fsizes[$dir.'/'.$file] = filesize($dir.'/'.$file);
		}
	
		closedir($dh);
	}else
	{
		extract($ftp_list);
	}
	
	foreach($dirs as $v)
	{
		$res = d_filelist_all($v);
		$dirs = array_merge($dirs, $res['dirs']);
		$files = array_merge($files, $res['files']);
		$fsizes = array_merge($fsizes, $res['fsizes']);
	}
	
	return array('dirs'=>$dirs,'files'=>$files,'fsizes'=>$fsizes);
}


function d_filelist($dir,&$dirs,&$files,&$fsizes)
{
	setreadable($dir,true);
	if(!(@$dh=opendir($dir)) && !(@$ftp_list=d_ftplist($dir))) return false;
	
	if($dh)
	{
		while(false!==(@$file=readdir($dh)))
		{
			if($file=='.' || $file=='..') continue;
			if(d_is_dir($dir.'/'.$file)) $dirs[]=$dir.'/'.$file;
			else $files[]=$dir.'/'.$file;
		}
	
		closedir($dh);
	}else
	{
		$dirs=$ftp_list['dirs'];
		$files=$ftp_list['files'];
		$fsizes=$ftp_list['fsizes'];
	}
	
	return true;
}

/**
 * The function returns the type of file $f (for example, JPEG File)
 *
 * @param string $f
 * @return string
 */
function get_type($f)
{
	global $desc;
	
	if(empty($desc)) include(ROOT.'/system/types.php');
	$ext=pathinfo($f);
	@$ext=strtolower($ext['extension']);
	if(!empty($desc[$ext])) return $desc[$ext];
	if(empty($ext)) return false;
	else return false;
}

/**
 * Function returns the string that contains the string "Reason: the reason of error" or empty string, if the reason could not be determinated
 *
 * @return string
 */
function reason()
{
	global $php_errormsg;
	
	if(!error_reporting()) return '';
	
	$reasons=array();
	if(!empty($php_errormsg) && !preg_match('/undefined|deprecated/i',$php_errormsg)) $reasons[]=$php_errormsg;
	$errors=d_error('all');
	if(sizeof($errors)>0) foreach($errors as $v) $reasons[]=$v;
	
	array_unique($reasons);
	
	if(sizeof($reasons)==0) return '';
	else
	{
		foreach($reasons as $k=>$v) if($v[strlen($v)-1] == '.') $reasons[$k] = trim(substr($v,0,strlen($v)-1));
		return "\nReason".(sizeof($reasons)==1?'':'s').': '.implode(".\n",$reasons).'.';
	}
}

function gen_copy_name($dir, $file)
{
	if(abs_path(dirname($file)) != abs_path($dir) ) return $dir.'/'.basename($file);

	$v = $file;
	
	$dir=dirname($v);                 // write to $f the name of file, to $ext - it's full extension
	$fname=explode('.',basename($v));
	
	$f=$fname[0];
	unset($fname[0]);
	
	@$ext=implode('.',$fname);
	if(!empty($ext)) $ext='.'.$ext;
	
	$i=0;
	if(d_file_exists($name=$dir.'/'.$f.'-copy'.$ext)) while(d_file_exists($name=$dir.'/'.$f.'-copy'.(++$i).$ext));
	
	return $name;
}

/**
 * This function realizes the procedure of pasting files, that is common for all the versions of Dolphin.php . The $func_print parameter is used to specify some function for printing messages.
 *
 * @param string $func_print
 * @return bool
 */
function paste($func_print)
{
	if(!empty($_SESSION['copy']))
	{
		if(!empty($_GET['dir'])) $_SESSION['DIR']=clean($_GET['dir']);
		
		$error=false;
		if(abs_path($_SESSION['DIR'])==abs_path(dirname($_SESSION['copy'][0]))) // if it's the same dir, modify $_SESSION['copy'] to make the copies of files
		{
			foreach($_SESSION['copy'] as $k=>$v)
			{
				$name = gen_copy_name($_SESSION['DIR'], $v);
				//echo $v.' - '.$name;
				if(!d_copy($v,$name)) $error=true;
			}
		}else foreach($_SESSION['copy'] as $v) if(!d_copy($v,$_SESSION['DIR'].'/'.basename($v))) $error=true;

		$_SESSION['copy']=array();
		if(!$error)
		{
			$func_print('All files were copied successfully');
			return true;
		}else
		{
			$func_print('There were problems while copying files. '.reason());
			return false;
		}
	}
	if(!empty($_SESSION['cut']))
	{
		if(!empty($_GET['dir'])) $_SESSION['DIR']=clean($_GET['dir']);

		$error=false;

		if(abs_path($_SESSION['DIR'])!=abs_path(dirname($_SESSION['cut'][0]))) foreach($_SESSION['cut'] as $v) if(!@d_rename($v,$_SESSION['DIR'].'/'.basename($v)) && !(@d_copy($v,$_SESSION['DIR'].'/'.basename($v)) && @d_remove($v))) $error=true;

		$_SESSION['cut']=array();
		if(!$error)
		{
			$func_print('All files were cut successfully.');
			return true;
		}else
		{
			$func_print('There were some problems while cutting files. '.reason());
			return false;
		}
	}
}

define('NEED_MORE_TIME', -1);
define('FINISHED_COPY', 0);

function advanced_paste($dir /* dir to copy into */, $act /* 'cut' or 'copy' */, $files /* list of files to copy from */, &$bytes)
{
	//echo 'blya';
	switch($act)
	{
		case 'cut':
			$_SESSION['cut'] = $files;
			
			
			break;
		case 'copy':
			
			if(!empty($_SESSION['copy_cache'])) advanced_copy_cache($_SESSION['copy_cache']);
			else
			{
				$to = array();
				foreach($files as $k=>$v)
				{
					$to[$k] = gen_copy_name($dir, $v);
				}
				
				advanced_copy_start($files, $to);
			}
			
			//echo 'Pasting?';
			
			$bytes = advanced_copy_cont();
			
			if(sizeof($GLOBALS['__copy_cache']['files']) + sizeof($GLOBALS['__copy_cache']['dirs']) == 0 /* means end of copy process */)
			{
				$_SESSION['copy_cache'] = null;
				return FINISHED_COPY;
			}else
			{
				$_SESSION['copy_cache'] = advanced_copy_cache();
				return NEED_MORE_TIME;
			}
			
			break;
		default:
			return d_error('Unknown action '.$act.' when pasting files.');
			break;
	}
}

define('NEED_UPLOAD', -1);
/**
 * update_dolphin is a system function for Dolphin.php . You need to specify the function to print errors, if they happen. update_dolphin requires $_REQUEST['act'] to be set to 'upload-new' or 'download-new', and returns NEED_UPLOAD if it needs to show the upload form (it happens if it cannot download the archive from site). The upload form must contain an input field with name "files[]". If all is ok, returns TRUE, otherwise returns string, containing error description or special NEED_UPLOAD. You need to check for TRUE using === operator.
 *
 * @param string $print_err_func
 * @return mixed
 */
function update_dolphin($print_err_func)
{
	if(IS_DEVELOPER) return 'This operation is not permitted for you';
	
	ini_set('display_errors', 'On');
		
	/* disable attempts to download the archive with Dolphin.php by itself */
	if(!CAN_SELFUPDATE) $_REQUEST['act'] = 'upload-new';

	$tmp  = abs_path(get_tmp_dir());
	if(!$tmp) return 'No suitable temp directory found';
	$root = abs_path(ROOT);
			
	if(!$tmp || $tmp == $root || substr($tmp, 0, strlen($root)) == $root)
	{
		/* TODO: write normal answer if $tmp is ROOT or subdirectory of ROOT */
		return 'Update is impossible. '.reason();
	}
	
	chdir($tmp);
	
	if(empty($_FILES['files']) && $_REQUEST['act']!='download-new')
	{
		return NEED_UPLOAD;
	}
	
	if($_REQUEST['act']!='download-new') upload_files($tmp);
	else
	{
		$build = file_get_contents(MASTER_SITE.'files/dolphin-build.txt');
		
		$errtext = 'It seems that your server does not allow outgoing connections for PHP scripts, or '.MASTER_SITE.' is down. Try to upload archive with '.SNAME.' manually.';
		
		if(!$build) return NEED_UPLOAD;
		
		if(BUILD >= $build) return 'Your build is up-to-date';
		
		$dolphin = file_get_contents(MASTER_SITE.'files/dolphin-current.zip');
		
		if(!$dolphin) return NEED_UPLOAD;
		
		if(!d_file_put_contents($tmp.'/dolphin-current.zip', $dolphin)) return 'Cannot write '.show_file($tmp.'/dolphin-current.zip','file').' ('.$tmp.'/dolphin-current.zip).'.reason();
	}
	
	$f = $tmp.'/dolphin-current.zip';
	if(!file_exists($f)) return '<b>dolphin-current.zip</b> was not found';
	d_copy(ROOT.'/config.php',$cfg_old=$tmp.'/dolphin_config.php');
	
	require_once(ROOT.'/system/libs/pclzip.php');

	umask(0);
	/* note, that we check if extracting is possible _before_ deleting ROOT */
	$e=new PclZip($f);
	
	//echo '<!--';
	//echo 'file: '.$f.'<br>';
	
	// some hosters require PHP files and folders with PHP scripts to have special rights, so we need to
	// save rights for some essensial files
	
	$tochmod = array('','system','index.php','system/download.php','system/preview.php');
	$rights = array();
	foreach($tochmod as $v) $rights[ROOT.'/'.$v] = get_rights(ROOT.'/'.$v);
	
	d_remove(ROOT);
	//echo 'Removed ROOT.<br>';
	d_mkdir(ROOT,777);
	setwritable(ROOT);
	//echo 'Created ROOT.<br>';
	//echo 'ROOT is writable: '.(is_writable(ROOT) ? 'true' : 'false').'<br>';
	chdir(ROOT);
	//echo 'Changed directory to ROOT.<br>';
	//echo '-->';
	
	if(!$e->extract('.')) return '<b>dolphin-current.zip</b> could not be extracted. Upload the new version <b>via FTP</b>. Here are the contents of your <b>config.php</b>:<pre>'.htmlspecialchars(d_file_get_contents($cfg_old)).'</pre>';
	
	foreach($rights as $k=>$v) d_chmod($k,$v);
	
	if(!empty($_POST['save-login']) || $_REQUEST['act']=='download-new')
	{	
		/* delete BUILD, VERSION and NAME from config.php, they are now in core.php for compatibilty */
		$conf = d_file_get_contents($cfg_old);
		$conf = preg_replace('/define\\(\'(BUILD|VERSION|NAME)\'.*\\)\\;/sU','',$conf);
		d_file_put_contents(ROOT.'/config.php', $conf);
		
		$core = d_file_get_contents(ROOT.'/system/core.php');
		if(!$core)
		{
			$build = "undefined";
		}else
		{
			preg_match("/define\\('BUILD'\\,([0-9]+)\\)/is", $core, $m);
			$build = $m[1];
		}
		
		//echo '<!--Writing new information about build: '.$build.' and '.$version.'<br>-->';
	}
	
	d_unlink($f);
	//echo '<!-- Deleting archive<br> -->';
	
	if($build!='undefined' && $build > BUILD)
	{
		d_unlink($cfg_old);
		return true;
	}
	
	return 'Update did not complete successfully. Please upload the new version <b>via FTP</b>. Here are the contents of your previous <b>config.php</b>:<pre>'.htmlspecialchars(d_file_get_contents($cfg_old)).'</pre>';
}

function compress_js()
{
	$old = getcwd();
	// FULL VERSION
	
	// Dolphin.php Javascript (the sequence is important!)
	$f[]='render.js';
	$f[]='engine.js';
	$f[]='left_menu.js';
	$f[]='interface.js';
	// dolphin.js MUST be included last
	$f[]='dolphin.js';
	
	// JsHttpRequest (Dmitry Koterov)
	$f[]='../system/libs/JsHttpRequest.js';
	
	chdir(ROOT.'/f/');
	// compile library and disable cache by versioning
	// jscmp is my script which uses Dojo Toolkit to compress js files.
	exec('rm all*');
	exec('cat '.implode(' ', $f).' >all.src.js');
	
	$cont = d_file_get_contents(ROOT.'/f/all.src.js');
	
	ob_start();
	
	include_once(ROOT.'/full/full_func.php');
	include_once(ROOT.'/light/light_func.php');
	
	foreach(get_defined_constants() as $k=>$v)
	{
		$reg = '/([^\w])'.preg_quote($k).'([^\w])/s';
		
		if(strpos($cont, $k) !== false && preg_match($reg, $cont))
		{
			echo $k." = $v\n";
			
			$cont = preg_replace($reg, '${1}'.$v.'${2}', $cont);
		}
	}
	$buf = ob_get_clean();
	
	d_file_put_contents(ROOT.'/f/log.txt', $buf);
	
	d_file_put_contents(ROOT.'/f/all.src.js', $cont);
	
	exec('jscmp all.src.js all.'.FVER.'.js');
	
	
	// CSS versioning
	
	@exec('mv overall.'.BUILD.'.css overall.'.FVER.'.css');
	@exec('mv i/overall.'.BUILD.'.png i/overall.'.FVER.'.png');
	
	// LIGHT VERSION
	
	chdir(ROOT.'/light/');
	
	@exec('mv light.'.BUILD.'.css light.'.FVER.'.css');
	@exec('mv light.'.BUILD.'.js light.'.FVER.'.js');
	
	
	chdir($old);
}

/* returns smth like "~/music/" instead of "/home/yourock/music/" */
function getcwd_short()
{
	return ( substr($_SESSION['DIR'],0,strlen(abs_path(HOMEDIR))) == abs_path(HOMEDIR) ? '~'.substr($_SESSION['DIR'],strlen(abs_path(HOMEDIR))) : $_SESSION['DIR'] );
}

/**
 * The function that shows the message (in which is said that some operation completed successfully) $msg
 *
 * @param string $msg
 */
function mobile_message($msg)
{
	echo '<tr><td>';
	echo $msg;
	proceed();
}

/**
 *
 * @param    string  $url
 * @param    string  $arg
 * @param    string  $value
 * @return   string
 * @author   Nasibullin Rinat <rin at starlink ru>
 * @charset  ANSI
 * @version  1.0.5
 */
function urlReplaceArg($url, $arg, $value)
{
	if (preg_match('/([?&]' . preg_quote($arg, '/') . '=)[^&]*/s', $url, $m))
	{
		$v = is_null($value) ? '' : $m[1] . urlencode($value);
		return str_replace($m[0], $v, $url);
	}
	if (is_null($value))
	{
		return $url;
	}
	$div = strpos($url, '?') !== false ? '&' : '?';
	return $url . $div . $arg . '=' . urlencode($value);
}

/**
 * The function from PHP manual that returns size in bytes of PHP.INI sizes: e.g. 4K , 5M , 10G
 *
 * @param string $val
 * @return int
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

/**
 * This function shows links for sorting ( for example, <a href="sort_link&sort=$param&order=desc">$name &uparr;</a> ). Depending on $ver ( light or full ), it will use different design for links
 *
 * @param string $name
 * @param string $param
 * @param string $ver
 */
function show_sort($name,$param,$ver)
{
	if(@$_GET['sort']!=$param) $order='asc';
	else if(@$_GET['order']!='asc') $order='asc';
	else $order='desc';
	$url=urlReplaceArg($_SERVER['REQUEST_URI'],'order',$order);
	$url=urlReplaceArg($url,'sort',$param);
	
	switch($ver)
	{
	case 'light':
	    if(@$_GET['sort']!=$param) $order='';
	    else $order=' <font color="black"><b>'.($order=='asc' ? 'v' : '^').'</b></font>';
	    
		echo '<nobr><a href="'.str_replace('&','&amp;',$url).'" class="sort">'.$name.$order.'</a></nobr>';
		break;
	}
}

/**
 * The function uploads the files to $dir (or $_SESSION['DIR'] if $dir is not set).
 * returns TRUE if everything is ok, FALSE otherwise
 */
function upload_files($dir=false)
{
	global $error;
	if($dir===false) $dir = $_SESSION['DIR'];
	
	$error=false;
	foreach($_FILES['files']['name'] as $i=>$value)
	{
		if(empty($_FILES['files']['tmp_name'][$i]) /* error happened */) continue;
		$f=clean($dir."/".basename($value));
		//if(!@is_writable($dir)) d_chmod($dir);
		if(!@d_copy($_FILES['files']['tmp_name'][$i],$f)) $error=true;
	}
	return !$error;
}

/**
 * The function returns the current language string for $str
 *
 * @param string $str
 */
function lang($str)
{
	return $str;
}

function array_display($array)
{
	echo "<table border=1 cellpadding=2 cellspacing=2><tr><td colspan=2 style='text-align:center;'><b>array</b></td></tr>";
	
	foreach($array as $key=>$value)
	{
		if(!is_array($value))
		{
			echo "<tr><td width=100><i>".$key."</i></td><td>".$value."</td></tr>";
		}else
		{
			echo "<tr><td width=100><i><b style='color:red;'>".$key."</b></i></td><td>";
			array_display($value);
			echo "</td></tr>";
		}
	}

	echo "</table>";
}

/* returns array( extensions list (with dots), path directories list); */
function get_path_dirs()
{
	if(getenv('PATHEXT')) /* Windows... */
	{	
		return array(array_merge(explode(';', getenv('PATH')), array('.')), array_merge(array(""), explode(';', getenv('PATHEXT'))));
	}else
	{
		return array(array_merge(explode(':', getenv('PATH')), array('.')), array(""));
	}
}

function exec_split($out, $col=80)
{
	static $hyphen = "<img src='f/i/hyphen.png' width='13' height='9' title='string is too long' alt=''>";
	static $sep = false, $l = false, $unistr;
	
	if($sep === false)
	{
		$unistr = md5(uniqid(''));
		$sep = $hyphen.'<br>';
		$l = -strlen($unistr);
	}
	
	$out = explode("\n", $out);
	$res = '';
	
	foreach($out as $k=>$v)
	{
		$v = str_replace("\t", '    ', $v);
		$v = substr(chunk_split($v,$col,$unistr),0,$l);
		$res .= str_replace(array(' ', $unistr), array('&nbsp;', $sep), htmlspecialchars($v)).'<br>';
	}
	
	return substr($res,0,-4);
}

/* executes command $cmd, lines separated each $col symbols
   returns array ('startdir' => ..., 'output' => ..., 'dir' => ..., 'cmd' => ...) */

function exec_command($command, $col=80)
{	
	chdir($_SESSION['DIR']);
	//echo $_SESSION['DIR']."\n";
	
	$_RESULT['startdir'] = getcwd_short();
	
	$out = array();
	$ret = false;
	
	$command = ltrim($command);
	if(substr($command,0,3) == 'cd ' || substr($command,0,6) == 'chdir ')
	{
		if(substr($command,0,3) == 'cd ') $folder = trim(substr($command,3));
		else $folder = trim(substr($command,6));
		
		if($folder[0] == '"')
		{
			if($folder[strlen($folder)-1] == '"') $folder = stripcslashes(substr($folder, 1, strlen($folder)-2));
			else $folder = stripcslashes(substr($folder,1));
		}else
		{
			$folder = str_replace('\\ ', ' ', $folder);
		}
		
		if($folder[0]=='~') $folder = HOMEDIR.'/'.substr($folder,1);
		
		if(@chdir($folder))
		{
			$_SESSION['DIR'] = abs_path($folder);
			//echo $_SESSION['DIR'];
			$_RESULT['output']='';
		}else
		{
			$_RESULT['output']='cd: cannot change directory';
		}
	}else if(trim($command) == 'exit' || trim($command) == 'quit')
	{
		$_RESULT['exit'] = true;
	}else if(trim($command) == 'pwd')
	{
		$_RESULT['output'] = getcwd();
	}else
	{
		$tmp = explode(' ', $command);
		$cmd = $tmp[0];
		
		$ex = false; /* exists ? */
		list($dirs, $exts) = get_path_dirs();
		
		foreach($dirs as $dir)
		{
			foreach($exts as $ext)
			{
				if(file_exists($dir.'/'.$cmd.$ext))
				{
					$ex = true;
					break(2);
				}
			}
		}
		
		
		/*if(!$ex)
		{
			$out[] = 'command not found';
		}else
		{*/
		exec('('.$command.') 2>&1 <"'.(file_exists('/dev/null') ? '/dev/null' : 'nul').'"', $out, $ret);
		if($ret!=0 && !$ex) $out = array(GREET.': '.$cmd.': command not found');
		/*}*/
		
		//$fp = popen($command.' 2>&1')
		$_RESULT['output'] = implode("\n", $out);
	}
	
	$add = GREET.'$ ';
	
	$_RESULT['output'] = exec_split($_RESULT['output'], $col);
	$_RESULT['cmd'] = exec_split($add.$command, $col);
	$_RESULT['cmd'] = substr($_RESULT['cmd'],strlen($add)+strlen('&nbsp;')-1);
	
	$_RESULT['dir'] = getcwd_short();
	
	return $_RESULT;
}

function add_to_zip($files)
{
	@set_time_limit(0);
	if(sizeof($files)>1) $name=$_SESSION['DIR'].'/'.basename($_SESSION['DIR']);
	else $name=$_SESSION['DIR'].'/'.substr($n=basename($files[0]),0,($p=strrpos($n,'.'))===false ? strlen($n) : $p);
	if(file_exists($name.'.zip')) $name.='-'.time();
	$name.='.zip';
	
	
	require_once(ROOT.'/system/libs/pclzip.php');
	$arc=new PclZip($name);
	
	foreach($files as $k=>$v) $files[$k]=basename($v);
	$old = getcwd();
	setwritable($_SESSION['DIR'], true);
	@chdir($_SESSION['DIR']);
	
	$res = $arc->create($files) or d_error($arc->errorInfo(true));
	@chdir($old);
	
	return $res;
}

/* extract $files
   $mode - either 'extract' (each archive is extracted to it's own folder)
   or 'extract_here' (all is extracted to current directory) */

function unzip_files($files, $mode)
{
	@set_time_limit(0);
	$old = getcwd();
	setwritable($_SESSION['DIR'], true);
	if(!@chdir($_SESSION['DIR'])) return d_error('Cannot change directory to "'.$_SESSION['dir'].'".');
	
	require_once(ROOT.'/system/libs/pclzip.php');

	$extract=array();
	foreach($files as $k=>$v) $extract[$v]=new PclZip(basename(clean($v)));
	
	if($mode=='extract')
	{
		foreach($extract as $k=>$v)
		{
			if(!$v->extract(PCLZIP_OPT_PATH,substr($n=basename($k),0,($p=strrpos($n,'.'))===false ? strlen($n) : $p)))
				$error=!d_error($v->errorInfo(true));
		}
	}else/* if($mode=='extract_here')*/
	{
		foreach($extract as $k=>$v)
		{
			if(!$v->extract('.')) $error=!d_error($v->errorInfo(true));
		}
	}
	
	@chdir($old);
	return !isset($error);
}
?>