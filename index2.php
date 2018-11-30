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
		
    </head	>

	<body>

<p>This example calls a function which performs a calculation, and returns the result:</p>

<p id="demo"></p>

<script>
function myFunction(a, b) {
    return a * b;
}
function toCelsius(fahrenheit) {
    return (5/9) * (fahrenheit-32);
}
document.getElementById("demo").innerHTML = toCelsius;	
</script>



    </body>
</html>	
	
