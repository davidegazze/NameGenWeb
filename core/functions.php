<?PHP 

// NameGen function declarations



function createGraphFile($node_array, $edge_array, $attribute_array, $format, $output_file, $include_ego = FALSE) {
	
/**
* Takes alter and edge data, and outputs a file of type $format with filename $output_file
*
* @param array 	$node_array 		Array of node and node attribute data.
* @param array 	$edge_array 		Array of edges linked by id to node array.
* @param array 	$attribute_array 	Array of node attribute names.
* @param string	$format				One of graphml, ucinet, json, guess.
* @param string $output_file		Filename (without extension) to be output.
* @param bool	$include_ego		Whether to include ego in resulting file.
*
* @return string $path				The path to the file created.
*
* TODO:
* 	 	- Update GraphML generation to use PHPs DOMDOCUMENT
*		- Implement $include_ego switch
*
*/	
	
$output = "";
$extension = "";


// insert ego 

if($include_ego == TRUE) {
	
	$ego_name = "Ego";
	$ego_uid = "0000000";
	
	$node_array['ego']['name'] = $ego_name;
	$node_array['ego']['uid'] = $ego_uid;
	
	foreach ($node_array as $key => $value) {
		$edge_array[$ego_uid.",".$node_array[$key]['uid']] = 1;
		
	}
		
	
}




$helper_array = array();

foreach ($node_array as $key => $value) {

	$helper_array[$node_array[$key]['uid']]['name'] = $node_array[$key]['name'];
	$safe_name = str_replace(' ', '_', $node_array[$key]['name']);
	$safe_name.= "_".$node_array[$key]['uid'];
	$helper_array[$node_array[$key]['uid']]['safe_name'] = $safe_name;

}



//echo "<pre>";
//var_dump($helper_array);

// Set global output directory
$output_directory = "../output/";
	
	// Set file extension
	
	switch($format) {
		
		case 'graphml':
			$extension = ".graphml";
			$output.= '<?xml version="1.0" encoding="UTF-8"?>
			<graphml xmlns="http://graphml.graphdrawing.org/xmlns"
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xsi:schemaLocation="http://graphml.graphdrawing.org/xmlns
			http://graphml.graphdrawing.org/xmlns/1.0/graphml.xsd">';
			break;

		case 'ucinet':
			$extension = ".dl";
			$friend_count = count($node_array);
			$output.= "dl n = $friend_count format = edgelist1\nlabels:\n";			
			break;

		case 'json':
			$extension = ".json";
			$helper_array = array();
			break;
		
		case 'guess':
			$extension = ".gdf";
			$output.= "nodedef>name";
			foreach ($attribute_array as $key => $value) {

				$type = (($key == "mutual_friend_count") || ($key == "friend_count") || ($key == "likes_count")) ? "int" : "string";
				$attributes[$key] = $type;
				$output.=",".$key." ".strtoupper($type);

			}
			
			$output.="\n";
						
			break;
		
		default:
			return die("Requested format not available.");
		
	}

	// Path to file
	$path = $output_directory.$output_file.$extension;
	
	// Check we can write where we want to write
	if (is_writable($output_directory)) {
		$file = fopen($path, "a");
	} else {
		return die("File is not writable.");
	}
	

// ********************************************************************************************
// HANDLE ATTRIBUTE DATA
// ********************************************************************************************
	
	$attributes ="";

	switch($format) {
		
		case 'graphml':
			foreach ($attribute_array as $key => $value) {

				$type = (($value == "mutual_friend_count") || ($value == "friend_count") || ($value == "likes_count")) ? "int" : "string";
				$attributes.= "\n".'<key id="'.$value.'" for="node" attr.name="'.$value.'" attr.type="'.$type.'">'."\n\t".'<default></default>'."\n".'</key>';

			}
			$attributes.='<graph id="G" edgedefault="undirected">'."\n";			
			break;

		case 'ucinet':
		
			break;

		case 'json':

			break;
		
		case 'guess':
		
			break;

	}

	$output.=$attributes;
	
// ********************************************************************************************
// HANDLE NODE DATA
// ********************************************************************************************
	
	$nodes = "";
	
	switch($format) {
		
		case 'graphml':
		
			$attribute_helper_array = array();
			foreach ($attribute_array as $key => $value) {
				$attribute_helper_array[$value] = $key;
			}
		
			foreach ($node_array as $key => $value) {	

				$nodes.= "\n".'<node id="'.$helper_array[$node_array[$key]['uid']]['name'].'">';
				foreach ($node_array[$key] as $attribute => $attr_value) {
					
					if ($attribute =="hometown_location") {
						$attr_value = $node_array[$key]['hometown_location']['name'];
					}
					
					if (isset($attr_value)) {
						$attr_value = htmlspecialchars($attr_value);
					    $attr_value = "<![CDATA[".$attr_value."]]>";							
						$helper_key = $attribute_helper_array[$attribute];
						$nodes.="\n\t".'<data key="'.$attribute.'">'.$attr_value.'</data>';												
					}

		


				}

				$nodes.= "\n".'</node>';	

			}
			
			break;

		case 'ucinet':
			foreach ($node_array as $key => $value) {	

				$nodes.= $helper_array[$node_array[$key]['uid']]['safe_name']."\n";	

			}
			break;

		case 'json':
			foreach ($node_array as $key => $value) {

				$json_network['nodes'][]['name'] = $node_array[$key]['name'];
				$helper_array[$node_array[$key]['uid']] = $key;

			}
			break;
		
		case 'guess':
			foreach ($node_array as $key => $value) {	

				$nodes.= $node_array[$key]['uid']."\n";	

			}	
			break;

	}
	
	$output.=$nodes;

// ********************************************************************************************
// HANDLE EDGE DATA
// ********************************************************************************************
	
	$i=0;
	$edges="";

	switch($format) {
		
		case 'graphml':
		
			foreach ($edge_array as $key => $value) {

				$uids = explode(",", $key);	 
				$edges.="\n".'<edge id="'.$i.'" source="'.$helper_array[$uids[0]]['name'].'" target="'.$helper_array[$uids[1]]['name'].'"></edge>';
				$i++;

			}
		
			break;

		case 'ucinet':
			$output.= "labels embedded\ndata:\n";		
			foreach ($edge_array as $key => $value) {
				$uids = explode(",", $key);		 
				$edges.=$helper_array[$uids[0]]['safe_name']." ".$helper_array[$uids[1]]['safe_name']."\n";
			}		
			break;

		case 'json':
			foreach ($edge_array as $key => $value) {

				$uids = explode(",", $key);

				$user1 = $helper_array[$uids[0]];
				$user2 = $helper_array[$uids[1]];	

				$json_network['links'][] = array('source' => $user1, 'target' => $user2);

			}
			break;
		
		case 'guess':
		$output.= "edgedef>node1,node2\n";		
			foreach ($edge_array as $key => $value) {	 
				$edges.=$key."\n";
			}
			break;

	}
	
	$output.=$edges;

// ********************************************************************************************
// HANDLE FILE WRITE AND RETURN
// ********************************************************************************************

	switch($format) {
	
		case 'graphml':
			$output.="\n".'</graph></graphml>';
			break;

		case 'ucinet':
	
			break;

		case 'json':
			$output = json_encode($json_network);
			break;
	
		case 'guess':
	
			break;

	}	

	fputs($file, $output);
	
	return $path;
	
}



function downloadPrompt($path, $browserFilename) {
	
/**
* Prompts the user to download a file in the browser.
* Works even with IE6.
*
* @param string $path                   The file path to the file to be downloaded
* @param string $browserFilename        The name sent to the browser
* @param string $mimeType               The mime type like 'image/png'
*
* @return void
*/	

	if (!file_exists($path) || !is_readable($path)) {

		return null;
	}

//	header("Content-Type: " . $mimeType);
	date_default_timezone_set('Europe/London');
	header("Content-Disposition: attachment; filename=\"$browserFilename\"");
	header('Expires: ' . date('D, d M Y H:i:s', time() - 3600) . ' GMT');
	header("Content-Length: " . filesize($path));
	// If you wish you can add some code here to track or log the download

	// Special headers for IE 6
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	$fp = fopen($path, "r");
	fpassthru($fp);
}


?>