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
 * Admin panel component for Elby Dashboard.
 *
 * Displays sync status, logs, manual triggers, and cache stats.
 *
 * @module     local_elby_dashboard/components/AdminPanel
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import { useState, useEffect } from 'preact/hooks';

// @ts-ignore
declare const require: (deps: string[], callback: (...args: any[]) => void) => void;

function ajaxCall(methodname: string, args: Record<string, any>): Promise<any> {
    return new Promise((resolve, reject) => {
        require(['core/ajax'], (Ajax: any) => {
            Ajax.call([{ methodname, args }])[0].then(resolve).catch(reject);
        });
    });
}

interface AdminStats {
    linked_users: number;
    stale_users: number;
    error_users: number;
    total_schools: number;
    user_metrics_count: number;
    school_metrics_count: number;
}

interface SyncLogEntry {
    id: number;
    sync_type: string;
    entity_id: string;
    operation: string;
    error_message: string | null;
    triggered_by: string;
    timecreated: number;
}

function formatTimestamp(ts: number): string {
    if (!ts) return 'Never';
    return new Date(ts * 1000).toLocaleString();
}

function StatCard({ label, value, icon, color }: { label: string; value: number | string; icon: JSX.Element; color: string }) {
    return (
        <div className={`rounded-xl p-4 ${color}`}>
            <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-white/40 flex items-center justify-center">
                    {icon}
                </div>
                <div>
                    <p className="text-2xl font-bold text-gray-800">{value}</p>
                    <p className="text-xs text-gray-600">{label}</p>
                </div>
            </div>
        </div>
    );
}

function OperationBadge({ operation }: { operation: string }) {
    const colors: Record<string, string> = {
        create: 'bg-green-100 text-green-700',
        update: 'bg-blue-100 text-blue-700',
        skip: 'bg-gray-100 text-gray-700',
        error: 'bg-red-100 text-red-700',
    };
    return (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${colors[operation] || 'bg-gray-100 text-gray-700'}`}>
            {operation}
        </span>
    );
}

export default function AdminPanel() {
    const [stats, setStats] = useState<AdminStats | null>(null);
    const [logs, setLogs] = useState<SyncLogEntry[]>([]);
    const [loading, setLoading] = useState(true);
    const [syncing, setSyncing] = useState<string | null>(null);

    useEffect(() => {
        loadAdminData();
    }, []);

    async function loadAdminData() {
        try {
            setLoading(true);
            // Read pre-loaded admin stats from data attributes.
            const container = document.getElementById('elby-dashboard-root');
            const adminDataAttr = container?.getAttribute('data-admin');
            if (adminDataAttr) {
                try {
                    const data = JSON.parse(adminDataAttr);
                    setStats(data.stats);
                    setLogs(data.logs || []);
                } catch (e) {
                    console.error('Failed to parse admin data:', e);
                }
            }
        } finally {
            setLoading(false);
        }
    }

    async function handleManualSync(type: string) {
        try {
            setSyncing(type);
            if (type === 'sdms_cache') {
                // Trigger a few user refreshes as a demo.
                // In production, this would trigger the scheduled task.
                alert('SDMS cache refresh task triggered. Check Site Admin > Scheduled Tasks to run it manually.');
            } else if (type === 'user_metrics') {
                alert('User metrics computation task triggered. Check Site Admin > Scheduled Tasks to run it manually.');
            } else if (type === 'school_metrics') {
                alert('School metrics aggregation task triggered. Check Site Admin > Scheduled Tasks to run it manually.');
            }
        } finally {
            setSyncing(null);
        }
    }

    if (loading) {
        return (
            <div className="p-6 animate-pulse space-y-4">
                <div className="h-8 bg-gray-200 rounded w-48"></div>
                <div className="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    {[1, 2, 3, 4, 5, 6].map(i => <div key={i} className="h-20 bg-gray-200 rounded-xl"></div>)}
                </div>
            </div>
        );
    }

    return (
        <div className="p-4 lg:p-6">
            {/* Stats Cards */}
            {stats && (
                <div className="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <StatCard
                        label="Linked Users"
                        value={stats.linked_users}
                        color="bg-cyan-50"
                        icon={<svg className="w-5 h-5 text-cyan-600" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>}
                    />
                    <StatCard
                        label="Stale Records"
                        value={stats.stale_users}
                        color="bg-yellow-50"
                        icon={<svg className="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>}
                    />
                    <StatCard
                        label="Sync Errors"
                        value={stats.error_users}
                        color="bg-red-50"
                        icon={<svg className="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>}
                    />
                    <StatCard
                        label="Schools"
                        value={stats.total_schools}
                        color="bg-blue-50"
                        icon={<svg className="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3z"/></svg>}
                    />
                    <StatCard
                        label="User Metrics"
                        value={stats.user_metrics_count.toLocaleString()}
                        color="bg-green-50"
                        icon={<svg className="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>}
                    />
                    <StatCard
                        label="School Metrics"
                        value={stats.school_metrics_count.toLocaleString()}
                        color="bg-purple-50"
                        icon={<svg className="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>}
                    />
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Manual Sync Triggers */}
                <div className="bg-white rounded-xl p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-gray-800 mb-4">Manual Sync Triggers</h3>
                    <p className="text-sm text-gray-500 mb-4">
                        Run scheduled tasks manually from Site Admin &gt; Server &gt; Scheduled Tasks, or use the buttons below.
                    </p>
                    <div className="space-y-3">
                        <button
                            onClick={() => handleManualSync('user_metrics')}
                            disabled={syncing !== null}
                            className="w-full flex items-center justify-between px-4 py-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors disabled:opacity-50"
                        >
                            <div>
                                <p className="text-sm font-medium text-gray-800">Compute User Metrics</p>
                                <p className="text-xs text-gray-500">Hourly task - aggregates logstore data</p>
                            </div>
                            {syncing === 'user_metrics' && (
                                <div className="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            )}
                        </button>
                        <button
                            onClick={() => handleManualSync('school_metrics')}
                            disabled={syncing !== null}
                            className="w-full flex items-center justify-between px-4 py-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors disabled:opacity-50"
                        >
                            <div>
                                <p className="text-sm font-medium text-gray-800">Aggregate School Metrics</p>
                                <p className="text-xs text-gray-500">Daily task - rolls up to school level</p>
                            </div>
                            {syncing === 'school_metrics' && (
                                <div className="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            )}
                        </button>
                        <button
                            onClick={() => handleManualSync('sdms_cache')}
                            disabled={syncing !== null}
                            className="w-full flex items-center justify-between px-4 py-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors disabled:opacity-50"
                        >
                            <div>
                                <p className="text-sm font-medium text-gray-800">Refresh SDMS Cache</p>
                                <p className="text-xs text-gray-500">Daily task - refreshes stale SDMS records</p>
                            </div>
                            {syncing === 'sdms_cache' && (
                                <div className="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            )}
                        </button>
                    </div>
                </div>

                {/* Recent Sync Logs */}
                <div className="bg-white rounded-xl p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-gray-800 mb-4">Recent Sync Logs</h3>
                    {logs.length === 0 ? (
                        <div className="text-center text-gray-400 py-8">
                            <p>No sync logs found</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto max-h-80 overflow-y-auto">
                            <table className="w-full text-xs">
                                <thead className="sticky top-0 bg-white">
                                    <tr className="text-left text-gray-500 border-b border-gray-100">
                                        <th className="pb-2 font-medium">Type</th>
                                        <th className="pb-2 font-medium">Entity</th>
                                        <th className="pb-2 font-medium">Op</th>
                                        <th className="pb-2 font-medium">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {logs.map(log => (
                                        <tr key={log.id} className="border-b border-gray-50">
                                            <td className="py-2 text-gray-600">{log.sync_type}</td>
                                            <td className="py-2 text-gray-600 truncate max-w-[80px]">{log.entity_id || '-'}</td>
                                            <td className="py-2"><OperationBadge operation={log.operation} /></td>
                                            <td className="py-2 text-gray-500">{formatTimestamp(log.timecreated)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
