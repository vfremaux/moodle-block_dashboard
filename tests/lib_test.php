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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/dashboard/lib.php');
require_once($CFG->dirroot.'/lib/phpunit/classes/basic_testcase.php');

class block_dashboard_lib_testcase extends basic_testcase {

    public function test_rotate() {

        $resultdata = array(
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2014-07', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2014-10', 'courses' => 2),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2015-01', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2015-04', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2015-05', 'courses' => 4),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2015-06', 'courses' => 2),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2015-09', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2015-10', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2015-12', 'courses' => 2),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2016-01', 'courses' => 2),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2016-09', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2016-10', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2016-11', 'courses' => 1),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2016-12', 'courses' => 2),
            array('domaincode' => 'C&D', 'domain' => 'COMMERCE & DISTRIBUTION', 'month' => '2017-01', 'courses' => 2),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2014-01', 'courses' => 14),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2014-03', 'courses' => 12),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2014-04', 'courses' => 1 ),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2014-07', 'courses' => 3 ),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2014-09', 'courses' => 5 ),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2015-06', 'courses' => 57),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2015-12', 'courses' => 1 ),
            array('domaincode' => 'LOG', 'domain' => 'LOGISTIQUE', 'month' => '2016-10', 'courses' => 2 ),
            array('domaincode' => 'SAP', 'domain' => 'SERVICES A LA PERSONNE', 'month' => '2014-07', 'courses' => 1),
            array('domaincode' => 'SAP', 'domain' => 'SERVICES A LA PERSONNE', 'month' => '2016-01', 'courses' => 1),
            array('domaincode' => 'SAP', 'domain' => 'SERVICES A LA PERSONNE', 'month' => '2016-09', 'courses' => 1),
            array('domaincode' => 'SAP', 'domain' => 'SERVICES A LA PERSONNE', 'month' => '2016-10', 'courses' => 2),
            array('domaincode' => 'SAP', 'domain' => 'SERVICES A LA PERSONNE', 'month' => '2016-11', 'courses' => 5),
            array('domaincode' => 'SAP', 'domain' => 'SERVICES A LA PERSONNE', 'month' => '2016-12', 'courses' => 1),
            array('domaincode' => 'SAP', 'domain' => 'SERVICES A LA PERSONNE', 'month' => '2017-01', 'courses' => 2),
            array('domaincode' => 'FCO', 'domain' => 'FONCTION COMMERCIALE', 'month' => '2014-07', 'courses' => 4),
            array('domaincode' => 'FCO', 'domain' => 'FONCTION COMMERCIALE', 'month' => '2015-03', 'courses' => 1),
            array('domaincode' => 'FCO', 'domain' => 'FONCTION COMMERCIALE', 'month' => '2016-09', 'courses' => 1),
            array('domaincode' => 'SAC', 'domain' => 'SECRETAIRE ASSISTANT et SECRETAIRE COMPTABLE', 'month' => '2014-10', 'courses' => 9),
            array('domaincode' => 'SAC', 'domain' => 'SECRETAIRE ASSISTANT et SECRETAIRE COMPTABLE', 'month' => '2015-03', 'courses' => 3),
            array('domaincode' => 'SAC', 'domain' => 'SECRETAIRE ASSISTANT et SECRETAIRE COMPTABLE', 'month' => '2016-06', 'courses' => 1),
            array('domaincode' => 'SAC', 'domain' => 'SECRETAIRE ASSISTANT et SECRETAIRE COMPTABLE', 'month' => '2016-09', 'courses' => 4),
            array('domaincode' => 'GES', 'domain' => 'GESTION ET COMPTABILITE', 'month' => '2014-07', 'courses' => 2),
            array('domaincode' => 'IFR', 'domain' => 'INFORMATIQUE ET RESEAUX', 'month' => '2016-09', 'courses' => 1),
            array('domaincode' => 'IFR', 'domain' => 'INFORMATIQUE ET RESEAUX', 'month' => '2016-10', 'courses' => 1),
            array('domaincode' => 'ROU', 'domain' => 'RESPONSABLE OPERATIONNEL D\'UNITE', 'month' => '2016-03', 'courses' => 38),
            array('domaincode' => 'ROU', 'domain' => 'RESPONSABLE OPERATIONNEL D\'UNITE', 'month' => '2016-09', 'courses' => 8 ),
            array('domaincode' => 'ROU', 'domain' => 'RESPONSABLE OPERATIONNEL D\'UNITE', 'month' => '2016-10', 'courses' => 3 ),
            array('domaincode' => 'ROU', 'domain' => 'RESPONSABLE OPERATIONNEL D\'UNITE', 'month' => '2016-12', 'courses' => 1 ),
            array('domaincode' => 'ROU', 'domain' => 'RESPONSABLE OPERATIONNEL D\'UNITE', 'month' => '2017-01', 'courses' => 3 ),
            array('domaincode' => 'OOE', 'domain' => 'OUTILS OPERATIONNELS D\'ENTREPRISE', 'month' => '2016-06', 'courses' => 1),
            array('domaincode' => 'MOO', 'domain' => 'FORMATION MOODLE','month' => '2016-09', 'courses' => 7),
            array('domaincode' => 'FRH', 'domain' => 'FILIERE RH', 'month' => '2016-09', 'courses' => 1),
            array('domaincode' => 'MOE', 'domain' => 'DIRIGEANT MANAGER OPERATIONNEL D\'ENTREPRISE', 'month' => '2016-09', 'courses' => 5),
            array('domaincode' => 'MOE', 'domain' => 'DIRIGEANT MANAGER OPERATIONNEL D\'ENTREPRISE', 'month' => '2016-11', 'courses' => 1),
            array('domaincode' => 'MQU', 'domain' => 'MANUEL QUALITE UTILISATEURS', 'month' => '2016-10', 'courses' => 1),
            array('domaincode' => 'MQU', 'domain' => 'MANUEL QUALITE UTILISATEURS', 'month' => '2016-11', 'courses' => 3),
            array('domaincode' => 'MQU', 'domain' => 'MANUEL QUALITE UTILISATEURS', 'month' => '2016-12', 'courses' => 1),
        );

        $result = array();
        foreach ($resultdata as $d) {
            $pkey = $d['domaincode'].'_'.$d['month'];
            $d['pkey'] = $pkey;
            $result[$pkey] = (object)$d;
        }

        $config = new StdClass();
        $config->queryrotatecols = 'domaincode';
        $config->queryrotatenewkeys = 'month';
        $config->queryrotatepivot = 'courses';

        $theblock = new StdClass;
        $theblock->config = $config;

        // Add members simulating output of configuration processing.
        $theblock->output = array('month' => 'Month', 'courses' => 'Course');
        $theblock->outputf = array('month' => '%s', 'courses' => '%d');

        print_r($result);

        $rotated = dashboard_rotate_result($theblock, $result);

        print_r($theblock);
    }

}