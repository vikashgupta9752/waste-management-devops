@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

    <a href="{{ route('admin.dashboard') }}" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="{{ route('admin.smart-dashboard') }}"><i class="fa-solid fa-city"></i> Smart Dashboard</a>
    <a href="{{ route('admin.users') }}"><i class="fa-solid fa-users-gear"></i> User Management</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary-custom">
            <h3>{{ $totalRequests }}</h3>
            <p>Total Requests</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning-custom">
            <h3>{{ $pendingRequests }}</h3>
            <p>Pending Pickups</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success-custom">
            <h3>{{ $completedRequests }}</h3>
            <p>Completed/Disposed</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info" style="background-color: #17a2b8; color: white; padding: 20px; border-radius: 10px;">
            <h3>{{ $drivers->count() }}</h3>
            <p>Active Drivers</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <h5 class="mb-3"><i class="fa-solid fa-chart-pie text-primary"></i> Category Distribution</h5>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fa-solid fa-map-location-dot text-danger"></i> Live Operational Map</h5>
                <span class="badge bg-danger blink">Live Tracking</span>
            </div>
            <div id="liveMap" style="height: 350px; border-radius: 8px;"></div>
        </div>
    </div>
</div>

<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">All Waste Requests</h4>
        <a href="{{ route('admin.export-reports') }}" class="btn btn-outline-success btn-sm">
            <i class="fa-solid fa-file-csv"></i> Export Reports (CSV)
        </a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Req ID</th>
                <th>Citizen</th>
                <th>Category</th>
                <th>Date/Time</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $req)
            <tr>
                <td>#{{ $req->id }}</td>
                <td>{{ $req->user->name }}</td>
                <td>{{ $req->wasteCategory->name }}</td>
                <td>
                    <div class="fw-bold">{{ $req->schedule ? $req->schedule->pickup_date->format('M d, Y') : $req->created_at->format('M d, Y') }}</div>
                    <div class="small text-muted">{{ $req->schedule->time_slot ?? 'N/A' }}</div>
                </td>
                <td>
                    <span class="badge bg-{{ $req->status == 'pending' ? 'warning' : ($req->status == 'assigned' ? 'info' : ($req->status == 'on_the_way' ? 'primary' : 'success')) }}">
                        {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                    </span>
                </td>
                <td>
                    {{ $req->assignment->driver->name ?? 'Unassigned' }}
                </td>
                <td>
                    @if($req->status == 'pending')
                    <form action="{{ route('admin.assign') }}" method="POST" class="d-flex">
                        @csrf
                        <input type="hidden" name="waste_request_id" value="{{ $req->id }}">
                        <select name="driver_id" class="form-select form-select-sm me-2" required>
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                    </form>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
.blink { animation: blinker 1.5s linear infinite; }
@keyframes blinker { 50% { opacity: 0; } }
</style>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Category Distribution Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryStats->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($categoryStats->pluck('waste_requests_count')) !!},
                backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Live Operational Map
    const map = L.map('liveMap').setView([23.8103, 90.4125], 13);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012'
    }).addTo(map);

    let driverMarkers = {};

    function updateDriverLocations() {
        @foreach($driverLocations as $loc)
            if (driverMarkers["{{ $loc->driver_id }}"]) {
                driverMarkers["{{ $loc->driver_id }}"].setLatLng([{{ $loc->latitude }}, {{ $loc->longitude }}]);
            } else {
                driverMarkers["{{ $loc->driver_id }}"] = L.marker([{{ $loc->latitude }}, {{ $loc->longitude }}], {
                    icon: L.icon({
                        iconUrl: 'https://cdn-icons-png.flaticon.com/512/2830/2830305.png',
                        iconSize: [30, 30]
                    })
                }).addTo(map).bindPopup("Driver: {{ $loc->driver->name }}");
            }
        @endforeach
    }

    // Add Request Heatmap points
    @foreach($requests as $req)
        @if($req->latitude && $req->longitude)
            L.circle([{{ $req->latitude }}, {{ $req->longitude }}], {
                color: '{{ $req->status == "pending" ? "red" : "green" }}',
                fillColor: '{{ $req->status == "pending" ? "#f03" : "#0f3" }}',
                fillOpacity: 0.5,
                radius: 50
            }).addTo(map).bindPopup("Request #{{ $req->id }} ({{ $req->status }})");
        @endif
    @endforeach

    updateDriverLocations();
    setInterval(() => location.reload(), 30000); // Simple auto-refresh for live feel
</script>
@endsection
