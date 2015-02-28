<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
class HTMLForm {
	var $error_color = array(
		'empty'		=>	'#0000FF',
		'number'	=>	'#7EBA01',
		'>0'		=>	'#47A2CB',
		'>=0'		=>	'#585CFE',
		'url'		=>	'#202020',
	);
	function createSQL($config_arr,$inp_arr) {
		if ($config_arr[0] == 'INSERT') {
			foreach ($inp_arr as $key=>$arr) {
				if (!$arr['table']) continue;
				$s1 .= '`'.$arr['table'].'`,';
				if ($arr['type'] == 'hidden_value')	$s2 .= '\"'.$arr['value'].'\",';
				else $s2 .= '\'$'.$key.'\',';
			}
			$s1 = substr($s1,0,-1);
			$s2 = substr($s2,0,-1);
			$sql = "INSERT INTO ".$config_arr[1]." (".$s1.") VALUES (".$s2.")";
		}
		elseif ($config_arr[0] == 'UPDATE') {
			foreach ($inp_arr as $key=>$arr) {
				global $$key;
				if (!$arr['table']) continue;
				if ($arr['update_if_true'] && !eval('return ('.$arr['update_if_true'].');')) continue;
				
				if ($arr['type'] == 'hidden_value' && !$arr['change_on_update']) continue;
				if ($arr['type'] == 'hidden_value')	$s1 .= $arr['table'].' = \''.$arr['value'].'\', ';
				else $s1 .= $arr['table'].' = \"$'.$key.'\", ';
			}
			$s1 = substr($s1,0,-2);
			if ($config_arr[2] && $config_arr[3]) $sql = "UPDATE ".$config_arr[1]." SET ".$s1." WHERE ".$config_arr[2]." = '\$".$config_arr[3]."'";
			else $sql = "UPDATE ".$config_arr[1]." SET ".$s1."";
		}
		return $sql;
	}
	function createTableArray($inp_arr,$field_arr) {
		$keys = array_keys($inp_arr);
		$tb_arr = array();
		for ($i=0;$i<count($keys);$i++)
			$tb_arr[$keys[$i]] = $field_arr[$i];
		return $tb_arr;
	}
	function getWarnString($error_arr) {
		if (!$error_arr) return;
		if (in_array('empty',$error_arr)) $warn = "<b style='color:".$this->error_color['empty']."'>*</b> : Fill require field<br>";
		if (in_array('number',$error_arr)) $warn .= "<b style='color:".$this->error_color['number']."'>*</b> : Require only numbers<br>";
		if (in_array('>0',$error_arr)) $warn .= "<b style='color:".$this->error_color['>0']."'>*</b> : Not allow 0 value<br>";
		if (in_array('>=0',$error_arr)) $warn .= "<b style='color:".$this->error_color['>=0']."'>*</b> : Not allow below 0<br>";
		if (in_array('url',$error_arr)) $warn .= "<b style='color:".$this->error_color['url']."'>*</b> : Require a URL<br>";
		return substr($warn,0,-4);
	}
	function checkForm($inp_arr) {
		
		foreach ($inp_arr as $key=>$arr) {
			if ($arr['type'] == 'hidden_value') continue;
			global $$key;
		}
		foreach ($inp_arr as $key=>$arr) {
			if (!$$key && $arr['can_be_empty']) continue;
			if ($arr['type'] == 'hidden_value') continue;
			if ($arr['check_if_true'] && !eval('return ('.$arr['check_if_true'].');')) continue;
			
			$$key = htmlspecialchars($_POST[$key]);
			//fix IE6 bug
			if ($arr['type'] == 'text' && $$key == '&lt;P&gt;&amp;nbsp;&lt;/P&gt;') { $$key = ''; }
			if ($arr['type'] == 'text' && $$key == '&lt;br&gt;') { $$key = ''; }
			//if ($arr['type'] == 'text') { $$key = nl2br($$key); }
			if ($$key == '' && !$arr['can_be_empty']) $error_arr[$key] = 'empty';
			if (ereg("^function::*::*",$arr['type'])) { $z = split('::',$arr['type']); $type = $z[1]; }
			else $type = $arr['type'];
			if (!$error_arr[$key]) {
				if ($type == 'number' && !is_numeric($$key)) $error_arr[$key] = 'number';
				elseif ($type == 'number' && $arr['>0'] && $$key <= 0 ) $error_arr[$key] = '>0';
				elseif ($type == 'number' && $arr['>=0'] && $$key < 0 ) $error_arr[$key] = '>=0';
				elseif ($type == 'url' && !ereg("[http|mms|ftp|rtsp]://[a-z0-9_-]+\.[a-z0-9_-]+",$$key)) $error_arr[$key] = 'url';
			}
		}
		return $error_arr;
	}
	function createForm($title,$inp_arr,$error_arr) {
		global $warn;
		echo "".
    "<form method=post enctype=\"multipart/form-data\">".
		"<table class=border cellpadding=2 cellspacing=0 width=90%>".
		"<tr><td colspan=2 class=title align=center>$title</td></tr>";
		if ($warn) echo "<tr><td class=fr><b style='color:red;'>Error</b></td><td class=fr_2 style='color:red'>$warn</td></tr>";
		
		foreach($inp_arr as $key=>$arr) {
			if ($arr['type'] == 'hidden_value') continue;
			global $$key;
			if ($arr['always_empty']) $$key = '';
			if (ereg("^function::*::*",$arr['type'])) {
				$ex_arr = split('::',$arr['type']);
				$str = $ex_arr[1]($$key);
				$type = 'function';
			}
			else $type = $arr['type'];
			echo "<tr><td class=fr width=30%><b>".$arr['name']."</b>".(($arr['desc'])?"<br>".$arr['desc']:'')."</td><td class=fr_2>";
			$value = ($$key != '') ? m_unhtmlchars(stripslashes($$key)):'';
			switch ($type) {
				case 'number' : echo "<input type=text name=".$key." size=10 value=\"".$value."\">"; break;
				case 'free' : echo "<input type=text name=".$key." size=50 value=\"".$value."\">"; break;
				case 'password' : echo "<input type=password name=".$key." size=50 value=\"".$value."\">"; break;
				case 'url' : echo "<input type=text name=".$key." size=50 value=\"".$value."\">"; break;
				case 'function' : echo $str; break;
				//case 'text' : echo "<textarea rows=8 cols=70 id=".$key." name=".$key.">".$value."</textarea><script language=\"JavaScript\">generate_wysiwyg('".$key."');</script>"; break;
				case 'text' : echo "<a href='javascript: void(0);' style='display: none;' id=".$key."Dis onclick=\"javascript: disableEditor('".$key."','".$key."', ".$key."Editor);\">Default editor</a>
<b id=".$key."DisB>Default editor</b>
&nbsp;&nbsp;
<a href='javascript: void(0);' id=".$key."Enb onclick=\"javascript: enableEditor('".$key."','".$key."', ".$key."Editor);\">WYSIWYG editor</a>
<b id=".$key."EnbB style='display: none;'>WYSIWYG editor</b>
</div>
<textarea id='".$key."' name='".$key."' cols='70' rows='10'>".$value."</textarea>
<div id=".$key."Box style='display: none;'>
<textarea id=".$key."Adv cols='70' rows='10'></textarea>
<script type='text/javascript'>
<!--
	if (isHTML_Editor) {
		var ".$key."Editor = new InnovaEditor('".$key."Editor');
		".$key."Editor.width = 49*9;
		if (navigator.appName.indexOf('Microsoft')!=-1)
			".$key."Editor.height = 7*20;
		else
			".$key."Editor.height = 7*9;
		".$key."Editor.features=[\"Bold\",\"Italic\",\"Underline\",\"Strikethrough\",\"|\",\"ForeColor\",\"|\",
				\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"JustifyFull\",\"|\",
				\"Numbering\",\"Bullets\",\"|\",\"Hyperlink\"];

		".$key."Editor.mode = 'XHTMLBody';
		".$key."Editor.useBR = true;
		".$key."Editor.useTagSelector=false;
		".$key."Editor.REPLACE(\"".$key."Adv\");

		var reg = new RegExp(\"(;|^)".$key."EditorEnabled=Y\",\"\");
		if (document.cookie.search(reg) != -1)
			document.getElementById('".$key."Enb').onclick;
	}
-->
</script>"; break;
				/*case 'text' : echo "<textarea rows=8 cols=70 id=".$key." name=".$key.">".preg_replace('/<br\\s*?\/??>/i', '', $value)."</textarea>"; break;*/
				case 'checkbox'	:	echo "<input value=1".(($arr['checked'])?' checked':'')." type=checkbox class=checkbox name=".$key.">"; break;
				case 'file'	:	{
					if ($value) echo "<input name=oldname type=hidden value=\"".$value."\"><img width=150px src=\"../".$value."\" alt=\"[".$value."]\"><br>";
					echo "<input type=file name=".$key." style='width: auto;' value=\"".$value."\">"; break;
				}
			}
			if ($error_arr[$key]) {
				echo ' ';
				switch ($error_arr[$key]) {
					case 'empty'	:	echo "<b style='color:".$this->error_color['empty']."'>*</b>";	break;
					case 'number'	:	echo "<b style='color:".$this->error_color['number']."'>*</b>";	break;
					case '>0'		:	echo "<b style='color:".$this->error_color['>0']."'>*</b>";		break;
					case '>=0'		:	echo "<b style='color:".$this->error_color['>=0']."'>*</b>";	break;
					case 'url'		:	echo "<b style='color:".$this->error_color['url']."'>*</b>";	break;
				}
			}
			echo "</td></tr>";
		}
		
		echo "<tr><td class=fr colspan=2 align=center><input type=submit name=submit class=submit value=Submit></td></tr>";
		echo "</table></form>";
	}
}
?>