<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Inventory System') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Custom Theme Styles -->
    <style>
        :root {
            --theme-primary: #8b0000;
            --theme-dark: #1e1e1e;
            --theme-accent: #c0392b;
        }
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: var(--theme-dark);
            border-right: 4px solid var(--theme-primary);
            flex-shrink: 0;
        }
        .sidebar .brand-title {
            color: #ffffff;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .sidebar .nav-link {
            color: #cfd2d6;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 4px;
            transition: all 0.2s ease-in-out;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--theme-primary);
            color: #ffffff;
        }
        .chart-container {
            position: relative;
            height: 260px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <aside class="sidebar p-3 d-flex flex-column">
            <div class="mb-4 pt-2 px-2">
                <span class="brand-title fs-5">BuildRight Hardware</span>
            </div>
            
            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-2"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right me-2"></i> Transactions
                    </a>
                </li>
                @if(auth()->user()->is_admin)
                    <li class="nav-item">
                        <a href="{{ route('adjustments.create') }}" class="nav-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}">
                            <i class="bi bi-sliders me-2"></i> Stock Adjustment
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line me-2"></i> Reports
                    </a>
                </li>
            </ul>

            <hr class="text-secondary">
            
            <div class="dropdown">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm text-start">
                        <i class="bi bi-box-arrow-right me-2"></i> Log Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0; min-height: 100vh;">
            <header class="bg-white border-bottom py-3 px-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-secondary">Inventory Management System</h5>
                    <div class="d-flex align-items-center gap-3">

                        <!-- Notification bell -->
                        <div class="dropdown">
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            <a class="position-relative text-dark" href="#" id="notifBell" data-bs-toggle="dropdown">
                                <i class="bi bi-bell-fill fs-5"></i>
                                @if ($unreadCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px;">
                                @forelse (auth()->user()->unreadNotifications()->take(5)->get() as $notification)
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('products.show', $notification->data['product_id']) }}">
                                            <strong>{{ $notification->data['product_name'] }}</strong> is low on stock
                                            ({{ $notification->data['current_quantity'] }} left)
                                        </a>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item-text small text-muted">No new alerts.</span></li>
                                @endforelse
                                @if ($unreadCount > 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('notifications.markRead') }}" method="POST" class="px-3">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-link p-0">Mark all as read</button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <span class="badge bg-secondary">{{ Auth::user()->name ?? 'Administrator' }}</span>
                    </div>
                </div>
            </header>

            <main class="p-4 flex-grow-1">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>