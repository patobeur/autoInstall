<?php

class Bdd
{
	private $host;
	private $username;
	private $password;

	private $database;
	private $table;
	private $pdo;

	public function __construct($host, $username, $password)
	{
		$this->host = Pat::get_clean($host);
		$this->username = Pat::get_clean($username);
		$this->password = Pat::get_clean($password);
	}
	public function connectServeur()
	{
		try {
			$dsn = "mysql:host={$this->host};charset=utf8mb4";
			$this->pdo = new PDO($dsn, $this->username, $this->password);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->pdo;
		} catch (PDOException $e) {
			throw new Exception('Erreur de connexion au serveur: ' . $e->getMessage());
		}
		return false;
	}
	public function set_DatabaseName($database)
	{
		$this->database = $database;
	}
	public function connectDb()
	{
		try {
			$dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";

			$this->pdo = new PDO($dsn, $this->username, $this->password);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new Exception('Erreur de connexion à la base de données: ' . $e->getMessage());
		}
		return $this->pdo;
	}
	// insert user account
	public function insertRow($table, $sql, $binds)
	{
		try {
			$connection = $this->connectDb($table);
			$stmt = $connection->prepare($sql);
			$service = $stmt->execute($binds);
		} catch (PDOException $e) {
			throw new Exception('Erreur lors de l\'insertion du user: ' . $e->getMessage());
		}
	}
	
			// foreach ($this->dbConnection->query("SHOW DATABASES") as $row)print_r($row['Database'] . "<br />");
			
}
