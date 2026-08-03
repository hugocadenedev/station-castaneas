@if (session('status'))
    <div class="mb-4 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
        {{ session('status') }}
    </div>
@endif