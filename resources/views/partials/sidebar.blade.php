<aside class="sidebar">
    <!-- Sidebar Header with Logo -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('images/azra-logo.png') }}" alt="AZRA Logo">
        </div>
        <div class="sidebar-title">
            <h3>Monitoring</h3>
            <h3>Asuransi</h3>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Menu Utama Section -->
        <div class="nav-section-title">Menu Utama</div>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
            <div class="nav-icon">
                <x-icon name="home" class="icon-lg" />
            </div>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="{{ route('admin.ruang-lingkup.index') }}" class="nav-link {{ request()->routeIs('admin.ruang-lingkup.*') ? 'active' : '' }}" title="Data Kerjasama">
            <div class="nav-icon">
                <x-icon name="document" class="icon-lg" />
            </div>
            <span class="nav-text">Data Kerjasama</span>
        </a>
        <a href="{{ route('admin.export.import-form') }}" class="nav-link {{ request()->routeIs('admin.export.*') ? 'active' : '' }}" title="Import/Export Data">
            <div class="nav-icon">
                <x-icon name="list" class="icon-lg" />
            </div>
            <span class="nav-text">Import / Export</span>
        </a>

        <div class="nav-divider"></div>

        <!-- Master Data Section -->
        <div class="nav-section-title">Master Data</div>

        @php
            $isMasterKerjasamaActive = request()->routeIs('admin.master-jenis-layanan.*') ||
                                     request()->routeIs('admin.master-obat.*') ||
                                     request()->routeIs('admin.master-badan-usaha.*') ||
                                     request()->routeIs('admin.diskon.*') ||
                                     request()->routeIs('admin.penagihan-pembayaran.*');
        @endphp

        <!-- Master Data Submenu -->
        <div class="nav-submenu-wrapper">
            <a href="#" class="nav-link nav-link-parent {{ $isMasterKerjasamaActive ? 'active' : '' }}" title="Master Kerjasama" onclick="toggleSubmenu(event, this)">
                <div class="nav-icon">
                    <x-icon name="grid" class="icon-lg" />
                </div>
                <span class="nav-text">Master Kerjasama</span>
                <x-icon name="chevron-down" class="submenu-arrow" />
            </a>
            <div class="nav-submenu {{ $isMasterKerjasamaActive ? 'open' : '' }}">
                <a href="{{ route('admin.master-jenis-layanan.index') }}" class="nav-link nav-link-sub {{ request()->routeIs('admin.master-jenis-layanan.*') ? 'active' : '' }}" title="Jenis Layanan">
                    <div class="nav-icon">
                        <x-icon name="list" class="icon-lg" />
                    </div>
                    <span class="nav-text">Jenis Layanan</span>
                </a>
                <a href="{{ route('admin.master-obat.index') }}" class="nav-link nav-link-sub {{ request()->routeIs('admin.master-obat.*') ? 'active' : '' }}" title="Obat">
                    <div class="nav-icon">
                        <x-icon name="filter" class="icon-lg" />
                    </div>
                    <span class="nav-text">Obat</span>
                </a>
                <a href="{{ route('admin.master-badan-usaha.index') }}" class="nav-link nav-link-sub {{ request()->routeIs('admin.master-badan-usaha.*') ? 'active' : '' }}" title="Badan Usaha">
                    <div class="nav-icon">
                        <x-icon name="building" class="icon-lg" />
                    </div>
                    <span class="nav-text">Badan Usaha</span>
                </a>
                <a href="{{ route('admin.diskon.index') }}" class="nav-link nav-link-sub {{ request()->routeIs('admin.diskon.*') ? 'active' : '' }}" title="Diskon Asuransi">
                    <div class="nav-icon">
                        <x-icon name="tag" class="icon-lg" />
                    </div>
                    <span class="nav-text">Diskon Asuransi</span>
                </a>
                <a href="{{ route('admin.penagihan-pembayaran.index') }}" class="nav-link nav-link-sub {{ request()->routeIs('admin.penagihan-pembayaran.*') ? 'active' : '' }}" title="Jangka Waktu Bayar">
                    <div class="nav-icon">
                        <x-icon name="clock" class="icon-lg" />
                    </div>
                    <span class="nav-text">Jangka Waktu Bayar</span>
                </a>
            </div>
        </div>

        <div class="nav-divider"></div>

        <!-- Pengaturan Section -->
        <div class="nav-section-title">Pengaturan</div>

        <a href="{{ route('admin.table-settings.index') }}" class="nav-link {{ request()->routeIs('admin.table-settings.*') ? 'active' : '' }}" title="Pengaturan Tampilan">
            <div class="nav-icon">
                <x-icon name="list" class="icon-lg" />
            </div>
            <span class="nav-text">Manajemen Kolom</span>
        </a>

        <a href="{{ route('admin.reminder-recipients.index') }}" class="nav-link {{ request()->routeIs('admin.reminder-recipients.*') ? 'active' : '' }}" title="Pengaturan Reminder">
            <div class="nav-icon">
                <x-icon name="envelope" class="icon-lg" />
            </div>
            <span class="nav-text">Manajemen Reminder</span>
        </a>


        <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" title="Manajemen Role">
            <div class="nav-icon">
                <x-icon name="shield-check-solid" class="icon-lg" />
            </div>
            <span class="nav-text">Manajemen Role</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="Manajemen User">
            <div class="nav-icon">
                <x-icon name="users-solid" class="icon-lg" />
            </div>
            <span class="nav-text">Manajemen User</span>
        </a>

        @else
        <!-- Menu untuk User (Read-Only) -->
        <a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" title="Dashboard">
            <div class="nav-icon">
                <x-icon name="home" class="icon-lg" />
            </div>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="{{ route('user.kerjasama.index') }}" class="nav-link {{ request()->routeIs('user.kerjasama.*') ? 'active' : '' }}" title="Data Kerjasama">
            <div class="nav-icon">
                <x-icon name="document" class="icon-lg" />
            </div>
            <span class="nav-text">Data Kerjasama</span>
        </a>
        @endif
    </nav>
</aside>
