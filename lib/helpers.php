<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function get_current_lang(?array $source = null): string
{
    $candidate = $source['lang'] ?? $_GET['lang'] ?? $_POST['lang'] ?? 'es';

    return in_array($candidate, ['es', 'ca'], true) ? $candidate : 'es';
}

function tr(string $key, ?string $lang = null): string
{
    $lang = $lang ?? get_current_lang();

    $translations = [
        'es' => [
            'app_title' => 'Presupuestador carpinteria metelica vidres sosa',
            'new' => 'Nuevo',
            'history' => 'Historial',
            'language' => 'Idioma',
            'spanish' => 'Castellano',
            'catalan' => 'Catalan',
            'budget_data' => 'Datos del presupuesto',
            'missing_config' => 'Falta config.php. Copia config.php.example a config.php y completa credenciales de base de datos.',
            'client' => 'Cliente',
            'email' => 'Email',
            'phone' => 'Telefono',
            'quantity' => 'Cantidad',
            'visual_selector' => 'Selector visual',
            'system' => 'Sistema',
            'opening' => 'Apertura',
            'carpentry' => 'Carpinteria',
            'reference' => 'Referencia',
            'trim' => 'Tapajuntas',
            'no_trim' => 'Sin tapajuntas',
            'frame_cut' => 'Corte del marco',
            'straight_cut' => 'Corte recto',
            'mitered_cut' => '45 grados / Perimetral',
            'fixed_always_mitered' => 'Los marcos fijos se dibujan siempre a 45 grados.',
            'tilt_turn_leaf' => 'Hoja oscilobatiente',
            'tilt_turn_leaf_hint' => 'Solo una hoja lleva la funcion oscilobatiente; la otra queda como practicable normal.',
            'tilt_turn_leaf_short' => 'Oscilo',
            'profile_finish' => 'Acabado carpinteria',
            'white' => 'Blanco',
            'matte_black' => 'Negro mate',
            'anthracite' => 'Gris antracita',
            'bronze' => 'Bronce',
            'walnut' => 'Nogal',
            'custom' => 'Personalizado',
            'profile_color' => 'Color del perfil',
            'profile_color_placeholder' => 'Nombre del color o referencia RAL',
            'width_mm' => 'Ancho (mm)',
            'height_mm' => 'Alto (mm)',
            'leaves' => 'Hojas',
            'glass' => 'Vidrio',
            'glass_hint' => 'Introduce las medidas reales del vidrio o usa una sugerencia basada en el hueco y las hojas.',
            'suggest_measures' => 'Sugerir medidas',
            'glass_selector' => 'Selector de vidrio',
            'glass_reference' => 'Composicion o referencia',
            'glass_reference_hint' => 'Puedes sobrescribir la composicion manualmente.',
            'glass_width' => 'Ancho vidrio (mm)',
            'glass_height' => 'Alto vidrio (mm)',
            'pieces_per_unit' => 'Piezas por unidad',
            'glass_price_m2' => 'Precio vidrio €/m²',
            'glass_price_hint' => 'Se rellena automaticamente segun el vidrio. Puedes actualizar tarifas en config.php.',
            'costs_margins' => 'Costes y margenes',
            'pricing_mode' => 'Modo de coste',
            'own_fabrication' => 'Fabricacion propia',
            'purchased_carpentry' => 'Carpinteria comprada',
            'supplier_finished' => 'Carpinteria fabricada por proveedor',
            'purchase_cost_me' => 'Precio coste para mi €/ud',
            'aluminum_price' => 'Precio aluminio €/ml',
            'labor' => 'Mano de obra €',
            'internal_extra_cost' => 'Coste interno extra €',
            'internal_extra_hint' => 'Uso interno: silicona, tubos, remates u otros costes no visibles para el cliente.',
            'margin' => 'Margen %',
            'commercial_margin' => 'Margen comercial %',
            'iva' => 'IVA %',
            'notes' => 'Notas',
            'notes_placeholder' => 'Observaciones del trabajo',
            'save_budget' => 'Guardar presupuesto',
            'technical_preview' => 'Vista previa tecnica',
            'financial_summary' => 'Resumen economico',
            'quote_items' => 'Partidas del presupuesto',
            'quote_items_hint' => 'Cada partida guarda su propia configuracion, dibujo y coste. La vista previa muestra la partida seleccionada.',
            'add_item' => 'Anadir ventana',
            'remove_item' => 'Eliminar ventana',
            'item' => 'Partida',
            'item_count' => 'Partidas',
            'total_units' => 'Unidades totales',
            'selected_item' => 'Partida seleccionada',
            'multiple_system' => 'Varias ventanas',
            'multiple_values' => 'Varios',
            'history_title' => 'Historial de presupuestos',
            'date' => 'Fecha',
            'actions' => 'Acciones',
            'view' => 'Ver',
            'duplicate' => 'Duplicar',
            'print_pdf' => 'Imprimir o PDF',
            'quote' => 'Presupuesto',
            'configuration' => 'Configuracion',
            'measurements' => 'Medidas',
            'profile' => 'Perfil',
            'glass_composition' => 'Composicion vidrio',
            'glass_measure' => 'Medida vidrio',
            'glass_pieces_per_unit' => 'Piezas vidrio por unidad',
            'pricing_mode_label' => 'Modo de coste',
            'supplier' => 'Proveedor',
            'already_finished' => 'Carpinteria ya fabricada',
            'amounts' => 'Importes',
            'subtotal' => 'Subtotal',
            'total' => 'Total',
            'base' => 'Base',
            'glass_summary' => 'Resumen de vidrio',
            'undefined' => 'Sin definir',
            'no_reference' => 'Sin referencia',
            'sqm_per_piece' => 'm² por pieza',
            'sqm_total' => 'm² totales',
            'glass_cost' => 'Coste vidrio',
            'pieces_per_unit_short' => 'pz/ud',
            'pieces_per_unit_text' => 'piezas por unidad',
            'sheet_series' => 'Serie',
            'sheet_color' => 'Color',
            'sheet_trim' => 'Tapajuntas',
            'sheet_cut' => 'Corte',
            'sheet_glass' => 'Vidrio',
            'sheet_size' => 'Medida',
            'trim_none' => 'Sin',
            'cut_recto' => 'Recto',
            'cut_mitered' => '45 grados',
            'units_short' => 'ud.',
            'leaves_short' => 'hojas',
            'custom_color_label' => 'Personalizado',
            'fixed' => 'Fijo',
            'sliding' => 'Corredera',
            'casement' => 'Abatible',
            'tilt_turn' => 'Oscilobatiente',
            'left' => 'Izquierda',
            'right' => 'Derecha',
            'center' => 'Central',
            'only_leaf' => 'Unica',
            'other_carpentry' => 'Otra carpinteria',
            'glass_group_camaras' => 'Camaras',
            'glass_group_laminados' => 'Laminados',
            'glass_group_lowe' => 'Bajo Emisivo',
            'glass_group_solar' => 'Control Solar',
            'glass_group_acoustic' => 'Acusticos',
            'glass_group_tempered' => 'Templados',
            'glass_group_triple' => 'Triples',
            'glass_group_other' => 'Otros',
            'invalid_id' => 'ID invalido',
            'quote_not_found' => 'Presupuesto no encontrado',
            'save_error' => 'Error guardando presupuesto',
            'duplicate_error' => 'Error duplicando presupuesto',
            'required_client' => 'Cliente obligatorio',
        ],
        'ca' => [
            'app_title' => 'Presupuestador carpinteria metelica vidres sosa',
            'new' => 'Nou',
            'history' => 'Historial',
            'language' => 'Idioma',
            'spanish' => 'Castella',
            'catalan' => 'Catala',
            'budget_data' => 'Dades del pressupost',
            'missing_config' => 'Falta config.php. Copia config.php.example a config.php i completa credencials de base de dades.',
            'client' => 'Client',
            'email' => 'Email',
            'phone' => 'Telefon',
            'quantity' => 'Quantitat',
            'visual_selector' => 'Selector visual',
            'system' => 'Sistema',
            'opening' => 'Obertura',
            'carpentry' => 'Fusteria',
            'reference' => 'Referencia',
            'trim' => 'Tapajuntes',
            'no_trim' => 'Sense tapajuntes',
            'frame_cut' => 'Tall del marc',
            'straight_cut' => 'Tall recte',
            'mitered_cut' => '45 graus / Perimetral',
            'fixed_always_mitered' => 'Els marcs fixos es dibuixen sempre a 45 graus.',
            'tilt_turn_leaf' => 'Fulla oscilobatent',
            'tilt_turn_leaf_hint' => 'Nomes una fulla porta la funcio oscilobatent; l\'altra queda com a practicable normal.',
            'tilt_turn_leaf_short' => 'Oscilo',
            'profile_finish' => 'Acabat fusteria',
            'white' => 'Blanc',
            'matte_black' => 'Negre mat',
            'anthracite' => 'Gris antracita',
            'bronze' => 'Bronze',
            'walnut' => 'Noguera',
            'custom' => 'Personalitzat',
            'profile_color' => 'Color del perfil',
            'profile_color_placeholder' => 'Nom del color o referencia RAL',
            'width_mm' => 'Ample (mm)',
            'height_mm' => 'Alt (mm)',
            'leaves' => 'Fulles',
            'glass' => 'Vidre',
            'glass_hint' => 'Introdueix les mides reals del vidre o usa una suggerencia basada en el buit i les fulles.',
            'suggest_measures' => 'Suggerir mides',
            'glass_selector' => 'Selector de vidre',
            'glass_reference' => 'Composicio o referencia',
            'glass_reference_hint' => 'Pots sobreescriure la composicio manualment.',
            'glass_width' => 'Ample vidre (mm)',
            'glass_height' => 'Alt vidre (mm)',
            'pieces_per_unit' => 'Peces per unitat',
            'glass_price_m2' => 'Preu vidre €/m²',
            'glass_price_hint' => 'Semplena automaticament segons el vidre. Pots actualitzar tarifes en config.php.',
            'costs_margins' => 'Costos i marges',
            'pricing_mode' => 'Mode de cost',
            'own_fabrication' => 'Fabricacio propia',
            'purchased_carpentry' => 'Fusteria comprada',
            'supplier_finished' => 'Fusteria fabricada pel proveidor',
            'purchase_cost_me' => 'Preu cost per a mi €/ud',
            'aluminum_price' => 'Preu alumini €/ml',
            'labor' => 'Ma d’obra €',
            'internal_extra_cost' => 'Cost intern extra €',
            'internal_extra_hint' => 'Us intern: silicona, tubs, remats o altres costos no visibles per al client.',
            'margin' => 'Marge %',
            'commercial_margin' => 'Marge comercial %',
            'iva' => 'IVA %',
            'notes' => 'Notes',
            'notes_placeholder' => 'Observacions de la feina',
            'save_budget' => 'Desar pressupost',
            'technical_preview' => 'Vista previa tecnica',
            'financial_summary' => 'Resum economic',
            'quote_items' => 'Partides del pressupost',
            'quote_items_hint' => 'Cada partida guarda la seva configuracio, dibuix i cost. La vista previa mostra la partida seleccionada.',
            'add_item' => 'Afegir finestra',
            'remove_item' => 'Eliminar finestra',
            'item' => 'Partida',
            'item_count' => 'Partides',
            'total_units' => 'Unitats totals',
            'selected_item' => 'Partida seleccionada',
            'multiple_system' => 'Diverses finestres',
            'multiple_values' => 'Diversos',
            'history_title' => 'Historial de pressupostos',
            'date' => 'Data',
            'actions' => 'Accions',
            'view' => 'Veure',
            'duplicate' => 'Duplicar',
            'print_pdf' => 'Imprimir o PDF',
            'quote' => 'Pressupost',
            'configuration' => 'Configuracio',
            'measurements' => 'Mides',
            'profile' => 'Perfil',
            'glass_composition' => 'Composicio vidre',
            'glass_measure' => 'Mida vidre',
            'glass_pieces_per_unit' => 'Peces vidre per unitat',
            'pricing_mode_label' => 'Mode de cost',
            'supplier' => 'Proveidor',
            'already_finished' => 'Fusteria ja fabricada',
            'amounts' => 'Imports',
            'subtotal' => 'Subtotal',
            'total' => 'Total',
            'base' => 'Base',
            'glass_summary' => 'Resum de vidre',
            'undefined' => 'Sense definir',
            'no_reference' => 'Sense referencia',
            'sqm_per_piece' => 'm² per peça',
            'sqm_total' => 'm² totals',
            'glass_cost' => 'Cost vidre',
            'pieces_per_unit_short' => 'pz/u',
            'pieces_per_unit_text' => 'peces per unitat',
            'sheet_series' => 'Serie',
            'sheet_color' => 'Color',
            'sheet_trim' => 'Tapajuntes',
            'sheet_cut' => 'Tall',
            'sheet_glass' => 'Vidre',
            'sheet_size' => 'Mida',
            'trim_none' => 'Sense',
            'cut_recto' => 'Recte',
            'cut_mitered' => '45 graus',
            'units_short' => 'ud.',
            'leaves_short' => 'fulles',
            'custom_color_label' => 'Personalitzat',
            'fixed' => 'Fix',
            'sliding' => 'Corredissa',
            'casement' => 'Abatible',
            'tilt_turn' => 'Oscilobatent',
            'left' => 'Esquerra',
            'right' => 'Dreta',
            'center' => 'Central',
            'only_leaf' => 'Unica',
            'other_carpentry' => 'Una altra fusteria',
            'glass_group_camaras' => 'Cambres',
            'glass_group_laminados' => 'Laminats',
            'glass_group_lowe' => 'Baix emissiu',
            'glass_group_solar' => 'Control solar',
            'glass_group_acoustic' => 'Acustics',
            'glass_group_tempered' => 'Templats',
            'glass_group_triple' => 'Triples',
            'glass_group_other' => 'Altres',
            'invalid_id' => 'ID invalid',
            'quote_not_found' => 'Pressupost no trobat',
            'save_error' => 'Error desant pressupost',
            'duplicate_error' => 'Error duplicant pressupost',
            'required_client' => 'Client obligatori',
        ],
    ];

    return $translations[$lang][$key] ?? $translations['es'][$key] ?? $key;
}

function url_with_lang(string $path, array $params = [], ?string $lang = null): string
{
    $lang = $lang ?? get_current_lang();
    $params['lang'] = $lang;
    $query = http_build_query($params);

    return $path . ($query !== '' ? '?' . $query : '');
}

function generate_quote_number(): string
{
    return 'P-' . date('Ymd-His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

function get_carpentry_options(): array
{
    return [
        'exlabesa' => 'Exlabesa',
        'cortizo' => 'Cortizo',
        'marco_40_40' => 'Marco 40+40',
        'marco_40_20' => 'Marco 40x20',
        'otra' => 'other_carpentry',
    ];
}

function humanize_carpentry_model(string $value): string
{
    $map = get_carpentry_options();
    $label = $map[$value] ?? trim(str_replace('_', ' ', $value));

    return array_key_exists($value, $map) ? tr((string)$label) : $label;
}

function humanize_system_type(string $value, ?string $lang = null): string
{
    $map = [
        'corredera' => 'sliding',
        'abatible' => 'casement',
        'fijo' => 'fixed',
        'oscilobatiente' => 'tilt_turn',
        'multiple' => 'multiple_system',
    ];

    return isset($map[$value]) ? tr($map[$value], $lang) : trim(str_replace('_', ' ', $value));
}

function humanize_opening_type(string $value, ?string $lang = null): string
{
    $map = [
        'izquierda' => 'left',
        'derecha' => 'right',
        'central' => 'center',
        'multiple' => 'multiple_values',
    ];

    return isset($map[$value]) ? tr($map[$value], $lang) : trim(str_replace('_', ' ', $value));
}

function humanize_tilt_turn_leaf(string $value, ?string $lang = null): string
{
    $map = [
        'izquierda' => 'left',
        'derecha' => 'right',
        'unica' => 'only_leaf',
    ];

    return isset($map[$value]) ? tr($map[$value], $lang) : trim(str_replace('_', ' ', $value));
}

function get_default_glass_price_catalog(): array
{
    return [
        'camara_4_12_4' => 38.00,
        'camara_4_16_4' => 40.00,
        'camara_4_4_12_4' => 42.00,
        'camara_4_4_16_4' => 45.00,
        'camara_6_12_6' => 48.00,
        'camara_6_16_6' => 52.00,
        'laminar_3_3' => 34.00,
        'laminar_4_4' => 39.00,
        'laminar_5_5' => 48.00,
        'laminar_6_6' => 56.00,
        'bajo_emisivo_4_16_4' => 49.00,
        'bajo_emisivo_4_4_16_4' => 54.00,
        'bajo_emisivo_6_16_4' => 58.00,
        'control_solar_4_16_4' => 53.00,
        'control_solar_4_4_16_4' => 58.00,
        'control_solar_6_16_6' => 64.00,
        'acustico_4_4_16_4' => 57.00,
        'acustico_5_5_16_6' => 66.00,
        'acustico_6_6_16_6' => 74.00,
        'templado_6' => 36.00,
        'templado_8' => 44.00,
        'templado_10' => 52.00,
        'triple_4_10_4_10_4' => 68.00,
        'triple_4_12_4_12_4' => 74.00,
        'monolitico_4' => 22.00,
        'monolitico_6' => 28.00,
        'otro' => 42.00,
    ];
}

function get_glass_options(): array
{
    return [
        'Camaras' => [
            'camara_4_12_4' => 'Camara 4/12/4',
            'camara_4_16_4' => 'Camara 4/16/4',
            'camara_4_4_12_4' => 'Camara 4+4/12/4',
            'camara_4_4_16_4' => 'Camara 4+4/16/4',
            'camara_6_12_6' => 'Camara 6/12/6',
            'camara_6_16_6' => 'Camara 6/16/6',
        ],
        'Laminados' => [
            'laminar_3_3' => 'Laminar 3+3',
            'laminar_4_4' => 'Laminar 4+4',
            'laminar_5_5' => 'Laminar 5+5',
            'laminar_6_6' => 'Laminar 6+6',
        ],
        'Bajo Emisivo' => [
            'bajo_emisivo_4_16_4' => 'Bajo emisivo 4/16/4',
            'bajo_emisivo_4_4_16_4' => 'Bajo emisivo 4+4/16/4',
            'bajo_emisivo_6_16_4' => 'Bajo emisivo 6/16/4',
        ],
        'Control Solar' => [
            'control_solar_4_16_4' => 'Control solar 4/16/4',
            'control_solar_4_4_16_4' => 'Control solar 4+4/16/4',
            'control_solar_6_16_6' => 'Control solar 6/16/6',
        ],
        'Acusticos' => [
            'acustico_4_4_16_4' => 'Acustico 4+4/16/4',
            'acustico_5_5_16_6' => 'Acustico 5+5/16/6',
            'acustico_6_6_16_6' => 'Acustico 6+6/16/6',
        ],
        'Templados' => [
            'templado_6' => 'Templado 6 mm',
            'templado_8' => 'Templado 8 mm',
            'templado_10' => 'Templado 10 mm',
        ],
        'Triples' => [
            'triple_4_10_4_10_4' => 'Triple 4/10/4/10/4',
            'triple_4_12_4_12_4' => 'Triple 4/12/4/12/4',
        ],
        'Otros' => [
            'monolitico_4' => 'Monolitico 4 mm',
            'monolitico_6' => 'Monolitico 6 mm',
            'otro' => 'Otro vidrio',
        ],
    ];
}

function get_glass_group_translation_key(string $group): string
{
    $map = [
        'Camaras' => 'glass_group_camaras',
        'Laminados' => 'glass_group_laminados',
        'Bajo Emisivo' => 'glass_group_lowe',
        'Control Solar' => 'glass_group_solar',
        'Acusticos' => 'glass_group_acoustic',
        'Templados' => 'glass_group_tempered',
        'Triples' => 'glass_group_triple',
    ];

    return $map[$group] ?? 'glass_group_other';
}

function flatten_glass_options(): array
{
    $flattened = [];

    foreach (get_glass_options() as $group => $options) {
        foreach ($options as $value => $label) {
            $flattened[$value] = $label;
        }
    }

    return $flattened;
}

function humanize_glass_type(string $value): string
{
    $map = flatten_glass_options();

    return $map[$value] ?? trim(str_replace('_', ' ', $value));
}

function build_quote_item_config(array $data, array $calc): array
{
    $pricingMode = (string)($data['pricing_mode'] ?? 'fabricada');
    $commercialMarginPct = max(0.0, (float)($data['commercial_margin_pct'] ?? ($data['margin_pct'] ?? 0)));
    $purchasedUnitCost = max(0.0, (float)($data['purchased_unit_cost'] ?? 0));
    $internalExtraCost = max(0.0, (float)($data['internal_extra_cost'] ?? 0));

    return [
        'system_type' => trim((string)($data['system_type'] ?? 'corredera')),
        'opening_type' => trim((string)($data['opening_type'] ?? 'izquierda')),
        'pricing_mode' => $pricingMode,
        'is_factory_finished' => !empty($data['is_factory_finished']),
        'purchased_unit_cost' => round($purchasedUnitCost, 2),
        'internal_extra_cost' => round($internalExtraCost, 2),
        'commercial_margin_pct' => round($commercialMarginPct, 2),
        'margin_pct' => $calc['margin_pct'],
        'iva_pct' => $calc['iva_pct'],
        'carpentry_model' => humanize_carpentry_model(trim((string)($data['carpentry_model'] ?? ''))),
        'carpentry_reference' => trim((string)($data['carpentry_reference'] ?? '')),
        'trim_size' => max(0, (int)($data['trim_size'] ?? 0)),
        'tilt_turn_leaf' => (string)($calc['tilt_turn_leaf'] ?? ''),
        'tilt_turn_leaf_label' => humanize_tilt_turn_leaf((string)($calc['tilt_turn_leaf'] ?? '')),
        'frame_cut_type' => trim((string)($data['frame_cut_type'] ?? 'recto')),
        'profile_color_hex' => trim((string)($data['profile_color_hex'] ?? '')),
        'profile_color_name' => trim((string)($data['profile_color'] ?? '')),
        'profile_color' => trim((string)($data['profile_color'] ?? '')),
        'width_mm' => $calc['width_mm'],
        'height_mm' => $calc['height_mm'],
        'leaves' => $calc['leaves'],
        'quantity' => $calc['quantity'],
        'aluminum_ml' => $calc['aluminum_ml'],
        'glass_m2' => $calc['glass_m2'],
        'glass_type_label' => humanize_glass_type(trim((string)($data['glass_type'] ?? ''))),
        'glass_type' => trim((string)($data['glass_type'] ?? '')),
        'glass_description' => trim((string)($data['glass_description'] ?? '')),
        'glass_width_mm' => $calc['glass_width_mm'],
        'glass_height_mm' => $calc['glass_height_mm'],
        'glass_panels' => $calc['glass_panels'],
        'glass_piece_area_m2' => $calc['glass_piece_area_m2'],
        'glass_cost' => $calc['glass_cost'],
        'pricing_breakdown' => [
            'base_cost' => $calc['base_cost'],
            'base_cost_label' => $calc['base_cost_label'],
        ],
        'subtotal' => $calc['subtotal'],
        'margin_amount' => $calc['margin_amount'],
        'taxable_base' => $calc['taxable_base'],
        'iva_amount' => $calc['iva_amount'],
        'total' => $calc['total'],
        'drawing_svg' => trim((string)($data['drawing_svg'] ?? '')),
    ];
}

function get_quote_items_from_request(array $data): array
{
    $rawItems = $data['quote_items_json'] ?? null;

    if (is_string($rawItems) && trim($rawItems) !== '') {
        $decoded = json_decode($rawItems, true);
        if (is_array($decoded) && $decoded !== []) {
            return array_values(array_filter($decoded, static fn ($item): bool => is_array($item)));
        }
    }

    return [$data];
}

function calculate_quote_item(array $data): array
{
    $widthMm = max(300, (int)($data['width_mm'] ?? 0));
    $heightMm = max(300, (int)($data['height_mm'] ?? 0));
    $systemType = (string)($data['system_type'] ?? 'corredera');
    $leaves = min(6, max(1, (int)($data['leaves'] ?? 1)));
    if ($systemType === 'fijo') {
        $leaves = 1;
    }
    $tiltTurnLeaf = trim((string)($data['tilt_turn_leaf'] ?? 'izquierda'));
    if ($systemType === 'oscilobatiente') {
        $leaves = min(2, max(1, $leaves));
        $tiltTurnLeaf = $leaves === 1 ? 'unica' : ($tiltTurnLeaf === 'derecha' ? 'derecha' : 'izquierda');
    } else {
        $tiltTurnLeaf = '';
    }
    $quantity = max(1, (int)($data['quantity'] ?? 1));
    $glassWidthMm = max(1, (int)($data['glass_width_mm'] ?? $widthMm));
    $glassHeightMm = max(1, (int)($data['glass_height_mm'] ?? $heightMm));
    $glassPanels = max(1, (int)($data['glass_panels'] ?? $leaves));

    $aluminumPriceMl = max(0.0, (float)($data['aluminum_price_ml'] ?? 0));
    $glassPriceM2 = max(0.0, (float)($data['glass_price_m2'] ?? 0));
    $laborCost = max(0.0, (float)($data['labor_cost'] ?? 0));
    $internalExtraCost = max(0.0, (float)($data['internal_extra_cost'] ?? 0));
    $marginPct = max(0.0, (float)($data['margin_pct'] ?? 0));
    $commercialMarginPct = max(0.0, (float)($data['commercial_margin_pct'] ?? $marginPct));
    $ivaPct = max(0.0, (float)($data['iva_pct'] ?? 0));
    $pricingMode = (string)($data['pricing_mode'] ?? 'fabricada');
    $purchasedUnitCost = max(0.0, (float)($data['purchased_unit_cost'] ?? 0));

    $widthM = $widthMm / 1000;
    $heightM = $heightMm / 1000;

    $frameMl = ($widthM * 2) + ($heightM * 2);
    $leafDividerMl = max(0, $leaves - 1) * $heightM;
    $leafPerimeterMl = $leaves * ((($widthM / $leaves) * 2) + ($heightM * 2));
    $aluminumMl = round(($frameMl + $leafDividerMl + ($leafPerimeterMl * 0.35)) * $quantity, 3);

    $glassPieceAreaM2 = round(($glassWidthMm / 1000) * ($glassHeightMm / 1000), 3);
    $glassM2 = round($glassPieceAreaM2 * $glassPanels * $quantity, 3);

    $aluminumCost = $aluminumMl * $aluminumPriceMl;
    $glassCost = $glassM2 * $glassPriceM2;
    $fabricatedBaseCost = $aluminumCost + $glassCost + $laborCost + $internalExtraCost;
    $purchasedBaseCost = ($purchasedUnitCost * $quantity) + $internalExtraCost;

    $baseCost = $pricingMode === 'comprada' ? $purchasedBaseCost : $fabricatedBaseCost;
    $effectiveMarginPct = $pricingMode === 'comprada' ? $commercialMarginPct : $marginPct;

    $subtotal = round($baseCost, 2);
    $marginAmount = round($subtotal * ($effectiveMarginPct / 100), 2);
    $taxableBase = round($subtotal + $marginAmount, 2);
    $ivaAmount = round($taxableBase * ($ivaPct / 100), 2);
    $total = round($taxableBase + $ivaAmount, 2);

    return [
        'width_mm' => $widthMm,
        'height_mm' => $heightMm,
        'leaves' => $leaves,
        'quantity' => $quantity,
        'glass_width_mm' => $glassWidthMm,
        'glass_height_mm' => $glassHeightMm,
        'glass_panels' => $glassPanels,
        'tilt_turn_leaf' => $tiltTurnLeaf,
        'glass_piece_area_m2' => $glassPieceAreaM2,
        'aluminum_price_ml' => round($aluminumPriceMl, 2),
        'glass_price_m2' => round($glassPriceM2, 2),
        'glass_cost' => round($glassCost, 2),
        'labor_cost' => round($laborCost, 2),
        'internal_extra_cost' => round($internalExtraCost, 2),
        'margin_pct' => round($effectiveMarginPct, 2),
        'commercial_margin_pct' => round($commercialMarginPct, 2),
        'purchased_unit_cost' => round($purchasedUnitCost, 2),
        'pricing_mode' => $pricingMode,
        'iva_pct' => round($ivaPct, 2),
        'aluminum_ml' => $aluminumMl,
        'glass_m2' => $glassM2,
        'base_cost' => round($baseCost, 2),
        'base_cost_label' => $pricingMode === 'comprada' ? 'Coste compra / Cost de compra' : 'Coste fabricación / Cost de fabricació',
        'subtotal' => $subtotal,
        'margin_amount' => $marginAmount,
        'taxable_base' => $taxableBase,
        'iva_amount' => $ivaAmount,
        'total' => $total,
    ];
}

function calculate_quote(array $data): array
{
    $items = get_quote_items_from_request($data);
    $itemCount = count($items);
    $calculatedItems = [];
    $firstItem = $items[0] ?? $data;
    $firstCalc = null;

    $totals = [
        'quantity' => 0,
        'aluminum_ml' => 0.0,
        'glass_m2' => 0.0,
        'base_cost' => 0.0,
        'glass_cost' => 0.0,
        'subtotal' => 0.0,
        'margin_amount' => 0.0,
        'taxable_base' => 0.0,
        'iva_amount' => 0.0,
        'total' => 0.0,
    ];

    foreach ($items as $item) {
        $itemCalc = calculate_quote_item($item);
        $calculatedItems[] = [
            'input' => $item,
            'calc' => $itemCalc,
        ];

        if ($firstCalc === null) {
            $firstCalc = $itemCalc;
        }

        $totals['quantity'] += (int)$itemCalc['quantity'];
        $totals['aluminum_ml'] += (float)$itemCalc['aluminum_ml'];
        $totals['glass_m2'] += (float)$itemCalc['glass_m2'];
        $totals['base_cost'] += (float)$itemCalc['base_cost'];
        $totals['glass_cost'] += (float)$itemCalc['glass_cost'];
        $totals['subtotal'] += (float)$itemCalc['subtotal'];
        $totals['margin_amount'] += (float)$itemCalc['margin_amount'];
        $totals['taxable_base'] += (float)$itemCalc['taxable_base'];
        $totals['iva_amount'] += (float)$itemCalc['iva_amount'];
        $totals['total'] += (float)$itemCalc['total'];
    }

    $firstCalc = $firstCalc ?? calculate_quote_item($firstItem);
    $summaryLabel = $itemCount > 1 ? 'multiple_values' : null;

    return [
        'item_count' => $itemCount,
        'items' => $calculatedItems,
        'system_type' => $itemCount > 1 ? 'multiple' : trim((string)($firstItem['system_type'] ?? 'corredera')),
        'opening_type' => $itemCount > 1 ? 'multiple' : trim((string)($firstItem['opening_type'] ?? 'izquierda')),
        'profile_color' => $itemCount > 1 ? tr('multiple_values') : trim((string)($firstItem['profile_color'] ?? '')),
        'glass_type' => $itemCount > 1 ? tr('multiple_values') : humanize_glass_type(trim((string)($firstItem['glass_type'] ?? ''))),
        'drawing_svg' => trim((string)($firstItem['drawing_svg'] ?? ($data['drawing_svg'] ?? ''))),
        'width_mm' => (int)$firstCalc['width_mm'],
        'height_mm' => (int)$firstCalc['height_mm'],
        'leaves' => (int)$firstCalc['leaves'],
        'quantity' => (int)$totals['quantity'],
        'glass_width_mm' => (int)$firstCalc['glass_width_mm'],
        'glass_height_mm' => (int)$firstCalc['glass_height_mm'],
        'glass_panels' => (int)$firstCalc['glass_panels'],
        'glass_piece_area_m2' => (float)$firstCalc['glass_piece_area_m2'],
        'aluminum_price_ml' => (float)$firstCalc['aluminum_price_ml'],
        'glass_price_m2' => (float)$firstCalc['glass_price_m2'],
        'glass_cost' => round($totals['glass_cost'], 2),
        'labor_cost' => (float)$firstCalc['labor_cost'],
        'internal_extra_cost' => (float)$firstCalc['internal_extra_cost'],
        'margin_pct' => (float)$firstCalc['margin_pct'],
        'commercial_margin_pct' => (float)$firstCalc['commercial_margin_pct'],
        'purchased_unit_cost' => (float)$firstCalc['purchased_unit_cost'],
        'pricing_mode' => (string)$firstCalc['pricing_mode'],
        'iva_pct' => (float)$firstCalc['iva_pct'],
        'aluminum_ml' => round($totals['aluminum_ml'], 3),
        'glass_m2' => round($totals['glass_m2'], 3),
        'base_cost' => round($totals['base_cost'], 2),
        'base_cost_label' => $itemCount > 1 ? tr('quote_items') : (string)$firstCalc['base_cost_label'],
        'subtotal' => round($totals['subtotal'], 2),
        'margin_amount' => round($totals['margin_amount'], 2),
        'taxable_base' => round($totals['taxable_base'], 2),
        'iva_amount' => round($totals['iva_amount'], 2),
        'total' => round($totals['total'], 2),
    ];
}

function build_quote_config(array $data, array $calc): array
{
    $itemsConfig = [];

    foreach ($calc['items'] as $index => $itemBundle) {
        $itemConfig = build_quote_item_config($itemBundle['input'], $itemBundle['calc']);
        $itemConfig['item_number'] = $index + 1;
        $itemsConfig[] = $itemConfig;
    }

    $firstItem = $itemsConfig[0] ?? [];

    return array_merge($firstItem, [
        'item_count' => $calc['item_count'],
        'total_quantity' => $calc['quantity'],
        'items' => $itemsConfig,
        'quote_totals' => [
            'aluminum_ml' => $calc['aluminum_ml'],
            'glass_m2' => $calc['glass_m2'],
            'glass_cost' => $calc['glass_cost'],
            'subtotal' => $calc['subtotal'],
            'margin_amount' => $calc['margin_amount'],
            'taxable_base' => $calc['taxable_base'],
            'iva_amount' => $calc['iva_amount'],
            'total' => $calc['total'],
        ],
    ]);
}