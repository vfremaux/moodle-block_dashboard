<?php

$string['plugindist'] = 'Distribution du plugin';
$string['plugindist_desc'] = '
<p>Ce plugin est distribué dans la communauté Moodle pour l\'évaluation de ses fonctions centrales
correspondant à une utilisation courante du plugin. Une version "professionnelle" de ce plugin existe et est distribuée
sous certaines conditions, afin de soutenir l\'effort de développement, amélioration; documentation et suivi des versions.</p>
<p>Contactez un distributeur pour obtenir la version "Pro" et son support.</p>
<p><a href="http://www.mylearningfactory.com/index.php/documentation/Distributeurs?lang=fr_utf8">Distributeurs MyLF</a></p>';

// Caches.
$string['cachedef_pro'] = 'Stocke des données spécifiques de la zone "pro"';

require_once($CFG->dirroot.'/blocks/dashboard/lib.php'); // to get xx_supports_feature();
if ('pro' == block_dashboard_supports_feature('emulate/community')) {
    include($CFG->dirroot.'/blocks/dashboard/pro/lang/fr/pro.php');
}
