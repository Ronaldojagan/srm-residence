<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alokha</title>
    <meta name="description" content="Request removal of your personal data from Alokha">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ url('img/fev.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Local CSS -->
    <link href="{{ url('assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ url('assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ url('css/custom.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #6c8aff;
            --primary-dark: #3a56d4;
            --danger-color: #ef476f;
            --danger-light: #ff709a;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --gray-color: #6c757d;
            --border-radius: 16px;
            --box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.user-data {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--dark-color);
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg,
                #667eea 0%,
                #764ba2 25%,
                #f093fb 50%,
                #f5576c 75%,
                #667eea 100%);
            background-size: 400% 400%;
            animation: gradientShift 20s ease infinite;
            filter: blur(1px);
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Animated Shapes */
        .animated-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            opacity: 0.08;
            animation: shapeFloat 25s infinite ease-in-out;
        }

        .shape-1 {
            top: 10%;
            left: 10%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4361ee, #3a0ca3);
            animation-delay: 0s;
        }

        .shape-2 {
            top: 60%;
            right: 10%;
            width: 200px;
            height: 200px;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            background: linear-gradient(45deg, #ef476f, #ff9e00);
            animation-delay: 5s;
        }

        .shape-3 {
            bottom: 20%;
            left: 20%;
            width: 150px;
            height: 150px;
            border-radius: 63% 37% 54% 46% / 55% 48% 52% 45%;
            background: linear-gradient(45deg, #06d6a0, #118ab2);
            animation-delay: 10s;
        }

        @keyframes shapeFloat {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
                border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            }
            25% {
                transform: translate(50px, -30px) rotate(90deg);
                border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
            }
            50% {
                transform: translate(-30px, 40px) rotate(180deg);
                border-radius: 40% 60% 30% 70% / 60% 40% 60% 40%;
            }
            75% {
                transform: translate(-50px, -20px) rotate(270deg);
                border-radius: 50% 50% 30% 70% / 40% 60% 40% 60%;
            }
        }

        /* Glassmorphism Effect */
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow:
                0 25px 45px rgba(0, 0, 0, 0.1),
                inset 0 0 0 1px rgba(255, 255, 255, 0.4),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.2);
        }

        /* Neon Glow Effect */
        .neon-border {
            position: relative;
        }

        .neon-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg,
                #ff0000, #ff7300, #fffb00, #48ff00,
                #00ffd5, #002bff, #7a00ff, #ff00c8, #ff0000);
            background-size: 400%;
            border-radius: calc(var(--border-radius) + 4px);
            z-index: -1;
            animation: neonGlow 20s linear infinite;
            filter: blur(8px);
            opacity: 0.7;
        }

        @keyframes neonGlow {
            0% { background-position: 0 0; }
            50% { background-position: 400% 0; }
            100% { background-position: 0 0; }
        }

        /* Floating Form Container */
        .signupSection {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            animation: floatIn 1s ease-out;
            position: relative;
        }

        @keyframes floatIn {
            0% {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .signupForm {
            padding: 45px;
            border-radius: var(--border-radius);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
        }

        /* Animated Header */
        .form-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .logo-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 25px;
            position: relative;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--danger-color), var(--primary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            color: white;
            box-shadow:
                0 10px 30px rgba(239, 71, 111, 0.3),
                inset 0 -5px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .signupForm h2 {
            text-align: center;
            color: var(--dark-color);
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 32px;
            background: linear-gradient(135deg, var(--dark-color), var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }

        .signupForm h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--danger-color), var(--primary-color));
            border-radius: 2px;
            animation: widthGrow 2s ease-in-out infinite alternate;
        }

        @keyframes widthGrow {
            0% { width: 80px; }
            100% { width: 120px; }
        }

        .form-header p {
            color: var(--gray-color);
            font-size: 15px;
            max-width: 300px;
            margin: 0 auto;
        }

        /* Enhanced Input Fields */
        .input-group {
            position: relative;
            margin-bottom: 30px;
        }

        .inputFields {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid transparent;
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.05),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
        }

        .inputFields:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow:
                0 10px 25px rgba(67, 97, 238, 0.15),
                0 0 0 3px rgba(67, 97, 238, 0.1),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        .inputFields::placeholder {
            color: #adb5bd;
            transition: var(--transition);
        }

        .inputFields:focus::placeholder {
            transform: translateX(10px);
            opacity: 0.5;
        }

        /* Floating Labels */
        .floating-label {
            position: absolute;
            top: 18px;
            left: 20px;
            font-size: 16px;
            color: var(--gray-color);
            pointer-events: none;
            transition: var(--transition);
            background: transparent;
            padding: 0 5px;
        }

        .inputFields:focus + .floating-label,
        .inputFields:not(:placeholder-shown) + .floating-label {
            top: -10px;
            left: 15px;
            font-size: 12px;
            color: var(--primary-color);
            background: white;
            font-weight: 500;
        }

        /* Animated Button */
        #join-btn {
            background: linear-gradient(135deg, var(--danger-color), #ff6b9d, var(--danger-color));
            background-size: 200% 200%;
            color: white;
            border: none;
            padding: 18px 45px;
            font-size: 17px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-width: 220px;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 10px 25px rgba(239, 71, 111, 0.3),
                0 5px 15px rgba(239, 71, 111, 0.2);
            animation: buttonGlow 3s ease-in-out infinite alternate;
        }

        @keyframes buttonGlow {
            0% {
                box-shadow:
                    0 10px 25px rgba(239, 71, 111, 0.3),
                    0 5px 15px rgba(239, 71, 111, 0.2);
            }
            100% {
                box-shadow:
                    0 15px 35px rgba(239, 71, 111, 0.4),
                    0 10px 25px rgba(239, 71, 111, 0.3);
            }
        }

        #join-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: var(--transition);
        }

        #join-btn:hover {
            background-position: right center;
            transform: translateY(-3px) scale(1.02);
        }

        #join-btn:hover::before {
            left: 100%;
        }

        #join-btn:active {
            transform: translateY(0) scale(0.98);
        }

        /* Enhanced Response Message */
        .response-message {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(6, 214, 160, 0.1), rgba(67, 97, 238, 0.1));
            border: 1px solid rgba(6, 214, 160, 0.2);
            color: var(--success-color);
            font-size: 14px;
            display: none;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .response-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 70%
            );
            animation: shimmer 2s infinite linear;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Footer Note */
        .footer-note {
            text-align: center;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--gray-color);
            font-size: 13px;
            position: relative;
        }

        .footer-note::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .shape-1, .shape-2, .shape-3 {
                display: none;
            }

            .signupForm {
                padding: 30px;
                backdrop-filter: blur(15px);
                background: rgba(255, 255, 255, 0.95);
            }

            .signupForm h2 {
                font-size: 28px;
            }

            .logo-container {
                width: 80px;
                height: 80px;
            }

            .logo {
                font-size: 36px;
            }
        }

        @media (max-width: 480px) {
            body.user-data {
                padding: 15px;
            }

            .signupForm {
                padding: 25px 20px;
            }

            .signupForm h2 {
                font-size: 24px;
            }

            .inputFields {
                padding: 16px;
                font-size: 15px;
            }

            #join-btn {
                width: 100%;
                padding: 16px 30px;
                font-size: 16px;
            }
        }

        /* Loading Animation */
        .loading-dots {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .loading-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            animation: dotPulse 1.4s infinite ease-in-out both;
        }

        .loading-dots span:nth-child(1) { animation-delay: -0.32s; }
        .loading-dots span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes dotPulse {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        /* Cursor Animation */
        .typing-cursor {
            display: inline-block;
            width: 3px;
            height: 24px;
            background: var(--primary-color);
            margin-left: 2px;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
    </style>
</head>

<body class="user-data">

    <!-- Animated Background -->
    <div class="background-animation"></div>

    <!-- Animated Shapes -->
    <div class="animated-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>

    <!-- Main Form -->
    <div class="signupSection">
        <form action="{{ url('/user-data-remove') }}" method="post" class="signupForm neon-border" name="signupform" id="dataRemovalForm">
            @csrf

            <div class="form-header">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
                <h2>Data Removal Request</h2>
                <p>Request removal of your personal data from Alokha</p>
            </div>

            <div class="form-content">
                <div class="input-group">
                    <input type="email" class="inputFields" id="email" name="email"
                           placeholder=" "
                           value="{{ old('email') }}"
                           required
                           autocomplete="email">
                    <label class="floating-label" for="email">
                        <i class="fas fa-envelope me-1"></i> Email Address
                    </label>
                    <span class="validation-hint" id="email-hint"></span>
                </div>

                <div class="input-group">
                    <input type="text" class="inputFields" id="username" name="username"
                           placeholder=" "
                           value="{{ old('username') }}"
                           oninput="validateUsername(this.value)"
                           required
                           autocomplete="username">
                    <label class="floating-label" for="username">
                        <i class="fas fa-user me-1"></i> Username
                    </label>
                    <span class="validation-hint" id="username-hint"></span>
                </div>

                <div class="input-group">
                    <input type="tel" class="inputFields" id="phone" name="phone"
                           placeholder=" "
                           value="{{ old('phone') }}"
                           required
                           autocomplete="tel">
                    <label class="floating-label" for="phone">
                        <i class="fas fa-phone me-1"></i> Phone Number
                    </label>
                    <span class="validation-hint" id="phone-hint"></span>
                </div>

                <div class="input-group">
                    <textarea class="inputFields" id="reason" name="reason"
                              placeholder=" "
                              rows="3">{{ old('reason') }}</textarea>
                    <label class="floating-label" for="reason">
                        <i class="fas fa-comment-dots me-1"></i> Reason for Removal
                    </label>
                    <span class="validation-hint">Helps us improve our service</span>
                </div>

                <input type="hidden" name="remove" value="Remove">

                <div class="text-center mt-4">
                    <button type="submit" id="join-btn" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i>
                        <span id="btn-text">Remove My Data</span>
                    </button>
                </div>

                <div id="responseMessage" class="response-message mt-4 {{ session('msg_send') ? 'show' : '' }}">
                    <i class="fas fa-check-circle me-2"></i>
                    <p class="mb-1">Thank you for your request. We will process your data removal request within 2-3 business days.</p>
                    <p class="small mb-0">You will receive a confirmation email once completed.</p>
                </div>
            </div>

            <div class="footer-note">
                <p>
                    <i class="fas fa-shield-alt me-1"></i>
                    Your privacy is important to us. All data removal requests are processed in accordance with our
                    <a href="{{ url('/privacy-policy') }}" class="text-decoration-underline">Privacy Policy</a>.
                </p>
                <p class="mt-2 small">
                    Need help? <a href="{{ url('/contact') }}" class="text-decoration-underline">Contact Support</a>
                </p>
            </div>
        </form>
    </div>

    <!-- JavaScript Libraries -->
    <script src="{{ url('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ url('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        // Create floating particles
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');

                // Random size between 3-10px
                const size = Math.random() * 7 + 3;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;

                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;

                // Random animation duration
                const duration = Math.random() * 20 + 10;
                particle.style.animationDuration = `${duration}s`;

                // Random delay
                const delay = Math.random() * 5;
                particle.style.animationDelay = `${delay}s`;

                // Random opacity
                particle.style.opacity = Math.random() * 0.3 + 0.1;

                container.appendChild(particle);
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Create particles
            createParticles();

            // Form submission handling
            const form = document.getElementById('dataRemovalForm');
            const submitBtn = document.getElementById('join-btn');
            const btnText = document.getElementById('btn-text');

            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return;
                }

                // Show loading state
                btnText.innerHTML = 'Processing<span class="loading-dots"><span></span><span></span><span></span></span>';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.8';
            });

            // Input validation functions
            function validateForm() {
                const email = document.getElementById('email').value;
                const username = document.getElementById('username').value;
                const phone = document.getElementById('phone').value;

                let isValid = true;

                // Email validation
                if (!validateEmail(email)) {
                    showError('email', 'Please enter a valid email address');
                    isValid = false;
                } else {
                    showSuccess('email');
                }

                // Username validation
                if (username.length < 3) {
                    showError('username', 'Username must be at least 3 characters');
                    isValid = false;
                } else {
                    showSuccess('username');
                }

                // Phone validation
                if (!validatePhone(phone)) {
                    showError('phone', 'Please enter a valid phone number');
                    isValid = false;
                } else {
                    showSuccess('phone');
                }

                return isValid;
            }

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            function validatePhone(phone) {
                const re = /^[\+]?[1-9][\d]{0,15}$/;
                return re.test(phone.replace(/[\s\-\(\)\.]/g, ''));
            }

            function validateUsername(username) {
                const hint = document.getElementById('username-hint');
                if (username.length < 3 && username.length > 0) {
                    hint.textContent = 'Minimum 3 characters required';
                    hint.classList.remove('success');
                    hint.classList.add('error');
                    return false;
                } else if (username.length >= 3) {
                    hint.textContent = '✓ Username looks good!';
                    hint.classList.remove('error');
                    hint.classList.add('success');
                    return true;
                } else {
                    hint.textContent = 'Minimum 3 characters';
                    hint.classList.remove('error', 'success');
                    return false;
                }
            }

            function showError(field, message) {
                const hint = document.getElementById(field + '-hint');
                hint.textContent = '✗ ' + message;
                hint.classList.remove('success');
                hint.classList.add('error');

                const input = document.getElementById(field);
                input.style.borderColor = 'var(--danger-color)';
                input.style.boxShadow = '0 10px 25px rgba(239, 71, 111, 0.15), 0 0 0 3px rgba(239, 71, 111, 0.1)';

                // Add shake animation
                input.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    input.style.animation = '';
                }, 500);
            }

            function showSuccess(field) {
                const hint = document.getElementById(field + '-hint');
                hint.textContent = '✓ Looks good!';
                hint.classList.remove('error');
                hint.classList.add('success');

                const input = document.getElementById(field);
                input.style.borderColor = 'var(--success-color)';
                input.style.boxShadow = '0 10px 25px rgba(6, 214, 160, 0.15), 0 0 0 3px rgba(6, 214, 160, 0.1)';
            }

            // Real-time validation
            document.getElementById('email').addEventListener('blur', function() {
                if (this.value && !validateEmail(this.value)) {
                    showError('email', 'Please enter a valid email address');
                } else if (this.value) {
                    showSuccess('email');
                }
            });

            document.getElementById('phone').addEventListener('blur', function() {
                if (this.value && !validatePhone(this.value)) {
                    showError('phone', 'Please enter a valid phone number');
                } else if (this.value) {
                    showSuccess('phone');
                }
            });

            // Add shake animation keyframes
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }
            `;
            document.head.appendChild(style);

            // SweetAlert for session messages
            @if (Session::has('success'))
                Swal.fire({
                    title: "🎉 Request Submitted!",
                    html: `
                        <div style="text-align: center;">
                            <div style="font-size: 60px; color: #06d6a0; margin-bottom: 20px;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <p style="font-size: 18px; margin-bottom: 20px;">{{ Session::get('success') }}</p>
                            <p style="color: #6c757d; font-size: 14px;">We've sent a confirmation to your email.</p>
                        </div>
                    `,
                    icon: "success",
                    confirmButtonColor: "#06d6a0",
                    confirmButtonText: "Got it!",
                    background: "rgba(255, 255, 255, 0.95)",
                    backdrop: "rgba(0, 0, 0, 0.5)"
                });
            @elseif (Session::has('error'))
                Swal.fire({
                    title: "⚠️ Oops!",
                    html: `
                        <div style="text-align: center;">
                            <div style="font-size: 60px; color: #ef476f; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <p style="font-size: 18px; margin-bottom: 20px;">{{ Session::get('error') }}</p>
                            <p style="color: #6c757d; font-size: 14px;">Please check your details and try again.</p>
                        </div>
                    `,
                    icon: "error",
                    confirmButtonColor: "#ef476f",
                    confirmButtonText: "Try Again",
                    background: "rgba(255, 255, 255, 0.95)",
                    backdrop: "rgba(0, 0, 0, 0.5)"
                });
            @endif
        });
    </script>
</body>

</html>
