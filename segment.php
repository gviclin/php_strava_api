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
	
	<script src="ajax1.js" type="text/javascript"></script>

    <body>
	
	
	 <?php 
	 session_start();
	 
	 require_once 'StravaApi.php';
	 
	 require_once 'StravaGvTool.php';
	 
	 // define variables and set to empty values
	 $segment_id =  "";	
	 
	 $gv = unserialize($_SESSION['gv']);
	 
	 $segmentId=$_REQUEST['id'];
	 $segmentEffortId=$_REQUEST['EffortId'];
	 $athleteId=$_REQUEST['AthleteId'];

	 echo ' <br/>';
	 
	 $gv->ProceedSegment($segmentId, $segmentEffortId,$athleteId);

	 
	?>



	
    </body>
</html>
