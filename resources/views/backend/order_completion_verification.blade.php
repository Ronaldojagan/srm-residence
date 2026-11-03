<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery Verification - Alokha</title>
    <link rel="shortcut icon" href="{{ url('img/fev.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #0ea1b1;
            --primary-dark: #0c8a97;
            --primary-light: #e0f7f9;
            --success: #10B981;
            --success-light: #D1FAE5;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --danger: #EF4444;
            --danger-light: #FEE2E2;
            --light-bg: #f0f9fa;
            --card-shadow: 0 8px 30px rgba(14, 161, 177, 0.12);
            --hover-shadow: 0 12px 40px rgba(14, 161, 177, 0.18);
            --primary-gradient: linear-gradient(135deg, #0ea1b1 0%, #10b9cb 100%);
            --success-gradient: linear-gradient(135deg, #10B981 0%, #34D399 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--light-bg) 0%, #ffffff 100%);
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 300px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            z-index: -1;
            opacity: 0.1;
            border-radius: 0 0 50% 50% / 0 0 100px 100px;
        }

        .verification-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            padding: 35px;
            margin-bottom: 24px;
            border: 1px solid rgba(14, 161, 177, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .verification-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .verification-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--hover-shadow);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 24px;
            color: white;
            padding: 40px 35px;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(14, 161, 177, 0.25);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .page-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            opacity: 0.95;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .order-info {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .order-info .row {
            margin: 0;
        }

        .order-info-item {
            padding: 10px 0;
        }

        .order-info-item strong {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .order-info-item span {
            font-size: 18px;
            font-weight: 600;
        }

        .section-title {
            color: var(--primary);
            margin-bottom: 24px;
            font-weight: 700;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .signature-container {
            border: 3px dashed rgba(14, 161, 177, 0.3);
            border-radius: 16px;
            background: linear-gradient(135deg, #f8fdfe 0%, #ffffff 100%);
            padding: 15px;
            position: relative;
            transition: all 0.3s ease;
        }

        .signature-container:hover {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .signature-pad {
            width: 100%;
            height: 220px;
            background: white;
            border-radius: 12px;
            cursor: crosshair;
            touch-action: none;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .signature-instruction {
            text-align: center;
            color: #6B7280;
            font-size: 14px;
            margin-top: 12px;
            font-weight: 500;
        }

        .signature-instruction i {
            color: var(--primary);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .camera-preview {
            border: 3px dashed rgba(14, 161, 177, 0.3);
            border-radius: 16px;
            background: linear-gradient(135deg, #f8fdfe 0%, #ffffff 100%);
            min-height: 350px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .camera-preview:hover {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .preview-image {
            max-width: 100%;
            max-height: 350px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-camera,
        .btn-gallery,
        .btn-clear,
        .btn-submit,
        .btn-primary {
            border: none;
            border-radius: 14px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-camera::before,
        .btn-gallery::before,
        .btn-clear::before,
        .btn-submit::before,
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-camera:hover::before,
        .btn-gallery:hover::before,
        .btn-clear:hover::before,
        .btn-submit:hover::before,
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-camera {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-camera:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(14, 161, 177, 0.3);
        }

        .btn-gallery {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .btn-gallery:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-clear {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
        }

        .btn-clear:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
            padding: 16px 40px;
            font-size: 17px;
            min-width: 280px;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        .btn-submit:disabled {
            background: linear-gradient(135deg, #9CA3AF 0%, #6B7280 100%);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .requirement-badge {
            background: linear-gradient(135deg, var(--warning) 0%, #f59e0b 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f8fdfe;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .status-indicator:hover {
            background: var(--primary-light);
        }

        .status-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #D1D5DB;
            transition: all 0.3s ease;
            box-shadow: 0 0 0 4px rgba(209, 213, 219, 0.2);
        }

        .status-dot.completed {
            background: var(--success);
            box-shadow: 0 0 0 4px var(--success-light);
            animation: checkPulse 1s ease-in-out;
        }

        @keyframes checkPulse {
            0% {
                transform: scale(0.8);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .status-text {
            font-size: 15px;
            color: #6B7280;
            font-weight: 500;
            flex: 1;
        }

        .status-text.completed {
            color: var(--success);
            font-weight: 600;
        }

        .status-text i {
            margin-left: 8px;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .button-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .image-info {
            background: var(--success-light);
            border-radius: 12px;
            padding: 14px 20px;
            margin-top: 16px;
            font-size: 14px;
            color: #065f46;
            font-weight: 500;
            border-left: 4px solid var(--success);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .image-info i {
            color: var(--success);
            margin-right: 8px;
        }

        .signature-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .signature-actions {
                width: 100%;
            }

            .signature-actions button {
                flex: 1;
            }
        }


        .signature-preview {
            border: 2px solid var(--primary-light);
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px;
            background: white;
            display: none;
            box-shadow: 0 4px 12px rgba(14, 161, 177, 0.1);
        }

        .signature-preview img {
            max-width: 100%;
            max-height: 120px;
            border-radius: 8px;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .modal-header {
            border-bottom: 2px solid var(--primary-light);
            padding: 24px 30px;
            background: linear-gradient(135deg, #f8fdfe 0%, #ffffff 100%);
        }

        .modal-title {
            color: var(--primary);
            font-weight: 700;
            font-size: 20px;
        }

        .modal-body {
            padding: 30px;
            background: #f8fdfe;
        }

        .modal-footer {
            border-top: 2px solid var(--primary-light);
            padding: 20px 30px;
            background: #ffffff;
        }

        #video {
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            background: #000;
        }

        .btn-close {
            opacity: 0.6;
            transition: all 0.3s ease;
        }

        .btn-close:hover {
            opacity: 1;
            transform: rotate(90deg);
        }

        .modal-backdrop.show {
            opacity: 0.7 !important;
        }

        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: none;
        }

        #cameraModal {
            z-index: 1055 !important;
        }

        #cameraModal .modal-backdrop {
            z-index: 1050 !important;
        }

        .video-container {
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #video.active {
            display: block !important;
        }

        .camera-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 16px;
            z-index: 10;
        }

        .camera-loading i {
            font-size: 32px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .camera-placeholder i {
            color: var(--primary);
            opacity: 0.5;
        }

        .alert-danger {
            background: var(--danger-light);
            border: 2px solid var(--danger);
            color: #991b1b;
            border-radius: 12px;
            padding: 16px;
        }

        /* SweetAlert2 Custom Styles */
        .swal2-popup {
            border-radius: 24px !important;
            padding: 2rem !important;
        }

        .swal2-title {
            color: var(--primary) !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 700 !important;
        }

        .swal2-confirm {
            background: var(--primary-gradient) !important;
            border-radius: 12px !important;
            padding: 12px 32px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 16px rgba(14, 161, 177, 0.3) !important;
        }

        .swal2-cancel {
            border-radius: 12px !important;
            padding: 12px 32px !important;
            font-weight: 600 !important;
        }

        .swal2-icon {
            border-width: 3px !important;
        }

        .swal2-icon.swal2-success {
            border-color: #10B981 !important;
        }

        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            background-color: #10B981 !important;
        }

        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: rgba(16, 185, 129, 0.3) !important;
        }

        .swal2-icon.swal2-warning {
            border-color: #F59E0B !important;
            color: #F59E0B !important;
        }

        .swal2-icon.swal2-question {
            border-color: #0ea1b1 !important;
            color: #0ea1b1 !important;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 30px 25px;
            }

            .page-header h2 {
                font-size: 24px;
            }

            .verification-card {
                padding: 25px;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group .btn {
                width: 100%;
            }

            .btn-submit {
                min-width: 100%;
            }

            .order-info-item {
                text-align: center;
                margin-bottom: 15px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 15px;
            }

            .verification-card {
                padding: 20px;
            }

            .section-title {
                font-size: 18px;
            }

            .signature-pad {
                height: 180px;
            }

            .camera-preview {
                min-height: 250px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="page-header">
                    <div class="text-center">
                        <h2>
                            <i class="fas fa-clipboard-check me-2"></i>Delivery Verification
                        </h2>
                        <p class="mb-4">Please complete the following steps to finalize order delivery</p>
                    </div>

                    <div class="order-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="order-info-item">
                                    <strong>Order Number</strong>
                                    <span>{{ $order->order_no }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="order-info-item">
                                    <strong>Customer Name</strong>
                                    <span>{{ $order->user->name ?? 'Guest' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="verification-card text-center">
                    <div class="requirement-badge mb-4">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Both steps are mandatory</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="status-indicator">
                                <div class="status-dot" id="signature-status"></div>
                                <span class="status-text" id="signature-text">Signature</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="status-indicator">
                                <div class="status-dot" id="camera-status"></div>
                                <span class="status-text" id="camera-text">Delivery Proof Photo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="verificationForm"
                    action="{{ url('orders/' . Crypt::encrypt($order->id) . '/save-verification') }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="verification-card">
                        <h4 class="section-title">
                            <i class="fas fa-signature"></i>
                            <span>Signature</span>
                        </h4>
                        <p class="text-muted mb-4">Please have the sign in the box below</p>

                        <div class="signature-container">
                            <canvas id="signature-pad" class="signature-pad"></canvas>
                            <div class="signature-instruction">
                                <i class="fas fa-hand-pointer me-1"></i>
                                Sign in the box above using mouse or touch
                            </div>
                        </div>
                        <input type="hidden" name="signature" id="signature-input">

                        <div class="signature-actions">
                            <button type="button" class="btn btn-clear" id="clear-signature">
                                <i class="fas fa-eraser me-2"></i>Clear Signature
                            </button>
                            <button type="button" class="btn btn-primary d-none" id="undo-signature">
                                <i class="fas fa-undo me-2"></i>Undo Last Stroke
                            </button>
                        </div>

                        <div class="signature-preview" id="signature-preview">
                            <small class="text-muted fw-semibold">Signature Preview:</small>
                            <div class="mt-2">
                                <img id="signature-preview-img" src="" alt="Signature Preview">
                            </div>
                        </div>
                    </div>

                    <div class="verification-card">
                        <h4 class="section-title">
                            <i class="fas fa-camera"></i>
                            <span>Delivery Proof Photo</span>
                        </h4>
                        <p class="text-muted mb-4">Take a photo using camera or upload from gallery</p>

                        <div class="camera-preview" id="camera-preview">
                            <div class="text-center camera-placeholder" id="camera-placeholder">
                                <i class="fas fa-camera fa-3x mb-3"></i>
                                <p class="text-muted fw-semibold">No photo selected</p>
                                <small class="text-muted">Click button below to capture or upload</small>
                            </div>
                            <img id="captured-image" class="preview-image d-none">
                        </div>

                        <input type="file" id="file-input" accept="image/*" class="d-none">
                        <input type="hidden" name="delivery_proof" id="delivery-proof-input">

                        <div class="button-group">
                            <button type="button" class="btn btn-camera" id="start-camera" data-bs-toggle="modal"
                                data-bs-target="#cameraModal">
                                <i class="fas fa-camera me-2"></i>Take Photo
                            </button>

                            <div class="file-input-wrapper">
                                <button type="button" class="btn btn-gallery" id="gallery-btn">
                                    <i class="fas fa-images me-2"></i>Choose from Gallery
                                </button>
                                <input type="file" id="gallery-input" accept="image/*">
                            </div>

                            <button type="button" class="btn btn-clear d-none" id="retake-photo">
                                <i class="fas fa-redo me-2"></i>Change Photo
                            </button>
                        </div>

                        <div class="image-info d-none" id="image-info">
                            <i class="fas fa-check-circle me-2"></i>
                            <span id="image-info-text">Photo selected successfully</span>
                        </div>
                    </div>

                    <div class="verification-card text-center">
                        <button type="submit" class="btn btn-submit btn-lg" id="submit-btn" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Complete Order Delivery
                        </button>
                        <p class="mt-3 mb-0" id="submit-message" style="font-size: 15px; color: #6B7280;">
                            Please complete both verification steps above
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Camera Modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraModalLabel">
                        <i class="fas fa-camera me-2"></i>Take Delivery Proof Photo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="camera-error" class="alert alert-danger d-none" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="camera-error-text">Camera access denied. Please allow camera permissions.</span>
                    </div>
                    <div class="video-container" style="position: relative; width: 100%; max-width: 100%;">
                        <div class="camera-loading" id="camera-loading">
                            <i class="fas fa-spinner"></i>
                            <p class="mt-2">Starting camera...</p>
                        </div>
                        <video id="video" autoplay playsinline muted
                            style="width: 100%; height: auto; max-height: 400px; border-radius: 12px; background: #000; display: none;"></video>
                    </div>
                    <canvas id="canvas" class="d-none"></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" id="cancel-camera-btn" data-bs-dismiss="modal"
                        style="background: linear-gradient(135deg, #9CA3AF 0%, #6B7280 100%); color: white; border: none; border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn" id="capture-btn" disabled
                        style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%); color: white; border: none; border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                        <i class="fas fa-camera me-2"></i>Capture Photo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        class DeliveryVerification {
            constructor() {
                this.signaturePad = null;
                this.stream = null;
                this.isSignatureComplete = false;
                this.isPhotoComplete = false;
                this.currentImageFile = null;
                this.undoStack = [];
                this.cameraModal = null;

                this.initializeSignaturePad();
                this.initializeCameraModal();
                this.initializeFileUpload();
                this.updateSubmitButton();
            }

            initializeSignaturePad() {
                const canvas = document.getElementById('signature-pad');

                this.setCanvasDimensions(canvas);

                this.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 2,
                    maxWidth: 4,
                    throttle: 16,
                    minDistance: 5
                });

                document.getElementById('clear-signature').addEventListener('click', () => {
                    this.clearSignature();
                });

                document.getElementById('undo-signature').addEventListener('click', () => {
                    this.undoSignature();
                });

                this.signaturePad.addEventListener('beginStroke', () => {
                    if (!this.signaturePad.isEmpty()) {
                        this.undoStack.push(this.signaturePad.toData());
                    }
                });

                this.signaturePad.addEventListener('endStroke', () => {
                    if (!this.signaturePad.isEmpty()) {
                        this.isSignatureComplete = true;
                        this.updateStatus('signature', true);
                        this.updateSubmitButton();
                        this.updateSignatureData();
                    }
                });

                window.addEventListener('resize', () => {
                    this.setCanvasDimensions(canvas);
                    this.signaturePad.clear();
                });

                canvas.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                });

                canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                });
            }

            setCanvasDimensions(canvas) {
                const container = canvas.parentElement;
                const rect = container.getBoundingClientRect();
                const ratio = Math.max(window.devicePixelRatio || 1, 1);

                canvas.width = rect.width * ratio;
                canvas.height = 220 * ratio;
                canvas.style.width = '100%';
                canvas.style.height = '220px';

                const context = canvas.getContext('2d');
                context.scale(ratio, ratio);
            }

            clearSignature() {
                this.signaturePad.clear();
                this.undoStack = [];
                this.isSignatureComplete = false;
                this.updateStatus('signature', false);
                this.updateSubmitButton();
                document.getElementById('signature-preview').style.display = 'none';
                document.getElementById('signature-input').value = '';
            }

            undoSignature() {
                if (this.undoStack.length > 0) {
                    const previousState = this.undoStack.pop();
                    this.signaturePad.fromData(previousState);

                    if (this.signaturePad.isEmpty()) {
                        this.isSignatureComplete = false;
                        this.updateStatus('signature', false);
                        document.getElementById('signature-preview').style.display = 'none';
                    } else {
                        this.updateSignatureData();
                    }
                    this.updateSubmitButton();
                } else {
                    this.clearSignature();
                }
            }

            updateSignatureData() {
                const signatureData = this.signaturePad.toDataURL('image/png');
                document.getElementById('signature-input').value = signatureData;

                const preview = document.getElementById('signature-preview');
                const previewImg = document.getElementById('signature-preview-img');
                previewImg.src = signatureData;
                preview.style.display = 'block';
            }

            initializeCameraModal() {
                // Initialize Bootstrap modal
                this.cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'), {
                    backdrop: 'static',
                    keyboard: false
                });

                // Add event listeners for modal events
                const cameraModalElement = document.getElementById('cameraModal');
                const startCameraBtn = document.getElementById('start-camera');
                const captureBtn = document.getElementById('capture-btn');
                const cancelBtn = document.getElementById('cancel-camera-btn');

                // When modal opens
                cameraModalElement.addEventListener('shown.bs.modal', () => {
                    this.startCamera();
                });

                // When modal closes
                cameraModalElement.addEventListener('hidden.bs.modal', () => {
                    this.stopCamera();
                    document.getElementById('video').style.display = 'none';
                    document.getElementById('camera-loading').style.display = 'block';
                    captureBtn.disabled = true;
                });

                // Capture button click
                captureBtn.addEventListener('click', () => {
                    this.capturePhoto();
                    this.cameraModal.hide();
                });

                // Cancel button click
                cancelBtn.addEventListener('click', () => {
                    this.cameraModal.hide();
                });
            }

            initializeFileUpload() {
                const galleryInput = document.getElementById('gallery-input');
                const galleryBtn = document.getElementById('gallery-btn');
                const retakeBtn = document.getElementById('retake-photo');

                galleryBtn.addEventListener('click', () => {
                    galleryInput.click();
                });

                galleryInput.addEventListener('change', (event) => {
                    const file = event.target.files[0];
                    if (file) {
                        this.handleFileSelect(file);
                    }
                });

                retakeBtn.addEventListener('click', () => {
                    this.retakePhoto();
                });
            }

            async startCamera() {
                const video = document.getElementById('video');
                const loadingDiv = document.getElementById('camera-loading');
                const captureBtn = document.getElementById('capture-btn');
                const errorDiv = document.getElementById('camera-error');

                loadingDiv.style.display = 'block';
                video.style.display = 'none';
                errorDiv.classList.add('d-none');
                captureBtn.disabled = true;

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment',
                            width: {
                                ideal: 1920
                            },
                            height: {
                                ideal: 1080
                            }
                        },
                        audio: false
                    });

                    video.srcObject = this.stream;

                    video.onloadedmetadata = () => {
                        video.play().then(() => {
                            loadingDiv.style.display = 'none';
                            video.style.display = 'block';
                            video.classList.add('active');
                            captureBtn.disabled = false;
                        }).catch(err => {
                            console.error('Error playing video:', err);
                            this.showCameraError('Unable to start video playback.');
                            loadingDiv.style.display = 'none';
                        });
                    };

                } catch (err) {
                    console.error('Error accessing camera:', err);
                    loadingDiv.style.display = 'none';
                    this.showCameraError(
                        'Unable to access camera. Please check permissions or try uploading from gallery.');
                }
            }

            showCameraError(message) {
                const errorDiv = document.getElementById('camera-error');
                const errorText = document.getElementById('camera-error-text');
                errorText.textContent = message;
                errorDiv.classList.remove('d-none');
            }

            stopCamera() {
                const video = document.getElementById('video');

                if (this.stream) {
                    this.stream.getTracks().forEach(track => {
                        track.stop();
                    });
                    this.stream = null;
                }

                if (video.srcObject) {
                    video.srcObject = null;
                }

                video.classList.remove('active');
            }

            capturePhoto() {
                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                const context = canvas.getContext('2d');

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob((blob) => {
                    this.handleImageBlob(blob, 'camera');
                }, 'image/jpeg', 0.8);
            }

            handleFileSelect(file) {
                if (!file.type.startsWith('image/')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File',
                        text: 'Please select an image file (JPEG, PNG, etc.)',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal2-confirm'
                        }
                    });
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'Please select an image smaller than 5MB',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal2-confirm'
                        }
                    });
                    return;
                }

                this.currentImageFile = file;
                this.displayImagePreview(file);
                this.convertFileToBase64(file);
            }

            handleImageBlob(blob, source) {
                const file = new File([blob], `delivery_proof_${Date.now()}.jpg`, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });

                this.currentImageFile = file;
                this.displayImagePreview(file);
                this.convertFileToBase64(file);
            }

            displayImagePreview(file) {
                const preview = document.getElementById('captured-image');
                const placeholder = document.getElementById('camera-placeholder');
                const imageInfo = document.getElementById('image-info');
                const imageInfoText = document.getElementById('image-info-text');

                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    placeholder.classList.add('d-none');

                    imageInfoText.textContent = `Photo selected: ${file.name} (${this.formatFileSize(file.size)})`;
                    imageInfo.classList.remove('d-none');

                    document.getElementById('retake-photo').classList.remove('d-none');
                    document.getElementById('start-camera').classList.add('d-none');
                    document.getElementById('gallery-btn').classList.add('d-none');

                    this.isPhotoComplete = true;
                    this.updateStatus('camera', true);
                    this.updateSubmitButton();
                };
                reader.readAsDataURL(file);
            }

            convertFileToBase64(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('delivery-proof-input').value = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            retakePhoto() {
                const preview = document.getElementById('captured-image');
                const placeholder = document.getElementById('camera-placeholder');
                const imageInfo = document.getElementById('image-info');

                preview.classList.add('d-none');
                placeholder.classList.remove('d-none');
                imageInfo.classList.add('d-none');

                document.getElementById('retake-photo').classList.add('d-none');
                document.getElementById('start-camera').classList.remove('d-none');
                document.getElementById('gallery-btn').classList.remove('d-none');

                document.getElementById('gallery-input').value = '';
                document.getElementById('delivery-proof-input').value = '';
                this.currentImageFile = null;

                this.isPhotoComplete = false;
                this.updateStatus('camera', false);
                this.updateSubmitButton();
            }

            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            updateStatus(type, completed) {
                const statusDot = document.getElementById(`${type}-status`);
                const statusText = document.getElementById(`${type}-text`);

                if (completed) {
                    statusDot.classList.add('completed');
                    statusText.classList.add('completed');
                    statusText.innerHTML =
                        `${type === 'signature' ? 'Customer Signature' : 'Delivery Proof Photo'} <i class="fas fa-check"></i>`;
                } else {
                    statusDot.classList.remove('completed');
                    statusText.classList.remove('completed');
                    statusText.innerHTML = type === 'signature' ? 'Customer Signature' : 'Delivery Proof Photo';
                }
            }

            updateSubmitButton() {
                const submitBtn = document.getElementById('submit-btn');
                const submitMessage = document.getElementById('submit-message');

                if (this.isSignatureComplete && this.isPhotoComplete) {
                    submitBtn.disabled = false;
                    submitMessage.innerHTML =
                        '<i class="fas fa-check-circle me-2" style="color: var(--success);"></i>All verification steps completed - Ready to submit';
                    submitMessage.style.color = 'var(--success)';
                    submitMessage.style.fontWeight = '600';
                } else {
                    submitBtn.disabled = true;
                    const missingSteps = [];
                    if (!this.isSignatureComplete) missingSteps.push('signature');
                    if (!this.isPhotoComplete) missingSteps.push('photo');
                    submitMessage.innerHTML =
                        `<i class="fas fa-info-circle me-2"></i>Please complete: ${missingSteps.join(' and ')}`;
                    submitMessage.style.color = '#6B7280';
                    submitMessage.style.fontWeight = '500';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const verification = new DeliveryVerification();

            document.getElementById('verificationForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('submit-btn');
                const originalText = submitBtn.innerHTML;
                const form = this;

                Swal.fire({
                    title: 'Complete Order Delivery?',
                    html: '<p style="font-size: 15px; color: #6B7280; margin-top: 10px;">This will mark the order as completed. This action cannot be undone.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check me-2"></i>Yes, Complete Order!',
                    cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
                    customClass: {
                        popup: 'swal2-popup',
                        title: 'swal2-title',
                        htmlContainer: 'swal2-html-container',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    },
                    reverseButtons: true,
                    focusConfirm: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitBtn.innerHTML =
                            '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                        submitBtn.disabled = true;

                        Swal.fire({
                            title: 'Processing...',
                            html: 'Please wait while we complete the delivery verification.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading()
                        });

                        // Get CSRF token from meta tag or form input
                        const csrfToken = getCsrfToken();

                        // Create FormData from the form
                        const formData = new FormData(form);

                        fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: data.message || 'Order delivery has been completed successfully.',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        if (data.redirect) {
                                            window.location.href = data.redirect;
                                        } else {
                                            resetForm();
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: error.message ||
                                        'There was an error completing the delivery. Please try again.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    resetSubmitButton();
                                });
                            });

                        function resetSubmitButton() {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }

                        function resetForm() {
                            if (typeof verification !== 'undefined') {
                                verification.clearSignature();
                                verification.retakePhoto();
                            }
                            resetSubmitButton();
                        }
                    }
                });
            });

            function getCsrfToken() {
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    return metaTag.getAttribute('content');
                }

                const formInput = document.querySelector('input[name="_token"]');
                if (formInput) {
                    return formInput.value;
                }

                console.warn(
                    'CSRF token not found. Please add: <meta name="csrf-token" content="{{ csrf_token() }}"> to your head tag'
                    );
                return '';
            }
        });
    </script>
    <style>
        .swal-custom-popup {
            border-radius: 24px !important;
            padding: 2.5rem !important;
            box-shadow: 0 20px 60px rgba(14, 161, 177, 0.25) !important;
        }

        .swal-custom-title {
            color: #0ea1b1 !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 700 !important;
            font-size: 1.75rem !important;
            margin-bottom: 1rem !important;
        }

        .swal-custom-text {
            font-family: 'Poppins', sans-serif !important;
            color: #374151 !important;
            font-size: 1.05rem !important;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 12px 28px;
            font-weight: 600;
            color: #6B7280;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            border-color: #0ea1b1;
            color: #0ea1b1;
            background: rgba(14, 161, 177, 0.05);
        }

        .swal2-icon {
            border-width: 3px !important;
        }

        .swal2-icon.swal2-success {
            border-color: #10B981 !important;
        }

        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            background-color: #10B981 !important;
        }

        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: rgba(16, 185, 129, 0.3) !important;
        }

        .swal2-icon.swal2-warning {
            border-color: #F59E0B !important;
            color: #F59E0B !important;
        }

        .swal2-icon.swal2-question {
            border-color: #0ea1b1 !important;
            color: #0ea1b1 !important;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translate3d(0, -100%, 0);
            }

            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes fadeOutUp {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
                transform: translate3d(0, -100%, 0);
            }
        }

        .animate__animated {
            animation-duration: 0.5s;
            animation-fill-mode: both;
        }

        .animate__faster {
            animation-duration: 0.3s;
        }

        .animate__fadeInDown {
            animation-name: fadeInDown;
        }

        .animate__fadeOutUp {
            animation-name: fadeOutUp;
        }
    </style>
</body>

</html>
