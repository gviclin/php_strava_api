<html>
    <head>
	<meta charset=utf-8 />
	
	<style>
	body {
	background-color: grey;
	color: 	black;
	}
	p {
		margin: 0;
		color: black;
	}
	div,p {
		margin-left: 10px;
	}
	span {
		color: black;
	}
	</style>
        <title>Site PHP </title>
    </head>
	
	  <meta charset="utf-8">

	<script src="https://code.jquery.com/jquery-1.10.2.js"></script>

    <body>
	
	<p>Enter the activity ID</p>

	<form action="welcome.php" method="post">
	Name: <input type="text" name="name"><br>
	E-mail: <input type="text" name="email"><br>
	<input type="submit">
	</form>
		
		
	 <?php 
		require_once 'StravaApi.php';
		
		$clientId='9402';
		$clientSecret='7960741d3c1563506e073c364e71473c5da1405c';

		$api = new Iamstuartwilson\StravaApi(
			$clientId,
			$clientSecret
		);
		
		$state=$_REQUEST['state'];
		$code=$_REQUEST['code'];
		
		//echo 'state : '.$state;
		//echo '<br/>';
		//echo 'code : '.$code;
		//echo '<br/>';
		
		$res = $api->tokenExchange($code);
		//echo '<br/>';	
		//var_dump($res);
		
		$api->setAccessToken($res["access_token"]);
		
		
		//$kom = $api->get('athletes/:id/koms', ['per_page' => 100]);
		//$kom = $api->get('athlete/friends');
			
		
		//$kom = $api->get('activities',  ['per_page' => 200]);
		
		
	/*	$kom = $api->get('activities/459415699'); //VTT — Méridon, Madeleine
		var_dump($kom);
		echo '<br/>';
	*/	
/*	
		$segment = $api->get('segments/1099566/all_efforts', ['per_page' => 10]); //Montée Chemin Chateau de la Madelaine  
		//var_dump($segment);
		foreach ($segment as &$segmentEffort) {
			//echo $segmentEffort['id'].'<br/>';
		RetreiveSegmentEffort($segmentEffort['id'], $api);
		}
		echo '<br/>';
		*/
		//RetreiveSegmentEffort(11052188899, $api);
		
		echo 'FINI <br/>';
		
	


function RetreiveSegmentEffort($SegmentEffortId, $api)
{
	echo ' <br/>';
	echo 'SegmentEffortId : '.$SegmentEffortId.'<br/>';
	
	$kom = $api->get('segment_efforts/'.$SegmentEffortId); //'segment_effort //VTT — Méridon, Madeleine  //Montée Chemin Chateau de la Madelaine  
	//var_dump($kom);
	
	$s = $kom['start_date'];
	$segmentname = $kom['name'];
	$athleteId = $kom['athlete']['id'];
	
	$a = $api->get('athletes/'.$athleteId ); //Montée Chemin Chateau de la Madelaine  
	//var_dump($a);
	$athlete = $a['firstname'].'_'.$a['lastname'];
	$athlete =  html_entity_decode($athlete);
	$segmentname =html_entity_decode($segmentname);
	
	$athlete =  wd_remove_accents($athlete);
	$segmentname =  wd_remove_accents($segmentname);
	
	echo 'start date : '.$s.'<br/>';
	
	$d=mktime(substr($s,11,2),substr($s,14,2),substr($s,17,2),substr($s,5,2),substr($s,8,2), substr($s,0,4));
	echo "Created date is " . date("Y-m-d h:i:sa", $d).'<br/>';		
	
	$date = new DateTime();
	
	$date->setTimestamp($d);

	
	$kom = $api->get('segment_efforts/'.$SegmentEffortId.'/streams/latlng,altitude,distance,time,heartrate'); //'segment_effort //VTT — Méridon, Madeleine  //Montée Chemin Chateau de la Madelaine  
	$nb = count($kom[0]['data']);
	//echo 'nombre d\'elements : '.$nb;

	//var_dump($kom);
	
	$w=new XMLWriter();
	$w->openMemory();
	$w->setIndent(true);
	$w->startDocument('1.0','UTF-8');
	$w->startElement("gpx");
		$w->writeAttribute('version', '1.1');
		$w->writeAttribute('creator', 'G.Viclin');
	   
		
		$w->startElement('metadata');		
			$w->startElement('link');
				$w->writeAttribute('href', 'connect.garmin.com');
				$w->writeElement('text', 'Garmin Connect');
			$w->endElement();
			$w->writeElement('time', date("Y-m-d\TH:i:s\.000\Z", $date->getTimestamp ()));
		$w->endElement();
		
		$w->startElement('trk');
			$w->writeElement('name', $segmentname);
			$w->startElement('trkseg');
			$i = 0;
			$heureCourante = new DateTime();
			foreach ($kom[0]['data'] as &$value) {
				$time 	= $kom[1]['data'];
				$d 		= $kom[2]['data'];
				$alt 	= $kom[3]['data'];
				//$hr 	= $kom[4]['data'];
				$w->startElement('trkpt');
					$w->writeAttribute('lon', $value[1]);
					$w->writeAttribute('lat', $value[0]);
					$w->writeElement('ele', $alt[$i]);		
					$diff = $time[$i]-$time[0];
					//echo 'diff : '.$diff.' sec'.'<br/>';
					$heureCourante = clone $date;
					//echo "Set up " . date("Y-m-d\TH:i:s\.000\Z", $heureCourante->getTimestamp ()).'<br/>';
					date_add($heureCourante,date_interval_create_from_date_string($diff.' sec'));
							
					//echo "Modified date is " . date("Y-m-d\TH:i:s\.000\Z", $heureCourante->getTimestamp ()).'<br/>';
					//echo '<br/>';
					$w->writeElement('time', date("Y-m-d\TH:i:s\.000\Z", $heureCourante->getTimestamp ()));
					$w->startElement('extensions');
					$w->endElement();
				$w->endElement();
				//echo 'Coord : '.$value[0].'  '.$value[1].' ,    Time : '.$time[$i].' ,    Dist : '.$d[$i].' ,    Alt : '.$alt[$i].' ,    HR : '.$hr[$i].'<br/>';	
				
				$i++;
			}

			$w->endElement();
		$w->endElement();

		
		
	$w->endElement();
	//echo htmlentities($w->outputMemory(false));

	// Final flush to make sure we haven't missed anything
	file_put_contents(/*$segmentname.'-'.*/$athlete.'-'.$SegmentEffortId.'.gpx', $w->flush(true));
}

function wd_remove_accents($str, $charset='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);
    
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    
    return $str;
}

	?>



	
    </body>
</html>