@extends('admin.layouts.app')

@section('title', $tpu->exists ? 'Edit TPU' : 'Tambah TPU')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">{{ $tpu->exists ? 'Edit TPU' : 'Tambah TPU' }}</h4>
                <p class="text-muted mb-0">Isi data TPU yang akan tampil di seluruh sistem.</p>
            </div>

            <form method="POST" action="{{ $tpu->exists ? route('kdlh.tpu.update', $tpu) : route('kdlh.tpu.store') }}">
                @csrf
                @if($tpu->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama TPU</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $tpu->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror" value="{{ old('lokasi', $tpu->lokasi) }}">
                        @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Ringkasan</label>
                        <textarea name="ringkasan" class="form-control @error('ringkasan') is-invalid @enderror" rows="3">{{ old('ringkasan', $tpu->ringkasan) }}</textarea>
                        @error('ringkasan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Highlight</label>
                        <textarea name="highlight" class="form-control @error('highlight') is-invalid @enderror" rows="3">{{ old('highlight', $tpu->highlight) }}</textarea>
                        @error('highlight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- <div class="col-md-12">
                        <label class="form-label">Biaya Sewa Makam</label>
                        <p class="text-muted small mb-2">
                            Tambahkan satu baris untuk setiap jenis biaya sewa (misal: Reguler, VIP, Tumpang Sari).
                            Daftar ini akan muncul sebagai pilihan dropdown di halaman permohonan perpanjangan makam milik ahli waris.
                        </p>

                        <div id="biaya-sewa-wrapper">
                            @php
                                $existingBiayaSewa = old('biaya_sewa', $tpu->biayaSewas ?? []);
                            @endphp
                            @forelse($existingBiayaSewa as $i => $item)
                                <div class="row g-2 mb-2 biaya-row align-items-center">
                                    <div class="col-md-5">
                                        <input type="text" name="biaya_sewa[{{ $i }}][label]"
                                               class="form-control @error('biaya_sewa.'.$i.'.label') is-invalid @enderror"
                                               placeholder="Nama biaya (mis. Reguler)"
                                               value="{{ is_array($item) ? ($item['label'] ?? '') : $item->label }}">
                                        @error('biaya_sewa.'.$i.'.label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" min="0" step="1" name="biaya_sewa[{{ $i }}][harga]"
                                                   class="form-control @error('biaya_sewa.'.$i.'.harga') is-invalid @enderror"
                                                   placeholder="Harga"
                                                   value="{{ is_array($item) ? ($item['harga'] ?? '') : $item->harga }}">
                                        </div>
                                        @error('biaya_sewa.'.$i.'.harga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger w-100 btn-remove-biaya">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                {{-- Sengaja dikosongkan; user bisa klik "+ Tambah Biaya" untuk memulai baris pertama --}}
                            @endforelse
                        </div>

                        <button type="button" id="btn-add-biaya" class="btn btn-sm btn-outline-primary mt-1">
                            <i class="bi bi-plus-lg"></i> Tambah Biaya
                        </button> -->
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nomor WhatsApp Petugas <i class="bi bi-whatsapp text-success"></i></label>
                        <select name="wa_petugas_id" class="form-select @error('wa_petugas_id') is-invalid @enderror">
                            <option value="">— Tidak ditampilkan —</option>
                            @foreach($petugasList ?? [] as $petugas)
                                <option value="{{ $petugas->id }}" @selected(old('wa_petugas_id', $tpu->wa_petugas_id) == $petugas->id)>
                                    {{ $petugas->name }} — {{ $petugas->no_hp ?? '(nomor tidak tersedia)' }}
                                    @if($petugas->tpu) ({{ $petugas->tpu }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Nomor ini akan ditampilkan di halaman utama dan dashboard user sebagai kontak penguburan segera.</small>
                        @error('wa_petugas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('kdlh.tpu.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">{{ $tpu->exists ? 'Simpan Perubahan' : 'Simpan TPU' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('biaya-sewa-wrapper');
    const addBtn = document.getElementById('btn-add-biaya');
    let idx = wrapper.querySelectorAll('.biaya-row').length;

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 biaya-row align-items-center';
        row.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="biaya_sewa[${idx}][label]" class="form-control" placeholder="Nama biaya (mis. Reguler)">
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" min="0" step="1" name="biaya_sewa[${idx}][harga]" class="form-control" placeholder="Harga">
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 btn-remove-biaya">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
        wrapper.appendChild(row);
        idx++;
    });

    wrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-biaya');
        if (btn) {
            btn.closest('.biaya-row').remove();
        }
    });
});
</script>
@endpush
@endsection