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
 * TypeScript type definitions for Elby Dashboard.
 *
 * @module     local_elby_dashboard/types
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export interface UserData {
    id: number;
    fullname: string;
    firstname: string;
    lastname: string;
    email: string;
    avatar: string;
    roles: string[];
    isAdmin?: boolean;
}

export interface Teacher {
    id: number;
    firstname: string;
    lastname: string;
    fullname: string;
    email: string;
    avatar?: string;
}

export interface StatsData {
    totalCourses: number;
    totalUsers: number;
    totalEnrollments: number;
    totalActivities: number;
    activeUsers?: number;
    totalTeachers: number;
    totalStudents: number;
    teachers: Teacher[];
}

export interface MenuItem {
    id: string;
    name: string;
    url: string;
    icon: string;
}

export interface SidenavConfig {
    title: string;
    logoUrl: string | null;
}

export interface ThemeConfig {
    // Colors
    sidenavAccentColor: string;
    statCard1Color: string;
    statCard2Color: string;
    statCard3Color: string;
    statCard4Color: string;
    chartPrimaryColor: string;
    chartSecondaryColor: string;
    // Header options
    showSearchBar: boolean;
    showNotifications: boolean;
    showUserProfile: boolean;
    // Menu visibility
    menuVisibility: Record<string, boolean>;
}

export type PageId = 'home' | 'completion' | 'courses';

// Course report types
export interface CourseListItem {
    id: number;
    shortname: string;
    fullname: string;
    enrolled_count: number;
}

export interface CourseCategory {
    category_id: number;
    category_name: string;
    courses: CourseListItem[];
}

export interface CategoryNode {
    id: number;
    name: string;
    parent: number;
    children: CategoryNode[];
    courses: CourseListItem[];
}

export interface SectionStat {
    section_number: number;
    section_name: string;
    completion_rate: number;
    average_grade?: number;
    grademax?: number;
}

export interface SchoolReport {
    school_code: string;
    school_name: string;
    student_count: number;
    sections: SectionStat[];
}

export interface OverviewSection {
    section_number: number;
    section_name: string;
    completion_rate: number;
}

export interface CourseReport {
    courseid: number;
    course_name: string;
    course_shortname?: string;
    total_enrolled: number;
    total_schools: number;
    overview_sections: OverviewSection[];
    schools: SchoolReport[];
}

export interface AcademicYear {
    value: number;      // Start year (e.g., 2024)
    label: string;      // Display format (e.g., "2024-2025")
}

export interface CoursesReportData {
    courses_list: CategoryNode[];
    selected_courseid: number;
    course_report: CourseReport | null;
    available_years: AcademicYear[];
    selected_year: number;
}
