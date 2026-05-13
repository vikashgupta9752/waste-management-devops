@extends('layouts.dashboard')

@section('title', 'Smart Bin Management')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="{{ route('admin.smart-dashboard') }}"><i class="fa-solid fa-city"></i> Smart Dashboard</a>
    <a href="{{ route('admin.bins') }}" class="active"><i class="fa-solid fa-trash-can"></i> Smart Bins</a>
    <a href="{{ route('admin.requests') }}"><i class="fa-solid fa-list"></i> Requests</a>
    <a href="{{ route('admin.users') }}"><i class="fa-solid fa-users-gear"></i> User Management</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card p-0 overflow-hidden" style="height: 400px;">
            <div id="binMap" style="height: 100%; width: 100%;"></div>
        </div>
    </div>
</div>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">All Registered Smart Bins</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBinModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Bin
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Location Name</th>
                    <th>Coordinates</th>
                    <th>Fill Level</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bins as $bin)
                <tr>
                    <td>#{{ $bin->id }}</td>
                    <td>{{ $bin->location_name }}</td>
                    <td><small class="text-muted">{{ $bin->latitude }}, {{ $bin->longitude }}</small></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                <div class="progress-bar bg-{{ $bin->fill_level >= 90 ? 'danger' : ($bin->fill_level >= 70 ? 'warning' : 'success') }}" 
                                     role="progressbar" style="width: {{ $bin->fill_level }}%"></div>
                            </div>
                            <span>{{ $bin->fill_level }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $bin->status == 'full' ? 'danger' : 'success' }}">
                            {{ ucfirst($bin->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editBinModal{{ $bin->id }}">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <form action="{{ route('admin.bins.destroy', $bin) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editBinModal{{ $bin->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.bins.update', $bin) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Smart Bin #{{ $bin->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Location Name</label>
                                        <input type="text" name="location_name" class="form-control" value="{{ $bin->location_name }}" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Latitude</label>
                                            <input type="number" step="any" name="latitude" class="form-control" value="{{ $bin->latitude }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Longitude</label>
                                            <input type="number" step="any" name="longitude" class="form-control" value="{{ $bin->longitude }}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Fill Level (%)</label>
                                        <input type="number" name="fill_level" class="form-control" min="0" max="100" value="{{ $bin->fill_level }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Bin</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addBinModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.bins.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Smart Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="location_name" class="form-control" placeholder="e.g. Central Park West" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control" placeholder="e.g. 28.6139" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control" placeholder="e.g. 77.2090" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Fill Level (%)</label>
                        <input type="number" name="fill_level" class="form-control" min="0" max="100" value="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Bin</button>
                </div>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // India Bounds
        const indiaBounds = L.latLngBounds([6.4626999, 68.1097], [35.513327, 97.395358]);

        const map = L.map('binMap', {
            center: [31.2559, 75.7051],
            zoom: 13,
            minZoom: 5,
            maxBounds: indiaBounds,
            maxBoundsViscosity: 1.0
        });
        
        // Use CartoDB Voyager for English labels
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        const bins = @json($bins);
        const markers = [];

        bins.forEach(bin => {
            const color = bin.fill_level >= 90 ? 'red' : (bin.fill_level >= 70 ? 'orange' : 'green');
            const marker = L.circleMarker([bin.latitude, bin.longitude], {
                radius: 10,
                fillColor: color,
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map);

            marker.bindPopup(`
                <strong>${bin.location_name}</strong><br>
                Fill Level: ${bin.fill_level}%<br>
                Status: ${bin.status}
            `);
            markers.push(marker);
        });

        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // Add Click Handler to map
        let tempMarker;
        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            // Find active modal
            const activeModal = document.querySelector('.modal.show');
            if (activeModal) {
                const latInput = activeModal.querySelector('input[name="latitude"]');
                const lngInput = activeModal.querySelector('input[name="longitude"]');
                
                if (latInput && lngInput) {
                    latInput.value = lat;
                    lngInput.value = lng;
                    
                    // Show a temporary marker where they clicked
                    if (tempMarker) map.removeLayer(tempMarker);
                    tempMarker = L.marker([lat, lng]).addTo(map)
                        .bindPopup("Selected Location").openPopup();
                    
                    // Flash the inputs to show they were updated
                    latInput.classList.add('is-valid');
                    lngInput.classList.add('is-valid');
                    setTimeout(() => {
                        latInput.classList.remove('is-valid');
                        lngInput.classList.remove('is-valid');
                    }, 1000);
                }
            }
        });
    });
</script>
@endsection

<style>
    .progress { background-color: #e9ecef; border-radius: 5px; }
    .badge { font-weight: 500; }
</style>
@endsection
