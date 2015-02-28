<?
if(!function_exists('dolphin_handler')) die('dolphin not found');

$f = clean($_GET['file']);

if($_SERVER['REQUEST_METHOD']=='GET')
{
	if(!d_file_exists($f)) die('File does not exist!');
	
	if(!empty($_GET['img']))
	{
		$NOHANDLER=true;
?><html><head><title>View image <?=basename($f)?></title><style>body {margin:0px;padding:0px} td{vertical-align:middle;text-align:center;align:center;}</style></head><body><table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center"><a href="javascript:window.close()" title="Close window"><img src="system/preview.php?file=<?=rawurlencode($f)?>&size=big" border="0"></td></tr></table></body></html>
<?		die();
	}
	
	if(show_size($f,false) > 100*1024) die('File too big!');
	
	$wr = setwritable($f,true);
	?>
<html>
<head>
	<title>Edit file <?=basename($f)?></title>
	<link href="f/overall.<?=FVER?>.css" rel="stylesheet" />
	<style>
	body {margin: 10px; overflow: hidden;}
	body, td { font-family: Tahoma, Arial, Sans-serif; font-size: 11px; }
	textarea { font-family: monospace, Courier New; font-size: 12px; width: 620px; height: 350px; overflow: scroll; }
	#loading {background-color: #4ea6f1; font-family: Courier New, monospace; font-size: 12px;  color: white; position: absolute; top: 0px; left: 0px; visibility: hidden; }
	</style>
	<script>
	var cont_hash = false;
	</script>
	<script src="f/all.<?=FVER?>.js"></script>
	<script>
	/* the simpliest strhash */
	
	function count_hash()
	{
		return strhash($('content').value);
	}
	
	function strhash(str)
	{
		return ''+str;
	}
	
	function send_changes()
	{
		document.getElementById('save').disabled = true;
		document.getElementById('changes').innerHTML = '&nbsp;';
		
		Dolphin.qr('index.php?act=save-file', { filename_encoded: "<?=rawurlencode($f)?>", content: document.getElementById('content').value }, function(res,err)
		{
			if(err) alert(err);
			else
			{
				document.getElementById('changes').innerHTML = 'File has been saved successfully.';
				document.getElementById('save').disabled = false;
			}
		},true,"saving...");
	}
	</script>
</head>
<body onbeforeunload="if(cont_hash!=count_hash()) return 'Leave unsaved changes?';" onkeydown="if(event.ctrlKey && (event.charCode && event.charCode==115||event.keyCode==83) /* s */) { if(event.returnValue) event.returnValue=false; if(event.preventDefault) event.preventDefault(); if(event.stopPropagation) event.stopPropagation(); if(!$('save').disabled) $('save').onclick(event); return false; }" onload="cont_hash = count_hash();"><div id="loading">&nbsp;</div><table height="100%" width="100%" cellpadding="0" cellspacing="0" border="0"><tr height="40"><td>
<h2 align="left"><?=show_file($f)?></h2>
<form action="index.php?act=edit&file=<?=rawurlencode($f)?>" enctype="multipart/form-data" method="POST">
</td></tr><tr><td><textarea cols=10 rows=10 name="content" id="content" wrap="virtual"><?=str_replace(':','&#58;',htmlspecialchars(d_file_get_contents($f)))?></textarea></td></tr><tr height="40"><td>
<div align="center"><button name="save" value="save changes" onclick="cont_hash=count_hash(); send_changes();" id="save" <?=($wr?'':'disabled="disabled"')?>><b>save changes</b><small> (Ctrl+S)</small></button>&nbsp;&nbsp;&nbsp;<input type="submit" name="edit" value="save &amp; close window" onclick="cont_hash=count_hash();" <?=($wr?'':'disabled="disabled"')?>></div>
</td></tr><tr height="20"><td>
<div style="color: <?=($wr?'green':'red')?>" id="changes"><?=($wr?'&nbsp;':'Warning! File is not writable!')?></div></form>
</td></tr></table></body>
</html>
<?}else
{
	if(get_magic_quotes_gpc()) $_POST['content'] = stripslashes($_POST['content']);
	if(d_file_put_contents($f, $_POST['content']))
	{
		if(empty($_POST['save']))
		{
			echo '<script>window.close();</script>File successfully changed. You may now close the window.';
		}else
		{
			header('location: index.php?act=edit&file='.rawurlencode($f).'&success=true');
		}
	}else
	{
		echo 'An error occured. '.reason();
	}
}
?>