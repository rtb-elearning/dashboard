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
 * Courses Report component for Elby Dashboard.
 *
 * @module     local_elby_dashboard/components/CoursesReport
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import type { CoursesReportData, ThemeConfig, CourseReport, SchoolReport } from '../types';

interface CoursesReportProps {
    data: CoursesReportData;
    themeConfig: ThemeConfig;
}

// Donut Chart Component for completion rate
function CompletionDonut({ rate, label, color }: { rate: number; label: string; color: string }) {
    const circumference = 2 * Math.PI * 40;
    const strokeDasharray = `${(rate / 100) * circumference} ${circumference}`;

    return (
        <div className="flex flex-col items-center">
            <div className="relative w-24 h-24">
                <svg viewBox="0 0 100 100" className="w-full h-full transform -rotate-90">
                    {/* Background circle */}
                    <circle
                        cx="50"
                        cy="50"
                        r="40"
                        fill="none"
                        stroke="#e5e7eb"
                        strokeWidth="12"
                    />
                    {/* Progress circle */}
                    <circle
                        cx="50"
                        cy="50"
                        r="40"
                        fill="none"
                        stroke={color}
                        strokeWidth="12"
                        strokeDasharray={strokeDasharray}
                        strokeLinecap="round"
                    />
                </svg>
                <div className="absolute inset-0 flex flex-col items-center justify-center">
                    <span className="text-sm font-bold text-gray-800">{rate.toFixed(1)}%</span>
                    <span className="text-xs text-gray-500">CR</span>
                </div>
            </div>
            <span className="text-xs text-gray-600 mt-2 text-center">{label}</span>
        </div>
    );
}

// Bar Chart for completion rate and average score per school
function SchoolBarChart({ schools, sectionIndex, themeConfig }: {
    schools: SchoolReport[];
    sectionIndex: number;
    themeConfig: ThemeConfig;
}) {
    const maxStudents = Math.max(...schools.map(s => s.student_count), 1);

    return (
        <div className="bg-white rounded-xl p-4 shadow-sm">
            <div className="flex items-center gap-4 mb-4">
                <div className="flex items-center gap-2">
                    <span className="w-3 h-3 rounded-full" style={{ backgroundColor: themeConfig.chartPrimaryColor }}></span>
                    <span className="text-xs text-gray-600">Average</span>
                </div>
                <div className="flex items-center gap-2">
                    <span className="w-3 h-3 rounded-full" style={{ backgroundColor: themeConfig.chartSecondaryColor }}></span>
                    <span className="text-xs text-gray-600">Completion Rate</span>
                </div>
            </div>
            <div className="flex items-end gap-2 h-40 overflow-x-auto pb-2">
                {schools.slice(0, 15).map((school, idx) => {
                    const section = school.sections[sectionIndex];
                    const avgHeight = section ? (section.average_grade || 0) : 0;
                    const crHeight = section ? section.completion_rate : 0;

                    return (
                        <div key={school.school_code || `school-${idx}`} className="flex flex-col items-center min-w-[40px]">
                            <div className="flex gap-1 items-end h-32">
                                <div
                                    className="w-3 rounded-t-sm"
                                    style={{
                                        height: `${avgHeight}%`,
                                        backgroundColor: themeConfig.chartPrimaryColor,
                                    }}
                                    title={`Avg: ${avgHeight.toFixed(1)}%`}
                                />
                                <div
                                    className="w-3 rounded-t-sm"
                                    style={{
                                        height: `${crHeight}%`,
                                        backgroundColor: themeConfig.chartSecondaryColor,
                                    }}
                                    title={`CR: ${crHeight.toFixed(1)}%`}
                                />
                            </div>
                            <span className="text-[10px] text-gray-500 mt-1 truncate max-w-[40px]" title={String(school.school_name || '')}>
                                {String(school.school_name || '').substring(0, 8) || '-'}
                            </span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

// Scatter Plot for Completion Rate vs Average Score
function ScatterPlot({ schools, sectionIndex, themeConfig }: {
    schools: SchoolReport[];
    sectionIndex: number;
    themeConfig: ThemeConfig;
}) {
    return (
        <div className="bg-white rounded-xl p-4 shadow-sm">
            <div className="relative h-40 border-l border-b border-gray-200">
                {/* Y-axis label */}
                <div className="absolute -left-6 top-1/2 -rotate-90 text-xs text-gray-500">Score</div>
                {/* X-axis label */}
                <div className="absolute bottom-[-20px] left-1/2 text-xs text-gray-500">Completion Rate</div>

                {/* Grid lines */}
                {[0, 25, 50, 75, 100].map(pct => (
                    <div
                        key={pct}
                        className="absolute w-full border-t border-dashed border-gray-100"
                        style={{ bottom: `${pct}%` }}
                    />
                ))}

                {/* Data points */}
                {schools.map((school, idx) => {
                    const section = school.sections[sectionIndex];
                    if (!section) return null;

                    const x = section.completion_rate;
                    const y = section.average_grade || 0;

                    return (
                        <div
                            key={school.school_code || `scatter-${idx}`}
                            className="absolute w-2 h-2 rounded-full transform -translate-x-1/2 -translate-y-1/2"
                            style={{
                                left: `${x}%`,
                                bottom: `${y}%`,
                                backgroundColor: themeConfig.chartPrimaryColor,
                            }}
                            title={`${school.school_name || 'Unknown'}: CR ${x.toFixed(1)}%, Avg ${y.toFixed(1)}%`}
                        />
                    );
                })}
            </div>
            {/* X-axis labels */}
            <div className="flex justify-between text-xs text-gray-400 mt-1 pl-4">
                <span>0%</span>
                <span>50%</span>
                <span>100%</span>
            </div>
        </div>
    );
}

// Report Table Component
function ReportTable({ report, themeConfig }: { report: CourseReport; themeConfig: ThemeConfig }) {
    const numSections = report.overview_sections.length;

    return (
        <div className="bg-white rounded-xl shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50">
                            <th className="px-3 py-2 text-left text-xs font-semibold text-gray-600 sticky left-0 bg-gray-50">#</th>
                            <th className="px-3 py-2 text-left text-xs font-semibold text-gray-600 sticky left-8 bg-gray-50">School</th>
                            <th className="px-3 py-2 text-center text-xs font-semibold text-gray-600">Students</th>
                            {report.overview_sections.map((section, idx) => (
                                <>
                                    <th key={`avg-${idx}`} className="px-2 py-2 text-center text-xs font-semibold text-gray-600">
                                        U{section.section_number} Avg
                                    </th>
                                    <th key={`cr-${idx}`} className="px-2 py-2 text-center text-xs font-semibold text-gray-600">
                                        U{section.section_number} CR
                                    </th>
                                </>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {report.schools.map((school, idx) => (
                            <tr key={school.school_code || `row-${idx}`} className="border-t border-gray-100 hover:bg-gray-50">
                                <td className="px-3 py-2 text-gray-500 sticky left-0 bg-white">{idx + 1}</td>
                                <td className="px-3 py-2 font-medium text-gray-800 sticky left-8 bg-white">
                                    {school.school_name}
                                </td>
                                <td className="px-3 py-2 text-center text-gray-600">{school.student_count}</td>
                                {school.sections.map((section, sIdx) => (
                                    <>
                                        <td key={`avg-${sIdx}`} className="px-2 py-2 text-center text-gray-600">
                                            {section.average_grade?.toFixed(1) || '-'}
                                        </td>
                                        <td key={`cr-${sIdx}`} className="px-2 py-2 text-center text-gray-600">
                                            {section.completion_rate.toFixed(1)}%
                                        </td>
                                    </>
                                ))}
                            </tr>
                        ))}
                        {/* Grand Total Row */}
                        <tr className="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                            <td className="px-3 py-2 sticky left-0 bg-gray-50"></td>
                            <td className="px-3 py-2 sticky left-8 bg-gray-50">Grand Total</td>
                            <td className="px-3 py-2 text-center">{report.total_enrolled}</td>
                            {report.overview_sections.map((section, idx) => {
                                // Calculate averages
                                const avgGrades = report.schools
                                    .map(s => s.sections[idx]?.average_grade)
                                    .filter(g => g !== undefined && g > 0);
                                const avgGrade = avgGrades.length > 0
                                    ? avgGrades.reduce((a, b) => a + b!, 0) / avgGrades.length
                                    : 0;

                                return (
                                    <>
                                        <td key={`avg-${idx}`} className="px-2 py-2 text-center">
                                            {avgGrade > 0 ? avgGrade.toFixed(1) : '-'}
                                        </td>
                                        <td key={`cr-${idx}`} className="px-2 py-2 text-center">
                                            {section.completion_rate.toFixed(1)}%
                                        </td>
                                    </>
                                );
                            })}
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function CoursesReport({ data, themeConfig }: CoursesReportProps) {
    const handleCourseChange = (e: Event) => {
        const select = e.target as HTMLSelectElement;
        const courseid = select.value;
        if (courseid) {
            window.location.href = `/local/elby_dashboard/courses.php?courseid=${courseid}`;
        }
    };

    return (
        <div className="p-6 bg-gray-50 min-h-screen">
            {/* Course Selector */}
            <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 mb-2">Select Course</label>
                <select
                    className="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value={data.selected_courseid || ''}
                    onChange={handleCourseChange}
                >
                    <option value="">-- Select a course --</option>
                    {data.courses_list.map(course => (
                        <option key={course.id} value={course.id}>
                            {course.fullname} ({course.enrolled_count} enrolled)
                        </option>
                    ))}
                </select>
            </div>

            {data.course_report ? (
                <>
                    {/* Report Table */}
                    <div className="mb-6">
                        <ReportTable report={data.course_report} themeConfig={themeConfig} />
                    </div>

                    {/* Overview Section */}
                    <div className="mb-6">
                        <h2 className="text-lg font-semibold text-gray-800 mb-4">Overview</h2>
                        <div className="bg-white rounded-xl p-6 shadow-sm">
                            <div className="flex flex-wrap items-center gap-8">
                                {/* Enrolled Students */}
                                <div className="flex flex-col items-center">
                                    <span className="text-3xl font-bold text-gray-800">
                                        {data.course_report.total_enrolled}
                                    </span>
                                    <span className="text-sm text-gray-500">Enrolled Students</span>
                                </div>

                                {/* Completion Donuts per Unit */}
                                {data.course_report.overview_sections.map((section, idx) => (
                                    <CompletionDonut
                                        key={section.section_number}
                                        rate={section.completion_rate}
                                        label={section.section_name}
                                        color={themeConfig.chartPrimaryColor}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Charts per Unit */}
                    {data.course_report.overview_sections.map((section, sectionIndex) => (
                        <div key={section.section_number} className="mb-6">
                            <h3 className="text-md font-semibold text-gray-700 mb-3">
                                {section.section_name}
                            </h3>
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div>
                                    <h4 className="text-sm text-gray-600 mb-2">
                                        Completion rate and Average score for each TTC
                                    </h4>
                                    <SchoolBarChart
                                        schools={data.course_report.schools}
                                        sectionIndex={sectionIndex}
                                        themeConfig={themeConfig}
                                    />
                                </div>
                                <div>
                                    <h4 className="text-sm text-gray-600 mb-2">
                                        Completion rate x Average score for each TTC
                                    </h4>
                                    <ScatterPlot
                                        schools={data.course_report.schools}
                                        sectionIndex={sectionIndex}
                                        themeConfig={themeConfig}
                                    />
                                </div>
                            </div>
                        </div>
                    ))}
                </>
            ) : (
                <div className="bg-white rounded-xl p-8 shadow-sm text-center">
                    <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/>
                    </svg>
                    <p className="text-gray-500">Select a course to view the report</p>
                </div>
            )}
        </div>
    );
}
