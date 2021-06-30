<?php 
	session_start();
	if(!isset($_SESSION['valid_user']) or $_SESSION['valid_user']!=1){
		$_SESSION['page']="creation_scrutin";
		header("Location: connexion.php");
		exit();
	}

	if (isset($_POST["deconnexion"])){
		session_destroy();
		header("Location: accueil.php");
		exit();
	}

	if (isset($_POST["accueil"])){
		header("Location: accueil.php");
		exit();
	}

	function ajoutParticipant(){
		print("
			<div>
				<input type=email name=participant />
				<input type=submit name=ajout value=+ />
			</div>
		");
	};

	function ajout(&$tab){
		if(isset($_POST['ajout']) and $_POST['participant']!="" and !ctype_space($_POST['participant'])){ // a cliqué sur le bouton +, et on a rempli le champ participant avec autre chose que des caractères blancs
			array_push($tab, $_POST['participant']);
		};
	};

	function suppr(&$tab){
		if(isset($_POST['suppr'])){
			if(in_array($_POST['suppr'], $tab)){
				$cle=array_search($_POST['suppr'],$tab);
				unset($tab[$cle]);
			}
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

	function cloreVote($data,$num){
		$clore=$data;
		$clore['actif']=0;
		$newJsonString = json_encode($clore, JSON_PRETTY_PRINT);
		file_put_contents("scrutin_$num.json", $newJsonString);
		afficheResultat($num);
	};		

	//Bouton Détruire le vote 
	if(isset($_POST['detruireVote'])){
		$num=$_POST['num_scrutin'];
		if(file_exists("scrutin_$num.json")){
			unlink("scrutin_$num.json");
		}
		if(file_exists("resultat_$num.json")){
			unlink("resultat_$num.json");
		}
	}


	//Ouverture d'un fichier JSON Différente en fonction de si on a déjà commencer à remplir le formulaire
	if(isset($_POST['ajout-option']) or isset($_POST['enlever']) or isset($_POST['creation-scrutin']) or isset($_POST['cloreVote']) or isset($_POST['afficheParticipation']) or isset($_POST['ajout']) or isset($_POST['suppr'])){
		$jsonString = file_get_contents("creation_scrutin_en_cours.json");
	}else{
		$jsonString = file_get_contents("creation_scrutin.json");
	};

	$data = json_decode($jsonString, true);
	$data['organisateur']=$_SESSION['login'];
	$mdp=$_SESSION['password'];

	//Question
	if(isset($_POST['question']) and !ctype_space($_POST['question']) and $_POST['question']!=""){// vérifie qu'on a pas un champ vide et que la variable existe
		$question=$_POST['question'];
	}else{
		$question="";
	}
	$data['question']=$question;		

	//Ajout-Option
	if(isset($_POST['ajout-option']) and $_POST['option']!="" and !ctype_space($_POST['option'])){ //on a cliqué sur le bouton +, et on a rempli le champ option avec autre chose que des caractères blancs
		array_push($data['option'], $_POST['option']);
	};

	//Supprime Option
	if(isset($_POST['enlever'])){
		$cle=array_search($_POST['enlever'],$data['option'],true);
		unset($data['option'][$cle]);
	};

	//ELecteurs
	if (!isset($_POST['liste'])){
		$_POST['liste']="nouvelle.json";
	}
	$dataList;
	if(isset($_POST['listePrecedente']) and ($_POST['listePrecedente']==$_POST['liste'])){
		$dataList = $data['electeur'];
		ajout($dataList);
		suppr($dataList);
	}else{
		$fichier=$_POST['liste'];
		$json_String = file_get_contents("liste/$fichier");
		$dataList = json_decode($json_String, true);
		ajout($dataList);
		suppr($dataList);
	};
	$data['electeur']=$dataList;

	//Numéro scrutin
	$num;
	if(isset($_POST['num_scrutin'])){
		$num=$_POST['num_scrutin'];
	}else{
		$num="";
	}

	//Bouton création Scrutin
	$scrutin_cree=0;
	$erreur="";
	if(isset($_POST['creation-scrutin'])){
		if(!ctype_space($question) and $question!=""){
			if(count($data['option'])>=2){
				$num=random_int(0, 100000);
				while(file_exists("scrutin_$num.json")){
					$num=random_int(0, 100000);
				};
				$scrutin_cree=1;
				$data['numero de scrutin']=$num;
				$newJsonString = json_encode($data, JSON_PRETTY_PRINT);
				file_put_contents("scrutin_$num.json", $newJsonString);
				$resultat=array("numero de scrutin" => $num,"resultat"=>array(), "A_voter"=>array());
				$newResultString=json_encode($resultat, JSON_PRETTY_PRINT);
				file_put_contents("resultat_$num.json", $newResultString);
			} else{
				$erreur="options";
				$newJsonString = json_encode($data, JSON_PRETTY_PRINT);
				file_put_contents('creation_scrutin_en_cours.json', $newJsonString);
			}
		}else{
			$erreur="question";
			$newJsonString = json_encode($data, JSON_PRETTY_PRINT);
			file_put_contents('creation_scrutin_en_cours.json', $newJsonString);
		}
	}else{
		$newJsonString = json_encode($data, JSON_PRETTY_PRINT);
		file_put_contents('creation_scrutin_en_cours.json', $newJsonString);
	};
?>

<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8"/>
		<title> Création du scrutin </title>
		<link rel="stylesheet" type="text/css" href="syst-vote.css"/>
		<style type="text/css">
			input[name='creation-scrutin'],input[name='afficheParticipation'], input[name='cloreVote'],input[name='detruireVote']{
				width: 160px;
			}
		</style>
	</head>

	<body>
		<form action="creation_scrutin.php" method="POST">
			<?php
				print("
					<aside style='width: 60%; float: right;'>
			 			<h1 style='text-align:left;'><u>Création de scrutin</u></h1>
						<p style='text-align:right;'>
							Vous êtes connecté avec l'email: ".$_SESSION['login']."<br/>
							<input style='margin-top: 2%' type=submit name=deconnexion value=Deconnexion />
							<input style='margin-top: 2%' type=submit name=accueil value=Accueil />
						</p>
					</aside>
					<img id=vote src=vote.png alt=logo de vote />
					<hr/>
				");
			?>
			<table>
				<tr>
					<td>
						<h3><u>Organisateur</u></h3>
						<?php
							print("
								<input type=email name=login value=".$data['organisateur']." readonly />
								<input type=password name=mot-de-passe value=$mdp readonly />
							");
						?>
					</td>
					<td>
						<div style='margin-left: 20%; width: 100%;'>
							<br/>
							<input  type="submit" name='creation-scrutin' value='Créer le scrutin' />
							<?php
								if((isset($_POST['creation-scrutin']) and $scrutin_cree) or ((isset($_POST['cloreVote']) or isset($_POST['afficheParticipation'])) and isset($_POST['num_scrutin']))){
									print("
										<br/>
										<b>Numéro de scrutin : </b><input style='width:50px; font-weight:bold;' type=text name=num_scrutin value=$num readonly />
										<input type=hidden name=num_scrutin value=$num />
										
										<br/>
										<b>Lien pour voter : <a href=bulletin_de_vote.php?num_scrutin=$num>bulletin_de_vote.php?num_scrutin=$num</a></b>
									");
								}
							?>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<h3><u> Question</u></h3>
						<?php
							if($erreur=="question"){
								print("
									<span style='text-align:center; color:#FF0000'; font-size:2em'>
										<b>Pas de question entrée</b><br/>
									</span>
								");
							}
						?>
						<textarea name="question" cols="45" rows="5"><?php echo $question ?></textarea>
					</td>
					<td>
						<div style='margin-left: 20%; width: 100%;'>
							<?php
								if((isset($_POST['creation-scrutin']) and $scrutin_cree) or ((isset($_POST['cloreVote']) or isset($_POST['afficheParticipation'])) and isset($_POST['num_scrutin']))){
									print("
										<input type=submit name=afficheParticipation value='Affiche la participation' />
									");
								}

								//Bouton Affiche Participation
								if (isset($_POST['afficheParticipation'])) {
									print("
										<br/>
										<br/>
									");
									participation($data);
								};
							?>
						</div>
					</td>
				</tr>
				<?php
					print("
						<tr>
							<td>
								<h3><u>".count($data['option'])." Options</u></h3>
							
					");
				
					if($erreur=="options"){
						print("
								<span style='text-align:center; color:#FF0000'; font-size:2em'>
									<b>Veuillez entrer 2 options au minimum</b><br/>
								</span>
								
						");
					}
					print("
								<fieldset class=pasTrop>
					");
					foreach($data['option'] as $value){
						print("
									<div>
										<input class=option type='text' name=$value value=$value readonly/>
										<input type='submit' name='enlever' value='$value' />
									</div>
						");
					}
					print("
									<div>
										<input type='text' name='option'/>
										<input type='submit' name='ajout-option' value='+' />
									</div>
								</fieldset>
							</td>
					");
				?>
						<td>
							<div style='margin-left: 20%; width: 100%;'>
								<br/>
								<?php
									if((isset($_POST['creation-scrutin']) and $scrutin_cree) or ((isset($_POST['cloreVote']) or isset($_POST['afficheParticipation'])) and isset($_POST['num_scrutin']))){
										print("
											<input type=submit name=cloreVote value='Afficher les résultats' />
										");
									}
								?>
							</div>
						</td>
					</tr>
				<?php
					print("
						<tr>
							<td>
								<h3><u>".count($dataList)." Electeurs</u></h3>
								<p style='width: 344px'> 
									Chaque électeur pourra voter autant de fois que son email est dans la liste.
								</p>
					");

					$tabListe=scandir("liste");
					print("
								<u>Liste d'électeurs :</u>
								<select name=liste>
					");

					foreach ($tabListe as $value) {
						if($value!="." and $value!=".." ){
							if(isset($_POST['liste']) and $_POST['liste']==$value){
								print("
										<option value=$value selected>".str_replace('.json','',$value)."</option>
								");
							}else{
								print("
									<option value=$value>".str_replace('.json','',$value)."</option>
								");
							};
							
						};
					};
					print("
								</select>
								<br/>
								<br/>
								<fieldset class=pasTrop>
					");
					foreach ($dataList as $value) {
		 					print("
									<div>
										<input class=elec type='email' name=$value value=$value readonly/>
										<input type=submit name=suppr value=$value />
									</div>
							");
		 			}
		 			ajoutParticipant();
					print("
									<input type=hidden name=listePrecedente value=".$_POST['liste']." />
								</fieldset>
							</td>
					");
				?>
					<td>
						<div style='margin-left: 20%; width: 100%;'>
							<?php
								if((isset($_POST['creation-scrutin']) and $scrutin_cree) or ((isset($_POST['cloreVote']) or isset($_POST['afficheParticipation'])) and isset($_POST['num_scrutin']))){
									print("
										<input type=submit name=detruireVote value='Détuire le scrutin' />
									");
								}
								//Bouton Clore Scrutin puis affiche les résultats
								if(isset($_POST['cloreVote'])){
									cloreVote($data,$num);
								};
							?>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>