@extends('layouts.lms')

@section('title', 'Pengaturan Profil')

@section('content')
    <div class="mb-5 reveal">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color: var(--secondary);">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil Saya</li>
            </ol>
        </nav>
        <h1 class="h3 mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary) !important;">Pengaturan Profil</h1>
        <p class="text-muted small">Kelola informasi akun Anda dan ubah kata sandi di sini.</p>
    </div>

    <div style="max-width: 800px; margin: 0 auto; padding-bottom: 2rem;">
        <!-- Profile Information Card -->
        <div class="content-card reveal reveal-delay-1 mb-4" style="border-radius: var(--radius-md) !important;">
            <div class="content-card-header">
                <div class="content-card-header-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h5 class="content-card-title">Informasi Profil</h5>
            </div>
            <div class="content-card-body p-4">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Google Account Integration Card -->
        <div class="content-card reveal reveal-delay-2 mb-4" style="border-radius: var(--radius-md) !important;">
            <div class="content-card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="content-card-header-icon">
                        <i class="fab fa-google"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Integrasi Akun Google</h5>
                </div>
            </div>
            <div class="content-card-body p-4">
                <p class="text-muted small mb-3">
                    Hubungkan akun Google Anda untuk mempermudah login ke LMS tanpa perlu memasukkan password lagi.
                </p>

                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (auth()->user()->google_id || auth()->user()->google_email)
                    <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white p-2 rounded-circle border shadow-sm">
                                <svg width="24" height="24" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-0">{{ auth()->user()->google_email ?? 'Akun Google Terhubung' }}</div>
                                <span class="badge bg-success text-white"><i class="fas fa-link me-1"></i> Terhubung</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('auth.google.disconnect') }}" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan hubungan akun Google dari LMS?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                                <i class="fas fa-unlink me-1"></i> Putuskan Hubungan
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white p-2 rounded-circle border shadow-sm">
                                <i class="fab fa-google text-muted fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-muted mb-0">Belum Ada Akun Google Terhubung</div>
                                <span class="badge bg-secondary text-white">Tidak Terhubung</span>
                            </div>
                        </div>
                        <a href="{{ route('auth.google', ['action' => 'connect']) }}" class="btn btn-success btn-sm fw-bold px-3">
                            <i class="fab fa-google me-1"></i> Hubungkan Akun Google
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Update Password Card -->
        <div class="content-card reveal reveal-delay-2 mb-4" style="border-radius: var(--radius-md) !important;">
            <div class="content-card-header">
                <div class="content-card-header-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h5 class="content-card-title">Ubah Password</h5>
            </div>
            <div class="content-card-body p-4">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- ponytail: Hapus Akun is intentionally hidden to preserve LMS data integrity (grades, submissions, attendances) --}}
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        .cropper-container-wrapper {
            max-height: 400px;
            width: 100%;
            overflow: hidden;
            background-color: #f7f7f7;
        }
        .cropper-container-wrapper img {
            max-width: 100%;
            display: block;
        }
    </style>
@endpush

@push('modals')
<div class="modal fade" id="cropModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: var(--radius-md); overflow: hidden; border: none;">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold text-white" id="cropModalLabel" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #ffffff !important;"><i class="fas fa-user-edit me-2 text-white"></i> Edit Foto Profil</h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="cropper-container-wrapper rounded border">
                    <img id="cropper-image" src="" alt="To Crop">
                </div>
                
                <!-- Controls Toolbar -->
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2 px-3 fw-semibold shadow-sm" id="btn-rotate-left">
                        <i class="fas fa-undo"></i> Putar Kiri
                    </button>
                    <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2 px-3 fw-semibold shadow-sm" id="btn-rotate-right">
                        <i class="fas fa-redo"></i> Putar Kanan
                    </button>
                    <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2 px-3 fw-semibold shadow-sm" id="btn-mirror">
                        <i class="fas fa-arrows-alt-h"></i> Cermin (Mirror)
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4 fw-semibold" id="btn-save-crop">Terapkan & Simpan</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        let cropper = null;
        let scaleX = 1;
        const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
        const cropperImage = document.getElementById('cropper-image');
        const fileInput = document.getElementById('avatar');
        let currentFile = null;
        let isCropped = false;

        // Override standard previewImage function
        window.previewImage = function(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                isCropped = false;
                currentFile = files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    cropperImage.src = e.target.result;
                    cropModal.show();
                };
                reader.readAsDataURL(currentFile);
            }
        };

        // Initialize Cropper when Modal is shown
        document.getElementById('cropModal').addEventListener('shown.bs.modal', function () {
            scaleX = 1;
            cropper = new Cropper(cropperImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        });

        // Destroy Cropper when Modal is hidden
        document.getElementById('cropModal').addEventListener('hidden.bs.modal', function () {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            if (!isCropped && fileInput) {
                fileInput.value = ''; // Reset input so it doesn't upload the uncropped file
            }
        });

        // Rotate Left
        document.getElementById('btn-rotate-left').addEventListener('click', function() {
            if (cropper) cropper.rotate(-90);
        });

        // Rotate Right
        document.getElementById('btn-rotate-right').addEventListener('click', function() {
            if (cropper) cropper.rotate(90);
        });

        // Mirror / Flip Horizontal
        document.getElementById('btn-mirror').addEventListener('click', function() {
            if (cropper) {
                scaleX = scaleX === 1 ? -1 : 1;
                cropper.scaleX(scaleX);
            }
        });

        // Save Crop
        document.getElementById('btn-save-crop').addEventListener('click', function() {
            if (cropper) {
                isCropped = true;
                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                canvas.toBlob(function(blob) {
                    if (blob && fileInput) {
                        // Create a new File from the blob
                        const croppedFile = new File([blob], currentFile.name, {
                            type: currentFile.type,
                            lastModified: Date.now()
                        });

                        // Replace files array in file input using DataTransfer
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(croppedFile);
                        fileInput.files = dataTransfer.files;

                        // Update the profile page UI preview
                        const output = document.getElementById('avatar-preview');
                        const placeholder = document.getElementById('avatar-placeholder');
                        
                        if (output) {
                            output.src = canvas.toDataURL(currentFile.type || 'image/jpeg');
                            output.classList.remove('d-none');
                        }
                        if (placeholder) {
                            placeholder.classList.add('d-none');
                        }

                        // Close modal
                        cropModal.hide();
                    }
                }, currentFile.type || 'image/jpeg');
            }
        });
    </script>
@endpush
