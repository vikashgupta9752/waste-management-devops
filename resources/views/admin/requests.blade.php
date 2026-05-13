@extends('layouts.dashboard')

@section('title', 'Manage Waste Requests')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="{{ route('admin.smart-dashboard') }}"><i class="fa-solid fa-city"></i> Smart Dashboard</a>
    <a href="{{ route('admin.requests') }}" class="active"><i class="fa-solid fa-list"></i> Requests</a>
    <a href="{{ route('admin.users') }}"><i class="fa-solid fa-users-gear"></i> User Management</a>
@endsection

@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">All Waste Collection Requests</h4>
        <a href="{{ route('admin.export-reports') }}" class="btn btn-outline-success">
            <i class="fa-solid fa-file-csv me-1"></i> Export to CSV
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Citizen</th>
                    <th>Category</th>
                    <th>Address</th>
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
                    <td>
                        <div class="fw-bold">{{ $req->user->name }}</div>
                        <div class="small text-muted">{{ $req->user->email }}</div>
                    </td>
                    <td>{{ $req->wasteCategory->name }}</td>
                    <td>{{ $req->address }}</td>
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
                            <span class="text-muted small">Assigned</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $requests->links() }}
    </div>
</div>

<style>
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(26, 188, 156, 0.05);
    }
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }
</style>
@endsection
