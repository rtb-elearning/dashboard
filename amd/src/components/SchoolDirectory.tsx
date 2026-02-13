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
 * School directory component for Elby Dashboard.
 *
 * Displays school cards grid with province/district filters, search, and KPIs.
 *
 * @module     local_elby_dashboard/components/SchoolDirectory
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import { useState, useEffect } from 'preact/hooks';
import type { SchoolMetrics } from '../types';

// @ts-ignore - Moodle AMD module
declare const require: (deps: string[], callback: (...args: any[]) => void) => void;

interface SchoolCardData {
    school_code: string;
    school_name: string;
    region_code: string;
    total_enrolled: number;
    total_active: number;
    at_risk_count: number;
    avg_quiz_score: number | null;
    province: string;
    district: string;
}

function ajaxCall(methodname: string, args: Record<string, any>): Promise<any> {
    return new Promise((resolve, reject) => {
        require(['core/ajax'], (Ajax: any) => {
            Ajax.call([{ methodname, args }])[0].then(resolve).catch(reject);
        });
    });
}

function parseRegionCode(regionCode: string): { province: string; district: string } {
    if (!regionCode || regionCode.length < 2) {
        return { province: '', district: '' };
    }
    // Region code format: PP DD SS (province, district, sector digits)
    const province = regionCode.substring(0, 2);
    const district = regionCode.length >= 4 ? regionCode.substring(0, 4) : '';
    return { province, district };
}

// KPI Card
function KpiCard({ label, value, color }: { label: string; value: string | number; color: string }) {
    return (
        <div className={`rounded-xl p-4 ${color}`}>
            <p className="text-xs text-gray-600 font-medium">{label}</p>
            <p className="text-2xl font-bold text-gray-800">{value}</p>
        </div>
    );
}

// School Card
function SchoolCard({ school, onClick }: { school: SchoolCardData; onClick: () => void }) {
    return (
        <div
            className="bg-white rounded-xl p-5 shadow-sm border border-gray-100 cursor-pointer hover:shadow-md hover:border-gray-200 transition-all"
            onClick={onClick}
        >
            <h3 className="text-sm font-semibold text-gray-800 mb-1 truncate">{school.school_name}</h3>
            <p className="text-xs text-gray-500 mb-4">{school.school_code}</p>
            <div className="grid grid-cols-2 gap-3">
                <div>
                    <p className="text-xs text-gray-500">Enrolled</p>
                    <p className="text-lg font-bold text-gray-800">{school.total_enrolled}</p>
                </div>
                <div>
                    <p className="text-xs text-gray-500">Active</p>
                    <p className="text-lg font-bold text-green-600">{school.total_active}</p>
                </div>
                <div>
                    <p className="text-xs text-gray-500">At Risk</p>
                    <p className="text-lg font-bold text-red-500">{school.at_risk_count}</p>
                </div>
                <div>
                    <p className="text-xs text-gray-500">Avg Score</p>
                    <p className="text-lg font-bold text-blue-600">
                        {school.avg_quiz_score !== null ? `${school.avg_quiz_score}%` : 'N/A'}
                    </p>
                </div>
            </div>
        </div>
    );
}

export default function SchoolDirectory() {
    const [schools, setSchools] = useState<SchoolCardData[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [provinceFilter, setProvinceFilter] = useState('');
    const [districtFilter, setDistrictFilter] = useState('');
    const [provinces, setProvinces] = useState<string[]>([]);
    const [districts, setDistricts] = useState<string[]>([]);

    useEffect(() => {
        loadSchools();
    }, []);

    async function loadSchools() {
        try {
            setLoading(true);
            // Fetch all schools with their metrics via the school metrics API.
            const result = await ajaxCall('local_elby_dashboard_get_student_list', {
                school_code: '',
                courseid: 0,
                perpage: 1,
            });

            // For the directory we need the school list from the DB.
            // Use a different approach: fetch schools directly.
            const schoolsData = await fetchSchoolsFromDb();
            setSchools(schoolsData);

            // Extract unique provinces and districts.
            const provSet = new Set<string>();
            const distSet = new Set<string>();
            schoolsData.forEach(s => {
                if (s.province) provSet.add(s.province);
                if (s.district) distSet.add(s.district);
            });
            setProvinces(Array.from(provSet).sort());
            setDistricts(Array.from(distSet).sort());
        } catch (err) {
            console.error('Failed to load schools:', err);
        } finally {
            setLoading(false);
        }
    }

    async function fetchSchoolsFromDb(): Promise<SchoolCardData[]> {
        // Fetch school list via get_school_info for each school, or use a custom approach.
        // For efficiency, we use the pre-loaded data from PHP if available.
        const container = document.getElementById('elby-dashboard-root');
        const schoolsDataAttr = container?.getAttribute('data-schools');
        if (schoolsDataAttr) {
            try {
                return JSON.parse(schoolsDataAttr);
            } catch (e) {
                // Fall through.
            }
        }

        // Fallback: fetch each school's metrics individually.
        // This is less efficient but works without pre-loaded data.
        return [];
    }

    function handleSchoolClick(schoolCode: string) {
        window.location.href = `/local/elby_dashboard/schools.php?school_code=${encodeURIComponent(schoolCode)}&view=detail`;
    }

    const filteredSchools = schools.filter(school => {
        if (searchQuery) {
            const q = searchQuery.toLowerCase();
            if (!school.school_name.toLowerCase().includes(q) &&
                !school.school_code.toLowerCase().includes(q)) {
                return false;
            }
        }
        if (provinceFilter && school.province !== provinceFilter) return false;
        if (districtFilter && school.district !== districtFilter) return false;
        return true;
    });

    // Compute aggregate KPIs.
    const totalEnrolled = filteredSchools.reduce((sum, s) => sum + s.total_enrolled, 0);
    const totalActive = filteredSchools.reduce((sum, s) => sum + s.total_active, 0);
    const totalAtRisk = filteredSchools.reduce((sum, s) => sum + s.at_risk_count, 0);
    const schoolsWithScores = filteredSchools.filter(s => s.avg_quiz_score !== null);
    const avgScore = schoolsWithScores.length > 0
        ? Math.round(schoolsWithScores.reduce((sum, s) => sum + (s.avg_quiz_score || 0), 0) / schoolsWithScores.length)
        : 0;

    // Available districts filtered by selected province.
    const filteredDistricts = provinceFilter
        ? districts.filter(d => d.startsWith(provinceFilter))
        : districts;

    if (loading) {
        return (
            <div className="p-6">
                <div className="animate-pulse space-y-4">
                    <div className="h-8 bg-gray-200 rounded w-48"></div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {[1, 2, 3, 4].map(i => (
                            <div key={i} className="h-20 bg-gray-200 rounded-xl"></div>
                        ))}
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {[1, 2, 3, 4, 5, 6].map(i => (
                            <div key={i} className="h-40 bg-gray-200 rounded-xl"></div>
                        ))}
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="p-4 lg:p-6">
            {/* KPI Cards */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <KpiCard label="Total Schools" value={filteredSchools.length} color="bg-blue-50" />
                <KpiCard label="Total Enrolled" value={totalEnrolled.toLocaleString()} color="bg-cyan-50" />
                <KpiCard label="Active Students" value={totalActive.toLocaleString()} color="bg-green-50" />
                <KpiCard label="At Risk" value={totalAtRisk.toLocaleString()} color="bg-red-50" />
            </div>

            {/* Filters */}
            <div className="bg-white rounded-xl p-4 shadow-sm mb-6">
                <div className="flex flex-wrap items-center gap-3">
                    {/* Search */}
                    <div className="flex-1 min-w-[200px]">
                        <input
                            type="text"
                            placeholder="Search schools..."
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value={searchQuery}
                            onInput={(e) => setSearchQuery((e.target as HTMLInputElement).value)}
                        />
                    </div>

                    {/* Province filter */}
                    <select
                        className="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value={provinceFilter}
                        onChange={(e) => {
                            setProvinceFilter((e.target as HTMLSelectElement).value);
                            setDistrictFilter('');
                        }}
                    >
                        <option value="">All Provinces</option>
                        {provinces.map(p => (
                            <option key={p} value={p}>Province {p}</option>
                        ))}
                    </select>

                    {/* District filter */}
                    <select
                        className="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value={districtFilter}
                        onChange={(e) => setDistrictFilter((e.target as HTMLSelectElement).value)}
                    >
                        <option value="">All Districts</option>
                        {filteredDistricts.map(d => (
                            <option key={d} value={d}>District {d}</option>
                        ))}
                    </select>
                </div>
            </div>

            {/* School Cards Grid */}
            {filteredSchools.length === 0 ? (
                <div className="text-center py-12 text-gray-500">
                    <svg className="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p>No schools found</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {filteredSchools.map(school => (
                        <SchoolCard
                            key={school.school_code}
                            school={school}
                            onClick={() => handleSchoolClick(school.school_code)}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
