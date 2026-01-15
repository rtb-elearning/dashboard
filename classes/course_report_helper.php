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
 * Helper class for generating course reports grouped by school code.
 *
 * @package    local_elby_dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_elby_dashboard;

defined('MOODLE_INTERNAL') || die();

use context_course;
use context_system;

/**
 * Helper class for course report data.
 */
class course_report_helper {

    /**
     * Get course report data for a specific course grouped by school code.
     *
     * @param int $courseid Course ID
     * @return array Course report data
     */
    public static function get_course_report(int $courseid): array {
        global $DB;

        $course = get_course($courseid);
        $context = context_course::instance($course->id);
        $modinfo = get_fast_modinfo($course);

        // Build section data.
        $sections = self::get_course_sections($modinfo, $courseid);

        // Get enrolled students with school codes.
        $students = self::get_enrolled_students($context, $courseid);

        // Group by school.
        $studentsBySchool = [];
        foreach ($students as $student) {
            $schoolcode = !empty($student->schoolcode) ? $student->schoolcode : 'UNKNOWN';
            if (!isset($studentsBySchool[$schoolcode])) {
                $studentsBySchool[$schoolcode] = [];
            }
            $studentsBySchool[$schoolcode][] = $student;
        }

        // Get completion and grade data.
        $completionData = self::get_completion_data($courseid);
        $gradeData = self::get_grade_data($courseid);

        // Build school reports.
        $schoolReports = [];
        foreach ($studentsBySchool as $schoolcode => $schoolStudents) {
            $studentIds = array_column($schoolStudents, 'id');

            $sectionStats = [];
            foreach ($sections as $sectionNum => $sectionData) {
                $completionRate = self::calculate_completion_rate(
                    $studentIds,
                    $sectionData['activities'],
                    $completionData
                );

                $averageGrade = self::calculate_average_grade(
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
                'school_name' => $schoolcode, // Could look up from registry.
                'student_count' => count($schoolStudents),
                'sections' => $sectionStats,
            ];
        }

        // Sort by student count descending.
        usort($schoolReports, function($a, $b) {
            return $b['student_count'] - $a['student_count'];
        });

        // Build overview sections.
        $overviewSections = [];
        $allStudentIds = array_column($students, 'id');
        foreach ($sections as $sectionNum => $sectionData) {
            $completionRate = self::calculate_completion_rate(
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
            'course_shortname' => $course->shortname,
            'total_enrolled' => count($students),
            'total_schools' => count($schoolReports),
            'overview_sections' => $overviewSections,
            'schools' => $schoolReports,
        ];
    }

    /**
     * Get list of all courses with basic info.
     *
     * @return array List of courses
     */
    public static function get_courses_list(): array {
        global $DB;

        $courses = $DB->get_records_select(
            'course',
            'id > 1',
            null,
            'fullname ASC',
            'id, shortname, fullname, category'
        );

        $result = [];
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            $enrolledcount = count_enrolled_users($context);

            $result[] = [
                'id' => $course->id,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'enrolled_count' => $enrolledcount,
            ];
        }

        return $result;
    }

    /**
     * Get course sections with activities and grade items.
     *
     * @param \course_modinfo $modinfo Course mod info
     * @param int $courseid Course ID
     * @return array Sections data
     */
    private static function get_course_sections(\course_modinfo $modinfo, int $courseid): array {
        global $DB;

        $sections = [];

        foreach ($modinfo->get_section_info_all() as $sectioninfo) {
            if ($sectioninfo->section == 0 || !$sectioninfo->visible) {
                continue;
            }

            $sectioncms = $modinfo->sections[$sectioninfo->section] ?? [];
            $activities = [];
            $gradeItems = [];

            foreach ($sectioncms as $cmid) {
                $cm = $modinfo->cms[$cmid] ?? null;
                if (!$cm || !$cm->visible) {
                    continue;
                }

                if ($cm->completion > 0) {
                    $activities[] = $cm->id;
                }

                $gradeitem = $DB->get_record('grade_items', [
                    'courseid' => $courseid,
                    'itemmodule' => $cm->modname,
                    'iteminstance' => $cm->instance,
                ]);

                if ($gradeitem) {
                    $gradeItems[] = $gradeitem->id;
                }
            }

            $sectionName = $sectioninfo->name ?: 'Unit ' . $sectioninfo->section;

            $sections[$sectioninfo->section] = [
                'name' => $sectionName,
                'activities' => $activities,
                'grade_items' => $gradeItems,
            ];
        }

        return $sections;
    }

    /**
     * Get enrolled students with school codes.
     *
     * @param \context $context Course context
     * @param int $courseid Course ID
     * @return array Student records
     */
    private static function get_enrolled_students(\context $context, int $courseid): array {
        global $DB;

        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
        if (!$studentroleid) {
            return [];
        }

        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.schoolcode
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {role_assignments} ra ON ra.userid = u.id
                WHERE e.courseid = :courseid
                  AND u.deleted = 0
                  AND ra.roleid = :roleid
                  AND ra.contextid = :contextid
                ORDER BY u.schoolcode, u.lastname";

        return $DB->get_records_sql($sql, [
            'courseid' => $courseid,
            'roleid' => $studentroleid,
            'contextid' => $context->id,
        ]);
    }

    /**
     * Get completion data for a course.
     *
     * @param int $courseid Course ID
     * @return array Completion data indexed by cmid then userid
     */
    private static function get_completion_data(int $courseid): array {
        global $DB;

        $sql = "SELECT cmc.id, cmc.coursemoduleid, cmc.userid, cmc.completionstate
                FROM {course_modules_completion} cmc
                JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
                WHERE cm.course = :courseid
                  AND cmc.completionstate >= 1";

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

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
     * Get grade data for a course.
     *
     * @param int $courseid Course ID
     * @return array Grade data indexed by itemid then userid
     */
    private static function get_grade_data(int $courseid): array {
        global $DB;

        $sql = "SELECT gg.id, gg.itemid, gg.userid, gg.finalgrade, gi.grademax
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gi.id = gg.itemid
                WHERE gi.courseid = :courseid
                  AND gg.finalgrade IS NOT NULL";

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        $data = [];
        foreach ($records as $record) {
            if (!isset($data[$record->itemid])) {
                $data[$record->itemid] = [];
            }
            $percentage = $record->grademax > 0
                ? ($record->finalgrade / $record->grademax) * 100
                : 0;
            $data[$record->itemid][$record->userid] = $percentage;
        }

        return $data;
    }

    /**
     * Calculate completion rate.
     *
     * @param array $studentIds Student IDs
     * @param array $activityIds Activity IDs
     * @param array $completionData Completion data
     * @return float Completion rate (0-100)
     */
    private static function calculate_completion_rate(
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
                if (isset($activityCompletions[$studentId])) {
                    $totalCompleted++;
                }
            }
        }

        return $totalPossible > 0
            ? round(($totalCompleted / $totalPossible) * 100, 1)
            : 0.0;
    }

    /**
     * Calculate average grade.
     *
     * @param array $studentIds Student IDs
     * @param array $gradeItemIds Grade item IDs
     * @param array $gradeData Grade data
     * @return float Average grade (0-100)
     */
    private static function calculate_average_grade(
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
}
