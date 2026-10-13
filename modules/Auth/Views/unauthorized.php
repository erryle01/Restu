
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Access Denied</title>

    <link
        href="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background:
                radial-gradient(
                    circle at top left,
                    #ffe4ec 0%,
                    transparent 35%
                ),
                radial-gradient(
                    circle at bottom right,
                    #e0e7ff 0%,
                    transparent 40%
                ),
                linear-gradient(
                    135deg,
                    #fff7fa,
                    #f5f7ff
                );

            overflow: hidden;
        }

        /* Floating decorations */

        .bubble {
            position: fixed;

            width: 120px;
            height: 120px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(5px);

            animation: float 6s ease-in-out infinite;
        }

        .bubble.one {
            top: 8%;
            left: 8%;
        }

        .bubble.two {
            width: 80px;
            height: 80px;

            bottom: 12%;
            right: 12%;

            animation-delay: 1.5s;
        }

        .bubble.three {
            width: 45px;
            height: 45px;

            top: 20%;
            right: 20%;

            animation-delay: 3s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-18px);
            }

        }

        /* Main card */

        .access-card {

            position: relative;

            width: min(450px, 92%);

            padding: 45px 35px;

            text-align: center;

            background: rgba(255, 255, 255, 0.88);

            border: 1px solid rgba(255, 255, 255, 0.8);

            border-radius: 28px;

            box-shadow:
                0 25px 70px rgba(80, 80, 120, 0.15),
                0 8px 25px rgba(80, 80, 120, 0.08);

            backdrop-filter: blur(15px);

            animation: cardIn .6s ease-out;
        }

        @keyframes cardIn {

            from {
                opacity: 0;
                transform: translateY(25px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

        }

        /* Icon */

        .icon-wrapper {

            width: 100px;
            height: 100px;

            margin: 0 auto 25px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 30px;

            background:
                linear-gradient(
                    135deg,
                    #ffe0e8,
                    #eee7ff
                );

            box-shadow:
                inset 0 0 0 1px
                rgba(255, 255, 255, .8);

            animation: iconFloat 4s ease-in-out infinite;
        }

        .icon-wrapper i {

            font-size: 45px;

            color: #8b5cf6;
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: rotate(0deg) translateY(0);
            }

            50% {
                transform: rotate(-3deg) translateY(-5px);
            }

        }

        /* Text */

        .access-card h1 {

            margin-bottom: 10px;

            font-size: 30px;

            font-weight: 750;

            color: #24243a;
        }

        .access-card .subtitle {

            margin-bottom: 8px;

            font-size: 16px;

            font-weight: 600;

            color: #55556d;
        }

        .access-card .description {

            margin: 0 auto 28px;

            max-width: 340px;

            font-size: 14px;

            line-height: 1.7;

            color: #85859a;
        }

        /* Button */

        .dashboard-btn {

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 8px;

            padding: 12px 22px;

            border: none;

            border-radius: 14px;

            color: #fff;

            font-weight: 600;

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #8b5cf6,
                    #6366f1
                );

            box-shadow:
                0 8px 20px
                rgba(99, 102, 241, .25);

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .dashboard-btn:hover {

            color: #fff;

            transform: translateY(-2px);

            box-shadow:
                0 12px 28px
                rgba(99, 102, 241, .35);
        }

        .dashboard-btn:active {

            transform: translateY(0);

        }

        /* Small footer */

        .system-label {

            margin-top: 25px;

            font-size: 12px;

            color: #aaaabc;
        }

        .system-label i {

            margin-right: 4px;

            color: #8b5cf6;
        }

        /* Mobile */

        @media (max-width: 480px) {

            .access-card {

                padding: 35px 25px;

                border-radius: 24px;
            }

            .icon-wrapper {

                width: 85px;
                height: 85px;

                border-radius: 25px;
            }

            .icon-wrapper i {

                font-size: 38px;
            }

            .access-card h1 {

                font-size: 26px;
            }

        }

    </style>

</head>

<body>

    <!-- Decorative bubbles -->

    <div class="bubble one"></div>

    <div class="bubble two"></div>

    <div class="bubble three"></div>


    <!-- Access Card -->

    <div class="access-card">

        <div class="icon-wrapper">

            <i class="bi bi-shield-lock-fill"></i>

        </div>


        <h1>
            Oops! Access Denied
        </h1>


        <div class="subtitle">

            You can't access this page yet.

        </div>


        <p class="description">

            It looks like your account doesn't have the
            required permission to view this page.
            Please contact your administrator if you
            think this is a mistake.

        </p>


        <a
            href="<?= BASE_URL ?>/dashboard"
            class="dashboard-btn"
        >

            <i class="bi bi-house-door-fill"></i>

            Back to Dashboard

        </a>


        <div class="system-label">

            <i class="bi bi-shield-check"></i>

            Restu Management System

        </div>

    </div>


    <script
        src="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.bundle.min.js"
    ></script>

</body>

</html>

