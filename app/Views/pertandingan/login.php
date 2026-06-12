<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Perangkat Pertandingan — Digital Pencak Silat</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/penilaian/login.css') ?>">
</head>

<body class="login-body">
    <main class="login-wrap">
        <div class="login-card">
            <div class="login-card-body">
                <div class="text-center mb-4">
                    <img src="<?= base_url('assets/images/brand/dps/logo-digital-scoring.png') ?>" alt="Digital Pencak Silat" class="login-logo">
                    <h1 class="login-title">Perangkat Pertandingan</h1>
                    <p class="login-subtitle">Digital Pencak Silat — Scoring System</p>
                </div>

                <?= view('shared_components/notification') ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger border-0 rounded-4" role="alert">
                        <i class="fas fa-circle-exclamation me-2"></i>
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('perangkat-pertandingan/login') ?>" autocomplete="off" id="loginForm">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="far fa-user"></i></span>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= old('username') ?>"
                                class="form-control"
                                placeholder="Username"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Password"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        Masuk <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted">Akses terbatas Perangkat Pertandingan</small>
                </div>
            </div>
        </div>

        <footer class="text-center mt-4 login-footer">
            <span class="brand-text">DIGITAL PENCAK SILAT</span> &copy; <?= date('Y') ?>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
