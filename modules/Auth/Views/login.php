<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login - POS Payroll System
    </title>

    <link
        href="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        .async-skeleton {
            display: none;
        }

        body.is-loading .async-skeleton {
            display: block;
            position: fixed;
            inset: 0;
            z-index: 10;
            background: rgba(248, 249, 250, .88);
            pointer-events: all;
        }

        body.is-loading .async-skeleton::before {
            content: "";
            display: block;
            width: min(90%, 420px);
            height: 260px;
            margin: 25vh auto 0;
            border-radius: 6px;
            background: linear-gradient(90deg, #e5e7eb 25%, #f9fafb 50%, #e5e7eb 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.2s ease-in-out infinite;
        }

        @keyframes skeleton-shimmer {
            from { background-position: 200% 0; }
            to { background-position: -200% 0; }
        }
    </style>

</head>

<body class="bg-light">

<div class="container">

    <div
        class="row justify-content-center align-items-center"
        style="min-height:100vh;"
    >

        <div class="col-md-5 col-lg-4">

            <div class="card shadow">

                <div class="card-body p-4">

                    <div class="text-center mb-4">

                        <h3>
                            POS Payroll System
                        </h3>

                        <p class="text-muted">
                            Sign in to continue
                        </p>

                    </div>

                    <?php if (!empty($error)): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>

                    <form
                        method="POST"
                        action="<?= BASE_URL ?>/login"
                    >

                        <div class="mb-3">

                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                required
                                autofocus
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Login
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="async-skeleton" aria-hidden="true"></div>

<script src="<?= BASE_URL ?>/assets/js/async.js"></script>

</body>

</html>