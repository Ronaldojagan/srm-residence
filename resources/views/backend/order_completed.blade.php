<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - SRM RESIDENCE</title>
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
            --primary-dark: #0c8694;
            --primary-light: #10b9cb;
            --primary-gradient: linear-gradient(135deg, #0ea1b1 0%, #10b9cb 100%);
            --success: #10B981;
            --success-gradient: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            --warning: #F59E0B;
            --warning-gradient: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
            --danger: #EF4444;
            --light-bg: #f0f9fa;
            --card-shadow: 0 8px 32px rgba(14, 161, 177, 0.12);
            --hover-shadow: 0 16px 48px rgba(14, 161, 177, 0.20);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f0f9fa 0%, #e6f7f9 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #374151;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
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

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }

            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
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

        .container-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(14, 161, 177, 0.1);
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--hover-shadow);
            border-color: rgba(14, 161, 177, 0.3);
        }

        .card:hover::before {
            transform: scaleX(1);
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 24px 32px;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translate(-50%, -50%);
            }

            50% {
                transform: translate(-30%, -30%);
            }

            100% {
                transform: translate(-50%, -50%);
            }
        }

        .card-header h4 {
            position: relative;
            z-index: 1;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 32px;
        }

        .order-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-item {
            background: linear-gradient(135deg, rgba(14, 161, 177, 0.05) 0%, rgba(16, 185, 203, 0.05) 100%);
            border-radius: 18px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(14, 161, 177, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .summary-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .summary-item:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 8px 24px rgba(14, 161, 177, 0.15);
        }

        .summary-item:hover::before {
            opacity: 0.05;
        }

        .summary-label {
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .summary-value {
            font-size: 19px;
            font-weight: 700;
            color: #1F2937;
            position: relative;
            z-index: 1;
        }

        /* Order Items Table */
        .order-items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .order-items-table thead {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .order-items-table thead th {
            padding: 18px 20px;
            font-weight: 600;
            text-align: left;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .order-items-table thead th:first-child {
            border-top-left-radius: 16px;
        }

        .order-items-table thead th:last-child {
            border-top-right-radius: 16px;
            text-align: right;
        }

        .order-items-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(14, 161, 177, 0.1);
        }

        .order-items-table tbody tr:hover {
            background: linear-gradient(90deg, rgba(14, 161, 177, 0.05) 0%, rgba(14, 161, 177, 0.02) 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(14, 161, 177, 0.08);
        }

        .order-items-table tbody tr:last-child {
            border-bottom: none;
        }

        .order-items-table tbody td {
            padding: 20px;
            vertical-align: middle;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image {
            width: 70px;
            height: 70px;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(14, 161, 177, 0.15);
            border: 2px solid rgba(14, 161, 177, 0.1);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .order-items-table tbody tr:hover .product-image {
            transform: scale(1.08);
            box-shadow: 0 6px 16px rgba(14, 161, 177, 0.25);
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #1F2937;
            font-size: 15px;
            line-height: 1.4;
        }

        .product-meta {
            font-size: 13px;
            color: #6B7280;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .product-meta i {
            color: var(--primary);
        }

        .quantity-cell {
            text-align: center;
        }

        .quantity-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(14, 161, 177, 0.1) 0%, rgba(14, 161, 177, 0.05) 100%);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid rgba(14, 161, 177, 0.2);
        }

        .price-cell {
            text-align: center;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
        }

        .total-cell {
            text-align: right;
            font-weight: 700;
            color: var(--primary);
            font-size: 16px;
        }

        .price-original {
            text-decoration: line-through;
            color: #9CA3AF;
            font-size: 13px;
            display: block;
            margin-top: 4px;
        }

        .price-discount {
            background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin-top: 6px;
        }

        @media (max-width: 992px) {
            .order-items-table {
                display: block;
                overflow-x: auto;
            }

            .order-items-table thead {
                display: none;
            }

            .order-items-table tbody {
                display: block;
            }

            .order-items-table tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid rgba(14, 161, 177, 0.2);
                border-radius: 16px;
                padding: 15px;
                background: white;
            }

            .order-items-table tbody td {
                display: block;
                text-align: left !important;
                padding: 10px 0;
                border: none;
            }

            .order-items-table tbody td:before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--primary);
                display: block;
                margin-bottom: 5px;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .product-cell {
                flex-direction: column;
                align-items: flex-start;
            }

            .quantity-badge,
            .price-cell,
            .total-cell {
                text-align: left !important;
            }
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 14px;
            padding: 14px 28px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(14, 161, 177, 0.3);
            position: relative;
            overflow: hidden;
        }

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

        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(14, 161, 177, 0.4);
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
            border-radius: 14px;
            padding: 14px 28px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        }

        .btn-warning {
            background: var(--warning-gradient);
            border: none;
            border-radius: 14px;
            padding: 14px 28px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .status-badge {
            padding: 10px 20px;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .status-pending {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            color: #92400E;
        }

        .status-processing {
            background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
            color: #1E40AF;
        }

        .status-completed {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            color: #065F46;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            color: #991B1B;
        }

        .action-buttons {
            background: linear-gradient(135deg, rgba(14, 161, 177, 0.05) 0%, rgba(240, 249, 250, 0.8) 100%);
            border-radius: 20px;
            padding: 28px;
            margin-top: 30px;
            border: 2px solid rgba(14, 161, 177, 0.1);
        }

        .action-buttons h5 {
            margin-bottom: 20px;
            color: var(--primary);
            font-weight: 700;
        }

        .button-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .delivery-card {
            background: linear-gradient(135deg, rgba(14, 161, 177, 0.08) 0%, rgba(16, 185, 203, 0.08) 100%);
            border-radius: 20px;
            padding: 28px;
            border-left: 5px solid var(--primary);
            margin-bottom: 30px;
            box-shadow: 0 4px 16px rgba(14, 161, 177, 0.1);
        }

        .delivery-person {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .delivery-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 16px rgba(14, 161, 177, 0.2);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-up {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        .alert {
            border-radius: 16px;
            border: none;
            padding: 18px 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .alert-success {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            color: #065F46;
            border-left: 4px solid #10B981;
        }

        .alert-danger {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            color: #991B1B;
            border-left: 4px solid #EF4444;
        }

        .alert-warning {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            color: #92400E;
            border-left: 4px solid #F59E0B;
        }

        /* SweetAlert Custom Styling */
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

        @media (max-width: 576px) {
            .swal2-confirm {
                margin-bottom: 16px !important;
            }
        }

        .swal2-cancel {
            border-radius: 12px !important;
            padding: 12px 32px !important;
            font-weight: 600 !important;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }

            .order-summary {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group .btn {
                width: 100%;
            }

            .product-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .product-image {
                margin-bottom: 15px;
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
                            <i class="fas fa-receipt me-2"></i>Order Details
                        </h2>
                        <p class="mb-4">Manage and order information details</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success animate-up">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger animate-up">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <div class="card animate-up">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                            <h3 class="text-primary mb-0">Order #{{ $order->order_no }}</h3>
                            <span class="status-badge status-{{ strtolower($order->status) }}">
                                <i class="fas fa-info-circle me-1"></i> {{ ucfirst($order->status) }}
                            </span>
                        </div>

                        <div class="order-summary">
                            <div class="summary-item animate-up delay-1">
                                <span class="summary-label"><i class="fas fa-calendar me-2"></i>Order Date</span>
                                <span class="summary-value">{{ $order->created_at->format($date_format) }}</span>
                            </div>
                            <div class="summary-item animate-up delay-2">
                                <span class="summary-label"><i class="fas fa-money-bill-wave me-2"></i>Total
                                    Amount</span>
                                <span
                                    class="summary-value">{{ $currency }}{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <div class="summary-item animate-up delay-3">
                                <span class="summary-label"><i class="fas fa-credit-card me-2"></i>Payment Method</span>
                                <span class="summary-value">{{ strtoupper($order->payment->payment_method) }}</span>
                            </div>
                            <div class="summary-item animate-up delay-4">
                                <span class="summary-label"><i class="fas fa-check-circle me-2"></i>Payment
                                    Status</span>
                                <span
                                    class="summary-value
                                    @if ($order->payment->status === 'Paid' || $order->payment->status === 'Collected') text-success
                                    @elseif($order->payment->status === 'Pending') text-warning
                                    @else text-danger @endif">
                                    {{ $order->payment->status }}
                                </span>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-5 animate-up delay-1"><i class="fas fa-user me-2"></i>Customer Information
                        </h5>
                        <div class="row mb-4 animate-up delay-2">
                            <div class="col-md-6 mb-3">
                                <div class="summary-item h-100">
                                    <span class="summary-label">Name</span>
                                    <span class="summary-value">{{ $order->user->name ?? 'Guest' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="summary-item h-100">
                                    <span class="summary-label">Phone</span>
                                    <span class="summary-value">{{ $order->user->phone ?? $order->phone }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="summary-item">
                                    <span class="summary-label">Delivery Address</span>
                                    <span class="summary-value">
                                        {{ $order->shippingAddress->name }}<br>
                                        {{ $order->shippingAddress->phone }}<br>
                                        {{ $order->shippingAddress->full_address }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if ($order->deliveryMan)
                            <h5 class="mb-3 mt-5 animate-up delay-3"><i class="fas fa-truck me-2"></i>Delivery
                                Information</h5>
                            <div class="delivery-card animate-up delay-4">
                                <div class="delivery-person">
                                    @if ($order->deliveryMan->profile_image)
                                        <img src="{{ url('storage/delivery_man/' . $order->deliveryMan->profile_image) }}"
                                            class="delivery-avatar" alt="Delivery Person">
                                    @else
                                        <div
                                            class="delivery-avatar bg-primary d-flex align-items-center justify-content-center text-white">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ $order->deliveryMan->name }}</h6>
                                        <p class="text-muted mb-0">Delivery Partner</p>
                                        @if ($order->deliveryMan->phone)
                                            <small class="text-muted"><i
                                                    class="fas fa-phone me-1"></i>{{ $order->deliveryMan->phone }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <h5 class="mb-3 mt-5 animate-up"><i class="fas fa-shopping-cart me-2"></i>Order Items</h5>
                        <div class="card animate-up delay-1">
                            <div class="card-body">
                                @foreach ($order->orderItems as $item)
                                    @php
                                        $discountedTotal = $item->amount * $item->quantity;
                                        $originalTotal = $item->product->amount * $item->quantity;
                                    @endphp

                                    <div class="product-item d-flex align-items-center mb-3 pb-2"
                                        style="border-bottom: 1px solid #ccc;">
                                        @if ($item->product && $item->product->image)
                                            <img src="{{ url('storage/product/' . $item->product->image) }}"
                                                class="product-image me-2" alt="{{ $item->product->name }}"
                                                style="width:60px; height:60px; object-fit:cover;">
                                        @else
                                            <div class="product-image bg-light d-flex align-items-center justify-content-center me-2"
                                                style="width:60px; height:60px;">
                                                <i class="fas fa-box-open text-muted" style="font-size:1.5rem;"></i>
                                            </div>
                                        @endif

                                        <div class="product-details flex-grow-1">
                                            <h6 class="product-name mb-1">
                                                {{ $item->product->name ?? 'Product Deleted' }}</h6>
                                            @if ($item->quantity_unit)
                                                <div class="product-meta text-muted small">{{ $item->quantity_unit }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="product-price text-end">
                                            <div>
                                                {{ $currency }}{{ number_format($discountedTotal, 2) }}
                                                @if ($discountedTotal < $originalTotal)
                                                    <s class="text-muted small" style="font-size:0.85em;">
                                                        {{ $currency }}{{ number_format($originalTotal, 2) }}
                                                    </s>
                                                @endif
                                            </div>
                                            <div class="text-muted small">
                                                {{ $item->quantity }} ×
                                                {{ $currency }}{{ number_format($item->amount, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="card mt-4 animate-up delay-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span
                                        class="fw-semibold">{{ $currency }}{{ number_format($order->precheckout->sub_total ?? 0, 2) }}</span>
                                </div>
                                @if (isset($order->precheckout->delivery_charge) && $order->precheckout->delivery_charge > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Delivery Fee:</span>
                                        <span
                                            class="fw-semibold">{{ $currency }}{{ number_format($order->precheckout->delivery_charge, 2) }}</span>
                                    </div>
                                @endif
                                @if (isset($order->precheckout->platform_fee) && $order->precheckout->platform_fee > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Platform Fee:</span>
                                        <span
                                            class="fw-semibold">{{ $currency }}{{ number_format($order->precheckout->platform_fee, 2) }}</span>
                                    </div>
                                @endif
                                @if (isset($order->precheckout->discount_amount) && $order->precheckout->discount_amount > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted text-success">Saved:</span>
                                        <span
                                            class="fw-semibold text-success">-{{ $currency }}{{ number_format($order->precheckout->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                @if (isset($order->precheckout->coupon_amount) && $order->precheckout->coupon_amount > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted text-danger">Discount:</span>
                                        <span
                                            class="fw-semibold text-danger">-{{ $currency }}{{ number_format($order->precheckout->coupon_amount, 2) }}</span>
                                    </div>
                                @endif
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold fs-5">Grand Total:</span>
                                    <span
                                        class="fw-bold fs-5 text-primary">{{ $currency }}{{ number_format($order->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($order->status !== 'Completed')
                            <div class="action-buttons animate-up delay-3">
                                <h5><i class="fas fa-cog me-2"></i>Order Actions</h5>

                                @if ($order->payment->payment_method === 'COD')
                                    <div class="button-group">
                                        @if (!$order->payment->payment_collected)
                                            <form action="{{ route('order.collect-cash', $order->id) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-hand-holding-usd me-2"></i>Collect Cash
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ url('orders/complete/' . $order->id . '/' . $code) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check-circle me-2"></i>Complete Order
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        @if (!$order->payment->payment_collected)
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>COD Order:</strong> Please collect cash from customer before
                                                completing the order.
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <strong>Cash Collected:</strong> You can now complete this order.
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="button-group">
                                        <form action="{{ url('orders/complete/' . $order->id . '/' . $code) }}"
                                            method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check-circle me-2"></i>Complete Order
                                            </button>
                                        </form>
                                    </div>

                                    <div class="mt-3">
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-credit-card me-2"></i>
                                            <strong>Online Payment:</strong> Payment has been processed. You can
                                            complete the order.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="action-buttons animate-up delay-3">
                                <div class="alert alert-success mb-0 text-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Order Completed Successfully!</strong>
                                    @if ($order->completed_at)
                                        <br><small>Completed on:
                                            {{ \Carbon\Carbon::parse($order->completed_at)->format($date_format) }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.animate-up');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
            });

            setTimeout(() => {
                animatedElements.forEach(el => {
                    el.style.opacity = '1';
                });
            }, 100);
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const button = form.querySelector('button[type="submit"]');
                const actionText = button.textContent.trim();

                let icon = 'question';
                let confirmButtonColor = '#0ea1b1';
                let title = 'Confirm Action';
                let text = `Are you sure you want to ${actionText.toLowerCase()}?`;

                if (actionText.includes('Complete')) {
                    icon = 'success';
                    title = 'Complete Order';
                    text = 'This will mark the order as completed.';
                    confirmButtonColor = '#10B981';
                } else if (actionText.includes('Collect')) {
                    icon = 'warning';
                    title = 'Collect Cash';
                    text = 'Please confirm that you have collected the cash from the customer.';
                    confirmButtonColor = '#F59E0B';
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: `<i class="fas fa-check me-2"></i>Yes, proceed!`,
                    cancelButtonText: `<i class="fas fa-times me-2"></i>Cancel`,
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-lg mx-2 px-4 py-3',
                        cancelButton: 'btn btn-lg btn-outline mx-2 px-4 py-3',
                        popup: 'swal-custom-popup',
                        title: 'swal-custom-title',
                        htmlContainer: 'swal-custom-text'
                    },
                    backdrop: `
                        rgba(14, 161, 177, 0.15)
                        left top
                        no-repeat
                    `,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Processing...',
                            html: 'Please wait while we process your request.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'swal-custom-popup'
                            },
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
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
