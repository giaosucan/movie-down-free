<?php
if (!defined('IN_MEDIA')) die("Hacking attempt");
if ($isLoggedIn) {
	echo "<center><b>You are logined</b></center>";
	exit();
}
if ($_POST['reg']) {
	$warn = '';
	$name = m_htmlchars(stripslashes(trim(urldecode($_POST['name']))));
	$pwd = md5(stripslashes(urldecode($_POST['pwd'])));
	$email = stripslashes(trim(urldecode($_POST['email'])));
	$sex = ($_POST['sex'])?$_POST['sex']:1;
	
	if ($mysql->num_rows($mysql->query("SELECT user_id FROM ".$tb_prefix."user WHERE user_name = '".$name."'"))) $warn .= "Tài khoản này đã có người sử dụng<br>";
	
	if (!m_check_email($email)) $warn .= "Email is incorrect<br>";
	elseif ($mysql->num_rows($mysql->query("SELECT user_id FROM ".$tb_prefix."user WHERE user_email = '".$email."'"))) $warn .= "Email này đã có người sử dụng<br>";
	
	if ($warn) echo "<b>Error</b> : <br>".$warn;
	else {
		$playlist_id = m_random_str(20);
		if (m_get_config('sendmailconfirm'))
 {
 $xacnhan = rand(0,10000000000000);
 $web_email = m_get_config('web_email');
         $title = $webTitle." : Confirm";
         $header = m_build_mail_header($email,$web_email);
         $content = "Hi <b>".$name."</b>,".
             "Confirm your account with this link : <b>".$mainURL."Confirm,".$xacnhan."</b> ".
             "Have fun with us.".
             "<a href='".$mainURL."'><b>".$webTitle."</b></a>";
 if ( mail($email,$title,$content,$header) ) {
         $mysql->query("UPDATE ".$tb_prefix."online SET reg_check = '' WHERE ip ='".$ip."'");
$mysql->query("INSERT INTO ".$tb_prefix."user (user_name,user_password,user_email,user_sex,user_regdate,user_playlist_id,user_confirm) VALUES ('".$name."','".$pwd."','".$email."','".$sex."',NOW(),'".$playlist_id."','".$xacnhan."')");
 }
 else 
 {
 echo 'Host so not support Mail server';
 }
 }
 else
 {
 $mysql->query("UPDATE ".$tb_prefix."online SET reg_check = '' WHERE ip ='".$ip."'");
$mysql->query("INSERT INTO ".$tb_prefix."user (user_name,user_password,user_email,user_sex,user_regdate,user_playlist_id,user_activated) VALUES ('".$name."','".$pwd."','".$email."','".$sex."',NOW(),'".$playlist_id."','1')");
 }
	}
	exit();
}
$main = $tpl->get_tpl('register');
$tpl->parse_tpl($main);
?>