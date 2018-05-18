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
 * Moodle renderer used to display special elements of the learningtimecheck module
 *
 * @package   mod_Learningtimecheck
 * @category  mod
 * @copyright 2014 Valery Fremaux
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Rotates the result data around an axis to generate dynamic columns
 * @param object $config the block's config
 * @param array an array of data records out from a flat query
 */
function dashboard_rotate_result(&$theblock, $result) {

    // Get modalities.
    /*
     * Modalities are all distinct values of the rotated colums assembled into a single key.
     */
    $modalities = array();
    $rotatedarr = array();
    $modcolumns = explode(',', $theblock->config->queryrotatecols);
    $newkeycolumns = explode(',', $theblock->config->queryrotatenewkeys);
    $pivot = $theblock->config->queryrotatepivot;

    $resultpivot = $pivot;
    $iscumulative = false;
    if (preg_match('/S\((.+?)\)/', $pivot, $matches)) {
        $iscumulative = true;
        $resultpivot = $matches[1];
    }

    foreach ($result as $rid => $r) {
        $modparts = array();
        $keyparts = array();
        foreach ($r as $field => $value) {
            if (in_array($field, $modcolumns)) {
                $modparts[] = $value;
            }
            if (in_array($field, $newkeycolumns)) {
                $keyparts[] = $value;
            }
        }
        $modality = implode(' ', $modparts); // Modality aggregates rotated axis.
        $newkey = implode('_', $keyparts); // New key aggregates key column values.
        $modalities[$modality] = true; // Ensure unique.

        // Rotate pivot into modality column.
        $output[$newkey][$theblock->config->queryrotatenewkeys] = $newkey; // Confirm the new output primary key (may be composite).
        $output[$newkey][$modality] = $r->$resultpivot; // Store the pivot value rotated in modality.
    }
    $allmodalities = array_keys($modalities);

    // Process dynamic config to rotate labels and outputs.
    if (array_key_exists($pivot, $theblock->output)) {
        unset($theblock->output[$pivot]);
        foreach ($allmodalities as $m) {
            if ($iscumulative) {
                // Keep the cumulative marking.
                $mk = "S($m)";
            } else {
                $mk = $m;
            }
            $theblock->output[$mk] = $m;
        }
    }

    if (array_key_exists($pivot, $theblock->outputf)) {
        $pivotformat = $theblock->outputf[$pivot];
        unset($theblock->outputf[$pivot]);
        foreach ($allmodalities as $m) {
            $theblock->outputf[$mk] = $pivotformat;
        }
    }

    // Process dynamic config to rotate labels and outputs.
    if (!empty($theblock->yseries)) {
        // Do we have graph output ?
        if (array_key_exists($pivot, $theblock->yseries)) {
            unset($theblock->yseries[$pivot]);
            foreach ($allmodalities as $m) {
                if ($iscumulative) {
                    // Keep the cumulative marking.
                    $mk = "S($m)";
                } else {
                    $mk = $m;
                }
                $theblock->yseries[$mk] = $m;
            }
        }
    
        if (array_key_exists($pivot, $theblock->yseriesf)) {
            $pivotformat = $theblock->yseriesf[$pivot];
            unset($theblock->yseriesf[$pivot]);
            foreach ($allmodalities as $m) {
                $theblock->yseriesf[$mk] = $pivotformat;
            }
        }
    }

    // Reshape output to an array of pseudo records.
    if (!empty($output)) {
        foreach ($output as $id => $arr) {
            foreach ($allmodalities as $modal) {
                if (!isset($arr[$modal])) {
                    // Set an empty default value to fill the matrix empty holes.
                    $arr[$modal] = '';
                }
            }
            $objoutput[$id] = (object)$arr;
        }
        return $objoutput;
    }
    return array();
}