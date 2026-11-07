@php
    $resName = strtoupper($settings['residence_name'] ?? 'SRM RESIDENCE');
    $w = $width ?? 110;
    $h = $height ?? 110;
    $rot = $rotation ?? '-6deg';
    $stampColor = $color ?? '#0a5858';
    $customStamp = $settings['residence_stamp'] ?? null;
    $stampUid = 'stamp_' . mt_rand(10000, 99999);
@endphp

@if(!empty($customStamp) && file_exists(public_path('storage/' . $customStamp)))
    <img src="{{ asset('storage/' . $customStamp) }}" 
         alt="{{ $resName }} Stamp" 
         style="width: {{ $w }}px; height: {{ $h }}px; object-fit: contain; transform: rotate({{ $rot }}); display: inline-block;" 
         class="official-stamp-img">
@else
    <svg width="{{ $w }}" height="{{ $h }}" viewBox="0 0 200 200" 
         style="transform: rotate({{ $rot }}); display: inline-block; filter: drop-shadow(0 2px 4px rgba(10,88,88,0.12));" 
         class="official-stamp-svg"
         xmlns="http://www.w3.org/2000/svg">
        
        <!-- Outer Concentric Rings with Rubber Stamp Styling -->
        <circle cx="100" cy="100" r="95" fill="none" stroke="{{ $stampColor }}" stroke-width="3.5" stroke-dasharray="6, 2.5" />
        <circle cx="100" cy="100" r="88" fill="none" stroke="{{ $stampColor }}" stroke-width="2" />
        <circle cx="100" cy="100" r="63" fill="none" stroke="{{ $stampColor }}" stroke-width="1.6" />
        <circle cx="100" cy="100" r="60" fill="none" stroke="{{ $stampColor }}" stroke-width="1" stroke-dasharray="2.5, 2" />

        <defs>
            <path id="top_{{ $stampUid }}" d="M 26,100 A 74,74 0 1,1 174,100" fill="none" />
            <path id="bottom_{{ $stampUid }}" d="M 26,100 A 74,74 0 0,0 174,100" fill="none" />
        </defs>

        <!-- Top Arc: Residence Name -->
        <text fill="{{ $stampColor }}" font-size="13" font-weight="900" font-family="'Plus Jakarta Sans', Arial, sans-serif" letter-spacing="2.5">
            <textPath href="#top_{{ $stampUid }}" startOffset="50%" text-anchor="middle">
                ★ {{ $resName }} ★
            </textPath>
        </text>

        <!-- Bottom Arc: Official Seal -->
        <text fill="{{ $stampColor }}" font-size="11" font-weight="800" font-family="'Plus Jakarta Sans', Arial, sans-serif" letter-spacing="2">
            <textPath href="#bottom_{{ $stampUid }}" startOffset="50%" text-anchor="middle">
                • OFFICIAL SEAL •
            </textPath>
        </text>

        <!-- Center Badge -->
        <g transform="translate(100, 100)" text-anchor="middle" fill="{{ $stampColor }}" font-family="'Plus Jakarta Sans', Arial, sans-serif">
            <text y="-26" font-size="9" letter-spacing="2.5">★ ★ ★</text>
            <rect x="-56" y="-18" width="112" height="20" rx="3" fill="none" stroke="{{ $stampColor }}" stroke-width="1.8" />
            <text y="-4" font-size="10.5" font-weight="900" letter-spacing="1.5">AUTHORIZED</text>
            <text y="14" font-size="9.5" font-weight="800" letter-spacing="1.8">SIGNATORY</text>
            <text y="28" font-size="7.5" font-weight="700" letter-spacing="1">POTHERI • CHENNAI</text>
        </g>
    </svg>
@endif
