<?
require('core.php');

$f=clean($_GET['file']);

//ob_start();

if(@$_GET['size']=='small' || !empty($_GET['info'])) list($w,$h)=array(160,120);
else if(@$_GET['size']=='normal') list($w,$h)=array(220,220);
else if(@$_GET['size']=='big') list($w,$h)=array(620,460);
else die();

if(!send_thumbnail($f,$w,$h))
{
//	print_r(d_error('all'));
//	fputs(fopen('log.txt','w'),ob_get_clean());
	header('Location: ../images/no-preview-small.png');
}
?>