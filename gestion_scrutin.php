<?php
	session_start();

	//Numéro de scrutin
	$num;
	if(isset($_POST['num_scrutin']) and is_numeric($_POST['num_scrutin'])){
		$num=$_POST['num_scrutin'];
	}else{
		if(isset($_GET['num_scrutin']) and is_numeric($_GET['num_scrutin'])){
			$num=$_GET['num_scrutin'];
		}else{
			$num="";
		}
	}
	if(!isset($_SESSION['valid_user']) or $_SESSION['valid_user']!=1){
		$_SESSION['page']="gestion_scrutin";
		header("Location: connexion.php");
		exit();
	}

	//bouton deconnexion
	if (isset($_POST["deconnexion"])){
		session_destroy();
		header("Location: accueil.php");
		exit();
	}

	//bouton acceueil
	if (isset($_POST["accueil"])){
		header("Location: accueil.php");
		exit();
	}

	//Bouton Détruire le vote 
	if(isset($_POST['detruireVote'])){
		$num=$_POST['num_scrutin'];
		if(file_exists("scrutin_$num.json")){
			unlink("scrutin_$num.json");
		};
		if(file_exists("resultat_$num.json")){
			unlink("resultat_$num.json");
		};
		header("Location: accueil.php");
		exit();
	}

	function utilisateurValide($data){
		if($data['organisateur']==$_SESSION['login']){
			return true;
		}else{
			return false;
		}
	}

	function demandeNum(){
		print("
			<tr>
				<td>Veuillez entrer un numéro de scrutin :</td>
			 	<td><input type=text name=num_scrutin value='' /></td>
			</tr>
			<tr>
				<td style='text-align:center;' colspan=2><input type=submit name=Envoyer value=Envoyer /></td>
			</tr>
		");
	}

	function participation($data){
		$num=$_POST['num_scrutin'];
		if (file_exists("resultat_$num.json")){
			$jsonString = file_get_contents("resultat_$num.json");
			$res=json_decode($jsonString, true);
			if(count($res['resultat'])<=1){
				$strgVote="vote";
			}else{
				$strgVote="votes";
			}
			print("
				<b>
					Participation : ".count($res['resultat'])." $strgVote sur ".count($data['electeur'])."
				</b>
			");
		}else{
			print("
				<span style='text-align:center; color:#FF0000'; font-size:2em'><b>Scrutin non existant</b></span>
			");
		};
	};

	function afficheResultat($numero){
		print("
			<tr>
				<td class=Creascrutin>
					<h3><u>Résultats</u></h3>
		");
		if (file_exists("resultat_$numero.json")){
			$jsonString = file_get_contents("resultat_$numero.json");
			$res=json_decode($jsonString, true);
			
				if (count($res['resultat'])){
					$jsonString= file_get_contents("scrutin_$numero.json");
					$data=json_decode($jsonString, true);
					$compteur= array_count_values($res['resultat']);
					print("
						<fieldset class=pasTrop>
					");					
					foreach($res['resultat'] as $value){
						print("
							<b><textarea style='text-align: center; font-weight: bold;' readonly>$value </textarea></b>
						");
					};
					print("
						</fieldset>
						<br/>
					");
					foreach ($compteur as $key=>$value){
						if($value<=1){
							$strgVote="vote";
						}else{
							$strgVote="votes";
						}
						print("
							<span><u>$key :</u> $value $strgVote (".round($value*100/count($res['resultat']),4)."%)</span>
							<br/>
						");
					};
					print("
							<br/>
							<span><b>Nombre de votes total : ".count($res['resultat'])."</b></span>
					");
				}else{
					print("
						<span style='text-align:center; color:#FF0000'; font-size: 2em;'><b>Aucun vote</b></span>
					");
				}
		}else{
			print("
				<span style='text-align:center; color:#FF0000'; font-size: 2em;'><b>Le fichier résultat pour ce sondage n'existe pas!</b></span>
			");
		};
		print("
				</td>
			</tr>
		");
	};

	function cloreVote($data,$num){
		$clore=$data;
		$clore['actif']=0;
		$newJsonString = json_encode($clore, JSON_PRETTY_PRINT);
		file_put_contents("scrutin_$num.json", $newJsonString);
		afficheResultat($num);
	};
?>

<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8"/>
		<title> Gestion de scrutin</title>
		<link rel="stylesheet" type="text/css" href="syst-vote.css"/>
		<style type="text/css">
			input[name='creation-scrutin'],input[name='afficheParticipation'], input[name='cloreVote'],input[name='detruireVote']{
				width: 160px;
			}

			.option, .elec{
				width: 325px;
			}
		</style>
	</head>
	
	<body>
		<form action="gestion_scrutin.php" method="POST">
			<?php
				$login=$_SESSION['login'];
				print("
			 		<aside style='width: 60%; float: right;'>
			 			<h1 style='text-align:left;'><u>Gestion de scrutin</u></h1>
						<p style='text-align:right;'>
							Vous êtes connecté avec l'email: ".$_SESSION['login']."<br/>
							<input style='margin-top: 2%' type=submit name=deconnexion value=Deconnexion />
							<input style='margin-top: 2%' type=submit name=accueil value=Accueil />
						</p>
					</aside>
					<img id=vote src=vote.png alt=logo de vote />
					<hr/>
					<br/>
			 	");
				if(is_numeric($num)){
					if(file_exists("scrutin_$num.json") and file_exists("resultat_$num.json")){
						$jsonString = file_get_contents("scrutin_$num.json");
		 				$data=json_decode($jsonString, true);
		 				if(utilisateurValide($data)){
			 				$mdp=$_SESSION['password'];
			 				print("
			 					<table>
									<tr>
										<td>
											<h3><u>Organisateur</u></h3>
											<input type=email name=login value=".$data['organisateur']." readonly />
											<input type=password name=mot-de-passe value=$mdp readonly />

										</td>
										<td>
											<div style='margin-left: 20%; width: 100%;'>
												<b>Numéro de scrutin : </b><input style='font-weight:bold; width:50px;' type=text name=num_scrutin value=$num readonly />
												<br/>
												<b>
													Lien pour voter : 
													<a href=bulletin_de_vote.php?num_scrutin=$num>
														bulletin_de_vote.php?num_scrutin=$num
													</a>
												</b>
											</div>
										<td>
									</tr>
									<tr>
										<td>
											<h3><u> Question</u></h3>
											<textarea readonly name=question cols=45 rows=5>".$data['question']." </textarea>
										</td>
										<td>
											<div style='margin-left: 20%; width: 100%;'>
												<input type=submit name=afficheParticipation value='Affiche la participation' />
							");

							//Bouton Affiche Participation
							if (isset($_POST['afficheParticipation'])) {
								print("
									<br/>
									<br/>
								");
								participation($data);
							};

							print("
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<h3><u>".count($data['option'])." Options</u></h3>
											<fieldset class=pasTrop>
			 				");
			 				foreach($data['option'] as $value){
								print("
											<div>
												<input class=option type='text' name=$value value=$value readonly/>
											</div>
								");
							};
							print("
											</fieldset>
										</td>
										<td>
											<div style='margin-left: 20%; width: 100%;'>
												<input type=submit name=cloreVote value='Afficher les résultats' />
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<h3><u>".count($data['electeur'])." Electeurs</u></h3>
											<p style='width: 344px'> 
												Chaque électeur pourra voter autant de fois que son email est dans la liste.
											</p>
											<fieldset class=pasTrop>
							");
							foreach ($data['electeur'] as $value) {
			 					print("
										<div>
											<input class=elec type='email' name=$value value=$value readonly/>
										</div>
								");
		 					}
		 					print("
					 						</fieldset>
										</td>
										<td>
											<div style='margin-left: 20%; width: 100%;''>
												<input type=submit name=detruireVote value='Détuire le scrutin' />
											</div>
										</td>
									</tr>
								</table>
		 					");

		 					//Bouton Clore vote
							if(isset($_POST['cloreVote'])){
								cloreVote($data,$num);
							}

						}else{
							print("
								<table style='margin-bottom:10px;' align=center cellspacing=3px>
								<tr>
									<td style='text-align:center; color:#FF0000'; font-size:2em;' colspan=2>
										<b>Vous n'êtes pas l'organisateur de ce vote.</b>
									</td>
								<tr>
							");
							demandeNum();
							print("
								</table>
							");
							}
					}else{
						print("
							<table style='margin-bottom:10px;' align=center cellspacing=3px>
							<tr>
								<td style='text-align:center; color:#FF0000'; font-size:2em;' colspan=2>
									<b>Ce vote n'existe plus.</b>
								</td>
							<tr>
						");
						demandeNum();
						print("
							</table>
						");
					}
				}else{
					print("
						<table style='margin-bottom:10px;' align=center cellspacing=3px>
					");
					demandeNum();
					print("
						</table>
					");
				}
			?>
		</form>
	</body>
</html>