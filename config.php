<?php
// Configuration de FedaPay
define('FEDAPAY_API_KEY', 'sk_sandbox_YBW7_iQVqfvBcHef51mrorqg'); // Clé secrète de test
define('FEDAPAY_ENVIRONMENT', 'sandbox'); // Environnement de test

// Autres configurations
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));

// Configuration du timezone
date_default_timezone_set('Africa/Lagos'); // Utilisation d'un timezone valide pour le Bénin

// Configuration des logs et gestion des avertissements
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED); // Désactive les avertissements de dépréciation

// Désactivation des avertissements de dépréciation pour FedaPay
set_error_handler(function($errno, $errstr) {
    if (strpos($errstr, 'Creation of dynamic property') !== false) {
        return true; // Ignore les avertissements de propriétés dynamiques
    }
    return false;
}, E_DEPRECATED);
?> 
