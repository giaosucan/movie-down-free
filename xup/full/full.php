<?
   if(!function_exists('dolphin_handler')) die('dolphin not found');
   include_once(ROOT.'/full/full_func.php');
   if(@$_GET['act']=='logout') logout();
   else if(@$_GET['act']=='edit') die(include(ROOT.'/full/edit.php'));
   else if(@$_GET['act']=='terminal') die(include(ROOT.'/full/terminal.php'));
   else if(!empty($_REQUEST['act'])) die(include(ROOT.'/full/actions.php'));
?>
<html>
<head>
<title><?=NAME?> full version</title>
<meta name="author" content="Yuriy Nasretdinov" />
<style>
body { overflow: hidden; }
</style>
</head>
<body onload="D.init();" oncontextmenu="if(R && !R.is_inp(event)) return false;" onselectstart="/* onselectstart is IE-specific */ if(R && !R.is_inp(event)) return false;" onresize="if(D) D.resize();" onkeydown="if(I) return I.handle_keydown(event);"><a href="index.php?version=light&DIR=." id="safelink"><b>switch to light version...</b></a><script><!-- // loading
if(!document.getElementById || !document.getElementById('safelink') || !document.body || !document.createElement || !document.body.appendChild) /* at least DOM browsers are supported */ { alert('Sorry, but your browser is surely unsupported. Click OK to go to light version...'); window.location.href = 'index.php?version=light&DIR=.&<?=SID?>'; }else{ var l=document.getElementById('safelink');var d=document.body.appendChild(document.createElement('DIV'));var s=d.style;document.body.style.padding="0px";document.body.style.margin="0px";s.position="absolute";s.background="#4ea6f1";s.color="white";s.fontFamily="Courier New, Courier";s.fontSize="10pt";s.padding="3px";s.zIndex='10';d.innerHTML='loading, please wait<span id="dots">...</span> <a href="?version=light&DIR=." style="color: green;"><b><u>switch to light version</u></b></a>';l.style.display='none'; d.id="loading";interv=setInterval(function(){var d=document.getElementById('dots');if(!this.cnt)this.cnt=2;if(cnt==1)d.innerHTML='...';if(cnt==2)d.innerHTML='&nbsp;..';if(cnt==3)d.innerHTML='.&nbsp;.';if(cnt==4)d.innerHTML='..&nbsp;';cnt++;if(cnt>4)cnt=1;},600);   }
//-->
</script><script language="javascript" src="f/<?=(IS_DEVELOPER ?  'compress.php?act=js' : 'all.'.BUILD.'.js')?>"></script><script><!--
/* prevent from session expire */
setInterval(D.pingpong, <?=max(intval(session_cache_expire())*60*1000/2, 1000*60)?>);
//-->
</script><noscript><table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td align="center" valign="middle"><big>JavaScript MUST be enabled in order to use full version.<br><br><a href="index.php?version=light&DIR=.&<?=SID?>">click here to use light version</a></big></td>
</tr></table></noscript><link href="f/overall.<?=FVER?>.css" rel="stylesheet" /><div id="very_main" align="center" style="height: 100%; padding: 0px; margin: 0px; visibility: hidden;"><div id="main_div"><table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr height=25>
		<td colspan=4><div align="center">
		
		<?if(!defined('BAD_CONFIG')) { ?>
		
		<b>HINT:</b> please allow your pop-up blocker to open windows and prompts. It is required for correct work of this administrative tool.
		
		<? } else {?>
		
		<b>WARNING: </b> your <b><a href="#" onclick="I.window_open('index.php?act=edit&file=<?=rawurlencode(ROOT.'/config.php')?>', 'edit_configphp', 640, 480);">config.php</a></b> is corrupted! Using default configuration.
		
		<? } ?></div></td>
	</tr>
	
	<tr height="30">
		<td width="100%"><table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr height="30">
				<td width="22"><img src="f/i/no.png" width="22" height="30" alt="" title="" border="0" style="background: url('f/i/overall.<?=FVER?>.png'); background-position: -37px -360px;" id="header_icon"></td>
<td id="name_of_folder"></td>
<td width="28"><img id="btn_close" src="f/i/no.png" onmouseover="if(I) I.im(this,'h');" onmouseout="if(I) I.im(this,'');" onmousedown="if(I) I.im(this,'d');" onmouseup="if(I) { I.im(this,'h'); if(confirm('Do you really want to log out?')) window.location.href='index.php?act=logout'; }" alt="Close" title="Close" style="background: url('f/i/overall.<?=FVER?>.png'); background-position: -72px -360px;" width="28" height="30" /></td>
				</tr>
		</table></td>
	</tr>
	
	<tr height="24">
		<td width="100%"><table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr height="24">
				<td width="4" class="leftbg"></td>
				<td id="upperpanel"></td>
				<td width="40"><a href="<?=MASTER_SITE?>" target="_blank"><img src="<?=MASTER_SITE?>getlogo/" width="40" height="24" alt="Go to <?=SNAME?> homepage" title="Go to <?=SNAME?> homepage" border="0"></a></td>
				<td width="4" class="rightbg"></td>
			</tr>

		</table></td>
	</tr>
	
	<tr height="30">
		<td width="100%"><table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr height="30">
				<td width="4" class="leftbg"></td>
				<td class="panel" id="panel"></td>
				<td width="4" class="rightbg"></td>
			</tr>

		</table></td>
	</tr>
	
	<tr height="2">
		<td width="100%"><table width="100%" border="0" cellpadding="0" cellspacing="0">

		<tr height="2">
			<td width="4" class="leftbg"></td>
			<td class="sepbg"><img src="f/i/no.png" width="1" height="2" alt="" title="" border="0"></td>
			<td width="4" class="rightbg"></td>
		</tr>

		</table></td>
	</tr>
	
	<tr height="22">
		<td width="100%"><table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr height="22">
				<td width="68"><img src="f/i/no.png" width="68" height="22" alt="" title="" style="background: url('f/i/overall.<?=FVER?>.png'); background-position: -0px -516px;" border="0" id="address_img"></td>
				<td class="addressbar"><input type="text" name="address" id="address" value="." onkeydown="if(event.keyCode==13 /*ENTER*/) Interface.change_address();"></td>
				<td width="85"><img id="btn_go" src="f/i/no.png" onmouseover="if(I) I.im(this,'h');" onmouseout="if(I) I.im(this,'');" onmousedown="if(I) I.im(this,'d');" onmouseup="if(I) { I.im(this,'h'); I.change_address(); }" alt="Go" title="Go" style="background: url('f/i/overall.<?=FVER?>.png'); background-position: -0px -450px;" width="85" height="22" /></td>
			</tr>

		</table></td>
	</tr>
	
	<tr>
		<td width="100%"><table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">

			<tr id="">
				<td width="5" class="leftbg"></td>
				<!-- the left menu -->
				<td width="240" style="background: #c4c8d4;" id="left"><div id="left_menu_div">&nbsp;</div></td>
				<!-- /the left menu -->
				
				<td id="main"><div id="files" onmousedown="if(R) return R.handle_down(event);">&nbsp;</div></td>
				<td width="4" class="rightbg"></td>
			</tr>

			<tr height=1>
				<td width="5" class="leftbg"></td>
				<td colspan=2></td>
				<td width="4" class="rightbg"></td>
			</tr>

		</table></td>
	</tr>
	
	<tr height="27">
		<td width="100%"><table width="100%" border="0" cellpadding="0" cellspacing="0">

			<tr height="27">
				<td width="4"><img src="f/i/no.png" width="4" height="27" alt="" title="" style="background: url('f/i/overall.<?=FVER?>.png'); background-position: -96px -516px;" border="0"></td>
				<td class="btmbg"><div id="status_str"></div></td>
				<td width="218"><img src="f/i/btmright.png" width="218" height="27" alt="" title="" border="0"></td>
			</tr>

		</table></td>
	</tr>
	
	<tr height="60">
		<td colspan=4><?stats();?></td>
	</tr>
</table></div></div></body></html>