<x-guest-layout>
    <x-slot name="title">Login</x-slot>

    <div class="login-form-container">
        <div class="login-form-header">
            <div class="login-form-logo">
                <span class="login-form-logo-text">RS AZRA</span>
            </div>
            <h2>Selamat Datang</h2>
            <p>Masuk ke sistem monitoring asuransi</p>
        </div>

        @if (session('error'))
            <div class="alert-box error">
                <x-icon name="exclamation-circle" class="icon-sm" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('status'))
            <div class="alert-box success">
                <x-icon name="check-circle" class="icon-sm" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
            @csrf

            <div class="form-group-modern">
                <label for="username" class="input-label-modern">
                    <x-icon name="user" class="icon-sm" />
                    <span>Username</span>
                </label>
                <input id="username" type="text" name="username"
                    class="input-field-modern @error('username') has-error @enderror"
                    placeholder="Masukkan username Anda" value="{{ old('username') }}" required autofocus
                    autocomplete="username">
                @error('username')
                    <span class="input-error-modern">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-modern">
                <label for="password" class="input-label-modern">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Password</span>
                </label>
                <div class="password-wrapper">
                    <input id="password" type="password" name="password"
                        class="input-field-modern @error('password') has-error @enderror"
                        placeholder="Masukkan password Anda" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()"
                        aria-label="Toggle password visibility">
                        <x-icon name="eye" class="icon-sm" id="eyeIcon" />
                    </button>
                </div>
                @error('password')
                    <span class="input-error-modern">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-options">
                <label class="checkbox-label-modern">
                    <input type="checkbox" name="remember" class="checkbox-input-modern" id="remember">
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text-modern">Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="submit-btn-modern" id="loginSubmit">
                <span id="loginBtnText">Masuk</span>
                <div class="spinner-loader" id="loginBtnLoader"></div>
                <x-icon name="arrow-right" class="icon-sm" id="loginBtnIcon" />
            </button>
        </form>

    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                pwd.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('loginSubmit');
            const txt = document.getElementById('loginBtnText');
            const icon = document.getElementById('loginBtnIcon');
            const loader = document.getElementById('loginBtnLoader');
            btn.disabled = true;
            txt.textContent = 'Memproses...';
            icon.style.display = 'none';
            loader.style.display = 'block';
        });
    </script>
</x-guest-layout>
