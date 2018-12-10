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
 * @subpackage cli
 * @copyright  2008 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
$CLI_VMOODLE_PRECHECK = true; // Force first config to be minimal.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

require_once($CFG->dirroot.'/lib/clilib.php'); // Cli only functions.

list($options, $unrecognized) = cli_get_params(
    array('help' => false,
          'host' => false,
          'dashboard' => false,
          'force' => false,
          'debug' => false,
          'verbose' => false,
    ),
    array('h' => 'help',
          'H' => 'host',
          'D' => 'dashboard',
          'f' => 'force',
          'd' => 'debug',
          'v' => 'verbose',
    )
);
if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error("Not recognized options ".$unrecognized);
}

if ($options['help']) {
    $help = "
Exports a dashboard to its defined output file and location

Options:
    -h, --help            Print out this help.
    -H, --host            the virtual host you are working for.
    -D, --dashboard       the dashboard block ID.
    -f, --force           Forces generation whatever schedule time.
    -d, --debug           Turn on debug mode.
    -v, --verbose         Turns on dashtrace.

Example (from moodle root):
\$sudo -u www-data /usr/bin/php blocks/dashboard/cli/export_dashboard.php
";

    echo $help;
    exit(0);
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // Mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.
if (!$CLI_VMOODLE_PRECHECK) {
    /*
     * If was set to false, vmoodle snippet was intalled in the config file. Otherwise the first config
     * call was complete.
     */
    require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
}
require_once($CFG->dirroot.'/blocks/dashboard/block_dashboard.php');
echo('Config check : playing for '.$CFG->wwwroot."\n");

if (empty($options['dashboard'])) {
    die("Dashboard block id was not provided\n");
}

if (!empty($options['debug'])) {
    $CFG->debug = E_ALL;
}

$dsh = $DB->get_record('block_instances', array('id' => $options['dashboard'], 'blockname' => 'dashboard'));
if (!$dsh) {
    die("Dashboard block with id {$options['dashboard']} was not found in database\n");
}

$dashtrace = "[".strftime('%Y-%m-%d %H:%M:%S', time())."] Processing dashboards\n";
block_dashboard::export_dashboard($dsh, $config, $dashtrace, $options);
if ($options['verbose']) {
    if ($DASHTRACE = fopen($CFG->dataroot.'/dashboards_cli.log', 'a')) {
        fputs($DASHTRACE, $dashtrace);
        fclose($DASHTRACE);
    }
}

echo "Done.\n";
exit(0);