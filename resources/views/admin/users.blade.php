@extends('layouts.dashboard')

@section('title', 'User Management')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="{{ route('admin.smart-dashboard') }}"><i class="fa-solid fa-city"></i> Smart Dashboard</a>
    <a href="{{ route('admin.bins') }}"><i class="fa-solid fa-trash-can"></i> Smart Bins</a>
    <a href="{{ route('admin.requests') }}"><i class="fa-solid fa-list"></i> Requests</a>
    <a href="{{ route('admin.users') }}" class="active"><i class="fa-solid fa-users-gear"></i> User Management</a>
@endsection

@section('content')
{{-- Role Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary-custom">
            <h3>{{ $roleCounts['total'] }}</h3>
            <p><i class="fa-solid fa-users"></i> Total Users</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background-color: #e74c3c; color: white; padding: 20px; border-radius: 10px;">
            <h3>{{ $roleCounts['admin'] }}</h3>
            <p><i class="fa-solid fa-user-shield"></i> Admins</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background-color: #8e44ad; color: white; padding: 20px; border-radius: 10px;">
            <h3>{{ $roleCounts['driver'] }}</h3>
            <p><i class="fa-solid fa-truck"></i> Drivers</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success-custom">
            <h3>{{ $roleCounts['citizen'] }}</h3>
            <p><i class="fa-solid fa-user"></i> Citizens</p>
        </div>
    </div>
</div>

{{-- Search & Filter --}}
<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('admin.users') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-bold"><i class="fa-solid fa-magnifying-glass"></i> Search</label>
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold"><i class="fa-solid fa-filter"></i> Filter by Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="driver" {{ request('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                <option value="citizen" {{ request('role') == 'citizen' ? 'selected' : '' }}>Citizen</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i> Search</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary w-100"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        </div>
    </form>
</div>

{{-- Error message for self-demotion --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Users Table --}}
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fa-solid fa-users-gear"></i> Manage Users</h4>
        <span class="text-muted small">Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Registered</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted">#{{ $user->id }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background-color: {{ $user->role === 'admin' ? '#e74c3c' : ($user->role === 'driver' ? '#8e44ad' : '#2ecc71') }}; color: white; font-weight: bold; font-size: 0.85rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">You</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @php
                            $roleColors = ['admin' => 'danger', 'driver' => 'purple', 'citizen' => 'success'];
                            $roleIcons = ['admin' => 'fa-user-shield', 'driver' => 'fa-truck', 'citizen' => 'fa-user'];
                        @endphp
                        <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }}" style="{{ $user->role === 'driver' ? 'background-color: #8e44ad !important;' : '' }}">
                            <i class="fa-solid {{ $roleIcons[$user->role] ?? 'fa-user' }}"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="d-flex align-items-center" onsubmit="return confirm('Change {{ $user->name }}\'s role to ' + this.role.value + '?')">
                            @csrf
                            @method('PATCH')
                            <select name="role" class="form-select form-select-sm me-2" style="width: 130px;" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="citizen" {{ $user->role === 'citizen' ? 'selected' : '' }}>Citizen</option>
                                <option value="driver" {{ $user->role === 'driver' ? 'selected' : '' }}>Driver</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <i class="fa-solid fa-arrows-rotate"></i> Update
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-user-slash fa-2x mb-2 d-block opacity-25"></i>
                        No users found matching your criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $users->links() }}
    </div>
    @endif
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(26, 188, 156, 0.08);
    }
</style>
@endsection
