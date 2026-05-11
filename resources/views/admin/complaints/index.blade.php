@extends('layouts.dashboard')

@section('title', 'Manage Complaints')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="{{ route('admin.smart-dashboard') }}"><i class="fa-solid fa-city"></i> Smart Dashboard</a>
    <a href="{{ route('admin.users') }}"><i class="fa-solid fa-users-gear"></i> User Management</a>
    <a href="{{ route('admin.complaints') }}" class="active"><i class="fa-solid fa-circle-exclamation"></i> Complaints</a>
@endsection

@section('content')
<div class="card p-4 shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Citizen</th>
                    <th>Issue Details</th>
                    <th>Evidence</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $complaint)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $complaint->user->name }}</div>
                        <div class="small text-muted">{{ $complaint->created_at->diffForHumans() }}</div>
                    </td>
                    <td style="max-width: 300px;">
                        <div class="fw-bold">{{ $complaint->subject }}</div>
                        <div class="small text-muted text-truncate">{{ $complaint->description }}</div>
                        @if($complaint->latitude)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $complaint->latitude }},{{ $complaint->longitude }}" target="_blank" class="small text-info text-decoration-none">
                                <i class="fa-solid fa-map-pin"></i> View Location
                            </a>
                        @endif
                    </td>
                    <td>
                        @if($complaint->image_path)
                            <a href="{{ asset('storage/' . $complaint->image_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $complaint->image_path) }}" width="60" class="rounded shadow-sm">
                            </a>
                        @else
                            <span class="text-muted small">No Image</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $complaint->status == 'pending' ? 'warning' : ($complaint->status == 'resolved' ? 'success' : 'danger') }}">
                            {{ ucfirst($complaint->status) }}
                        </span>
                    </td>
                    <td>
                        @if($complaint->status == 'pending')
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $complaint->id }}">
                            Resolve
                        </button>

                        <!-- Resolve Modal -->
                        <div class="modal fade" id="resolveModal{{ $complaint->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.complaints.update', $complaint) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Resolve Complaint #{{ $complaint->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Action</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="resolved">Mark as Resolved</option>
                                                    <option value="rejected">Reject Complaint</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Admin Comment</label>
                                                <textarea name="admin_comment" class="form-control" rows="3" placeholder="Explain the resolution..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @else
                            <span class="text-muted small">Completed</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
