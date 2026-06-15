<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=escuela_sys', 'root', '');
$pdo->exec('DROP TABLE IF EXISTS maestro_materia');
echo "Tabla maestro_materia eliminada OK\n";
