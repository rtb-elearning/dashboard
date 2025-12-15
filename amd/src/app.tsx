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
 * Root Preact application component for RTB Dashboard.
 *
 * @module     local_rtbdashboard/app
 * @copyright  2025 Rwanda TVET Board
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import './styles.css';
import Dashboard from './components/Dashboard';
import Sidebar from './components/Sidebar';
import CompletionReport from './components/CompletionReport';
import type { UserData, StatsData, PageId } from './types';

interface AppProps {
    user: UserData;
    stats: StatsData;
    activePage: PageId;
}

// Header component with search and user profile
function Header({ user, activePage }: { user: UserData; activePage: PageId }) {
    const pageTitle = activePage === 'home' ? 'Dashboard' :
                      activePage === 'completion' ? 'Completion Report' : 'Dashboard';

    return (
        <header className="bg-white border-b border-gray-100 px-6 py-4">
            <div className="flex items-center justify-between">
                {/* Page Title */}
                <h1 className="text-xl font-semibold text-gray-800">{pageTitle}</h1>

                <div className="flex items-center gap-6">
                    {/* Search Bar */}
                    <div className="relative">
                        <svg
                            className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                        >
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input
                            type="text"
                            placeholder="Search"
                            className="pl-10 pr-4 py-2 w-64 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                    </div>

                    {/* Notification Bell */}
                    <button className="relative text-gray-500 hover:text-gray-700">
                        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span className="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    {/* User Profile */}
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-semibold text-sm overflow-hidden">
                            {user.avatar ? (
                                <img src={user.avatar} alt={user.fullname} className="w-full h-full object-cover" />
                            ) : (
                                <span>{user.firstname.charAt(0)}{user.lastname.charAt(0)}</span>
                            )}
                        </div>
                        <div className="hidden sm:block">
                            <p className="text-sm font-semibold text-gray-800">{user.fullname}</p>
                            <p className="text-xs text-gray-500">
                                {user.isAdmin ? 'Admin' : user.roles.includes('teacher') ? 'Teacher' : 'User'}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    );
}

export default function App({ user, stats, activePage }: AppProps) {
    return (
        <div className="flex min-h-screen bg-gray-50">
            <Sidebar activePage={activePage} />
            <div className="flex-1 flex flex-col overflow-hidden">
                <Header user={user} activePage={activePage} />
                <main className="flex-1 overflow-auto">
                    {activePage === 'home' && <Dashboard user={user} stats={stats} />}
                    {activePage === 'completion' && <CompletionReport />}
                </main>
            </div>
        </div>
    );
}
