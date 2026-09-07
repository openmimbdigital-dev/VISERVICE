<div class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-semibold text-slate-800">Facturación electrónica DIAN</h2>
            <p class="mt-0.5 text-xs text-slate-500">
                @if($setting)
                    Proveedor: {{ ucfirst($setting->provider) }} —
                    <span class="font-medium">{{ $setting->environment === 'production' ? 'Producción' : 'Pruebas' }}</span>
                @else
                    Este negocio aún no tiene configurada la facturación electrónica.
                @endif
            </p>
        </div>

        @if($electronic_invoice)
        <span class="inline-flex shrink-0 items-center self-start rounded-full px-3 py-1 text-xs font-semibold {{ $electronic_invoice->status->badgeClass() }}">
            {{ $electronic_invoice->status->label() }}
        </span>
        @endif
    </div>

    <div class="space-y-4 p-5">
        @if($missing !== [])
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-sm font-semibold text-amber-900">Faltan datos para poder emitir</p>
            <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs text-amber-800">
                @foreach($missing as $item)
                <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($electronic_invoice)
        <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium text-slate-500">Número autorizado</dt>
                <dd class="font-mono text-sm font-semibold text-slate-900">{{ $electronic_invoice->document_number }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500">Transacción del proveedor</dt>
                <dd class="font-mono text-sm text-slate-900">{{ $electronic_invoice->transaction_id ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-slate-500">CUFE</dt>
                <dd class="break-all font-mono text-xs text-slate-900">{{ $electronic_invoice->cufe ?? '—' }}</dd>
            </div>
            @if($electronic_invoice->sent_at)
            <div>
                <dt class="text-xs font-medium text-slate-500">Enviada</dt>
                <dd class="text-sm text-slate-900">{{ $electronic_invoice->sent_at->format('d/m/Y H:i') }}</dd>
            </div>
            @endif
            @if($electronic_invoice->accepted_at)
            <div>
                <dt class="text-xs font-medium text-slate-500">Validada por la DIAN</dt>
                <dd class="text-sm text-slate-900">{{ $electronic_invoice->accepted_at->format('d/m/Y H:i') }}</dd>
            </div>
            @endif
        </dl>

        @if($electronic_invoice->statusTimeline() !== [])
        <div>
            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500">Trazabilidad DIAN</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach($electronic_invoice->statusTimeline() as $step)
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">{{ $step }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($electronic_invoice->error_message)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
            <p class="text-sm font-semibold text-rose-900">
                Error del proveedor @if($electronic_invoice->error_id) (código {{ $electronic_invoice->error_id }}) @endif
            </p>
            <p class="mt-1 text-xs text-rose-800">{{ $electronic_invoice->error_message }}</p>
            <p class="mt-1.5 text-xs text-rose-700">
                Intentos: {{ $electronic_invoice->attempts }} — al reintentar se conserva el mismo número autorizado.
            </p>
        </div>
        @endif

        @if($electronic_invoice->hasFiles())
        <div class="flex flex-wrap gap-2">
            @if($electronic_invoice->xml_path)
            <a href="{{ route('admin.workshop.invoices.dian.file', ['electronicInvoice' => $electronic_invoice, 'type' => 'xml']) }}"
                class="btn btn-outline-secondary btn-sm">Descargar XML DIAN</a>
            @endif
            @if($electronic_invoice->pdf_path)
            <a href="{{ route('admin.workshop.invoices.dian.file', ['electronicInvoice' => $electronic_invoice, 'type' => 'pdf']) }}"
                class="btn btn-outline-secondary btn-sm">Descargar PDF DIAN</a>
            @endif
        </div>
        @endif
        @else
        <p class="text-sm text-slate-500">Esta factura todavía no se ha emitido electrónicamente.</p>
        @endif

        <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-4">
            @if($can_send && (! $electronic_invoice || $electronic_invoice->status->canBeSent()))
            <button type="button" wire:click="send" wire:loading.attr="disabled" wire:target="send"
                @disabled($missing !== [])
                class="btn btn-primary btn-sm disabled:cursor-not-allowed disabled:opacity-50">
                <span wire:loading.remove wire:target="send">
                    {{ $electronic_invoice && $electronic_invoice->attempts > 0 ? 'Reintentar envío' : 'Enviar a la DIAN' }}
                </span>
                <span wire:loading wire:target="send">Enviando...</span>
            </button>
            @endif

            @if($electronic_invoice && $electronic_invoice->transaction_id)
            <button type="button" wire:click="syncStatus" wire:loading.attr="disabled" wire:target="syncStatus"
                class="btn btn-outline-secondary btn-sm">
                <span wire:loading.remove wire:target="syncStatus">Actualizar estado</span>
                <span wire:loading wire:target="syncStatus">Consultando...</span>
            </button>

            @if($can_download)
            <button type="button" wire:click="downloadFiles" wire:loading.attr="disabled" wire:target="downloadFiles"
                class="btn btn-outline-secondary btn-sm">
                <span wire:loading.remove wire:target="downloadFiles">Traer XML y PDF</span>
                <span wire:loading wire:target="downloadFiles">Descargando...</span>
            </button>
            @endif
            @endif

            @if($electronic_invoice && $electronic_invoice->request_xml)
            <button type="button" wire:click="toggleXml" class="btn btn-outline-secondary btn-sm">
                {{ $show_xml ? 'Ocultar XML enviado' : 'Ver XML enviado' }}
            </button>
            @endif
        </div>

        @if($show_xml && $electronic_invoice?->request_xml)
        <pre class="max-h-96 overflow-auto rounded-xl bg-slate-900 p-4 text-[11px] leading-relaxed text-slate-100">{{ $electronic_invoice->request_xml }}</pre>
        @endif
    </div>
</div>
