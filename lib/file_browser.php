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
 * Utility class for browsing of files.
 *
 * @package   core_files
 * @copyright 2008 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filebrowser/file_info.php');

// General area types.
require_once($CFG->libdir.'/filebrowser/file_info_stored.php');
require_once($CFG->libdir.'/filebrowser/virtual_root_file.php');

// Description of available areas in each context level.
require_once($CFG->libdir.'/filebrowser/file_browser.php');
require_once($CFG->libdir.'/filebrowser/file_info_context_system.php');
require_once($CFG->libdir.'/filebrowser/file_info_context_user.php');
require_once($CFG->libdir.'/filebrowser/file_info_context_coursecat.php');
require_once($CFG->libdir.'/filebrowser/file_info_context_course.php');
require_once($CFG->libdir.'/filebrowser/file_info_context_module.php');

require_once($CFG->dirroot.'/blocks/dashboard/lib/file_info_context_block.php');

/**
 * This class provides the main entry point for other code wishing to get information about files.
 *
 * The whole file storage for a Moodle site can be seen as a huge virtual tree.
 * The spine of the tree is the tree of contexts (system, course-categories,
 * courses, modules, also users). Then, within each context, there may be any number of
 * file areas, and a file area contains folders and files. The various file_info
 * subclasses return info about the things in this tree. They should be obtained
 * from an instance of this class.
 *
 * This virtual tree is different for each user depending of his/her current permissions.
 * Some branches such as draft areas are hidden, but accessible.
 *
 * Always use this abstraction when you need to access module files from core code.
  *
 * @package   core_files
 * @category  files
 * @copyright 2008 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class dashboard_file_browser extends file_browser {

    /**
     * Looks up file_info instance
     *
     * @param stdClass $context context object
     * @param string $component component
     * @param string $filearea file area
     * @param int $itemid item ID
     * @param string $filepath file path
     * @param string $filename file name
     * @return file_info|null file_info instance or null if not found or access not allowed
     */
    public function get_file_info($context = null, $component = null, $filearea = null, $itemid = null, $filepath = null, $filename = null) {
        if (!$context) {
            $context = context_system::instance();
        }
        switch ($context->contextlevel) {
            case CONTEXT_SYSTEM:
                return $this->get_file_info_context_system($context, $component, $filearea, $itemid, $filepath, $filename);

            case CONTEXT_USER:
                return $this->get_file_info_context_user($context, $component, $filearea, $itemid, $filepath, $filename);

            case CONTEXT_COURSECAT:
                return $this->get_file_info_context_coursecat($context, $component, $filearea, $itemid, $filepath, $filename);

            case CONTEXT_COURSE:
                return $this->get_file_info_context_course($context, $component, $filearea, $itemid, $filepath, $filename);

            case CONTEXT_MODULE:
                return $this->get_file_info_context_module($context, $component, $filearea, $itemid, $filepath, $filename);

            case CONTEXT_BLOCK:
                return $this->get_file_info_context_block($context, $component, $filearea, $itemid, $filepath, $filename);
        }

        return null;
    }

    /**
     * Returns info about the files at block context
     *
     * @param stdClass $context context object
     * @param string $component component
     * @param string $filearea file area
     * @param int $itemid item ID
     * @param string $filepath file path
     * @param string $filename file name
     * @return file_info|null file_info instance or null if not found or access not allowed
     */
    private function get_file_info_context_block($context, $component, $filearea, $itemid, $filepath, $filename) {
        global $COURSE, $DB, $CFG;

        static $cachedmodules = array();

        $blockrec = $DB->get_record('block_instances', array('id' => $context->instanceid));
        $blockname = $blockrec->blockname;

        if (!array_key_exists($context->instanceid, $cachedmodules)) {
            $cachedmodules[$context->instanceid] = block_instance($blockrec->blockname, $blockrec);
        }

        if (!($blockinstance = $cachedmodules[$context->instanceid])) {
            throw(new moodle_exception('Failed finding block'));
        }

        $coursecontext = $DB->get_record('context', array('id' => $blockrec->parentcontextid));
        if ($coursecontext->instanceid == $COURSE->id) {
            $course = $COURSE;
        }

        if (!file_exists($CFG->dirroot.'/blocks/'.$blockname.'/lib.php')) {
            throw(new moodle_exception('No bloc library available'));
        }

        // Ok, we know that block exists, and user may access it.
        $level = new file_info_context_block($this, $context, $course, $blockinstance, $blockname);
        return $level->get_file_info($component, $filearea, $itemid, $filepath, $filename);
    }
}
