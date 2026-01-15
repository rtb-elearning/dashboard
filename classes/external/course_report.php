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
 * External API for course reports grouped by school code.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_elby_dashboard\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use context_course;
use context_system;

/**
 * External API for course reports grouped by school code.
 */
class course_report extends external_api {

    /**
     * Returns description of get_course_report_by_school parameters.
     *
     * @return external_function_parameters
     */
    public static function get_course_report_by_school_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    /**
     * Get course report data grouped by school code.
     *
     * Returns completion rates and average grades for each section (unit)
     * grouped by school code.
     *
     * @param int $courseid Course ID
     * @return array Course report data grouped by school
     */
    public static function get_course_report_by_school(int $courseid): array {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(
            self::get_course_report_by_school_parameters(),
            ['courseid' => $courseid]
        );
        $courseid = $params['courseid'];

        // Get course and validate it exists.
        $course = get_course($courseid);
        $context = context_course::instance($course->id);

        // Validate context.
        self::validate_context($context);

        // Check capability.
        require_capability('local/elby_dashboard:viewreports', $context);

        // Get course module info.
        $modinfo = get_fast_modinfo($course);

        // Build section data (units).
        $sections = self::get_course_sections($modinfo);

        // Get all enrolled students with their school codes.
        $students = self::get_enrolled_students_with_schools($context);

        // Group students by school code.
        $studentsBySchool = [];
        foreach ($students as $student) {
            $schoolcode = $student->schoolcode ?: 'UNKNOWN';
            if (!isset($studentsBySchool[$schoolcode])) {
                $studentsBySchool[$schoolcode] = [];
            }
            $studentsBySchool[$schoolcode][] = $student;
        }

        // Get completion and grade data for all students.
        $completionData = self::get_completion_data($courseid, array_keys($sections));
        $gradeData = self::get_grade_data($courseid, array_keys($sections));

        // Build report data per school.
        $schoolReports = [];
        $totalEnrolled = count($students);

        foreach ($studentsBySchool as $schoolcode => $schoolStudents) {
            $studentIds = array_column($schoolStudents, 'id');
            $studentCount = count($schoolStudents);

            // Calculate per-section stats for this school.
            $sectionStats = [];
            foreach ($sections as $sectionNum => $sectionData) {
                $sectionActivities = $sectionData['activities'];

                // Calculate completion rate for this section.
                $completionRate = self::calculate_section_completion_rate(
                    $studentIds,
                    $sectionActivities,
                    $completionData
                );

                // Calculate average grade for this section.
                $averageGrade = self::calculate_section_average_grade(
                    $studentIds,
                    $sectionData['grade_items'],
                    $gradeData
                );

                $sectionStats[] = [
                    'section_number' => $sectionNum,
                    'section_name' => $sectionData['name'],
                    'completion_rate' => $completionRate,
                    'average_grade' => $averageGrade,
                ];
            }

            $schoolReports[] = [
                'school_code' => $schoolcode,
                'school_name' => self::get_school_name($schoolcode),
                'student_count' => $studentCount,
                'sections' => $sectionStats,
            ];
        }

        // Sort by student count descending.
        usort($schoolReports, function($a, $b) {
            return $b['student_count'] - $a['student_count'];
        });

        // Calculate overview totals per section.
        $overviewSections = [];
        foreach ($sections as $sectionNum => $sectionData) {
            $sectionActivities = $sectionData['activities'];
            $allStudentIds = array_column($students, 'id');

            $completionRate = self::calculate_section_completion_rate(
                $allStudentIds,
                $sectionActivities,
                $completionData
            );

            $overviewSections[] = [
                'section_number' => $sectionNum,
                'section_name' => $sectionData['name'],
                'completion_rate' => $completionRate,
            ];
        }

        return [
            'courseid' => $courseid,
            'course_name' => $course->fullname,
            'total_enrolled' => $totalEnrolled,
            'total_schools' => count($schoolReports),
            'overview_sections' => $overviewSections,
            'schools' => $schoolReports,
        ];
    }

    /**
     * Get course sections with their activities and grade items.
     *
     * @param \course_modinfo $modinfo Course module info
     * @return array Sections data indexed by section number
     */
    private static function get_course_sections(\course_modinfo $modinfo): array {
        global $DB;

        $sections = [];
        $course = $modinfo->get_course();

        foreach ($modinfo->get_section_info_all() as $sectioninfo) {
            // Skip section 0 (general section).
            if ($sectioninfo->section == 0) {
                continue;
            }

            if (!$sectioninfo->visible) {
                continue;
            }

            // Get course modules in this section.
            $sectioncms = $modinfo->sections[$sectioninfo->section] ?? [];

            $activities = [];
            $gradeItems = [];

            foreach ($sectioncms as $cmid) {
                $cm = $modinfo->cms[$cmid] ?? null;
                if (!$cm || !$cm->visible) {
                    continue;
                }

                // Only track activities with completion enabled.
                if ($cm->completion > 0) {
                    $activities[] = $cm->id;
                }

                // Get grade item for this activity.
                $gradeitem = $DB->get_record('grade_items', [
                    'courseid' => $course->id,
                    'itemmodule' => $cm->modname,
                    'iteminstance' => $cm->instance,
                ]);

                if ($gradeitem) {
                    $gradeItems[] = $gradeitem->id;
                }
            }

            // Get section name (use "Unit X" if empty).
            $sectionName = $sectioninfo->name;
            if (empty($sectionName)) {
                $sectionName = 'Unit ' . $sectioninfo->section;
            }

            $sections[$sectioninfo->section] = [
                'name' => $sectionName,
                'activities' => $activities,
                'grade_items' => $gradeItems,
            ];
        }

        return $sections;
    }

    /**
     * Get enrolled students with their school codes.
     *
     * @param \context $context Course context
     * @return array Array of student objects with id, firstname, lastname, schoolcode
     */
    private static function get_enrolled_students_with_schools(\context $context): array {
        global $DB;

        // Get student role ID.
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
        if (!$studentroleid) {
            return [];
        }

        // Get enrolled users with student role.
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.schoolcode
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {role_assignments} ra ON ra.userid = u.id
                WHERE e.courseid = :courseid
                  AND u.deleted = 0
                  AND ra.roleid = :roleid
                  AND ra.contextid = :contextid
                ORDER BY u.schoolcode, u.lastname, u.firstname";

        $courseid = $context->instanceid;

        return $DB->get_records_sql($sql, [
            'courseid' => $courseid,
            'roleid' => $studentroleid,
            'contextid' => $context->id,
        ]);
    }

    /**
     * Get completion data for all activities in the course.
     *
     * @param int $courseid Course ID
     * @param array $sectionNums Section numbers
     * @return array Completion data indexed by cmid then userid
     */
    private static function get_completion_data(int $courseid, array $sectionNums): array {
        global $DB;

        // Get all completion records for this course.
        $sql = "SELECT cmc.id, cmc.coursemoduleid, cmc.userid, cmc.completionstate
                FROM {course_modules_completion} cmc
                JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
                WHERE cm.course = :courseid
                  AND cmc.completionstate >= 1";

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        // Index by cmid then userid.
        $data = [];
        foreach ($records as $record) {
            if (!isset($data[$record->coursemoduleid])) {
                $data[$record->coursemoduleid] = [];
            }
            $data[$record->coursemoduleid][$record->userid] = $record->completionstate;
        }

        return $data;
    }

    /**
     * Get grade data for all grade items in the course.
     *
     * @param int $courseid Course ID
     * @param array $sectionNums Section numbers
     * @return array Grade data indexed by itemid then userid
     */
    private static function get_grade_data(int $courseid, array $sectionNums): array {
        global $DB;

        // Get all grades for this course.
        $sql = "SELECT gg.id, gg.itemid, gg.userid, gg.finalgrade, gi.grademax
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gi.courseid = :courseid
                  AND gg.finalgrade IS NOT NULL";

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        // Index by itemid then userid.
        $data = [];
        foreach ($records as $record) {
            if (!isset($data[$record->itemid])) {
                $data[$record->itemid] = [];
            }
            // Store as percentage.
            $percentage = $record->grademax > 0
                ? ($record->finalgrade / $record->grademax) * 100
                : 0;
            $data[$record->itemid][$record->userid] = $percentage;
        }

        return $data;
    }

    /**
     * Calculate completion rate for a section for given students.
     *
     * @param array $studentIds Student IDs
     * @param array $activityIds Activity (course module) IDs
     * @param array $completionData Pre-fetched completion data
     * @return float Completion rate as percentage (0-100)
     */
    private static function calculate_section_completion_rate(
        array $studentIds,
        array $activityIds,
        array $completionData
    ): float {
        if (empty($studentIds) || empty($activityIds)) {
            return 0.0;
        }

        $totalPossible = count($studentIds) * count($activityIds);
        $totalCompleted = 0;

        foreach ($activityIds as $cmid) {
            $activityCompletions = $completionData[$cmid] ?? [];
            foreach ($studentIds as $studentId) {
                if (isset($activityCompletions[$studentId]) && $activityCompletions[$studentId] >= 1) {
                    $totalCompleted++;
                }
            }
        }

        return $totalPossible > 0
            ? round(($totalCompleted / $totalPossible) * 100, 1)
            : 0.0;
    }

    /**
     * Calculate average grade for a section for given students.
     *
     * @param array $studentIds Student IDs
     * @param array $gradeItemIds Grade item IDs
     * @param array $gradeData Pre-fetched grade data
     * @return float Average grade as percentage (0-100)
     */
    private static function calculate_section_average_grade(
        array $studentIds,
        array $gradeItemIds,
        array $gradeData
    ): float {
        if (empty($studentIds) || empty($gradeItemIds)) {
            return 0.0;
        }

        $totalGrades = 0;
        $gradeCount = 0;

        foreach ($gradeItemIds as $itemId) {
            $itemGrades = $gradeData[$itemId] ?? [];
            foreach ($studentIds as $studentId) {
                if (isset($itemGrades[$studentId])) {
                    $totalGrades += $itemGrades[$studentId];
                    $gradeCount++;
                }
            }
        }

        return $gradeCount > 0
            ? round($totalGrades / $gradeCount, 1)
            : 0.0;
    }

    /**
     * Get school name from school code.
     *
     * In the future, this could look up from a school registry table.
     * For now, returns the code itself.
     *
     * @param string $schoolcode School code
     * @return string School name
     */
    private static function get_school_name(string $schoolcode): string {
        // TODO: Implement school name lookup from registry.
        // For now, just return the school code.
        return $schoolcode;
    }

    /**
     * Returns description of get_course_report_by_school return value.
     *
     * @return external_single_structure
     */
    public static function get_course_report_by_school_returns(): external_single_structure {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'course_name' => new external_value(PARAM_TEXT, 'Course name'),
            'total_enrolled' => new external_value(PARAM_INT, 'Total enrolled students'),
            'total_schools' => new external_value(PARAM_INT, 'Total number of schools'),
            'overview_sections' => new external_multiple_structure(
                new external_single_structure([
                    'section_number' => new external_value(PARAM_INT, 'Section/Unit number'),
                    'section_name' => new external_value(PARAM_TEXT, 'Section/Unit name'),
                    'completion_rate' => new external_value(PARAM_FLOAT, 'Overall completion rate (0-100)'),
                ]),
                'Overview completion rates per section'
            ),
            'schools' => new external_multiple_structure(
                new external_single_structure([
                    'school_code' => new external_value(PARAM_TEXT, 'School code'),
                    'school_name' => new external_value(PARAM_TEXT, 'School name'),
                    'student_count' => new external_value(PARAM_INT, 'Number of students from this school'),
                    'sections' => new external_multiple_structure(
                        new external_single_structure([
                            'section_number' => new external_value(PARAM_INT, 'Section/Unit number'),
                            'section_name' => new external_value(PARAM_TEXT, 'Section/Unit name'),
                            'completion_rate' => new external_value(PARAM_FLOAT, 'Completion rate (0-100)'),
                            'average_grade' => new external_value(PARAM_FLOAT, 'Average grade (0-100)'),
                        ]),
                        'Per-section statistics'
                    ),
                ]),
                'Statistics grouped by school'
            ),
        ]);
    }

    /**
     * Returns description of get_all_courses_report parameters.
     *
     * @return external_function_parameters
     */
    public static function get_all_courses_report_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Get report data for all courses grouped by school code.
     *
     * @return array Report data for all courses
     */
    public static function get_all_courses_report(): array {
        global $DB;

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);

        // Check capability.
        require_capability('local/elby_dashboard:viewreports', $context);

        // Get all courses (exclude site course).
        $courses = $DB->get_records_select(
            'course',
            'id > 1',
            null,
            'fullname ASC',
            'id, shortname, fullname'
        );

        $coursesReport = [];
        foreach ($courses as $course) {
            try {
                $report = self::get_course_report_by_school_internal($course->id);
                if ($report['total_enrolled'] > 0) {
                    $coursesReport[] = $report;
                }
            } catch (\Exception $e) {
                // Skip courses that fail (e.g., permission issues).
                continue;
            }
        }

        return [
            'total_courses' => count($coursesReport),
            'courses' => $coursesReport,
        ];
    }

    /**
     * Internal method to get course report without validation.
     *
     * @param int $courseid Course ID
     * @return array Course report data
     */
    private static function get_course_report_by_school_internal(int $courseid): array {
        global $DB;

        $course = get_course($courseid);
        $context = context_course::instance($course->id);

        // Get course module info.
        $modinfo = get_fast_modinfo($course);

        // Build section data (units).
        $sections = self::get_course_sections($modinfo);

        // Get all enrolled students with their school codes.
        $students = self::get_enrolled_students_with_schools($context);

        // Group students by school code.
        $studentsBySchool = [];
        foreach ($students as $student) {
            $schoolcode = $student->schoolcode ?: 'UNKNOWN';
            if (!isset($studentsBySchool[$schoolcode])) {
                $studentsBySchool[$schoolcode] = [];
            }
            $studentsBySchool[$schoolcode][] = $student;
        }

        // Get completion and grade data.
        $completionData = self::get_completion_data($courseid, array_keys($sections));
        $gradeData = self::get_grade_data($courseid, array_keys($sections));

        // Build report data per school.
        $schoolReports = [];
        $totalEnrolled = count($students);

        foreach ($studentsBySchool as $schoolcode => $schoolStudents) {
            $studentIds = array_column($schoolStudents, 'id');
            $studentCount = count($schoolStudents);

            $sectionStats = [];
            foreach ($sections as $sectionNum => $sectionData) {
                $completionRate = self::calculate_section_completion_rate(
                    $studentIds,
                    $sectionData['activities'],
                    $completionData
                );

                $averageGrade = self::calculate_section_average_grade(
                    $studentIds,
                    $sectionData['grade_items'],
                    $gradeData
                );

                $sectionStats[] = [
                    'section_number' => $sectionNum,
                    'section_name' => $sectionData['name'],
                    'completion_rate' => $completionRate,
                    'average_grade' => $averageGrade,
                ];
            }

            $schoolReports[] = [
                'school_code' => $schoolcode,
                'school_name' => self::get_school_name($schoolcode),
                'student_count' => $studentCount,
                'sections' => $sectionStats,
            ];
        }

        // Sort by student count descending.
        usort($schoolReports, function($a, $b) {
            return $b['student_count'] - $a['student_count'];
        });

        // Calculate overview totals per section.
        $overviewSections = [];
        foreach ($sections as $sectionNum => $sectionData) {
            $allStudentIds = array_column($students, 'id');

            $completionRate = self::calculate_section_completion_rate(
                $allStudentIds,
                $sectionData['activities'],
                $completionData
            );

            $overviewSections[] = [
                'section_number' => $sectionNum,
                'section_name' => $sectionData['name'],
                'completion_rate' => $completionRate,
            ];
        }

        return [
            'courseid' => $courseid,
            'course_name' => $course->fullname,
            'total_enrolled' => $totalEnrolled,
            'total_schools' => count($schoolReports),
            'overview_sections' => $overviewSections,
            'schools' => $schoolReports,
        ];
    }

    /**
     * Returns description of get_all_courses_report return value.
     *
     * @return external_single_structure
     */
    public static function get_all_courses_report_returns(): external_single_structure {
        return new external_single_structure([
            'total_courses' => new external_value(PARAM_INT, 'Total number of courses'),
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'courseid' => new external_value(PARAM_INT, 'Course ID'),
                    'course_name' => new external_value(PARAM_TEXT, 'Course name'),
                    'total_enrolled' => new external_value(PARAM_INT, 'Total enrolled students'),
                    'total_schools' => new external_value(PARAM_INT, 'Total number of schools'),
                    'overview_sections' => new external_multiple_structure(
                        new external_single_structure([
                            'section_number' => new external_value(PARAM_INT, 'Section/Unit number'),
                            'section_name' => new external_value(PARAM_TEXT, 'Section/Unit name'),
                            'completion_rate' => new external_value(PARAM_FLOAT, 'Overall completion rate (0-100)'),
                        ]),
                        'Overview completion rates per section'
                    ),
                    'schools' => new external_multiple_structure(
                        new external_single_structure([
                            'school_code' => new external_value(PARAM_TEXT, 'School code'),
                            'school_name' => new external_value(PARAM_TEXT, 'School name'),
                            'student_count' => new external_value(PARAM_INT, 'Number of students'),
                            'sections' => new external_multiple_structure(
                                new external_single_structure([
                                    'section_number' => new external_value(PARAM_INT, 'Section number'),
                                    'section_name' => new external_value(PARAM_TEXT, 'Section name'),
                                    'completion_rate' => new external_value(PARAM_FLOAT, 'Completion rate'),
                                    'average_grade' => new external_value(PARAM_FLOAT, 'Average grade'),
                                ]),
                                'Per-section statistics'
                            ),
                        ]),
                        'Statistics grouped by school'
                    ),
                ]),
                'Course reports'
            ),
        ]);
    }
}
