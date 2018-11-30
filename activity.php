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
	
	<p>
	<form action="activity.php" method="post">
	Activity Id: <input type="text" name="id">
	<input type="checkbox" name="hidden" id="hidden" /> <label for="hidden">avec segments cachés</label>
	<input type="checkbox" name="favori_only" id="favori_only" /> <label for="favori_only">segments favoris seulements</label><br/>
	<input type="submit">
	</form>
		
	</p>
	
	<br/><br/>	
	<form action="activity.php" method="post">
	Segment effort Id: <input type="text" name="id2">
	<br/><input type="submit">
	</form>
	
	<br/><br/>	
	<form action="activity.php" method="post">
	Segment Id: <input type="text" name="id3">
	<br/><input type="submit">
	</form>
	
	<!-- create activity -->  
	<br/><br/>	
	<form method="POST" action="create_activity.php" enctype="multipart/form-data">	   
		<!-- On limite le fichier à 100Ko -->
		<input type="hidden" name="MAX_FILE_SIZE" value="100000">	
		Choose text file with activities to create : <input type="file" name="avatar">
		<input type="submit" name="envoyer" value="Send the file">
	</form> 
     
</form>
		
	 <?php 
	 session_start();
	 
	 require_once 'StravaGvTool.php';
	 	 
	 // define variables and set to empty values
	 $id =  "";
	
	
	

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		//echo 'POST<br/>';
		
		$gv = unserialize($_SESSION['gv']);
		
		//recherche par activité ou par segment effort
		if (isset($_POST['id']))
		{
			echo 'search by activity<br/>';
			
			$hidden = isset($_POST['hidden'])?TRUE:FALSE;
			$favori_only = isset($_POST['favori_only'])?TRUE:FALSE;
		
			if (empty($_POST["id"])) 
			{
				$id = 776060190;
			}
			else
			{					
				$id = $_POST["id"];							
			}
			$gv->ProceedActivity($id, $hidden, $favori_only);
						
		}
		else if (isset($_POST['id2']))
		{
			echo 'search by segment effort<br/>';
			
			if (empty($_POST["id2"])) 
			{
				$id = 1538493327; //cap aigrefoin
			}
			else
			{					
				$id = $_POST["id2"];							
			}			
			
			echo $gv->RetreiveSegmentEffort($id);
		}
		else if (isset($_POST['id3']))
		{
			echo 'search by segment <br/>';
			
			if (empty($_POST["id3"])) 
			{
				$id = 12805630;
			}
			else
			{					
				$id = $_POST["id3"];							
			}			
			
			echo $gv->ProceedSegment($id,0,0);
		}
		else
		{
			echo 'search type not implemented<br/>';
		}

	}
	else
	{

		//echo 'Loaded';	

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
