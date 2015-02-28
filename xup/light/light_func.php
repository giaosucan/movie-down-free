<?
/**
 * This function prints the common operations for files and folders - delete, cut, copy and rename both for dirs and files. Function is used in printing file list.
 *
 */
function s_act()
{
	global $f;
	return false;
	?><a href="index.php?act=delete&file=<?=$f?>" title="Delete" onclick="return del(this);"><img src="images/delete.png" width="16" height="16" border="0"></a> <a href="index.php?act=cut&file=<?=$f?>" title="Cut"><img src="images/cut.png" width="16" height="16" border="0"></a> <a href="index.php?act=copy&file=<?=$f?>" title="Copy"><img src="images/copy.png" width="16" height="16" border="0"></a> <a href="index.php?act=rename&file=<?=$f?>" title="Rename" onclick="return rnm(this);"><img src="images/rename.png" width="16" height="16" border="0"></a><?
}

function tr($i,$type)
{
	global $f;
	?><tr height="24" onmouseover="high(this,event);" onmouseout="high(this,event);" onclick="chk(this,event);" ondblclick="window.location.href='index.php?<?=($type=='dir'?'DIR='.$f:'act=edit&amp;file='.$f)?>';" id="t<?=$i?>" title="Click to select <?=($type=='dir'?'directory':'file')?>, Double Click to <?=($type=='dir'?'open':'edit')?> it" bgcolor="#e8eef7"><?
}

/**
 * Function prints the message $msg for light version of Dolphin.php . It also adds "back" link. If you want to change the href of "back" link, you can set it via $where parameter. You can also set the width of the window (e.g. 100% - needed in some cases for stupid opera)
 *
 * @param string $msg
 * @param string $where
 */
function light_message($msg,$where=false)
{	
	if(!empty($_REQUEST['back'])) $where=$_REQUEST['back'];
	?><table cellpadding="2" cellspacing="2" border="0" align="center" bgcolor="<?=(strpos($msg,'<form')===false ? '#fad163' : '#eeeeee')?>">
	   <tr>
	    <td>
<?if(!isset($GLOBALS['nomain']) || $GLOBALS['nomain']) { ?>

<a href="<?
	     if(!$where) echo 'index.php?DIR='.urlencode($_SESSION['DIR']).(!empty($_SESSION['p']) ? '&amp;p='.$_SESSION['p'] : '').(!empty($_SESSION['filter']) ? '&amp;filter='.$_SESSION['filter'] : '');
	     else echo $where;
	     ?>" class="back">&lt;- back</a>
	  <?=(substr($msg,0,2)=='<h' ? '<br>' : '<br><br>')?>
	  
<? } ?>

<?=$msg?>
	    </td>
	   </tr>
	  </table>
<?
}
/**
 * Function shows the contents of $dirs or $files array (formatted, of course). 'dirs' will show $dirs, 'files' will show $files
 *
 * @param string $what
 */
function light_show($what)
{
	global $fsizes,$dsizes;
	static $i=0;
	switch($what)
	{
	case 'dirs':
		global $dirs,$f,$drives;
		foreach($dirs as $v){ $f=rawurlencode($v);if(SHOW_DIRSIZE)$sz=(isset($dsizes[$v])?show_size('',true,$dsizes[$v]):show_size($v)); $i++;?>
	   <?tr($i,'dir');?>
	    <td><div style="overflow: hidden;"><nobr><input type="checkbox" name="files[]" id="c<?=$i?>" value="<?=htmlspecialchars($v)?>" onclick="c_sel(this);" align="absmiddle"><img src="images/<?=(!empty($drives)?'hdd':'dir')?>.png" width="16" height="16" alt="Directory" align="absmiddle"> <a href="index.php?DIR=<?=$f?>" title="Open directory"><?=basename($v)?></a></nobr></div></td>
	    <td width="75"><nobr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=(SHOW_DIRSIZE?$sz:'<a href="index.php?act=show-size&amp;file='.$f.'" class="show"><i>show</i></a>')?>&nbsp;&nbsp;</nobr></td>
	   </tr>
	   <?}
		break;
	case 'files':
	    global $files,$f;
		foreach($files as $v){ $f=rawurlencode($v);$sz=(isset($fsizes[$v])?show_size('',true,$fsizes[$v]):show_size($v)); $i++;?>
	   <?tr($i,'file');?>
	    <td><div style="overflow: hidden;"><nobr><input type="checkbox" name="files[]" id="c<?=$i?>" value="<?=htmlspecialchars($v)?>" onclick="c_sel(this);" align="absmiddle"><img src="images/file.png" width="16" height="16" alt="File" align="absmiddle"> <a href="index.php?act=edit&amp;file=<?=$f?>" title="Edit file"><?=basename($v)?></a></nobr></div></td>
	    <td align="left" width="75"><nobr><a href="system/download.php?file=<?=$f?>&amp;<?=session_name()?>=<?=session_id()?>" title="Download"><img src="images/download.png" width="16" height="16" border="0"></a><?=$sz?>&nbsp;&nbsp;</nobr></td>
	   </tr>
	   <?}
		break;
	}
}

/**
 * Function shows the filelist in the light version of file manager ( $dirs - the global array of directories, $files - the global array of files )
 *
 */
function light_show_filelist()
{
	global $dirs,$letters_d,$letters_f,$files,$files_c,$dirs_c;
	global $pages, $page, $per; // the pages count
	
	if($pages>1)
	{	
		//$s = ($page-1)*$per; // start
		$s = $page;
		//$e = $page*$per; // end
		$e = $page+$per;
		
		$i=0;
		
		$dirs_c = $dirs;
		$files_c = $files;
		
		$dirs=$files=array();
		
		foreach($dirs_c as $v) if($i++>=$s && $i<=$e) $dirs[]=$v;
		foreach($files_c as $v) if($i++>=$s && $i<=$e) $files[]=$v;
	}
	
	if(sizeof($files)+sizeof($dirs)>0)
	{
		if($_GET['sort']=='name' && $_GET['order']=='desc')
		{
			light_show('files');
			light_show('dirs');
		}else
		{
			light_show('dirs');
			light_show('files');
		}
	}else
	{
		echo '<tr height=50 bgcolor="#eeeeee"><td colspan=3><div align="center">'.(!empty($_GET['filter']) && (DIRS+FILES)>0 ? 'Result of filtering is empty' : 'The directory is empty').'</div></td></tr>';
	}
}

// class for generating the list of pages
// copyright Dmitry Koterov 2005
// http://forum.dklab.ru/php/advises/Tools_pageslistGenerationOfPaginalNavigation.html

class Tools_PagesList
{
        function make($pageSize, $nElts, $curElt=null, $url=false, $arg="p")
        {
                $pages = array();
                $pageSize = intval($pageSize);
                if ($pageSize <= 0) $pageSize = 10;
                if ($url === false) $url = $_SERVER["REQUEST_URI"];
                if ($curElt === null) $curElt = isset($_GET[$arg])? $_GET[$arg] : 0;
                for ($n=1,$i=0; $i<$nElts; $i+=$pageSize,$n++) {
                        if (preg_match("/([?&]$arg=)\d+/s", $url)) {
                                $purl = preg_replace("/([?&]$arg=)\d+/s", '${1}'.$i, $url);
                        } else {
                                $div = strpos($url, "?")? "&" : "?";
                                $purl = $url.$div.$arg."=".$i;
                        }
                        $pages[] = array(
                                "n"       => $n,
                                "pos"     => $i,
                                "isfirst" => false,
                                "islast"  => false,
                                "url"     => $purl,
                                "iscur"   => $curElt>=$i && $curElt<$i+$pageSize,
                        );
                }
                if (count($pages)) {
                        $pages[0]["isfirst"] = 1;               
                        $pages[count($pages)-1]["islast"] = 1;
                        if ($curElt >= $nElts) $pages[count($pages)-1]["iscur"] = true;
                }
                return $pages;
        }
       
        function frame($frameSize, $pageSize, $nElts, $curElt=null, $url=false, $arg="p")
        {
                $pages = Tools_PagesList::make($pageSize, $nElts, $curElt, $url, $arg);
                for ($i=0; $i<count($pages); $i++) if ($pages[$i]['iscur']) break;
                $cur = $i;
                $start = 0;
                if ($i > $frameSize/2) $start = intval($i-$frameSize/2);
                if (count($pages) - $start < $frameSize) $start = count($pages) - $frameSize;
                $start = max($start, 0);
                $framePages = array_slice($pages, $start, $frameSize);
               
                $frame = array();
                if ($start != 0) {
                        $prev = max($cur - $frameSize, 0);
                        $frame['prev'] = $pages[$prev];
                }
                if ($start + $frameSize < count($pages)) {
                        $next = min($cur + $frameSize, count($pages)-1);
                        $frame['next'] = $pages[$next];
                }
                $frame['pages'] = $framePages;
               
                return $frame;
        }
}
?>