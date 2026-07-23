<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feature Heatmap Sentinel</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                        }
                    }
                }
            }
        }
    </script>

    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        /* Custom scrollbar adjustments */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        
        /* Custom styles for Matrix cells spacing */
        table.matrix-table {
            border-collapse: separate;
            border-spacing: 3px;
        }
        
        /* Tooltip style */
        #futuristic-tooltip {
            position: absolute;
            pointer-events: none;
            z-index: 9999;
            display: none;
            transition: opacity 0.1s ease;
        }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans transition-colors duration-200">

    <script>
        // Set initial theme state from localStorage before rendering starts
        if (localStorage.getItem('feature-sentinel-theme') === 'dark' || 
            (!localStorage.getItem('feature-sentinel-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <div class="min-h-full flex flex-col max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-slate-200 dark:border-slate-800 mb-8">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-chart-simple text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Feature Sentinel</h1>
                    <p class="text-xs font-mono text-slate-500 dark:text-slate-400">AUTOMATED CONTROLLER METRICS PIPELINE</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 self-stretch sm:self-auto justify-between">
                <button onclick="toggleTheme()" class="h-9 w-9 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-all">
                    <i class="fa-solid fa-sun hidden dark:inline-block text-sm"></i>
                    <i class="fa-solid fa-moon inline-block dark:hidden text-sm"></i>
                </button>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium font-mono bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Pipeline Fallback
                </span>
            </div>
        </header>

        <!-- Filters & Search Toolbar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5 gap-2">
                    <span class="text-xs font-mono text-slate-400 uppercase">From</span>
                    <input type="date" id="from" value="{{ $from }}" class="bg-transparent border-none text-sm text-slate-900 dark:text-white outline-none focus:ring-0 w-32 color-scheme-dark">
                </div>
                <div class="flex items-center bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5 gap-2">
                    <span class="text-xs font-mono text-slate-400 uppercase">To</span>
                    <input type="date" id="to" value="{{ $to }}" class="bg-transparent border-none text-sm text-slate-900 dark:text-white outline-none focus:ring-0 w-32 color-scheme-dark">
                </div>
                <button onclick="loadData()" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <i class="fa-solid fa-rotate"></i> Sync
                </button>
            </div>
            
            <div class="relative min-w-[260px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="feature-search" placeholder="Filter controller action..." oninput="filterHeatmap()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg pl-9 pr-4 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-brand-500 dark:focus:border-brand-500 transition-all">
            </div>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Card 1 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[145px] relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Actions Logged</span>
                    <span class="text-slate-400 bg-slate-50 dark:bg-slate-950 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800"><i class="fa-solid fa-arrow-pointer"></i></span>
                </div>
                <div class="my-2">
                    <div class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight" id="stat-total-hits">0</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5" id="stat-avg-hits">Avg: 0 / day</div>
                </div>
                <div id="sparkline-total" class="h-10 mt-auto -mx-5 -mb-5 overflow-hidden"></div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[145px] relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Active Features</span>
                    <span class="text-slate-400 bg-slate-50 dark:bg-slate-950 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800"><i class="fa-solid fa-cubes"></i></span>
                </div>
                <div class="my-2">
                    <div class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight" id="stat-active-count">0</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">Distinct endpoints</div>
                </div>
                <div id="sparkline-active" class="h-10 mt-auto -mx-5 -mb-5 overflow-hidden"></div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[145px] relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Unused Features</span>
                    <span class="text-slate-400 bg-slate-50 dark:bg-slate-950 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800"><i class="fa-solid fa-ghost"></i></span>
                </div>
                <div class="my-2">
                    <div class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight" id="stat-unused-count">0</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">Unused since last 30 days</div>
                </div>
                <div id="sparkline-unused" class="h-10 mt-auto -mx-5 -mb-5 overflow-hidden"></div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between min-h-[145px] relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Peak Load Day</span>
                    <span class="text-slate-400 bg-slate-50 dark:bg-slate-950 p-1.5 rounded-lg border border-slate-100 dark:border-slate-800"><i class="fa-solid fa-bolt"></i></span>
                </div>
                <div class="my-2">
                    <div class="text-lg font-bold text-slate-900 dark:text-white tracking-tight truncate" id="stat-peak-day">N/A</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5" id="stat-peak-count">Max: 0 actions</div>
                </div>
                <div id="sparkline-peak" class="h-10 mt-auto -mx-5 -mb-5 overflow-hidden"></div>
            </div>
        </div>

        <!-- Navigation Tabs (Headless-style pills) -->
        <div class="flex border-b border-slate-250 dark:border-slate-800 mb-6 gap-2">
            <button id="btn-tab-heatmap" onclick="switchTab('tab-heatmap', this)" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-brand-500 text-brand-600 dark:text-brand-400 focus:outline-none">
                <i class="fa-solid fa-border-all mr-1.5"></i> Heatmaps Matrix
            </button>
            <button id="btn-tab-analytics" onclick="switchTab('tab-analytics', this)" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none">
                <i class="fa-solid fa-chart-line mr-1.5"></i> Activity Analytics
            </button>
            <button id="btn-tab-unused" onclick="switchTab('tab-unused', this)" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none">
                <i class="fa-solid fa-ban mr-1.5"></i> Unused Features
            </button>
            <button id="btn-tab-users" onclick="switchTab('tab-users', this); lazyLoadUsers()" class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none">
                <i class="fa-solid fa-users mr-1.5"></i> Users Tracking
            </button>
        </div>

        <!-- Main Display Content (With Loading State Overlay) -->
        <div class="relative flex-grow min-h-[350px]">
            <!-- Dynamic Spinner Overlay -->
            <div id="loading-spinner" class="absolute inset-0 bg-slate-50/70 dark:bg-slate-950/70 backdrop-blur-sm z-50 flex flex-col justify-center items-center gap-3 opacity-0 pointer-events-none transition-all duration-200 rounded-2xl">
                <div class="h-10 w-10 border-4 border-slate-200 dark:border-slate-800 border-t-brand-600 rounded-full animate-spin"></div>
                <span class="text-sm font-mono text-brand-600 dark:text-brand-400">SYNCING PIPELINE MATRIX...</span>
            </div>

            <!-- Heatmaps Tab Content -->
            <div id="tab-heatmap" class="tab-content block">
                <!-- Heatmap: Timeline -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm mb-6">
                    <h2 class="text-sm font-semibold uppercase text-slate-500 dark:text-slate-400 tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calendar text-brand-500"></i> Dimension: Feature × Timeline
                    </h2>
                    <div id="heatmap-feature-date" class="overflow-x-auto"></div>
                </div>

                <!-- Heatmap: User -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase text-slate-500 dark:text-slate-400 tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-users text-brand-500"></i> Dimension: Feature × Operator (User)
                    </h2>
                    <div id="heatmap-feature-user" class="overflow-x-auto"></div>
                </div>
            </div>

            <!-- Analytics Tab Content -->
            <div id="tab-analytics" class="tab-content hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-sm font-semibold uppercase text-slate-500 dark:text-slate-400 tracking-wider mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-wave-square text-brand-500"></i> Overall Activity Waveform
                        </h2>
                        <div id="chart-timeline"></div>
                    </div>
                    
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-sm font-semibold uppercase text-slate-500 dark:text-slate-400 tracking-wider mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-chart-bar text-brand-500"></i> Action Usage Distribution
                        </h2>
                        <div id="chart-distribution"></div>
                    </div>
                </div>
            </div>

            <!-- Unused Features Tab Content -->
            <div id="tab-unused" class="tab-content hidden">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase text-red-500 dark:text-red-400 tracking-wider mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-ban"></i> Unused Features (Not used in the last 30 days)
                    </h2>
                    <div id="unused-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <!-- Unused item badges injected here -->
                    </div>
                </div>
            </div>

            <!-- ═══════════════════ USERS TRACKING TAB ═══════════════════ -->
            <div id="tab-users" class="tab-content hidden">

                <!-- Users Table Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">

                    <!-- Card Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h2 class="text-sm font-semibold uppercase text-slate-500 dark:text-slate-400 tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-users text-brand-500"></i> Tracked Users &mdash; Activity Overview
                        </h2>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                type="text"
                                id="users-search"
                                placeholder="Search user or email…"
                                oninput="filterUsersTable()"
                                class="w-full sm:w-56 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg pl-8 pr-3 py-1.5 text-sm text-slate-900 dark:text-white outline-none focus:border-brand-500 transition-all"
                            >
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="users-table">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950 text-left">
                                    <th class="px-6 py-3 text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider">User</th>
                                    <th class="px-4 py-3 text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider text-right">Total Hits</th>
                                    <th class="px-4 py-3 text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider text-right">Distinct Features</th>
                                    <th class="px-4 py-3 text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider">Last Seen</th>
                                    <th class="px-4 py-3 text-xs font-mono font-semibold text-slate-400 uppercase tracking-wider text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-tbody">
                                <tr>
                                    <td colspan="6" class="text-center py-12 font-mono text-xs text-slate-400">
                                        <i class="fa-solid fa-spinner animate-spin mr-2"></i> Loading users…
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ══════════════════════════════════════════════════════════ -->

            <!-- Empty State / Setup Guide Diagnostics -->
            <div id="dashboard-empty-state" class="hidden max-w-2xl mx-auto py-12 text-center">
                <div class="h-16 w-16 bg-amber-50 dark:bg-amber-950/20 text-amber-500 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center rounded-2xl mx-auto text-2xl mb-4">
                    <i class="fa-solid fa-triangle-exclamation animate-bounce"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-950 dark:text-white mb-2">No Active Logs Detected</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Database tables are online, but no metrics are tracked. Verify that middleware logs traffic or sync logs manually below.</p>
                
                <div class="bg-slate-100/50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 text-left">
                    <h4 class="text-xs font-mono font-bold text-brand-600 dark:text-brand-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-terminal"></i> Sentinel Setup Diagnostics
                    </h4>
                    <div class="space-y-5 text-sm">
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white mb-1">1. Route Middleware Binding</div>
                            <span class="text-slate-500 dark:text-slate-400 text-xs">Verify that <code>track.feature</code> middleware is applied inside your routing files or Kernel:</span>
                            <pre class="bg-slate-200/50 dark:bg-slate-950 border border-slate-300/50 dark:border-slate-850 rounded-lg p-3 text-xs font-mono text-indigo-600 dark:text-indigo-400 mt-2 select-all overflow-x-auto">Route::middleware('track.feature')->group(function () { ... });</pre>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white mb-1">2. Laravel Queue Worker status</div>
                            <span class="text-slate-500 dark:text-slate-400 text-xs">By default, logs are buffered via Queues. Run a worker or switch queue writing off inside your <code>.env</code> file:</span>
                            <pre class="bg-slate-200/50 dark:bg-slate-950 border border-slate-300/50 dark:border-slate-850 rounded-lg p-3 text-xs font-mono text-indigo-600 dark:text-indigo-400 mt-2 select-all overflow-x-auto">FEATURE_HEATMAP_USE_QUEUE=false</pre>
                            <span class="text-slate-500 dark:text-slate-400 text-xs mt-1 block">Or execute the worker console thread:</span>
                            <pre class="bg-slate-200/50 dark:bg-slate-950 border border-slate-300/50 dark:border-slate-850 rounded-lg p-3 text-xs font-mono text-indigo-600 dark:text-indigo-400 mt-1 select-all overflow-x-auto">php artisan queue:work</pre>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white mb-1">3. Manual Log Aggregation sync</div>
                            <span class="text-slate-500 dark:text-slate-400 text-xs">Process logs manually by running:</span>
                            <pre class="bg-slate-200/50 dark:bg-slate-950 border border-slate-300/50 dark:border-slate-850 rounded-lg p-3 text-xs font-mono text-indigo-600 dark:text-indigo-400 mt-2 select-all overflow-x-auto">php artisan feature-heatmap:aggregate</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════ USER DETAIL SLIDE-OVER PANEL ═══════════ -->
    <div id="user-panel-backdrop" onclick="closeUserPanel()" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden transition-opacity duration-200"></div>

    <aside id="user-panel" class="fixed top-0 right-0 h-full w-full max-w-2xl z-50 bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl translate-x-full transition-transform duration-300 ease-in-out flex flex-col">

        <!-- Panel Header -->
        <div class="flex items-start justify-between px-6 py-5 border-b border-slate-200 dark:border-slate-800 shrink-0">
            <div class="flex items-center gap-4">
                <div id="panel-avatar" class="h-12 w-12 rounded-xl bg-brand-600 flex items-center justify-center text-white text-lg font-bold shadow-lg shadow-brand-500/20">?</div>
                <div>
                    <div id="panel-name" class="text-base font-bold text-slate-900 dark:text-white">Loading…</div>
                    <div id="panel-email" class="text-xs text-slate-400 font-mono mt-0.5"></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="panel-download-btn" onclick="" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all">
                    <i class="fa-solid fa-file-csv"></i> Download CSV
                </button>
                <button onclick="closeUserPanel()" class="h-8 w-8 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition-all">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Panel Body (scrollable) -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

            <!-- Top Features Bar Chart -->
            <div>
                <h3 class="text-xs font-mono font-semibold uppercase text-slate-400 tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-chart-bar text-brand-500"></i> Top Features — Hit Distribution
                </h3>
                <div id="panel-bar-chart" class="min-h-[180px]"></div>
            </div>

            <!-- Module Heatmap Matrix -->
            <div>
                <h3 class="text-xs font-mono font-semibold uppercase text-slate-400 tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-border-all text-brand-500"></i> Module Heatmap — Feature × Timeline
                </h3>
                <div id="panel-heatmap" class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 p-2"></div>
            </div>
        </div>
    </aside>
    <!-- ════════════════════════════════════════════════════ -->

    <!-- Floating Tooltip Container -->
    <div id="futuristic-tooltip" class="bg-slate-950/95 dark:bg-slate-900/95 border border-slate-800 dark:border-slate-700 shadow-xl rounded-xl p-3 text-white text-xs backdrop-blur-sm"></div>

    <script>
        let globalData = null;
        let timelineChart = null;
        let distributionChart = null;
        let sparklineTotal = null;
        let sparklineActive = null;
        let sparklineUnused = null;
        let sparklinePeak = null;

        // Custom theme-aware cell density color generator
        function getDensityColor(val, max) {
            const isDark = document.documentElement.classList.contains('dark');
            if (!val || !max) {
                return {
                    bg: isDark ? 'rgba(51, 65, 85, 0.15)' : 'rgba(226, 232, 240, 0.4)',
                    glow: 'transparent'
                };
            }
            const ratio = val / max;
            
            // Premium Violet/Indigo density levels (similar to TailWind colors)
            let r, g, b;
            if (isDark) {
                // Violet-purple values for dark theme
                r = 139; g = 92; b = 246;
            } else {
                // Indigo-purple values for light theme
                r = 99; g = 102; b = 241;
            }

            const alpha = Math.max(0.12, ratio).toFixed(2);
            return {
                bg: `rgba(${r}, ${g}, ${b}, ${alpha})`,
                glow: `rgba(${r}, ${g}, ${b}, 0.5)`
            };
        }

        // Custom status label levels
        function getHeatLevelBadge(val, max) {
            const ratio = val / max;
            if (ratio === 0) return '<span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">Inactive</span>';
            if (ratio < 0.25) return '<span class="px-2 py-0.5 rounded text-[10px] bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-400 font-bold uppercase">Cold</span>';
            if (ratio < 0.6) return '<span class="px-2 py-0.5 rounded text-[10px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 font-bold uppercase">Warm</span>';
            if (ratio < 0.85) return '<span class="px-2 py-0.5 rounded text-[10px] bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-400 font-bold uppercase">Hot</span>';
            return '<span class="px-2 py-0.5 rounded text-[10px] bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-400 font-bold uppercase animate-pulse">Critical 🔥</span>';
        }

        // Render Matrix grid using minimal contribution style
        function renderMatrix(containerId, rows, rowKey, colKey, valueKey) {
            const container = document.getElementById(containerId);
            
            if (!rows || rows.length === 0) {
                container.innerHTML = `<div class="text-center py-10 font-mono text-xs text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">[No activity data mapped for this segment]</div>`;
                return;
            }

            const rowValues = [...new Set(rows.map(r => r[rowKey]))];
            const colValues = [...new Set(rows.map(r => String(r[colKey])))].sort();
            const max = Math.max(...rows.map(r => Number(r[valueKey])), 1);

            const map = {};
            rows.forEach(r => { map[r[rowKey] + '|' + r[colKey]] = Number(r[valueKey]); });

            let html = '<table class="matrix-table w-full min-w-[700px]"><thead><tr><th class="text-left py-2 px-3 text-xs font-mono font-medium text-slate-400 uppercase tracking-wider bg-slate-100/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">Controller Action</th>';
            colValues.forEach(c => {
                const displayCol = c.includes('-') ? c.split('-').slice(1).join('-') : c;
                html += `<th class="py-2 px-1 text-center text-xs font-mono font-medium text-slate-400 uppercase tracking-wider bg-slate-100/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800">${displayCol}</th>`;
            });
            html += '</tr></thead><tbody>';

            rowValues.forEach(rv => {
                const actionClass = 'row-action-' + btoa(rv).replace(/=/g, '');
                html += `<tr class="heatmap-row ${actionClass}" data-action="${rv.toLowerCase()}">`;
                html += `<td class="py-2 px-3 text-xs font-semibold text-slate-700 dark:text-slate-350 border-b border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10 truncate max-w-[280px]" title="${rv}">${rv}</td>`;
                
                colValues.forEach(cv => {
                    const val = map[rv + '|' + cv] || 0;
                    const styleMeta = getDensityColor(val, max);
                    
                    html += `
                        <td class="cell-value text-xs font-mono text-center select-none py-1.5 cursor-crosshair font-medium transition-all duration-150 text-slate-700 dark:text-slate-200 rounded" 
                            style="background:${styleMeta.bg}; --glow-color: ${styleMeta.glow}" 
                            data-val="${val}" 
                            data-max="${max}"
                            data-row="${rv}" 
                            data-col="${cv}">
                            ${val || ''}
                        </td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;

            setupCellTooltips(container);
        }

        // Global HTML escaper to prevent DOM XSS
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Setup Floating tooltip box
        function setupCellTooltips(container) {
            const cells = container.querySelectorAll('td.cell-value');
            const tooltip = document.getElementById('futuristic-tooltip');

            cells.forEach(cell => {
                cell.addEventListener('mouseenter', () => {
                    const val = cell.getAttribute('data-val');
                    const max = cell.getAttribute('data-max');
                    const row = cell.getAttribute('data-row');
                    const col = cell.getAttribute('data-col');

                    tooltip.innerHTML = `
                        <div class="font-semibold text-slate-200 border-b border-slate-800 pb-1.5 mb-1.5 tracking-tight font-mono text-[11px] truncate max-w-[320px]">${escapeHtml(row)}</div>
                        <div class="space-y-1 text-[11px]">
                            <div class="flex justify-between gap-6"><span class="text-slate-400">Dimension Key:</span><span class="font-mono text-brand-400">${escapeHtml(col)}</span></div>
                            <div class="flex justify-between gap-6"><span class="text-slate-400">Total Usage:</span><span class="font-mono font-bold text-white">${escapeHtml(val)}</span></div>
                            <div class="flex justify-between items-center gap-6 pt-1.5"><span class="text-slate-400">Status:</span><span>${getHeatLevelBadge(val, max)}</span></div>
                        </div>
                    `;
                    tooltip.style.display = 'block';
                });

                cell.addEventListener('mousemove', (e) => {
                    tooltip.style.left = (e.pageX + 12) + 'px';
                    tooltip.style.top = (e.pageY + 12) + 'px';
                });

                cell.addEventListener('mouseleave', () => {
                    tooltip.style.display = 'none';
                });
            });
        }

        // Fuzzy filter matrix rows
        function filterHeatmap() {
            const query = document.getElementById('feature-search').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.heatmap-row');
            
            rows.forEach(row => {
                const action = row.getAttribute('data-action');
                if (!query || action.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Switch navigation tabs
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(c => {
                c.classList.add('hidden');
                c.classList.remove('block');
            });
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-brand-500', 'text-brand-600', 'dark:text-brand-400');
                b.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-slate-700', 'dark:hover:text-slate-200');
            });

            document.getElementById(tabId).classList.add('block');
            document.getElementById(tabId).classList.remove('hidden');
            
            btn.classList.add('border-brand-500', 'text-brand-600', 'dark:text-brand-400');
            btn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 50);
        }

        // Render mini stats sparkline
        function renderSparkline(elementId, data, color, type = 'area') {
            const isDark = document.documentElement.classList.contains('dark');
            if (!data || data.length === 0) data = [0, 0, 0];
            const options = {
                chart: {
                    type: type,
                    height: 40,
                    sparkline: { enabled: true },
                    animations: { enabled: true, speed: 450 },
                    background: 'transparent'
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                series: [{
                    name: 'Hits',
                    data: data
                }],
                stroke: {
                    curve: 'smooth',
                    width: 1.5,
                    colors: [color]
                },
                fill: {
                    opacity: type === 'bar' ? 0.7 : 0.1,
                    colors: [color]
                },
                colors: [color],
                tooltip: {
                    enabled: false
                }
            };
            const container = document.getElementById(elementId);
            if (!container) return null;
            container.innerHTML = '';
            const chart = new ApexCharts(container, options);
            chart.render();
            return chart;
        }

        // Update stats metrics
        function populateMetrics(data) {
            const dateSummary = data.by_feature_and_date || [];
            const unusedFeatures = data.unused_features || [];

            // 1. Total usage hits
            const totalHits = dateSummary.reduce((acc, curr) => acc + Number(curr.total), 0);
            document.getElementById('stat-total-hits').innerText = totalHits.toLocaleString();

            // 2. Active features
            const activeFeatures = new Set(dateSummary.map(x => x.controller_action));
            document.getElementById('stat-active-count').innerText = activeFeatures.size.toLocaleString();

            // 3. Unused count
            document.getElementById('stat-unused-count').innerText = unusedFeatures.length.toLocaleString();

            // 4. Avg hits per day
            const uniqueDates = [...new Set(dateSummary.map(x => x.usage_date))];
            const avg = uniqueDates.length ? Math.round(totalHits / uniqueDates.length) : 0;
            document.getElementById('stat-avg-hits').innerText = `Avg: ${avg.toLocaleString()} / day`;

            // 5. Peak day loading
            const dailyTotals = {};
            const dailyActiveFeatures = {};
            dateSummary.forEach(r => {
                dailyTotals[r.usage_date] = (dailyTotals[r.usage_date] || 0) + Number(r.total);
                
                if (!dailyActiveFeatures[r.usage_date]) {
                    dailyActiveFeatures[r.usage_date] = new Set();
                }
                if (Number(r.total) > 0) {
                    dailyActiveFeatures[r.usage_date].add(r.controller_action);
                }
            });

            let peakDate = 'N/A';
            let peakCount = 0;
            Object.keys(dailyTotals).forEach(d => {
                if (dailyTotals[d] > peakCount) {
                    peakCount = dailyTotals[d];
                    peakDate = d.includes('-') ? d.split('-').slice(1).join('-') : d;
                }
            });
            
            document.getElementById('stat-peak-day').innerText = peakDate;
            document.getElementById('stat-peak-count').innerText = `Max: ${peakCount.toLocaleString()} hits`;

            // Setup sparkline series datasets
            const datesSorted = Object.keys(dailyTotals).sort();
            const totalHitsSeries = datesSorted.map(d => dailyTotals[d]);
            const activeFeaturesSeries = datesSorted.map(d => dailyActiveFeatures[d] ? dailyActiveFeatures[d].size : 0);
            
            const totalActiveFeaturesCount = activeFeatures.size;
            const unusedFeaturesSeries = datesSorted.map(d => {
                const activeToday = dailyActiveFeatures[d] ? dailyActiveFeatures[d].size : 0;
                return Math.max(0, totalActiveFeaturesCount - activeToday);
            });

            // Destroy & Re-render sparklines
            if (sparklineTotal) sparklineTotal.destroy();
            if (sparklineActive) sparklineActive.destroy();
            if (sparklineUnused) sparklineUnused.destroy();
            if (sparklinePeak) sparklinePeak.destroy();

            sparklineTotal = renderSparkline('sparkline-total', totalHitsSeries, '#8b5cf6', 'area');
            sparklineActive = renderSparkline('sparkline-active', activeFeaturesSeries, '#ec4899', 'area');
            sparklineUnused = renderSparkline('sparkline-unused', unusedFeaturesSeries, '#a855f7', 'area');
            sparklinePeak = renderSparkline('sparkline-peak', totalHitsSeries, '#10b981', 'bar');

            // Toggle display of empty state
            const emptyEl = document.getElementById('dashboard-empty-state');
            const heatmapTab = document.getElementById('tab-heatmap');
            const tabsNav = document.getElementById('btn-tab-heatmap').parentNode;

            if (totalHits === 0 && unusedFeatures.length === 0) {
                emptyEl.classList.remove('hidden');
                heatmapTab.classList.add('hidden');
                tabsNav.classList.add('hidden');
            } else {
                emptyEl.classList.add('hidden');
                heatmapTab.classList.remove('hidden');
                tabsNav.classList.remove('hidden');
            }
        }

        // Render timeline charts
        function renderCharts(data) {
            const dateSummary = data.by_feature_and_date || [];
            
            const dailyMap = {};
            dateSummary.forEach(item => {
                dailyMap[item.usage_date] = (dailyMap[item.usage_date] || 0) + Number(item.total);
            });
            const datesSorted = Object.keys(dailyMap).sort();
            const seriesTrend = datesSorted.map(d => dailyMap[d]);

            const isDark = document.documentElement.classList.contains('dark');
            const themeMode = isDark ? 'dark' : 'light';
            const foreColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';

            // Line/Area Chart Options
            const timelineOptions = {
                chart: {
                    type: 'area',
                    height: 280,
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: foreColor
                },
                theme: {
                    mode: themeMode
                },
                series: [{
                    name: 'Total Hits',
                    data: seriesTrend
                }],
                xaxis: {
                    categories: datesSorted.map(d => d.includes('-') ? d.split('-').slice(1).join('-') : d),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    colors: ['#8b5cf6'],
                    width: 2.5
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.01,
                        colorStops: [
                            { offset: 0, color: '#8b5cf6', opacity: 0.25 },
                            { offset: 100, color: '#c084fc', opacity: 0.01 }
                        ]
                    }
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4
                },
                tooltip: { theme: themeMode }
            };

            if (timelineChart) timelineChart.destroy();
            timelineChart = new ApexCharts(document.getElementById('chart-timeline'), timelineOptions);
            timelineChart.render();

            // Prepare Top Distribution chart
            const featureTotals = {};
            dateSummary.forEach(item => {
                featureTotals[item.controller_action] = (featureTotals[item.controller_action] || 0) + Number(item.total);
            });
            
            const sortedFeatures = Object.keys(featureTotals)
                .map(k => ({ action: k, hits: featureTotals[k] }))
                .sort((a, b) => b.hits - a.hits)
                .slice(0, 10);

            // Distribution bar chart
            const barOptions = {
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: foreColor
                },
                theme: {
                    mode: themeMode
                },
                series: [{
                    name: 'Hits',
                    data: sortedFeatures.map(x => x.hits)
                }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '55%',
                        borderRadius: 4,
                        distributed: true
                    }
                },
                colors: ['#8b5cf6', '#a78bfa', '#c084fc', '#f472b6', '#38bdf8', '#34d399', '#fca5a5', '#fbbf24', '#2dd4bf', '#818cf8'],
                xaxis: {
                    categories: sortedFeatures.map(x => x.action.split('@').join(' @ ')),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4
                },
                legend: { show: false },
                tooltip: { theme: themeMode }
            };

            if (distributionChart) distributionChart.destroy();
            distributionChart = new ApexCharts(document.getElementById('chart-distribution'), barOptions);
            distributionChart.render();
        }

        // Render Unused List
        function renderUnusedFeatures(unusedFeatures) {
            const container = document.getElementById('unused-list');
            if (!unusedFeatures || unusedFeatures.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-6 text-emerald-600 dark:text-emerald-400 font-mono text-xs bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-250 dark:border-emerald-900 rounded-xl">
                        <i class="fa-solid fa-square-check mr-1"></i> No unused features found 🎉 (All endpoints registered and active)
                    </div>`;
                return;
            }

            container.innerHTML = unusedFeatures.map(f => {
                const actionEscaped = escapeHtml(f.controller_action);
                return `
                <div class="flex justify-between items-center bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 hover:border-red-400 dark:hover:border-red-950 transition-all font-mono text-xs">
                    <span class="truncate text-slate-800 dark:text-slate-300 font-medium mr-4" title="${actionEscaped}">${actionEscaped}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900">DEAD</span>
                </div>
            `}).join('');
        }

        // Theme Toggle Handler
        function toggleTheme() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            
            const isDark = html.classList.contains('dark');
            localStorage.setItem('feature-sentinel-theme', isDark ? 'dark' : 'light');
            
            if (globalData) {
                renderMatrix('heatmap-feature-date', globalData.by_feature_and_date, 'controller_action', 'usage_date', 'total');
                renderMatrix('heatmap-feature-user', globalData.by_feature_and_user, 'controller_action', 'user_id', 'total');
                populateMetrics(globalData);
                renderCharts(globalData);
            }
        }

        // Load all data
        function loadData() {
            const from = document.getElementById('from').value;
            const to = document.getElementById('to').value;
            const spinner = document.getElementById('loading-spinner');

            spinner.classList.add('opacity-100', 'pointer-events-auto');

            fetch(`{{ route('feature-heatmap.data') }}?from=${from}&to=${to}`)
                .then(res => res.json())
                .then(data => {
                    globalData = data;
                    
                    renderMatrix('heatmap-feature-date', data.by_feature_and_date, 'controller_action', 'usage_date', 'total');
                    renderMatrix('heatmap-feature-user', data.by_feature_and_user, 'controller_action', 'user_id', 'total');
                    
                    populateMetrics(data);
                    renderCharts(data);
                    renderUnusedFeatures(data.unused_features);
                    
                    filterHeatmap();
                })
                .catch(err => {
                    console.error('Heatmap sentinel fetching failure:', err);
                })
                .finally(() => {
                    spinner.classList.remove('opacity-100', 'pointer-events-auto');
                });
        }

        // ══════════════════════════════════════════════
        //  USERS TRACKING — state & helpers
        // ══════════════════════════════════════════════
        let usersLoaded  = false;   // lazy-load guard
        let panelBarChart = null;   // ApexCharts instance for panel
        let currentPanelUserId = null;

        /** Called once when the Users tab is first activated */
        function lazyLoadUsers() {
            if (!usersLoaded) {
                loadUsersTable();
            }
        }

        /** Fetch /users and render the users table */
        function loadUsersTable() {
            const from = document.getElementById('from').value;
            const to   = document.getElementById('to').value;

            fetch(`{{ route('feature-heatmap.users') }}?from=${from}&to=${to}`)
                .then(r => r.json())
                .then(rows => {
                    renderUsersTable(rows);
                    usersLoaded = true;
                })
                .catch(err => {
                    document.getElementById('users-tbody').innerHTML =
                        `<tr><td colspan="6" class="text-center py-10 font-mono text-xs text-red-400"><i class="fa-solid fa-circle-exclamation mr-1"></i> Failed to load users: ${escapeHtml(err.message)}</td></tr>`;
                });
        }

        /** Build the users table rows */
        function renderUsersTable(rows) {
            const tbody = document.getElementById('users-tbody');

            if (!rows || rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 font-mono text-xs text-slate-400 border-t border-slate-100 dark:border-slate-800">[No tracked users found in the selected date range]</td></tr>`;
                return;
            }

            // Store in global lookup map to avoid quote-escaping syntax errors in inline HTML onclick
            window.trackedUsersMap = {};
            rows.forEach(u => {
                window.trackedUsersMap[u.user_id] = u;
            });

            tbody.innerHTML = rows.map(u => {
                const rawName  = u.user_name  || `User #${u.user_id}`;
                const rawEmail = u.user_email || '—';
                const initial  = escapeHtml((rawName || rawEmail || '#').charAt(0).toUpperCase());
                const name     = escapeHtml(rawName);
                const email    = escapeHtml(rawEmail);
                const lastSeen = escapeHtml(u.last_seen ? u.last_seen.substring(0, 16).replace('T', ' ') : '—');
                const avatarColors = ['#7c3aed','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6'];
                const avatarColor  = avatarColors[u.user_id % avatarColors.length];

                return `
                <tr class="users-row border-t border-slate-100 dark:border-slate-800 hover:bg-brand-50/40 dark:hover:bg-brand-950/10 transition-colors cursor-pointer group"
                    data-user-id="${u.user_id}"
                    data-search="${escapeHtml((rawName + ' ' + rawEmail).toLowerCase())}"
                    onclick="openUserDetailById(${u.user_id})">

                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-sm" style="background:${avatarColor}">${initial}</div>
                            <span class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[160px]" title="${name}">${name}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs font-mono truncate max-w-[200px]" title="${email}">${email}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 dark:bg-brand-950/30 text-brand-700 dark:text-brand-300 border border-brand-100 dark:border-brand-900">${Number(u.total_hits).toLocaleString()}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">${Number(u.distinct_features).toLocaleString()}</span>
                    </td>
                    <td class="px-4 py-3 text-xs font-mono text-slate-400">${lastSeen}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="event.stopPropagation(); openUserDetailById(${u.user_id})" title="View Detail"
                                class="h-7 w-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-300 dark:hover:border-brand-700 flex items-center justify-center transition-all text-xs">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button onclick="event.stopPropagation(); downloadUserReportById(${u.user_id})" title="Download CSV"
                                class="h-7 w-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-300 dark:hover:border-emerald-700 flex items-center justify-center transition-all text-xs">
                                <i class="fa-solid fa-file-csv"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        /** Helper to open user detail by integer user ID without string escaping issues */
        function openUserDetailById(userId) {
            const u = (window.trackedUsersMap && window.trackedUsersMap[userId]) || {};
            const name = u.user_name || `User #${userId}`;
            const email = u.user_email || '';
            loadUserDetail(userId, name, email);
        }

        /** Helper to download user report by integer user ID without string escaping issues */
        function downloadUserReportById(userId) {
            const u = (window.trackedUsersMap && window.trackedUsersMap[userId]) || {};
            const name = u.user_name || `User #${userId}`;
            downloadUserReport(userId, name);
        }

        /** Live-search filter on the users table */
        function filterUsersTable() {
            const q = document.getElementById('users-search').value.toLowerCase().trim();
            document.querySelectorAll('.users-row').forEach(row => {
                row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
            });
        }

        /** Fetch /users/{id} and open the detail slide-over */
        function loadUserDetail(userId, name, email) {
            currentPanelUserId = userId;

            // Identity
            const initial = (name || email || '#').charAt(0).toUpperCase();
            const avatarColors = ['#7c3aed','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6'];
            document.getElementById('panel-avatar').textContent = initial;
            document.getElementById('panel-avatar').style.background = avatarColors[userId % avatarColors.length];
            document.getElementById('panel-name').textContent  = name  || `User #${userId}`;
            document.getElementById('panel-email').textContent = email || '';

            // Wire download button
            document.getElementById('panel-download-btn').onclick = () => downloadUserReport(userId, name);

            // Reset panel body
            document.getElementById('panel-heatmap').innerHTML  = `<div class="text-center py-8 font-mono text-xs text-slate-400"><i class="fa-solid fa-spinner animate-spin mr-2"></i>Loading…</div>`;
            document.getElementById('panel-bar-chart').innerHTML = '';

            // Open panel
            document.getElementById('user-panel-backdrop').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('user-panel').classList.remove('translate-x-full');
            }, 10);

            // Fetch data
            const from = document.getElementById('from').value;
            const to   = document.getElementById('to').value;

            fetch(`{{ url(config('feature-heatmap.route_prefix', 'feature-heatmap')) }}/users/${userId}?from=${from}&to=${to}`)
                .then(r => r.json())
                .then(d => {
                    // Heatmap matrix
                    renderMatrix('panel-heatmap', d.by_feature_and_date, 'controller_action', 'usage_date', 'total');

                    // Top Features bar chart
                    if (panelBarChart) { panelBarChart.destroy(); panelBarChart = null; }

                    const feats = (d.feature_totals || []).slice(0, 12);
                    if (feats.length === 0) {
                        document.getElementById('panel-bar-chart').innerHTML =
                            `<div class="text-center py-6 font-mono text-xs text-slate-400">[No feature data]</div>`;
                        return;
                    }

                    const isDark = document.documentElement.classList.contains('dark');
                    panelBarChart = new ApexCharts(document.getElementById('panel-bar-chart'), {
                        chart: {
                            type: 'bar',
                            height: Math.max(180, feats.length * 32),
                            toolbar: { show: false },
                            background: 'transparent',
                            foreColor: isDark ? '#94a3b8' : '#64748b'
                        },
                        theme: { mode: isDark ? 'dark' : 'light' },
                        series: [{ name: 'Hits', data: feats.map(f => f.total) }],
                        plotOptions: {
                            bar: { horizontal: true, barHeight: '55%', borderRadius: 4, distributed: true }
                        },
                        colors: ['#8b5cf6','#a78bfa','#c084fc','#f472b6','#38bdf8','#34d399','#fca5a5','#fbbf24','#2dd4bf','#818cf8','#fb923c','#e879f9'],
                        xaxis: {
                            categories: feats.map(f => f.controller_action.includes('@') ? f.controller_action.split('@').join(' @ ') : f.controller_action),
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        grid: {
                            borderColor: isDark ? 'rgba(51,65,85,0.4)' : 'rgba(226,232,240,0.8)',
                            strokeDashArray: 4
                        },
                        legend: { show: false },
                        tooltip: { theme: isDark ? 'dark' : 'light' }
                    });
                    panelBarChart.render();
                })
                .catch(err => {
                    document.getElementById('panel-heatmap').innerHTML =
                        `<div class="text-center py-8 font-mono text-xs text-red-400"><i class="fa-solid fa-circle-exclamation mr-1"></i>${err.message}</div>`;
                });
        }

        /** Close the user detail slide-over */
        function closeUserPanel() {
            document.getElementById('user-panel').classList.add('translate-x-full');
            setTimeout(() => {
                document.getElementById('user-panel-backdrop').classList.add('hidden');
            }, 310);
        }

        /** Trigger CSV download for a user */
        function downloadUserReport(userId, name) {
            const from = document.getElementById('from').value;
            const to   = document.getElementById('to').value;
            const url  = `{{ url(config('feature-heatmap.route_prefix', 'feature-heatmap')) }}/users/${userId}/report?from=${from}&to=${to}`;
            const a    = document.createElement('a');
            a.href     = url;
            a.download = '';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        // Re-load users table on Sync when Users tab is active
        const _origLoadData = loadData;
        loadData = function () {
            _origLoadData();
            if (usersLoaded) {
                usersLoaded = false;
                lazyLoadUsers();
            }
        };

        // Close panel on ESC
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeUserPanel();
        });

        document.addEventListener('DOMContentLoaded', () => {
            loadData();
        });
    </script>
</body>
</html>
