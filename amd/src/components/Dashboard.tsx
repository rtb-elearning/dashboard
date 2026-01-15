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
 * Dashboard component for Elby Dashboard.
 *
 * @module     local_elby_dashboard/components/Dashboard
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import type { UserData, StatsData, ThemeConfig } from '../types';

interface DashboardProps {
    user: UserData;
    stats: StatsData;
    themeConfig: ThemeConfig;
}

// Sample attendance data (requires mod_attendance for real data)
const attendanceData = [
    { day: 'Mon', present: 45, absent: 25 },
    { day: 'Tue', present: 78, absent: 55 },
    { day: 'Wed', present: 60, absent: 30 },
    { day: 'Thu', present: 55, absent: 45 },
    { day: 'Fri', present: 70, absent: 40 },
];

// Course enrollment data (replacing earnings)
const enrollmentData = [
    { day: 1, enrollments: 120, completions: 45 },
    { day: 5, enrollments: 185, completions: 78 },
    { day: 10, enrollments: 240, completions: 125 },
    { day: 15, enrollments: 380, completions: 210 },
    { day: 20, enrollments: 520, completions: 295 },
    { day: 25, enrollments: 450, completions: 340 },
    { day: 30, enrollments: 380, completions: 310 },
];

// Stat Card Component
function StatCard({ icon, value, label, bgColor, customBgColor }: { icon: JSX.Element; value: string; label: string; bgColor: string; customBgColor?: string }) {
    return (
        <div
            className={`${bgColor} rounded-xl p-4 flex items-center gap-4`}
            style={customBgColor ? { backgroundColor: customBgColor } : undefined}
        >
            <div className="w-12 h-12 rounded-full bg-white/30 flex items-center justify-center">
                {icon}
            </div>
            <div>
                <p className="text-xs text-gray-600 font-medium">{label}</p>
                <p className="text-xl font-bold text-gray-800">{value}</p>
            </div>
        </div>
    );
}

// Donut Chart Component
function DonutChart({ total, boys, girls }: { total: number; boys: number; girls: number }) {
    const boysPercent = (boys / total) * 100;
    const girlsPercent = (girls / total) * 100;

    return (
        <div className="relative w-40 h-40">
            <svg viewBox="0 0 100 100" className="w-full h-full transform -rotate-90">
                {/* Background circle */}
                <circle
                    cx="50"
                    cy="50"
                    r="40"
                    fill="none"
                    stroke="#e0f2fe"
                    strokeWidth="15"
                />
                {/* Boys segment (cyan) */}
                <circle
                    cx="50"
                    cy="50"
                    r="40"
                    fill="none"
                    stroke="#22d3ee"
                    strokeWidth="15"
                    strokeDasharray={`${boysPercent * 2.51} ${251.2 - boysPercent * 2.51}`}
                    strokeLinecap="round"
                />
                {/* Girls segment (pink) */}
                <circle
                    cx="50"
                    cy="50"
                    r="40"
                    fill="none"
                    stroke="#f0abfc"
                    strokeWidth="15"
                    strokeDasharray={`${girlsPercent * 2.51} ${251.2 - girlsPercent * 2.51}`}
                    strokeDashoffset={`${-boysPercent * 2.51}`}
                    strokeLinecap="round"
                />
            </svg>
            <div className="absolute inset-0 flex items-center justify-center">
                <span className="text-3xl font-bold text-gray-800">{total}</span>
            </div>
        </div>
    );
}

// Bar Chart Component
function AttendanceChart({ data }: { data: typeof attendanceData }) {
    const maxValue = 100;

    return (
        <div className="flex items-end justify-between h-48 gap-4 px-4">
            {data.map((item, index) => (
                <div key={item.day} className="flex flex-col items-center gap-1 flex-1">
                    <div className="flex gap-1 items-end h-36 w-full justify-center">
                        {/* Present bar */}
                        <div
                            className="w-3 bg-cyan-400 rounded-t-sm transition-all relative group"
                            style={{ height: `${(item.present / maxValue) * 100}%` }}
                        >
                            {index === 1 && (
                                <div className="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded">
                                    55%
                                </div>
                            )}
                        </div>
                        {/* Absent bar */}
                        <div
                            className="w-3 bg-purple-300 rounded-t-sm transition-all"
                            style={{ height: `${(item.absent / maxValue) * 100}%` }}
                        />
                    </div>
                    <span className="text-xs text-gray-500">{item.day}</span>
                </div>
            ))}
        </div>
    );
}

// Course Enrollment Chart Component (replacing Earnings)
function EnrollmentChart({ data }: { data: typeof enrollmentData }) {
    const maxValue = 600;
    const width = 300;
    const height = 150;

    // Create path for enrollments
    const enrollmentPath = data.map((d, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - (d.enrollments / maxValue) * height;
        return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
    }).join(' ');

    // Create path for completions
    const completionPath = data.map((d, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - (d.completions / maxValue) * height;
        return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
    }).join(' ');

    // Create filled area path
    const enrollmentAreaPath = enrollmentPath + ` L ${width} ${height} L 0 ${height} Z`;
    const completionAreaPath = completionPath + ` L ${width} ${height} L 0 ${height} Z`;

    return (
        <div className="relative h-40">
            <svg viewBox={`0 0 ${width} ${height}`} className="w-full h-full" preserveAspectRatio="none">
                {/* Grid lines */}
                {[0, 25, 50, 75, 100].map((pct) => (
                    <line
                        key={pct}
                        x1="0"
                        y1={height - (pct / 100) * height}
                        x2={width}
                        y2={height - (pct / 100) * height}
                        stroke="#e5e7eb"
                        strokeWidth="1"
                        strokeDasharray="4"
                    />
                ))}

                {/* Completions area */}
                <path d={completionAreaPath} fill="rgba(196, 181, 253, 0.3)" />
                <path d={completionPath} fill="none" stroke="#a78bfa" strokeWidth="2" />

                {/* Enrollments area */}
                <path d={enrollmentAreaPath} fill="rgba(34, 211, 238, 0.3)" />
                <path d={enrollmentPath} fill="none" stroke="#22d3ee" strokeWidth="2" />

                {/* Tooltip indicator */}
                <circle cx={(4 / 6) * width} cy={height - (520 / maxValue) * height} r="4" fill="#22d3ee" />
            </svg>

            {/* Tooltip */}
            <div className="absolute top-4 right-20 bg-gray-800 text-white text-xs px-2 py-1 rounded">
                Enrollments<br />520
            </div>

            {/* X-axis labels */}
            <div className="flex justify-between text-xs text-gray-500 mt-2">
                <span>01</span>
                <span>05</span>
                <span>10</span>
                <span>15</span>
                <span>20</span>
                <span>25</span>
                <span>30</span>
            </div>
        </div>
    );
}

// Icons
const StudentsIcon = () => (
    <svg className="w-6 h-6 text-cyan-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
    </svg>
);

const TeacherIcon = () => (
    <svg className="w-6 h-6 text-amber-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
    </svg>
);

const EmployeeIcon = () => (
    <svg className="w-6 h-6 text-purple-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
    </svg>
);

const CoursesIcon = () => (
    <svg className="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/>
    </svg>
);

export default function Dashboard({ user, stats, themeConfig }: DashboardProps) {
    return (
        <div className="p-2 sm:p-4 lg:p-6 bg-gray-50 min-h-screen">
            {/* Stats Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <StatCard
                    icon={<StudentsIcon />}
                    value={stats.totalStudents.toLocaleString()}
                    label="Number of Students"
                    bgColor=""
                    customBgColor={themeConfig.statCard1Color}
                />
                <StatCard
                    icon={<TeacherIcon />}
                    value={stats.totalTeachers.toLocaleString()}
                    label="Number of Teachers"
                    bgColor=""
                    customBgColor={themeConfig.statCard2Color}
                />
                <StatCard
                    icon={<EmployeeIcon />}
                    value={stats.totalUsers.toLocaleString()}
                    label="Total Users"
                    bgColor=""
                    customBgColor={themeConfig.statCard3Color}
                />
                <StatCard
                    icon={<CoursesIcon />}
                    value={stats.totalCourses.toLocaleString()}
                    label="Total Courses"
                    bgColor=""
                    customBgColor={themeConfig.statCard4Color}
                />
            </div>

            {/* Middle Section: Students & Teacher List */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {/* Students Donut Chart */}
                <div className="bg-white rounded-xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-semibold text-gray-800">Students</h3>
                        <select className="border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option>Class 9</option>
                            <option>Class 8</option>
                            <option>Class 7</option>
                        </select>
                    </div>
                    <div className="flex items-center justify-center gap-8">
                        {/* Gender breakdown uses estimated 55/45 ratio - real data requires custom profile field */}
                        <DonutChart
                            total={stats.totalStudents}
                            boys={Math.round(stats.totalStudents * 0.55)}
                            girls={Math.round(stats.totalStudents * 0.45)}
                        />
                        <div className="space-y-3">
                            <div className="flex items-center gap-2">
                                <span className="w-3 h-3 rounded-full bg-cyan-400"></span>
                                <span className="text-sm text-gray-600">Boys : {Math.round(stats.totalStudents * 0.55).toLocaleString()}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="w-3 h-3 rounded-full bg-fuchsia-300"></span>
                                <span className="text-sm text-gray-600">Girls : {Math.round(stats.totalStudents * 0.45).toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Teacher List */}
                <div className="bg-white rounded-xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-semibold text-gray-800">Teacher List</h3>
                        <span className="text-sm text-gray-500">{stats.totalTeachers} total</span>
                    </div>
                    <div className="overflow-x-auto">
                        {stats.teachers.length > 0 ? (
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="text-left text-gray-500 border-b border-gray-100">
                                        <th className="pb-3 font-medium">Name</th>
                                        <th className="pb-3 font-medium">Email</th>
                                        <th className="pb-3 font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {stats.teachers.map((teacher) => (
                                        <tr key={teacher.id} className="border-b border-gray-50">
                                            <td className="py-3">
                                                <div className="flex items-center gap-2">
                                                    {teacher.avatar ? (
                                                        <div
                                                            className="w-8 h-8 rounded-full overflow-hidden"
                                                            dangerouslySetInnerHTML={{ __html: teacher.avatar }}
                                                        />
                                                    ) : (
                                                        <div className="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white text-xs font-medium">
                                                            {teacher.firstname.charAt(0)}{teacher.lastname.charAt(0)}
                                                        </div>
                                                    )}
                                                    <span className="text-gray-800">{teacher.fullname}</span>
                                                </div>
                                            </td>
                                            <td className="py-3 text-cyan-600">{teacher.email}</td>
                                            <td className="py-3">
                                                <a
                                                    href={`/user/profile.php?id=${teacher.id}`}
                                                    className="text-rtb-blue hover:underline text-sm"
                                                >
                                                    View Profile
                                                </a>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <div className="text-center py-8 text-gray-500">
                                <p>No teachers found</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Bottom Section: Attendance & Course Enrollments */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Attendance Chart */}
                <div className="bg-white rounded-xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-semibold text-gray-800">Attendance</h3>
                        <select className="border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option>This week</option>
                            <option>Last week</option>
                            <option>This month</option>
                        </select>
                    </div>
                    <div className="flex items-center gap-6 mb-4">
                        <div className="flex items-center gap-2">
                            <span className="w-3 h-3 rounded-full bg-cyan-400"></span>
                            <span className="text-sm text-gray-600">Total Present</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="w-3 h-3 rounded-full bg-purple-300"></span>
                            <span className="text-sm text-gray-600">Total Absent</span>
                        </div>
                    </div>
                    <div className="relative">
                        {/* Y-axis labels */}
                        <div className="absolute left-0 top-0 h-36 flex flex-col justify-between text-xs text-gray-400">
                            <span>100</span>
                            <span>75</span>
                            <span>50</span>
                            <span>25</span>
                            <span>0</span>
                        </div>
                        <div className="ml-8">
                            <AttendanceChart data={attendanceData} />
                        </div>
                    </div>
                </div>

                {/* Course Enrollments Chart */}
                <div className="bg-white rounded-xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-semibold text-gray-800">Course Activity</h3>
                        <select className="border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option>This month</option>
                            <option>Last month</option>
                            <option>Last 3 months</option>
                        </select>
                    </div>
                    <div className="flex items-center gap-6 mb-4">
                        <div className="flex items-center gap-2">
                            <span className="w-3 h-3 rounded-full bg-cyan-400"></span>
                            <span className="text-sm text-gray-600">Enrollments</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="w-3 h-3 rounded-full bg-purple-400"></span>
                            <span className="text-sm text-gray-600">Completions</span>
                        </div>
                    </div>
                    <div className="relative">
                        {/* Y-axis labels */}
                        <div className="absolute left-0 top-0 h-40 flex flex-col justify-between text-xs text-gray-400">
                            <span>600</span>
                            <span>450</span>
                            <span>300</span>
                            <span>150</span>
                            <span>0</span>
                        </div>
                        <div className="ml-10">
                            <EnrollmentChart data={enrollmentData} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
