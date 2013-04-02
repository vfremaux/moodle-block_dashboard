<?php  //$Id: upgrade.php,v 1.4 2012-09-19 18:52:56 vf Exp $

// This file keeps track of upgrades to 
// the vmoodle block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_dashboard_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

    if ($result && $oldversion < 2011101102) {

    /// Define table dashboard_geo_cache to be created
        $table = new XMLDBTable('block_dashboard_geo_cache');

    /// Adding fields to table dashboard_geo_cache
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('address', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('regioncode', XMLDB_TYPE_CHAR, '16', null, null, null, null, null, null);
        $table->addFieldInfo('latlng', XMLDB_TYPE_CHAR, '20', null, null, null, null, null, null);

    /// Adding keys to table dashboard_geo_cache
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for dashboard_geo_cache
        $result = $result && create_table($table);
    }

    if ($result && $oldversion < 2012091800) {
	    if ($table = new XMLDBTable('dashboard_cache')){
			rename_table ($table, 'block_dashboard_cache');
		}
	    if ($table = new XMLDBTable('dashboard_cache_data')){
			rename_table ($table, 'block_dashboard_cache_data');
		}
	    if ($table = new XMLDBTable('dashboard_geo_cache')){
			rename_table ($table, 'block_dashboard_geo_cache');
		}
	}
    
    return $result;
}