<?
$CFG = array(

'login' => 'yakiwi',
'password' => 'y@kiwi',
'ftp' => false/*array(
	'host'       => '',
	'login'      => '',
	'password'   => '',
	'dir'        => '',
	'fix_rights' => false,
	)*/,
'tmpdir' => false,
);

define('HOMEDIR','../picz'); // the first directory, where you go

// performance
define('USE_RESAMPLE',true);
define('SHOW_DIRSIZE',false);
define('DIRSIZE_LIMIT',1);    // how many seconds the directory size can be counted
define('MAX_COPY_TIME',4);    // how many seconds the files can be copied

define('CHARSET','utf-8');
?>