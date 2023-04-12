<?php

	require_once '../config.php';
	set_time_limit(0);
	
	//userid and password to call API
	$user =  "LewDowskiCapitalLLC_apiuser";
	$pass = "jLdz>PLy5&";
	
	//pulling all the events from db to get their listings currently only pulling 1 event 'cause I 
	//don't wanna pull too much data 
	$sql = 'SELECT * FROM logitix_events limit 1';
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		
		//this loop will run till there's data in the db table
		while($row = $result->fetch_assoc()) {
			
			$total_calls = 1;
			$now_calling = 1;
			
			while(true){
				
				//this while loop will run till there's data left to be pulled from the API
				if($now_calling > $total_calls){
					break;
				}
				
				//url of API
				$url = 'https://api.preview.autoprocessor.com/V03/ApexInventoryService/GetTicketGroups?PageSize=100&ProductionId='.$row['ProductionId'].'&PageNumber='.$now_calling;
				
				//initializing curl request
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_USERPWD, $user.":".$pass);
				$res = curl_exec($ch);
				
				//this variable will hold response of API
				$someArray = json_decode($res, true);
				
				//this if condition will check if the response contain some useful data or not
				if(isset($someArray['IsSuccessful']) && isset($someArray['Data']) && count($someArray['Data']) > 0 &&
				isset($someArray['TotalCount']) && $someArray['TotalCount'] > 0){
					
					//this for loop is here to loop through all objects of ticket group
					for($i=0;$i<count($someArray['Data']);$i++){
						
						//this variable holds all the values of an object
						$values = "'".$row['ProductionId']."','".
							$conn->real_escape_string($someArray['Data'][$i]['TicketGroupId'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['Status'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['ClientName'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['Section'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['Row'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['Quantity'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['ExternalNotes'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['StockType'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['IsInstant'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['QuantitySplits'])."','".
							$conn->real_escape_string($someArray['Data'][$i]['Price'])."'";
						
						//this variable contains sql query
						$sql = "INSERT INTO `logitix_listings`(`event_id`, `listing_id`, `status`, `client_name`,`section`,
						`row1`, `quantity`, `notes`, `stock_type`, `is_instant`, `split`, `price`) VALUES ($values) 
						ON DUPLICATE 
							status = '" .$conn->real_escape_string($someArray['ticket_groups'][$i]['section'])."', 
							client_name = '" .$conn->real_escape_string($someArray['events'][$i]['category']['parent']['name'])."', 
							section = '" .$conn->real_escape_string($someArray['events'][$i]['category']['name'])."', 
							row = '" .$performers."','".$conn->real_escape_string($someArray['events'][$i]['venue']['name'])."', 
							quantity = '" .$conn->real_escape_string($someArray['events'][$i]['venue']['location'])."', 
							notes = '" .$conn->real_escape_string($someArray['events'][$i]['stubhub_id'])."', 
							stock_type = '" .$conn->real_escape_string($someArray['events'][$i]['occurs_at'])."', 
							is_instant = '" .$conn->real_escape_string($someArray['events'][$i]['name'])."', 
							split = '" .$conn->real_escape_string($someArray['events'][$i]['available_count'])."', 
							price = '" .$conn->real_escape_string($someArray['events'][$i]['popularity_score'])."'";

						//here we run the query and throw an error if it doesn't work
						if(!$conn->query($sql)){
							die('data wasn\'t saved due to error.'.$conn->error.' '.$sql);
						}
					}
					
					//this code determines how many times do we have to call API
					$total_calls = ceil($someArray['TotalCount']/100);
				}
				
				$now_calling++;
				
				//this script sleeps for 1 second after every API call 
				sleep(1);
			}
		}
		
	}

	$conn->close();
?>