<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Terjadi Kesalahan' }} - BumdesKA</title>

    <meta name="robots" content="noindex, nofollow">

    <style>
        :root {
            --primary: #3F51B5;
            --primary-dark: #303F9F;
            --primary-light: #E8EAF6;
            --text: #1F2937;
            --muted: #6B7280;
            --border: #E5E7EB;
            --background: #F8FAFC;
            --white: #FFFFFF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background: var(--background);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            width: 100%;
            padding: 28px 32px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text);
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--primary);
            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 18px;
            font-weight: 800;

            box-shadow:
                0 6px 16px rgba(63, 81, 181, 0.20);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-name {
            font-size: 16px;
            font-weight: 750;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            margin-top: 3px;
            font-size: 12px;
            color: var(--muted);
        }

        .content {
            flex: 1;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 40px 24px 80px;
        }

        .error-wrapper {
            width: 100%;
            max-width: 720px;
            text-align: center;
        }

        .error-code {
            color: var(--primary);

            font-size: clamp(92px, 16vw, 170px);
            line-height: 0.9;

            font-weight: 850;
            letter-spacing: -0.08em;

            user-select: none;
        }

        .error-icon {
            width: 64px;
            height: 64px;

            margin: 32px auto 24px;

            border-radius: 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: var(--primary-light);
            color: var(--primary);

            font-size: 27px;
            font-weight: 700;
        }

        .error-title {
            font-size: clamp(26px, 4vw, 36px);
            line-height: 1.2;

            font-weight: 750;
            letter-spacing: -0.035em;

            margin-bottom: 14px;
        }

        .error-message {
            max-width: 520px;
            margin: 0 auto;

            color: var(--muted);

            font-size: 16px;
            line-height: 1.7;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: center;

            gap: 12px;

            margin-top: 32px;

            flex-wrap: wrap;
        }

        .button {
            min-height: 46px;

            padding: 0 20px;

            border-radius: 10px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            text-decoration: none;

            font-size: 14px;
            font-weight: 650;

            transition:
                transform 0.18s ease,
                box-shadow 0.18s ease,
                background 0.18s ease;
        }

        .button-primary {
            background: var(--primary);
            color: white;

            box-shadow:
                0 6px 16px rgba(63, 81, 181, 0.18);
        }

        .button-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);

            box-shadow:
                0 9px 22px rgba(63, 81, 181, 0.25);
        }

        .button-secondary {
            background: white;
            color: var(--text);

            border: 1px solid var(--border);
        }

        .button-secondary:hover {
            background: #F9FAFB;
            transform: translateY(-1px);
        }

        .footer {
            padding: 24px 32px 30px;

            text-align: center;

            color: #9CA3AF;
            font-size: 12px;
        }

        .footer span {
            color: #D1D5DB;
            margin: 0 5px;
        }

        @media (max-width: 640px) {
            .header {
                padding: 22px 20px;
            }

            .content {
                padding: 30px 20px 60px;
            }

            .error-icon {
                margin-top: 24px;
            }

            .error-message {
                font-size: 15px;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
                max-width: 280px;
            }

            .footer {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <header class="header">
        <a href="{{ url('/') }}" class="brand">

            <div class="brand-mark">
                B
            </div>

            <div class="brand-text">
                <span class="brand-name">
                    BJFin
                </span>

                <span class="brand-subtitle">
                    Bandar Jaya Financial &bull; BUMDesa Kuala Alam
                </span>
            </div>

        </a>
    </header>


    <main class="content">

        <div class="error-wrapper">

            <div class="error-code">
                {{ $code ?? '500' }}
            </div>

            <div class="error-icon">
                {{ $icon ?? '!' }}
            </div>

            <h1 class="error-title">
                {{ $title ?? 'Terjadi Kesalahan' }}
            </h1>

            <p class="error-message">
                {{ $message ?? 'Maaf, terjadi kesalahan saat memproses permintaan Anda.' }}
            </p>

            <div class="actions">

                <a href="{{ url('/') }}" class="button button-primary">
                    ← Kembali ke Beranda
                </a>

                <a href="javascript:history.back()" class="button button-secondary">
                    Kembali
                </a>

            </div>

        </div>

    </main>


    <footer class="footer">
        © {{ date('Y') }} BJFin (Bandar Jaya Financial) <span>•</span> BUMDesa Kuala Alam
    </footer>

</div>

</body>
</html>