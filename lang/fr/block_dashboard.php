<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_dashboard
 * @category   blocks
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  Exporter of dashboard data snapshot
 */

// Capabilities.
$string['dashboard:addinstance'] = 'Peut ajouter une instance';
$string['dashboard:myaddinstance'] = 'Peut ajouter une instance à la page personnalisée';
$string['dashboard:configure'] = 'Peut configurer le tableau de bord';
$string['dashboard:systempathaccess'] = 'Peut générer des fichiers dans tout le système de fichiers';
$string['dashboard:export'] = 'Exporter les données (web service)';

$string['backtocourse'] = 'Revenir au cours';
$string['backtoview'] = 'Revenir au résultat de requête';
$string['bar'] = 'Barres';
$string['bigresult'] = 'Désactiver la sécurité';
$string['blockname'] = 'Tableaux de bord';
$string['cacheddata'] = 'Données du cache de requête';
$string['checktohide'] = 'Cocher la case pour cacher le titre';
$string['choicevalue'] = 'Choix';
$string['cleandisplayuptocolumn'] = 'Ne pas nettoyer après la colonne n°&nbsp;';
$string['cleararea'] = 'Vider la zone de fichiers';
$string['colon'] = 'Deux points';
$string['coma'] = 'Virgule';
$string['configcaching'] = 'Activation du cache de résultats&nbsp;';
$string['configcachingttl'] = 'TTL du cache&nbsp;';
$string['configcleandisplay'] = 'Nettoyer la table&nbsp;';
$string['configcoloredvalues'] = 'Valeurs de contrôle des couleurs';
$string['configcolorfield'] = 'Champ de controle des couleurs';
$string['configcolors'] = 'Couleurs';
$string['configcopy'] = 'Importer la configuration d\'un tableau de bord';
$string['configcronfrequency'] = 'Fréquence&nbsp;';
$string['configcronmode'] = 'Mode de programmation du raffraichissement';
$string['configcrontime'] = 'Heure&nbsp;';
$string['configdashboardparams'] = 'Paramètres du tableau de bord';
$string['configdata'] = 'Données GoogleMaps';
$string['configdelayedrefresh'] = 'Raffraichissement différé des données de tableau de bord.';
$string['configdisplay'] = 'Eléments d\'affichage&nbsp;';
$string['configenablehorizsums'] = 'Activer les totaux horizontaux';
$string['configenablevertsums'] = 'Activer les totaux verticaux';
$string['configeventmapping'] = 'Mapping de données pour les événements';
$string['configdescription'] = 'Description';
$string['configfileformat'] = 'Type de fichier de sortie&nbsp;';
$string['configfileheaders'] = 'En-têtes du fichier&nbsp;';
$string['configfilelocation'] = 'Emplacement du fichier (dans moodledata) ';
$string['configfileoutput'] = 'Champs de sortie sur fichier&nbsp;';
$string['configfileoutputformats'] = 'Formattage des données&nbsp;';
$string['configfilepathadminoverride'] = 'Emplacement spécial&nbsp;';
$string['configfilesqlouttable'] = 'Nom de la table SQL (sortie SQL)&nbsp;';
$string['configfilterdefaults'] = 'Défaut pour les filtres&nbsp;';
$string['configfilterlabels'] = 'Label des filtres&nbsp;';
$string['configfilteroptions'] = 'Options pour les filtres&nbsp;';
$string['configfilters'] = 'Filtres';
$string['configgraphheight'] = 'Hauteur du graphe&nbsp;';
$string['configgraphtype'] = 'Type de graphe&nbsp;';
$string['configgraphwidth'] = 'Largeur du graphe&nbsp;';
$string['confighidetitle'] = 'Cacher le titre du bloc';
$string['confighierarchic'] = 'Affichage de données hiérarchiques';
$string['confighorizformat'] = 'Formatage des clefs horizontales&nbsp;';
$string['confighorizkey'] = 'Clef horizontale&nbsp;';
$string['confighorizlabel'] = 'Label de la série horizontale&nbsp;';
$string['confighorodatefiles'] = 'Horodater les fichiers&nbsp;';
$string['configimportexport'] = 'Import/Export de la configuration';
$string['configlat'] = 'Latitude&nbsp;';
$string['configlayout'] = 'Publier les données dans le bloc&nbsp;';
$string['configlocation'] = 'Emplacement&nbsp;';
$string['configlowerbandunit'] = 'Echelle de la bande inférieure&nbsp;';
$string['configmakefile'] = 'Générer la sortie&nbsp;';
$string['configmaptype'] = 'Type de carte&nbsp;';
$string['confignumsums'] = 'Champs sommateurs&nbsp;';
$string['confignumsumsformats'] = 'Format des sommes&nbsp;';
$string['confignumsumslabels'] = 'Titre des sommes&nbsp;';
$string['configoutputfields'] = 'Colonnes de sorties&nbsp;';
$string['configoutputfieldslabels'] = 'Nom des colonnes de sortie&nbsp;';
$string['configoutputformats'] = 'Format des données de sorties&nbsp;';
$string['configpagesize'] = 'Taille de pagination&nbsp;';
$string['configparams'] = 'Les paramètres utilisateur permettent à l\'exploitant du tableau de bord de rentrer des paramètres pour modifier le comportement de la requête et son domaine de sortie.';
$string['configparent'] = 'Série de la liaison hiérarchique&nbsp;';
$string['configquery'] = 'Requête&nbsp;';
$string['configqueryrotate'] = 'Pivoter les données&nbsp;';
$string['configreminderonsep'] = '<span style="font-size:1.3em;color:#808080">Ne pas oublier que le séparateur est <b>nécessairement</b> un ;</span>';
$string['configrotatecolumns'] = 'Faire une rotation de ';
$string['configrotatenewkeys'] = ' sur les clefs ';
$string['configrotatepivot'] = ' autour du pivot ';
$string['configsendadminnotification'] = 'Envoyer des notifications administrateur sur cron&nbsp;';
$string['configserieslabels'] = 'Titre des séries&nbsp;';
$string['configshowdata'] = 'Montrer les données&nbsp;';
$string['configshowfilterqueries'] = 'Montrer les requêtes des filtres (debug)&nbsp;';
$string['configshowgraph'] = 'Montrer le graphe&nbsp;';
$string['configshowlegend'] = 'Montrer la légende&nbsp;';
$string['configshowlowerband'] = 'Afficher la bande inférieure&nbsp;';
$string['configshownumsums'] = 'Montrer les sommateurs&nbsp;';
$string['configshowquery'] = 'Montrer la requête (debug)&nbsp;';
$string['configsortable'] = 'Table triable&nbsp;';
$string['configspliton'] = 'Dissocier la table sur la série&nbsp;';
$string['configsplitsumsonsort'] = 'Colonne de séparation de sous-totaux&nbsp;';
$string['configtablesplit_help'] = 'Si défini, les valeurs différentes de cette série de données sépareront les résultats en tables différentes';
$string['configtabletype'] = 'Type de table&nbsp;';
$string['configtarget'] = 'Système cible&nbsp;';
$string['configtickspacing'] = 'Espacement entre marques&nbsp;';
$string['configtitle'] = 'Titre du bloc&nbsp;';
$string['configtreeoutput'] = 'Séries de l\'arbre&nbsp;';
$string['configtreeoutputformats'] = 'Formats des séries de l\'arbre&nbsp;';
$string['configupperbandunit'] = 'Echelle de la bande supérieure&nbsp;';
$string['configure'] = 'Configurer';
$string['configverticalformats'] = 'Formatage des clefs verticales&nbsp;';
$string['configverticalkeys'] = 'Clef(s) verticales&nbsp;';
$string['configverticallabels'] = 'Label des séries verticales&nbsp;';
$string['configxaxisfield'] = 'Champ des abscisses (axe X)&nbsp;';
$string['configxaxislabel'] = 'Nom d\'axe X&nbsp;';
$string['configyaxislabel'] = 'Nom d\'axe Y&nbsp;';
$string['configyaxisscale'] = 'Type d\'échelle Y&nbsp;';
$string['configyaxistickangle'] = 'Angle des étiquettes X&nbsp;';
$string['configymax'] = 'Max axe Y&nbsp;';
$string['configymin'] = 'Min axe Y&nbsp;';
$string['configyseries'] = 'Séries de données';
$string['configyseriesformats'] = 'Formats de données des séries&nbsp;';
$string['configzoom'] = 'Zoom&nbsp;';
$string['crontraceon'] = 'Trace spécifique de cron';
$string['crontraceon_desc'] = 'Si activé, génère un fichier de log <dataroot>/dashboards.log pour tracer les exécutions de cron.';
$string['csv'] = 'Enregistrements CSV';
$string['csvfieldseparator'] = 'Séparateur de champs&nbsp;';
$string['csvfieldseparator_desc'] = 'Séparateur de champs CSV (Toute la plate-forme)&nbsp;';
$string['csvlineseparator'] = 'Séparateur de lignes&nbsp;';
$string['csvlineseparator_desc'] = 'Séparateur de champs CSV (Toute la plate-forme)&nbsp;';
$string['csvwithoutheader'] = 'Enregistrements CSV sans ligne d\'en-tête';
$string['daily'] = 'Tous les jours&nbsp;';
$string['dashboard_big_result_threshold'] = 'Sécurité "gros résultat"&nbsp;';
$string['dashboard_cron_enabled'] = 'Activation du cron&nbsp;';
$string['dashboard_cron_freq'] = 'Fréquence hebdomadaire&nbsp;';
$string['dashboard_cron_hour'] = 'Heure&nbsp;';
$string['dashboard_cron_min'] = 'Minutes&nbsp;';
$string['dashboard_enable_isediting_security'] = 'Active la sécurisation en mode édition. En mode sécurisé, les requêtes ne sont pas exécutées tant que le cours est en mode édition.';
$string['dashboard_extra_db_db'] = 'Connexion accessoire (Postgre) : base ';
$string['dashboard_extra_db_host'] = 'Connexion accessoire (Postgre) : hôte ';
$string['dashboard_extra_db_password'] = 'Connexion accessoire (Postgre) : mot de passe ';
$string['dashboard_extra_db_port'] = 'Connexion accessoire (Postgre) : port ';
$string['dashboard_extra_db_user'] = 'Connexion accessoire (Postgre) : login ';
$string['dashboard_output_encoding'] = 'Encodage de sortie ';
$string['dashboard_output_field_separator'] = 'Séparateur de champs de sortie&nbsp;';
$string['dashboard_output_line_separator'] = 'Séparateur de lignes de sortie&nbsp;';
$string['dashboardlayout'] = 'Mise en page du tableau de bord';
$string['dashboards'] = 'Tableaux de bord';
$string['dashboardstoragearea'] = 'Zone de fichiers du tableau de bord';
$string['datalocations'] = 'Géolocaliseurs';
$string['datarefresh'] = 'Raffraichissement de données';
$string['datatitles'] = 'Titre des marques&nbsp;';
$string['datatypes'] = 'Types de données&nbsp;';
$string['daterangevalue'] = 'Plage de date';
$string['datevalue'] = 'Date';
$string['day'] = 'Jour';
$string['dofilter'] = 'Filtrer';
$string['donut'] = 'Donut';
$string['dropconfig'] = 'Copier la configuration';
$string['editingnoexecute'] = ' Le tableau de bord ne peut pas exécuter de requêtes en mode édition.';
$string['enabled'] = ' activé ';
$string['eventdesc'] = 'Série des descriptions d\'événement&nbsp;';
$string['eventend'] = 'Série des fins de plage&nbsp;';
$string['eventlink'] = 'Série des cibles de liens&nbsp;';
$string['eventstart'] = 'Série des débuts de plage&nbsp;';
$string['eventtaskprocessed'] = 'Tâche de tableau de bord exécutée';
$string['eventtaskempty'] = 'Tâche de tableau de bord sans résultats';
$string['eventtitles'] = 'Série des titres&nbsp;';
$string['exportall'] = 'Exporter toutes les données';
$string['exportconfig'] = 'Obtenir la configuration';
$string['exportdataastable'] = 'Exporter les données en tableau';
$string['exportfiltered'] = 'Exporter les données filtrées';
$string['extradbparams'] = 'Paramètres de la connexion accessoire';
$string['filegenerated'] = 'Le fichier a été généré';
$string['fileoutput'] = 'Sortie sur fichier';
$string['filesview'] = 'Voir la zone de fichiers';
$string['filternotretrievable'] = 'Données de filtre non récupérables';
$string['filegenerationfailed'] = 'Le fichier n\'a pas pu être généré';
$string['filters'] = 'Filtres&nbsp;';
$string['friday'] = 'Vendredi';
$string['from'] = 'de';
$string['generalparams'] = 'Accéder à la définition du tableau de bord';
$string['generatedexports'] = 'Exports générés';
$string['globalcron'] = 'Paramètres globaux de cron';
$string['googlemap'] = 'Carte Google';
$string['googlemap'] = 'Google Map';
$string['googleparams'] = 'Paramètres GoogleMaps';
$string['graphparams'] = 'Configuration du graphe';
$string['hour'] = 'Heure';
$string['hours'] = 'heure';
$string['importconfig'] = 'Importer la configuration';
$string['instancecron'] = 'Paramètres d\'instance';
$string['invalidorobsoletefilterquery'] = 'Requête de filtre obsolete ou invalide.';
$string['invalidorobsoletequery'] = 'Requête obsolete ou invalide.';
$string['line'] = 'Lignes';
$string['linear'] = 'Linéaire';
$string['listvalue'] = 'Liste';
$string['log'] = 'Logarithmique';
$string['maptypehybrid'] = 'Vue combinée';
$string['maptyperoadmap'] = 'Carte routière';
$string['maptypesatellite'] = 'Vue satellite';
$string['maptypeterrain'] = 'Relief';
$string['mins'] = 'minute(s)';
$string['monday'] = 'Lundi';
$string['month'] = 'Mois';
$string['newdashboard'] = 'Nouveau tableau de bord';
$string['node'] = 'Noeud';
$string['nodata'] = 'Aucune donnée disponible.';
$string['nofiles'] = 'Pas de fichiers';
$string['noquerystored'] = 'Pas de requête enregistrée';
$string['norefresh'] = 'Pas de raffraichissement&nbsp;';
$string['notretrievable'] = 'Les données ne peuvent être affichées. Vous êtes probablement en mode édition et il n\'existe aucun cache de données disponible. Cette situation est forcée afin de prévenir une perte de contrôle sur le paramétrage du tableau de bord en cas de requête erronée.';
$string['obsoletequery'] = 'Cette requête semble être écrite pour Moodel 1.9 et est incompatible avec la base de donnée actuelle.';
$string['ofbiz_dashboard'] = 'Tableaux de bord Ofbiz';
$string['outputfilegeneration'] = 'Extraction de données';
$string['outputfiltered'] = 'Générer le fichier de sortie (filtré)';
$string['outputparams'] = 'Configuration des données de sorties';
$string['paramas'] = 'Le paramètre est ';
$string['paramasvar'] = 'une variable';
$string['paramassql'] = 'une clause where';
$string['paramascol'] = 'une colonne de sortie';
$string['pie'] = 'Camenberts';
$string['pluginname'] = 'Tableau de bord';
$string['publishinblock'] = 'Dans l\'espace du bloc';
$string['publishinpage'] = 'Dans une page séparée';
$string['querydesc'] = 'Définition de requête';
$string['queryparams'] = 'Paramètres utilisateur';
$string['rangevalue'] = 'Plage de valeurs';
$string['saturday'] = 'Samedi';
$string['savechangesandconfig'] = 'Enregistrer et continuer la configuration';
$string['savechangesandview'] = 'Enregistrer et afficher';
$string['securityparams'] = 'Paramètres de sécurité et de performance';
$string['selectnone'] = '(aucune valeur)';
$string['semicolon'] = 'Point-virgule';
$string['setup'] = 'Configuration';
$string['sqlinserts'] = 'Instructions SQL INSERT';
$string['sqlparamlabel'] = 'Libellé';
$string['sqlparamdefault'] = 'Valeur par défaut';
$string['sqlparamtype'] = 'Type';
$string['sqlparamvalues'] = 'Valeurs';
$string['sqlparamvar'] = 'Champ SQL';
$string['subtotal'] = 'Sous-total';
$string['sumsandfiltersparams'] = 'Sommes et filtres';
$string['sunday'] = 'Dimanche';
$string['tab'] = 'Tabulation';
$string['tablecolormapping'] = 'Colorisation des lignes de table';
$string['tabular'] = 'Table croisée';
$string['tabularparams'] = 'Paramètres supplémentaires affichage croisé';
$string['task_exportdata'] = 'Export automatisé de données';
$string['textvalue'] = 'Texte';
$string['thursday'] = 'Jeudi';
$string['timegraph'] = 'Courbes temporelle';
$string['timeline'] = 'Données temporelle';
$string['timelineparams'] = 'Paramètres de graphe temporel';
$string['to'] = 'à';
$string['toomanyrecordsusepaging'] = 'Cette requête produit trop de résultats pour ce mode d\'observation. La pagination des résultats est forcée pour limiter la taille des sorties';
$string['total'] = 'Total';
$string['treeview'] = 'Données hiérarchiques';
$string['treeviewparams'] = 'Paramètres supplémentaires données hiérarchiques';
$string['tuesday'] = 'Mardi';
$string['viewdashboard'] = 'Voir le tableau de bord';
$string['wednesday'] = 'Mercredi';
$string['week'] = 'Semaine';
$string['year'] = 'Année';
$string['sums'] = 'Sommateurs';

$string['configdelayedrefresh_help'] = '
Le raffraicchissement de données dans le cache et un fichier de sortie peut être programmé dans le temps.</p>
<p>Vous pouvez choisir de sortir les données selon le réglage général de la fonction cron des tableaux de bord, ou
choisir un autre moment particulier pour cette instance.
';

$string['configmakefile_help'] = '
Lorsque le cache est régénéré, vous pouvez choisir de sortir également les données vers un fichier.
';

$string['configfileoutput_help'] = '
La sortie sur fichier peut être alimentée par d\'autres champs que ceux utilisés pour l\'affichage. Si ce champ est vide, alors
les colonnes exportées seront les mêmes que celles définies pour l\'affichage.
';

$string['configfileheaders_help'] = '
Vous pouvez définir pour chaque champ la valeur de l\'en-tête produite en première ligne de fichier. Si ce champ est vide, alors
les alias SQL de colonnes de sorties sont utilisés par défaut comme en-têtes.
';

$string['configfilesqlouttable_help'] = '
<p>Dans le cas où vous choisissez de sortir des instructions SQL, vous devez mentionner le nom de la table d\'arrivée pour les insertions. </p>
';

$string['configfilepathadminoverride_help'] = 'Les administrateurs peuvent avoir besoin de générer les fichiers de sortie
dans des chemins non contrôllés par Moodle';

$string['configformatting_help'] = '
<p>Ces champs permettent de reformater les données en sorties, selon des masques compatibles
	avec la fonction "sprintf".</p>
<p>Pour une liste de champs de référence, la liste de format doit présenter un nombre identique
de formule de formatage séparées par des points-virgules. La formule vide vaut pour "aucun formatage".</p> 

<p><p>Exemple :</b></p>
<p>Pour une requête définissante un taux de fréquentation</p>
<pre>
</pre>
<p>On peut vouloir formatter la division (flottant) en nombre à un chiffre après la virgule.</p>
<p>Les champs "sortie de données" et "format des sorties", par exemple, seraient exprimés ainsi :
<pre>mois;ratio</pre>
<pre>;%.1f</pre>
';

$string['configpagesize_help'] = '
<h3>Taille de page de résultat</h3>
<p>Lorsque mentionné (non nul), force la pagination des résultats de table.</p>.
';

$string['configfilters_help'] = '
<p>Les données utiles pour l\'affichage des tables et des graphes peuvent être filtrées selon certaines colonnes.
Ce champ permet de définir les expressions qui servent de filtres. Ce champ accepte une liste à point-virgules pour
définir successivement plusieurs filtres à présenter dans l\'interface.</p>
<p>La définition d\'un filtre</p>
<ul>
    <li>Modifie dynamiquement la requête (voir ci-après les contraintes sur la requête) au moment de la production du bloc.</li>
    <li>Ajoute automatiquement les listes déroulantes de critères qui permettent à l\'utilisateur d\'opérer le filtrage</li>
</ul>

<h4>Définition des filtres</h4>

<p>Les filtres doivent être définis avec l\'expression ENTIERE de la colonne résultat</p>

<p>Exemple :</p>
<p>Pour la requête</p>

<pre>
    SELECT
       DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y\') as year,
       DATE_FORMAT(FROM_UNIXTIME(l.time), \'%m\') as month,
       count(l.id) as queries
    FROM
        mdl_log l
    GROUP BY
        year,month
</pre>

<p>La valeur du filtre devra mentionner :</p>
<pre>DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y\') as year</pre>

<p>Pour mettre en place deux filtres indépendants sur le mois et l\'année, on écrira :</p>
<pre>DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y\') as year<b>;</b>DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y-%m\') as month</pre>

<p>Attention : lors de l\'usage de deux filtres, chaque filtre fournit ses valeurs possibles indépendamment des autres.
Il est donc potentiellement possible que certaines combinaisons de filtrage ne produisent aucun résultat.</p>

<h4>Configuration de la requête</h4>

<p>L\'action des filtres nécessite qu\'un marqueur soit implanté dans la requête pour y inscrire l\'instruction
dynamique de filtrage. Ce marqueur doit apparaitre comme une balise "<%%FILTERS%%>" en remplacement ou en complément d\'une clause WHERE</p>
<p><u>Exemples de position valide :</u></p>
<pre>
    SELECT
      data1,data2
    FROM
       table1 t1,
       table2 t2
       <span style="color:green">&lt;%%FILTERS%%&gt;</span>
    ORDER BY
       data1
</pre>
<pre>
    SELECT
      data1,data2
    FROM
       table1 t1,
       table2 t2
    WHERE
        t1.id = t2.t1key
       <span style="color:green">&lt;%%FILTERS%%&gt;</span>
    ORDER BY
       data1
</pre>

<p><u>Exemples de position invalide :</u></p>
<pre>
    SELECT
      data1,data2
    FROM
       table1 t1,
       table2 t2
    GROUP BY
        data2
       <span style="color:red">&lt;%%FILTERS%%&gt;</span>
    ORDER BY
       data1
</pre>
';

$string['configfilterdefaults_help'] = '
<p>Il est possible d\'imposer une contrainte par défaut aux filtres définis dans le bloc.</p>
<p>Lorsqu\'une contrainte par défaut est définie, Le chargement du bloc est automatiquement filtré sur la valeur par défaut.</p>
<p>Cela est utile lorsque la requête sur la totalité du domaine de données est susceptible de fournir beaucoup de résultats.
Dans ce cas également on pourra utiliser avantageusement les options pour interdire l\'accès à la sortie complète.</p>

<p>Les listes de valeurs des filtres sont toujours fournies ordonnées sur le critère spécifié pour le filtre.</p>

<h4>Cas de plusieurs filtres</h4>

<p>Lorsque plusieurs filtres sont définis comme une liste séparée pard es ";", alors les valeurs par défaut doivent également faire aparaitre une liste
de valeurs séparées par des ";". Une valeur vide vaudra pour "pas de défaut".</p>

<h4>Valeurs spéciales</h4>

<p>Certaines valeurs spéciales permettent :</p>
<ul>
    <li>"LAST" : d\'obtenir par défaut la dernière valeur de la liste. (Appliqué à des dates, cela revient à "la page la plus récente").</li>
    <li>"FIRST" : d\'obtenir par défaut la première valeur de la liste. (Appliqué à des dates, cela revient à "la page la plus ancienne").</li>
</ul> 
';

$string['configfilteroptions_help'] = '
Certaines options complémentaires peuvent être appliquées aux filtres, sous forme de marqueurs dans une chaîne d\'options.

## Valeurs

* __m__ : (multiple) permet de choisir plusieurs valeurs de filtrage pour réaliser des "intervalles".
* __s_ : (single) Interdit l\'utilisation de la valeur "*" du filtre (plage complète). Le filtre est forcé
sur "FIRST" si aucun défaut n\'est défini. Un filtre en mode "s" exclut le précédent.
* __x__ : (crossthrough) Invalide le prétraitement de requête pour la recherche des modalités de filtre.
Ceci peut être activé sur certaines requêtes complexes (UNIONS, requêtes multi-imbriquées), mais le résultat
ne peut être garanti.
* __g__ : Filtre Global. si cette option est utilisée sur plusieurs blocs tableau de bord affichés sur la même page,
alors les valeurs de tous les filtres globaux portant la même définition de champ se synchronisent.

## Cas de plusieurs filtres

Lorsque plusieurs filtres sont définis comme une liste séparée pard es ";", alors les valeurs par défaut doivent
également faire aparaitre une liste de valeurs séparées par des ";". Une valeur vide vaudra pour "pas de défaut".

## Exemple

Soit les filtres :

    year;month;day

les options

    s;m;

permettront la sélection d\'une et une seule année d\'extraction, une sélection de mois, et n\'importe quel jour OU tous les jours.
';

$string['configgmdata_help'] = '
La prise en charge d\'affichage de données sur une carte GoogleMaps suppose la disponibilité d\'information
disposant de coordonnées de géolocalisaiton, ou de mentions d\'adresse transposable. Le bloc dashboard 
peut prendre en charge la transposition d\'adresses en coordonnées de localisation et mettra en cache
ces informations.

La conversion de données d\'adresse en coordonnées de géolocalisation est soumise aux conditions de service
    de l\'API de géocodage de Google. La conversion "gratuite" pour un utilisateur non enregistré ne
peut excéder 2500 appels par jour. Consulter la documentation sur
l\'<a href="http://code.google.com/intl/fr/apis/maps/documentation/geocoding/" target="_blank" >API de
géocodage de Google</a> pour plus d\'information.

## Paramétrage des données de carte

Les données géolocalisables sont implantées sur la carte comme des marqueurs. Ces marqueurs peuvent être définis :

* Comme un quadruplet : Titre, Latitude, Longitude, Type</li>
* Comme un sextuplet : Titre, Adresse, Code Postal, Ville, Région, Type</li>

Les valeurs attendues sont :

* Titre : Le label texte du marqueur
* Latitude : La latitude de géolocalisation (flottant)
* Longitude : La longitude de géolocalisation (flottant)
* Adresse : Les données de voie, numéro
* Code postal : Le code postal ou Zip code pour les pays anglosaxons
* Ville : La ville
* Type : Un label de type, permettant la sélection d\'une icone graphique particulière

## Configuration des champs

Les champs de paramétrage permettent de désigner les champs de sortie de requête qui fournissent ces informations.
Les champs de paramétrage prennent en général un ou plusieurs nom (ou alias) de colonne de résultat.

* Champs de titre : Le nom de la colonne de sortie fournissant le label texte
* Champs de localisation :
** __Cas 1__ : Le nom de LA colonne de sortie fournissant la paire de géolocation sous forme d\'une paire à virgule : "lat,lng"
** __Cas 2__ : la liste des noms de colonnes qui fournissent successivement (et dans l\'ordre) :
            l\'information d\'adresse, le code postal, la ville, et le code de région (*)
* Champs de type : Le nom de la colonne de sortie fournissant la classification des marqueurs

(*) Il est possible de donner des valeurs constantes ici, en encadrant la valeur de liste par des quotes :

    address;cp;city;\'FR\'

par exemple, fournira toujours le code de région à la valeur \'FR\'.
';

$string['configoutputfields_help'] = '
<p>Ce paramètre détermine quels champs du résultat de requête seront affichés dans la table résultat
(affichage des données).</p>
<p>Les chammps doivent être nommés selon les noms de sortie des colonnes (nom de champ ou nom d\'alias) et
ne peuvent être des expressions SQL. Les noms des champs sont séparés par des points-virgule (;).</p>

<p><b>Exemple :</b>
<p>Si la requête est : </p>
<pre>
SELECT
   YEAR(FROM_UNIXTIME(time)) as year,
   count(*) access
FROM
   mdl_log
GROUP BY
year
</pre>
<p> Alors les colonnes de sorties peuvent être :</p>
<pre>year;access</pre>

<h4>Fonctions spéciales sur les données de sorties</h4>

<p>Si une colonne de sortie est exprimée sous forme S(<i>nom_colonne</i>), alors les valeurs sont cumulées
dans l\'ordre d\'affichage de la sortie. </p>
';

$string['configquery_help'] = '
<p>Le tableau de bord appuie ses affichages sur une requête permettant d\'obtenir les données
source.</p>
<p>Cette requête :</p>
<ul>
    <li>doit présenter des champs de sortie nommés (si besoin par des clauses AS </li>
    <li>peut présenter des jointures</li>
    <li>peut utiliser des fonctions d\'aggrégation et des clauses GROUP BY</li>
    <li>ne doit pas présenter de clauses ORDER BY si les données sont destinées à un affichage en table de données</li>
</ul>

<h3>Introduction des filtres</h3>
<p>Si des filtres doivent être définis sur les données, alors il est nécessaire d\'insérer une balise <%%FILTERS%%>
dans la requête à la place ou pour compléter une clause WHERE</p>
<p>Notez qu\'il n\'est pas possible d\'utiliser des requêtes imbriquées.</p>
';

$string['configsplitsumonsort_help'] = '
<p>Lorsque les données sont triées, il est possible d\'obtenir des sous-totaux partiels sur un critère de séparation.
L\'ensemble des sommateurs déclarés sera traité et une ligne de sous-totaux apparaîtra à chaque changement de valeur.</p>
<p>Le critère de découpage est le nom d\'une colonne de sortie. La table de sortie doit être triable et triée effectivement
sur le critère de séparation pour que l\'affichage se produise.</p>
<p>Seules les tables linéaires de données peuvent bénéficier de cette fonctionnalité</p>
';

$string['configtabletype_help'] = '
<p>Ce sélecteur permet de choisir l\'un des types de tables suivants :</p>
<ul><li><b>Table linéaire</b> : Les données sont présentées par ligne de résultat</li>
<ul><li><b>Tableau croisé</b> : Les données sont présentées sous forme d\'un tableau à deux dimensions. La description de la table doit préciser:
    <ul><li>La colonne portant la dimension horizontale (un seul choix)</li>
        <li>La ou les colonnes portant la dimension verticale.</li>
        <li>La composition de la cellule contenu</li>
    </ul>
    </li>
    <li><b>Table hiérachique</b> : si les données extraites présentent un principe hiérachique (id,parent)
il est possible de les afficher dans une représentation arborescente.</li>
</ul>
';

$string['configparent_help'] = '
Désigner ici l\'alias de champ de la requête qui porte l\'information de hiérarchie. Souvent, on le nomme "parent".
';

$string['confighierarchic_help'] = '
Affichage hiérarchique
';

$string['configyseries_help'] = '
<p>Les séries de données sont les données graphées. Suivant le type de graphe il est possible de définir une ou
plusieurs séries à afficher. Les séries de données doivent êtres de noms de colonnes de sortie (ou leurs alias)
et doivent être séparées par des ";".</p>
<p>Les noms des séries peuvent être définies dans la zone de texte à droite (Titre des séries) sous forme
d\'une liste de mentions séparées par des ";".</p>

<h4>Fonctions spéciales sur les séries de sorties</h4>

<p>Si une série de données est exprimée sous forme S(<i>nom_colonne</i>), alors les valeurs sont cumulées
dans l\'ordre d\'affichage de la sortie. </p>
';

$string['configbigresult_help'] = '
<h3>Protection contre les résultats massifs</h3>
<p>Les requêtes d\'extraction peuvent avoir des conséquences dramatiques sur les performances, et notamment au moment
de la mise au point des jointures.
Pour éviter que l\'interface ne se retrouve dans une situation irrécupérable, une sécurité ajoute une pagination
forcée sur les résultats en mode édition.</p>
<p>Cette protection peut par contre poser des problèmes dans certains cas, par exemple lors de la production de
courbes graphiques qui nécessitent beaucoup de données.</p>

<p>Lorsqu\'elle est désactivée, des résultars massifs sont effectivement produits et remontés dans le bloc pour rendu.</p>
<p>Il est fortement conseillé d\'utiliser les caches de résultats pour ce type de requête.</p>
';

$string['configcaching_help'] = '
<h3>Caching</h3>
<p>Enabling cache will allow dashboard to fetch data in a pre-stored result in a cache table, thus
saving a lot of database power.</p>
<p>This is particularily useful on statistics results consuming a lot of records to produce a small
amount of output data. The cron automation will allow to shedule at appropriate time the refresh
of the cache, combined with cache TTL value.</p>
';

$string['configcolouring_help'] = '
<h3>Coloration de données dans les résultats</h3>
<p>La coloration des données résulat permet d\'attribuer ertaines couleurs à des valeurs partiulières
d\'une colonne de sortie de la table.</p>
<p>La one de texte de gauche permet de lister des couleurs, (une par ligne) par leur code HTML. Dans le champ central,
vous définirez le nom de la colonne de sortie à coloriser. A l\'heure actuelle, seul un champ
peut être traité ainsi.</p>
<p>La zone de texte de droite permet d\'associer à chaque couleur une "formule condition". Il s\'agit d\'une expression
arithmétique ou logique simple qui accepte le marqueur %% comme valeur courante de la donnée de cellule.</p>
<h4>Exemple</h4>

<pre><code>
    #FF0000                             %% < 0
    #00FF00                             %% > 0
    #0000FF     ->    outputcolumn  ->  %% == 0
</code></pre>

<p>coloriserait en rouge les valeurs négatives, en vert les positives et laisserait les zéros en bleu.</p>
<p>Notez que la couleur est appliquée au fond de cellule.</p>
 
';

$string['configtabular_help'] = '
<p>L\'affichage en table croisée affiche les données de sortie dans un tableau bisdimensionnel, en exploitant un champ de sortie
défini pour poduire les colonnes (horitonales) et un jeu de champ pour produire des intitulés de ligne multichamp.</p>
<p>La valeur de cellule est donnée par la définition des colonnes de sortie générales de table (ou un n-uplet) s\'il s\'agit d\'une liste).
';

$string['configspliton_help'] = '
<h3>Sous-résultats d\'un tableau</h3>

<p>Vous pouvez choisir un champ (nom d\'alias de colonne) pour segmenter une table en plusieurs sous-tables. Les données de la table
seront alors automatiquement ordonnées selon cette colonne, et une nouvelle table avec un rappel des titres de colonnes est démarrée
à chaque changement de valeur dans cette colonne de segmentation.</p>
';

$string['configsums_help'] = '
<p>Lorsqu\'elles sont activées, chaque rangée (resp. colonne) aura une colonne (resp. rangée) supplémentaire donnant la somme numérique
de la ligne (resp. colonne). Notez que si les cellules affichent un multiplet, seul le premier champ est sommé.
';

$string['configparentserie_help'] = '
<p>Il s\'agit de la colonne (alias) qui contient les informations de hiérarchie relatives à la clef naturelle de
l\'enregistrement. La clef naturelle est la valeur du premier champ de l\'enreigstrement.</p>
';

$string['configtreeoutput_help'] = '
<p>Définit la liste de colonnes (aliases) qui produisent l\'intitulé de noeud d\'arbre.</p>
';

$string['configzoom_help'] = '
<p>Zoom géographique. Permet de définir l\'éhelle géographique du fond de carte.</p>
';

$string['configlocation_help'] = '
<p>La localisation géographique centrale du fond de carte. Vous pouvez obtenir les coordonnées de
ce point directement sur l\'interface GoogleMaps.</p>
';

$string['configxaxisfield_help'] = '
<p>Le champ de sortie utilisé pour les donénes de l\'axe X. Ceci est particulièrement utile pour :</p>
<ul>
<li>Les graphes temporels</li>
<li>Les graphes en "barres" (catégoriels)</li>
</ul>
';

$string['configexplicitscaling_help'] = '
<p>Normalement, la production du graphe automatise le calibrage de l\'axe des Y en fonction des données à afficher.
Vous pouvez cependant imposer le calibrage des axes en définissant l\'un des paramètres min ou max. Le grapheur peut
néanmoins parfois prendre certaines décisions au delà de vos indications.</p>
';

$string['configfilelocation_help'] = '
<p>Il est possible d\'obtenir une génération de données à partir d\'un raffraichissement programmé (rafraichissement différé).
Ce paramètre permet de déterminer le chemin dans les fichiers du cours où le fichier sera généré.</p>
<p><b>Attention :</b> Ce chemin doit exister dans les fichiers du cours. Le générateur ne créera pas le répertoire s\'il n\'existe pas.</p>
';

$string['configfilelocationadmin_help'] = '
<p><b>Seul l\'administrateur peut renseigner ce champ.</b></p>
<p>Si ce champ est renseigné, son chemin s\'intercale à la place du chemin du cours courant. Ceci permet
    à l\'administrateur de générer les sortes dans des répertoires non directement accessibles par
    les environnements de cours.</p>
';

$string['confighorodatefiles_help'] = 'Si actif, chaque fichier généré sera complété par un horodatage';

$string['configqueryrotate_help'] = '
# Pivotement des données

Parce qu\'un résultat de requête SQL ne peut avoir un nombre de colonnes de sorties variables, ce mode d\'interrogation
de données n\'est pas approprié pour interroger des données matricielles. Le pivotement de données est une manière
de transformer une sortie d\'enregistrement "à plat" en une matrice dimensonnée dynamiquement. Cette technique est par
exemple utile lorque vous voulez alimenter plusieurs séries de courbes distinctes dans un graphe à partir des
résultats d\'une requête simple. Le pivotement utilise une colonne de "dimension" dite colonne "pivot" pour 
ranger les données des autres colonnes dans une grille conservant les autres dimensions, mais dont l\'autre dimension est 
formée par les valeurs distinctes du pivot.

Pour des exemples concrets d\'un pivot de données consulter la documentation en ligne <a href="http://www.mylearningfactory.com/"></a>

';

$string['configserieslabels_help'] = '
# Labels des séries Y

Lorsque vous affichez plusieurs séries de données dans un graphe (ou affichez un camembert ou un donut). Les séries
prennent par défaut le nom des valeurs de l\'axe des abscisses (X). vous pouvez utiliser ce champ pour fournir des
libellés alternatifs pour les valeurs de l\'axe X. La liste doit faire figurer les labels dans l\'ordre d\'arrivée
des valeurs d\'abscisses.
';

$string['tablecolormapping_help'] = '
Vous pouvez coloriser les résultats dans une colonne de résultat :
  - dans la zone de texte de gauche, en entrant une expression évaluante qui remplace la valeur de sortie par %% (f.e. %% == 0).
  - dans la zone de droite, le code couleur HTML de la colorisation (couleur de fond).
';

include(__DIR__.'/pro_additional_strings.php');
