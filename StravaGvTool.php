<?php
	
	require_once 'StravaApi.php';
	/*
    use PDO;
	use DateTime;
	use XMLWriter;*/	

    /**
     * Stravo API implementation by GV
     *
     * 
     * 
     */

    class StravaGV
    {
        const BASE_URL = 'https://www.strava.com/';

        // Strava API
		public  $api;
		
		//pour calculer les durees de traitement
		private $timestart;
		
		//table HTML
		private $tab;


        /**
         * Sets up the class with the  $this->api
         *
         * @param     $this->api
         */
        public function __construct($code)
        {				
            $this->InitAPIAccess($code);
			
        }
		
		public function InitAPIAccess($code)
		{
			
			$clientId='9402';
			$clientSecret='7960741d3c1563506e073c364e71473c5da1405c';
			
			$this->api =  new Iamstuartwilson\StravaApi(
				$clientId,
				$clientSecret
			);								
						
			$res = $this->api->tokenExchange($code);	
			
			if ($this->IsStravaVariableValid($res))
			{		
				if (isset($res['access_token']))
				{
					$this->api->setAccessToken($res["access_token"]);
					//var_dump($res);
				}
				else
				{
					var_dump($res);
				}
			}
			else
			{
				echo 'code <'.$code.'> is invalid';
			}
		}
		
		public function  ProceedSegment($Id, $segmentEffortId, $athlete_id = 134706)
		{	
			$all;
			$segmentEffortList;
			if ($athlete_id ==0)
			{
				$all = true;
			}
			else
			{
				$all = false;
			}
		
			$segment_id = $this->trimText($Id);	
			
			echo 'Segment Id requested : '.$Id.' , Segment effort id to compare : '.$segmentEffortId;
			echo ' <br/>';	
			
			// relever le point de départ
			$this->StartTimeCount();
			$segment =0;
			if ($all)
			{
				$segment = $this->api->get('segments/'.$Id);
				//var_dump($segment);
				$segmentEffortList =  $this->api->get('segments/'.$Id.'/leaderboard',  array('page' => 1, 'per_page' => 200, 'following' => true)); //, ['per_page' => 200]  ['page' => 6]   ['athlete_id' => 134706]
				var_dump($segmentEffortList);
				$segmentEffortList = $segmentEffortList['entries'];
			}
			else
			{
				$segmentEffortList =  $this->api->get('segments/'.$Id.'/all_efforts',  array('page' => 1, 'per_page' => 200, 'athlete_id' => $athlete_id)); //, ['per_page' => 200]  ['page' => 6]   ['athlete_id' => 134706]
			}
			
			if ($this->IsStravaVariableValid($segmentEffortList))
			{
				$size = count($segmentEffortList);
				echo 'Nombre d\'effort : '.$size;
				echo ' <br/>';
				echo '<button onclick="getGPX(16268492394)">Download</button>';
				echo ' <br/>';
	
				$data = $this->GetDataFromsSegmentEffortList($segment, $segmentEffortList, $segmentEffortId);
				
//				var_dump($data);
				//var_dump( $this->ComputeAverage($data));
				echo $this->GetTableFromData($data, $this->ComputeAverage($data));
				echo '<br/>';
				//var_dump($data);
				
				echo '<br/>';
				
			}	
			
			
			$this->StopTimeCount();
			
		}
		
		
		
		public function  ProceedActivity($Id, $hidden, $favori_only)
		{	
		
			$segment_id = $this->trimText($Id);	
			
			echo 'Activity Id requested : '.$Id;
			echo ' <br/>';
			
			// relever le point de départ
			$this->StartTimeCount();
			
			// $this->api = unserialize($_SESSION['api']);
			if ($hidden)
			{
				$result =  $this->api->get('activities/'.$Id, ['include_all_efforts' => TRUE]);
				echo 'all efforts<br/>';			
			}
			else
			{
				$result =  $this->api->get('activities/'.$Id);

			}
			
			//include_all_efforts:
			
			
					
			if ($this->IsStravaVariableValid($result))
			{
				$size = count($result);

				echo ' <br/>';
				
				$d = $this->GetDateOnly($result['start_date_local']);	
				
				$dist = $result['distance']/1000;

				$moy = round(3600*$dist/$result['elapsed_time'],1);		
					
				$duration = $this->GetDurationFromSecond($result['elapsed_time']);

				echo 'Activity : '.$result['name'].' , Date : '.$d.' , distance : '.round($dist,1).' km , moyenne : '.$moy.' km/h , durée : '.$duration ;
				echo '<br/>';echo '<br/>';
				
				//var_dump($result);
								
				echo count($result['segment_efforts']).' segments';echo '<br/>';								
				
				//var_dump($result['segment_efforts']);
				echo $this->GetTableSegmentEffortsFromActivity($result['segment_efforts'], $favori_only);						

				echo '<br/>';				
				
					
				$this->StopTimeCount();
			}
			
			//var_dump($result);
		}
		
		private function IsStravaVariableValid($var)
		{
			$bo = TRUE;
			if (isset($var['errors']))
			{
				$bo = FALSE;
				echo ' <br/>';
				echo 'Error : \''.$var['message'].'\' <br/>';   
				
				if (isset($var['errors'][0]))
				{
						echo 'Details : \''.$var['errors'][0]['resource'].'\'   \''.$var['errors'][0]['field'].'\'   \''.$var['errors'][0]['code'].'\' <br/>';
				}
				
				//var_dump($var);
			}
			return $bo;
		}
		
		
		
		function GetTableSegmentEffortsFromActivity($id, $favori_only)
		{
			
			$tab = '	<table border="1" cellpadding="2" >
		   <tr>
			  <th>Id</th>
			  <th>Name</th>
			  <th>Etat</th>
			  <th>Star</th>
			  <th>Dist </th>
			  <th>Dist segment</th>
			  <th>Elev</th>
			  <th>%</th>
			  <th>m/h</th>
			  <th>Duration</th>
			  <th>V moy</th>
			  <th>V Dépla</th>
			  <th>Bpm</th>
			  <th>Cadence</th>
			  <th>Puissance</th>
			  <th>Caché</th>
			  <th>Segment Id</th>
		   </tr>';



			foreach ($id as &$segmentEffort) 
			{		
				$s = $segmentEffort['segment'];
				if (!$favori_only || $s['starred'])
				{
					//var_dump($segmentEffort);
					$tab .='<tr>';
				
					$athlete = $this->GetUserName($segmentEffort['athlete']['id'],  $this->api);	
														
					$dist = round($segmentEffort['distance']/1000,2);
					$t2 = $this->GetMinSecFromSecond($segmentEffort['elapsed_time']);
					$moy = round(3.6*$segmentEffort['distance']/$segmentEffort['elapsed_time'],1);		
					$moyMoving = round(3.6*$segmentEffort['distance']/$segmentEffort['elapsed_time'],1);		
					
					$distseg = round($s['distance']/1000,2);
					$deniv =$s['elevation_high']-$s['elevation_low'];
					$pourcent = 0.1*$deniv/$dist;
					$mh = $deniv*(3600/$segmentEffort['elapsed_time']);
					
					$tab .= '<td>';
					$tab .= $segmentEffort['id'];
					$tab .= '</td>';	
														
					$tab .= '<td align="right">';
					$tab .= $segmentEffort['name'];
					$tab .= '</td>';				

					$tab .= '<td>';
					if ($s['private'])
					{
						$tab .= '<img src="resources/cadena.ico" alt="Mountain View" style="width:30px;height:30px;">';
					}				
					$tab .= '</td>';
					
					$tab .= '<td>';
					if ($s['starred'])
					{
						$tab .= '<img src="resources/star.png" alt="Mountain View" style="width:30px;height:30px;">';
					}				
					$tab .= '</td>';
					
						
					$tab .= '<td>';
					$tab .= sprintf("%'0.2f", $dist);
					$tab .= '</td>';
					
					$tab .= '<td>';
					$tab .= sprintf("%'0.2f", $distseg);
					$tab .= '</td>';
					
					$tab .= '<td>';
					$tab .= round($deniv,0);
					$tab .= '</td>';
					
					$tab .= '<td>';
					$tab .= round($pourcent,1);
					$tab .= '</td>';
					
					$tab .= '<td>';
					$tab .= round($mh,0);
					$tab .= '</td>';	
					
					$tab .= '<td>';
					$tab .= $t2;
					$tab .= '</td>';
					
					$tab .= '<td>';
					$tab .= $moy;
					$tab .= '</td>';
					
					$tab .= '<td>';
					$tab .= $moyMoving;
					$tab .= '</td>';
					
					$tab .= '<td>';
					if (isset($segmentEffort['average_heartrate']))
					{
						$tab .= round($segmentEffort['average_heartrate']);				
					}
					else
					{
						$tab .='';
					}
				

					$tab .= '</td>';
					
					$tab .= '<td>';
					if (isset($segmentEffort['average_cadence']))
					{
						$tab .= round($segmentEffort['average_cadence']);				
					}
					else
					{
						$tab .='';
					}
					$tab .= '</td>';
					
					$tab .= '<td>';
					//$tab .= round($segmentEffort['average_watts']);
					$tab .= '</td>';
					
					$tab .= '<td>';
					$tab .= ($segmentEffort['hidden']?'oui':'non') ;
					$tab .= '</td>';

					$tab .= '<td>';
					$tab .= '<a href="segment.php?id='.$s['id'].'&EffortId='.$segmentEffort['id'].'&AthleteId='.$segmentEffort['athlete']['id'].'">'.$s['id'].'</a><br/>';
					$tab .= '</td>';
					
					$tab .='</tr>';	
				}

			}	
			$tab .=' </table>';
			
			return $tab;
		}
		
			
		function GetDataFromsSegmentEffortList($segment, $segmentEffortList, $segmentEffortId)
		{
			//var_dump($segmentEffortList);
			if (count($segmentEffortList) == 0)
			{
				return;
			}			
	
			$leaderboards = false;
			if (isset($segmentEffortList[0]['effort_id']))
			{
				//cas de la liste des leaderboards
				$leaderboards  = true;
				
				echo ' <br/>';
				echo $segment['name'].'<br/>';	
			}
			else
			{
				echo ' <br/>';
				echo $segmentEffortList[0]['segment']['name'].'<br/>';	
			}		
	
			
			//var_dump($segmentEffortList);
			
			//tableau retourné
			$data = array();
			
			//variable a extraire
			$ranking = 1;
			$bold = FALSE;
			$hr;		
			$seg;	
			
			
			foreach ($segmentEffortList as &$segmentEffort) 
			{					
				$bold = FALSE;
				$elt = array();				
				
				if (isset($segmentEffort['segment']))
				{
					$seg = $segmentEffort['segment'];			
				}
				else
				{
					//si le segment n'est pas present dans le segmenteffort, on le recupère des paramètres de la méthode
					$seg = $segment;
				}
				$type = $seg['activity_type'];
				$isRun = FALSE;
				if (preg_match("/run/i", $type))
				{
					$isRun = TRUE;
				}		
								

				if (isset($segmentEffort['id']))
				{
					$segmentId = $segmentEffort['id'];
					if ($segmentEffort['id'] == $segmentEffortId)
					{
						$bold = TRUE;
					}
					
				} 
				else if (isset($segmentEffort['effort_id']))
				{
					$segmentId = $segmentEffort['effort_id'];
					if ($segmentEffort['effort_id'] == $segmentEffortId)
					{
						$bold = TRUE;
					}						
				}
				else
				{
					$bold = FALSE;
					$segmentId = 0;
				}

				$this->tab .='<tr>';
			
				//récupère le username id
				$athleteid;
				if (isset($segmentEffort['athlete']['id']))
				{
					$athleteid = $segmentEffort['athlete']['id'];
				}
				else if (isset($segmentEffort['athlete_id']))
				{
					$athleteid = $segmentEffort['athlete_id'];
				}
				else
				{					
					return 'GetTableSegmentEffortsFromSegmentId : impossible to retrieve athlete id !!!';
				}
				$athlete = $this->GetUserName($athleteid,  $this->api);	
				
				$duration = $this->GetMinSecFromSecond($segmentEffort['elapsed_time']);
				$dist = round($segmentEffort['distance']/1000,2);
				$moy = round(3.6*$segmentEffort['distance']/$segmentEffort['elapsed_time'],1);		
				$moyMoving = round(3.6*$segmentEffort['distance']/$segmentEffort['elapsed_time'],1);
				

				
				$distseg = round($seg['distance']/1000,2);
				$deniv =$seg['elevation_high']-$seg['elevation_low'];
				$pourcent = 0.1*$deniv/$dist;
				$mh = $deniv*(3600/$segmentEffort['elapsed_time']);	
				
				if (isset($segmentEffort['average_heartrate']))
				{
					$hr = round($segmentEffort['average_heartrate']);
				}
				else if (isset($segmentEffort['average_hr']))
				{
					$hr = round( $segmentEffort['average_hr']);
				}
				else
				{
					$hr='';					
				}				
			
				$elt["Bold"]=$bold;
				$elt["Ranking"]=$ranking;
				$elt["Nom"]=$athlete;
				$elt["Date"]=$this->GetDateOnly($segmentEffort['start_date_local']);
				$elt["Hour"]=$this->GetHourOnly($segmentEffort['start_date_local']);
				$elt["Duration"]=$duration;
				$elt["Distance"]=$dist;
				$elt["Deniv"]=$deniv;
				$elt["Grade"]=round($pourcent,1);
				
				// Le "i" après le délimiteur du pattern indique que la recherche ne sera pas sensible à la casse
				if ($isRun)
				{
					$elt["Allure"]=$this->getAllure($moy);
					$elt["Avg speed"]=$moy;
				} 
				else
				{
					$elt["Avg speed"]=$moy;
					$elt["Mov speed"]=$moyMoving;	
				}
				$elt["Vertical speed"]=intval (round($mh,0));
				$elt["Average HR"]=intval (round($hr));	

									
				if (!$leaderboards)
				{
					$cad = $this->GetValueFromKey($segmentEffort,'average_cadence');
					$elt["Max HR"]=intval (round($this->GetValueFromKey($segmentEffort,'max_heartrate')));
					if (!$isRun)
					{	
						if ($cad!=0)
						{
							$elt["Average CAD"]=intval (round($this->GetValueFromKey($segmentEffort,'average_cadence')));	
						}
						else
						{
							$elt["Average CAD"]=0;	
						}
					}
					else
					{
						if ($cad!=0)
						{
							$elt["Average CAD"]=2*intval (round($this->GetValueFromKey($segmentEffort,'average_cadence')));	
						}
						else
						{
							$elt["Average CAD"]=0;	
						}						
					}
				}
				if (!$isRun)
				{
					$elt["Average Power"]=intval (round($this->GetValueFromKey($segmentEffort,'average_watts')));	
				}

						
				$elt["Gpx link"]=	'<button onclick="getGPX('.$segmentId.')">Download</button>';	

				if ($leaderboards)
				{
					$elt["Leaderboard link"]='<a href="segment.php?id='.$seg['id'].'&EffortId='.$segmentId.'&AthleteId='.$athleteid.'">Click</a><br/>';
				}
				
				$ranking++;
				array_push($data,$elt);
			}		
			
			return $data;
		}
		
		
		function 	ComputeAverage($data)
		{
			if (!isset($data[0]))
			{
				return 'no data';
			}
			
			$num = count($data);
			
			$sumBPM = 0;
			$numbBPM =0;
			
			$sumMaxBPM = 0;
			$numMaxBPM =0;
			
			$sumDist = 0;
			$numbDist=0;
			
			$sumTemps = 0;
			$numbTemps=0;	
			
			$sumCad = 0;
			$numCad=0;	
			
			$isRun = FALSE;
			
			//remplissage du tableau
			foreach ($data as $dataItem)
			{
				//ligne
				foreach ($dataItem as $key=>$value)
				{
					//element dans la ligne
					if (preg_match("/Allure/i", $key))
					{
						$isRun = TRUE;
					}
					if (preg_match("/Average HR/i", $key))
					{
						if (intval($value)>0)
						{
							$sumBPM+= intval($value);
							$numbBPM++;
						}
					}	
					if (preg_match("/Max HR/i", $key))
					{
						if (intval($value)>0)
						{
							$sumMaxBPM+= intval($value);
							$numMaxBPM++;
						}
					}	
					if (preg_match("/Distance/i", $key))
					{
						$sumDist+= floatval ($value);
						$numbDist++;
					}
					if (preg_match("/Duration/i", $key))
					{

						$pieces = explode(":", $value);						
						$sumTemps+= intval($pieces[0]*60+$pieces[1]);
						$numbTemps++;
					}	
					if (preg_match("/Average CAD/i", $key))
					{
						if (intval($value)>0)
						{
							$sumCad+= intval($value);
							$numCad++;					
						}

					}						

				}
			}	
			$elt = array();	
			$avgDuration = round($sumTemps/$numbTemps,0);
			if ($numbTemps>0)
				$elt["Duration"]= $this->GetMinSecFromSecond($avgDuration);
			
			if ($numbDist>0)
			{
				$dist = round($sumDist/$numbDist,2);
				$elt["Distance"]= $dist;
				$moySpeed= round(3600*$dist/$avgDuration,1);	
				$elt["Avg speed"]=$moySpeed;
				if ($isRun)
					$elt["Allure"]=$this->getAllure($moySpeed);
			}		
			if ($numbBPM>0)
				$elt["Avg BMP"]= round($sumBPM/$numbBPM,0);	
			if ($numMaxBPM>0)
				$elt["Max BMP"]= round($sumMaxBPM/$numMaxBPM,0);	
			
			if ($numCad>0)
				$elt["Average CAD"]=round($sumCad/$numCad,0);
			

			return $elt;			
		}
		
		
		function 	GetTableFromData($data,$avgData)
		{
			if (!isset($data[0]))
			{
				return 'no data';
			}
			
			$this->tab = '	
			<table border="1" cellpadding="2">
		    <tr>
			';
			
			//construction des colonnes				
			foreach ($data[0] as $key=>$value)
			{
				if (strcasecmp ($key,'Bold') != 0)
				{
					$this->tab .= '<th>'.$key.'</th>
					 ';
				}
			}		
			$this->tab .='</tr>
			';	
			
			//remplissage du tableau
			foreach ($data as $dataItem)
			{
				//ligne
				$this->tab .='<tr>';
				$bold = FALSE;
				foreach ($dataItem as $key=>$value)
				{
					//element dans la ligne
					if (strcasecmp ($key,'Bold') == 0)
					{
						//field bold
						$bold = $value;
					}
					else
					{
						$this->AddField($value,$bold);				
					}

				}
				$this->tab .='</tr>
				';
				if ($bold)
				{
					//ajout de la ligne de moyenne
					$this->AddAvgLine($avgData);
				}
			}	
			$this->tab.= '
			</table>
			<p>SegmentId : <span id="txtHint"></span></p>';
			return $this->tab;			
		}
		
		function AddAvgLine($avgData)
		{
			$this->tab .='<tr>';
			$this->tab .='<tr>';
			$this->AddField("...",false);	
			$this->tab .='</tr>
				';	
	
			$this->AddField("Avg",false);				
			$this->AddField("",false);				
			$this->AddField("",false);				
			$this->AddField("",false);				
			$this->AddField($avgData["Duration"],false);				
			$this->AddField($avgData["Distance"],false);	
			$this->AddField("",false);				
			$this->AddField("",false);		
			if (isset(	$avgData["Allure"]))			
				$this->AddField($avgData["Allure"],false);	
			else
				$this->AddField($avgData["Avg speed"],false);		
			$this->AddField($avgData["Avg speed"],false);	
			$this->AddField("",false);		
			if (isset(	$avgData["Avg BMP"]))
				$this->AddField($avgData["Avg BMP"],false);	
			else	
				$this->AddField("",false);	
			if (isset(	$avgData["Avg BMP"]))
				$this->AddField($avgData["Max BMP"],false);	
			else
				$this->AddField(0,false);	
			if (isset(	$avgData["Average CAD"]))			
				$this->AddField($avgData["Average CAD"],false);	
			else
				$this->AddField("",false);	
			$this->AddField("",false);	
			$this->AddField("",false);	
			
			$this->tab .='<tr>';
			$this->AddField("...",false);	
			$this->tab .='</tr>
				';
			
			$this->tab .='</tr>
				';		
		}
		
		function GetTableSegmentEffortsFromSegmentId($segment, $segmentEffortList, $segmentEffortId)
		{
			if (count($segmentEffortList) == 0)
			{
				return;
			}
			
			$leaderboards = false;
			if (isset($segmentEffortList[0]['effort_id']))
			{
				//cas de la liste des leaderboards
				$leaderboards  = true;
				
				echo ' <br/>';
				echo $segment['name'].'<br/>';	
			}
			else
			{
				echo ' <br/>';
				echo $segmentEffortList[0]['segment']['name'].'<br/>';	
			}
			
			$data = array();		
			$this->tab = '	<table border="1" cellpadding="2">
		   <tr>
			  <th>Ranking</th>
			  <th>Name</th>
			  <th>Date</th>
			  <th>Time</th>
			  <th>Dist </th>
			  <th>Elev</th>
			  <th>%</th>
			  <th>V moy</th>
			  <th>V Dépla</th>
			  <th>m/h</th>
			  <th>Bpm</th>'.($leaderboards?'':'
			  <th>Bpm max</th>
			  <th>Cadence</th>').'
			  <th>Puissance</th>			  
			  <th>Gpx</th>'.(!$leaderboards?'':'
			  <th>All efforts</th>').'
		   </tr>';

			$i = 1;
			
			//var_dump($segmentEffortList);
			foreach ($segmentEffortList as &$segmentEffort) 
			{	
				$elt = array();
				$deniv = '';
				$pourcent = '';
				$mh ='';
				$segmentId;
				$bold = FALSE;
				if (isset($segmentEffort['id']))
				{
					$segmentId = $segmentEffort['id'];
					if ($segmentEffort['id'] == $segmentEffortId)
					{
						$bold = TRUE;
					}
					
				} 
				else if (isset($segmentEffort['effort_id']))
				{
					$segmentId = $segmentEffort['effort_id'];
					if ($segmentEffort['effort_id'] == $segmentEffortId)
					{
						$bold = TRUE;
					}						
				}
				else
				{
					$bold = FALSE;
					$segmentId = 0;
				}

				$this->tab .='<tr>';
			
				//récupère le username id
				$athleteid;
				if (isset($segmentEffort['athlete']['id']))
				{
					$athleteid = $segmentEffort['athlete']['id'];
				}
				else if (isset($segmentEffort['athlete_id']))
				{
					$athleteid = $segmentEffort['athlete_id'];
				}
				else
				{					
					return 'GetTableSegmentEffortsFromSegmentId : impossible to retrieve athlete id !!!';
				}
				$athlete = $this->GetUserName($athleteid,  $this->api);	
				
				$t2 = $this->GetMinSecFromSecond($segmentEffort['elapsed_time']);
				$dist = round($segmentEffort['distance']/1000,2);
				$moy = round(3.6*$segmentEffort['distance']/$segmentEffort['elapsed_time'],1);		
				$moyMoving = round(3.6*$segmentEffort['distance']/$segmentEffort['elapsed_time'],1);
				
				$s;
				if (isset($segmentEffort['segment']))
				{
					$s = $segmentEffort['segment'];			
				}
				else
				{
					//si le segment n'est pas present dans le segmenteffort, on le recupère des paramètres de la méthode
					$s = $segment;
				}
				
				$distseg = round($s['distance']/1000,2);
				$deniv =$s['elevation_high']-$s['elevation_low'];
				$pourcent = 0.1*$deniv/$dist;
				$mh = $deniv*(3600/$segmentEffort['elapsed_time']);	
				
				
			
				
				//$this->AddField($i, $bold);
				$elt["Nom"]=$athlete;
				$elt["Date"]=$this->GetDateOnly($segmentEffort['start_date_local']);
		
				$this->AddField($t2, $bold);
				$this->AddField($dist, $bold);
				$this->AddField($deniv, $bold);
				$this->AddField(round($pourcent,1), $bold);
				$this->AddField($moy, $bold);
				$this->AddField($moyMoving, $bold);
				$this->AddField(round($mh,0), $bold);
				$hr;
				if (isset($segmentEffort['average_heartrate']))
				{
					$hr = round($segmentEffort['average_heartrate']);
				}
				else if (isset($segmentEffort['average_hr']))
				{
					$hr = round( $segmentEffort['average_hr']);
				}
				else
				{
					$hr='';					
				}
				$this->AddField(round($hr), $bold);
				if (!$leaderboards)
				{
					$cad = $this->GetValueFromKey($segmentEffort,'average_cadence');
					$this->AddField(round($this->GetValueFromKey($segmentEffort,'max_heartrate')), $bold);
					if ($cad!=0)
					{
						$this->AddField(round($this->GetValueFromKey($segmentEffort,'average_cadence')), $bold);
					}
					else
					{
						$this->AddField('', $bold);
					}
				}
				$this->AddField(round($this->GetValueFromKey($segmentEffort,'average_watts')), $bold);				
				
				$this->AddField('<button onclick="getGPX('.$segmentId.')">Download</button>', $bold);
				
				if ($leaderboards)
				{
					$this->AddField('<a href="segment.php?id='.$s['id'].'&EffortId='.$segmentId.'&AthleteId='.$athleteid.'">Click</a><br/>', $bold);
				}
	
				$this->tab .='</tr>';
				
				$i++;
				array_push($data,$elt);
			}	
			$this->tab .=' </table>';
			
			$this->tab .='<p>SegmentId : <span id="txtHint"></span></p>';
			

		

			$this->tab .='</tr>';
			

			
			return $this->tab;
		}
		
		private function GetValueFromKey($tab, $key)
		{
			if (array_key_exists ($key, $tab))
			{
				return $tab[$key];
			}
			else
			{
				return '';
			}
		}
		
		private function AddField($field, $bold)
		{
				$this->tab .= '<td>'.($bold?'<b>':'');
				$this->tab .= $field;
				$this->tab .= '</td>'.($bold?'</b>':'');
		}
		
				
		private function GenerateAllSegmentEffortFromSegmentId($SegmentId)
		{	
			echo 'segment Id requested : '.$SegmentId;
			echo ' <br/>';
			
			 $this->api = unserialize($_SESSION['api']);
			
			$segment =  $this->api->get('segments/'.$SegmentId.'/all_efforts', ['per_page' => 20]); //Montée Chemin Chateau de la Madelaine  
			//var_dump($segment);
			foreach ($segment as &$segmentEffort) {
				//echo $segmentEffort['id'].'<br/>';
				$this->RetreiveSegmentEffort($segmentEffort['id'],  $this->api);
			}
			echo '<br/>';
		}

		// créer le fichier gpx associé à un segment effort id donné en paramètre
		public function RetreiveSegmentEffort($SegmentEffortId, $silentMode = false)
		{
				
			if (!$silentMode)
			{
				$this->StartTimeCount();
			}	
			
			$kom =  $this->api->get('segment_efforts/'.$SegmentEffortId); //'segment_effort //VTT — Méridon, Madeleine  //Montée Chemin Chateau de la Madelaine  
			var_dump($kom);
			
			//name
			$segmentname = $this->GetVariable($kom, 'name');
			
			
			//athlete
			$athleteId = $kom['athlete']['id'];
			$athlete = $this->GetUserName($athleteId,  $this->api);
			
			
			//date
			$d = $this->GetDateOnly($kom['start_date_local']);	
			$d = $this->trimText($d);
			
			
			if (!$silentMode)
			{
				echo ' <br/>';
				echo 'SegmentEffortId : '.$SegmentEffortId.'<br/>';
				echo $segmentname.'<br/>';
				echo 'By : '.$athlete.'<br/>';
				echo 'Date : '.$d.'<br/>';				
			}

			//var_dump($kom);
			
			$s = $kom['start_date'];
			
			$athlete =  html_entity_decode($athlete);
			$segmentname =html_entity_decode($segmentname);
			
			$athlete =  $this->wd_remove_accents($athlete);
			$segmentname =  $this->wd_remove_accents($segmentname);
			
			$filename = $athlete.'_'.$d.'.gpx';
			
			$d=mktime(substr($s,11,2),substr($s,14,2),substr($s,17,2),substr($s,5,2),substr($s,8,2), substr($s,0,4));
			//echo "Created date is " . date("Y-m-d h:i:sa", $d).'<br/>';		
			
			$date = new DateTime();
			
			$date->setTimestamp($d);

			
			$kom =  $this->api->get('segment_efforts/'.$SegmentEffortId.'/streams/latlng,altitude,distance,time,heartrate'); //'segment_effort //VTT — Méridon, Madeleine  //Montée Chemin Chateau de la Madelaine  
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
			$dir = getcwd().'//gpx//'.$filename;
			file_put_contents($dir, $w->flush(true));
			
			if (!$silentMode)
			{
				$this->StopTimeCount();
			}

			return 'gpx/'.$filename;
		}

		private function wd_remove_accents($str, $charset='utf-8')
		{
			$str = htmlentities($str, ENT_NOQUOTES, $charset);
			
			$str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
			$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
			$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
			
			return $str;
		}

		private function GetUserName($AthleteId)
		{
				//recuperatin dans la BD
				$servername = "localhost";
				$username = "root";
				$password = "";
				$bd = "bd_strava";
				$retour = "not_found";
			
				try {		
				
					//echo 'GetUserName <'.$AthleteId.'> <br/>';

					// Sous WAMP (Windows)
					$conn = new PDO('mysql:host=localhost;dbname='.$bd.';charset=utf8', $username, $password);
												
					// set the PDO error mode to exception
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					//echo "Connected successfully"; 
					
				
					$reponse = $conn->query('SELECT * FROM username WHERE Id=\''.$AthleteId.'\''	);					
							
					if ($data = $reponse->fetch())
					{
						//existe
						$retour =  $data['Username'];			
					}
					else
					{								
						//demande StravaAp
						$a =  $this->api->get('athletes/'.$AthleteId); //Montée Chemin Chateau de la Madelaine  
										
						if ($this->IsStravaVariableValid($a))
						{
							//var_dump($a);
							$retour = $a['firstname'].'_'.$a['lastname'];
							$retour =  html_entity_decode($retour); //, Firstname, Lastname
							
							$retour	 = str_replace( "'", "\'", $retour);
							$firstname	 = str_replace( "'", "\'", $a['firstname']);
							$lastname 	 = str_replace( "'", "\'", $a['lastname']);
							$city		 = str_replace( "'", "\'", $a['city']);
																					
							//stockage en BD
							$d = 'INSERT INTO username (Id, Username, Firstname, Lastname, friend, follower, profile_medium, city, created_at) 
							VALUES ('.$AthleteId.',\''.$retour	.'\',\''.$firstname.'\',\''.
							$lastname.'\',\''.$a['friend'].'\',\''.$a['follower'].'\',\''.$a['profile_medium'].'\',\''.$city.'\',\''.$a['created_at'].'\')';
							
							/*echo $d;
							echo '<br/>';
							var_dump($r1);*/
							$r1 = $conn->exec($d);	
					
						}
					}
					$reponse->closeCursor();					
					
					}
				catch(PDOException $e)
					{
						//$reponse->closeCursor();
						return "Connection failed: " .$e->getMessage();
					}

				//echo 'GetUserName return '.$retour;
				return $retour;
		}

		private function trimText($data) {
		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  return $data;
		}

		private function GetMinSecFromSecond($sec)
		{
			//conversion sec en minute/seconde
			$min = floor($sec / 60);
			$sec = $sec - (60 * $min);
			$strsec = sprintf("%'02d", $sec);
			$strmin = sprintf("%'02d", $min);
			return $strmin.':'.$strsec;
			
		}
		
		private function getAllure($moy)
		{
			if ($moy==0)
				return '';
		
			//Allure from km/h
			$secperkm = 3600/$moy;
			
			$min = floor($secperkm/60);
			$sec = floor($secperkm-60*$min);
			return $min.":".sprintf('%02d', $sec)." /km";
		}
		
		private function GetDurationFromSecond($sec)
		{
			//conversion sec en minute/seconde
			$heure = floor($sec / 3600);
			$secRestante = $sec - 3600*$heure;			
			
			$min = floor($secRestante / 60);
			$secRestante = $secRestante - (60 * $min);
			$strheu = sprintf("%'02d", $heure);
			$strsec = sprintf("%'02d", $secRestante);
			$strmin = sprintf("%'02d", $min);
			return $strheu.':'.$strmin.':'.$strsec;
			
		}
		
		private function GetDateTime($s)
		{
			$d=mktime(substr($s,11,2),substr($s,14,2),substr($s,17,2),substr($s,5,2),substr($s,8,2), substr($s,0,4));
			//echo "GetDateTimeate is " . date("Y-m-d h:i:sa", $d).'<br/>';		
			
			$date = new DateTime();
			
			$date->setTimestamp($d);	

			return $date;
		}
		
		private function GetDateOnly($s)
		{
			return date("d-m-Y", $this->GetDateTime($s)->getTimestamp ());
		}
		
		private function GetHourOnly($s)
		{
			return date("H:i:s", $this->GetDateTime($s)->getTimestamp ());
		}
		
		private function StartTimeCount()
		{
			$this->timestart=microtime(true);
		}

		
		private function StopTimeCount()
		{
			//Fin du code PHP
			$timeend=microtime(true);
			$time=$timeend-$this->timestart;
			 
			//Afficher le temps d'éxecution
			$page_load_time = number_format($time, 3);
			//echo "Debut du script: ".date("H:i:s", $timestart);
			//echo "<br>Fin du script: ".date("H:i:s", $timeend);
			echo "<br>Part executed in " . $page_load_time . " sec";
			echo ' <br/>';
			
			$this->timestart=microtime(true);
		}
		
		private function GetVariable($var, $name)
		{
			if (isset($var[$name]))
			{
				return $var['name'];
			}
			else
			{
				return $name.' is not set';
			}

		}	

		public function  IsRideActivityForADay($startDate)
		{				
			$bo = false;
			$sec = strtotime($startDate);
			$secend = $sec + 86400;
			//echo $sec." ".$secend.' <br/>';
			
			$result =  $this->api->get('athlete/activities/', ['before' =>  $secend]);
			//var_dump($result);
			foreach ($result as $act) 
			{		
				$s = $act['start_date'];
				
				
				$res = strpos($s,$startDate);
				if ($res  === false)
				{
					//echo "start_date : ".$s." : ".$startDate." return : false<br/>";										
				}
				else 
				{
					//var_dump($act);
					$type = $act['type']; 
					$name = $act['name'];  
					$type = $act['type'];  
					$time = $act['moving_time'];
					$dist = round($act['distance']/1000,1); 
					$moy = 0;
					if ($time != 0)
					{
						$moy = round(3.6*$act['distance']/$time,1);
					}
					if ($type == "Ride")
					{
 
					
						echo "ride activity exists : ".$startDate."   ".$type.", ".$dist." km ".$moy." km/h    ".$name."<br/>";
						$bo =  true;
					}
					else
					{
						echo "run activity exists : ".$startDate."   ".$type.", ".$dist." km ".$moy." km/h    ".$name."<br/>";
					}
				}				
			} 
			
			return $bo;
		}	
		
		public function  CreateActivity($date, $dist, $dur)		
		{	
			$d = trim($date)."T19:00:00Z";
			//echo $d."<br/>";
			
			$sec = 60*$dur;
			
			$result =  $this->api->post('activities', array('name' =>  'vélotaf', 'type' =>  'Ride', 'start_date_local' =>  $d, 'elapsed_time' =>  $sec, 'distance' =>  $dist));
			//var_dump($result);
			echo "activity created : ".$date."<br/>";
		}
	}
