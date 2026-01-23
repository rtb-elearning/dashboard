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

import { useState, useEffect, useRef, useMemo } from 'preact/hooks';
import type { CoursesReportData, ThemeConfig, CourseReport, SchoolReport, CourseListItem } from '../types';

// Sort configuration type
type SortColumn = 'school' | 'students' | `avg-${number}` | `cr-${number}`;
type SortDirection = 'asc' | 'desc';

interface CoursesReportProps {
    data: CoursesReportData;
    themeConfig: ThemeConfig;
}

// Searchable Select Component
interface SearchableSelectProps {
    options: CourseListItem[];
    selectedId: number | null;
    placeholder: string;
    onSelect: (id: number) => void;
}

function SearchableSelect({ options, selectedId, placeholder, onSelect }: SearchableSelectProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const containerRef = useRef<HTMLDivElement>(null);
    const searchInputRef = useRef<HTMLInputElement>(null);

    // Use == for comparison to handle string/number type mismatch from URL params
    const selectedOption = options.find(opt => opt.id == selectedId);

    // Filter options based on search query (searches ID, fullname, shortname)
    const filteredOptions = options.filter(opt =>
        opt.id.toString().includes(searchQuery) ||
        opt.fullname.toLowerCase().includes(searchQuery.toLowerCase()) ||
        opt.shortname.toLowerCase().includes(searchQuery.toLowerCase())
    );

    // Handle click outside to close dropdown
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setIsOpen(false);
                setSearchQuery('');
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Focus search input when dropdown opens
    useEffect(() => {
        if (isOpen && searchInputRef.current) {
            searchInputRef.current.focus();
        }
    }, [isOpen]);

    const handleSelect = (id: number) => {
        onSelect(id);
        setIsOpen(false);
        setSearchQuery('');
    };

    return (
        <div ref={containerRef} className="relative w-full max-w-md">
            {/* Trigger Button */}
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className="w-full px-4 py-2.5 text-left bg-white border border-gray-300 rounded-lg flex items-center justify-between hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <span className={selectedOption ? 'text-gray-900' : 'text-gray-500'}>
                    {selectedOption ? `[${selectedOption.id}] ${selectedOption.fullname}` : placeholder}
                </span>
                <svg
                    className={`w-5 h-5 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`}
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
            </button>

            {/* Dropdown */}
            {isOpen && (
                <div className="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg">
                    {/* Search Input */}
                    <div className="p-2 border-b border-gray-100">
                        <div className="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-md focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500">
                            <svg
                                className="w-4 h-4 text-gray-400 shrink-0"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fillRule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clipRule="evenodd" />
                            </svg>
                            <input
                                ref={searchInputRef}
                                type="text"
                                placeholder="Search course..."
                                value={searchQuery}
                                onInput={(e) => setSearchQuery((e.target as HTMLInputElement).value)}
                                className="w-full text-sm bg-transparent border-none outline-none focus:outline-none focus:ring-0"
                            />
                        </div>
                    </div>

                    {/* Options List */}
                    <div className="max-h-60 overflow-y-auto">
                        {filteredOptions.length > 0 ? (
                            filteredOptions.map(option => (
                                <button
                                    key={option.id}
                                    type="button"
                                    onClick={() => handleSelect(option.id)}
                                    className={`w-full px-4 py-2.5 text-left text-sm hover:bg-blue-50 flex items-center justify-between ${
                                        option.id == selectedId ? 'bg-blue-50 text-blue-700' : 'text-gray-700'
                                    }`}
                                >
                                    <div className="flex items-center gap-2 truncate">
                                        <span className="text-xs text-gray-400 shrink-0">[{option.id}]</span>
                                        <span className="truncate">{option.fullname}</span>
                                    </div>
                                    <span className="text-xs text-gray-400 ml-2 shrink-0">
                                        {option.enrolled_count} enrolled
                                    </span>
                                </button>
                            ))
                        ) : (
                            <div className="px-4 py-3 text-sm text-gray-500 text-center">
                                No courses found
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

// Year Select Component
interface YearSelectProps {
    years: { value: number; label: string }[];
    selectedYear: number;
    onSelect: (year: number) => void;
}

function YearSelect({ years, selectedYear, onSelect }: YearSelectProps) {
    return (
        <select
            value={selectedYear}
            onChange={(e) => onSelect(parseInt((e.target as HTMLSelectElement).value))}
            className="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
            {years.map(year => (
                <option key={year.value} value={year.value}>
                    {year.label}
                </option>
            ))}
        </select>
    );
}

// Color palette for donut charts (orange, green, blue, purple, teal)
const DONUT_COLORS = ['#F5A623', '#4CAF50', '#2196F3', '#9C27B0', '#00BCD4'];

// Donut Chart Component for completion rate
function CompletionDonut({ rate, label, colorIndex = 0 }: { rate: number; label: string; color?: string; colorIndex?: number }) {
    const radius = 42;
    const strokeWidth = 6;
    const circumference = 2 * Math.PI * radius;
    const strokeDasharray = `${(rate / 100) * circumference} ${circumference}`;
    const progressColor = DONUT_COLORS[colorIndex % DONUT_COLORS.length];

    return (
        <div className="flex flex-col items-center">
            <div className="relative w-28 h-28">
                <svg viewBox="0 0 100 100" className="w-full h-full transform -rotate-90">
                    {/* Background circle */}
                    <circle
                        cx="50"
                        cy="50"
                        r={radius}
                        fill="none"
                        stroke="#e0e0e0"
                        strokeWidth={strokeWidth}
                    />
                    {/* Progress circle */}
                    <circle
                        cx="50"
                        cy="50"
                        r={radius}
                        fill="none"
                        stroke={progressColor}
                        strokeWidth={strokeWidth}
                        strokeDasharray={strokeDasharray}
                        strokeLinecap="round"
                    />
                </svg>
                <div className="absolute inset-0 flex flex-col items-center justify-center">
                    <span className="text-xl font-medium text-gray-500">{rate.toFixed(0)}%</span>
                </div>
            </div>
            <span className="text-xs text-gray-600 mt-2 text-center font-medium max-w-[120px]">{label}</span>
        </div>
    );
}

// Truncate text with ellipsis
function truncateText(text: string, maxLength: number): string {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength - 1) + '…';
}

// Bar Chart for completion rate and average score per school
function SchoolBarChart({ schools, sectionIndex, themeConfig }: {
    schools: SchoolReport[];
    sectionIndex: number;
    themeConfig: ThemeConfig;
}) {
    // Get grademax from first school's section data for percentage conversion
    const grademax = schools[0]?.sections[sectionIndex]?.grademax || 100;

    return (
        <div className="bg-white rounded-xl p-6 shadow-sm overflow-visible">
            <div className="flex items-center gap-4 mb-6">
                <div className="flex items-center gap-2">
                    <span className="w-3 h-3 rounded-full" style={{ backgroundColor: themeConfig.chartPrimaryColor }}></span>
                    <span className="text-sm text-gray-600">Average Score</span>
                </div>
                <div className="flex items-center gap-2">
                    <span className="w-3 h-3 rounded-full" style={{ backgroundColor: themeConfig.chartSecondaryColor }}></span>
                    <span className="text-sm text-gray-600">Completion Rate</span>
                </div>
            </div>
            <div className="flex">
                {/* Left Y-axis for Average Score */}
                <div className="flex flex-col justify-between h-48 pr-2 text-xs text-gray-500 shrink-0">
                    <span>{grademax}</span>
                    <span>{(grademax * 0.75).toFixed(grademax >= 10 ? 0 : 1)}</span>
                    <span>{(grademax * 0.5).toFixed(grademax >= 10 ? 0 : 1)}</span>
                    <span>{(grademax * 0.25).toFixed(grademax >= 10 ? 0 : 1)}</span>
                    <span>0</span>
                </div>
                {/* Chart bars */}
                <div className="flex-1 overflow-x-auto">
                    <div className="flex items-end gap-8">
                        {schools.slice(0, 15).map((school, idx) => {
                            const section = school.sections[sectionIndex];
                            const avgValue = section?.average_grade || 0;
                            const sectionGrademax = section?.grademax || grademax;
                            // Convert raw score to percentage for bar height
                            const avgHeight = sectionGrademax > 0 ? (avgValue / sectionGrademax) * 100 : 0;
                            const crHeight = section ? section.completion_rate : 0;
                            const fullName = String(school.school_name || '-');
                            const displayName = truncateText(fullName, 20);

                            return (
                                <div key={school.school_code || `school-${idx}`} className="flex flex-col items-center min-w-[50px]">
                                    <div className="flex gap-1 items-end h-48">
                                        <div
                                            className="w-5 rounded-t-sm cursor-pointer"
                                            style={{
                                                height: `${avgHeight}%`,
                                                backgroundColor: themeConfig.chartPrimaryColor,
                                            }}
                                            title={`${fullName}\nAvg: ${avgValue.toFixed(1)}/${sectionGrademax}`}
                                        />
                                        <div
                                            className="w-5 rounded-t-sm cursor-pointer"
                                            style={{
                                                height: `${crHeight}%`,
                                                backgroundColor: themeConfig.chartSecondaryColor,
                                            }}
                                            title={`${fullName}\nCR: ${crHeight.toFixed(1)}%`}
                                        />
                                    </div>
                                    <div className="h-32 flex items-start justify-center mt-2">
                                        <span
                                            className="text-[11px] text-gray-600 whitespace-nowrap cursor-default origin-top"
                                            style={{ transform: 'rotate(45deg)' }}
                                            title={fullName}
                                        >
                                            {displayName}
                                        </span>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
                {/* Right Y-axis for Completion Rate */}
                <div className="flex flex-col justify-between h-48 pl-2 text-xs text-gray-500 shrink-0">
                    <span>100%</span>
                    <span>75%</span>
                    <span>50%</span>
                    <span>25%</span>
                    <span>0%</span>
                </div>
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
        <div className="bg-white rounded-xl p-6 shadow-sm">
            <p className="text-xs text-gray-400 mb-3 italic">Hover over points to see school names</p>
            <div className="relative h-64 border-l border-b border-gray-200 ml-8">
                {/* Y-axis label */}
                <div className="absolute -left-6 top-1/2 -rotate-90 text-xs text-gray-500 whitespace-nowrap">Avg Score</div>
                {/* X-axis label */}
                <div className="absolute bottom-[-24px] left-1/2 -translate-x-1/2 text-xs text-gray-500">Completion Rate</div>

                {/* Y-axis values */}
                {[0, 25, 50, 75, 100].map(pct => (
                    <div key={`y-${pct}`} className="absolute text-[10px] text-gray-400" style={{ bottom: `${pct}%`, left: '-20px', transform: 'translateY(50%)' }}>
                        {pct}
                    </div>
                ))}

                {/* Grid lines */}
                {[0, 25, 50, 75, 100].map(pct => (
                    <div
                        key={pct}
                        className="absolute w-full border-t border-dashed border-gray-100"
                        style={{ bottom: `${pct}%` }}
                    />
                ))}

                {/* Vertical grid lines */}
                {[0, 25, 50, 75, 100].map(pct => (
                    <div
                        key={`v-${pct}`}
                        className="absolute h-full border-l border-dashed border-gray-100"
                        style={{ left: `${pct}%` }}
                    />
                ))}

                {/* Data points - hover shows school name */}
                {schools.map((school, idx) => {
                    const section = school.sections[sectionIndex];
                    if (!section) return null;

                    const x = section.completion_rate;
                    const y = section.average_grade || 0;
                    const fullName = String(school.school_name || 'Unknown');

                    return (
                        <div
                            key={school.school_code || `scatter-${idx}`}
                            className="absolute w-3 h-3 rounded-full cursor-pointer transform -translate-x-1/2 translate-y-1/2 hover:scale-150 hover:z-10 transition-transform"
                            style={{
                                left: `${x}%`,
                                bottom: `${y}%`,
                                backgroundColor: themeConfig.chartPrimaryColor,
                            }}
                            title={`${fullName}\nCompletion: ${x.toFixed(1)}%\nAvg Score: ${y.toFixed(1)}%`}
                        />
                    );
                })}
            </div>
            {/* X-axis labels */}
            <div className="flex justify-between text-xs text-gray-400 mt-3 ml-8">
                <span>0%</span>
                <span>25%</span>
                <span>50%</span>
                <span>75%</span>
                <span>100%</span>
            </div>
        </div>
    );
}

// Sort indicator component
function SortIndicator({ column, sortColumn, sortDirection }: {
    column: SortColumn;
    sortColumn: SortColumn | null;
    sortDirection: SortDirection;
}) {
    const isActive = sortColumn === column;
    return (
        <span className={`ml-1 inline-flex ${isActive ? 'text-blue-600' : 'text-gray-400'}`}>
            {isActive && sortDirection === 'asc' ? '↑' : isActive && sortDirection === 'desc' ? '↓' : '↕'}
        </span>
    );
}

// Sortable header component
function SortableHeader({ children, column, sortColumn, sortDirection, onSort, className }: {
    children: React.ReactNode;
    column: SortColumn;
    sortColumn: SortColumn | null;
    sortDirection: SortDirection;
    onSort: (column: SortColumn) => void;
    className?: string;
}) {
    return (
        <th
            className={`${className} cursor-pointer hover:bg-gray-100 select-none`}
            onClick={() => onSort(column)}
        >
            <div className="flex items-center justify-center">
                {children}
                <SortIndicator column={column} sortColumn={sortColumn} sortDirection={sortDirection} />
            </div>
        </th>
    );
}

// Report Table Component
function ReportTable({ report, themeConfig }: { report: CourseReport; themeConfig: ThemeConfig }) {
    const [sortColumn, setSortColumn] = useState<SortColumn | null>(null);
    const [sortDirection, setSortDirection] = useState<SortDirection>('desc');

    const handleSort = (column: SortColumn) => {
        if (sortColumn === column) {
            setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            setSortColumn(column);
            setSortDirection('desc');
        }
    };

    const sortedSchools = useMemo(() => {
        if (!sortColumn) return report.schools;

        return [...report.schools].sort((a, b) => {
            let aVal: number | string = 0;
            let bVal: number | string = 0;

            if (sortColumn === 'school') {
                aVal = a.school_name || '';
                bVal = b.school_name || '';
                return sortDirection === 'asc'
                    ? String(aVal).localeCompare(String(bVal))
                    : String(bVal).localeCompare(String(aVal));
            } else if (sortColumn === 'students') {
                aVal = a.student_count;
                bVal = b.student_count;
            } else if (sortColumn.startsWith('avg-')) {
                const idx = parseInt(sortColumn.replace('avg-', ''));
                aVal = a.sections[idx]?.average_grade || 0;
                bVal = b.sections[idx]?.average_grade || 0;
            } else if (sortColumn.startsWith('cr-')) {
                const idx = parseInt(sortColumn.replace('cr-', ''));
                aVal = a.sections[idx]?.completion_rate || 0;
                bVal = b.sections[idx]?.completion_rate || 0;
            }

            return sortDirection === 'asc'
                ? (aVal as number) - (bVal as number)
                : (bVal as number) - (aVal as number);
        });
    }, [report.schools, sortColumn, sortDirection]);

    return (
        <div className="bg-white rounded-xl shadow-sm overflow-hidden">
            {/* Legend */}
            <div className="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <span className="text-sm text-gray-600">
                    <span className="font-medium">Legend:</span> U = Unit, Avg = Average score, CR = Completion rate
                    <span className="ml-4 text-gray-400">(Click column headers to sort)</span>
                </span>
            </div>
            <div className="overflow-auto max-h-[600px]">
                <table className="w-full text-sm">
                    <thead className="sticky top-0 z-20">
                        <tr className="bg-gray-50">
                            <th className="px-3 py-2 text-left text-xs font-semibold text-gray-600 sticky left-0 z-30 bg-gray-50">#</th>
                            <SortableHeader
                                column="school"
                                sortColumn={sortColumn}
                                sortDirection={sortDirection}
                                onSort={handleSort}
                                className="px-3 py-2 text-left text-xs font-semibold text-gray-600 sticky left-8 z-30 bg-gray-50 min-w-[200px]"
                            >
                                School
                            </SortableHeader>
                            <SortableHeader
                                column="students"
                                sortColumn={sortColumn}
                                sortDirection={sortDirection}
                                onSort={handleSort}
                                className="px-3 py-2 text-center text-xs font-semibold text-gray-600 sticky left-[232px] z-30 bg-gray-50"
                            >
                                Enrolled students
                            </SortableHeader>
                            {report.overview_sections.map((section, idx) => (
                                <>
                                    <SortableHeader
                                        key={`avg-${idx}`}
                                        column={`avg-${idx}` as SortColumn}
                                        sortColumn={sortColumn}
                                        sortDirection={sortDirection}
                                        onSort={handleSort}
                                        className="px-2 py-2 text-center text-xs font-semibold text-gray-600 bg-gray-50"
                                    >
                                        U{section.section_number} Avg
                                    </SortableHeader>
                                    <SortableHeader
                                        key={`cr-${idx}`}
                                        column={`cr-${idx}` as SortColumn}
                                        sortColumn={sortColumn}
                                        sortDirection={sortDirection}
                                        onSort={handleSort}
                                        className="px-2 py-2 text-center text-xs font-semibold text-gray-600 bg-gray-50"
                                    >
                                        U{section.section_number} CR
                                    </SortableHeader>
                                </>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {sortedSchools.map((school, idx) => (
                            <tr key={school.school_code || `row-${idx}`} className="border-t border-gray-100 hover:bg-gray-50 group">
                                <td className="px-3 py-2 text-gray-500 sticky left-0 z-10 bg-white group-hover:bg-gray-50">{idx + 1}</td>
                                <td className="px-3 py-2 font-medium text-gray-800 sticky left-8 z-10 bg-white group-hover:bg-gray-50 min-w-[200px]">
                                    {school.school_name}
                                </td>
                                <td className="px-3 py-2 text-center text-gray-600 sticky left-[232px] z-10 bg-white group-hover:bg-gray-50">{school.student_count}</td>
                                {school.sections.map((section, sIdx) => (
                                    <>
                                        <td key={`avg-${sIdx}`} className="px-2 py-2 text-center text-gray-600">
                                            {section.average_grade
                                                ? section.grademax
                                                    ? `${section.average_grade.toFixed(1)}/${section.grademax}`
                                                    : section.average_grade.toFixed(1)
                                                : '-'}
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
                            <td className="px-3 py-2 sticky left-0 z-10 bg-gray-50"></td>
                            <td className="px-3 py-2 sticky left-8 z-10 bg-gray-50 min-w-[200px]">Grand Total</td>
                            <td className="px-3 py-2 text-center sticky left-[232px] z-10 bg-gray-50">{report.total_enrolled}</td>
                            {report.overview_sections.map((section, idx) => {
                                // Calculate averages
                                const avgGrades = report.schools
                                    .map(s => s.sections[idx]?.average_grade)
                                    .filter(g => g !== undefined && g > 0);
                                const avgGrade = avgGrades.length > 0
                                    ? avgGrades.reduce((a, b) => a + b!, 0) / avgGrades.length
                                    : 0;
                                // Get grademax from first school's section data
                                const grademax = report.schools[0]?.sections[idx]?.grademax;

                                return (
                                    <>
                                        <td key={`avg-${idx}`} className="px-2 py-2 text-center">
                                            {avgGrade > 0
                                                ? grademax
                                                    ? `${avgGrade.toFixed(1)}/${grademax}`
                                                    : avgGrade.toFixed(1)
                                                : '-'}
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
    const handleCourseSelect = (courseid: number) => {
        const params = new URLSearchParams();
        params.set('courseid', String(courseid));
        if (data.selected_year) {
            params.set('year', String(data.selected_year));
        }
        window.location.href = `/local/elby_dashboard/courses.php?${params.toString()}`;
    };

    const handleYearSelect = (year: number) => {
        const params = new URLSearchParams();
        if (data.selected_courseid) {
            params.set('courseid', String(data.selected_courseid));
        }
        params.set('year', String(year));
        window.location.href = `/local/elby_dashboard/courses.php?${params.toString()}`;
    };

    return (
        <div className="p-2 sm:p-4 lg:p-6 bg-gray-50 min-h-screen">
            {/* Selectors */}
            <div className="mb-6 space-y-3">
                {/* Year Selector */}
                <div className="flex items-center gap-3">
                    <label className="text-sm font-medium text-gray-700 whitespace-nowrap">Academic Year</label>
                    <YearSelect
                        years={data.available_years}
                        selectedYear={data.selected_year}
                        onSelect={handleYearSelect}
                    />
                </div>

                {/* Course Selector */}
                <div className="flex items-center gap-3">
                    <label className="text-sm font-medium text-gray-700 whitespace-nowrap">Select Course</label>
                    <div className="flex-1 max-w-md">
                        <SearchableSelect
                            options={data.courses_list}
                            selectedId={data.selected_courseid}
                            placeholder="Select a course..."
                            onSelect={handleCourseSelect}
                        />
                    </div>
                </div>
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
                                        colorIndex={idx}
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
                                        Completion rate and Average score for each school
                                    </h4>
                                    <SchoolBarChart
                                        schools={data.course_report.schools}
                                        sectionIndex={sectionIndex}
                                        themeConfig={themeConfig}
                                    />
                                </div>
                                <div>
                                    <h4 className="text-sm text-gray-600 mb-2">
                                        Completion rate x Average score for each school
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
