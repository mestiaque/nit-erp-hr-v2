@extends(adminTheme().'layouts.app')
@section('title')
<title>{{ websiteTitle('Import ZKTeco Attendance') }}</title>
@endsection

@push('css')
<style>
    .import-container {
        max-width: 700px;
        margin: 40px auto;
    }
    .upload-area {
        border: 2px dashed #56d2ff;
        border-radius: 15px;
        padding: 40px;
        text-align: center;
        background: #f8fdff;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    .upload-area:hover, .upload-area.dragover {
        background: #eefbff;
        border-color: #007bff;
    }
    .upload-icon {
        font-size: 50px;
        color: #56d2ff;
        margin-bottom: 15px;
    }
    #attendance_file {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    .file-info {
        margin-top: 15px;
        display: none;
        font-weight: 600;
        color: #333;
    }
    .progress-wrapper {
        display: none;
        margin-top: 20px;
    }
    .progress {
        height: 10px;
        border-radius: 5px;
    }
    .btn-import {
        background: #56d2ff;
        border: none;
        color: #fff;
        padding: 10px 30px;
        font-weight: 600;
        border-radius: 25px;
        transition: 0.3s;
    }
    .btn-import:hover {
        background: #33b5e5;
        box-shadow: 0 4px 12px rgba(86, 210, 255, 0.4);
    }
    .instruction-card {
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        border-left: 4px solid #56d2ff;
        margin-bottom: 20px;
    }
    .guideline-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(86, 210, 255, 0.10);
        padding: 18px 15px;
        margin-bottom: 18px;
        border-left: 5px solid #56d2ff;
        transition: box-shadow 0.3s, border-color 0.3s;
        position: relative;
    }
    .guideline-section:hover {
        box-shadow: 0 6px 24px rgba(86, 210, 255, 0.18);
        border-left: 5px solid #007bff;
        background: #f8fdff;
    }
    .guideline-section h5 {
        color: #007bff;
        font-weight: 800;
        margin-bottom: 14px;
        letter-spacing: 0.5px;
        font-size: 1.25rem;
        text-shadow: 0 2px 8px rgba(86,210,255,0.08);
        /* background: linear-gradient(90deg, #e3f2fd 0%, #fff 100%); */
        padding: 6px 16px 6px 0;
        border-radius: 6px;
        display: inline-block;
    }
    .guideline-section ul, .guideline-section ol {
        margin-bottom: 0;
    }
    .smart-list-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.08rem;
        /* background: #f4faff; */
        border-radius: 7px;
        padding: 14px 20px;
        color: #222;
        font-weight: 500;
        box-shadow: 0 1px 4px rgba(86,210,255,0.07);
        line-height: 1.7;
        letter-spacing: 0.01em;
        margin-bottom: 7px;
        transition: background 0.2s, box-shadow 0.2s, color 0.2s;
    }
    .smart-list-item:hover {
        background: #e3f2fd;
        box-shadow: 0 4px 16px rgba(86,210,255,0.13);
        color: #007bff;
    }
    .smart-icon {
        color: #56d2ff;
        font-size: 1.3em;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
    }
</style>
@endpush

@section('contents')
@include(adminTheme().'alerts')
<div class="flex-grow-1">
    <div class="container-fluid">
        <div class="import-container">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="card-title text-center">Import ZKTeco Machine Log</h4>
                </div>
                <div class="card-body">

                    <!-- Instructions -->
                    <div class="instruction-card shadow-sm">
                        <h6><i class="fa fa-info-circle mr-1"></i> নির্দেশাবলী:</h6>
                        <small class="text-muted">
                            ১. মেশিন থেকে সংগৃহীত .txt বা .dat ফাইলটি আপলোড করুন। <br>
                            ২. ডাটা ফরম্যাট হতে হবে: ID, Date, Time, Terminal ID।
                        </small>
                    </div>

                    <form id="uploadForm" action="{{ route('hr-center.importZktecoAction') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Drag & Drop Area -->
                        <div class="upload-area" id="dropArea">
                            <input type="file" name="attendance_file" id="attendance_file" accept=".txt,.dat">
                            <div class="upload-icon">
                                <i class="fa fa-upload"></i>
                            </div>
                            <h5>Drag & Drop your file here</h5>
                            <p class="text-muted">or click to browse from computer</p>

                            <div id="fileInfo" class="file-info">
                                <i class="fa fa-file text-primary"></i> <span id="fileName"></span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress-wrapper" id="progressWrapper">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Processing...</small>
                                <small id="progressPercent">0%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                    id="progressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-import shadow-sm" id="submitBtn">
                                <i class="fa fa-upload mr-1"></i> Start Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ZKTimeSync Tutorial Modal Trigger Button -->
        {{-- <div class="text-center my-4">
            <button type="button" class="btn btn-import btn-sm shado text-dark" data-bs-toggle="modal" data-bs-target="#zkTutorialModal" style="background: #bfc1d03b; border: 1px dashed #56d2ff;">
                <i class="fa fa-book mr-2"></i> ZKTimeSync App
            </button>
        </div> --}}

        <!-- ZKTimeSync Tutorial Modal -->
        <div class="modal fade" id="zkTutorialModal" tabindex="-1" aria-labelledby="zkTutorialModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header text-white" style="background:#56d2ff">
                        <h4 class="modal-title text-white" id="zkTutorialModalLabel"><i class="fa fa-book mr-2"></i> ZKTimeSync Tutorial</h4>
                        <button type="button" class="btn-close btn btn-sm btn-custom danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>ভূমিকা</h5>
                                    <p>ZKTimeSync হলো একটি সহজ Python ভিত্তিক টাইম অ্যান্ড অ্যাটেনডেন্স ডিভাইস ম্যানেজমেন্ট টুল। এর মূল বৈশিষ্ট্যগুলো হলো ডিভাইস অ্যাড, এডিট, রিমুভ, তারিখ অনুসারে সিঙ্ক এবং অটো সিঙ্ক।</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>ইনস্টলেশন</h5>
                                    <div class="smart-list">
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-play"></i></span> App ডাউনলোড করুন এবং এক্সিকিউট করুন <a href="{{ url('admin/download/zk-installer') }}" download class="btn btn-import btn-sm"><i class="fa fa-download"> &nbsp; Download</i></a></div>
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-cogs"></i></span> ইনস্টল করার সময় যদি “Don’t run” লেখা দেখা যায়, তাহলে “More info”-তে ক্লিক করুন এবং এরপর “Run anyway” নির্বাচন করুন। </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>GUI বৈশিষ্ট্য</h5>
                                    <div class="smart-list row g-3">
                                        <div class="smart-list-item col-4"><span class="smart-icon"><i class="fa fa-plus-circle"></i></span> Add Device</div>
                                        <div class="smart-list-item col-4"><span class="smart-icon"><i class="fa fa-edit"></i></span> Edit Device</div>
                                        <div class="smart-list-item col-4"><span class="smart-icon"><i class="fa fa-trash"></i></span> Remove Device</div>
                                        <div class="smart-list-item col-4"><span class="smart-icon"><i class="fa fa-calendar"></i></span> Date Selection</div>
                                        <div class="smart-list-item col-4"><span class="smart-icon"><i class="fa fa-refresh"></i></span> Auto Sync</div>
                                        <div class="smart-list-item col-4"><span class="smart-icon"><i class="fa fa-bolt"></i></span> SYNC NOW</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>ডিভাইস পরিচালনা</h5>
                                    <p>নতুন ডিভাইস যোগ, সম্পাদনা ও মুছে ফেলার নিয়ম:</p>
                                    <div class="smart-list">
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-plus"></i></span> Add: ডিভাইসের নাম, IP এবং পোর্ট এবং পাসওয়ার্ড লিখুন</div>
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-edit"></i></span> Edit: ডিভাইসের তথ্য পরিবর্তন করুন</div>
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-trash"></i></span> Remove: ডিভাইস সম্পূর্ণ মুছে ফেলুন</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>তারিখ নির্বাচন</h5>
                                    <p>Data Sync এর জন্য বিভিন্ন তারিখের অপশন:</p>
                                    <div class="smart-list row">
                                        <div class="smart-list-item col-3"><span class="smart-icon"><i class="fa fa-calendar"></i></span> Today</div>
                                        <div class="smart-list-item col-3"><span class="smart-icon"><i class="fa fa-calendar"></i></span> Yesterday</div>
                                        <div class="smart-list-item col-3"><span class="smart-icon"><i class="fa fa-calendar"></i></span> Last 7 Days</div>
                                        <div class="smart-list-item col-3"><span class="smart-icon"><i class="fa fa-calendar"></i></span> Custom Date</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>অটো সিঙ্ক</h5>
                                    <p>আপনি চাইলে ডিভাইস অটো সিঙ্ক করতে পারেন নিম্নলিখিত সময়সূচীতে:</p>
                                    <div class="smart-list row">
                                        <div class="smart-list-item col-2"><span class="smart-icon"><i class="fa fa-clock"></i></span> 1h</div>
                                        <div class="smart-list-item col-2"><span class="smart-icon"><i class="fa fa-clock"></i></span> 4h</div>
                                        <div class="smart-list-item col-2"><span class="smart-icon"><i class="fa fa-clock"></i></span> 6h</div>
                                        <div class="smart-list-item col-2"><span class="smart-icon"><i class="fa fa-clock"></i></span> 8h</div>
                                        <div class="smart-list-item col-2"><span class="smart-icon"><i class="fa fa-clock"></i></span> 12h</div>
                                        <div class="smart-list-item col-2"><span class="smart-icon"><i class="fa fa-clock"></i></span> 24h</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>সমস্যা হলে করণীয়</h5>
                                    <div class="smart-list">
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-wifi"></i></span> ডিভাইস ও পিসি একই নেটওয়ার্কে থাকতে হবে</div>
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-globe"></i></span> পিসিতে ইন্টারনেট সংযোগ সক্রিয় থাকতে হবে</div>
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-key"></i></span> IP, Port এবং Password সঠিকভাবে প্রদান করুন</div>
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-shield"></i></span> Permission Error → App Run as Admin করুন</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="guideline-section">
                                    <h5>সমর্থিত প্ল্যাটফর্মসমূহ:</h5>
                                    <div class="smart-list">
                                        <div class="smart-list-item"><span class="smart-icon"><i class="fa fa-desktop"></i></span> উইন্ডোজ ৮ বা তার পরের সংস্করণ </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <small class="text-muted">&copy; 2026 ZKTimeSync Tutorial</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('attendance_file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const uploadForm = document.getElementById('uploadForm');
    const progressWrapper = document.getElementById('progressWrapper');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');

    // highlight drop area
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropArea.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropArea.classList.remove('dragover');
        }, false);
    });

    // handle file select
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileName.textContent = this.files[0].name;
            fileInfo.style.display = 'block';
        }
    });

    // Form Submit with Fake Progress for UX
    uploadForm.onsubmit = function() {
        document.getElementById('submitBtn').disabled = true;
        progressWrapper.style.display = 'block';
        let width = 0;
        let interval = setInterval(() => {
            if (width >= 90) {
                clearInterval(interval);
            } else {
                width += 5;
                progressBar.style.width = width + '%';
                progressPercent.textContent = width + '%';
            }
        }, 100);
    };
</script>
@endpush
