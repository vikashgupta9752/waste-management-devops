@extends('layouts.dashboard')

@section('title', 'Citizen Dashboard')

@section('sidebar')
    <a href="{{ route('citizen.dashboard') }}" class="active"><i class="fa-solid fa-house"></i> Home</a>
    <a href="{{ route('citizen.bins') }}"><i class="fa-solid fa-trash-can"></i> Smart Bins</a>
@endsection

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { height: 300px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
</style>
@endsection

@section('content')
<!-- Smart City Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary-custom shadow-sm h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h3 class="mb-0">{{ Auth::user()->points }}</h3>
                    <p class="mb-0">Green Points</p>
                </div>
                <i class="fa-solid fa-star text-warning fa-2x opacity-50"></i>
            </div>
            <div class="mt-2 small text-white-50">Rank: {{ Auth::user()->rank }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success-custom shadow-sm h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h3 class="mb-0">{{ Auth::user()->total_co2_saved }} kg</h3>
                    <p class="mb-0">CO2 Reduced</p>
                </div>
                <i class="fa-solid fa-cloud-arrow-down fa-2x opacity-50"></i>
            </div>
            <div class="mt-2 small text-white-50">Environmental Impact</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info shadow-sm h-100 text-white">
            <div class="d-flex justify-content-between">
                <div>
                    <h3 class="mb-0">{{ Auth::user()->badges()->count() }}</h3>
                    <p class="mb-0">Badges Earned</p>
                </div>
                <i class="fa-solid fa-award fa-2x opacity-50"></i>
            </div>
            <div class="mt-2 small text-white-50">Level Up to Unlock More!</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning shadow-sm h-100 text-white">
            <div class="d-flex justify-content-between">
                <div>
                    <h3 class="mb-0">Top 10%</h3>
                    <p class="mb-0">Eco-Leaderboard</p>
                </div>
                <i class="fa-solid fa-ranking-star fa-2x opacity-50"></i>
            </div>
            <div class="mt-2 small text-white-50">Last updated: Just now</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-5">
        <div class="card p-4">
            <h4>Request Waste Pickup</h4>
            <form action="{{ route('citizen.request.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label>Waste Category</label>
                    <select name="waste_category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="d-block">Pickup Location</label>
                    <button type="button" id="getLocationBtn" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fa-solid fa-location-crosshairs"></i> Use My Current Location
                    </button>
                    <div id="locationStatus" class="small text-muted mb-2">Location not set.</div>
                    
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    
                    <!-- Keeping the map but making it read-only for verification -->
                    <div id="map" style="height: 200px; display: none;"></div>
                </div>

                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label>Pickup Date</label>
                        <input type="date" name="pickup_date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label>Time Slot</label>
                        <select name="time_slot" class="form-select" required>
                            <option value="08:00-10:00">08:00 AM - 10:00 AM</option>
                            <option value="10:00-12:00">10:00 AM - 12:00 PM</option>
                            <option value="12:00-14:00">12:00 PM - 02:00 PM</option>
                            <option value="14:00-16:00">02:00 PM - 04:00 PM</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Pickup Address (Detail)</label>
                    <textarea name="address" id="address" class="form-control" rows="2" required placeholder="Enter street, house no, etc."></textarea>
                </div>
                <div class="mb-3">
                    <label>Upload Image Proof (Optional)</label>
                    <input type="file" name="image_proof" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success w-100">Schedule Pickup</button>
            </form>
        </div>
        
        <div class="card p-4 mt-4 border-0 shadow-sm overflow-hidden relative">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">QR Waste Tracking</h4>
                <i class="fa-solid fa-qrcode fa-2x opacity-25"></i>
            </div>
            <p class="text-muted small">Drivers will scan your unique QR code during collection to verify the pickup.</p>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card p-4 h-100">
            <h4>My Pickup Requests</h4>
            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Tracking</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $req->schedule ? $req->schedule->pickup_date->format('M d, Y') : $req->created_at->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $req->schedule->time_slot ?? 'Not set' }}</div>
                            </td>
                            <td>{{ $req->wasteCategory->name }}</td>
                            <td>
                                <span class="badge bg-{{ $req->status == 'pending' ? 'warning' : ($req->status == 'assigned' ? 'info' : ($req->status == 'on_the_way' ? 'primary' : 'success')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($req->status == 'on_the_way' && $req->assignment)
                                    <a href="{{ route('citizen.track', $req->assignment->driver_id) }}" class="btn btn-sm btn-primary mb-1 w-100">
                                        <i class="fa-solid fa-location-dot"></i> Track
                                    </a>
                                @endif
                                
                                @if($req->qrCode)
                                    <button class="btn btn-sm btn-outline-dark w-100" onclick="showQR('{{ $req->qrCode->code }}', '{{ $req->id }}')">
                                        <i class="fa-solid fa-qrcode"></i> View QR
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- QR Modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <h5>Waste Pickup QR</h5>
            <div id="qrcode" class="d-flex justify-content-center my-3"></div>
            <p id="qrCodeText" class="fw-bold text-primary"></p>
            <p class="small text-muted mb-0">Show this to the driver during collection.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let map, marker;

    function showQR(code, id) {
        const qrContainer = document.getElementById("qrcode");
        qrContainer.innerHTML = "";
        new QRCode(qrContainer, {
            text: code,
            width: 150,
            height: 150
        });
        document.getElementById("qrCodeText").innerText = code;
        new bootstrap.Modal(document.getElementById('qrModal')).show();
    }
    
    const getLocationBtn = document.getElementById('getLocationBtn');
    const locationStatus = document.getElementById('locationStatus');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const mapDiv = document.getElementById('map');

    getLocationBtn.addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }

        locationStatus.innerText = "Fetching location...";
        getLocationBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                latInput.value = lat;
                lngInput.value = lng;
                locationStatus.innerHTML = `<span class="text-success"><i class="fa-solid fa-circle-check"></i> Location Set: ${lat.toFixed(4)}, ${lng.toFixed(4)}</span>`;
                getLocationBtn.disabled = false;

                // Show map for verification
                mapDiv.style.display = 'block';
                if (!map) {
                    const indiaBounds = L.latLngBounds([6.4626999, 68.1097], [35.513327, 97.395358]);
                    map = L.map('map', {
                        center: [lat, lng],
                        zoom: 16,
                        minZoom: 5,
                        maxBounds: indiaBounds,
                        maxBoundsViscosity: 1.0
                    });
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '© OpenStreetMap contributors © CARTO',
                        subdomains: 'abcd',
                        maxZoom: 20
                    }).addTo(map);

                    // Add click handler to allow manual adjustments
                    map.on('click', function(e) {
                        const newLat = e.latlng.lat;
                        const newLng = e.latlng.lng;
                        latInput.value = newLat;
                        lngInput.value = newLng;
                        marker.setLatLng([newLat, newLng]);
                        locationStatus.innerHTML = `<span class="text-success"><i class="fa-solid fa-circle-check"></i> Location Set: ${newLat.toFixed(4)}, ${newLng.toFixed(4)}</span>`;
                    });
                } else {
                    map.setView([lat, lng], 16);
                }

                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(map);
                }
            },
            (error) => {
                locationStatus.innerHTML = `<span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Error: ${error.message}</span>`;
                getLocationBtn.disabled = false;
                alert('Unable to retrieve your location. Please ensure location permissions are enabled.');
            },
            { enableHighAccuracy: true }
        );
    });
</script>
@endsection
