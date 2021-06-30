<?php
	session_start();

	function utilisateur_existe($nom,$mdp){
		if (file_exists("mdp.json")){
			$jsonString = file_get_contents("mdp.json");
			$data=json_decode($jsonString,true);
			if(isset($data["$nom"]) and $data["$nom"]===$mdp){
				$_SESSION ['valid_user']=1;
				$_SESSION ['login']=$nom;
				$_SESSION ['password']=$mdp;
				return true;
			}
		}
		return false;
	}

	if(isset($_POST['login'])){
			$login=$_POST['login'];
		}else{
			$login="";
	}

	$num;
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
	if(isset($_POST['connexion'])){
		if (utilisateur_existe($_POST['login'],$_POST['mot-de-passe'])){
			if($_SESSION['page']=="bulletin_de_vote" or $_SESSION['page']=="gestion_scrutin"){
				header("location: ".$_SESSION['page'].".php?num_scrutin=$num");
				exit();
			}else{
				header("location: ".$_SESSION['page'].".php");
				exit();
			}
		}else{
			$message="Email ou mot de passe incorrect";
		}
	}

	if (isset($_POST["accueil"])){
		header("Location: accueil.php");
		exit();
	}

	$adresse;
	if(isset($num)){
		$adresse="creation-compte.php?num_scrutin=$num";
	}else{
		$adresse="creation-compte.php";
	}
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8"/>
		<title> Connexion</title>
		<link rel="stylesheet" type="text/css" href="syst-vote.css"/>
	</head>
	
	<body>
		<form action="connexion.php" method="POST">
			<img id="vote" src="vote.png" alt="logo de vote"/>
			<aside class="titre">
				<h1><u>Connectez-vous</u></h1>
			</aside>
			<table class=P_connexion>
				<?php
					if($message!=""){
						print("
							<tr>
								<td colspan=2 style='text-align:center; color:#FF0000'; font-size:2em;'><b>$message</b></td>
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
					<td><input type="submit" name="connexion" value="Connexion"/></td>
					<td><input type="submit" name="accueil" value="Accueil"/></td>
				</tr>
				<tr>
					<td colspan="2" style='text-align: center'>Si vous n'avez pas de compte, 
						<?php 
							print("
								<a href=$adresse>cliquez ici</a>.
							");
						?>
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>