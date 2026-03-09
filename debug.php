<?php
// Archivo de depuración

echo "<h1>Información de Depuración</h1>";

echo "<h2>Constantes definidas:</h2>";
echo "ROOT_PATH: " . (defined('ROOT_PATH') ? ROOT_PATH : 'NO DEFINIDA') . "<br>";
echo "APP_PATH: " . (defined('APP_PATH') ? APP_PATH : 'NO DEFINIDA') . "<br>";
echo "CONFIG_PATH: " . (defined('CONFIG_PATH') ? CONFIG_PATH : 'NO DEFINIDA') . "<br>";

echo "<h2>Variables de servidor:</h2>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "<br>";

echo "<h2>Sesión:</h2>";
session_start();
echo "ID de sesión: " . session_id() . "<br>";
echo "Datos de sesión: <pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Funciones helper:</h2>";
echo "isLoggedIn(): " . (function_exists('isLoggedIn') ? 'EXISTS' : 'NO EXISTS') . "<br>";
echo "isAdmin(): " . (function_exists('isAdmin') ? 'EXISTS' : 'NO EXISTS') . "<br>";
echo "redirect(): " . (function_exists('redirect') ? 'EXISTS' : 'NO EXISTS') . "<br>";

echo "<h2>Archivos incluidos:</h2>";
echo "<pre>" . print_r(get_included_files(), true) . "</pre>";
?>
