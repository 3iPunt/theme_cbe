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
 * Class course_navigation
 *
 * @package     theme_cbe
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_cbe\navigation;

use coding_exception;
use context_course;
use moodle_exception;
use moodle_url;
use stdClass;
use theme_cbe\course;
use theme_cbe\course_user;
use theme_cbe\output\course_header_navbar_component;
use theme_cbe\output\course_left_section_component;
use theme_cbe\output\menu_apps_button;

defined('MOODLE_INTERNAL') || die;

/**
 * Class course_navigation
 *
 * @package     theme_cbe
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_navigation extends navigation {

    const PAGE_BOARD = 'theme/cbe/view_board.php';
    const PAGE_THEMES = 'course/view.php';
    const PAGE_TASKS = 'theme/cbe/view_tasks.php';
    const PAGE_VCLASSES = 'theme/cbe/view_virtualclasses.php';
    const PAGE_MOREINFO = 'theme/cbe/view_moreinfo.php';
    const PAGE_RESOURCES = 'theme/cbe/view_resources.php';

    /** @var array Templates Header */
    protected $templates_header = [
        'board' => 'theme_cbe/header/board',
        'themes' => 'theme_cbe/header/themes',
        'tasks' => 'theme_cbe/header/custom',
        'vclasses' => 'theme_cbe/header/custom',
        'resources' => 'theme_cbe/header/custom',
        'moreinfo' => 'theme_cbe/header/custom',
        'modedit' => 'theme_cbe/header/custom',
        'generic' => 'theme_cbe/header/generic',
        'default' => 'theme_cbe/header/header',
    ];

    /**
     * constructor.
     */
    public function __construct() {
    }

    /**
     * Get Template Layout.
     *
     * @return string
     */
    public function get_template_layout(): string {
        return 'theme_cbe/columns2/columns2_course';
    }

    /**
     * Get Page.
     *
     * @return string
     */
    protected function get_page(): string {
        global $PAGE;
        $path = $PAGE->url->get_path();
        if (strpos($path, self::PAGE_BOARD)) {
            return 'board';
        } else if (strpos($path, self::PAGE_THEMES)) {
            return 'themes';
        } else if (strpos($path, self::PAGE_TASKS)) {
            return 'tasks';
        } else if (strpos($path, self::PAGE_RESOURCES)) {
            return 'resources';
        } else if (strpos($path, self::PAGE_VCLASSES)) {
            return 'vclasses';
        } else if (strpos($path, self::PAGE_MOREINFO)) {
            return 'moreinfo';
        } else if (strpos($path, 'course/modedit')) {
            return 'modedit';
        } else if (strpos($path, 'grade') ||
            strpos($path, 'user') ||
            strpos($path, 'calendar') ||
            strpos($path, 'contentbank') ||
            strpos($path, 'course/edit'
            )) {
            return 'generic';
        } else {
            return '';
        }
    }

    /**
     * Get Data Header.
     *
     * @param stdClass $data
     * @return stdClass
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_data_header(stdClass $data): stdClass {
        global $PAGE;
        $courseid = $PAGE->context->instanceid;
        $coursecbe = new course($courseid);
        $nav_context = 'course';
        $courseimage = $coursecbe->get_courseimage();
        $teachers = $coursecbe->get_users_by_role('editingteacher');
        $coursename = $coursecbe->get_name();
        $coursecategory = $coursecbe->get_category();
        $is_teacher = course_user::is_teacher($courseid);
        $data->nav_context = $nav_context;
        $data->courseimage = $courseimage;
        $data->teachers = $teachers;
        $data->is_teacher = $is_teacher;
        $data->coursename = $coursename;
        $data->categoryname = $coursecategory;
        $data->edit_course= new moodle_url('/course/edit.php', ['id'=> $courseid]);
        return $data;
    }

    /**
     * Get Data Layout.
     *
     * @param array $data
     * @return array
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_data_layout(array $data): array {
        global $PAGE;
        $output_theme_cbe = $PAGE->get_renderer('theme_cbe');
        $course_id = $PAGE->context->instanceid;
        $nav_header_course_component = new course_header_navbar_component($course_id);
        $nav_header_course = $output_theme_cbe->render($nav_header_course_component);
        $course_left_menu_component = new course_left_section_component($course_id);
        $course_left_menu = $output_theme_cbe->render($course_left_menu_component);
        $menu_apps_button_component = new menu_apps_button();
        $menu_apps_button = $output_theme_cbe->render($menu_apps_button_component);

        $cbe_page = course_navigation::get_page();
        if ($cbe_page === 'board' ||
            $cbe_page === 'themes' ||
            $cbe_page === 'moreinfo' ||
            $cbe_page === 'modedit'  ||
            $cbe_page === 'module') {
            $is_course_blocks = true;
        } else {
            $is_course_blocks = false;
        }

        $data['in_course'] = true;
        $data['course_left_menu'] = $course_left_menu;
        $data['navbar_header_course'] =  $nav_header_course;
        $data['is_course_blocks'] = $is_course_blocks;
        $data['is_teacher'] = course_user::is_teacher($course_id);
        $data['menu_apps_button'] = $menu_apps_button;
        $data['nav_context'] = 'course';
        $data['nav_cbe'] =  $cbe_page;

        return $data;
    }

    /**
     * Is Current Page.
     *
     * @param string $page
     * @return bool
     */
    static function is_current_page(string $page): bool {
        global $PAGE;
        $path = $PAGE->url->get_path();
        return strpos($path, $page);
    }

    /**
     * Left Section
     *
     * @param int $course_id
     * @return array
     * @throws coding_exception
     */
    static function left_section(int $course_id): array {
        global $PAGE;
        $path = $PAGE->url->get_path();
        if (strpos($path, self::PAGE_BOARD)) {
            return self::left_section_board($course_id);
        } else if (strpos($path, self::PAGE_THEMES)) {
            return self::left_section_themes($course_id);
        } else if (strpos($path, self::PAGE_TASKS)) {
            return [];
        } else if (strpos($path, self::PAGE_VCLASSES)) {
            return [];
        } else if (strpos($path, self::PAGE_MOREINFO)) {
            return self::left_section_themes($course_id);
        } else if (strpos($path, 'course/modedit')) {
            return self::left_section_themes($course_id);
        } else if (strpos($path, 'grade')) {
            return [];
        } else {
            return [];
        }
    }

}
