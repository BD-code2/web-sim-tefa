@if($errors->any())
    <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
        <p class="font-semibold">Data belum dapat disimpan. Periksa isian berikut:</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach
        </ul>
    </div>
@endif
