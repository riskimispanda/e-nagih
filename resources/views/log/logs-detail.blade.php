@extends('layouts.contentNavbarLayout')

@section('title', 'Detail Aktivitas')
<style>
    .card {
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        background: #fff;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 28px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        background-color: rgba(245, 247, 251, 0.5);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
    }
    
    .card-title {
        color: #2c3e50;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    
    .user-info-card {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        border: 1px solid #e7eaf3 !important;
        transition: all 0.3s ease;
    }
    
    .user-info-card:hover {
        border-color: #5469d4 !important;
    }
    
    .avatar img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .text-heading {
        color: #1a3b89;
    }
    
    .detail-section {
        padding: 1.25rem;
        margin-bottom: 1rem;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    }
    
    .detail-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .detail-value {
        color: #6c757d;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    hr {
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        margin: 1.5rem 0;
    }
    .detail-section {
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        border-radius: 0.75rem;
        background-color: #ffffff;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    
    .detail-section:hover {
        border-color: #5469d4;
        box-shadow: 0 4px 15px rgba(84,105,212,0.1);
    }
    
    .detail-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .detail-label::before {
        content: '';
        width: 4px;
        height: 4px;
        background-color: #5469d4;
        border-radius: 50%;
    }
    
    .detail-value {
        color: #4b5563;
        font-size: 0.95rem;
        line-height: 1.6;
        background-color: #f8fafc;
        padding: 1rem;
        border-radius: 0.5rem;
    }
    
    .detail-value strong {
        color: #374151;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    
    .detail-value span {
        color: #6b7280;
    }
    
    .property-item {
        padding: 0.75rem;
        border-bottom: 1px dashed rgba(0,0,0,0.06);
        transition: background-color 0.2s ease;
    }
    
    .property-item:last-child {
        border-bottom: none;
    }
    
    .property-item:hover {
        background-color: #f1f5f9;
    }
</style>

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-5">
            <div class="card-header">
                <div>
                    <h4 class="card-title">Log Aktivitas</h4>
                    <small class="text-muted">Detail aktivitas {{ $log->causer->name }}</small>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between border-bottom mb-5">
                <h5 class="card-title m-0 me-2">Details Aktivitas</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center user-info-card py-3 px-4 rounded mb-5">
                    <div class="avatar flex-shrink-0 me-3">
                        <img src="{{ asset($profil) }}" alt="User" class="rounded-circle">
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-start gap-4">
                        <div class="me-3">
                            <p class="mb-0 text-heading fw-bold">{{$log->causer->name}}</p>
                            <small class="text-muted">{{$log->causer->roles->name}}</small>
                        </div>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-end gap-4">
                        <div class="me-3">
                            <p class="mb-0 text-heading fw-bold">{{$log->causer->email}}</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row p-2">
                    <div class="col-sm-12">
                        <div class="detail-section">
                            <div class="detail-label">Nama Log</div>
                            <div class="detail-value">{{ $log->log_name }}</div>
                        </div>
                        
                        <div class="detail-section">
                            <div class="detail-label">Aktivitas</div>
                            <div class="detail-value">{{ $log->description }}</div>
                        </div>
                        @php
                            $props = is_array($log->properties) ? $log->properties : $log->properties->toArray();
                        @endphp
                        @if(!empty($props))
                        <div class="detail-section">
                            @php
                                $props = $log->properties;
                            @endphp
                            <div class="detail-label mb-5">Detail Data</div>
                            <div class="detail-value d-flex justify-content-between align-items-center">
                                <ul class="p-0 m-0">
                                    <li class="d-flex align-items-center mb-5">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-building"></i></span>
                                        </div>
                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <h6 class="mb-0">Nama Perusahaan</h6>
                                                <small>{{$prop['nama_perusahaan']}}</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-center mb-5">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-success"><i class="icon-base bx bx-user"></i></span>
                                        </div>
                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <h6 class="mb-0">Nama PIC</h6>
                                                <small>{{$prop['nama_pic']}}</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-center mb-5">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-info"><i class="icon-base bx bx-rss"></i></span>
                                        </div>
                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <h6 class="mb-0">Paket Langganan</h6>
                                                <small>{{$paket['nama_paket']}}</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-secondary"><i class="icon-base bx bx-money"></i></span>
                                        </div>
                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <h6 class="mb-0">Harga Langganan</h6>
                                                <small>Rp.{{ number_format($props['harga'] ?? 0, 0, ',', '.') }}</small>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection