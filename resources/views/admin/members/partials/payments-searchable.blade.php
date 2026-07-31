<div class="ajax-panel" data-panel-url="{{ route('admin.members.payments-panel', $member) }}">
    <div class="mb-2">
        <input type="text" data-ajax-param="search" class="form-control form-control-sm" placeholder="Search by receipt #, type, method or account...">
    </div>
    <div class="ajax-panel-results">
        @include('admin.members.partials._payments-results', ['payments' => $payments])
    </div>
</div>
