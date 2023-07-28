<?php

if (str_contains( strtolower(__FILE__),strtolower(basename($_SERVER['PHP_SELF'])))) {
	header('refresh:0; url=' . '../');
	die();
}
class Installer
{
	private $default = [
		'v' => 0.9,
		'dev' => true,
		'rootpath' => '',
		'host' => '',
		'username' => '',
		'password' => '',
		'mail' => '',
		'adminname' => '',
		'adminpassword' => '',
		'submit' => '',
		'defaultDatabaseName' => 'tools',
		'table' => 'tools_users',
	];
	private $deleteOn = false;
	private $filepath_config;
	private $filepath_page;
	private $filepath_pageOld;
	private $filepath_mainController;

	private $BDD;
	private $dbConnection;

	private $msg = [];
	private $errors = [];

	public function __construct()
	{
		$this->dbConnection = null;
		$this->default['rootpath'] = getcwd();
		$this->default['host'] = $_SERVER['SERVER_NAME'];

		$this->filepath_config = $this->default['rootpath'] . '/conf/config.php';
		$this->filepath_page = $this->default['rootpath'] . '/php/page.php';
		$this->filepath_pageOld = $this->default['rootpath'] . '/php/pageOld.php';

		$this->filepath_mainController = './php/mainController.php';
	}


	public function install()
	{
		$retour = [];
		$this->errors = [];

		if (!$this->checkConfigFile()) {
			$this->errors[] = ['Le fichier de configuration n\'existe pas.', 'Installation'];
		} else {
			$this->redirect('Le fichier de configuration existe déjà.', './', 4);
			die();
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->processConfigForm();
			if (count($this->errors) < 1) {
				$this->autoDelete();
			}
		}



		if (count($this->errors) < 1) {
			$this->redirect('Installation ok !', './', 4);
		} else {
			$retour[] = $this->getConfigForm();
		}


		if (count($this->errors) > 0) {
			$retour[] = 'Quelques erreurs persistent...';
		}
		$this->afficheDesRetours($retour);
		$this->afficheDesErreures();

		die();
	}

	private function redirect($message, $url, $delay)
	{
		print_r($message);
		print_r('Redirection dans 4sec !');
		header('refresh:' . $delay . '; url=' . $url);
		die();
	}

	public function afficheDesErreures()
	{
		foreach ($this->errors as $key => $value) {
			echo("<div>"."[".htmlentities($value[1]) . "] ".htmlentities($value[0])."</div>");
		}
	}
	public function afficheDesRetours($retour)
	{
		foreach ($retour as $key => $value) {
			// print_r($key, $retour[$key]);
			echo($retour[$key]);
		}
	}

	private function checkConfigFile()
	{
		return file_exists($this->filepath_config);
	}
	private function processConfigForm()
	{
		$data = $_POST;

		$msg = [];
		if ($this->validateDataForm($data)) {
			$this->errors = [];
			$dbdata = [
				'host' => Pat::get_clean($data['host']),
				'username' => Pat::get_clean($data['username']),
				'password' => Pat::get_clean($data['password'])
			];
			$admindata = [
				'adminname' => Pat::get_clean($data['adminname']),
				'adminpassword' => Pat::get_clean($data['adminpassword']),
				'mail' => Pat::get_clean($data['mail'])
			];
			$confDatas = [
				'v' => $this->default['v'],
				'rootpath' => getcwd(),
				'defaultDatabaseName' => $this->default['defaultDatabaseName'],
				'defaultUsersTableName' => $this->default['table'],
			];

			try {
				$this->BDD = new Bdd(
					$dbdata['host'],
					$dbdata['username'],
					$dbdata['password']
				);
				$this->dbConnection = $this->BDD->connectServeur();
			} catch (Exception $e) {
				$this->errors[] = [$e->getMessage(),'connectServeur'];
			}

			if (count($this->errors) < 1) {
				try {
					$this->createDatabase($this->default['defaultDatabaseName']);
				} catch (Exception $e) {
					$this->errors[] = [$e->getMessage(),'createDatabase'];
				}
			}
			if (count($this->errors) < 1) {
				try {
					$this->createUsersTable();
				} catch (Exception $e) {
					$this->errors[] = [$e->getMessage(),'createUsersTable'];
				}
			}
			if (count($this->errors) < 1) {
				try {
					$this->createAdminUserRow($admindata);
				} catch (Exception $e) {
					$this->errors[] = [$e->getMessage(),'createAdminUserRow'];
				}
			}
			if (count($this->errors) < 1) {
				try {
					$this->writeConfigFile($this->filepath_config, $dbdata, $confDatas);
				} catch (Exception $e) {
					$this->errors[] = [$e->getMessage(),'writeConfigFile'];
				}
			}
			if (count($this->errors) < 1) {
				$this->replacePageFile();
			}
		}
	}

	private function createDatabase($database)
	{
		$connection = $this->dbConnection;
		try {
			// Connexion au serveur MySQL
			// $conn = new PDO("mysql:host=$this->servername", $this->username, $this->password);
			// Définition du mode d'erreur PDO sur Exception
			// $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Création de la base de données "Tools" si elle n'existe pas déjà
			$query = "CREATE DATABASE IF NOT EXISTS " . $database;
			$connection->exec($query);
			$this->BDD->set_DatabaseName($database);
		} catch (PDOException $e) {
			$this->msg[] = "Erreur lors de la création de la base de données " . $this->default['defaultDatabaseName'] . ": " . $e->getMessage();
		}
		$this->msg[] = "La base de données " . $this->default['defaultDatabaseName'] . " a été créée avec succès!";

		// Fermeture de la connexion à la base de données
		// $connection = null;
	}
	private function createUsersTable()
	{

		$this->dbConnection = $this->BDD->connectDb();
		try {
			$connection = $this->dbConnection;
			$sql = "CREATE TABLE IF NOT EXISTS " . $this->default['table'] . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                ip VARCHAR(45) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
			$stmt = $connection->prepare($sql);
			$stmt->execute();
		} catch (PDOException $e) {
			throw new Exception('Erreur lors de la création de la table: ' . $this->default['table'] . ': ' . $e->getMessage());
		}
		$this->msg[] = "La table " . $this->default['table'] . " a été créée avec succès.";
	}
	private function createAdminUserRow($admindata)
	{
		try {
			$sql = "INSERT INTO " . $this->default['table'] . " (username, password, email, ip) VALUES (?, ?, ?, ?)";

			$binds = [
				Pat::get_clean($admindata['adminname']),
				md5(Pat::get_clean($admindata['adminpassword'])), // attention Erreur en cour ici
				Pat::get_clean($admindata['mail']),
				Pat::get_ip_address()
			];
			$this->BDD->insertRow($this->default['table'], $sql, $binds);
		} catch (PDOException $e) {
			throw new Exception("Erreur lors de la création de la " . $this->default['table'] . ": " . $e->getMessage());
		}
		$this->msg[] = "Une ligne a été ajoutée à la table " . $this->default['table'] . ".";
	}
	// ---------------------------- Form Manager
	private function getConfigForm($errors = [])
	{
		$form = '
        <form class="form-container" method="POST" action="">
            <h2>Server Datas</h2>
            <label for="host">Host:</label>
            <input type="text" name="host" required class="error" value="' . $this->default['host'] . '"><br>

            <label for="username">DB Username:</label>
            <input type="text" name="username" required value="' . $this->default['username'] . '"><br>

            <label for="password">DB Password:</label>
            <input type="password" name="password" required value="' . $this->default['password'] . '"><br>

            <h2>Account Datas</h2>
            <label for="mail">E-mail:</label>
            <input type="mail" name="mail" required value="' . $this->default['mail'] . '"><br>
            
            <label for="adminname">Login Name</label>
            <input type="text" name="adminname" required value="' . $this->default['adminname'] . '"><br>

            <label for="adminpassword">Admin Password:</label>
            <input type="password" name="adminpassword" required value="' . $this->default['adminpassword'] . '"><br>

            <input type="submit" name="submit" value="' . $this->default['submit'] . '">
        </form>';

		return $form;
	}
	private function validateDataForm($data)
	{
		// Valider les champs requis
		if (
			!empty($data['host']) ||
			!empty($data['username']) ||
			!empty($data['password']) ||
			!empty($data['adminname']) ||
			!empty($data['adminpassword']) ||
			!empty($data['submit']) ||
			$data['submit'] === "Save"
		) {
			return true;
		}
		return false;
	}

	// ---------------------------- File Manager
	private function replacePageFile()
	{
		try {
			if (file_exists($this->filepath_page)) {
				if (file_exists($this->filepath_pageOld)) {
					unlink($this->filepath_pageOld);
					$this->msg[] =   "Le fichier " . $this->filepath_pageOld . " supprimé avec succès !";
				}
				rename($this->filepath_page, $this->filepath_pageOld);
				$this->msg[] =  "Le fichier " . $this->filepath_page . " a été renommé en " . $this->filepath_pageOld . " avec succès !";
				// unlink($this->filepath_page);
				// echo "Le fichier ".$this->filepath_page." a été supprimé avec succès !";


				$htmlContent = "";
				if (isset($this->filepath_mainController) and file_exists($this->filepath_mainController)) {
					$htmlContent = "<?php\n";
					$htmlContent .= "	define('ON',true);\n";
					$htmlContent .= '	require_once("' . $this->filepath_mainController . '");' . "\n";
				}
				$this->writePageFile($this->filepath_page, $htmlContent);
			} else {
				$this->msg[] =  "Le fichier " . $this->filepath_page . " n'existe pas.";
			}
		} catch (Exception $e) {
			throw new Exception("Erreur lors de la suppression du fichier 'config.php': " . $e->getMessage());
		}
	}
	private function writeConfigFile($Path, $dbdatas, $confdatas)
	{
		$date = date('Y-m-d H:i:s');
		$configData = "<?php\n";
		$configData .= "// birth----- time----\n";
		$configData .= "// $date\n\n";

		$configData .= 'define("DATAS", array(' . "\n";
		foreach ($dbdatas as $key => $value) {
			$configData .= "    '$key' => '$value',\n";
		}
		$configData .= "));\n";


		$configData .= 'define("CONF", array(' . "\n";
		foreach ($confdatas as $key => $value) {
			$configData .= "    '$key' => '$value',\n";
		}
		$configData .= "));\n";

		if (file_put_contents($Path, $configData)) {
			chmod($Path, 0644);
			$this->msg[] = 'Fichier de configuration créé et enregistré !';
			return true;
		} else {
			throw new Exception('Impossible d\'écrire le fichier {$Path}.');
		}
	}
	private function writePageFile($Path, $htmlstring)
	{
		if (file_put_contents($Path, $htmlstring)) {
			$this->msg[] =   "Fichier 'page' modifié et enregistré !";
			chmod($Path, 0644);
			return true;
		} else {
			throw new Exception("Impossible d'écrire le fichier {$Path}.");
		}
	}
	private function autoDelete()
	{
		try {
			$file = "./php/Installer.php";
			$rep = "./php";
			if (file_exists($file)) {
				chmod($file, 700);
				if($this->deleteOn) {
					unlink($file);
				}else {
					rename($file, $file.'old');
					chmod($file.'old', 644);
				}
				chmod($rep, 644);
			}
		} catch (PDOException $e) {
			$this->errors[] = ["Erreur de supression du fichier : " . $file . ": ", $e->getMessage()];
		}
	}
}
include('php/FunMini.php');
include('php/Bdd.php');
$installer = new Installer();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
	<title>SaveTools</title>
	<link rel="stylesheet" href="./assets/css/main.css">
	<link rel="stylesheet" href="./assets/css/forminstall.css">
</head>

<body>
	<script>
		window.addEventListener('DOMContentLoaded', function() {
			var themeToggleCheckbox = document.getElementById('theme-toggle-checkbox');
			var themeToggleLabel = document.getElementById('theme-toggle-label');

			themeToggleCheckbox.addEventListener('change', function() {
				if (this.checked) {
					document.body.classList.add('dark-theme');
					themeToggleLabel.setAttribute('aria-label', 'Activer le thème jour');
				} else {
					document.body.classList.remove('dark-theme');
					themeToggleLabel.setAttribute('aria-label', 'Activer le thème nuit');
				}
			});
		});
	</script>
	<div class="theme-toggle">
		<input type="checkbox" id="theme-toggle-checkbox">
		<label for="theme-toggle-checkbox" id="theme-toggle-label"></label>
	</div>
	<?php
	$installer->install();
	?>
</body>

</html>
