	 <?php 
	 session_start();
	 
	 require_once 'StravaApi.php';
	 
	 require_once 'StravaGvTool.php';
	 
	 // define variables and set to empty values
	 $segment_id =  "";	
	 
	 $gv = unserialize($_SESSION['gv']);
	 
	if(isset($_FILES['avatar']))
	{ 

			//On fait un tableau contenant les extensions autorisées.
			//Comme il s'agit d'un avatar pour l'exemple, on ne prend que des extensions d'images.
			$extensions = array('.txt');
			// récupère la partie de la chaine à partir du dernier . pour connaître l'extension.
			$extension = strrchr($_FILES['avatar']['name'], '.');
			//Ensuite on teste
			if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
			{
				 echo 'only text file is supported';
			}
			else
			{
				$dossier = 'upload/';
				$fichier = basename($_FILES['avatar']['name']);
				 
				$fichier = strtr($fichier,
					 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
					 'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy'); 
				//On remplace les lettres accentutées par les non accentuées dans $fichier.
				//Et on récupère le résultat dans fichier
				 
				//En dessous, il y a l'expression régulière qui remplace tout ce qui n'est pas une lettre non accentuées ou un chiffre
				//dans $fichier par un tiret "-" et qui place le résultat dans $fichier.
				$fichier = preg_replace('/([^.a-z0-9]+)/i', '-', $fichier);


				 if(move_uploaded_file($_FILES['avatar']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
				 {
					echo 'Upload successful !'.' <br/>'.' <br/>';
					  
					 
					$monfichier = fopen($dossier . $fichier, 'r');
					
					if ($monfichier) {
						while (($buffer = fgets($monfichier, 4096)) !== false) {
							// echo $buffer.' <br/>';
							echo "<br/>";
							
							//$keywords = preg_split("/[\s,]+/", $buffer);
							//print_r($keywords);
							$pieces = explode(";", $buffer);
							
							$date = str_replace("\"","",$pieces[0]);
							$dist = str_replace("\"","",$pieces[1]);
							$dur = str_replace("\"","",$pieces[2]);

							//echo $date." ".$dist." ".$dur."	<br />\n";
							
							//if (!$gv->IsRideActivityForADay($date))
							{
								$gv->CreateActivity($date, $dist, $dur);
							}
						}
						if (!feof($monfichier)) {
							echo "Erreur: fgets() failed\n";
						}
						fclose($monfichier);
					}	
				 }
				 else //Sinon (la fonction renvoie FALSE).
				 {
					  echo 'Upload failed !';
				 }	
			}

	}
	 
	 //echo 'Get the gpx file from segment effort id : '.$segmentEffortId;
	 //echo ' <br/>';
	 
	 //echo $gv->RetreiveSegmentEffort($segmentEffortId, true);

	 
	?>


