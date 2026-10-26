<?php
// Configuration de FedaPay
// La clé secrète n'est plus dans le code : elle vient de la variable d'environnement FEDAPAY_API_KEY.
define('FEDAPAY_API_KEY', getenv('FEDAPAY_API_KEY') ?: '');
define('FEDAPAY_ENVIRONMENT', getenv('FEDAPAY_ENVIRONMENT') ?: 'sandbox'); // sandbox ou live

// Autres configurations
// APP_URL (ex. https://school.onrender.com) si défini, sinon déduit de la requête
// (derrière Render, le HTTPS est détecté via X-Forwarded-Proto).
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
define('SITE_URL', getenv('APP_URL')
    ? rtrim(getenv('APP_URL'), '/')
    : ($isHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));

// Configuration du timezone
date_default_timezone_set('Africa/Lagos'); // Utilisation d'un timezone valide pour le Bénin

// Configuration des logs et gestion des avertissements
// Erreurs à l'écran seulement si APP_DEBUG=1 (jamais en production).
ini_set('display_errors', getenv('APP_DEBUG') ? 1 : 0);
ini_set('display_startup_errors', getenv('APP_DEBUG') ? 1 : 0);
error_reporting(E_ALL & ~E_DEPRECATED); // Désactive les avertissements de dépréciation

// Désactivation des avertissements de dépréciation pour FedaPay
set_error_handler(function($errno, $errstr) {
    if (strpos($errstr, 'Creation of dynamic property') !== false) {
        return true; // Ignore les avertissements de propriétés dynamiques
    }
    return false;
}, E_DEPRECATED);
?> 
