<?php 
	session_start();

	function creationCompte($nom,$mdp,&$msg){
		if($nom!="" and isset($mdp)){
			if(!ctype_space($mdp) and $mdp!=""){
				if (file_exists("mdp.json")){
					$jsonString = file_get_contents("mdp.json");
					$data=json_decode($jsonString,true);
					if(isset($data["$nom"])){
						$msg="Compte déjà existant";
					}else{
						$data["$nom"]=$mdp;
						$NewJsonString=json_encode($data, JSON_PRETTY_PRINT);
						file_put_contents("mdp.json", $NewJsonString);
						$_SESSION ['valid_user']=1;
						$_SESSION ['login']=$nom;
						$_SESSION ['password']=$mdp;
						return true;
					}
				}
			}else{
				$msg="Caractères incorrects dans le mot de passe";
			};
		}else{
			$msg="Veuillez remplir tous les champs obligatoires";
		}
		return false;
	}

	$login;
	if(isset($_POST['login'])){
			$login=$_POST['login'];
		}else{
			$login="";
	}

	if($_SESSION['page']=="bulletin_de_vote" or $_SESSION['page']=="gestion_scrutin"){
		if(isset($_POST['num_scrutin']) and is_numeric($_POST['num_scrutin'])){
			$num=$_POST['num_scrutin'];
		}else{
			if(isset($_GET['num_scrutin']) and is_numeric($_GET['num_scrutin'])){
				$num=$_GET['num_scrutin'];
			}else{
				$num="";
			}
		}
	}

	$message="";
	if(isset($_POST['Creer'])){
		if(creationCompte($_POST['login'],$_POST['mot-de-passe'],$message)){
			if($_SESSION['page']=="bulletin_de_vote" or $_SESSION['page']=="gestion_scrutin"){
				header("location: ".$_SESSION['page'].".php?num_scrutin=$num");
				exit();
			}else{
				header("location: ".$_SESSION['page'].".php");
				exit();
			}
		}
	}

	if (isset($_POST["accueil"])){
		header("Location: accueil.php");
		exit();
	}
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8"/>
		<title> Création de compte</title>
		<link rel="stylesheet" type="text/css" href="syst-vote.css"/>
	</head>
	
	<body>
		<form action="creation-compte.php" method="POST">
			<img id="vote" src="vote.png" alt="logo de vote"/>
			<aside class="titre">
				<h1><u>Création de compte</u></h1>
			</aside>
			<table class="P_connexion">
				<?php
					if($message!=""){
						print("
							<tr>
								<td colspan=2 style='text-align:center; color:#FF0000'; font-size:2em'><b>$message</b></td>
							</tr>
						");
					}
				?>
				<tr>
					<td>Email : </td>
					<td><input type="email" name="login" value="<?php echo $login ?>"/></td>
				</tr>
				<tr>
					<td>Mot de passe :</td>
					<td><input type="password" name="mot-de-passe" value="" /></td>
				</tr>
				<?php
					if($_SESSION['page']=="bulletin_de_vote" or $_SESSION['page']=="gestion_scrutin"){
						print("
							<tr>
								<td>Numéro de scrutin (facultatif) :</td>
								<td><input type=text name=num_scrutin value='$num' /></td>
							</tr>
						");
					}
				?>
				<tr>
					<td><input type="submit" name="Creer" value="Créer"/></td>
					<td><input type="submit" name="accueil" value="Accueil"/></td>
				</tr>
			</table>
		</form>
	</body>
</html>