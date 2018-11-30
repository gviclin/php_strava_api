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
	
	<p>
	<form action="page1.php" method="post">
	Segment Id: <input type="text" name="segment_id"><br>
	<input type="submit">
	</form>
		
	</p>
		
	 <?php 
	 session_start();
	 
	 require_once 'StravaApi.php';
	 require_once 'StravaGvTool.php';
	 
	 // define variables and set to empty values
	 $segment_id =  "";
	
	
	

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		echo 'POST<br/>';
		/*if (empty($_POST["segment_id"])) 
		{
			$nameErr = "Activity Id is required";
			echo $nameErr;
			echo '<br/>';
		}
		else*/
		{
						
			/*echo ' Activity Id : '.$_POST["segment_id"];
			echo '<br/>';
			echo '<br/>';*/
			
			$gv = unserialize($_SESSION['gv']);
						
			$segment_id = $_POST["segment_id"];	
			
					
			//$gv->DisplaySegmentEffortFromSegmentId($segment_id);
			$gv->DisplaySegmentEffortFromSegmentId(810063);
			//$gv->DisplaySegmentEffortFromSegmentId(11381841);
			
			
		}
	}
	else
	{

		echo 'Please select an activity ID<br/>';	

		InitAPIAccess();		
	}
	 

	function InitAPIAccess()
	{
		echo 'init API access<br/>';	
		
		$state=$_REQUEST['state'];
		$code=$_REQUEST['code'];
		$_SESSION['state'] = $state;
		$_SESSION['code'] = $code;
		
				
		/*$add = $_SERVER["REMOTE_ADDR"];
		echo 'REMOTE_ADDR IP: '.$add;
		echo '<br/>';
		echo '<br/>';*/
	 
		$clientId='9402';
		$clientSecret='7960741d3c1563506e073c364e71473c5da1405c';

		 $api =  new Iamstuartwilson\StravaApi(
			$clientId,
			$clientSecret
		);
		
		 $gv =  new GVTool\StravaGV($api);
		
		
				
		//echo 'state : '.$state;
		//echo '<br/>';
		//echo 'code : '.$code;
		//echo '<br/>';
		
		$res = $api->tokenExchange($_SESSION['code']);
		
		
		//var_dump($res);
		
		$api->setAccessToken($res["access_token"]);
		
		$_SESSION['gv'] = serialize($gv);
		
	
	}



	?>



	
    </body>
</html>