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
	<form action="segment_effort.php" method="post">
	Segment effort Id: <input type="text" name="id">
	<input type="submit">
	</form>
		
	</p>
		
	 <?php 
	 session_start();
	 
	 require_once 'StravaGvTool.php';
	 	 
	 // define variables and set to empty values
	 $id =  "";
	
	
	

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		echo 'POST<br/>';
		
		$gv = unserialize($_SESSION['gv']);

		if (empty($_POST["id"])) 
		{
			$id = 14713952946;
		}
		else
		{					
			$id = $_POST["id"];							
		}
		$gv->RetreiveSegmentEffort($id);
	}
	else
	{

		echo 'Please select an segment effort 	ID<br/>';	

		$state=$_REQUEST['state'];
		$code=$_REQUEST['code'];
		$_SESSION['state'] = $state;
		$_SESSION['code'] = $code;
		
				
		/*$add = $_SERVER["REMOTE_ADDR"];
		echo 'REMOTE_ADDR IP: '.$add;
		echo '<br/>';
		echo '<br/>';*/		
		
		$gv =  new StravaGV($_SESSION['code']);//$_SESSION['code']		
					
		$_SESSION['gv'] = serialize($gv);
	}
	 



	?>



	
    </body>
</html>