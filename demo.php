<HTML>
<HEAD>
<TITLE>Cisco Live Demo</TITLE>
</HEAD>
<BODY>
<?php

// We need a compliance account auth token to call the API with.
$token=“MmYzMDc2NDAtNDY5Yi00NGRlLWI1MmItMTU2YWUwNTJjYjkzZmFhNzhhOTktNDk0_PF84_0e5507d4-6b47-4dc1-88a2-9be9d7136a1e”;




// Lets grab the current date and time and subtract 5 minutes to get
// the last 5 minutes worth of events from the compliance API.
// This would normally be done much more elegantly and the date
// used would be the last time we pulled data...

$date	=gmdate("Y-m"); 
$minute	=gmdate("i");
$hour	=gmdate("H");
$day	=gmdate("d");

$minute=$minute-5;
if($minute<0){
	$minute+=60;
	$hour=$hour-1;
	if(hour<0){
		$hour+=24;
		$day=$day-1;
		if($day<1)$day=1; // We're just going to ignore this fpor the demo. ;-)
		}
}

if(strlen($minute)<2)$minute="0".$minute;
$postDate= gmdate(":00.000\Z"); 

$fullFormattedDate= $date."-".$day."T".$hour.":".$minute.$postDate;






// Create our actual API request to get the events:

$ch = curl_init();
$authorization = "Authorization: Bearer ".$token;
$url='https://api.ciscospark.com/v1/events?from='.$fullFormattedDate.'&max=50';

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                        
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
if( ! $result = curl_exec($ch)) 
    { 
	echo 'Error'.curl_error($ch);
	exit;
    } 
else
	{                     
    // We got data back from our request to the compliance API
	// Lets just dump it out here
	
	$compliance_events = json_decode($result);
	echo '<PRE>';
	var_dump($compliance_events);
	echo '</PRE>';
	if(sizeof($compliance_events->items)>0){
		foreach($compliance_events->items as $item) {
			echo '<P>New item:<BR>';
		    $complianceDate = $item->created;
			$complianceResource=$item->resource;
			$complianceType=$item->type;
			$complianceItemID = $item->id;
			$complianceActedUpon=$item->data->personEmail;
			$complianceData=$item->data;
			$complianceDataID=$item->data->id;
			$complianceText=$complianceData->text;
			$complianceFiles=$complianceData->files;
			$complianceEmail=strtolower($complianceData->personEmail);
			echo  $complianceItemID.'->'.$complianceEmail.':'.$complianceText.'<BR>';
			
			$pattern="/http:\/\//i";


			$hitcount=preg_match($pattern, $complianceText);
			echo $hitcount;
			if ($hitcount>0) {
				echo "Deleting: ";
				$ch2 = curl_init();
				$url='https://api.ciscospark.com/v1/messages/'.$complianceDataID;
				echo $url."<P>";
				curl_setopt($ch2, CURLOPT_URL, $url);
 				curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "DELETE");
   				curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);                                                                        
				curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
				$result = curl_exec($ch2);
				
				$messageData = array("toPersonEmail"=> $complianceEmail,  "text"=> "You just violated company policy by mentioning a restricted project.");
				$data_string = json_encode($messageData);                                                            
				$ch3 = curl_init();
				curl_setopt($ch3, CURLOPT_URL, 'https://api.ciscospark.com/v1/messages');                                                                      
				curl_setopt($ch3, CURLOPT_POST, 1);
				curl_setopt($ch3, CURLOPT_POSTFIELDS, $data_string);                                                                  
				curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);  
				curl_setopt($ch3, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
				$result = curl_exec($ch3);
				echo $result;
				}
			}
		}

	}



		
		




?>
</BODY>