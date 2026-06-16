@extends('admin.layouts.app')

@section('title')
<title>{{ $entity['title'] }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">{{ $item->exists ? 'Edit' : 'Create' }} {{ $entity['title'] }}</h4>
        </div>
        <div class="card-body">
            <form method="post" action="{{ $item->exists ? route('hr-center.masters.update', [$entityKey, $item->id]) : route('hr-center.masters.store', $entityKey) }}">
                @csrf
                @if($item->exists)
                    @method('put')
                @endif

                <div class="row">
                    @php($formContext = 'page')
                    @include('hr::masters.partials.fields')
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    @if($entityKey === 'bonus-titles')
                        <button type="submit" name="add_policy" value="1" class="btn btn-info btn-sm">Save & Add Policy</button>
                    @endif
                    <a href="{{ route('hr-center.masters.index', $entityKey) }}" class="btn btn-light btn-sm">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        (function () {
            function initTiny() {
                if (typeof tinymce === 'undefined') return;

                tinymce.remove('textarea.js-master-textarea');
                tinymce.init({
                    selector: 'textarea.js-master-textarea',
                    height: 180,
                    menubar: false,
                    branding: false,
                    plugins: 'lists link code',
                    toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
                    statusbar: true,
                    resize: 'both'
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTiny);
            } else {
                initTiny();
            }
        })();
    </script>
</div>
@endsection
