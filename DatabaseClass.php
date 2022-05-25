<?php
namespace GetDateFromService\Classes\Orm;

use PDO;
use Exception;

require_once __DIR__ . "/../../../vendor/autoload.php";
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

class DatabaseClass
{
    protected string $query;
    protected PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';',
                $_ENV['DB_USER'],
                $_ENV['DB_PASS']
            );
        } catch (Exception $e) {
            $this->pdo = new PDO(
                'mysql:host=' . $_ENV['DB_HOST'] . ';',
                $_ENV['DB_USER'],
                $_ENV['DB_PASS']
            );
            if (count($_ENV['DB_NAME']) !== 0) {
                $this->pdo->query('CREATE DATABASE ' . $_ENV['DB_NAME']);
            }
        }
        $this->pdo->query("SET CHARSET 'utf-8'");
    }

    public function insert($tableName): object
    {
        $this->query = 'INSERT INTO `' . $tableName . '` ';
        return $this;
    }

    public function select($tableName): object
    {
        $this->query = "SELECT * FROM `" . $tableName . "`";
        return $this;
    }

    public function create($tableName): object
    {
        $this->query = 'CREATE TABLE `' . $tableName . '` (id int auto_increment primary key, content text not null, year_id int not null)';
        return $this;
    }

    public function drop($tableName): object
    {
        $this->query = 'DROP TABLE `' . $tableName . '`';
        return $this;
    }

    public function values($valueForTable): object
    {
        $colsTable = [];
        $valueInTable = [];

        foreach ($valueForTable as $key => $item) {
            $colsTable[] = $key;
            $valueInTable[] = $item;
        }
        $this->query .= "(" . implode(', ', $colsTable) . ") VALUE ('" . implode('\', \'', $valueInTable) . "')";
        return $this;
    }

    public function execute(): array
    {
        $q = $this->pdo->prepare($this->query);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    public function arbitraryRequest($request): object
    {
        $this->query = $request;
        return $this;
    }

    public function existTable($tableName): bool
    {
        $result = $this->pdo->prepare("SELECT TABLE_NAME AS NAME 
            FROM  INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_TYPE='BASE TABLE' 
            AND   TABLE_SCHEMA = '" . $_ENV['DB_NAME'] . "'");
        $result->execute();
        while ($res = $result->fetch(PDO::FETCH_ASSOC)) {
            if ($res['NAME'] == $tableName) {
                return true;
            }
        }
        return false;
    }
}