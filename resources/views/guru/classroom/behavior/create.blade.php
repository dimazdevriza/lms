@extends('layouts.lms')

@section('title', 'Tambah Catatan Perilaku - ' . $class->name)

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif;">✍️ Tambah Catatan Perilaku</h1>
                    <p>Kelas {{ $class->name }} • Catat perilaku positif atau negatif siswa</p>
                </div>
                <a href="{{ route('guru.classroom.behavior', $class) }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="content-card reveal reveal-delay-1">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-pen-nib"></i>
                    </div>
                    <h5 class="content-card-title">Formulir Catatan Perilaku</h5>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="{{ route('guru.classroom.behavior.store', $class) }}">
                        @csrf

                        <!-- Pilih Siswa -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600;">👤 Pilih Siswa</label>
                            <select class="form-select" name="student_id" required>
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                        {{ $student->user?->name ?? 'Siswa (Tanpa Nama)' }} (NISN: {{ $student->nisn }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Judul Catatan -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600;">📝 Judul Catatan</label>
                            <input class="form-control" type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Kedisiplinan Kelas atau Kerja Sama Tim" required>
                            @error('title')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Deskripsi Perilaku -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600;">📖 Deskripsi Perilaku</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Jelaskan perilaku siswa secara mendalam..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Jenis Catatan -->
                        <div class="mb-4">
                            <label class="form-label d-block mb-3" style="font-weight: 600;">🏷️ Jenis Catatan</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="behavior-type-card behavior-type-card--positif">
                                        <input class="form-check-input d-none" type="radio" name="type" id="typePositif" value="positif" @checked(old('type') === 'positif' || !old('type')) required>
                                        <label class="behavior-type-label" for="typePositif">
                                            <div class="behavior-type-icon">
                                                <i class="fas fa-thumbs-up"></i>
                                            </div>
                                            <div>
                                                <div class="behavior-type-title">Positif</div>
                                                <div class="behavior-type-desc">Perilaku baik, patut dicontoh</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="behavior-type-card behavior-type-card--negatif">
                                        <input class="form-check-input d-none" type="radio" name="type" id="typeNegatif" value="negatif" @checked(old('type') === 'negatif') required>
                                        <label class="behavior-type-label" for="typeNegatif">
                                            <div class="behavior-type-icon">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div>
                                                <div class="behavior-type-title">Negatif</div>
                                                <div class="behavior-type-desc">Perlu pembinaan/perbaikan</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('type')
                                <small class="text-danger mt-2 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Tanggal Kejadian -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600;">📅 Tanggal Kejadian</label>
                            <input class="form-control" type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
                            @error('date')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Tombol Submit -->
                        <div class="d-flex gap-2 mt-5">
                            <button class="btn btn-primary btn-lg px-4" type="submit">
                                <i class="fas fa-check-circle"></i> Simpan Catatan
                            </button>
                            <a class="btn btn-outline-secondary btn-lg px-4" href="{{ route('guru.classroom.behavior', $class) }}">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .behavior-type-card {
            border: 2px solid rgba(0, 0, 0, 0.08);
            border-radius: var(--radius-md);
            overflow: hidden;
            transition: all 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        .behavior-type-label {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            cursor: pointer;
            width: 100%;
            margin: 0;
        }
        .behavior-type-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            background: rgba(0, 0, 0, 0.04);
            color: #6c757d;
            transition: all 0.2s;
        }
        .behavior-type-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-heading);
        }
        .behavior-type-desc {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Positif checked */
        .behavior-type-card--positif:has(input[type="radio"]:checked) {
            border-color: var(--secondary);
            background-color: rgba(67, 160, 71, 0.05);
            box-shadow: 0 4px 12px rgba(67, 160, 71, 0.1);
        }
        .behavior-type-card--positif input[type="radio"]:checked + .behavior-type-label .behavior-type-icon {
            background-color: var(--secondary);
            color: white;
        }
        
        /* Negatif checked */
        .behavior-type-card--negatif:has(input[type="radio"]:checked) {
            border-color: #C62828;
            background-color: rgba(198, 40, 40, 0.03);
            box-shadow: 0 4px 12px rgba(198, 40, 40, 0.08);
        }
        .behavior-type-card--negatif input[type="radio"]:checked + .behavior-type-label .behavior-type-icon {
            background-color: #C62828;
            color: white;
        }
    </style>
    @endpush
@endsection
