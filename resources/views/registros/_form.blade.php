@php
    $editing = isset($registro);
    $selectedAnexo = $anexos->firstWhere('id', (int) old('anexo_id', $selectedAnexoId ?? $registro->anexo_id ?? 0));
    $showAnexoSelector = $anexos->count() > 1;
@endphp

<div class="space-y-6">
    <section class="card-panel">
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-display text-xl font-bold text-slate-900">Datos de asignación</h3>
                <p class="mt-1 text-sm text-slate-500">Puedes escribir el ITEM manualmente. Si lo dejas vacío, el sistema calculará el siguiente disponible para ese anexo.</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @if ($showAnexoSelector)
                <div class="md:col-span-2">
                    <label class="form-label" for="anexo_id">Anexo</label>
                    <select class="form-input" id="anexo_id" name="anexo_id">
                        <option value="">Selecciona</option>
                        @foreach ($anexos as $anexoItem)
                            <option value="{{ $anexoItem->id }}" @selected((string) old('anexo_id', $selectedAnexoId ?? $registro->anexo_id ?? '') === (string) $anexoItem->id)>
                                #{{ $anexoItem->id }} - {{ $anexoItem->titulo ?: 'Sin título' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="anexo_id" value="{{ old('anexo_id', $selectedAnexoId ?? $registro->anexo_id ?? $anexos->first()?->id) }}">
                <div class="md:col-span-2 rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm text-cyan-900">
                    <p class="font-semibold">Anexo aplicado automáticamente</p>
                    <p class="mt-1">{{ $selectedAnexo?->titulo ?: 'Anexo único' }}</p>
                </div>
            @endif
            <div>
                <label class="form-label" for="item">{{ config('anexo.column_labels.item') }}</label>
                <input class="form-input" type="number" min="1" id="item" name="item" value="{{ old('item', $registro->item ?? $nextItemPreview ?? '') }}" placeholder="Déjalo vacío para autogenerar">
            </div>
            <div>
                <label class="form-label" for="codigo_unidad">{{ config('anexo.column_labels.codigo_unidad') }}</label>
                @php
                    $unidadActual = (string) old('codigo_unidad', $registro->codigo_unidad ?? '');
                    $listaUnidades = collect($codigosUnidad ?? []);
                    $gruposUnidad = $listaUnidades->groupBy(fn ($u) => $u->accion ?: 'Códigos de unidad');
                    $unidadEnLista = $listaUnidades->contains(fn ($u) => $u->descripcion === $unidadActual);
                @endphp
                <select class="form-input" id="codigo_unidad" name="codigo_unidad" title="{{ $unidadActual }}">
                    <option value="">Seleccione</option>
                    @if ($unidadActual !== '' && ! $unidadEnLista)
                        <option value="{{ $unidadActual }}" selected>{{ \Illuminate\Support\Str::limit($unidadActual, 90) }} (valor anterior)</option>
                    @endif
                    @foreach ($gruposUnidad as $grupo => $unidades)
                        <optgroup label="{{ $grupo }}">
                            @foreach ($unidades as $unidad)
                                <option value="{{ $unidad->descripcion }}" title="{{ $unidad->descripcion }}" @selected($unidadActual === $unidad->descripcion)>{{ \App\Models\CodigoUnidad::etiqueta($unidad->descripcion) }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label" for="expediente_siged">{{ config('anexo.column_labels.expediente_siged') }}</label>
                <input class="form-input" type="text" id="expediente_siged" name="expediente_siged" value="{{ old('expediente_siged', $registro->expediente_siged ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="fecha_asignacion">{{ config('anexo.column_labels.fecha_asignacion') }}</label>
                <input class="form-input" type="date" id="fecha_asignacion" name="fecha_asignacion" value="{{ old('fecha_asignacion', isset($registro) && $registro->fecha_asignacion ? $registro->fecha_asignacion->format('Y-m-d') : '') }}">
            </div>
            <div>
                <label class="form-label" for="numero_documento">{{ config('anexo.column_labels.numero_documento') }}</label>
                <input class="form-input" type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento', $registro->numero_documento ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="codigo_osinergmin">{{ config('anexo.column_labels.codigo_osinergmin') }}</label>
                <input class="form-input" type="text" id="codigo_osinergmin" name="codigo_osinergmin" list="lista-agentes-codigo" autocomplete="off" placeholder="Escribe el código..." value="{{ old('codigo_osinergmin', $registro->codigo_osinergmin ?? '') }}">
                <datalist id="lista-agentes-codigo"></datalist>
            </div>
            <div>
                <label class="form-label" for="codigo_actividad">{{ config('anexo.column_labels.codigo_actividad') }}</label>
                @php
                    $actividadActual = (string) old('codigo_actividad', $registro->codigo_actividad ?? '');
                    $listaActividades = collect($actividades ?? []);
                @endphp
                <select class="form-input" id="codigo_actividad" name="codigo_actividad">
                    <option value="">Seleccione</option>
                    @if ($actividadActual !== '' && ! $listaActividades->contains('codigo_actividad', $actividadActual))
                        <option value="{{ $actividadActual }}" selected>{{ $actividadActual }}</option>
                    @endif
                    @foreach ($listaActividades as $act)
                        <option value="{{ $act->codigo_actividad }}" @selected($actividadActual === (string) $act->codigo_actividad)>{{ $act->codigo_actividad }} - {{ $act->actividad }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Datos del agente</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="form-label" for="razon_social_agente">{{ config('anexo.column_labels.razon_social_agente') }}</label>
                <input class="form-input" type="text" id="razon_social_agente" name="razon_social_agente" list="lista-agentes-razon" autocomplete="off" placeholder="Escribe la razón social o elige un código OSINERGMIN..." value="{{ old('razon_social_agente', $registro->razon_social_agente ?? '') }}">
                <datalist id="lista-agentes-razon"></datalist>
                <p id="estado-agente" class="mt-2 text-xs text-slate-500"></p>
            </div>
            <div class="md:col-span-2 rounded-2xl border border-cyan-200 bg-cyan-50" style="padding:16px">
                <p class="text-sm font-semibold text-cyan-900" style="margin-bottom:12px">Datos del agente según el Registro de Hidrocarburos (se llenan con el CODIGO OSINERGMIN)</p>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="md:col-span-2"><label class="form-label" for="ag_actividad">ACTIVIDAD</label><input class="form-input bg-white" id="ag_actividad" readonly></div>
                    <div><label class="form-label" for="ag_codigo">CODIGO OSINERGMIN</label><input class="form-input bg-white" id="ag_codigo" readonly></div>
                    <div><label class="form-label" for="ag_nro_registro">NRO. DE REGISTRO</label><input class="form-input bg-white" id="ag_nro_registro" readonly></div>
                    <div><label class="form-label" for="ag_fecha_emision">FECHA DE EMISIÓN</label><input class="form-input bg-white" id="ag_fecha_emision" readonly></div>
                    <div><label class="form-label" for="ag_ruc">RUC</label><input class="form-input bg-white" id="ag_ruc" readonly></div>
                    <div class="md:col-span-2"><label class="form-label" for="ag_razon_social">RAZÓN SOCIAL</label><input class="form-input bg-white" id="ag_razon_social" readonly></div>
                    <div class="md:col-span-2"><label class="form-label" for="ag_direccion">DIRECCIÓN</label><input class="form-input bg-white" id="ag_direccion" readonly></div>
                    <div><label class="form-label" for="ag_ubigeo">UBIGEO</label><input class="form-input bg-white" id="ag_ubigeo" readonly></div>
                    <div><label class="form-label" for="ag_placa">PLACA PRINCIPAL</label><input class="form-input bg-white" id="ag_placa" readonly></div>
                </div>
            </div>
            <div>
                <label class="form-label" for="personal_es">{{ config('anexo.column_labels.personal_es') }}</label>
                <input class="form-input" type="text" id="personal_es" name="personal_es" value="{{ old('personal_es', $registro->personal_es ?? '') }}">
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Supervisión/Fiscalización</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="md:col-span-2">
                <label class="form-label" for="tipo_supervision">{{ config('anexo.column_labels.tipo_supervision') }}</label>
                <input class="form-input" type="text" id="tipo_supervision" name="tipo_supervision" value="{{ old('tipo_supervision', $registro->tipo_supervision ?? '') }}">
            </div>
            <div class="md:col-span-2">
                <label class="form-label" for="tipo_entregable">{{ config('anexo.column_labels.tipo_entregable') }}</label>
                <input class="form-input" type="text" id="tipo_entregable" name="tipo_entregable" value="{{ old('tipo_entregable', $registro->tipo_entregable ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="numero_informe">{{ config('anexo.column_labels.numero_informe') }}</label>
                <input class="form-input" type="text" id="numero_informe" name="numero_informe" value="{{ old('numero_informe', $registro->numero_informe ?? '') }}">
            </div>
            <div>
                <label class="form-label" for="visitado">{{ config('anexo.column_labels.visitado') }}</label>
                <select class="form-input" id="visitado" name="visitado">
                    <option value="1" @selected((string) old('visitado', isset($registro) ? (int) $registro->visitado : '0') === '1')>Sí</option>
                    <option value="0" @selected((string) old('visitado', isset($registro) ? (int) $registro->visitado : '0') === '0')>No</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="efectividad">{{ config('anexo.column_labels.efectividad') }}</label>
                <select class="form-input" id="efectividad" name="efectividad">
                    <option value="1" @selected((string) old('efectividad', isset($registro) ? (int) $registro->efectividad : '0') === '1')>Sí</option>
                    <option value="0" @selected((string) old('efectividad', isset($registro) ? (int) $registro->efectividad : '0') === '0')>No</option>
                </select>
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Fechas y estado</h3>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="form-label" for="fecha_visita">{{ config('anexo.column_labels.fecha_visita') }}</label>
                <input class="form-input" type="date" id="fecha_visita" name="fecha_visita" value="{{ old('fecha_visita', isset($registro) && $registro->fecha_visita ? $registro->fecha_visita->format('Y-m-d') : '') }}">
            </div>
            <div class="md:col-span-2">
                <label class="form-label" for="fecha_derivacion">{{ config('anexo.column_labels.fecha_derivacion') }}</label>
                <input class="form-input" type="date" id="fecha_derivacion" name="fecha_derivacion" value="{{ old('fecha_derivacion', isset($registro) && $registro->fecha_derivacion ? $registro->fecha_derivacion->format('Y-m-d') : '') }}">
            </div>
            <div>
                <label class="form-label" for="estado_entregable">{{ config('anexo.column_labels.estado_entregable') }}</label>
                <select class="form-input" id="estado_entregable" name="estado_entregable">
                    <option value="">Selecciona</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(old('estado_entregable', $registro->estado_entregable ?? 'EN PROCESO') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="card-panel">
        <div class="mb-5">
            <h3 class="font-display text-xl font-bold text-slate-900">Observaciones</h3>
            @if ($selectedAnexo)
                <p class="mt-1 text-sm text-slate-500">Trabajando sobre {{ $selectedAnexo->titulo }}.</p>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="form-label" for="observaciones">{{ config('anexo.column_labels.observaciones') }}</label>
                <textarea class="form-textarea" id="observaciones" name="observaciones" rows="5">{{ old('observaciones', $registro->observaciones ?? '') }}</textarea>
            </div>
            <div>
                <label class="form-label" for="comentarios">{{ config('anexo.column_labels.comentarios') }}</label>
                <textarea class="form-textarea" id="comentarios" name="comentarios" rows="5">{{ old('comentarios', $registro->comentarios ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $editing ? route('registros.show', $registro) : route('registros.index') }}" class="btn-secondary">Volver</a>
        <button type="submit" class="btn-primary">{{ $editing ? 'Actualizar registro' : 'Guardar registro' }}</button>
    </div>
</div>

<script>
(function () {
    const url = @json(route('agentes.buscar', [], false));
    const inCodigo = document.getElementById('codigo_osinergmin');
    const inRazon = document.getElementById('razon_social_agente');
    const selAct = document.getElementById('codigo_actividad');
    const lstCodigo = document.getElementById('lista-agentes-codigo');
    const lstRazon = document.getElementById('lista-agentes-razon');
    const estado = document.getElementById('estado-agente');
    if (!inCodigo || !inRazon) return;

    const campos = {
        ag_actividad: a => (a.codigo_actividad ? a.codigo_actividad + ' - ' : '') + (a.actividad || ''),
        ag_codigo: a => a.codigo_osinergmin,
        ag_nro_registro: a => a.nro_registro,
        ag_fecha_emision: a => a.fecha_emision,
        ag_ruc: a => a.ruc,
        ag_razon_social: a => a.razon_social,
        ag_direccion: a => a.direccion,
        ag_ubigeo: a => a.ubigeo,
        ag_placa: a => a.placa,
    };

    let cache = [];
    let timer = null;

    function aviso(texto, color) {
        if (!estado) return;
        estado.textContent = texto;
        estado.className = 'mt-2 text-xs ' + (color || 'text-slate-500');
    }

    function limpiarPanel() {
        Object.keys(campos).forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    }

    function llenarListas(agentes) {
        lstCodigo.innerHTML = '';
        lstRazon.innerHTML = '';
        agentes.forEach(function (a) {
            const o1 = document.createElement('option');
            o1.value = a.codigo_osinergmin;
            o1.label = a.razon_social + ' | ' + (a.codigo_actividad || '') + ' ' + (a.actividad || '');
            lstCodigo.appendChild(o1);
            const o2 = document.createElement('option');
            o2.value = a.razon_social;
            o2.label = a.codigo_osinergmin + ' | RUC ' + (a.ruc || '');
            lstRazon.appendChild(o2);
        });
    }

    function aplicar(a) {
        inCodigo.value = a.codigo_osinergmin;
        inRazon.value = a.razon_social;
        if (selAct && a.codigo_actividad) {
            if (![...selAct.options].some(o => o.value === a.codigo_actividad)) {
                const o = document.createElement('option');
                o.value = a.codigo_actividad;
                o.textContent = a.codigo_actividad + ' - ' + (a.actividad || '');
                selAct.appendChild(o);
            }
            selAct.value = a.codigo_actividad;
        }
        Object.entries(campos).forEach(([id, fn]) => { const el = document.getElementById(id); if (el) el.value = fn(a) || ''; });
        aviso('Agente encontrado: ' + a.razon_social, 'text-emerald-700');
    }

    function buscar(texto, alTerminar) {
        clearTimeout(timer);
        if (texto.trim().length < 2) { aviso(''); return; }
        aviso('Buscando...');
        timer = setTimeout(function () {
            fetch(url + '?q=' + encodeURIComponent(texto.trim()), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(function (agentes) {
                    cache = agentes;
                    llenarListas(agentes);
                    if (!agentes.length) aviso('No se encontró ningún agente con: ' + texto.trim(), 'text-amber-700');
                    else aviso(agentes.length + ' coincidencia(s). Elige una de la lista.');
                    if (alTerminar) alTerminar(agentes);
                })
                .catch(function (e) { aviso('Error al buscar agentes (' + e.message + '). Recarga la página.', 'text-red-700'); });
        }, 250);
    }

    function porCodigo() {
        const v = inCodigo.value.trim();
        const exacto = cache.find(a => a.codigo_osinergmin === v);
        if (exacto) { aplicar(exacto); return; }
        limpiarPanel();
        buscar(v, function (agentes) {
            const e = agentes.find(a => a.codigo_osinergmin === inCodigo.value.trim());
            if (e) aplicar(e);
        });
    }

    inCodigo.addEventListener('input', porCodigo);
    inCodigo.addEventListener('change', porCodigo);

    inRazon.addEventListener('input', function () {
        const v = inRazon.value.trim();
        const exacto = cache.find(a => a.razon_social === v);
        if (exacto) { aplicar(exacto); return; }
        buscar(v);
    });

    if (inCodigo.value.trim() !== '') porCodigo();
})();
</script>
