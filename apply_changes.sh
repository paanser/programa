#!/usr/bin/env bash
# Aplica las mejoras del presupuestador a tu clon local o Codespace.
# Uso: bash apply_changes.sh && git push -u origin claude/code-review-improvements-qbzGK

set -euo pipefail

BRANCH="claude/code-review-improvements-qbzGK"
TMPDIR_PATCHES="$(mktemp -d)"
trap 'rm -rf "$TMPDIR_PATCHES"' EXIT

git checkout -b "$BRANCH" 2>/dev/null || git checkout "$BRANCH"
cat > "$TMPDIR_PATCHES/0001.patch" << 'PATCHEOF'
From 13b4bbd75bd9a1dbb80a10e7540337516b908f16 Mon Sep 17 00:00:00 2001
From: Claude <noreply@anthropic.com>
Date: Sat, 9 May 2026 11:47:18 +0000
Subject: [PATCH 1/2] Aplicar mejoras de seguridad, calidad y UX

- diagnose.php: restringir acceso solo a localhost (403 para IPs externas)
- save_quote / view_quote / duplicate_quote: ocultar mensajes de excepcion al cliente; usar error_log internamente
- helpers.php: extraer traducciones a lang/es.php y lang/ca.php; tr() carga los archivos con cache estatica
- index.php: pasar placeholder de carpentry_reference por tr() en lugar de hardcoded en castellano
- list_quotes.php: anadir busqueda por cliente, filtro por sistema y columna de numero de partidas
- assets/styles.css: estilos para el formulario de filtros

https://claude.ai/code/session_01T5pGSS1F6wZBA8fNWNCLpo
---
 assets/styles.css   |  14 +++
 diagnose.php        |   8 ++
 duplicate_quote.php |   3 +-
 index.php           |   2 +-
 lang/ca.php         | 149 ++++++++++++++++++++++
 lang/es.php         | 149 ++++++++++++++++++++++
 lib/helpers.php     | 297 ++------------------------------------------
 list_quotes.php     |  62 ++++++++-
 save_quote.php      |   3 +-
 view_quote.php      |   3 +-
 10 files changed, 399 insertions(+), 291 deletions(-)
 create mode 100644 lang/ca.php
 create mode 100644 lang/es.php

diff --git a/assets/styles.css b/assets/styles.css
index d5784ed..d1591a5 100644
--- a/assets/styles.css
+++ b/assets/styles.css
@@ -474,6 +474,20 @@ p {
     transition: opacity 0.2s ease;
 }
 
+.list-filters {
+    display: flex;
+    gap: 0.5rem;
+    flex-wrap: wrap;
+    align-items: center;
+    margin-bottom: 1.25rem;
+}
+
+.list-filters input[type="search"],
+.list-filters select {
+    flex: 1;
+    min-width: 160px;
+}
+
 .table-wrap {
     overflow-x: auto;
 }
diff --git a/diagnose.php b/diagnose.php
index 68c2687..174d993 100644
--- a/diagnose.php
+++ b/diagnose.php
@@ -2,6 +2,14 @@
 
 declare(strict_types=1);
 
+$allowedIps = ['127.0.0.1', '::1'];
+$remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
+if (!in_array($remoteIp, $allowedIps, true)) {
+    http_response_code(403);
+    echo 'Acceso restringido. Esta pagina solo es accesible desde el servidor local.';
+    exit;
+}
+
 $root = __DIR__;
 $checks = [];
 
diff --git a/duplicate_quote.php b/duplicate_quote.php
index 827e00c..6e25a8f 100644
--- a/duplicate_quote.php
+++ b/duplicate_quote.php
@@ -82,6 +82,7 @@ try {
     header('Location: ' . url_with_lang('view_quote.php', ['id' => $newId], $lang));
     exit;
 } catch (Throwable $e) {
+    error_log('[duplicate_quote] ' . $e->getMessage());
     http_response_code(500);
-    echo h(tr('duplicate_error', $lang)) . ': ' . h($e->getMessage());
+    echo h(tr('duplicate_error', $lang));
 }
diff --git a/index.php b/index.php
index a7d184c..8efdb74 100644
--- a/index.php
+++ b/index.php
@@ -123,7 +123,7 @@ if ($configExists) {
                     </select>
                 </label>
                 <label><?= h(tr('reference', $lang)) ?>
-                    <input type="text" name="carpentry_reference" id="carpentryReference" value="" placeholder="Serie concreta, acabado o referencia">
+                    <input type="text" name="carpentry_reference" id="carpentryReference" value="" placeholder="<?= h(tr('carpentry_reference_placeholder', $lang)) ?>">
                 </label>
                 <label><?= h(tr('trim', $lang)) ?>
                     <select name="trim_size" id="trimSize">
diff --git a/lang/ca.php b/lang/ca.php
new file mode 100644
index 0000000..d0ba095
--- /dev/null
+++ b/lang/ca.php
@@ -0,0 +1,149 @@
+<?php
+
+declare(strict_types=1);
+
+return [
+    'app_title' => 'Presupuestador carpinteria metelica vidres sosa',
+    'new' => 'Nou',
+    'history' => 'Historial',
+    'language' => 'Idioma',
+    'spanish' => 'Castella',
+    'catalan' => 'Catala',
+    'budget_data' => 'Dades del pressupost',
+    'missing_config' => 'Falta config.php. Copia config.php.example a config.php i completa credencials de base de dades.',
+    'client' => 'Client',
+    'email' => 'Email',
+    'phone' => 'Telefon',
+    'quantity' => 'Quantitat',
+    'visual_selector' => 'Selector visual',
+    'system' => 'Sistema',
+    'opening' => 'Obertura',
+    'carpentry' => 'Fusteria',
+    'reference' => 'Referencia',
+    'trim' => 'Tapajuntes',
+    'no_trim' => 'Sense tapajuntes',
+    'frame_cut' => 'Tall del marc',
+    'straight_cut' => 'Tall recte',
+    'mitered_cut' => '45 graus / Perimetral',
+    'fixed_always_mitered' => 'Els marcs fixos es dibuixen sempre a 45 graus.',
+    'tilt_turn_leaf' => 'Fulla oscilobatent',
+    'tilt_turn_leaf_hint' => 'Nomes una fulla porta la funcio oscilobatent; l\'altra queda com a practicable normal.',
+    'tilt_turn_leaf_short' => 'Oscilo',
+    'profile_finish' => 'Acabat fusteria',
+    'white' => 'Blanc',
+    'matte_black' => 'Negre mat',
+    'anthracite' => 'Gris antracita',
+    'bronze' => 'Bronze',
+    'walnut' => 'Noguera',
+    'custom' => 'Personalitzat',
+    'profile_color' => 'Color del perfil',
+    'profile_color_placeholder' => 'Nom del color o referencia RAL',
+    'width_mm' => 'Ample (mm)',
+    'height_mm' => 'Alt (mm)',
+    'leaves' => 'Fulles',
+    'glass' => 'Vidre',
+    'glass_hint' => 'Introdueix les mides reals del vidre o usa una suggerencia basada en el buit i les fulles.',
+    'suggest_measures' => 'Suggerir mides',
+    'glass_selector' => 'Selector de vidre',
+    'glass_reference' => 'Composicio o referencia',
+    'glass_reference_hint' => 'Pots sobreescriure la composicio manualment.',
+    'glass_width' => 'Ample vidre (mm)',
+    'glass_height' => 'Alt vidre (mm)',
+    'pieces_per_unit' => 'Peces per unitat',
+    'glass_price_m2' => 'Preu vidre €/m²',
+    'glass_price_hint' => 'Semplena automaticament segons el vidre. Pots actualitzar tarifes en config.php.',
+    'costs_margins' => 'Costos i marges',
+    'pricing_mode' => 'Mode de cost',
+    'own_fabrication' => 'Fabricacio propia',
+    'purchased_carpentry' => 'Fusteria comprada',
+    'supplier_finished' => 'Fusteria fabricada pel proveidor',
+    'purchase_cost_me' => 'Preu cost per a mi €/ud',
+    'aluminum_price' => 'Preu alumini €/ml',
+    'labor' => 'Ma d\'obra €',
+    'internal_extra_cost' => 'Cost intern extra €',
+    'internal_extra_hint' => 'Us intern: silicona, tubs, remats o altres costos no visibles per al client.',
+    'margin' => 'Marge %',
+    'commercial_margin' => 'Marge comercial %',
+    'iva' => 'IVA %',
+    'notes' => 'Notes',
+    'notes_placeholder' => 'Observacions de la feina',
+    'save_budget' => 'Desar pressupost',
+    'technical_preview' => 'Vista previa tecnica',
+    'financial_summary' => 'Resum economic',
+    'quote_items' => 'Partides del pressupost',
+    'quote_items_hint' => 'Cada partida guarda la seva configuracio, dibuix i cost. La vista previa mostra la partida seleccionada.',
+    'add_item' => 'Afegir finestra',
+    'remove_item' => 'Eliminar finestra',
+    'item' => 'Partida',
+    'item_count' => 'Partides',
+    'total_units' => 'Unitats totals',
+    'selected_item' => 'Partida seleccionada',
+    'multiple_system' => 'Diverses finestres',
+    'multiple_values' => 'Diversos',
+    'history_title' => 'Historial de pressupostos',
+    'date' => 'Data',
+    'actions' => 'Accions',
+    'view' => 'Veure',
+    'duplicate' => 'Duplicar',
+    'print_pdf' => 'Imprimir o PDF',
+    'quote' => 'Pressupost',
+    'configuration' => 'Configuracio',
+    'measurements' => 'Mides',
+    'profile' => 'Perfil',
+    'glass_composition' => 'Composicio vidre',
+    'glass_measure' => 'Mida vidre',
+    'glass_pieces_per_unit' => 'Peces vidre per unitat',
+    'pricing_mode_label' => 'Mode de cost',
+    'supplier' => 'Proveidor',
+    'already_finished' => 'Fusteria ja fabricada',
+    'amounts' => 'Imports',
+    'subtotal' => 'Subtotal',
+    'total' => 'Total',
+    'base' => 'Base',
+    'glass_summary' => 'Resum de vidre',
+    'undefined' => 'Sense definir',
+    'no_reference' => 'Sense referencia',
+    'sqm_per_piece' => 'm² per peça',
+    'sqm_total' => 'm² totals',
+    'glass_cost' => 'Cost vidre',
+    'pieces_per_unit_short' => 'pz/u',
+    'pieces_per_unit_text' => 'peces per unitat',
+    'sheet_series' => 'Serie',
+    'sheet_color' => 'Color',
+    'sheet_trim' => 'Tapajuntes',
+    'sheet_cut' => 'Tall',
+    'sheet_glass' => 'Vidre',
+    'sheet_size' => 'Mida',
+    'trim_none' => 'Sense',
+    'cut_recto' => 'Recte',
+    'cut_mitered' => '45 graus',
+    'units_short' => 'ud.',
+    'leaves_short' => 'fulles',
+    'custom_color_label' => 'Personalitzat',
+    'fixed' => 'Fix',
+    'sliding' => 'Corredissa',
+    'casement' => 'Abatible',
+    'tilt_turn' => 'Oscilobatent',
+    'left' => 'Esquerra',
+    'right' => 'Dreta',
+    'center' => 'Central',
+    'only_leaf' => 'Unica',
+    'other_carpentry' => 'Una altra fusteria',
+    'glass_group_camaras' => 'Cambres',
+    'glass_group_laminados' => 'Laminats',
+    'glass_group_lowe' => 'Baix emissiu',
+    'glass_group_solar' => 'Control solar',
+    'glass_group_acoustic' => 'Acustics',
+    'glass_group_tempered' => 'Templats',
+    'glass_group_triple' => 'Triples',
+    'glass_group_other' => 'Altres',
+    'invalid_id' => 'ID invalid',
+    'quote_not_found' => 'Pressupost no trobat',
+    'save_error' => 'Error desant pressupost',
+    'duplicate_error' => 'Error duplicant pressupost',
+    'required_client' => 'Client obligatori',
+    'search_client' => 'Cercar client...',
+    'filter_system' => 'Tots els sistemes',
+    'no_results' => 'No hi ha pressupostos que coincideixin amb la cerca.',
+    'carpentry_reference_placeholder' => 'Serie concreta, acabat o referencia',
+];
diff --git a/lang/es.php b/lang/es.php
new file mode 100644
index 0000000..0dfccc3
--- /dev/null
+++ b/lang/es.php
@@ -0,0 +1,149 @@
+<?php
+
+declare(strict_types=1);
+
+return [
+    'app_title' => 'Presupuestador carpinteria metelica vidres sosa',
+    'new' => 'Nuevo',
+    'history' => 'Historial',
+    'language' => 'Idioma',
+    'spanish' => 'Castellano',
+    'catalan' => 'Catalan',
+    'budget_data' => 'Datos del presupuesto',
+    'missing_config' => 'Falta config.php. Copia config.php.example a config.php y completa credenciales de base de datos.',
+    'client' => 'Cliente',
+    'email' => 'Email',
+    'phone' => 'Telefono',
+    'quantity' => 'Cantidad',
+    'visual_selector' => 'Selector visual',
+    'system' => 'Sistema',
+    'opening' => 'Apertura',
+    'carpentry' => 'Carpinteria',
+    'reference' => 'Referencia',
+    'trim' => 'Tapajuntas',
+    'no_trim' => 'Sin tapajuntas',
+    'frame_cut' => 'Corte del marco',
+    'straight_cut' => 'Corte recto',
+    'mitered_cut' => '45 grados / Perimetral',
+    'fixed_always_mitered' => 'Los marcos fijos se dibujan siempre a 45 grados.',
+    'tilt_turn_leaf' => 'Hoja oscilobatiente',
+    'tilt_turn_leaf_hint' => 'Solo una hoja lleva la funcion oscilobatiente; la otra queda como practicable normal.',
+    'tilt_turn_leaf_short' => 'Oscilo',
+    'profile_finish' => 'Acabado carpinteria',
+    'white' => 'Blanco',
+    'matte_black' => 'Negro mate',
+    'anthracite' => 'Gris antracita',
+    'bronze' => 'Bronce',
+    'walnut' => 'Nogal',
+    'custom' => 'Personalizado',
+    'profile_color' => 'Color del perfil',
+    'profile_color_placeholder' => 'Nombre del color o referencia RAL',
+    'width_mm' => 'Ancho (mm)',
+    'height_mm' => 'Alto (mm)',
+    'leaves' => 'Hojas',
+    'glass' => 'Vidrio',
+    'glass_hint' => 'Introduce las medidas reales del vidrio o usa una sugerencia basada en el hueco y las hojas.',
+    'suggest_measures' => 'Sugerir medidas',
+    'glass_selector' => 'Selector de vidrio',
+    'glass_reference' => 'Composicion o referencia',
+    'glass_reference_hint' => 'Puedes sobrescribir la composicion manualmente.',
+    'glass_width' => 'Ancho vidrio (mm)',
+    'glass_height' => 'Alto vidrio (mm)',
+    'pieces_per_unit' => 'Piezas por unidad',
+    'glass_price_m2' => 'Precio vidrio €/m²',
+    'glass_price_hint' => 'Se rellena automaticamente segun el vidrio. Puedes actualizar tarifas en config.php.',
+    'costs_margins' => 'Costes y margenes',
+    'pricing_mode' => 'Modo de coste',
+    'own_fabrication' => 'Fabricacion propia',
+    'purchased_carpentry' => 'Carpinteria comprada',
+    'supplier_finished' => 'Carpinteria fabricada por proveedor',
+    'purchase_cost_me' => 'Precio coste para mi €/ud',
+    'aluminum_price' => 'Precio aluminio €/ml',
+    'labor' => 'Mano de obra €',
+    'internal_extra_cost' => 'Coste interno extra €',
+    'internal_extra_hint' => 'Uso interno: silicona, tubos, remates u otros costes no visibles para el cliente.',
+    'margin' => 'Margen %',
+    'commercial_margin' => 'Margen comercial %',
+    'iva' => 'IVA %',
+    'notes' => 'Notas',
+    'notes_placeholder' => 'Observaciones del trabajo',
+    'save_budget' => 'Guardar presupuesto',
+    'technical_preview' => 'Vista previa tecnica',
+    'financial_summary' => 'Resumen economico',
+    'quote_items' => 'Partidas del presupuesto',
+    'quote_items_hint' => 'Cada partida guarda su propia configuracion, dibujo y coste. La vista previa muestra la partida seleccionada.',
+    'add_item' => 'Anadir ventana',
+    'remove_item' => 'Eliminar ventana',
+    'item' => 'Partida',
+    'item_count' => 'Partidas',
+    'total_units' => 'Unidades totales',
+    'selected_item' => 'Partida seleccionada',
+    'multiple_system' => 'Varias ventanas',
+    'multiple_values' => 'Varios',
+    'history_title' => 'Historial de presupuestos',
+    'date' => 'Fecha',
+    'actions' => 'Acciones',
+    'view' => 'Ver',
+    'duplicate' => 'Duplicar',
+    'print_pdf' => 'Imprimir o PDF',
+    'quote' => 'Presupuesto',
+    'configuration' => 'Configuracion',
+    'measurements' => 'Medidas',
+    'profile' => 'Perfil',
+    'glass_composition' => 'Composicion vidrio',
+    'glass_measure' => 'Medida vidrio',
+    'glass_pieces_per_unit' => 'Piezas vidrio por unidad',
+    'pricing_mode_label' => 'Modo de coste',
+    'supplier' => 'Proveedor',
+    'already_finished' => 'Carpinteria ya fabricada',
+    'amounts' => 'Importes',
+    'subtotal' => 'Subtotal',
+    'total' => 'Total',
+    'base' => 'Base',
+    'glass_summary' => 'Resumen de vidrio',
+    'undefined' => 'Sin definir',
+    'no_reference' => 'Sin referencia',
+    'sqm_per_piece' => 'm² por pieza',
+    'sqm_total' => 'm² totales',
+    'glass_cost' => 'Coste vidrio',
+    'pieces_per_unit_short' => 'pz/ud',
+    'pieces_per_unit_text' => 'piezas por unidad',
+    'sheet_series' => 'Serie',
+    'sheet_color' => 'Color',
+    'sheet_trim' => 'Tapajuntas',
+    'sheet_cut' => 'Corte',
+    'sheet_glass' => 'Vidrio',
+    'sheet_size' => 'Medida',
+    'trim_none' => 'Sin',
+    'cut_recto' => 'Recto',
+    'cut_mitered' => '45 grados',
+    'units_short' => 'ud.',
+    'leaves_short' => 'hojas',
+    'custom_color_label' => 'Personalizado',
+    'fixed' => 'Fijo',
+    'sliding' => 'Corredera',
+    'casement' => 'Abatible',
+    'tilt_turn' => 'Oscilobatiente',
+    'left' => 'Izquierda',
+    'right' => 'Derecha',
+    'center' => 'Central',
+    'only_leaf' => 'Unica',
+    'other_carpentry' => 'Otra carpinteria',
+    'glass_group_camaras' => 'Camaras',
+    'glass_group_laminados' => 'Laminados',
+    'glass_group_lowe' => 'Bajo Emisivo',
+    'glass_group_solar' => 'Control Solar',
+    'glass_group_acoustic' => 'Acusticos',
+    'glass_group_tempered' => 'Templados',
+    'glass_group_triple' => 'Triples',
+    'glass_group_other' => 'Otros',
+    'invalid_id' => 'ID invalido',
+    'quote_not_found' => 'Presupuesto no encontrado',
+    'save_error' => 'Error guardando presupuesto',
+    'duplicate_error' => 'Error duplicando presupuesto',
+    'required_client' => 'Cliente obligatorio',
+    'search_client' => 'Buscar cliente...',
+    'filter_system' => 'Todos los sistemas',
+    'no_results' => 'No hay presupuestos que coincidan con la busqueda.',
+    'carpentry_reference_placeholder' => 'Serie concreta, acabado o referencia',
+];
diff --git a/lib/helpers.php b/lib/helpers.php
index 767bc14..1fa5e91 100644
--- a/lib/helpers.php
+++ b/lib/helpers.php
@@ -16,294 +16,21 @@ function get_current_lang(?array $source = null): string
 
 function tr(string $key, ?string $lang = null): string
 {
+    static $cache = [];
+
     $lang = $lang ?? get_current_lang();
 
-    $translations = [
-        'es' => [
-            'app_title' => 'Presupuestador carpinteria metelica vidres sosa',
-            'new' => 'Nuevo',
-            'history' => 'Historial',
-            'language' => 'Idioma',
-            'spanish' => 'Castellano',
-            'catalan' => 'Catalan',
-            'budget_data' => 'Datos del presupuesto',
-            'missing_config' => 'Falta config.php. Copia config.php.example a config.php y completa credenciales de base de datos.',
-            'client' => 'Cliente',
-            'email' => 'Email',
-            'phone' => 'Telefono',
-            'quantity' => 'Cantidad',
-            'visual_selector' => 'Selector visual',
-            'system' => 'Sistema',
-            'opening' => 'Apertura',
-            'carpentry' => 'Carpinteria',
-            'reference' => 'Referencia',
-            'trim' => 'Tapajuntas',
-            'no_trim' => 'Sin tapajuntas',
-            'frame_cut' => 'Corte del marco',
-            'straight_cut' => 'Corte recto',
-            'mitered_cut' => '45 grados / Perimetral',
-            'fixed_always_mitered' => 'Los marcos fijos se dibujan siempre a 45 grados.',
-            'tilt_turn_leaf' => 'Hoja oscilobatiente',
-            'tilt_turn_leaf_hint' => 'Solo una hoja lleva la funcion oscilobatiente; la otra queda como practicable normal.',
-            'tilt_turn_leaf_short' => 'Oscilo',
-            'profile_finish' => 'Acabado carpinteria',
-            'white' => 'Blanco',
-            'matte_black' => 'Negro mate',
-            'anthracite' => 'Gris antracita',
-            'bronze' => 'Bronce',
-            'walnut' => 'Nogal',
-            'custom' => 'Personalizado',
-            'profile_color' => 'Color del perfil',
-            'profile_color_placeholder' => 'Nombre del color o referencia RAL',
-            'width_mm' => 'Ancho (mm)',
-            'height_mm' => 'Alto (mm)',
-            'leaves' => 'Hojas',
-            'glass' => 'Vidrio',
-            'glass_hint' => 'Introduce las medidas reales del vidrio o usa una sugerencia basada en el hueco y las hojas.',
-            'suggest_measures' => 'Sugerir medidas',
-            'glass_selector' => 'Selector de vidrio',
-            'glass_reference' => 'Composicion o referencia',
-            'glass_reference_hint' => 'Puedes sobrescribir la composicion manualmente.',
-            'glass_width' => 'Ancho vidrio (mm)',
-            'glass_height' => 'Alto vidrio (mm)',
-            'pieces_per_unit' => 'Piezas por unidad',
-            'glass_price_m2' => 'Precio vidrio €/m²',
-            'glass_price_hint' => 'Se rellena automaticamente segun el vidrio. Puedes actualizar tarifas en config.php.',
-            'costs_margins' => 'Costes y margenes',
-            'pricing_mode' => 'Modo de coste',
-            'own_fabrication' => 'Fabricacion propia',
-            'purchased_carpentry' => 'Carpinteria comprada',
-            'supplier_finished' => 'Carpinteria fabricada por proveedor',
-            'purchase_cost_me' => 'Precio coste para mi €/ud',
-            'aluminum_price' => 'Precio aluminio €/ml',
-            'labor' => 'Mano de obra €',
-            'internal_extra_cost' => 'Coste interno extra €',
-            'internal_extra_hint' => 'Uso interno: silicona, tubos, remates u otros costes no visibles para el cliente.',
-            'margin' => 'Margen %',
-            'commercial_margin' => 'Margen comercial %',
-            'iva' => 'IVA %',
-            'notes' => 'Notas',
-            'notes_placeholder' => 'Observaciones del trabajo',
-            'save_budget' => 'Guardar presupuesto',
-            'technical_preview' => 'Vista previa tecnica',
-            'financial_summary' => 'Resumen economico',
-            'quote_items' => 'Partidas del presupuesto',
-            'quote_items_hint' => 'Cada partida guarda su propia configuracion, dibujo y coste. La vista previa muestra la partida seleccionada.',
-            'add_item' => 'Anadir ventana',
-            'remove_item' => 'Eliminar ventana',
-            'item' => 'Partida',
-            'item_count' => 'Partidas',
-            'total_units' => 'Unidades totales',
-            'selected_item' => 'Partida seleccionada',
-            'multiple_system' => 'Varias ventanas',
-            'multiple_values' => 'Varios',
-            'history_title' => 'Historial de presupuestos',
-            'date' => 'Fecha',
-            'actions' => 'Acciones',
-            'view' => 'Ver',
-            'duplicate' => 'Duplicar',
-            'print_pdf' => 'Imprimir o PDF',
-            'quote' => 'Presupuesto',
-            'configuration' => 'Configuracion',
-            'measurements' => 'Medidas',
-            'profile' => 'Perfil',
-            'glass_composition' => 'Composicion vidrio',
-            'glass_measure' => 'Medida vidrio',
-            'glass_pieces_per_unit' => 'Piezas vidrio por unidad',
-            'pricing_mode_label' => 'Modo de coste',
-            'supplier' => 'Proveedor',
-            'already_finished' => 'Carpinteria ya fabricada',
-            'amounts' => 'Importes',
-            'subtotal' => 'Subtotal',
-            'total' => 'Total',
-            'base' => 'Base',
-            'glass_summary' => 'Resumen de vidrio',
-            'undefined' => 'Sin definir',
-            'no_reference' => 'Sin referencia',
-            'sqm_per_piece' => 'm² por pieza',
-            'sqm_total' => 'm² totales',
-            'glass_cost' => 'Coste vidrio',
-            'pieces_per_unit_short' => 'pz/ud',
-            'pieces_per_unit_text' => 'piezas por unidad',
-            'sheet_series' => 'Serie',
-            'sheet_color' => 'Color',
-            'sheet_trim' => 'Tapajuntas',
-            'sheet_cut' => 'Corte',
-            'sheet_glass' => 'Vidrio',
-            'sheet_size' => 'Medida',
-            'trim_none' => 'Sin',
-            'cut_recto' => 'Recto',
-            'cut_mitered' => '45 grados',
-            'units_short' => 'ud.',
-            'leaves_short' => 'hojas',
-            'custom_color_label' => 'Personalizado',
-            'fixed' => 'Fijo',
-            'sliding' => 'Corredera',
-            'casement' => 'Abatible',
-            'tilt_turn' => 'Oscilobatiente',
-            'left' => 'Izquierda',
-            'right' => 'Derecha',
-            'center' => 'Central',
-            'only_leaf' => 'Unica',
-            'other_carpentry' => 'Otra carpinteria',
-            'glass_group_camaras' => 'Camaras',
-            'glass_group_laminados' => 'Laminados',
-            'glass_group_lowe' => 'Bajo Emisivo',
-            'glass_group_solar' => 'Control Solar',
-            'glass_group_acoustic' => 'Acusticos',
-            'glass_group_tempered' => 'Templados',
-            'glass_group_triple' => 'Triples',
-            'glass_group_other' => 'Otros',
-            'invalid_id' => 'ID invalido',
-            'quote_not_found' => 'Presupuesto no encontrado',
-            'save_error' => 'Error guardando presupuesto',
-            'duplicate_error' => 'Error duplicando presupuesto',
-            'required_client' => 'Cliente obligatorio',
-        ],
-        'ca' => [
-            'app_title' => 'Presupuestador carpinteria metelica vidres sosa',
-            'new' => 'Nou',
-            'history' => 'Historial',
-            'language' => 'Idioma',
-            'spanish' => 'Castella',
-            'catalan' => 'Catala',
-            'budget_data' => 'Dades del pressupost',
-            'missing_config' => 'Falta config.php. Copia config.php.example a config.php i completa credencials de base de dades.',
-            'client' => 'Client',
-            'email' => 'Email',
-            'phone' => 'Telefon',
-            'quantity' => 'Quantitat',
-            'visual_selector' => 'Selector visual',
-            'system' => 'Sistema',
-            'opening' => 'Obertura',
-            'carpentry' => 'Fusteria',
-            'reference' => 'Referencia',
-            'trim' => 'Tapajuntes',
-            'no_trim' => 'Sense tapajuntes',
-            'frame_cut' => 'Tall del marc',
-            'straight_cut' => 'Tall recte',
-            'mitered_cut' => '45 graus / Perimetral',
-            'fixed_always_mitered' => 'Els marcs fixos es dibuixen sempre a 45 graus.',
-            'tilt_turn_leaf' => 'Fulla oscilobatent',
-            'tilt_turn_leaf_hint' => 'Nomes una fulla porta la funcio oscilobatent; l\'altra queda com a practicable normal.',
-            'tilt_turn_leaf_short' => 'Oscilo',
-            'profile_finish' => 'Acabat fusteria',
-            'white' => 'Blanc',
-            'matte_black' => 'Negre mat',
-            'anthracite' => 'Gris antracita',
-            'bronze' => 'Bronze',
-            'walnut' => 'Noguera',
-            'custom' => 'Personalitzat',
-            'profile_color' => 'Color del perfil',
-            'profile_color_placeholder' => 'Nom del color o referencia RAL',
-            'width_mm' => 'Ample (mm)',
-            'height_mm' => 'Alt (mm)',
-            'leaves' => 'Fulles',
-            'glass' => 'Vidre',
-            'glass_hint' => 'Introdueix les mides reals del vidre o usa una suggerencia basada en el buit i les fulles.',
-            'suggest_measures' => 'Suggerir mides',
-            'glass_selector' => 'Selector de vidre',
-            'glass_reference' => 'Composicio o referencia',
-            'glass_reference_hint' => 'Pots sobreescriure la composicio manualment.',
-            'glass_width' => 'Ample vidre (mm)',
-            'glass_height' => 'Alt vidre (mm)',
-            'pieces_per_unit' => 'Peces per unitat',
-            'glass_price_m2' => 'Preu vidre €/m²',
-            'glass_price_hint' => 'Semplena automaticament segons el vidre. Pots actualitzar tarifes en config.php.',
-            'costs_margins' => 'Costos i marges',
-            'pricing_mode' => 'Mode de cost',
-            'own_fabrication' => 'Fabricacio propia',
-            'purchased_carpentry' => 'Fusteria comprada',
-            'supplier_finished' => 'Fusteria fabricada pel proveidor',
-            'purchase_cost_me' => 'Preu cost per a mi €/ud',
-            'aluminum_price' => 'Preu alumini €/ml',
-            'labor' => 'Ma d’obra €',
-            'internal_extra_cost' => 'Cost intern extra €',
-            'internal_extra_hint' => 'Us intern: silicona, tubs, remats o altres costos no visibles per al client.',
-            'margin' => 'Marge %',
-            'commercial_margin' => 'Marge comercial %',
-            'iva' => 'IVA %',
-            'notes' => 'Notes',
-            'notes_placeholder' => 'Observacions de la feina',
-            'save_budget' => 'Desar pressupost',
-            'technical_preview' => 'Vista previa tecnica',
-            'financial_summary' => 'Resum economic',
-            'quote_items' => 'Partides del pressupost',
-            'quote_items_hint' => 'Cada partida guarda la seva configuracio, dibuix i cost. La vista previa mostra la partida seleccionada.',
-            'add_item' => 'Afegir finestra',
-            'remove_item' => 'Eliminar finestra',
-            'item' => 'Partida',
-            'item_count' => 'Partides',
-            'total_units' => 'Unitats totals',
-            'selected_item' => 'Partida seleccionada',
-            'multiple_system' => 'Diverses finestres',
-            'multiple_values' => 'Diversos',
-            'history_title' => 'Historial de pressupostos',
-            'date' => 'Data',
-            'actions' => 'Accions',
-            'view' => 'Veure',
-            'duplicate' => 'Duplicar',
-            'print_pdf' => 'Imprimir o PDF',
-            'quote' => 'Pressupost',
-            'configuration' => 'Configuracio',
-            'measurements' => 'Mides',
-            'profile' => 'Perfil',
-            'glass_composition' => 'Composicio vidre',
-            'glass_measure' => 'Mida vidre',
-            'glass_pieces_per_unit' => 'Peces vidre per unitat',
-            'pricing_mode_label' => 'Mode de cost',
-            'supplier' => 'Proveidor',
-            'already_finished' => 'Fusteria ja fabricada',
-            'amounts' => 'Imports',
-            'subtotal' => 'Subtotal',
-            'total' => 'Total',
-            'base' => 'Base',
-            'glass_summary' => 'Resum de vidre',
-            'undefined' => 'Sense definir',
-            'no_reference' => 'Sense referencia',
-            'sqm_per_piece' => 'm² per peça',
-            'sqm_total' => 'm² totals',
-            'glass_cost' => 'Cost vidre',
-            'pieces_per_unit_short' => 'pz/u',
-            'pieces_per_unit_text' => 'peces per unitat',
-            'sheet_series' => 'Serie',
-            'sheet_color' => 'Color',
-            'sheet_trim' => 'Tapajuntes',
-            'sheet_cut' => 'Tall',
-            'sheet_glass' => 'Vidre',
-            'sheet_size' => 'Mida',
-            'trim_none' => 'Sense',
-            'cut_recto' => 'Recte',
-            'cut_mitered' => '45 graus',
-            'units_short' => 'ud.',
-            'leaves_short' => 'fulles',
-            'custom_color_label' => 'Personalitzat',
-            'fixed' => 'Fix',
-            'sliding' => 'Corredissa',
-            'casement' => 'Abatible',
-            'tilt_turn' => 'Oscilobatent',
-            'left' => 'Esquerra',
-            'right' => 'Dreta',
-            'center' => 'Central',
-            'only_leaf' => 'Unica',
-            'other_carpentry' => 'Una altra fusteria',
-            'glass_group_camaras' => 'Cambres',
-            'glass_group_laminados' => 'Laminats',
-            'glass_group_lowe' => 'Baix emissiu',
-            'glass_group_solar' => 'Control solar',
-            'glass_group_acoustic' => 'Acustics',
-            'glass_group_tempered' => 'Templats',
-            'glass_group_triple' => 'Triples',
-            'glass_group_other' => 'Altres',
-            'invalid_id' => 'ID invalid',
-            'quote_not_found' => 'Pressupost no trobat',
-            'save_error' => 'Error desant pressupost',
-            'duplicate_error' => 'Error duplicant pressupost',
-            'required_client' => 'Client obligatori',
-        ],
-    ];
+    if (!isset($cache[$lang])) {
+        $path = __DIR__ . ‘/../lang/’ . $lang . ‘.php’;
+        $cache[$lang] = file_exists($path) ? (require $path) : [];
+    }
+
+    if (!isset($cache[‘es’])) {
+        $path = __DIR__ . ‘/../lang/es.php’;
+        $cache[‘es’] = file_exists($path) ? (require $path) : [];
+    }
 
-    return $translations[$lang][$key] ?? $translations['es'][$key] ?? $key;
+    return (string)($cache[$lang][$key] ?? $cache[‘es’][$key] ?? $key);
 }
 
 function url_with_lang(string $path, array $params = [], ?string $lang = null): string
diff --git a/list_quotes.php b/list_quotes.php
index 094c97c..cb66cb5 100644
--- a/list_quotes.php
+++ b/list_quotes.php
@@ -9,12 +9,44 @@ $lang = get_current_lang();
 $error = null;
 $rows = [];
 
+$search = trim((string)($_GET['q'] ?? ''));
+$filterSystem = trim((string)($_GET['system'] ?? ''));
+
 try {
     $pdo = get_pdo();
-    $rows = $pdo->query('SELECT id, quote_number, created_at, client_name, system_type, total FROM quotes ORDER BY id DESC LIMIT 200')->fetchAll();
+
+    $conditions = [];
+    $params = [];
+
+    if ($search !== '') {
+        $conditions[] = 'client_name LIKE :search';
+        $params[':search'] = '%' . $search . '%';
+    }
+
+    if ($filterSystem !== '') {
+        $conditions[] = 'system_type = :system_type';
+        $params[':system_type'] = $filterSystem;
+    }
+
+    $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';
+    $sql = 'SELECT id, quote_number, created_at, client_name, system_type, total, config_json
+            FROM quotes ' . $where . ' ORDER BY id DESC LIMIT 200';
+
+    $stmt = $pdo->prepare($sql);
+    $stmt->execute($params);
+    $rows = $stmt->fetchAll();
 } catch (Throwable $e) {
-    $error = $e->getMessage();
+    error_log('[list_quotes] ' . $e->getMessage());
+    $error = tr('save_error', $lang);
 }
+
+$systemOptions = [
+    'corredera' => tr('sliding', $lang),
+    'abatible' => tr('casement', $lang),
+    'fijo' => tr('fixed', $lang),
+    'oscilobatiente' => tr('tilt_turn', $lang),
+    'multiple' => tr('multiple_system', $lang),
+];
 ?>
 <!doctype html>
 <html lang="<?= h($lang) ?>">
@@ -47,7 +79,26 @@ try {
         <?php if ($error): ?>
             <div class="alert">Error: <?= h($error) ?></div>
         <?php endif; ?>
+
+        <form method="get" action="list_quotes.php" class="list-filters">
+            <input type="hidden" name="lang" value="<?= h($lang) ?>">
+            <input type="search" name="q" value="<?= h($search) ?>" placeholder="<?= h(tr('search_client', $lang)) ?>">
+            <select name="system">
+                <option value=""><?= h(tr('filter_system', $lang)) ?></option>
+                <?php foreach ($systemOptions as $systemValue => $systemLabel): ?>
+                    <option value="<?= h($systemValue) ?>" <?= $filterSystem === $systemValue ? 'selected' : '' ?>><?= h($systemLabel) ?></option>
+                <?php endforeach; ?>
+            </select>
+            <button type="submit"><?= h(tr('view', $lang)) ?></button>
+            <?php if ($search !== '' || $filterSystem !== ''): ?>
+                <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>" class="secondary-button">&times;</a>
+            <?php endif; ?>
+        </form>
+
         <div class="table-wrap">
+            <?php if ($rows === []): ?>
+                <p><?= h(tr('no_results', $lang)) ?></p>
+            <?php else: ?>
             <table>
                 <thead>
                 <tr>
@@ -56,18 +107,24 @@ try {
                     <th><?= h(tr('date', $lang)) ?></th>
                     <th><?= h(tr('client', $lang)) ?></th>
                     <th><?= h(tr('system', $lang)) ?></th>
+                    <th><?= h(tr('item_count', $lang)) ?></th>
                     <th><?= h(tr('total', $lang)) ?></th>
                     <th><?= h(tr('actions', $lang)) ?></th>
                 </tr>
                 </thead>
                 <tbody>
                 <?php foreach ($rows as $row): ?>
+                    <?php
+                    $decodedConfig = json_decode((string)($row['config_json'] ?? '{}'), true);
+                    $itemCount = is_array($decodedConfig) ? (int)($decodedConfig['item_count'] ?? 1) : 1;
+                    ?>
                     <tr>
                         <td><?= (int)$row['id'] ?></td>
                         <td><?= h((string)$row['quote_number']) ?></td>
                         <td><?= h((string)$row['created_at']) ?></td>
                         <td><?= h((string)$row['client_name']) ?></td>
                         <td><?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></td>
+                        <td><?= $itemCount ?></td>
                         <td><?= number_format((float)$row['total'], 2, ',', '.') ?> EUR</td>
                         <td>
                             <a href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('view', $lang)) ?></a>
@@ -78,6 +135,7 @@ try {
                 <?php endforeach; ?>
                 </tbody>
             </table>
+            <?php endif; ?>
         </div>
     </section>
 </main>
diff --git a/save_quote.php b/save_quote.php
index aaf6f53..552a518 100644
--- a/save_quote.php
+++ b/save_quote.php
@@ -79,6 +79,7 @@ try {
     header('Location: ' . url_with_lang('view_quote.php', ['id' => $id], $lang));
     exit;
 } catch (Throwable $e) {
+    error_log('[save_quote] ' . $e->getMessage());
     http_response_code(500);
-    echo h(tr('save_error', $lang)) . ': ' . h($e->getMessage());
+    echo h(tr('save_error', $lang));
 }
diff --git a/view_quote.php b/view_quote.php
index e79fd1a..f0d1bac 100644
--- a/view_quote.php
+++ b/view_quote.php
@@ -34,8 +34,9 @@ try {
     $items = is_array($config['items'] ?? null) ? $config['items'] : [];
     $quoteTotals = is_array($config['quote_totals'] ?? null) ? $config['quote_totals'] : [];
 } catch (Throwable $e) {
+    error_log('[view_quote] ' . $e->getMessage());
     http_response_code(500);
-    echo 'Error: ' . h($e->getMessage());
+    echo h(tr('save_error', $lang));
     exit;
 }
 ?>
-- 
2.43.0

PATCHEOF
cat > "$TMPDIR_PATCHES/0002.patch" << 'PATCHEOF'
From cac19c9cffa49e35841807a8ff8c6f8c62bdcd49 Mon Sep 17 00:00:00 2001
From: Claude <noreply@anthropic.com>
Date: Sat, 9 May 2026 11:59:46 +0000
Subject: [PATCH 2/2] Ampliar presupuestador: estado, herrajes, instalacion,
 estadisticas, eliminar

Nuevas funcionalidades:
- Estado del presupuesto (borrador/enviado/aceptado/rechazado/pedido) con badge visual
- Cambio de estado desde la lista y desde la vista de presupuesto
- Fecha de validez con deteccion automatica de presupuestos caducados
- Campos de coste de herrajes e instalacion por unidad (incluidos en el calculo)
- Panel de estadisticas en el historial (total, importe, aceptados, pendientes)
- Eliminar presupuesto con confirmacion (delete_quote.php)
- Filtro por estado en el historial
- update_status.php para cambiar estado via POST
- database_migration_v2.sql para actualizar instalaciones existentes
- database.sql actualizado con las nuevas columnas
- Catalogo de series de carpinteria simplificado y extensible
- CSS: badges de estado, tarjetas de estadistica, boton eliminar, barra de estado

https://claude.ai/code/session_01T5pGSS1F6wZBA8fNWNCLpo
---
 assets/styles.css         | 140 +++++++++++++++++++++++++++++++++++++-
 database.sql              |   9 ++-
 database_migration_v2.sql |  12 ++++
 delete_quote.php          |  33 +++++++++
 index.php                 |  13 ++++
 lang/ca.php               |  27 ++++++++
 lang/es.php               |  27 ++++++++
 lib/helpers.php           |  49 +++++++++++--
 list_quotes.php           | 103 ++++++++++++++++++++++++----
 save_quote.php            |  15 ++--
 update_status.php         |  41 +++++++++++
 view_quote.php            |  40 +++++++++--
 12 files changed, 473 insertions(+), 36 deletions(-)
 create mode 100644 database_migration_v2.sql
 create mode 100644 delete_quote.php
 create mode 100644 update_status.php

diff --git a/assets/styles.css b/assets/styles.css
index d1591a5..decc71d 100644
--- a/assets/styles.css
+++ b/assets/styles.css
@@ -474,6 +474,137 @@ p {
     transition: opacity 0.2s ease;
 }
 
+/* ---- Stats grid ---- */
+.stats-grid {
+    display: grid;
+    grid-template-columns: repeat(4, minmax(0, 1fr));
+    gap: 0.75rem;
+    margin-bottom: 1.5rem;
+}
+
+.stat-card {
+    display: flex;
+    flex-direction: column;
+    align-items: center;
+    padding: 1rem;
+    border-radius: 10px;
+    background: rgba(255, 255, 255, 0.78);
+    border: 1px solid var(--panel-border);
+    text-align: center;
+}
+
+.stat-card--accepted {
+    background: rgba(31, 122, 69, 0.08);
+    border-color: rgba(31, 122, 69, 0.2);
+}
+
+.stat-card--pending {
+    background: rgba(159, 79, 47, 0.08);
+    border-color: rgba(159, 79, 47, 0.2);
+}
+
+.stat-value {
+    font-size: 1.5rem;
+    font-weight: 700;
+    color: var(--text);
+}
+
+.stat-label {
+    font-size: 0.8rem;
+    color: var(--muted);
+    margin-top: 0.2rem;
+}
+
+/* ---- Status badges ---- */
+.status-badge {
+    display: inline-block;
+    padding: 0.3rem 0.7rem;
+    border-radius: 999px;
+    font-size: 0.8rem;
+    font-weight: 600;
+}
+
+.status-badge--draft    { background: rgba(88,101,114,0.12); color: #3d4a55; }
+.status-badge--sent     { background: rgba(63,124,133,0.14); color: #1d5f69; }
+.status-badge--accepted { background: rgba(31,122,69,0.14);  color: #155a30; }
+.status-badge--rejected { background: rgba(163,58,42,0.14);  color: #7a2918; }
+.status-badge--ordered  { background: rgba(100,74,145,0.14); color: #4a2a7a; }
+
+.status-select {
+    margin-top: 0;
+    padding: 0.3rem 0.5rem;
+    font-size: 0.82rem;
+    border-radius: 6px;
+    width: auto;
+    min-width: 110px;
+}
+
+.status-select.status-badge--accepted { border-color: rgba(31,122,69,0.35); }
+.status-select.status-badge--rejected { border-color: rgba(163,58,42,0.35); }
+.status-select.status-badge--sent     { border-color: rgba(63,124,133,0.35); }
+.status-select.status-badge--ordered  { border-color: rgba(100,74,145,0.35); }
+
+.status-bar {
+    display: flex;
+    align-items: center;
+    gap: 0.75rem;
+    flex-wrap: wrap;
+    margin-bottom: 1.25rem;
+    padding: 0.75rem 1rem;
+    border-radius: 8px;
+    background: rgba(255,255,255,0.72);
+    border: 1px solid var(--panel-border);
+}
+
+.valid-until-label {
+    font-size: 0.86rem;
+    color: var(--muted);
+}
+
+.expired-badge {
+    display: inline-block;
+    padding: 0.2rem 0.55rem;
+    border-radius: 999px;
+    font-size: 0.75rem;
+    font-weight: 600;
+    background: rgba(163,58,42,0.12);
+    color: #7a2918;
+}
+
+/* ---- Alert success ---- */
+.alert--success {
+    background: rgba(31,122,69,0.1);
+    color: #155a30;
+}
+
+/* ---- Inline form / table actions ---- */
+.inline-form {
+    display: inline;
+}
+
+.actions-cell {
+    display: flex;
+    align-items: center;
+    gap: 0.5rem;
+    flex-wrap: wrap;
+}
+
+.delete-button {
+    padding: 0.3rem 0.65rem;
+    font-size: 0.82rem;
+    background: rgba(163,58,42,0.1);
+    color: #7a2918;
+    border: 1px solid rgba(163,58,42,0.2);
+    border-radius: 6px;
+    box-shadow: none;
+    cursor: pointer;
+}
+
+.delete-button:hover {
+    background: rgba(163,58,42,0.18);
+}
+
+/* ---- List filters ---- */
 .list-filters {
     display: flex;
     gap: 0.5rem;
@@ -532,8 +663,13 @@ td {
 @media (max-width: 920px) {
     .layout,
     .grid.two,
-    .glass-summary-grid {
-        grid-template-columns: 1fr;
+    .glass-summary-grid,
+    .stats-grid {
+        grid-template-columns: repeat(2, 1fr);
+    }
+
+    .stats-grid {
+        grid-template-columns: repeat(2, 1fr);
     }
 
     .color-input-row {
diff --git a/database.sql b/database.sql
index 9ff34e0..133d7b3 100644
--- a/database.sql
+++ b/database.sql
@@ -18,7 +18,9 @@ CREATE TABLE IF NOT EXISTS quotes (
     quantity INT UNSIGNED NOT NULL DEFAULT 1,
     aluminum_price_ml DECIMAL(10,2) NOT NULL,
     glass_price_m2 DECIMAL(10,2) NOT NULL,
-    labor_cost DECIMAL(10,2) NOT NULL,
+    labor_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
+    hardware_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
+    installation_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
     margin_pct DECIMAL(5,2) NOT NULL,
     iva_pct DECIMAL(5,2) NOT NULL,
     aluminum_ml DECIMAL(10,3) NOT NULL,
@@ -31,6 +33,9 @@ CREATE TABLE IF NOT EXISTS quotes (
     drawing_svg MEDIUMTEXT,
     config_json JSON,
     notes TEXT,
+    status VARCHAR(30) NOT NULL DEFAULT 'draft',
+    valid_until DATE DEFAULT NULL,
     INDEX idx_created_at (created_at),
-    INDEX idx_client_name (client_name)
+    INDEX idx_client_name (client_name),
+    INDEX idx_status (status)
 ) ENGINE=InnoDB;
diff --git a/database_migration_v2.sql b/database_migration_v2.sql
new file mode 100644
index 0000000..990b2b1
--- /dev/null
+++ b/database_migration_v2.sql
@@ -0,0 +1,12 @@
+-- Migracion v2: estado, validez, herrajes, instalacion
+-- Ejecutar sobre instalaciones existentes que ya tienen la tabla quotes
+
+ALTER TABLE quotes
+    ADD COLUMN IF NOT EXISTS status        VARCHAR(30)    NOT NULL DEFAULT 'draft'  AFTER notes,
+    ADD COLUMN IF NOT EXISTS valid_until   DATE           DEFAULT NULL               AFTER status,
+    ADD COLUMN IF NOT EXISTS hardware_cost DECIMAL(10,2)  NOT NULL DEFAULT 0.00      AFTER labor_cost,
+    ADD COLUMN IF NOT EXISTS installation_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00   AFTER hardware_cost;
+
+-- Indices utiles para filtrar por estado
+ALTER TABLE quotes
+    ADD INDEX IF NOT EXISTS idx_status (status);
diff --git a/delete_quote.php b/delete_quote.php
new file mode 100644
index 0000000..e79dbaf
--- /dev/null
+++ b/delete_quote.php
@@ -0,0 +1,33 @@
+<?php
+
+declare(strict_types=1);
+
+require_once __DIR__ . '/lib/db.php';
+require_once __DIR__ . '/lib/helpers.php';
+
+$lang = get_current_lang();
+
+if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
+    header('Location: ' . url_with_lang('list_quotes.php', [], $lang));
+    exit;
+}
+
+$id = (int)($_POST['id'] ?? 0);
+if ($id <= 0) {
+    http_response_code(400);
+    echo h(tr('invalid_id', $lang));
+    exit;
+}
+
+try {
+    $pdo = get_pdo();
+    $stmt = $pdo->prepare('DELETE FROM quotes WHERE id = :id');
+    $stmt->execute([':id' => $id]);
+
+    header('Location: ' . url_with_lang('list_quotes.php', ['deleted' => '1'], $lang));
+    exit;
+} catch (Throwable $e) {
+    error_log('[delete_quote] ' . $e->getMessage());
+    http_response_code(500);
+    echo h(tr('delete_error', $lang));
+}
diff --git a/index.php b/index.php
index 8efdb74..00b03b9 100644
--- a/index.php
+++ b/index.php
@@ -231,6 +231,14 @@ if ($configExists) {
                 <label class="fabricated-cost-field"><?= h(tr('labor', $lang)) ?>
                     <input type="number" name="labor_cost" id="labor" min="0" step="0.01" value="65.00" required>
                 </label>
+                <label><?= h(tr('hardware_cost', $lang)) ?>
+                    <input type="number" name="hardware_cost" id="hardwareCost" min="0" step="0.01" value="0.00">
+                    <span class="field-hint"><?= h(tr('hardware_hint', $lang)) ?></span>
+                </label>
+                <label><?= h(tr('installation_cost', $lang)) ?>
+                    <input type="number" name="installation_cost" id="installationCost" min="0" step="0.01" value="0.00">
+                    <span class="field-hint"><?= h(tr('installation_hint', $lang)) ?></span>
+                </label>
                 <label><?= h(tr('internal_extra_cost', $lang)) ?>
                     <input type="number" name="internal_extra_cost" id="internalExtraCost" min="0" step="0.01" value="0.00">
                     <span class="field-hint"><?= h(tr('internal_extra_hint', $lang)) ?></span>
@@ -246,6 +254,11 @@ if ($configExists) {
                 </label>
             </div>
 
+            <label><?= h(tr('valid_until', $lang)) ?>
+                <input type="date" name="valid_until" id="validUntil">
+                <span class="field-hint"><?= h(tr('valid_until_hint', $lang)) ?></span>
+            </label>
+
             <label><?= h(tr('notes', $lang)) ?>
                 <textarea name="notes" rows="3" placeholder="<?= h(tr('notes_placeholder', $lang)) ?>"></textarea>
             </label>
diff --git a/lang/ca.php b/lang/ca.php
index d0ba095..f451783 100644
--- a/lang/ca.php
+++ b/lang/ca.php
@@ -146,4 +146,31 @@ return [
     'filter_system' => 'Tots els sistemes',
     'no_results' => 'No hi ha pressupostos que coincideixin amb la cerca.',
     'carpentry_reference_placeholder' => 'Serie concreta, acabat o referencia',
+    // Estat
+    'status' => 'Estat',
+    'status_all' => 'Tots els estats',
+    'status_draft' => 'Esborrany',
+    'status_sent' => 'Enviat',
+    'status_accepted' => 'Acceptat',
+    'status_rejected' => 'Rebutjat',
+    'status_ordered' => 'Demanat',
+    'valid_until' => 'Valid fins',
+    'valid_until_hint' => 'Data de validesa del pressupost.',
+    // Ferratges i installacio
+    'hardware_cost' => 'Ferratges €/ud',
+    'hardware_hint' => 'Cost de ferratges per unitat: maneta, frontisses, tancament, burlet...',
+    'installation_cost' => 'Installacio €/ud',
+    'installation_hint' => 'Cost de ma d\'obra d\'installacio per unitat.',
+    // Accions
+    'delete' => 'Eliminar',
+    'delete_confirm' => 'Estas segur que vols eliminar aquest pressupost? Aquesta accio no es pot desfer.',
+    'delete_error' => 'Error eliminant pressupost',
+    'delete_success' => 'Pressupost eliminat correctament',
+    'change_status' => 'Canviar estat',
+    // Estadistiques
+    'stats_title' => 'Resum',
+    'stats_total' => 'Total pressupostos',
+    'stats_amount' => 'Import total',
+    'stats_accepted' => 'Acceptats',
+    'stats_pending' => 'Pendents',
 ];
diff --git a/lang/es.php b/lang/es.php
index 0dfccc3..9361530 100644
--- a/lang/es.php
+++ b/lang/es.php
@@ -146,4 +146,31 @@ return [
     'filter_system' => 'Todos los sistemas',
     'no_results' => 'No hay presupuestos que coincidan con la busqueda.',
     'carpentry_reference_placeholder' => 'Serie concreta, acabado o referencia',
+    // Estado
+    'status' => 'Estado',
+    'status_all' => 'Todos los estados',
+    'status_draft' => 'Borrador',
+    'status_sent' => 'Enviado',
+    'status_accepted' => 'Aceptado',
+    'status_rejected' => 'Rechazado',
+    'status_ordered' => 'Pedido',
+    'valid_until' => 'Valido hasta',
+    'valid_until_hint' => 'Fecha de validez del presupuesto.',
+    // Herrajes e instalacion
+    'hardware_cost' => 'Herrajes €/ud',
+    'hardware_hint' => 'Coste de herrajes por unidad: manilla, bisagras, cierre, burlete...',
+    'installation_cost' => 'Instalacion €/ud',
+    'installation_hint' => 'Coste de mano de obra de instalacion por unidad.',
+    // Acciones
+    'delete' => 'Eliminar',
+    'delete_confirm' => 'Estas seguro de que quieres eliminar este presupuesto? Esta accion no se puede deshacer.',
+    'delete_error' => 'Error eliminando presupuesto',
+    'delete_success' => 'Presupuesto eliminado correctamente',
+    'change_status' => 'Cambiar estado',
+    // Estadisticas
+    'stats_title' => 'Resumen',
+    'stats_total' => 'Total presupuestos',
+    'stats_amount' => 'Importe total',
+    'stats_accepted' => 'Aceptados',
+    'stats_pending' => 'Pendientes',
 ];
diff --git a/lib/helpers.php b/lib/helpers.php
index 1fa5e91..4aa6403 100644
--- a/lib/helpers.php
+++ b/lib/helpers.php
@@ -50,11 +50,12 @@ function generate_quote_number(): string
 function get_carpentry_options(): array
 {
     return [
-        'exlabesa' => 'Exlabesa',
-        'cortizo' => 'Cortizo',
-        'marco_40_40' => 'Marco 40+40',
-        'marco_40_20' => 'Marco 40x20',
-        'otra' => 'other_carpentry',
+        'corredera'  => 'Serie corredera',
+        'abatible'   => 'Serie abatible',
+        'fijo'       => 'Serie fijo',
+        'oscilo'     => 'Serie oscilobatiente',
+        'rpt'        => 'Serie RPT',
+        'otra'       => 'other_carpentry',
     ];
 }
 
@@ -66,6 +67,28 @@ function humanize_carpentry_model(string $value): string
     return array_key_exists($value, $map) ? tr((string)$label) : $label;
 }
 
+function get_status_options(?string $lang = null): array
+{
+    return [
+        'draft'    => tr('status_draft', $lang),
+        'sent'     => tr('status_sent', $lang),
+        'accepted' => tr('status_accepted', $lang),
+        'rejected' => tr('status_rejected', $lang),
+        'ordered'  => tr('status_ordered', $lang),
+    ];
+}
+
+function get_status_css_class(string $status): string
+{
+    return match ($status) {
+        'accepted' => 'status-badge--accepted',
+        'rejected' => 'status-badge--rejected',
+        'sent'     => 'status-badge--sent',
+        'ordered'  => 'status-badge--ordered',
+        default    => 'status-badge--draft',
+    };
+}
+
 function humanize_system_type(string $value, ?string $lang = null): string
 {
     $map = [
@@ -224,6 +247,8 @@ function build_quote_item_config(array $data, array $calc): array
     $pricingMode = (string)($data['pricing_mode'] ?? 'fabricada');
     $commercialMarginPct = max(0.0, (float)($data['commercial_margin_pct'] ?? ($data['margin_pct'] ?? 0)));
     $purchasedUnitCost = max(0.0, (float)($data['purchased_unit_cost'] ?? 0));
+    $hardwareCostPerUnit = max(0.0, (float)($data['hardware_cost'] ?? 0));
+    $installationCostPerUnit = max(0.0, (float)($data['installation_cost'] ?? 0));
     $internalExtraCost = max(0.0, (float)($data['internal_extra_cost'] ?? 0));
 
     return [
@@ -232,6 +257,8 @@ function build_quote_item_config(array $data, array $calc): array
         'pricing_mode' => $pricingMode,
         'is_factory_finished' => !empty($data['is_factory_finished']),
         'purchased_unit_cost' => round($purchasedUnitCost, 2),
+        'hardware_cost' => round($hardwareCostPerUnit, 2),
+        'installation_cost' => round($installationCostPerUnit, 2),
         'internal_extra_cost' => round($internalExtraCost, 2),
         'commercial_margin_pct' => round($commercialMarginPct, 2),
         'margin_pct' => $calc['margin_pct'],
@@ -310,6 +337,8 @@ function calculate_quote_item(array $data): array
     $aluminumPriceMl = max(0.0, (float)($data['aluminum_price_ml'] ?? 0));
     $glassPriceM2 = max(0.0, (float)($data['glass_price_m2'] ?? 0));
     $laborCost = max(0.0, (float)($data['labor_cost'] ?? 0));
+    $hardwareCost = max(0.0, (float)($data['hardware_cost'] ?? 0));
+    $installationCost = max(0.0, (float)($data['installation_cost'] ?? 0));
     $internalExtraCost = max(0.0, (float)($data['internal_extra_cost'] ?? 0));
     $marginPct = max(0.0, (float)($data['margin_pct'] ?? 0));
     $commercialMarginPct = max(0.0, (float)($data['commercial_margin_pct'] ?? $marginPct));
@@ -330,8 +359,10 @@ function calculate_quote_item(array $data): array
 
     $aluminumCost = $aluminumMl * $aluminumPriceMl;
     $glassCost = $glassM2 * $glassPriceM2;
-    $fabricatedBaseCost = $aluminumCost + $glassCost + $laborCost + $internalExtraCost;
-    $purchasedBaseCost = ($purchasedUnitCost * $quantity) + $internalExtraCost;
+    $hardwareTotalCost = $hardwareCost * $quantity;
+    $installationTotalCost = $installationCost * $quantity;
+    $fabricatedBaseCost = $aluminumCost + $glassCost + $laborCost + $hardwareTotalCost + $installationTotalCost + $internalExtraCost;
+    $purchasedBaseCost = ($purchasedUnitCost * $quantity) + $hardwareTotalCost + $installationTotalCost + $internalExtraCost;
 
     $baseCost = $pricingMode === 'comprada' ? $purchasedBaseCost : $fabricatedBaseCost;
     $effectiveMarginPct = $pricingMode === 'comprada' ? $commercialMarginPct : $marginPct;
@@ -356,6 +387,10 @@ function calculate_quote_item(array $data): array
         'glass_price_m2' => round($glassPriceM2, 2),
         'glass_cost' => round($glassCost, 2),
         'labor_cost' => round($laborCost, 2),
+        'hardware_cost' => round($hardwareCost, 2),
+        'hardware_total_cost' => round($hardwareTotalCost, 2),
+        'installation_cost' => round($installationCost, 2),
+        'installation_total_cost' => round($installationTotalCost, 2),
         'internal_extra_cost' => round($internalExtraCost, 2),
         'margin_pct' => round($effectiveMarginPct, 2),
         'commercial_margin_pct' => round($commercialMarginPct, 2),
diff --git a/list_quotes.php b/list_quotes.php
index cb66cb5..0ec0182 100644
--- a/list_quotes.php
+++ b/list_quotes.php
@@ -8,13 +8,33 @@ require_once __DIR__ . '/lib/helpers.php';
 $lang = get_current_lang();
 $error = null;
 $rows = [];
+$stats = ['total' => 0, 'amount' => 0.0, 'accepted' => 0, 'pending' => 0];
 
 $search = trim((string)($_GET['q'] ?? ''));
 $filterSystem = trim((string)($_GET['system'] ?? ''));
+$filterStatus = trim((string)($_GET['status'] ?? ''));
+$deleted = !empty($_GET['deleted']);
 
 try {
     $pdo = get_pdo();
 
+    // Estadisticas globales
+    $statsRow = $pdo->query(
+        'SELECT COUNT(*) as total, COALESCE(SUM(total),0) as amount,
+                SUM(status = "accepted") as accepted,
+                SUM(status IN ("draft","sent")) as pending
+         FROM quotes'
+    )->fetch();
+    if (is_array($statsRow)) {
+        $stats = [
+            'total'    => (int)$statsRow['total'],
+            'amount'   => (float)$statsRow['amount'],
+            'accepted' => (int)$statsRow['accepted'],
+            'pending'  => (int)$statsRow['pending'],
+        ];
+    }
+
+    // Listado filtrado
     $conditions = [];
     $params = [];
 
@@ -22,14 +42,17 @@ try {
         $conditions[] = 'client_name LIKE :search';
         $params[':search'] = '%' . $search . '%';
     }
-
     if ($filterSystem !== '') {
         $conditions[] = 'system_type = :system_type';
         $params[':system_type'] = $filterSystem;
     }
+    if ($filterStatus !== '') {
+        $conditions[] = 'status = :status';
+        $params[':status'] = $filterStatus;
+    }
 
     $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';
-    $sql = 'SELECT id, quote_number, created_at, client_name, system_type, total, config_json
+    $sql = 'SELECT id, quote_number, created_at, client_name, system_type, total, status, valid_until, config_json
             FROM quotes ' . $where . ' ORDER BY id DESC LIMIT 200';
 
     $stmt = $pdo->prepare($sql);
@@ -41,12 +64,13 @@ try {
 }
 
 $systemOptions = [
-    'corredera' => tr('sliding', $lang),
-    'abatible' => tr('casement', $lang),
-    'fijo' => tr('fixed', $lang),
+    'corredera'      => tr('sliding', $lang),
+    'abatible'       => tr('casement', $lang),
+    'fijo'           => tr('fixed', $lang),
     'oscilobatiente' => tr('tilt_turn', $lang),
-    'multiple' => tr('multiple_system', $lang),
+    'multiple'       => tr('multiple_system', $lang),
 ];
+$statusOptions = get_status_options($lang);
 ?>
 <!doctype html>
 <html lang="<?= h($lang) ?>">
@@ -76,21 +100,50 @@ $systemOptions = [
 
 <main class="layout" style="grid-template-columns: 1fr;">
     <section class="panel">
+
+        <?php if ($deleted): ?>
+            <div class="alert alert--success"><?= h(tr('delete_success', $lang)) ?></div>
+        <?php endif; ?>
         <?php if ($error): ?>
             <div class="alert">Error: <?= h($error) ?></div>
         <?php endif; ?>
 
+        <div class="stats-grid">
+            <div class="stat-card">
+                <span class="stat-value"><?= $stats['total'] ?></span>
+                <span class="stat-label"><?= h(tr('stats_total', $lang)) ?></span>
+            </div>
+            <div class="stat-card">
+                <span class="stat-value"><?= number_format($stats['amount'], 0, ',', '.') ?> €</span>
+                <span class="stat-label"><?= h(tr('stats_amount', $lang)) ?></span>
+            </div>
+            <div class="stat-card stat-card--accepted">
+                <span class="stat-value"><?= $stats['accepted'] ?></span>
+                <span class="stat-label"><?= h(tr('stats_accepted', $lang)) ?></span>
+            </div>
+            <div class="stat-card stat-card--pending">
+                <span class="stat-value"><?= $stats['pending'] ?></span>
+                <span class="stat-label"><?= h(tr('stats_pending', $lang)) ?></span>
+            </div>
+        </div>
+
         <form method="get" action="list_quotes.php" class="list-filters">
             <input type="hidden" name="lang" value="<?= h($lang) ?>">
             <input type="search" name="q" value="<?= h($search) ?>" placeholder="<?= h(tr('search_client', $lang)) ?>">
             <select name="system">
                 <option value=""><?= h(tr('filter_system', $lang)) ?></option>
-                <?php foreach ($systemOptions as $systemValue => $systemLabel): ?>
-                    <option value="<?= h($systemValue) ?>" <?= $filterSystem === $systemValue ? 'selected' : '' ?>><?= h($systemLabel) ?></option>
+                <?php foreach ($systemOptions as $sv => $sl): ?>
+                    <option value="<?= h($sv) ?>" <?= $filterSystem === $sv ? 'selected' : '' ?>><?= h($sl) ?></option>
+                <?php endforeach; ?>
+            </select>
+            <select name="status">
+                <option value=""><?= h(tr('status_all', $lang)) ?></option>
+                <?php foreach ($statusOptions as $sv => $sl): ?>
+                    <option value="<?= h($sv) ?>" <?= $filterStatus === $sv ? 'selected' : '' ?>><?= h($sl) ?></option>
                 <?php endforeach; ?>
             </select>
             <button type="submit"><?= h(tr('view', $lang)) ?></button>
-            <?php if ($search !== '' || $filterSystem !== ''): ?>
+            <?php if ($search !== '' || $filterSystem !== '' || $filterStatus !== ''): ?>
                 <a href="<?= h(url_with_lang('list_quotes.php', [], $lang)) ?>" class="secondary-button">&times;</a>
             <?php endif; ?>
         </form>
@@ -102,13 +155,13 @@ $systemOptions = [
             <table>
                 <thead>
                 <tr>
-                    <th>ID</th>
                     <th><?= h(tr('quote', $lang)) ?></th>
                     <th><?= h(tr('date', $lang)) ?></th>
                     <th><?= h(tr('client', $lang)) ?></th>
                     <th><?= h(tr('system', $lang)) ?></th>
                     <th><?= h(tr('item_count', $lang)) ?></th>
                     <th><?= h(tr('total', $lang)) ?></th>
+                    <th><?= h(tr('status', $lang)) ?></th>
                     <th><?= h(tr('actions', $lang)) ?></th>
                 </tr>
                 </thead>
@@ -117,19 +170,41 @@ $systemOptions = [
                     <?php
                     $decodedConfig = json_decode((string)($row['config_json'] ?? '{}'), true);
                     $itemCount = is_array($decodedConfig) ? (int)($decodedConfig['item_count'] ?? 1) : 1;
+                    $rowStatus = (string)($row['status'] ?? 'draft');
+                    $isExpired = ($row['valid_until'] ?? '') !== '' && $row['valid_until'] !== null
+                        && strtotime((string)$row['valid_until']) < strtotime('today')
+                        && !in_array($rowStatus, ['accepted', 'ordered'], true);
                     ?>
                     <tr>
-                        <td><?= (int)$row['id'] ?></td>
-                        <td><?= h((string)$row['quote_number']) ?></td>
+                        <td><a href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h((string)$row['quote_number']) ?></a></td>
                         <td><?= h((string)$row['created_at']) ?></td>
                         <td><?= h((string)$row['client_name']) ?></td>
                         <td><?= h(humanize_system_type((string)$row['system_type'], $lang)) ?></td>
                         <td><?= $itemCount ?></td>
-                        <td><?= number_format((float)$row['total'], 2, ',', '.') ?> EUR</td>
+                        <td><?= number_format((float)$row['total'], 2, ',', '.') ?> €</td>
                         <td>
+                            <form method="post" action="update_status.php" class="inline-form">
+                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
+                                <input type="hidden" name="from_list" value="1">
+                                <input type="hidden" name="lang" value="<?= h($lang) ?>">
+                                <select name="status" onchange="this.form.submit()" class="status-select <?= h(get_status_css_class($rowStatus)) ?>">
+                                    <?php foreach ($statusOptions as $sv => $sl): ?>
+                                        <option value="<?= h($sv) ?>" <?= $rowStatus === $sv ? 'selected' : '' ?>><?= h($sl) ?></option>
+                                    <?php endforeach; ?>
+                                </select>
+                            </form>
+                            <?php if ($isExpired): ?>
+                                <span class="expired-badge"><?= h($lang === 'ca' ? 'Caducat' : 'Caducado') ?></span>
+                            <?php endif; ?>
+                        </td>
+                        <td class="actions-cell">
                             <a href="<?= h(url_with_lang('view_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('view', $lang)) ?></a>
-                            |
                             <a href="<?= h(url_with_lang('duplicate_quote.php', ['id' => (int)$row['id']], $lang)) ?>"><?= h(tr('duplicate', $lang)) ?></a>
+                            <form method="post" action="delete_quote.php" class="inline-form" onsubmit="return confirm(<?= json_encode(tr('delete_confirm', $lang)) ?>)">
+                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
+                                <input type="hidden" name="lang" value="<?= h($lang) ?>">
+                                <button type="submit" class="delete-button"><?= h(tr('delete', $lang)) ?></button>
+                            </form>
                         </td>
                     </tr>
                 <?php endforeach; ?>
diff --git a/save_quote.php b/save_quote.php
index 552a518..2c3d227 100644
--- a/save_quote.php
+++ b/save_quote.php
@@ -27,20 +27,23 @@ $createdAt = date('Y-m-d H:i:s');
 try {
     $pdo = get_pdo();
 
+    $validUntilRaw = trim((string)($_POST['valid_until'] ?? ''));
+    $validUntil = ($validUntilRaw !== '' && strtotime($validUntilRaw)) ? $validUntilRaw : null;
+
     $sql = 'INSERT INTO quotes (
         quote_number, created_at, client_name, client_email, client_phone,
         system_type, opening_type, profile_color, glass_type,
         width_mm, height_mm, leaves, quantity,
-        aluminum_price_ml, glass_price_m2, labor_cost, margin_pct, iva_pct,
+        aluminum_price_ml, glass_price_m2, labor_cost, hardware_cost, installation_cost, margin_pct, iva_pct,
         aluminum_ml, glass_m2, subtotal, margin_amount, taxable_base, iva_amount, total,
-        drawing_svg, config_json, notes
+        drawing_svg, config_json, notes, status, valid_until
     ) VALUES (
         :quote_number, :created_at, :client_name, :client_email, :client_phone,
         :system_type, :opening_type, :profile_color, :glass_type,
         :width_mm, :height_mm, :leaves, :quantity,
-        :aluminum_price_ml, :glass_price_m2, :labor_cost, :margin_pct, :iva_pct,
+        :aluminum_price_ml, :glass_price_m2, :labor_cost, :hardware_cost, :installation_cost, :margin_pct, :iva_pct,
         :aluminum_ml, :glass_m2, :subtotal, :margin_amount, :taxable_base, :iva_amount, :total,
-        :drawing_svg, :config_json, :notes
+        :drawing_svg, :config_json, :notes, :status, :valid_until
     )';
 
     $stmt = $pdo->prepare($sql);
@@ -61,6 +64,8 @@ try {
         ':aluminum_price_ml' => $calc['aluminum_price_ml'],
         ':glass_price_m2' => $calc['glass_price_m2'],
         ':labor_cost' => $calc['labor_cost'],
+        ':hardware_cost' => $calc['hardware_cost'] ?? 0,
+        ':installation_cost' => $calc['installation_cost'] ?? 0,
         ':margin_pct' => $calc['margin_pct'],
         ':iva_pct' => $calc['iva_pct'],
         ':aluminum_ml' => $calc['aluminum_ml'],
@@ -73,6 +78,8 @@ try {
         ':drawing_svg' => (string)$calc['drawing_svg'],
         ':config_json' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
         ':notes' => trim((string)($_POST['notes'] ?? '')),
+        ':status' => 'draft',
+        ':valid_until' => $validUntil,
     ]);
 
     $id = (int)$pdo->lastInsertId();
diff --git a/update_status.php b/update_status.php
new file mode 100644
index 0000000..3e53388
--- /dev/null
+++ b/update_status.php
@@ -0,0 +1,41 @@
+<?php
+
+declare(strict_types=1);
+
+require_once __DIR__ . '/lib/db.php';
+require_once __DIR__ . '/lib/helpers.php';
+
+$lang = get_current_lang();
+
+if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
+    header('Location: ' . url_with_lang('list_quotes.php', [], $lang));
+    exit;
+}
+
+$id = (int)($_POST['id'] ?? 0);
+$status = trim((string)($_POST['status'] ?? ''));
+$validStatuses = ['draft', 'sent', 'accepted', 'rejected', 'ordered'];
+
+if ($id <= 0 || !in_array($status, $validStatuses, true)) {
+    http_response_code(400);
+    echo h(tr('invalid_id', $lang));
+    exit;
+}
+
+$redirect = url_with_lang('view_quote.php', ['id' => $id], $lang);
+if (!empty($_POST['from_list'])) {
+    $redirect = url_with_lang('list_quotes.php', [], $lang);
+}
+
+try {
+    $pdo = get_pdo();
+    $stmt = $pdo->prepare('UPDATE quotes SET status = :status WHERE id = :id');
+    $stmt->execute([':status' => $status, ':id' => $id]);
+
+    header('Location: ' . $redirect);
+    exit;
+} catch (Throwable $e) {
+    error_log('[update_status] ' . $e->getMessage());
+    http_response_code(500);
+    echo h(tr('save_error', $lang));
+}
diff --git a/view_quote.php b/view_quote.php
index f0d1bac..d6d7dee 100644
--- a/view_quote.php
+++ b/view_quote.php
@@ -75,6 +75,26 @@ try {
             <button type="button" onclick="window.print()"><?= h(tr('print_pdf', $lang)) ?></button>
         </div>
 
+        <?php
+        $quoteStatus = (string)($row['status'] ?? 'draft');
+        $statusOptions = get_status_options($lang);
+        ?>
+        <div class="status-bar no-print">
+            <span class="status-badge <?= h(get_status_css_class($quoteStatus)) ?>"><?= h($statusOptions[$quoteStatus] ?? $quoteStatus) ?></span>
+            <?php if (($row['valid_until'] ?? '') !== '' && $row['valid_until'] !== null): ?>
+                <span class="valid-until-label"><?= h(tr('valid_until', $lang)) ?>: <?= h((string)$row['valid_until']) ?></span>
+            <?php endif; ?>
+            <form method="post" action="update_status.php" class="inline-form">
+                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
+                <input type="hidden" name="lang" value="<?= h($lang) ?>">
+                <select name="status" onchange="this.form.submit()" class="status-select <?= h(get_status_css_class($quoteStatus)) ?>">
+                    <?php foreach ($statusOptions as $sv => $sl): ?>
+                        <option value="<?= h($sv) ?>" <?= $quoteStatus === $sv ? 'selected' : '' ?>><?= h($sl) ?></option>
+                    <?php endforeach; ?>
+                </select>
+            </form>
+        </div>
+
         <h2><?= h(tr('client', $lang)) ?></h2>
         <p><strong><?= h((string)$row['client_name']) ?></strong></p>
         <p><?= h((string)$row['client_email']) ?> - <?= h((string)$row['client_phone']) ?></p>
@@ -123,18 +143,24 @@ try {
         <h3><?= h(tr('amounts', $lang)) ?></h3>
         <div class="totals">
             <div class="total-row"><span><?= h(tr('aluminum_price', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['aluminum_ml'] ?? $row['aluminum_ml']), 3, ',', '.') ?> ml</strong></div>
-            <div class="total-row"><span><?= h(tr('glass', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['glass_m2'] ?? $row['glass_m2']), 3, ',', '.') ?> m2</strong></div>
+            <div class="total-row"><span><?= h(tr('glass', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['glass_m2'] ?? $row['glass_m2']), 3, ',', '.') ?> m²</strong></div>
             <?php if (isset($quoteTotals['glass_cost']) || isset($config['glass_cost'])): ?>
-                <div class="total-row"><span><?= h(tr('glass_cost', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['glass_cost'] ?? $config['glass_cost']), 2, ',', '.') ?> EUR</strong></div>
+                <div class="total-row"><span><?= h(tr('glass_cost', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['glass_cost'] ?? $config['glass_cost']), 2, ',', '.') ?> €</strong></div>
+            <?php endif; ?>
+            <?php if ((float)($row['hardware_cost'] ?? 0) > 0): ?>
+                <div class="total-row"><span><?= h(tr('hardware_cost', $lang)) ?></span><strong><?= number_format((float)$row['hardware_cost'], 2, ',', '.') ?> €/ud</strong></div>
+            <?php endif; ?>
+            <?php if ((float)($row['installation_cost'] ?? 0) > 0): ?>
+                <div class="total-row"><span><?= h(tr('installation_cost', $lang)) ?></span><strong><?= number_format((float)$row['installation_cost'], 2, ',', '.') ?> €/ud</strong></div>
             <?php endif; ?>
             <?php if (($config['pricing_mode'] ?? 'fabricada') === 'comprada'): ?>
-                <div class="total-row"><span><?= h(tr('purchase_cost_me', $lang)) ?></span><strong><?= number_format((float)($config['purchased_unit_cost'] ?? 0), 2, ',', '.') ?> EUR/ud</strong></div>
+                <div class="total-row"><span><?= h(tr('purchase_cost_me', $lang)) ?></span><strong><?= number_format((float)($config['purchased_unit_cost'] ?? 0), 2, ',', '.') ?> €/ud</strong></div>
                 <div class="total-row"><span><?= h(tr('commercial_margin', $lang)) ?></span><strong><?= number_format((float)($config['commercial_margin_pct'] ?? $row['margin_pct']), 2, ',', '.') ?> %</strong></div>
             <?php endif; ?>
-            <div class="total-row"><span><?= h(tr('subtotal', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['subtotal'] ?? $row['subtotal']), 2, ',', '.') ?> EUR</strong></div>
-            <div class="total-row"><span><?= h(tr('margin', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['margin_amount'] ?? $row['margin_amount']), 2, ',', '.') ?> EUR</strong></div>
-            <div class="total-row"><span><?= h(tr('iva', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['iva_amount'] ?? $row['iva_amount']), 2, ',', '.') ?> EUR</strong></div>
-            <div class="total-row total-main"><span><?= h(tr('total', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['total'] ?? $row['total']), 2, ',', '.') ?> EUR</strong></div>
+            <div class="total-row"><span><?= h(tr('subtotal', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['subtotal'] ?? $row['subtotal']), 2, ',', '.') ?> €</strong></div>
+            <div class="total-row"><span><?= h(tr('margin', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['margin_amount'] ?? $row['margin_amount']), 2, ',', '.') ?> €</strong></div>
+            <div class="total-row"><span><?= h(tr('iva', $lang)) ?> (<?= number_format((float)$row['iva_pct'], 0) ?>%)</span><strong><?= number_format((float)($quoteTotals['iva_amount'] ?? $row['iva_amount']), 2, ',', '.') ?> €</strong></div>
+            <div class="total-row total-main"><span><?= h(tr('total', $lang)) ?></span><strong><?= number_format((float)($quoteTotals['total'] ?? $row['total']), 2, ',', '.') ?> €</strong></div>
         </div>
 
         <h3><?= h(tr('notes', $lang)) ?></h3>
-- 
2.43.0

PATCHEOF

git am "$TMPDIR_PATCHES/0001.patch"
git am "$TMPDIR_PATCHES/0002.patch"

echo ""
echo "✓ Cambios aplicados en rama $BRANCH"
echo "  Ahora ejecuta: git push -u origin $BRANCH"
