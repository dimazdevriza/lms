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
