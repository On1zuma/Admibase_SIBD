<?php

class DataController
{
    private $bdd;
    public function __construct()
    {
        $this->bdd = new PDO('mysql:host=localhost;dbname=gamedb;charset=utf8;', $_SESSION['username'], $_SESSION['password']);
    }

    public function listOurTables()
    {
        $table_name = $_SESSION['id']['table'];

        if ($table_name != "*") {
            $tables = explode(", ", $table_name);
        } else {
            $stmt = $this->bdd->prepare("SHOW TABLES");
            $stmt->execute();
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $tables;
    }

    public function checkIfUserCanAccessTable()
    {
        $table_name = $_SESSION['id']['table']; // users tables
        $tableUrl = $_GET['table']; // table set in the url
        $stmt = $this->bdd->prepare("SHOW TABLES");
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
        // Prepare the table name for the query
        $tableName = filter_var($tableUrl, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

        // Prepare the query
        $stmt = $this->bdd->prepare("DESCRIBE $tableName");

        // Execute the query
        $stmt->execute();

        // Fetch the results
        $columns = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }

    public function listOfRowName($tableUrl)
    {
        $offset = $this->pagination();
        $tableName = $tableUrl;
        $sql = "SELECT * FROM $tableName LIMIT 10 OFFSET :offset";
        $stmt = $this->bdd->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }


    public function listOfRowNameWithFilter($tableUrl, $column, $order)
    {
        $offset = $this->pagination();
        $tableName = $tableUrl;
        $sql = "SELECT * FROM $tableName ORDER BY $column $order LIMIT 10 OFFSET :offset";
        $stmt = $this->bdd->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }


    public function listOfRowNameWithSearch($tableUrl, $search)
    {
        $offset = $this->pagination();

        $tableName = $tableUrl;
        $columns = $this->listOfTableName($tableUrl);

        $sql = "SELECT * FROM $tableName WHERE ";
        $conditions = array();
        $params = array();
        foreach ($columns as $colonne) {
            $conditions[] = $colonne . " LIKE ?";
            $params[] = '%' . $search . '%';
        }
        $sql .= implode(' OR ', $conditions);
        $sql = $sql." LIMIT 10 OFFSET $offset";

        $stmt = $this->bdd->prepare($sql);
        $stmt->execute($params);

        $rows = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }


    public function listOfRowNameWithId($tableUrl, $id)
    {
        $tableName = $tableUrl;
        $stmt = $this->bdd->prepare("SELECT * FROM $tableName WHERE id = ?");
        $stmt->execute(array($id));

        $rows = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function insertRow(string $table, array $data)
    {
        $this->checkIfUserCanAccessTable();
        try {
            // Get the maximum ID value from the table
            $sql = "SELECT MAX(id) FROM $table";
            $stmt = $this->bdd->prepare($sql);
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
            $stmt = $this->bdd->prepare($sql);
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
        $this->checkIfUserCanAccessTable();
        try {
            $data['id'] = $id; // Set the id in the array

            $set = implode(', ', array_map(function ($key) {
                return "$key = ?";
            }, array_keys($data)));
            $sql = "UPDATE `$table` SET $set WHERE id = ?";
            $stmt = $this->bdd->prepare($sql);

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

    public function deleteRows(string $table, array $ids)
    {
        $this->checkIfUserCanAccessTable();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "DELETE FROM `$table` WHERE id IN ($placeholders)";
        $stmt = $this->bdd->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->execute($ids);
        return true;
    }

    public function pagination()
    {
        $page = 1;
        $offset = 0;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        } else {
            $page = 1;
        }

        if ($page == 1) {
            $offset = 0;
        } else {
            $offset = ($page - 1) * 10;
        }

        return $offset;
    }
}
