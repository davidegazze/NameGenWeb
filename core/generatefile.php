<?php

session_start();
require "functions.php";

if (!isset($_GET['format'])) {
	$format = "graphml";
	$include_ego = FALSE;
}
	
	$format = $_GET['format'];
	$include_ego = (isset($_GET['ego'])) ? TRUE : FALSE;	
	$user_name = $_SESSION['user_profile']['name'];
	$friend_list_array = $_SESSION['friend_list_array'];
	$edge_list_array = $_SESSION['edge_list_array'];
	$attribute_array = $_SESSION['attribute_array'];		
	
	
	$file_name = $user_name."_".time();
	//echo "$path $format $user_name $file_name";

	$path = createGraphFile($friend_list_array, $edge_list_array, $attribute_array, $format, $file_name, $include_ego);
	downloadPrompt($path, $path);
	


?>