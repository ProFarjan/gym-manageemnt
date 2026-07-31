<div class="ajax-panel" data-panel-url="{{ route('admin.members.attendance-panel', $member) }}">
    <div class="mb-2">
        <input type="text" class="form-control form-control-sm ajax-panel-search" placeholder="Search by source (manual/device)...">
    </div>
    <div class="ajax-panel-results">
        @include('admin.members.partials._attendance-results', ['attendances' => $attendances])
    </div>
</div>
