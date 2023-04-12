<?php
	require_once '../config.php';
	$firstcall = true;
	$total_calls = 1;
	$now_calling = 1;
	$page = 1;
	
	// this loop will run only if there are still some data left pulling from API
	while(true){
				//if the all calls to the API has been made then end the loop
		if($now_calling > $total_calls){
			break;
		}
				// api url
		$url = 'https://api.sandbox.ticketevolution.com/v9/events/?page='.$page;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'X-Token:d9f567481378940eceecf0d6dd8930f2'
		));
		$res = curl_exec($ch);
		$someArray = json_decode($res, true);
		
		//only run code if there is some data returned from the API and per page records are more than 0.
		if(isset($someArray['per_page']) && $someArray['per_page'] > 0){
			
			for($i=0;$i<count($someArray['events']);$i++){
				
				$performers = '';
				
				if(isset($someArray['events'][$i]['performances'])){
					$performers = $conn->real_escape_string($someArray['events'][$i]['performances'][0]['performer']['name']);
				}
				
				$value = "'".$conn->real_escape_string($someArray['events'][$i]['id'])."',
				'".$conn->real_escape_string($someArray['events'][$i]['category']['name'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['category']['parent']['name'])."',
					'".$performers."','".$conn->real_escape_string($someArray['events'][$i]['venue']['name'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['venue']['location'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['stubhub_id'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['occurs_at'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['name'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['available_count'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['popularity_score'])."',
					'".$conn->real_escape_string($someArray['events'][$i]['products_eticket_count'])."'";
					
				$sql = "INSERT INTO tevo_events(tevo_id, category,parent_category, performer,venue,location, shid, event_datetime,
				event_name, available_count, popularity_score,products_eticket_count) VALUES ($value) 
				ON DUPLICATE KEY UPDATE
					category = '" .$conn->real_escape_string($someArray['events'][$i]['category']['name'])."', 
					parent_category = '" .$conn->real_escape_string($someArray['events'][$i]['category']['parent']['name'])."', 
					performer = '" .$performers."', 
					venue = '" .$conn->real_escape_string($someArray['events'][$i]['venue']['name'])."', 
					location = '" .$conn->real_escape_string($someArray['events'][$i]['venue']['location'])."', 
					shid = '" .$conn->real_escape_string($someArray['events'][$i]['stubhub_id'])."', 
					event_datetime = '" .$conn->real_escape_string($someArray['events'][$i]['occurs_at'])."', 
					event_name = '" .$conn->real_escape_string($someArray['events'][$i]['name'])."', 
					available_count = '" .$conn->real_escape_string($someArray['events'][$i]['available_count'])."', 
					popularity_score = '" .$conn->real_escape_string($someArray['events'][$i]['popularity_score'])."', 
					products_eticket_count = '" .$conn->real_escape_string($someArray['events'][$i]['products_eticket_count'])."'";

				if(!$conn->query($sql)){
					die('data wasn\'t saved due to error.'.$conn->error.' '.$sql);
				}
			}
		}
		if($firstcall){
			$total_calls = round($someArray['total_entries']/$someArray['per_page']);
		}
		$now_calling++;
		$page++;
		//this break is here because we don't want to pull all the data
		//this break will be removed as we start using this data on production level.
		break;
		sleep(1);
	}
	
	$conn->close();
?>