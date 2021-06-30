<?php
	session_start();

	//numéro scrutin
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
		$_SESSION['page']="bulletin_de_vote";
		
		header("Location: connexion.php?num_scrutin=$num");
		exit();
	}

	//Déconnexion
	if (isset($_POST["deconnexion"])){
		session_destroy();
		header("Location: accueil.php");
		exit();
	}

	//Accueil
	if (isset($_POST["accueil"])){
		header("Location: accueil.php");
		exit();
	}

	function TraitementVote($num,$data,&$res){
		if(isset($_POST['vote'])){
			if(isset($_POST['choix-option'])){
			 	array_push($res['resultat'],$data['option'][$_POST['choix-option']]);
			 	array_push($res['A_voter'],$_SESSION['login']);
			 	$newResString=json_encode($res, JSON_PRETTY_PRINT);
			 	file_put_contents("resultat_$num.json", $newResString);
			 	$msg="<p style='color: green'><b>Votre vote a bien été pris en compte !</b> </p>";
			}else{
				$msg="<p style='color: red'><b>Veuillez sélectionner une option pour voter</b> </p>";
			}
		}else{
			$msg="";
		}
		return $msg;
	}

	function Peut_Voter($data){
		if(in_array($_SESSION['login'], $data['electeur'], true)){
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

	function A_deja_voter($login,$data,$res){
		$electeurs=array_count_values($data['electeur']);
		$deja_voter=array_count_values($res['A_voter']);
		if(isset($deja_voter[$login]) and $electeurs[$login]==$deja_voter[$login]){
			return true;
		}else{
			return false;
		}
	}
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8"/>
		<title> Bulletin de vote </title>
		<link rel="stylesheet" type="text/css" href="syst-vote.css"/>
	</head>
	
	<body>
		<form action="bulletin_de_vote.php?" method="POST">
			<?php
				$login=$_SESSION['login'];
				print("
			 		<aside style='width: 60%; float: right;'>
			 			<h1 style='text-align:left;'><u>Bulletin de vote</u></h1>
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
					if (file_exists("scrutin_$num.json") and file_exists("resultat_$num.json")){
		 				$jsonString = file_get_contents("scrutin_$num.json");
		 				$data = json_decode($jsonString, true);
		 				if(Peut_Voter($data)){
			 				if($data['actif']){
			 					$ResString = file_get_contents("resultat_$num.json");
			 					$res = json_decode($ResString, true);
			 					if(!A_deja_voter($login,$data,$res)){
				 					print("
				 						<div style='text-align:center;'>
						 					Numéro de scrutin : <input style='font-weight:bold; width:50px;' type=text name=num_scrutin value=$num readonly />
						 					<br/>
						 					<br/>
				 					");
				 					print ("
					 						Question :
					 						<br/> 
					 						<textarea readonly cols=50 rows=10>".$data['question']."</textarea>
				 					");

				 					print(TraitementVote($num,$data,$res)."
						 					<p style='text-align:center;'> 
												Choisissez une option et cliquez sur Voter !
											</p>
											<fieldset class=pasTrop>
												<table align=center>
													
									");
									foreach ($data['option'] as $key=>$value) {
										print("
													<tr>
														<td>
															<input type=radio name=choix-option value=$key />
															<label for=$key>$value</label>	
														</td>
													</tr>
										");
									};
									print("		
													<tr>
														<td>
															<input type=submit name=vote value=Voter />
														</td>
													</tr>
												</table>
											</fieldset>
										</div>
									");
								}else{
									print("
										<table style='margin-bottom:10px;' align=center cellspacing=3px>
										<tr>
											<td style='text-align:center; color:#FF0000'; font-size:2em;' colspan=2>
												<b>Vous avez déjà voté.</b>
											</td>
										<tr>
									");
									demandeNum();
									print("
										</table>
									");
								};
							}else{
								print("
									<table style='margin-bottom:10px;' align=center cellspacing=3px>
									<tr>
										<td style='text-align:center; color:#FF0000'; font-size:2em;' colspan=2>
											<b>Ce vote est terminé.</b>
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
										<b>Vous n'êtes pas autoriser à participer à ce vote.</b>
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
		<br/>
	</body>
</html>