@extends('layouts.lms')

@section('title', 'Tambah Kelas Mandiri (Ad-hoc)')

@section('content')
<div class="container-fluid p-0 reveal">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('guru.meetings.index') }}" class="btn btn-icon btn-light rounded-circle shadow-sm me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="h3 mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary);">Tambah Kelas Mandiri</h1>
            <p class="text-muted mb-0">Tambahkan mata pelajaran yang ingin Anda ajarkan secara mandiri tanpa harus melalui penugasan Tata Usaha.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4" style="border-top: 4px solid var(--primary) !important;">
        <div class="card-body p-4 p-md-5">
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" style="background-color: #fdf2f2; border-left: 4px solid #dc3545 !important;">
                    <i class="fas fa-exclamation-circle text-danger me-3 fs-5"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <form action="{{ route('guru.meetings.mandiri.store') }}" method="POST">
                @csrf
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="class_id" class="form-label fw-bold text-dark">Pilih Kelas</label>
                        <select name="class_id" id="class_id" class="form-select border-2" required style="border-radius: var(--radius-sm); padding: 0.75rem 1rem;">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="subject_id" class="form-label fw-bold text-dark">Pilih Mata Pelajaran</label>
                        <select name="subject_id" id="subject_id" class="form-select border-2" required style="border-radius: var(--radius-sm); padding: 0.75rem 1rem;">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 pt-4 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('guru.meetings.index') }}" class="btn btn-light px-4" style="border-radius: var(--radius-sm); font-weight: 600;">Batal</a>
                    <button type="submit" class="btn btn-success px-4" style="border-radius: var(--radius-sm); font-weight: 600; background-color: var(--primary); border: none;">
                        <i class="fas fa-save me-2"></i> Tambahkan Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
