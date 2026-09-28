{{--
    Ported from mestiaque/acc-sfl's admin.partials.excel-import-page (same preview ->
    row-by-row validate -> save workflow used there for Expense/Balance Receive import) so
    HR-side imports follow the exact same pattern instead of a one-off upload-and-go modal.

    Editable in place: double-click any data cell to edit its value; on blur/Enter the
    edited row is re-validated against $recheckUrl (server-side, without re-uploading the
    file) and that row's status/errors update immediately - no page reload.

    Required props: title, backUrl, previewUrl, saveUrl, recheckUrl, columns (array of
    ['key','label', optional 'align']). The row objects returned by the preview/save/recheck
    endpoints must expose a value for each column's key, plus 'row' (spreadsheet row
    number), 'errors' (array), and 'saved' (bool).
--}}
@php
    $normalizedColumns = collect($columns)->map(fn ($c) => [
        'key' => $c['key'],
        'label' => $c['label'],
        'align' => $c['align'] ?? 'left',
    ])->values();
@endphp
<div class="excel-import-page" data-preview-url="{{ $previewUrl }}" data-save-url="{{ $saveUrl }}"
    data-recheck-url="{{ $recheckUrl }}" data-columns='{{ $normalizedColumns->toJson() }}'>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fa-solid fa-file-import mr-2"></i>{{ $title }}</h4>
            <a href="{{ $backUrl }}" class="btn btn-light btn-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap mb-3">
                <div>
                    <label class="form-label mb-1">Choose Excel File (.xlsx)</label>
                    <input type="file" class="excel-import-file form-control form-control-sm" accept=".xlsx,.xls">
                </div>
                @if(!empty($templateUrl))
                <a href="{{ $templateUrl }}" class="btn btn-light btn-sm">
                    <i class="fa-solid fa-download mr-1"></i> Download Template
                </a>
                @endif
            </div>

            <div class="excel-import-loading text-center py-4 d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 mb-0 text-muted">Processing file&hellip;</p>
            </div>

            <div class="excel-import-error alert alert-danger d-none"></div>
            <div class="excel-import-summary alert alert-info d-none"></div>
            <p class="excel-import-hint text-muted small d-none">Double-click a cell to edit it. It will be rechecked automatically once you leave the field.</p>

            <div class="excel-import-table-wrapper table-responsive" style="display:none; max-height: 65vh; overflow-y: auto;">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="excel-import-thead">
                        <tr>
                            <th>Row</th>
                            @foreach($normalizedColumns as $column)
                            <th>{{ $column['label'] }}</th>
                            @endforeach
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="excel-import-table-body"></tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ $backUrl }}" class="btn btn-light btn-sm">Cancel</a>
            <button type="button" class="excel-import-save-btn btn btn-primary btn-sm d-none" disabled>Save Valid Rows</button>
        </div>
    </div>
</div>

<style>
    .excel-import-table-wrapper .excel-import-thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #198754;
        color: #fff;
        box-shadow: 0 1px 0 #dee2e6;
    }
    .excel-import-row-saved {
        opacity: .6;
    }
    .excel-import-row-saved td[data-key] {
        pointer-events: none;
    }
</style>

@push('js')
@once
<script>
    (function () {
        var csrfToken = {!! json_encode(csrf_token()) !!};

        function escapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : value).html();
        }

        function getColumns($page) {
            try {
                return JSON.parse($page.attr('data-columns') || '[]');
            } catch (e) {
                return [];
            }
        }

        function statusHtmlFor(row) {
            if (row.saved) {
                return '<span class="badge badge-success">Saved</span>';
            }
            if (row.errors && row.errors.length) {
                return '<span class="badge badge-danger">' + escapeHtml(row.errors.join(' ')) + '</span>';
            }
            return '<span class="badge badge-secondary">Ready</span>';
        }

        function rowHtml(row, columns, rowIndex) {
            var hasErrors = !!(row.errors && row.errors.length);
            var cells = '<td>' + escapeHtml(row.row) + '</td>';
            columns.forEach(function (column) {
                var alignClass = column.align === 'right' ? ' class="text-right"' : '';
                cells += '<td' + alignClass + ' data-row-index="' + rowIndex + '" data-key="' + column.key + '">' + escapeHtml(row[column.key]) + '</td>';
            });
            cells += '<td class="excel-import-status">' + statusHtmlFor(row) + '</td>';
            var rowClass = (hasErrors ? ' table-danger' : '') + (row.saved ? ' excel-import-row-saved' : '');
            return '<tr' + (rowClass ? ' class="' + rowClass.trim() + '"' : '') + ' data-row-index="' + rowIndex + '">' + cells + '</tr>';
        }

        function renderRows($page, rows) {
            var columns = getColumns($page);
            var $body = $page.find('.excel-import-table-body');
            $body.empty();

            rows.forEach(function (row, index) {
                $body.append(rowHtml(row, columns, index));
            });

            return summarize(rows);
        }

        function summarize(rows) {
            var validCount = 0;
            var savedCount = 0;
            rows.forEach(function (row) {
                if (!(row.errors && row.errors.length)) { validCount++; }
                if (row.saved) { savedCount++; }
            });
            return { validCount: validCount, savedCount: savedCount, total: rows.length };
        }

        function refreshSaveButtonState($page) {
            var rows = $page.data('rows') || [];
            var counts = summarize(rows);
            var anySaved = counts.savedCount > 0;
            var $btn = $page.find('.excel-import-save-btn');
            if (anySaved && counts.savedCount === counts.total) {
                $btn.addClass('d-none');
            } else if (counts.validCount > 0) {
                $btn.removeClass('d-none').prop('disabled', false);
            } else {
                $btn.addClass('d-none');
            }
        }

        function ajaxErrorMessage(xhr) {
            if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                return xhr.responseJSON.message || xhr.responseJSON.error;
            }
            return 'Something went wrong. Please check the file and try again.';
        }

        function runPreview($page, file) {
            $page.find('.excel-import-error').addClass('d-none').text('');
            $page.find('.excel-import-summary').addClass('d-none').text('');
            $page.find('.excel-import-hint').addClass('d-none');
            $page.find('.excel-import-table-wrapper').hide();
            $page.find('.excel-import-table-body').empty();
            $page.find('.excel-import-save-btn').addClass('d-none').prop('disabled', true);
            $page.find('.excel-import-loading').removeClass('d-none');
            $page.removeData('rows');

            var formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: $page.data('preview-url'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (response) {
                    $page.find('.excel-import-loading').addClass('d-none');
                    if (response.error) {
                        $page.find('.excel-import-error').removeClass('d-none').text(response.error);
                        return;
                    }
                    if (!response.rows || response.rows.length === 0) {
                        $page.find('.excel-import-error').removeClass('d-none').text('No data rows found in the file.');
                        return;
                    }
                    $page.data('rows', response.rows);
                    var counts = renderRows($page, response.rows);
                    $page.find('.excel-import-table-wrapper').show();
                    $page.find('.excel-import-hint').removeClass('d-none');
                    $page.find('.excel-import-summary').removeClass('d-none').text(
                        counts.total + ' row(s) found - ' + counts.validCount + ' ready to import, ' + (counts.total - counts.validCount) + ' with errors.'
                    );
                    if (counts.validCount > 0) {
                        $page.find('.excel-import-save-btn').removeClass('d-none').prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    $page.find('.excel-import-loading').addClass('d-none');
                    $page.find('.excel-import-error').removeClass('d-none').text(ajaxErrorMessage(xhr));
                },
            });
        }

        function runSave($page, $btn, file) {
            if ($page.data('saving') || $page.data('saveCompleted')) { return; }
            $page.data('saving', true);

            $btn.prop('disabled', true).text('Saving...');
            $page.find('.excel-import-file').prop('disabled', true);
            $page.find('.excel-import-error').addClass('d-none').text('');
            $page.find('.excel-import-loading').removeClass('d-none');

            var formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: $page.data('save-url'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (response) {
                    $page.data('saving', false);
                    $page.find('.excel-import-loading').addClass('d-none');
                    if (response.error) {
                        $btn.prop('disabled', false).text('Save Valid Rows');
                        $page.find('.excel-import-file').prop('disabled', false);
                        $page.find('.excel-import-error').removeClass('d-none').text(response.error);
                        return;
                    }
                    $page.data('rows', response.rows);
                    var counts = renderRows($page, response.rows);
                    $page.find('.excel-import-summary').removeClass('d-none').removeClass('alert-info').addClass('alert-success').text(
                        counts.savedCount + ' row(s) saved successfully. ' + (counts.total - counts.savedCount) + ' row(s) skipped due to errors.'
                    );

                    if (counts.savedCount > 0) {
                        // Stays disabled from here on (saveCompleted guard in runSave):
                        // a re-click would re-upload the same file and re-save every
                        // already-saved row a second time, since the server has no way
                        // to know which rows were already persisted.
                        $page.data('saveCompleted', true);
                        $btn.text('Saved');
                        $page.find('.excel-import-file').prop('disabled', true);
                    } else {
                        $btn.prop('disabled', false).text('Save Valid Rows');
                        $page.find('.excel-import-file').prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    $page.data('saving', false);
                    $page.find('.excel-import-loading').addClass('d-none');
                    $btn.prop('disabled', false).text('Save Valid Rows');
                    $page.find('.excel-import-file').prop('disabled', false);
                    $page.find('.excel-import-error').removeClass('d-none').text(ajaxErrorMessage(xhr));
                },
            });
        }

        function recheckRow($page, rowIndex) {
            var rows = $page.data('rows') || [];
            var row = rows[rowIndex];
            if (!row) { return; }

            var columns = getColumns($page);
            var fields = {};
            columns.forEach(function (column) { fields[column.key] = row[column.key]; });

            var $tr = $page.find('tr[data-row-index="' + rowIndex + '"]');
            $tr.find('.excel-import-status').html('<span class="badge badge-secondary">Checking&hellip;</span>');

            $.ajax({
                url: $page.data('recheck-url'),
                method: 'POST',
                data: { row: row.row, fields: fields, _token: csrfToken },
                success: function (response) {
                    rows[rowIndex] = $.extend({}, row, response, { row: row.row });
                    $page.data('rows', rows);
                    $tr.replaceWith(rowHtml(rows[rowIndex], columns, rowIndex));
                    refreshSaveButtonState($page);
                },
                error: function (xhr) {
                    $tr.find('.excel-import-status').html('<span class="badge badge-danger">' + escapeHtml(ajaxErrorMessage(xhr)) + '</span>');
                },
            });
        }

        $(document).on('change', '.excel-import-page .excel-import-file', function () {
            var file = this.files && this.files[0];
            if (!file) { return; }
            var $page = $(this).closest('.excel-import-page');
            $page.find('.excel-import-save-btn').data('selectedFile', file);
            runPreview($page, file);
        });

        $(document).on('click', '.excel-import-page .excel-import-save-btn', function () {
            var $btn = $(this);
            var file = $btn.data('selectedFile');
            if (!file) { return; }
            runSave($btn.closest('.excel-import-page'), $btn, file);
        });

        $(document).on('dblclick', '.excel-import-table-body td[data-key]', function () {
            var $td = $(this);
            if ($td.find('input').length) { return; }

            var $page = $td.closest('.excel-import-page');
            var rowIndex = $td.data('row-index');
            var key = $td.data('key');
            var rows = $page.data('rows') || [];
            var row = rows[rowIndex];
            if (!row || row.saved) { return; }

            var currentValue = row[key] === null || row[key] === undefined ? '' : row[key];
            $td.empty().append(
                $('<input type="text" class="form-control form-control-sm">').val(currentValue)
            );
            $td.children('input').trigger('focus').trigger('select');
        });

        function commitEdit($td) {
            var $input = $td.children('input');
            if (!$input.length) { return; }

            var $page = $td.closest('.excel-import-page');
            var rowIndex = $td.data('row-index');
            var key = $td.data('key');
            var rows = $page.data('rows') || [];
            var row = rows[rowIndex];
            if (!row) { return; }

            row[key] = $input.val();
            $page.data('rows', rows);
            $td.text(row[key]);

            recheckRow($page, rowIndex);
        }

        $(document).on('blur', '.excel-import-table-body td[data-key] input', function () {
            commitEdit($(this).closest('td'));
        });

        $(document).on('keydown', '.excel-import-table-body td[data-key] input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).trigger('blur');
            } else if (e.key === 'Escape') {
                var $td = $(this).closest('td');
                var $page = $td.closest('.excel-import-page');
                var rowIndex = $td.data('row-index');
                var key = $td.data('key');
                var row = ($page.data('rows') || [])[rowIndex];
                $td.text(row ? row[key] : '');
            }
        });
    })();
</script>
@endonce
@endpush
