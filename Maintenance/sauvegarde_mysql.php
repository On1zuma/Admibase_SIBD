<?php

// Configuration de la base de données
$host = 'localhost';
$user = 'tache_automatisee';
$password = 'tacheAutosecurite';
$dbname = 'gamedb';

// Récupération des tables MyISAM
$tables = $pdo->query("SHOW TABLES WHERE Engine = 'MyISAM'")->fetchAll(PDO::FETCH_COLUMN);

// Pour chaque table
foreach ($tables as $table) {
  // Sauvegarder la table
  $pdo->query("SELECT * INTO OUTFILE '/home/mysqlbackup/$table.sql' FROM $table;")->execute();

}

//lancer le fichier une fois une fois
//php /var/www/Admibase_SIBD/Maintenance/sauvegarde_mysql.php
