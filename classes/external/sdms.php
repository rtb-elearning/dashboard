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
 * External API for SDMS integration.
 *
 * Provides web service endpoints for SDMS data access with
 * cache-first, sync-on-miss pattern.
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
use context_system;

/**
 * External API for SDMS integration.
 */
class sdms extends external_api {

    // =========================================================================
    // get_user_sdms_profile — Cache-first read of user's SDMS data.
    // =========================================================================

    /**
     * Parameters for get_user_sdms_profile.
     */
    public static function get_user_sdms_profile_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'Moodle user ID'),
        ]);
    }

    /**
     * Get a user's SDMS profile from local cache. Triggers refresh if stale.
     *
     * @param int $userid Moodle user ID.
     * @return array User SDMS profile data.
     */
    public static function get_user_sdms_profile(int $userid): array {
        global $DB;

        $params = self::validate_parameters(
            self::get_user_sdms_profile_parameters(),
            ['userid' => $userid]
        );
        $userid = $params['userid'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:view', $context);

        // Check cache.
        $cached = $DB->get_record('elby_sdms_users', ['userid' => $userid]);

        // If stale, try to refresh.
        if ($cached && self::is_stale($cached->last_synced)) {
            try {
                $syncservice = new \local_elby_dashboard\sync_service();
                $syncservice->refresh_user($userid, false);
                $cached = $DB->get_record('elby_sdms_users', ['userid' => $userid]);
            } catch (\Exception $e) {
                // Use stale cache rather than failing.
            }
        }

        if (!$cached) {
            return [
                'success' => false,
                'error' => 'User not linked to SDMS',
                'sdms_id' => '',
                'user_type' => '',
                'school_code' => '',
                'school_name' => '',
                'academic_year' => '',
                'sdms_status' => '',
                'program' => '',
                'position' => '',
                'sync_status' => 0,
                'last_synced' => 0,
            ];
        }

        // Resolve school info.
        $schoolcode = '';
        $schoolname = '';
        if ($cached->schoolid) {
            $school = $DB->get_record('elby_schools', ['id' => $cached->schoolid],
                'school_code, school_name');
            if ($school) {
                $schoolcode = $school->school_code;
                $schoolname = $school->school_name;
            }
        }

        // Fetch type-specific data.
        $program = '';
        $position = '';
        if ($cached->user_type === 'student') {
            $student = $DB->get_record('elby_students', ['sdms_userid' => $cached->id], 'program');
            $program = $student ? ($student->program ?? '') : '';
        } else {
            $teacher = $DB->get_record('elby_teachers', ['sdms_userid' => $cached->id], 'position');
            $position = $teacher ? ($teacher->position ?? '') : '';
        }

        return [
            'success' => true,
            'error' => '',
            'sdms_id' => $cached->sdms_id,
            'user_type' => $cached->user_type,
            'school_code' => $schoolcode,
            'school_name' => $schoolname,
            'academic_year' => $cached->academic_year ?? '',
            'sdms_status' => $cached->sdms_status ?? '',
            'program' => $program,
            'position' => $position,
            'sync_status' => (int) $cached->sync_status,
            'last_synced' => (int) $cached->last_synced,
        ];
    }

    /**
     * Return structure for get_user_sdms_profile.
     */
    public static function get_user_sdms_profile_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether data was found'),
            'error' => new external_value(PARAM_TEXT, 'Error message if failed'),
            'sdms_id' => new external_value(PARAM_TEXT, 'SDMS identifier'),
            'user_type' => new external_value(PARAM_TEXT, 'User type (student/staff)'),
            'school_code' => new external_value(PARAM_TEXT, 'School code'),
            'school_name' => new external_value(PARAM_TEXT, 'School name'),
            'academic_year' => new external_value(PARAM_TEXT, 'Academic year'),
            'sdms_status' => new external_value(PARAM_TEXT, 'SDMS status'),
            'program' => new external_value(PARAM_TEXT, 'Program name (students)'),
            'position' => new external_value(PARAM_TEXT, 'Position (staff)'),
            'sync_status' => new external_value(PARAM_INT, '1=synced, 0=error'),
            'last_synced' => new external_value(PARAM_INT, 'Unix timestamp of last sync'),
        ]);
    }

    // =========================================================================
    // get_school_info — Cache-first read of school data with hierarchy.
    // =========================================================================

    /**
     * Parameters for get_school_info.
     */
    public static function get_school_info_parameters(): external_function_parameters {
        return new external_function_parameters([
            'school_code' => new external_value(PARAM_TEXT, 'SDMS school code'),
        ]);
    }

    /**
     * Get school info with hierarchy from cache. Triggers sync on miss/stale.
     *
     * @param string $schoolcode SDMS school code.
     * @return array School data with nested hierarchy.
     */
    public static function get_school_info(string $schoolcode): array {
        global $DB;

        $params = self::validate_parameters(
            self::get_school_info_parameters(),
            ['school_code' => $schoolcode]
        );
        $schoolcode = $params['school_code'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:viewreports', $context);

        // Cache-first: check local, sync on miss/stale.
        $school = $DB->get_record('elby_schools', ['school_code' => $schoolcode]);

        if (!$school || self::is_stale($school->last_synced)) {
            try {
                $syncservice = new \local_elby_dashboard\sync_service();
                $syncservice->sync_school($schoolcode, false);
                $school = $DB->get_record('elby_schools', ['school_code' => $schoolcode]);
            } catch (\Exception $e) {
                if (!$school) {
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'school_code' => $schoolcode,
                        'school_name' => '',
                        'region_code' => '',
                        'is_active' => 0,
                        'has_tvet' => 0,
                        'academic_year' => '',
                        'levels' => [],
                        'last_synced' => 0,
                    ];
                }
                // Use stale cache.
            }
        }

        if (!$school) {
            return [
                'success' => false,
                'error' => 'School not found',
                'school_code' => $schoolcode,
                'school_name' => '',
                'region_code' => '',
                'is_active' => 0,
                'has_tvet' => 0,
                'academic_year' => '',
                'levels' => [],
                'last_synced' => 0,
            ];
        }

        // Build hierarchy.
        $levels = self::build_school_hierarchy($school->id);

        return [
            'success' => true,
            'error' => '',
            'school_code' => $school->school_code,
            'school_name' => $school->school_name,
            'region_code' => $school->region_code ?? '',
            'is_active' => (int) $school->is_active,
            'has_tvet' => (int) $school->has_tvet,
            'academic_year' => $school->academic_year ?? '',
            'levels' => $levels,
            'last_synced' => (int) $school->last_synced,
        ];
    }

    /**
     * Return structure for get_school_info.
     */
    public static function get_school_info_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'error' => new external_value(PARAM_TEXT, 'Error message'),
            'school_code' => new external_value(PARAM_TEXT, 'School code'),
            'school_name' => new external_value(PARAM_TEXT, 'School name'),
            'region_code' => new external_value(PARAM_TEXT, 'Region code'),
            'is_active' => new external_value(PARAM_INT, '1 if active'),
            'has_tvet' => new external_value(PARAM_INT, '1 if has TVET level'),
            'academic_year' => new external_value(PARAM_TEXT, 'Academic year'),
            'levels' => new external_multiple_structure(
                new external_single_structure([
                    'level_id' => new external_value(PARAM_TEXT, 'SDMS level ID'),
                    'level_name' => new external_value(PARAM_TEXT, 'Level name'),
                    'level_desc' => new external_value(PARAM_TEXT, 'Description'),
                    'combinations' => new external_multiple_structure(
                        new external_single_structure([
                            'combination_code' => new external_value(PARAM_TEXT, 'Code'),
                            'combination_name' => new external_value(PARAM_TEXT, 'Name'),
                            'combination_desc' => new external_value(PARAM_TEXT, 'Description'),
                            'grades' => new external_multiple_structure(
                                new external_single_structure([
                                    'grade_code' => new external_value(PARAM_TEXT, 'Grade code'),
                                    'grade_name' => new external_value(PARAM_TEXT, 'Grade name'),
                                    'classgroups' => new external_multiple_structure(
                                        new external_single_structure([
                                            'class_id' => new external_value(PARAM_TEXT, 'Class ID'),
                                            'class_name' => new external_value(PARAM_TEXT, 'Class name'),
                                        ])
                                    ),
                                ])
                            ),
                        ])
                    ),
                ])
            ),
            'last_synced' => new external_value(PARAM_INT, 'Last sync timestamp'),
        ]);
    }

    // =========================================================================
    // lookup_sdms_user — Live SDMS lookup, no caching.
    // =========================================================================

    /**
     * Parameters for lookup_sdms_user.
     */
    public static function lookup_sdms_user_parameters(): external_function_parameters {
        return new external_function_parameters([
            'sdms_code' => new external_value(PARAM_TEXT, 'SDMS code (studentNumber or staffNumber)'),
            'user_type' => new external_value(PARAM_TEXT, 'User type: student or staff'),
        ]);
    }

    /**
     * Live lookup of SDMS user data. Does NOT cache. Also fetches school hierarchy.
     *
     * @param string $sdmscode SDMS identifier.
     * @param string $usertype "student" or "staff".
     * @return array Raw SDMS data with school hierarchy.
     */
    public static function lookup_sdms_user(string $sdmscode, string $usertype): array {
        $params = self::validate_parameters(
            self::lookup_sdms_user_parameters(),
            ['sdms_code' => $sdmscode, 'user_type' => $usertype]
        );
        $sdmscode = $params['sdms_code'];
        $usertype = $params['user_type'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:manage', $context);

        try {
            $client = new \local_elby_dashboard\sdms_client();

            // Fetch user.
            if ($usertype === 'student') {
                $data = $client->get_student($sdmscode);
            } else {
                $data = $client->get_staff($sdmscode);
            }

            if ($data === null) {
                return [
                    'success' => false,
                    'error' => 'Not found in SDMS',
                    'sdms_id' => $sdmscode,
                    'user_type' => $usertype,
                    'status' => '',
                    'academic_year' => '',
                    'school' => null,
                    'student_data' => null,
                    'staff_data' => null,
                ];
            }

            // Extract school code.
            $schoolcode = ($usertype === 'student')
                ? ($data->schoolCode ?? '')
                : ($data->schooCode ?? $data->schoolCode ?? '');

            // Fetch school (live, no caching).
            $school = null;
            if (!empty($schoolcode)) {
                $schooldata = $client->get_school($schoolcode);
                if ($schooldata) {
                    $school = self::format_school_data($schooldata);
                }
            }

            // Format type-specific data.
            $studentdata = null;
            $staffdata = null;

            if ($usertype === 'student') {
                $studentdata = [
                    'program' => $data->combination ?? '',
                    'program_code' => $data->combinationCode ?? '',
                    'registration_date' => $data->registrationDate ?? '',
                    'classgroup_id' => $data->classGroupId ?? '',
                ];
            } else {
                $specialities = [];
                if (!empty($data->specialities) && is_array($data->specialities)) {
                    foreach ($data->specialities as $spec) {
                        $specialities[] = [
                            'level_name' => $spec->levelName ?? '',
                            'combination_code' => $spec->combinationCode ?? '',
                            'combination_name' => $spec->combinationName ?? '',
                            'subject_code' => $spec->subjectCode ?? '',
                            'subject_name' => $spec->subjectName ?? '',
                            'grade_code' => $spec->gradeCode ?? '',
                            'grade_name' => $spec->gradeName ?? '',
                            'class_group' => $spec->classGroup ?? '',
                        ];
                    }
                }
                $staffdata = [
                    'position' => $data->position ?? '',
                    'specialities' => $specialities,
                ];
            }

            return [
                'success' => true,
                'error' => '',
                'sdms_id' => ($usertype === 'student')
                    ? ($data->studentNumber ?? $sdmscode)
                    : ($data->staffId ?? $sdmscode),
                'user_type' => $usertype,
                'status' => $data->status ?? '',
                'academic_year' => $data->currentAcadmicYear ?? $data->academicYear ?? '',
                'school' => $school,
                'student_data' => $studentdata,
                'staff_data' => $staffdata,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'sdms_id' => $sdmscode,
                'user_type' => $usertype,
                'status' => '',
                'academic_year' => '',
                'school' => null,
                'student_data' => null,
                'staff_data' => null,
            ];
        }
    }

    /**
     * Return structure for lookup_sdms_user.
     */
    public static function lookup_sdms_user_returns(): external_single_structure {
        $schoolstructure = new external_single_structure([
            'school_code' => new external_value(PARAM_TEXT, 'School code'),
            'school_name' => new external_value(PARAM_TEXT, 'School name'),
            'region_code' => new external_value(PARAM_TEXT, 'Region code'),
            'is_active' => new external_value(PARAM_TEXT, 'Active status'),
            'academic_year' => new external_value(PARAM_TEXT, 'Academic year'),
            'levels' => new external_multiple_structure(
                new external_single_structure([
                    'level_id' => new external_value(PARAM_TEXT, 'Level ID'),
                    'level_name' => new external_value(PARAM_TEXT, 'Level name'),
                    'combinations' => new external_multiple_structure(
                        new external_single_structure([
                            'combination_code' => new external_value(PARAM_TEXT, 'Code'),
                            'combination_name' => new external_value(PARAM_TEXT, 'Name'),
                            'grades' => new external_multiple_structure(
                                new external_single_structure([
                                    'grade_code' => new external_value(PARAM_TEXT, 'Grade code'),
                                    'grade_name' => new external_value(PARAM_TEXT, 'Grade name'),
                                    'classgroups' => new external_multiple_structure(
                                        new external_single_structure([
                                            'class_id' => new external_value(PARAM_TEXT, 'Class ID'),
                                            'class_name' => new external_value(PARAM_TEXT, 'Class name'),
                                        ])
                                    ),
                                ])
                            ),
                        ])
                    ),
                ])
            ),
        ], 'School data with hierarchy', VALUE_OPTIONAL);

        $studentstructure = new external_single_structure([
            'program' => new external_value(PARAM_TEXT, 'Program/combination name'),
            'program_code' => new external_value(PARAM_TEXT, 'Program code'),
            'registration_date' => new external_value(PARAM_TEXT, 'Registration date'),
            'classgroup_id' => new external_value(PARAM_TEXT, 'Class group ID'),
        ], 'Student-specific data', VALUE_OPTIONAL);

        $specialitystructure = new external_single_structure([
            'level_name' => new external_value(PARAM_TEXT, 'Level name'),
            'combination_code' => new external_value(PARAM_TEXT, 'Combination code'),
            'combination_name' => new external_value(PARAM_TEXT, 'Combination name'),
            'subject_code' => new external_value(PARAM_TEXT, 'Subject code'),
            'subject_name' => new external_value(PARAM_TEXT, 'Subject name'),
            'grade_code' => new external_value(PARAM_TEXT, 'Grade code'),
            'grade_name' => new external_value(PARAM_TEXT, 'Grade name'),
            'class_group' => new external_value(PARAM_TEXT, 'Class group'),
        ]);

        $staffstructure = new external_single_structure([
            'position' => new external_value(PARAM_TEXT, 'Position/title'),
            'specialities' => new external_multiple_structure($specialitystructure, 'Subject assignments'),
        ], 'Staff-specific data', VALUE_OPTIONAL);

        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether lookup succeeded'),
            'error' => new external_value(PARAM_TEXT, 'Error message'),
            'sdms_id' => new external_value(PARAM_TEXT, 'SDMS identifier'),
            'user_type' => new external_value(PARAM_TEXT, 'User type'),
            'status' => new external_value(PARAM_TEXT, 'SDMS status'),
            'academic_year' => new external_value(PARAM_TEXT, 'Academic year'),
            'school' => $schoolstructure,
            'student_data' => $studentstructure,
            'staff_data' => $staffstructure,
        ]);
    }

    // =========================================================================
    // link_user — Link a new SDMS user to a Moodle account.
    // =========================================================================

    /**
     * Parameters for link_user.
     */
    public static function link_user_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'Moodle user ID'),
            'sdms_code' => new external_value(PARAM_TEXT, 'SDMS code (studentNumber or staffNumber)'),
            'user_type' => new external_value(PARAM_TEXT, 'User type: student or staff'),
        ]);
    }

    /**
     * Link a new SDMS user to an existing Moodle account.
     *
     * @param int $userid Moodle user ID.
     * @param string $sdmscode SDMS identifier.
     * @param string $usertype "student" or "staff".
     * @return array Result with success status.
     */
    public static function link_user(int $userid, string $sdmscode, string $usertype): array {
        $params = self::validate_parameters(
            self::link_user_parameters(),
            ['userid' => $userid, 'sdms_code' => $sdmscode, 'user_type' => $usertype]
        );
        $userid = $params['userid'];
        $sdmscode = $params['sdms_code'];
        $usertype = $params['user_type'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:manage', $context);

        try {
            $syncservice = new \local_elby_dashboard\sync_service();
            $result = $syncservice->link_user($userid, $sdmscode, $usertype);

            return [
                'success' => $result,
                'error' => $result ? '' : 'Not found in SDMS',
                'userid' => $userid,
                'sdms_code' => $sdmscode,
                'timestamp' => time(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'userid' => $userid,
                'sdms_code' => $sdmscode,
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Return structure for link_user.
     */
    public static function link_user_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether link succeeded'),
            'error' => new external_value(PARAM_TEXT, 'Error message'),
            'userid' => new external_value(PARAM_INT, 'Moodle user ID'),
            'sdms_code' => new external_value(PARAM_TEXT, 'SDMS code'),
            'timestamp' => new external_value(PARAM_INT, 'Operation timestamp'),
        ]);
    }

    // =========================================================================
    // refresh_user — Force refresh an existing linked user.
    // =========================================================================

    /**
     * Parameters for refresh_user.
     */
    public static function refresh_user_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'Moodle user ID'),
        ]);
    }

    /**
     * Force refresh cached SDMS data for an existing linked user.
     *
     * @param int $userid Moodle user ID.
     * @return array Result with success status.
     */
    public static function refresh_user(int $userid): array {
        $params = self::validate_parameters(
            self::refresh_user_parameters(),
            ['userid' => $userid]
        );
        $userid = $params['userid'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:manage', $context);

        try {
            $syncservice = new \local_elby_dashboard\sync_service();
            $result = $syncservice->refresh_user($userid, true);

            return [
                'success' => $result,
                'error' => $result ? '' : 'User not linked to SDMS',
                'userid' => $userid,
                'timestamp' => time(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'userid' => $userid,
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Return structure for refresh_user.
     */
    public static function refresh_user_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether refresh succeeded'),
            'error' => new external_value(PARAM_TEXT, 'Error message'),
            'userid' => new external_value(PARAM_INT, 'Moodle user ID'),
            'timestamp' => new external_value(PARAM_INT, 'Operation timestamp'),
        ]);
    }

    // =========================================================================
    // sync_school_now — Force sync a school from SDMS.
    // =========================================================================

    /**
     * Parameters for sync_school_now.
     */
    public static function sync_school_now_parameters(): external_function_parameters {
        return new external_function_parameters([
            'school_code' => new external_value(PARAM_TEXT, 'SDMS school code'),
        ]);
    }

    /**
     * Force sync a school from SDMS (ignores cache freshness).
     *
     * @param string $schoolcode SDMS school code.
     * @return array Result with success status.
     */
    public static function sync_school_now(string $schoolcode): array {
        $params = self::validate_parameters(
            self::sync_school_now_parameters(),
            ['school_code' => $schoolcode]
        );
        $schoolcode = $params['school_code'];

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/elby_dashboard:manage', $context);

        try {
            $syncservice = new \local_elby_dashboard\sync_service();
            $result = $syncservice->sync_school($schoolcode, true);

            return [
                'success' => $result,
                'error' => $result ? '' : 'School not found in SDMS',
                'school_code' => $schoolcode,
                'timestamp' => time(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'school_code' => $schoolcode,
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Return structure for sync_school_now.
     */
    public static function sync_school_now_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether sync succeeded'),
            'error' => new external_value(PARAM_TEXT, 'Error message'),
            'school_code' => new external_value(PARAM_TEXT, 'School code'),
            'timestamp' => new external_value(PARAM_INT, 'Operation timestamp'),
        ]);
    }

    // =========================================================================
    // Helper methods.
    // =========================================================================

    /**
     * Check if a cached record is stale based on configured TTL.
     *
     * @param int $lastsynced Unix timestamp of last sync.
     * @return bool True if stale.
     */
    private static function is_stale(int $lastsynced): bool {
        $ttl = (int) (get_config('local_elby_dashboard', 'sdms_cache_ttl') ?: 604800);
        return (time() - $lastsynced) > $ttl;
    }

    /**
     * Build school hierarchy from cached database records.
     *
     * @param int $schoolid School ID in elby_schools.
     * @return array Nested hierarchy array.
     */
    private static function build_school_hierarchy(int $schoolid): array {
        global $DB;

        $levels = [];
        $levelrecords = $DB->get_records('elby_levels', ['schoolid' => $schoolid]);

        foreach ($levelrecords as $level) {
            $combinations = [];
            $comborecords = $DB->get_records('elby_combinations', ['levelid' => $level->id]);

            foreach ($comborecords as $combo) {
                $grades = [];
                $graderecords = $DB->get_records('elby_grades', ['combinationid' => $combo->id]);

                foreach ($graderecords as $grade) {
                    $classgroups = [];
                    $classrecords = $DB->get_records('elby_classgroups', ['gradeid' => $grade->id]);

                    foreach ($classrecords as $class) {
                        $classgroups[] = [
                            'class_id' => $class->sdms_class_id,
                            'class_name' => $class->class_name,
                        ];
                    }

                    $grades[] = [
                        'grade_code' => $grade->grade_code,
                        'grade_name' => $grade->grade_name,
                        'classgroups' => $classgroups,
                    ];
                }

                $combinations[] = [
                    'combination_code' => $combo->combination_code,
                    'combination_name' => $combo->combination_name,
                    'combination_desc' => $combo->combination_desc ?? '',
                    'grades' => $grades,
                ];
            }

            $levels[] = [
                'level_id' => $level->sdms_level_id,
                'level_name' => $level->level_name,
                'level_desc' => $level->level_desc ?? '',
                'combinations' => $combinations,
            ];
        }

        return $levels;
    }

    /**
     * Format raw SDMS school response data for the lookup_sdms_user endpoint.
     *
     * @param object $data SDMS school response.
     * @return array Formatted school data with hierarchy.
     */
    private static function format_school_data(object $data): array {
        $levels = [];

        if (!empty($data->levels) && is_array($data->levels)) {
            foreach ($data->levels as $leveldata) {
                $combinations = [];

                if (!empty($leveldata->combinations) && is_array($leveldata->combinations)) {
                    foreach ($leveldata->combinations as $combodata) {
                        $grades = [];

                        if (!empty($combodata->grades) && is_array($combodata->grades)) {
                            foreach ($combodata->grades as $gradedata) {
                                $classgroups = [];

                                if (!empty($gradedata->classGroups) && is_array($gradedata->classGroups)) {
                                    foreach ($gradedata->classGroups as $classdata) {
                                        $classgroups[] = [
                                            'class_id' => $classdata->classGroupId ?? '',
                                            'class_name' => $classdata->classGroupName ?? '',
                                        ];
                                    }
                                }

                                $grades[] = [
                                    'grade_code' => $gradedata->gradeCode ?? '',
                                    'grade_name' => $gradedata->gradeName ?? '',
                                    'classgroups' => $classgroups,
                                ];
                            }
                        }

                        $combinations[] = [
                            'combination_code' => $combodata->combinationCode ?? '',
                            'combination_name' => $combodata->combinationName ?? '',
                            'grades' => $grades,
                        ];
                    }
                }

                $levels[] = [
                    'level_id' => $leveldata->levelId ?? '',
                    'level_name' => $leveldata->levelName ?? '',
                    'combinations' => $combinations,
                ];
            }
        }

        return [
            'school_code' => $data->schoolCode ?? '',
            'school_name' => $data->schoolName ?? '',
            'region_code' => $data->regionCode ?? '',
            'is_active' => $data->isActive ?? '',
            'academic_year' => $data->academicYear ?? '',
            'levels' => $levels,
        ];
    }
}
