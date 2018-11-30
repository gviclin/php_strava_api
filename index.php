<html>
    <head>
	<meta charset=utf-8 />
	
	<style>
		body {
			background-color: grey;
			color: 	black;
		}
	</style>
        <title>Site PHP </title>
		
		<link rel="stylesheet" href="http://ol3js.org/en/master/css/ol.css" type="text/css"> <script src="http://ol3js.org/en/master/build/ol.js" type="text/javascript"></script>
		
    </head>




<div id="mapOL3" style="width: 100%, height: 400px"></div> <script> new ol.Map({ layers: [ new ol.layer.Tile({source: new ol.source.OSM()}) ], view: new ol.View2D({ center: [0, 0], zoom: 2 }), target: 'mapOL3' }); </script>


	<p><a href="	 
	 <?php 
		require_once 'StravaApi.php';

		$clientId='9402';
		$clientSecret='7960741d3c1563506e073c364e71473c5da1405c';

		$api = new Iamstuartwilson\StravaApi(
			$clientId,
			$clientSecret
		);
		
		$redirect = 'http://localhost/php/activity.php';
		//$redirect = 'http://localhost/activity.php';
		
		$scope='view_private,write';
		$state = 44;
		$approvalPrompt = 'force';
		//echo 'i<br/>';
		
		$acceptAppli =  urldecode($api->authenticationUrl($redirect, $approvalPrompt, $scope, $state));
		
		echo $acceptAppli;			
	?>
">Strava login</a></p>

    </body>
</html>	
	
