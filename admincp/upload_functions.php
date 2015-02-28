<?php
function uptoload($upname, $newname, $des = '../picz/') {
    $img = isset( $upname['name'] ) ? $upname['name'] : '';

    if ( !empty($img) ){
    // GET THE TYPE OF IMAGE . EX: .GIF , .JPG ,....
    $start = strpos($img, ".");
    $type = substr($img, $start, strlen($img));
    if ( (strtolower($type)!=".gif")&&(strtolower($type)!=".jpg")&&(strtolower($type)!=".bmp")&&(strtolower($type)!=".png")){
       return "picz/nopic.gif";
    }
		
		$img = $newname.strtolower($type);
    if ( !(copy($upname['tmp_name'], $des.$img)) ) die("Cannot upload files.");
		return "picz/".$img;
    }
}
?>