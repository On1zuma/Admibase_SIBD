<?php

class DataController
{
    public function __construct()
    {
    }

    public function listOurTables()
    {
        $table_name = $_SESSION['id']['table'];
        $bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');

        if ($table_name != "*") {
            $tables = explode(", ", $table_name);
        } else {
            $stmt = $bdd->prepare("SHOW TABLES");
            $stmt->execute();
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $tables;
    }

    public function checkIfUserCanAccessTable()
    {
        $bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');

        $table_name = $_SESSION['id']['table']; // users tables
        $tableUrl = $_GET['table']; // table set in the url
        $stmt = $bdd->prepare("SHOW TABLES");
        $stmt->execute();
        $tablesBd = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Check if the table exists in the database
        if (!in_array($tableUrl, $tablesBd)) {
            header('Location: 404.php');
            exit; // stop the script
        }

        // Check if the user has access to the table
        if ($table_name != "*" && !in_array($tableUrl, explode(", ", $table_name))) {
            header('Location: list-table.php');
            exit; // stop the script
        }
        return $tableUrl;
    }

    public function listOfTableName($tableUrl)
    {
        $bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');
        // Get the column names for a table
        $tableName = $tableUrl;
        $stmt = $bdd->query("DESCRIBE $tableName");
        $columns = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }

    public function listOfRowName($tableUrl)
    {
        $bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');
        $tableName = $tableUrl;
        $stmt = $bdd->query("SELECT * FROM $tableName");
        $rows = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function listOfRowNameWithId($tableUrl, $id)
    {
        $bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');
        $tableName = $tableUrl;
        $stmt = $bdd->prepare("SELECT * FROM $tableName WHERE id = ?");
        $stmt->execute(array($id));

        $rows = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function insertRow(string $table, array $data)
    {
        try {
            $bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');

            // Get the maximum ID value from the table
            $sql = "SELECT MAX(id) FROM $table";
            $stmt = $bdd->prepare($sql);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Increment the maximum ID value to get the new ID value for the new row
            $newId = $maxId + 1;

            // Add the new ID value to the data array
            $data['id'] = $newId;

            // Build the SQL query
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));

            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $bdd->prepare($sql);
            if (!$stmt) {
                return false;
            }
            $values = array_values($data);
            $success = $stmt->execute($values);
            if (!$success) {
                return false;
            }
            header('Location: list-data.php?table='.$table);
        } catch (PDOException $e) {
            echo "Something went wrong: " . $e->getMessage();
        }
    }

    public function updateRow(string $table, int $id, array $data)
    {
        try {
            $data['id'] = $id; // Set the id in the array
            $bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', 'root', '');
            $set = implode(', ', array_map(function ($key) {
                return "$key = ?";
            }, array_keys($data)));
            $sql = "UPDATE `$table` SET $set WHERE id = ?";
            $stmt = $bdd->prepare($sql);

            if (!$stmt) {
                return false;
            }

            $values = array_merge(array_values($data), [$id]);
            $stmt->execute($values);
            header('Location: list-data.php?table='.$table);
        } catch (PDOException $e) {
            echo "Something went wrong: " . $e->getMessage();
        }
    }
}
