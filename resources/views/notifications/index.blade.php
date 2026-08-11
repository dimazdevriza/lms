@extends('layouts.lms')

@section('title', 'Notifikasi Saya')

@section('content')
    <!-- Header Banner -->
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="mb-1"><i class="fas fa-bell text-warning me-2"></i> Notifikasi Saya</h1>
                    <p class="mb-0">Pemberitahuan tugas baru, nilai, dan info akademik</p>
                </div>
                @if($notifications->whereNull('read_at')->count() > 0)
                    <div class="text-md-end">
                        <form action="{{ route('notifications.read-all') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light rounded-pill px-4 fw-bold text-success shadow-sm">
                                <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notification List -->
    <div class="reveal">
        @forelse($notifications as $n)
            <form action="{{ route('notifications.read', $n) }}" method="POST" id="read-form-{{ $n->id }}" class="mb-3">
                @csrf
                <div onclick="document.getElementById('read-form-{{ $n->id }}').submit();" 
                     class="content-card p-4 d-flex align-items-start gap-3 position-relative" 
                     style="border-radius: 16px; border: none; cursor: pointer; transition: all 0.2s; {{ is_null($n->read_at) ? 'background-color: rgba(37, 103, 30, 0.03); border-left: 5px solid var(--primary) !important;' : 'opacity: 0.85;' }}">
                    
                    <!-- Icon / Bullet -->
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" 
                         style="width: 42px; height: 42px; background-color: {{ is_null($n->read_at) ? 'var(--primary)' : '#6c757d' }}; flex-shrink: 0;">
                        @if(str_contains($n->title, 'Nilai'))
                            <i class="fas fa-star" style="font-size: 0.95rem;"></i>
                        @else
                            <i class="fas fa-file-alt" style="font-size: 0.95rem;"></i>
                        @endif
                    </div>

                    <!-- Message details -->
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                            <h6 class="mb-0 fw-bold {{ is_null($n->read_at) ? 'text-dark' : 'text-secondary' }}" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                {{ $n->title }}
                            </h6>
                            <span class="small text-muted" style="font-size: 0.75rem;">
                                {{ $n->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="mb-0 text-muted small text-truncate-2">
                            {{ $n->message }}
                        </p>
                    </div>

                    <!-- Unread green dot indicator -->
                    @if(is_null($n->read_at))
                        <span class="position-absolute end-0 top-0 mt-3 me-3 p-1 bg-success border border-light rounded-circle" style="width: 10px; height: 10px;" title="Belum dibaca"></span>
                    @endif
                </div>
            </form>
        @empty
            <div class="content-card py-5 text-center text-muted reveal" style="border-radius: 16px;">
                <i class="fas fa-bell-slash fa-3x mb-3 text-light"></i>
                <h5 class="fw-bold mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">Tidak ada notifikasi</h5>
                <p class="mb-0 text-muted small">Semua pemberitahuan Anda akan muncul di sini.</p>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    </div>

    <!-- Hover style overrides -->
    <style>
        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05) !important;
            background-color: rgba(37, 103, 30, 0.05) !important;
        }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
    </style>
@endsection
