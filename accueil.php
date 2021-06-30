<?php
	session_start();
	$connecte=0;

	//Bouton déconnexion
	if(isset($_POST['deconnexion'])){
		session_destroy();
		header("location: accueil.php");
		exit();
	}

	if(isset($_SESSION['valid_user']) and $_SESSION['valid_user']==1){
		$connecte=1;
	}
	
	//Bouton Créer le scrutin
	if(isset($_POST['Creer-scrutin'])){
		$_SESSION['page']="creation_scrutin";
		header("location: creation_scrutin.php");
		exit();
	}

	//Bouton Voter
	if(isset($_POST['veux-vote'])){
		$_SESSION['page']="bulletin_de_vote";
		header("location: bulletin_de_vote.php");
		exit();
	}

	//Bouton Créer un compte
	if(isset($_POST['Creer-compte'])){
		$_SESSION['page']="accueil";
		header("location: Creation-compte.php");
		exit();
	}

	//Bouton Connexion
	if(isset($_POST['connexion'])){
		$_SESSION['page']="accueil";
		header("location: connexion.php");
		exit();
	}

	//Bouton Gérer un scrutin
	if(isset($_POST['gestion'])){
		$_SESSION['page']="gestion_scrutin";
		header("location: gestion_scrutin.php");
		exit();
	}
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8"/>
		<title> Accueil</title>
		<link rel="stylesheet" type="text/css" href="syst-vote.css"/>
	</head>
	
	<body>
		<form action="accueil.php" method="POST">
			<?php
				if($connecte){
					print("
						<aside style='width: 60%; float: right;'>
			 				<h1 style='text-align:left;'><u>Accueil</u></h1>
							<p style='text-align:right;'>
								Vous êtes connecté avec l'email: ".$_SESSION['login']."<br/>
								<input style='margin-top: 2%' type=submit name=deconnexion value=Deconnexion />
							</p>
						</aside>
						<img id=vote src=vote.png alt=logo de vote />
						<hr/>
					");
				}else{
					print("
						<aside style='width: 60%; float: right;'>
				 			<h1 style='text-align:left;'><u>Accueil</u></h1>
							<p style='text-align:right;'>
								<input style='height:40px; font-size: 0.85em;' type=submit name=connexion value=Connexion />
								<input style='height:40px; font-size: 0.85em;' type=submit name=Creer-compte value='Créer un compte'/>
							</p>
						</aside>
						<img id=vote src=vote.png alt=logo de vote />
						<hr/>
					");
				}
			?>
			<p id='Accueil_bouton'>
				<input class='Accueil_bouton' type="submit" name="Creer-scrutin" value="Je veux créer un scrutin"/>
				<input class='Accueil_bouton' type="submit" name="gestion" value="Je veux gérer un scrutin"/>
				<br/><br/>
				<input class='Accueil_bouton' type="submit" name="veux-vote" value="Je veux voter"/>
			</p>
		</form>
	</body>
</html>