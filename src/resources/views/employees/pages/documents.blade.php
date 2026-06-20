@extends(adminTheme().'layouts.app')

@section('title')
<title>Employee Documents — {{ $employee->name }}</title>
@endsection

@push('css')
<style>
    .doc-row {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 10px;
        position: relative;
    }
    .doc-row .remove-row {
        position: absolute;
        top: 8px;
        right: 10px;
        cursor: pointer;
        color: #dc3545;
        background: none;
        border: none;
        font-size: 16px;
        line-height: 1;
        padding: 0;
    }
    .file-preview-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
    }
    .file-preview-item {
        position: relative;
        width: 70px;
        height: 70px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .file-preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .file-preview-item .pdf-icon { font-size: 28px; color: #dc3545; }
    .file-preview-item .remove-preview {
        position: absolute;
        top: 2px; right: 2px;
        background: rgba(220,53,69,0.85);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 18px; height: 18px;
        font-size: 10px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        line-height: 1;
        padding: 0;
    }
    .doc-card {
        border: 1px solid #e3e6ea;
        border-radius: 8px;
        overflow: hidden;
        transition: box-shadow .15s;
    }
    .doc-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.1); }
    .doc-thumb {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        overflow: hidden;
    }
    .doc-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .doc-thumb .pdf-big { font-size: 44px; color: #dc3545; }
</style>
@endpush

@section('contents')
@include(adminTheme().'alerts')

<div class="flex-grow-1 p-4">

    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Documents: {{ $employee->name }}</h4>
            <div class="d-flex align-items-center" style="gap:8px">
                <button type="button" class="btn btn-primary btn-sm" id="toggleUploadForm">
                    <i class="fa-solid fa-upload me-1"></i> Upload Documents
                </button>
                <a href="{{ route('hr-center.employees.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body py-2 px-3">
            <table class="table table-sm table-borderless mb-0" style="max-width:400px">
                <tr><th style="width:130px">Employee ID</th><td>: {{ $employee->employee_id ?? '-' }}</td></tr>
                <tr><th>Department</th><td>: {{ $employeeMeta['department'] ?? '-' }}</td></tr>
                <tr><th>Designation</th><td>: {{ $employeeMeta['designation'] ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    {{-- Upload Form --}}
    <div class="card mb-4" id="uploadFormCard" style="display:none">
        <div class="card-header"><h6 class="mb-0">Upload New Documents</h6></div>
        <div class="card-body">
            <form action="{{ route('hr-center.employees.documents.store', $employee->id) }}"
                  method="POST" enctype="multipart/form-data" id="docUploadForm">
                @csrf

                <div id="docRowsContainer">
                    {{-- Row template injected by JS --}}
                </div>

                <div class="d-flex align-items-center mt-2" style="gap:10px">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addDocRow">
                        <i class="fa-solid fa-plus"></i> Add Row
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-cloud-upload-alt"></i> Submit All
                    </button>
                    <button type="button" class="btn btn-light btn-sm" id="cancelUpload">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Document Grid --}}
    @if($documents->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa-solid fa-folder-open fa-3x mb-3"></i>
            <p>কোনো document পাওয়া যায়নি।</p>
        </div>
    @else
        <div class="row g-3">
            @foreach($documents->groupBy('title') as $title => $group)
            <div class="col-12 mb-2">
                <div class="d-flex align-items-center mb-2" style="gap:8px">
                    <i class="fa-solid fa-folder text-warning"></i>
                    <strong>{{ $title }}</strong>
                    <span class="badge badge-secondary">{{ $group->count() }} file</span>
                </div>
                <div class="row g-2">
                    @foreach($group as $doc)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="doc-card">
                            <div class="doc-thumb">
                                @if($doc->is_image)
                                    <img src="{{ $doc->url }}" alt="{{ $doc->file_name }}" loading="lazy">
                                @else
                                    <i class="fa-solid fa-file-pdf pdf-big"></i>
                                @endif
                            </div>
                            <div class="p-2">
                                <div class="small text-truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</div>
                                <div class="d-flex justify-content-between align-items-center mt-1" style="gap:4px">
                                    <span class="badge badge-light text-muted" style="font-size:10px">{{ $doc->file_size_human }}</span>
                                    <div class="d-flex" style="gap:4px">
                                        <a href="{{ $doc->url }}" target="_blank" class="btn-custom" title="View/Download" style="font-size:12px"><i class="fa-solid fa-eye"></i></a>
                                        <form method="POST" action="{{ route('hr-center.employees.documents.delete', $employee->id) }}"
                                              onsubmit="return confirm('এই document টি delete করবেন?')" style="display:inline">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="document_id" value="{{ $doc->id }}">
                                            <button type="submit" class="btn-custom danger" title="Delete" style="font-size:12px"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

@push('js')
<script>
(function () {
    let rowIndex = 0;

    function buildRow(index) {
        return `
        <div class="doc-row" id="docRow_${index}" data-index="${index}">
            <button type="button" class="remove-row" data-target="${index}" title="Remove row">
                <i class="fa-solid fa-times-circle"></i>
            </button>
            <div class="row align-items-start g-2">
                <div class="col-md-4">
                    <label class="form-label mb-1 small fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text"
                           name="documents[${index}][title]"
                           class="form-control form-control-sm"
                           placeholder="e.g. NID, Certificate, Contract..."
                           required>
                </div>
                <div class="col-md-8">
                    <label class="form-label mb-1 small fw-semibold">Files (Image / PDF) <span class="text-danger">*</span></label>
                    <input type="file"
                           name="documents[${index}][files][]"
                           class="form-control form-control-sm doc-file-input"
                           accept=".jpg,.jpeg,.png,.gif,.pdf"
                           multiple
                           required
                           data-row="${index}">
                    <div class="file-preview-wrap" id="preview_${index}"></div>
                </div>
            </div>
        </div>`;
    }

    function addRow() {
        const container = document.getElementById('docRowsContainer');
        container.insertAdjacentHTML('beforeend', buildRow(rowIndex));
        bindFileInput(rowIndex);
        rowIndex++;
    }

    function bindFileInput(index) {
        const input = document.querySelector(`.doc-file-input[data-row="${index}"]`);
        if (!input) return;
        input.addEventListener('change', function () {
            const preview = document.getElementById(`preview_${index}`);
            preview.innerHTML = '';
            Array.from(this.files).forEach((file, fi) => {
                const item = document.createElement('div');
                item.className = 'file-preview-item';
                item.dataset.fileIndex = fi;

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    item.appendChild(img);
                } else {
                    const icon = document.createElement('i');
                    icon.className = 'fa-solid fa-file-pdf pdf-icon';
                    item.appendChild(icon);
                }

                const nameTip = document.createElement('span');
                nameTip.title = file.name;
                nameTip.style.cssText = 'position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.45);color:#fff;font-size:9px;padding:1px 3px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis';
                nameTip.textContent = file.name;
                item.appendChild(nameTip);

                preview.appendChild(item);
            });
        });
    }

    // Toggle upload form
    document.getElementById('toggleUploadForm').addEventListener('click', function () {
        const card = document.getElementById('uploadFormCard');
        const isHidden = card.style.display === 'none' || card.style.display === '';
        card.style.display = isHidden ? 'block' : 'none';
        if (isHidden && rowIndex === 0) addRow();
    });

    document.getElementById('cancelUpload').addEventListener('click', function () {
        document.getElementById('uploadFormCard').style.display = 'none';
    });

    document.getElementById('addDocRow').addEventListener('click', addRow);

    // Remove row (event delegation)
    document.getElementById('docRowsContainer').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;
        const container = document.getElementById('docRowsContainer');
        if (container.querySelectorAll('.doc-row').length <= 1) {
            alert('কমপক্ষে একটি row থাকতে হবে।');
            return;
        }
        document.getElementById('docRow_' + btn.dataset.target)?.remove();
    });

    // Auto-open if there were validation errors
    @if($errors->any())
    document.getElementById('uploadFormCard').style.display = 'block';
    if (rowIndex === 0) addRow();
    @endif
})();
</script>
@endpush
