<?
 if (!defined('IN_MEDIA')) die("Hacking attempt");
 if (!m_get_config('sendmailconfirm')) {
     $html = $tpl->get_tpl('directlogin');
 $tpl->parse_tpl($html);
 }
 else
 {
     $html = $tpl->get_tpl('mustactivate');
 $tpl->parse_tpl($html);
 }
 ?>