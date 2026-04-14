{{-- ── Uploads Tab (Prescriptions / Reports) ── --}}
<div class="row g-4">

    {{-- Upload Action Card --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0" style="color: #2d3748;">
                    <i class="fas fa-{{ $uploadType === 'prescription' ? 'prescription' : 'file-medical-alt' }} me-2" style="color: #667eea;"></i>
                    {{ __('ui.health.my_uploads', ['type' => ucfirst($uploadType)]) }}
                </h5>
                <p class="text-muted mb-0" style="font-size: 0.82rem;">
                    {{ $uploadItems->count() }} {{ $uploadType }}(s) {{ __('ui.health.uploaded') }}
                </p>
            </div>
            <button class="btn text-white" data-bs-toggle="modal" data-bs-target="#addUploadModal"
                style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px; font-size: 0.88rem;">
                <i class="fas fa-cloud-upload-alt me-1"></i> {{ __('ui.health.upload_upload', ['type' => ucfirst($uploadType)]) }}
            </button>
        </div>
    </div>

    @if ($uploadItems->isEmpty())
        <div class="col-12">
            <div class="health-card">
                <div class="health-card-body">
                    <div class="empty-state">
                        <i class="fas fa-{{ $uploadType === 'prescription' ? 'prescription' : 'file-medical-alt' }} d-block"></i>
                        <p>{{ __('ui.health.no_uploads_yet', ['type' => ucfirst($uploadType)]) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        @foreach ($uploadItems as $upload)
            <div class="col-md-6 col-lg-4">
                <div class="health-card">
                    {{-- Upload Image --}}
                    <div style="height: 180px; overflow: hidden; background: #f8f9fb; display: flex; align-items: center; justify-content: center;">
                        @if ($upload->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($upload->file_path))
                            <img src="{{ asset('storage/' . $upload->file_path) }}" alt="{{ $upload->title }}"
                                style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                                onclick="window.open(this.src, '_blank')">
                        @else
                            <div class="text-center" style="color: #cbd5e0;">
                                <i class="fas fa-image" style="font-size: 2.5rem;"></i>
                                <div style="font-size: 0.78rem; margin-top: 0.5rem;">{{ __('ui.health.no_preview') }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- Upload Details --}}
                    <div class="health-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0" style="color: #2d3748; font-size: 0.92rem;">
                                {{ $upload->title }}
                            </h6>
                            <span class="health-card-badge {{ $upload->type === 'prescription' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success' }}">
                                {{ __("ui.health.{$upload->type}") }}
                            </span>
                        </div>

                        @if ($upload->summary)
                            <p class="mb-2" style="font-size: 0.82rem; color: #4a5568; line-height: 1.4;">
                                {{ Str::limit($upload->summary, 100) }}
                            </p>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @if ($upload->doctor_name)
                                <span class="metric-value-pill">
                                    <strong><i class="fas fa-user-md"></i></strong> {{ $upload->doctor_name }}
                                </span>
                            @endif
                            @if ($upload->institution)
                                <span class="metric-value-pill">
                                    <strong><i class="fas fa-hospital"></i></strong> {{ Str::limit($upload->institution, 20) }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top: 1px solid #edf2f7;">
                            <span style="font-size: 0.75rem; color: #a0aec0;">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $upload->document_date ? $upload->document_date->format('M d, Y') : $upload->created_at->format('M d, Y') }}
                            </span>
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="openEditUpload({{ $upload->id }}, '{{ addslashes($upload->title) }}', '{{ $upload->type }}', '{{ addslashes($upload->doctor_name ?? '') }}', '{{ addslashes($upload->institution ?? '') }}', '{{ $upload->document_date ? $upload->document_date->format('Y-m-d') : '' }}', '{{ addslashes($upload->notes ?? '') }}', '{{ addslashes($upload->summary ?? '') }}')">
                                    <i class="fas fa-edit"></i> {{ __('ui.health.edit') }}
                                </button>
                                <form action="{{ route('health.upload.destroy', $upload) }}" method="POST"
                                    onsubmit="return confirm('{{ __('ui.health.delete_upload_confirm') }}')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> {{ __('ui.health.delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>