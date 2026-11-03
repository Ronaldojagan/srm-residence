@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <style>
            .search-suggestions {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 4px 4px;
                max-height: 250px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .search-suggestions.active {
                display: block;
            }

            .suggestion-item {
                padding: 10px 15px;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
                transition: background 0.2s;
            }

            .suggestion-item:hover {
                background: #f8f9fa;
            }

            .suggestion-item:last-child {
                border-bottom: none;
            }

            .suggestion-loading {
                padding: 10px 15px;
                text-align: center;
                color: #666;
                font-size: 14px;
            }

            .input-group {
                position: relative;
            }

            .map-container {
                position: relative;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
            }

            .map-coordinates {
                padding: 8px 12px;
                background: #f8f9fa;
                border-top: 1px solid #ddd;
                font-size: 14px;
                font-family: monospace;
            }

            #map {
                height: 100%;
                width: 100%;
            }
        </style>
    @endpush

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page">
                {{ isset($edit) ? 'Edit Shop' : 'Create Shop' }}
            </h4>
        </div>
        <div class="col-md-6 mt-3 mb-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/shops') }}" class="btn add-btn text-white">
                <i class="mdi mdi-keyboard-backspace me-1"></i> Back
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="mdi mdi-check-circle-outline me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ isset($edit) ? url('/admin/shops/' . Crypt::encrypt($edit->id)) : url('/admin/shops') }}"
                method="post" class="form-horizontal" enctype="multipart/form-data">
                @csrf
                @if (isset($edit))
                    @method('PUT')
                @endif

                <div class="row">
                    <!-- Shop Basic Information -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Shop Name </label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            placeholder="Enter Shop Name" value="{{ old('name', $edit->name ?? '') }}">
                        @error('name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address </label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="5"
                            placeholder="Enter Shop Address" rows="2">{{ old('address', $edit->address ?? '') }}</textarea>
                        @error('address')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                        @if (isset($edit) && $edit->image)
                            <div class="mt-2">
                                <img src="{{ url('storage/shop/' . $edit->image) }}" alt="Shop Image" class="img-thumbnail"
                                    style="max-height: 100px;">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label text-dark">Status</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input type="radio" id="active" name="status" class="form-check-input"
                                        value="1" @if ((isset($edit) && $edit->status) || old('status', '1') == '1') checked @endif>
                                    <label class="form-check-label" for="active">Active</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input type="radio" id="inactive" name="status" class="form-check-input"
                                        value="0" @if ((isset($edit) && !$edit->status) || old('status') == '0') checked @endif>
                                    <label class="form-check-label" for="inactive">Inactive</label>
                                </div>
                            </div>
                        </div>
                        @error('status')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Location on Map</label>

                        <div class="input-group mb-3">
                            <input type="text" id="location-search" class="form-control"
                                placeholder="Search for a location..." onkeydown="return handleEnter(event)">
                            <button class="btn btn-primary me-2" type="button" id="search-btn">
                                <i class="mdi mdi-magnify"></i> Search
                            </button>
                            <button class="btn add-btn text-white" type="button" id="current-location-btn">
                                <i class="mdi mdi-crosshairs-gps"></i> Current Location
                            </button>
                        </div>

                        <div class="map-container" style="height: 400px;">
                            <div id="map" style="height: 100%; width: 100%;"></div>
                            <div class="map-coordinates mt-2">
                                Latitude: <span id="lat-display">{{ old('latitude', $edit->latitude ?? '') }}</span>,
                                Longitude: <span id="lng-display">{{ old('longitude', $edit->longitude ?? '') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" id="latitude"
                            class="form-control @error('latitude') is-invalid @enderror"
                            placeholder="Latitude will be set from map"
                            value="{{ old('latitude', $edit->latitude ?? '') }}" readonly>
                        @error('latitude')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" id="longitude"
                            class="form-control @error('longitude') is-invalid @enderror"
                            placeholder="Longitude will be set from map"
                            value="{{ old('longitude', $edit->longitude ?? '') }}" readonly>
                        @error('longitude')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Owner Name </label>
                        <input type="text" name="owner_name"
                            class="form-control @error('owner_name') is-invalid @enderror" placeholder="Enter Owner Name"
                            value="{{ old('owner_name', $edit->owner_name ?? '') }}">
                        @error('owner_name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Owner Phone </label>
                        <input type="text" name="owner_phone"
                            class="form-control @error('owner_phone') is-invalid @enderror"
                            placeholder="Enter Owner Phone" @if(isset($edit->owner_phone)) readonly @endif value="{{ old('owner_phone', $edit->owner_phone ?? '') }}">
                        @error('owner_phone')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email </label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            placeholder="Enter Email" value="{{ old('email', $edit->email ?? '') }}"
                            @if (isset($edit)) @endif>
                        @error('email')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Commission Percentage (%)</label>
                        <input type="number" step="0.01" name="commission_percentage"
                            class="form-control @error('commission_percentage') is-invalid @enderror"
                            placeholder="Enter Commission Percentage"
                            value="{{ old('commission_percentage', $edit->commission_percentage ?? '') }}">
                        @error('commission_percentage')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <hr>

                    <!-- Bank Information -->
                    <div class="col-12 mt-4">
                        <h5 class="mb-3"><i class="mdi mdi-bank me-2"></i> Bank Information</h5>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name"
                            class="form-control @error('bank_name') is-invalid @enderror" placeholder="Enter Bank Name"
                            value="{{ old('bank_name', $bank->bank_name ?? '') }}">
                        @error('bank_name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number"
                            class="form-control @error('account_number') is-invalid @enderror"
                            placeholder="Enter Account Number"
                            value="{{ old('account_number', $bank->account_number ?? '') }}">
                        @error('account_number')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc_code"
                            class="form-control @error('ifsc_code') is-invalid @enderror" placeholder="Enter IFSC Code"
                            value="{{ old('ifsc_code', $bank->ifsc_code ?? '') }}">
                        @error('ifsc_code')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Holder Name</label>
                        <input type="text" name="holder_name"
                            class="form-control @error('holder_name') is-invalid @enderror"
                            placeholder="Enter Holder Name" value="{{ old('holder_name', $bank->holder_name ?? '') }}">
                        @error('holder_name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Branch</label>
                        <input type="text" name="branch" class="form-control @error('branch') is-invalid @enderror"
                            placeholder="Enter Branch" value="{{ old('branch', $bank->branch ?? '') }}">
                        @error('branch')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit"
                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save">
                            {{ isset($edit) ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('footer_content')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places"></script>
        <script>
            let searchTimeout;
            let currentLat, currentLng;
            let map, marker, autocomplete;

            function handleEnter(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const suggestionsDiv = document.getElementById('search-suggestions');
                    if (suggestionsDiv && suggestionsDiv.classList.contains('active')) {
                        const firstItem = suggestionsDiv.querySelector('.suggestion-item');
                        if (firstItem) {
                            firstItem.click();
                        }
                    } else {
                        document.getElementById('search-btn').click();
                    }
                    return false;
                }
                return true;
            }

            function initMap() {
                var defaultLat = {{ old('latitude', $edit->latitude ?? '26.8467') }};
                var defaultLng = {{ old('longitude', $edit->longitude ?? '80.9462') }};

                currentLat = defaultLat;
                currentLng = defaultLng;

                map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: defaultLat,
                        lng: defaultLng
                    },
                    zoom: 13,
                    mapTypeControl: true,
                    streetViewControl: true,
                    fullscreenControl: true
                });

                marker = new google.maps.Marker({
                    position: {
                        lat: defaultLat,
                        lng: defaultLng
                    },
                    map: map,
                    draggable: true,
                    title: 'Shop Location'
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: 'Shop Location'
                });

                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });

                marker.addListener('dragend', function(event) {
                    updateLocation(event.latLng.lat(), event.latLng.lng(), 'Dragged Location');
                });

                map.addListener('click', function(event) {
                    updateLocation(event.latLng.lat(), event.latLng.lng(), 'Selected Location');
                });

                const searchInput = document.getElementById('location-search');
                autocomplete = new google.maps.places.Autocomplete(searchInput);
                autocomplete.bindTo('bounds', map);

                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();

                    if (!place.geometry || !place.geometry.location) {
                        alert("No location found for: '" + place.name + "'");
                        return;
                    }

                    updateLocation(
                        place.geometry.location.lat(),
                        place.geometry.location.lng(),
                        place.formatted_address || place.name
                    );
                });

                const inputGroup = searchInput.closest('.input-group');
                let suggestionsDiv = document.getElementById('search-suggestions');
                if (!suggestionsDiv) {
                    suggestionsDiv = document.createElement('div');
                    suggestionsDiv.id = 'search-suggestions';
                    suggestionsDiv.className = 'search-suggestions';
                    inputGroup.appendChild(suggestionsDiv);
                }

                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    clearTimeout(searchTimeout);

                    if (query.length < 3) {
                        suggestionsDiv.classList.remove('active');
                        return;
                    }

                    if (!autocomplete) {
                        searchTimeout = setTimeout(() => {
                            fetchSuggestions(query);
                        }, 300);
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.input-group')) {
                        suggestionsDiv.classList.remove('active');
                    }
                });

                function fetchSuggestions(query) {
                    suggestionsDiv.innerHTML = '<div class="suggestion-loading">Searching...</div>';
                    suggestionsDiv.classList.add('active');

                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        address: query
                    }, function(results, status) {
                        if (status === 'OK' && results.length > 0) {
                            suggestionsDiv.innerHTML = '';
                            results.slice(0, 5).forEach(result => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.textContent = result.formatted_address;
                                div.addEventListener('click', function() {
                                    updateLocation(
                                        result.geometry.location.lat(),
                                        result.geometry.location.lng(),
                                        result.formatted_address
                                    );
                                    searchInput.value = result.formatted_address;
                                    suggestionsDiv.classList.remove('active');
                                });
                                suggestionsDiv.appendChild(div);
                            });
                        } else {
                            suggestionsDiv.innerHTML = '<div class="suggestion-loading">No results found</div>';
                            setTimeout(() => {
                                suggestionsDiv.classList.remove('active');
                            }, 2000);
                        }
                    });
                }

                document.getElementById('search-btn').addEventListener('click', function() {
                    var query = document.getElementById('location-search').value.trim();
                    if (query) {
                        searchLocation(query);
                        if (suggestionsDiv) {
                            suggestionsDiv.classList.remove('active');
                        }
                    }
                });

                document.getElementById('current-location-btn').addEventListener('click', function() {
                    const btn = this;
                    const originalHtml = btn.innerHTML;

                    if (navigator.geolocation) {
                        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Getting location...';
                        btn.disabled = true;

                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                updateLocation(position.coords.latitude, position.coords.longitude,
                                    'Your Current Location');
                                btn.innerHTML = originalHtml;
                                btn.disabled = false;
                            },
                            function(error) {
                                alert("Could not get your location: " + error.message);
                                btn.innerHTML = originalHtml;
                                btn.disabled = false;
                            }
                        );
                    } else {
                        alert("Geolocation is not supported by your browser");
                    }
                });

                function searchLocation(query) {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        address: query
                    }, function(results, status) {
                        if (status === 'OK' && results.length > 0) {
                            const result = results[0];
                            updateLocation(
                                result.geometry.location.lat(),
                                result.geometry.location.lng(),
                                result.formatted_address
                            );
                        } else {
                            alert("Location not found");
                        }
                    });
                }

                function updateLocation(lat, lng, displayName = null) {
                    currentLat = lat;
                    currentLng = lng;

                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    document.getElementById('lat-display').textContent = lat.toFixed(6);
                    document.getElementById('lng-display').textContent = lng.toFixed(6);

                    const position = {
                        lat: lat,
                        lng: lng
                    };
                    marker.setPosition(position);
                    map.setCenter(position);
                    map.setZoom(15);

                    if (displayName) {
                        infoWindow.setContent(displayName);
                        document.getElementById('location-search').value = displayName;
                    }
                }

                if (defaultLat && defaultLng) {
                    updateLocation(defaultLat, defaultLng, "Shop Location");
                }

                @if (isset($edit) && $edit->latitude && $edit->longitude)
                    updateLocation({{ $edit->latitude }}, {{ $edit->longitude }}, "Shop Location");
                @endif
            }

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof google !== 'undefined' && google.maps) {
                    initMap();
                } else {
                    console.error('Google Maps API not loaded. Please add the API script in your HTML head section.');
                }
            });
        </script>
    @endpush
@endsection
