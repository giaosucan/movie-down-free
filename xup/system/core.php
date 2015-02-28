<?
define('START_TIME',array_sum(explode(' ',microtime())));
define('ROOT',dirname(dirname(__FILE__)));


define('IS_DEVELOPER',file_exists(ROOT.'/release.php'));

define('CAN_SELFUPDATE',ini_get('allow_url_fopen'));

define('BUILD',78);

if(IS_DEVELOPER)
{
	define('LAST_UPDATE', filemtime(ROOT.'/../dolphin.zip'));
	define('FVER', BUILD+1); /* file (js, css) version */
}else
{
	define('FVER', BUILD);
}

define('VERSION','0.1 RC0');
define('SNAME','Dolphin.php'); /* the short name of product */
define('NAME',SNAME.' '.VERSION/*.' (build '.BUILD.')'*/);
define('GREET',preg_replace("/\\s+/s",'','websh-'.VERSION/*.BUILD*/));

define('MASTER_SITE','http://dolphin-php.org/');

if(!isset($_REQUEST)) die('<b>'.SNAME.' error:</b> PHP must have version not less than 4.1.0');

session_name('S');
session_start() or die('<b>'.SNAME.' error:</b> PHP must have support of sessions in order to use Dolphin.');

$fp = fopen(ROOT.'/config.php','rb') or die('<b>'.SNAME.' error:</b> cannot read config.php');
if(!@eval('?>'.fread($fp, filesize(ROOT.'/config.php')).'<?return true;') || !isset($CFG))
{
	define('BAD_CONFIG', true);
	require_once(ROOT.'/system/config-default.php');
	if(!isset($CFG)) die('<b>'.SNAME.' error:</b> installation of file manager is corrupted. Please install it again.');
}
fclose($fp);

/* the next piece is for backward compatibility and will be removed after version 1.0 */
if(!defined('MAX_COPY_TIME')) define('MAX_COPY_TIME',4);    // how many seconds the files can be copied
/* end of piece */

require_once(ROOT.'/system/func.php');


auth();

/*
$_GET, $_POST and $_REQUEST are saved in case session has expired suddenly
*/

if(!empty($_SESSION['savedreq']))
{
	list($r,$g,$p) = $_SESSION['savedreq'];
	$_REQUEST = array_merge($_REQUEST, $r);
	$_POST = array_merge($_POST, $p);
	$_GET = array_merge($_GET, $g);
	
	$_SESSION['savedreq'] = false;
}

if(!empty($_GET['version'])) $_SESSION['version'] = $_GET['version'];
if(!empty($_SESSION['version']) && !defined('VER') /* VER can be defined as 'full' in order to make AJAX requests work as they should */) define('VER',$_SESSION['version']);
?>