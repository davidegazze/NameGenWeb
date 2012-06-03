<?php

ini_set("memory_limit","256M"); // needed for extra large networks

header('Content-Type:text/html; charset=UTF-8');
error_reporting(E_ALL);

// Begin the session
session_start();


$session_id=session_id();

require "../config/facebook-config.php";
require "../facebook-php-sdk/src/facebook.php";

// Initialise Facebook
$facebook = new Facebook(array(
	'appId'  => $app_id,
	'secret' => $app_secret,
));

?>
<!DOCTYPE html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<html>
	<head> 
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<style type="text/css">
	body {
		background:#fff;
		margin:0;
		margin-top:10px;
	}
	</style>
	</head>
  <body style="overflow:hidden;" scroll="no">


<?php	

if (isset($_GET['download'])) {
	
?>	

	<div id="tohide" style="width:97%;">
		<div style="margin-left:auto;margin-right:auto;width:440px">
	<img src="../images/ajax-loader.gif" alt="loading..." style="float:left">	
	<div id="progressbarcontainer" style="width:400px;background:white;border:1px solid #ccc;float:right">
		<div id="progressbar" style="color:white;background: -webkit-gradient(
		linear, left top, left bottom, 
		from(#4677d9),
		to(#3e5b96));;width:40px;height:30px;line-height:30px;text-indent:10px;font-size:12px">10%
		</div>
	</div>
		</div>
	</div>	

<? } else { ?>
	<div id="tohide" style="width:100%; text-align:right">
	<div id="progressbarcontainer" style="margin-left:auto;margin-right:auto">	
	<form action="phase3a.php" method="GET">
		<input type="hidden" name="phase" value="3" />		
	     <button type="submit" value="Submit" name="download" id="" class="mainformbutton">Download</button>
	</form>	
	</div>
	</div>	
<? }?>
		

<?

if (isset($_GET['download'])) {


// Retrieve session variables
$friend_count = count($_SESSION['friend_list_array']);
//$json_network = $_SESSION['json_network'];
//$json_helper_array = $_SESSION['json_helper_array'];
$friend_list_array = $_SESSION['friend_list_array'];

// Scale Sleep time depending on network size

$sleep_time = ($friend_count > 350) ? 1.5 : 0;

// Define multiquery loops and variables, as-per NodeXL Facebook importer. 	
$outer_max_num = 125;
$inner_max_num = 500;
$outer_iter_interval = ceil($friend_count/$outer_max_num);
$inner_iter_interval = ceil($friend_count/$inner_max_num);
$outer_index_start = 0;
$outer_index_end = 0;
$inner_index_start = 0;
$inner_index_end = 0;
$num_of_queries = 0;
$edge_list_array = array();

// Begin outer loop
for ($i = 0; $i < $outer_iter_interval; $i++) {

	// Calculate start and end index
	$outer_index_start = $i * $outer_max_num;
	$outer_index_end = ($i + 1) * $outer_max_num;
	$outer_index_end = $outer_index_end >= $friend_count ? $friend_count - 1 : $outer_index_end;	// ternary operator - neat.

	// Begin inner loop
	for ($j = 0; $j< $inner_iter_interval; $j++) {

		// Calculate start and end index
		$inner_index_start = $j * $inner_max_num;
		$inner_index_end = ($j + 1) * $inner_max_num;
		$inner_index_end = $inner_index_end >= $friend_count ? $friend_count - 1 : $inner_index_end;
		
		// Structure query
		$query = '{"query'.$num_of_queries.'":"select uid1, uid2 from friend where uid1 in (select uid2 from friend where uid1=me() and uid2>='.$friend_list_array[$outer_index_start]['uid'].' and uid2<='.$friend_list_array[$outer_index_end]['uid'].') and uid2 in (select uid1 from friend where uid2=me() and uid1>='.$friend_list_array[$inner_index_start]['uid'].' and uid1<='.$friend_list_array[$outer_index_end]['uid'].')"}';

		//Specify the parameters
		$param = array(       
	     	'method' => 'fql.multiquery',       
	     	'queries' => $query,       
	     	'callback' => '');

		try { // Run the query.

			$fqlresult = $facebook->api($param);
			sleep($sleep_time); // Hack for CURL timeouts
	
			// Insert the mutual friends into the edge array
			foreach ($fqlresult['0']['fql_result_set'] as $key => $value) {
				$r1 = $value['uid1'];
				$r2 = $value['uid2'];
				$sp=",";


				//$json_r1 = $json_helper_array[$r1];
				//$json_r2 = $json_helper_array[$r2];
			
				if (strcmp($r1, $r2) > 0) {
					$edge_list_array[$r1 . $sp . $r2] = 1;
					//$json_network['edges'][] = array("source" => $r1, "target" => $r2);
				
					//$json_network['links'][] = array("source" => $json_r1, "target" => $json_r2);					
				} else {
					$edge_list_array[$r2 . $sp . $r1] = 1;
					//$json_network['edges'][] = array("source" => $r2, "target" => $r1);
					//$json_network['links'][] = array("source" => $json_r2, "target" => $json_r1);															
				}

			}
	
			// Progress bar. TODO: make this with with asynchronous ajax query to another script.
			$current_percent = round((($i+1) / $outer_iter_interval) * 100);
			echo'<SCRIPT>
				d = document.getElementById("progressbar");
			d.style.width="'.$current_percent.'%";
			d.innerHTML = "'.$current_percent.'%";
			</SCRIPT>';
			@ob_flush();
			flush();
			unset($fqlresult);
			gc_collect_cycles();
			
		} catch (FacebookApiException $e) {
	    	//TODO: exception handling on MQ. Handling should be to retry query after delay.	
	     	echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
	
			sleep(4); // Hack for CURL timeouts	
			$fqlresult = $facebook->api($param);

	
			// Insert the mutual friends into the edge array
			foreach ($fqlresult['0']['fql_result_set'] as $key => $value) {
				$r1 = $value['uid1'];
				$r2 = $value['uid2'];
				$sp=",";


				$json_r1 = $json_helper_array[$r1];
				$json_r2 = $json_helper_array[$r2];
			
				if (strcmp($r1, $r2) > 0) {
					$edge_list_array[$r1 . $sp . $r2] = 1;
					//$json_network['edges'][] = array("source" => $r1, "target" => $r2);
				
					//$json_network['links'][] = array("source" => $json_r1, "target" => $json_r2);					
				} else {
					$edge_list_array[$r2 . $sp . $r1] = 1;
					//$json_network['edges'][] = array("source" => $r2, "target" => $r1);
					//$json_network['links'][] = array("source" => $json_r2, "target" => $json_r1);															
				}

			}
	
			// Progress bar. TODO: make this with with asynchronous ajax query to another script.
			$current_percent = round((($i+1) / $outer_iter_interval) * 100);
			echo'<SCRIPT>
				d = document.getElementById("progressbar");
			d.style.width="'.$current_percent.'%";
			d.innerHTML = "'.$current_percent.'%";
			</SCRIPT>';
			@ob_flush();
			flush();
		
		}
	}	// End inner loop

	$num_of_queries++;
	
} // End outer loop

?>
<SCRIPT>
	d = document.getElementById("tohide");
d.style.visibility="hidden";
d.style.display="none";
</SCRIPT>
<script> parent.location.href='../index.php?page=phase4'</script>

<?

// Set new session data
//$_SESSION['json_network'] = $json_network;
$_SESSION['edge_list_array'] = $edge_list_array;
$_SESSION['friend_list_array'] = $friend_list_array;


} // End some condition
?>
</body>
</html>