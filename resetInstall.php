<?php

if (isset($_GET['reset']) && $_GET['reset'] === "true" && isset($_GET['vraimentsur']) && $_GET['vraimentsur'] === "oui") {
	$msg = [];
	function deleteFile($filename, $delete = true)
	{
		if (file_exists($filename)) {
			if ($delete === true) {
				try {
					unlink($filename);
				} catch (Exception $e) {
					throw new Exception("Erreur lors de la suppression du fichier '" . $filename . "': " . $e->getMessage());
				}
				$msg[] = "Le fichier '" . $filename . "' a été supprimé avec succès!";
			} else {
				try {
					rename($filename, $filename . "Bak");
				} catch (Exception $e) {
					throw new Exception("Erreur lors du renommage du fichier '" . $filename . "': " . $e->getMessage());
				}
				$msg[] = "Le fichier '" . $filename . "' a été renommé avec succès!";
			}
		} else {
			$msg[] = "Le fichier '" . $filename . "' n'existe pas.";
		}
	}
	function getConn($config, $table = false)
	{
		try {
			$dsn = "mysql:host={$config["host"]};charset=utf8mb4";
			$conn = new PDO($dsn, $config["username"], $config["password"]);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new Exception("Erreur lors de la suppression de la table '" . $config['tableName'] . "': " . $e->getMessage());
		}
		return $conn;
	}

	function dropDatabase($datas, $config)
	{
		getConn($datas)->exec("DROP DATABASE IF EXISTS " . $config["defaultDatabaseName"]);
	}
	function rewriteFile($Path)
	{
		$htmlstring = "<?php\n\nif (!file_exists('./conf/config.php') && file_exists('./php/Installer.php')) require('./php/Installer.php');\n\n";
		if (file_put_contents($Path, $htmlstring)) {
			$msg[] = "Fichier 'page' terminée et enregistré !";
			return true;
		} else {
			throw new Exception("Impossible d'écrire le fichier {$Path}. " . $e->getMessage());
			return false;
		}
	}
	function full_path()
	{
		$s = &$_SERVER;
		$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
		$sp = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port = $s['SERVER_PORT'];
		$port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
		$host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
		$host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
		$uri = $protocol . '://' . $host . $s['REQUEST_URI'];
		$segments = explode('?', $uri, 2);
		$url = $segments[0];
		return $url;
	}
	$filename = './conf/config.php';
	$pagefilename = './php/page.php';
	$root = dirname('./resetinstall.php');

	if (file_exists($filename)) {
		include($filename);
		dropDatabase(DATAS, CONF);
		deleteFile($filename, false);
		rewriteFile($pagefilename);
		print_r('<div class="merci" style="position: absolute;width: 70px;background-color: black;color: white;text-align: center;padding: .5rem;font-style: normal;font-variant-caps: all-petite-caps;border-radius: 7px;left: calc( 50% - 35px);top: 47%;height: 13px;">ok</div>');
		header("refresh:2; url=" . $root);
		die();
	}
} else {
	$msg[] = 'Are you Realy Sure you want to erase all datas ? : <a href="resetInstall.php?reset=true&vraimentsur=oui">Yes</a> !';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
	<title>Delete Install</title>
</head>

<body><?php
		$contentHtml = '';
		if (count($msg) > 0) {
			foreach ($msg  as $key => $value) {
				$contentHtml .= $value;
			}
			echo $contentHtml;
		}
		?></body>

</html>
