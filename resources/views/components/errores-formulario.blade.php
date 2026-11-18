@if ($errors->any())
    <div class="aris-aviso mb-5" role="alert">
        <p class="font-semibold">Revisa los datos del formulario.</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
