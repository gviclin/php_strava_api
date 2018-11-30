	 <?php 
	 session_start();
	 
	 require_once 'StravaApi.php';
	 
	 require_once 'StravaGvTool.php';	 

	 // define variables and set to empty values
	 $segment_id =  "";	
	 
	 $gv = unserialize($_SESSION['gv']);
	 
	 $segmentEffortId=$_REQUEST['id'];
	 
	 // echo 'Get the gpx file from segment effort id : '.$segmentEffortId;
	 // echo ' <br/>';
	 
	 echo $gv->RetreiveSegmentEffort($segmentEffortId, true);

	 
	?>


