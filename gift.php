<?php
define('IN_MEDIA',true);
include('includes/config.php');
include('includes/functions.php');
include('includes/class_template.php');
$isLoggedIn = m_checkLogin();
if (!$isLoggedIn) die("<center><b>Have to login</b></center>");
$tpl =& new Template;

if ($_POST['gift'] && $_POST['media_id']) {
	$warn = '';
	$media_id = $_POST['media_id'];
	$sender_id = $_SESSION['user_id'];
	$sender_name = m_htmlchars(stripslashes(trim(urldecode($_POST['sender_name']))));
	$recip_name = m_htmlchars(stripslashes(trim(urldecode($_POST['recip_name']))));
	$sender_email = stripslashes(trim(urldecode($_POST['sender_email'])));
	$recip_email = stripslashes(trim(urldecode($_POST['recip_email'])));
	$message = substr(stripslashes(trim(urldecode($_POST['message']))),0,255);
	if ($sender_name && $recip_name && $sender_email && $recip_email && $message) {
		if (!m_check_email($sender_email)) $warn = "Your email is incorrect";
		elseif (!m_check_email($recip_email)) $warn = "Friend email is incorrect";
		else {
			$q = $mysql->query("SELECT gift_id FROM ".$tb_prefix."gift WHERE gift_sender_id = '".$sender_id."' AND gift_recip_email = '".$recip_email."' AND gift_media_id = '".$media_id."' AND gift_message = '".$message."'");
			$r = $mysql->fetch_array($q);
			if (!$mysql->num_rows($q)) {
				$gift_id = m_random_str(20);
				$title = $webTitle." : Send to friend";
				$header = m_build_mail_header($recip_email,$sender_email);
				$link = $mainURL."/Gift,".$gift_id;
				$time = NOW;
				$web_link = "<a href='".$mainURL."' target=_blank><b>".$webTitle."</b></a>";
				$content = "Hi <b>".$recip_name."</b>,<br>".
					$sender_name." has sended to you a movie at ".$web_link.".<br>".
					"Follow the below link to view this movie :<br>".
					"<a href='".$link."' target='_blank'>".$link."</a><br>".
					"or you can visit our website with this code <b>".$gift_id."</b> to view this movie.<br>".
					$web_link;
				
				if ( mail($recip_email,$title,$content,$header) ) {
					$mysql->query("INSERT INTO ".$tb_prefix."gift VALUES ('".$gift_id."','".$media_id."','".$sender_id."','".$sender_name."','".$sender_email."','".$recip_name."','".$recip_email."','".$message."','".$time."')");
					echo "Has sended. You can view this movie with this code <b>".$gift_id."</b>";
				}
				else $warn = "Host do not support Mail server";
			}
			else {
				echo "Has sended. You can view this movie with this code <b>".$r['gift_id']."</b>";
			}
		}
	}
	else $warn = "Fill the require filed.";
	if ($warn) echo "<b>Error :</b> ".$warn;
	exit();
}
elseif ($value[0] == 'Gift' && $value[1]) {
	
}
else {
	$id = $_GET['id'];
	$main = $tpl->get_tpl('gift');
	$main = $tpl->assign_vars($main,
		array(
			'song.ID'	=>	$id,
		)
	);
	$tpl->parse_tpl($main);
}
?>