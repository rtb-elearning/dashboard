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
 * Traffic report component for Elby Dashboard.
 *
 * Shows daily/weekly/monthly platform traffic trends as a bar chart.
 *
 * @module     local_elby_dashboard/components/TrafficReport
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import { useState, useEffect } from 'preact/hooks';
import type { TrafficDataPoint } from '../types';

// @ts-ignore
declare const require: (deps: string[], callback: (...args: any[]) => void) => void;

function ajaxCall(methodname: string, args: Record<string, any>): Promise<any> {
    return new Promise((resolve, reject) => {
        require(['core/ajax'], (Ajax: any) => {
            Ajax.call([{ methodname, args }])[0].then(resolve).catch(reject);
        });
    });
}

type Period = 'daily' | 'weekly' | 'monthly';

export default function TrafficReport() {
    const [data, setData] = useState<TrafficDataPoint[]>([]);
    const [loading, setLoading] = useState(true);
    const [period, setPeriod] = useState<Period>('daily');
    const [daysBack, setDaysBack] = useState(30);

    useEffect(() => {
        loadTraffic();
    }, [period, daysBack]);

    async function loadTraffic() {
        try {
            setLoading(true);
            const result = await ajaxCall('local_elby_dashboard_get_platform_traffic', {
                period,
                days_back: daysBack,
            });
            setData(result.data || []);
        } catch (err) {
            console.error('Failed to load traffic data:', err);
        } finally {
            setLoading(false);
        }
    }

    // Compute max for bar scaling.
    const maxActions = Math.max(...data.map(d => d.total_actions), 1);
    const maxUsers = Math.max(...data.map(d => d.unique_users), 1);
    const totalActions = data.reduce((sum, d) => sum + d.total_actions, 0);
    const totalUniqueUsers = data.length > 0 ? Math.max(...data.map(d => d.unique_users)) : 0;
    const avgActions = data.length > 0 ? Math.round(totalActions / data.length) : 0;

    function handleExportCsv() {
        const headers = ['Period', 'Total Actions', 'Unique Users'];
        const rows = data.map(d => [d.period_label, String(d.total_actions), String(d.unique_users)]);
        const csv = [headers, ...rows].map(r => r.map(c => `"${c}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `traffic_${period}_${daysBack}d.csv`;
        a.click();
        URL.revokeObjectURL(url);
    }

    if (loading) {
        return (
            <div className="p-6">
                <div className="animate-pulse space-y-4">
                    <div className="h-8 bg-gray-200 rounded w-48"></div>
                    <div className="grid grid-cols-3 gap-4">
                        {[1, 2, 3].map(i => <div key={i} className="h-20 bg-gray-200 rounded-xl"></div>)}
                    </div>
                    <div className="h-64 bg-gray-200 rounded-xl"></div>
                </div>
            </div>
        );
    }

    return (
        <div className="p-4 lg:p-6">
            {/* Summary Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div className="bg-white rounded-xl p-4 shadow-sm">
                    <p className="text-xs text-gray-500 font-medium">Total Actions</p>
                    <p className="text-2xl font-bold text-gray-800">{totalActions.toLocaleString()}</p>
                </div>
                <div className="bg-white rounded-xl p-4 shadow-sm">
                    <p className="text-xs text-gray-500 font-medium">Peak Unique Users</p>
                    <p className="text-2xl font-bold text-blue-600">{totalUniqueUsers.toLocaleString()}</p>
                </div>
                <div className="bg-white rounded-xl p-4 shadow-sm">
                    <p className="text-xs text-gray-500 font-medium">Avg Actions / {period === 'daily' ? 'Day' : period === 'weekly' ? 'Week' : 'Month'}</p>
                    <p className="text-2xl font-bold text-green-600">{avgActions.toLocaleString()}</p>
                </div>
            </div>

            {/* Controls */}
            <div className="bg-white rounded-xl p-4 shadow-sm mb-6">
                <div className="flex flex-wrap items-center gap-3">
                    <div className="flex gap-1 bg-gray-100 rounded-lg p-1">
                        {(['daily', 'weekly', 'monthly'] as Period[]).map(p => (
                            <button
                                key={p}
                                onClick={() => setPeriod(p)}
                                className={`px-3 py-1.5 text-sm rounded-md transition-colors ${
                                    period === p ? 'bg-white shadow-sm text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800'
                                }`}
                            >
                                {p.charAt(0).toUpperCase() + p.slice(1)}
                            </button>
                        ))}
                    </div>

                    <select
                        className="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value={daysBack}
                        onChange={(e) => setDaysBack(Number((e.target as HTMLSelectElement).value))}
                    >
                        <option value={7}>Last 7 days</option>
                        <option value={30}>Last 30 days</option>
                        <option value={90}>Last 90 days</option>
                        <option value={180}>Last 6 months</option>
                        <option value={365}>Last year</option>
                    </select>

                    <button
                        onClick={handleExportCsv}
                        className="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 ml-auto"
                    >
                        Export CSV
                    </button>
                </div>
            </div>

            {/* Chart */}
            <div className="bg-white rounded-xl p-4 shadow-sm">
                {data.length === 0 ? (
                    <div className="text-center py-12 text-gray-500">No traffic data available for this period</div>
                ) : (
                    <div>
                        {/* Legend */}
                        <div className="flex items-center gap-4 mb-4 text-xs text-gray-500">
                            <div className="flex items-center gap-1">
                                <div className="w-3 h-3 rounded bg-blue-500"></div>
                                <span>Total Actions</span>
                            </div>
                            <div className="flex items-center gap-1">
                                <div className="w-3 h-3 rounded bg-green-500"></div>
                                <span>Unique Users</span>
                            </div>
                        </div>

                        {/* Bar chart */}
                        <div className="overflow-x-auto">
                            <div className="flex items-end gap-1 min-w-fit" style={{ height: '240px' }}>
                                {data.map((d, i) => (
                                    <div key={i} className="flex flex-col items-center gap-1 flex-1 min-w-[24px] max-w-[48px]" style={{ height: '100%' }}>
                                        <div className="flex-1 flex items-end gap-0.5 w-full">
                                            <div
                                                className="flex-1 bg-blue-500 rounded-t opacity-80 hover:opacity-100 transition-opacity"
                                                style={{ height: `${(d.total_actions / maxActions) * 100}%`, minHeight: '2px' }}
                                                title={`${d.period_label}: ${d.total_actions.toLocaleString()} actions`}
                                            />
                                            <div
                                                className="flex-1 bg-green-500 rounded-t opacity-80 hover:opacity-100 transition-opacity"
                                                style={{ height: `${(d.unique_users / maxActions) * 100}%`, minHeight: '2px' }}
                                                title={`${d.period_label}: ${d.unique_users.toLocaleString()} users`}
                                            />
                                        </div>
                                        <span className="text-[9px] text-gray-400 truncate w-full text-center" title={d.period_label}>
                                            {d.period_label.length > 5 ? d.period_label.slice(-5) : d.period_label}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Table fallback */}
                        <details className="mt-4">
                            <summary className="text-sm text-gray-500 cursor-pointer hover:text-gray-700">Show data table</summary>
                            <table className="w-full text-sm mt-2">
                                <thead>
                                    <tr className="text-left text-gray-500 border-b border-gray-100">
                                        <th className="pb-2 font-medium">Period</th>
                                        <th className="pb-2 font-medium text-right">Actions</th>
                                        <th className="pb-2 font-medium text-right">Unique Users</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.map((d, i) => (
                                        <tr key={i} className="border-b border-gray-50">
                                            <td className="py-2 text-gray-700">{d.period_label}</td>
                                            <td className="py-2 text-right text-gray-800">{d.total_actions.toLocaleString()}</td>
                                            <td className="py-2 text-right text-gray-800">{d.unique_users.toLocaleString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </details>
                    </div>
                )}
            </div>
        </div>
    );
}
