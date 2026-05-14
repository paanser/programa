window.S28Engine = (function () {
    'use strict';

    var PROFILES = {
        '5.980':  'Marco Ventana',
        '5.981':  'Marco Ventana con solape 28mm',
        '5.982':  'Hoja Ventana 47mm',
        '5.984':  'Inversor recto',
        '5.985':  'Pilastra Ventana',
        '5.986':  'Marco Puerta',
        '5.987':  'Hoja Balconera',
        '5.988':  'Hoja Balconera apertura exterior',
        '5.989':  'Pilastra Puerta',
        '5.228':  'Marco Fijo liso',
        '9.619':  'Vierteaguas hoja',
        '9.622':  'Marco bajo Puerta',
        '6.064':  'Solape Grapa 30mm',
        '6.069':  'Guia Compacto 120mm',
        '6.755':  'Forro Registro Recto 40mm',
        '6.756':  'Forro Registro Recto 60mm',
        '6.757':  'Forro Registro Recto 85mm',
        '9.213':  'Premarco de Obra 36mm',
        '9.214':  'Premarco de Obra 122mm',
        '9.215':  'Premarco de Obra 136mm',
        '3.360':  'Junquillo Recto C-14.5',
        '5.070':  'Junquillo Curvo grapa C-8.5',
        '5.071':  'Junquillo Curvo grapa C-20.5',
        '5.612':  'Junquillo Redondo grapa C-8.5',
        '5.613':  'Junquillo Redondo grapa C-20.5',
        '6.179':  'Junquillo Curvo clip C-8.5',
        '6.180':  'Junquillo Curvo clip C-20.5',
        '6.181':  'Junquillo Redondo clip C-8.5',
        '6.182':  'Junquillo Redondo clip C-20.5',
    };

    var K = {
        MARCO_CARA:     21.8,
        MARCO_CARA2:    43.6,
        GLASS_OFFSET_1H: 108,
        GLASS_OFFSET_H:  108,
        HOJA_OFFSET:    43.6,
        HOJA_OFFSET_2H: 26,
        GLASS_OFFSET_2H: 88,
        VIERTEG_EXTRA:   5,
        JUNQ_EXTRA:     12,
        PUERTA_H_OFFSET: 73.6,
        PUERTA_H_GLASS:  138,
        FIJO_OFFSET:    48,
    };

    function junquilloRef(glassThick, junqType) {
        var heavy = glassThick > 20;
        var types = {
            'curvo_grapa':   heavy ? '5.071' : '5.070',
            'curvo_clip':    heavy ? '6.180' : '6.179',
            'recto':         '3.360',
            'redondo_grapa': heavy ? '5.613' : '5.612',
            'redondo_clip':  heavy ? '6.182' : '6.181',
        };
        return types[junqType] || '6.179';
    }

    function thickFromGlassType(glassTypeValue) {
        var nums = (glassTypeValue || '').match(/\d+/g);
        if (!nums) { return 20; }
        return nums.reduce(function (sum, n) { return sum + parseInt(n, 10); }, 0);
    }

    function mm(v) { return Math.round(v); }

    function accesorios_v1h_prac(osci) {
        var list = [
            { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'2' },
            { ref:'04.APS.C01', desc:'Cremona Practicable',              qty:'1' },
            { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',   qty:'1' },
            { ref:'04.OBS.020', desc:'Kit Base Ancho Hoja 360/1.400',    qty:'1', note:'segun ancho' },
            { ref:'04.OBS.021', desc:'Kit Base Ancho Hoja 1.401/1.700',  qty:'1', note:'si ancho >1400' },
            { ref:'04.OBS.022', desc:'Compas Corte Ancho Hoja 360/500',  qty:'1', note:'segun corte' },
            { ref:'04.OBS.023', desc:'Compas Estandar Ancho Hoja 510/794', qty:'1' },
            { ref:'04.OBS.030', desc:'Kit bisagras ambidextras 110 Kg',  qty:'1' },
            { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'8' },
            { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
            { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'4' },
            { ref:'04.TA.016',  desc:'Tapa desague',                     qty:'2' },
            { ref:'04.AC.T01',  desc:'Tubo desague hoja',                qty:'2' },
            { ref:'04.JU.001',  desc:'Junta Central',                    qty:'2H+2L' },
            { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'2H+2L' },
            { ref:'04.JU.004.D',desc:'Junta Interior EPDM',              qty:'2H+2L' },
            { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',     qty:'4 ml' },
            { ref:'04.TA.020',  desc:'Juego tapas vierteaguas hoja',     qty:'1' },
            { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'2H+2L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',      qty:'2H+2L' },
        ];
        if (osci) {
            list.push({ ref:'04.OBS.005', desc:'Cremona Oscilobatiente', qty:'1' });
        }
        return list;
    }

    function accesorios_v2h_prac() {
        return [
            { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'4' },
            { ref:'04.APS.C01', desc:'Cremona Practicable',              qty:'1' },
            { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',   qty:'1' },
            { ref:'04.APS.002', desc:'Kit de cierre Hoja Pasiva',        qty:'1' },
            { ref:'04.OBS.020', desc:'Kit Base Ancho Hoja 360/1.400',    qty:'1', note:'segun ancho' },
            { ref:'04.OBS.044', desc:'Kit bisagras Hoja Pasiva 80 Kg',   qty:'1' },
            { ref:'04.TA.003',  desc:'Juego tapas inversor',             qty:'1' },
            { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'12' },
            { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
            { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'8' },
            { ref:'04.TA.016',  desc:'Tapa desague',                     qty:'2' },
            { ref:'04.AC.T01',  desc:'Tubo desague hoja',                qty:'4' },
            { ref:'04.JU.001',  desc:'Junta Central',                    qty:'3H+2L' },
            { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'4H+2L' },
            { ref:'04.JU.004.D',desc:'Junta Interior EPDM',              qty:'3H+2L' },
            { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',     qty:'4 ml' },
            { ref:'04.TA.020',  desc:'Juego tapas vierteaguas hoja',     qty:'2' },
            { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'4H+4L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',      qty:'4H+4L' },
        ];
    }

    function accesorios_v3h_prac() {
        return [
            { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'6' },
            { ref:'04.APS.C01', desc:'Cremona Practicable',              qty:'1' },
            { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',   qty:'1' },
            { ref:'04.APS.002', desc:'Kit de cierre Hoja Pasiva',        qty:'2' },
            { ref:'04.TA.003',  desc:'Juego tapas inversor',             qty:'2' },
            { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'16' },
            { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
            { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'12' },
            { ref:'04.TA.016',  desc:'Tapa desague',                     qty:'2' },
            { ref:'04.AC.T01',  desc:'Tubo desague hoja',                qty:'6' },
            { ref:'04.JU.001',  desc:'Junta Central',                    qty:'4H+2L' },
            { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'4H+2L' },
            { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',     qty:'4 ml' },
            { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'6H+6L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',      qty:'6H+6L' },
        ];
    }

    function accesorios_abatible() {
        return [
            { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                    qty:'2' },
            { ref:'04.AP.G01',  desc:'Cierre de golpete',                qty:'1' },
            { ref:'04.APL.C01', desc:'Cierre de golpete marco enrasado', qty:'1' },
            { ref:'04.AP.G10',  desc:'Mando a distancia',               qty:'1', note:'opcional' },
            { ref:'04.AP.G02',  desc:'Compas desmontable',              qty:'2' },
            { ref:'04.ES.003',  desc:'Escuadra ventana',                 qty:'8' },
            { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'4' },
            { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'4' },
            { ref:'04.TA.016',  desc:'Tapa desague',                     qty:'2' },
            { ref:'04.AC.T01',  desc:'Tubo desague hoja',                qty:'2' },
            { ref:'04.JU.001',  desc:'Junta Central',                    qty:'2H+2L' },
            { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',              qty:'2H+2L' },
            { ref:'04.JU.004.D',desc:'Junta Interior EPDM',              qty:'2H+2L' },
            { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',       qty:'2H+2L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',     qty:'2H+2L' },
        ];
    }

    function accesorios_fijo() {
        return [
            { ref:'04.ES.003',   desc:'Escuadra ventana',               qty:'8' },
            { ref:'04.TA.016',   desc:'Tapa desague',                   qty:'2' },
            { ref:'04.GR.001',   desc:'Grapa junquillo curvo',          qty:'segun L' },
            { ref:'04.JA.001',   desc:'Junta Acristalamiento Ext.',     qty:'2H+2L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',     qty:'2H+2L' },
        ];
    }

    function accesorios_balconera(nHojas) {
        return [
            { ref:'04.APS.B11', desc:'Bisagra 80 Kg',                   qty: String(nHojas * 3) },
            { ref:'04.APS.C01', desc:'Cremona Practicable',             qty:'1' },
            { ref:'04.APS.001', desc:'Kit de cierre Hoja Practicable',  qty:'1' },
            { ref:'04.OBS.030', desc:'Kit bisagras ambidextras 110 Kg', qty:'1' },
            { ref:'04.APL.C05', desc:'Cremona Apertura exterior',       qty: nHojas > 1 ? '1' : '\u2013' },
            { ref:'04.ES.003',  desc:'Escuadra ventana',                qty: String(nHojas * 4 + 4) },
            { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',     qty:'4' },
            { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',      qty: String(nHojas * 4) },
            { ref:'04.TA.016',  desc:'Tapa desague',                    qty:'2' },
            { ref:'04.AC.T01',  desc:'Tubo desague hoja',               qty: String(nHojas * 2) },
            { ref:'04.JU.001',  desc:'Junta Central',                   qty: nHojas === 1 ? '2H+2L' : '3H+2L' },
            { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',             qty: nHojas === 1 ? '2H+2L' : '4H+2L' },
            { ref:'04.AC.008',  desc:'Tornillo clipaje vierteaguas',    qty:'4 ml' },
            { ref:'04.TA.020',  desc:'Juego tapas vierteaguas hoja',    qty: String(nHojas) },
            { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',      qty: nHojas === 1 ? '2H+2L' : '4H+4L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',     qty: nHojas === 1 ? '2H+2L' : '4H+4L' },
        ];
    }

    function accesorios_balconera_ext() {
        return [
            { ref:'04.APS.B11', desc:'Bisagra 90 Kg',                   qty:'3' },
            { ref:'04.APL.C05', desc:'Cremona Apertura exterior',       qty:'1' },
            { ref:'04.APL.C07', desc:'Perno de conexion',               qty:'2' },
            { ref:'04.APL.C13', desc:'Alargador polo',                  qty:'2' },
            { ref:'04.APS.006', desc:'Kit de cierre Apertura Exterior', qty:'1' },
            { ref:'04.APS.003', desc:'Kit de cierre Hoja Pasiva con leva', qty:'1' },
            { ref:'04.TA.003',  desc:'Juego tapas inversor',            qty:'1' },
            { ref:'04.ES.003',  desc:'Escuadra Marco Ventana',          qty:'4' },
            { ref:'04.EA.005',  desc:'Escuadra alineamiento Hoja Balconera', qty:'8' },
            { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',     qty:'4' },
            { ref:'04.JU.001',  desc:'Junta Central',                   qty:'2H+2L' },
            { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',             qty:'2H+2L' },
            { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',      qty:'2H+2L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',     qty:'2H+2L' },
        ];
    }

    function accesorios_puerta() {
        return [
            { ref:'04.MH.008',  desc:'Juego manillas recuperables',     qty:'1' },
            { ref:'04.BP.017',  desc:'Bisagra puerta 100 Kg',           qty:'2' },
            { ref:'04.BP.001',  desc:'Bisagra puerta',                   qty:'2' },
            { ref:'04.TA.034',  desc:'Tope union Marco Interior',       qty:'2' },
            { ref:'04.ES.006',  desc:'Escuadra puerta',                  qty:'4' },
            { ref:'04.EA.002',  desc:'Escuadra alineamiento Marco',      qty:'2' },
            { ref:'04.EA.009',  desc:'Escuadra alineamiento Hoja',       qty:'4' },
            { ref:'04.TO.012',  desc:'Techo de union pilastra',         qty:'2', note:'si lleva pilastra' },
            { ref:'04.JU.001',  desc:'Junta Central',                    qty:'2H+1L' },
            { ref:'04.JU.004.E',desc:'Junta Exterior EPDM',             qty:'2H+1L' },
            { ref:'04.JU.004.D',desc:'Junta Interior EPDM',             qty:'2H+1L' },
            { ref:'04.JA.001',  desc:'Junta Acristalamiento Ext.',      qty:'2H+4L' },
            { ref:'SEGUN VIDRIO',desc:'Junta Acristalamiento Int.',     qty:'2H+4L' },
            { ref:'04.AC.F06',  desc:'Felpudo inferior',                qty:'L' },
            { ref:'04.TA.018',  desc:'Juego tapas vierteaguas',         qty:'1' },
        ];
    }

    function vBase(L, H, qty, opts, extraBars) {
        return { bars: extraBars, glass: [], accessories: [] };
    }

    var SYSTEMS = {
        v1h_prac: {
            name: 'Ventana 1 Hoja Practicable', page: '28-B1',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var gW = L - K.GLASS_OFFSET_1H, gH = H - K.GLASS_OFFSET_H;
                var hojaH = L - K.HOJA_OFFSET, hojaV = H - K.HOJA_OFFSET;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,                 qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,                 qty: 2, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',    cut: hojaH,             qty: 2, formula: 'L \u2212 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',      cut: hojaV,             qty: 2, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',   cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L \u2212 48.6' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }], accessories: accesorios_v1h_prac(false) };
            }
        },
        v1h_osci: {
            name: 'Ventana 1 Hoja Oscilobatiente', page: '28-B1',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var gW = L - K.GLASS_OFFSET_1H, gH = H - K.GLASS_OFFSET_H;
                var hojaH = L - K.HOJA_OFFSET, hojaV = H - K.HOJA_OFFSET;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',    cut: hojaH,  qty: 2, formula: 'L \u2212 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',      cut: hojaV,  qty: 2, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',   cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L \u2212 48.6' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }], accessories: accesorios_v1h_prac(true) };
            }
        },
        v1h_fijo: {
            name: 'Ventana 1 Hoja + Fijo', page: '28-B2',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var L1 = Math.round(L * 0.55), L2 = L - L1;
                var hojaH = L1 - K.HOJA_OFFSET, hojaV = H - K.HOJA_OFFSET;
                var gWHoja = L1 - K.GLASS_OFFSET_1H, gWFijo = L2 - K.FIJO_OFFSET, gH = H - K.GLASS_OFFSET_H;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',    cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',      cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.985', desc:'Pilastra ventana',    cut: H,      qty: 1, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',     cut: hojaH,  qty: 2, formula: 'L1 \u2212 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',       cut: hojaV,  qty: 2, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',    cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L1 \u2212 48.6' },
                    { ref: jRef,   desc:'Junquillo H (hoja)',  cut: gWHoja + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (hoja)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                    { ref: jRef,   desc:'Junquillo H (fijo)',  cut: gWFijo + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_F_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (fijo)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [
                    { desc: 'Vidrio hoja', W: gWHoja, H: gH, qty: 1 },
                    { desc: 'Vidrio fijo', W: gWFijo, H: gH, qty: 1 },
                ], accessories: accesorios_v1h_prac(false) };
            }
        },
        v2h_prac: {
            name: 'Ventana 2 Hojas Practicable', page: '28-B3',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var hojaH = Math.round(L / 2 - K.HOJA_OFFSET_2H), hojaV = H - K.HOJA_OFFSET;
                var gW = Math.round(L / 2 - K.GLASS_OFFSET_2H), gH = H - K.GLASS_OFFSET_H;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',    cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',      cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto',      cut: hojaV,  qty: 1, formula: 'H \u2212 43.6' },
                    { ref:'5.982', desc:'Hoja horizontal',     cut: hojaH,  qty: 4, formula: 'L/2 \u2212 26' },
                    { ref:'5.982', desc:'Hoja vertical',       cut: hojaV,  qty: 4, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (\u00d72)',    cut: hojaH-5, qty: 2, formula: 'hoja_H \u2212 5' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio por hoja', W: gW, H: gH, qty: 2 }], accessories: accesorios_v2h_prac() };
            }
        },
        v2h_fijo: {
            name: 'Ventana 2 Hojas + Fijo', page: '28-B4',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var L_hojas = Math.round(L * 0.65), L_fijo = L - L_hojas;
                var hojaH = Math.round(L_hojas / 2 - K.HOJA_OFFSET_2H), hojaV = H - K.HOJA_OFFSET;
                var gW = Math.round(L_hojas / 2 - K.GLASS_OFFSET_2H), gWFijo = L_fijo - K.FIJO_OFFSET, gH = H - K.GLASS_OFFSET_H;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',     cut: L,       qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',       cut: H,       qty: 2, formula: 'H' },
                    { ref:'5.985', desc:'Pilastra ventana',     cut: H,       qty: 1, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto',       cut: hojaV,   qty: 1, formula: '(H\u221243.6)' },
                    { ref:'5.982', desc:'Hoja horizontal',      cut: hojaH,   qty: 4, formula: 'L_hojas/2 \u2212 26' },
                    { ref:'5.982', desc:'Hoja vertical',        cut: hojaV,   qty: 4, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (\u00d72)',     cut: hojaH-5, qty: 2, formula: 'hoja_H \u2212 5' },
                    { ref: jRef,   desc:'Junquillo H (hojas)',  cut: gW + K.JUNQ_EXTRA,     qty: 4, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (hojas)',  cut: gH + K.JUNQ_EXTRA,     qty: 4, formula: 'vidrio_H + 12' },
                    { ref: jRef,   desc:'Junquillo H (fijo)',   cut: gWFijo + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_F_L + 12' },
                    { ref: jRef,   desc:'Junquillo V (fijo)',   cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [
                    { desc: 'Vidrio por hoja', W: gW, H: gH, qty: 2 },
                    { desc: 'Vidrio fijo',     W: gWFijo, H: gH, qty: 1 },
                ], accessories: accesorios_v2h_prac() };
            }
        },
        v3h_prac: {
            name: 'Ventana 3 Hojas Practicable', page: '28-B5',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var hojaH = Math.round(L / 3 - K.HOJA_OFFSET_2H), hojaV = H - K.HOJA_OFFSET;
                var gW = Math.round(L / 3 - K.GLASS_OFFSET_2H), gH = H - K.GLASS_OFFSET_H;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',    cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',      cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto (\u00d72)', cut: hojaV,  qty: 2, formula: 'H \u2212 43.6' },
                    { ref:'5.982', desc:'Hoja horizontal',     cut: hojaH,  qty: 6, formula: 'L/3 \u2212 26' },
                    { ref:'5.982', desc:'Hoja vertical',       cut: hojaV,  qty: 6, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (\u00d73)',    cut: hojaH-5, qty: 3, formula: 'hoja_H \u2212 5' },
                    { ref: jRef,   desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 6, formula: 'vidrio_L + 12' },
                    { ref: jRef,   desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 6, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio por hoja', W: gW, H: gH, qty: 3 }], accessories: accesorios_v3h_prac() };
            }
        },
        v_abatible: {
            name: 'Ventana Abatible (proyectante)', page: '28-B6',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var gW = L - K.GLASS_OFFSET_1H, gH = H - K.GLASS_OFFSET_H;
                var hojaH = L - K.HOJA_OFFSET, hojaV = H - K.HOJA_OFFSET;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,     qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,     qty: 2, formula: 'H' },
                    { ref:'5.982', desc:'Hoja horizontal',    cut: hojaH, qty: 2, formula: 'L \u2212 43.6' },
                    { ref:'5.982', desc:'Hoja vertical',      cut: hojaV, qty: 2, formula: 'H \u2212 43.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio', W: gW, H: gH, qty: 1 }], accessories: accesorios_abatible() };
            }
        },
        v_fijo: {
            name: 'Ventana Fija', page: '28',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var gW = L - K.FIJO_OFFSET, gH = H - K.FIJO_OFFSET;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',   cut: L,                   qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',     cut: H,                   qty: 2, formula: 'H' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA,  qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA,  qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio fijo', W: gW, H: gH, qty: 1 }], accessories: accesorios_fijo() };
            }
        },
        b1h_prac: {
            name: 'Balconera 1 Hoja Practicable', page: '28-B10',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var gW = L - K.GLASS_OFFSET_1H, gH = H - K.GLASS_OFFSET_H;
                var hojaH = L - K.HOJA_OFFSET, hojaV = H - K.HOJA_OFFSET;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',       cut: L,     qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',         cut: H,     qty: 2, formula: 'H' },
                    { ref:'5.987', desc:'Hoja Balconera horizontal', cut: hojaH, qty: 2, formula: 'L \u2212 43.6' },
                    { ref:'5.987', desc:'Hoja Balconera vertical',   cut: hojaV, qty: 2, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',       cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L \u2212 48.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }], accessories: accesorios_balconera(1) };
            }
        },
        b2h_prac: {
            name: 'Balconera 2 Hojas Practicable', page: '28-B12',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var hojaH = Math.round(L / 2 - K.HOJA_OFFSET_2H), hojaV = H - K.HOJA_OFFSET;
                var gW = Math.round(L / 2 - K.GLASS_OFFSET_2H), gH = H - K.GLASS_OFFSET_H;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',        cut: L,      qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',          cut: H,      qty: 2, formula: 'H' },
                    { ref:'5.984', desc:'Inversor recto',          cut: hojaV,  qty: 1, formula: 'H \u2212 43.6' },
                    { ref:'5.987', desc:'Hoja Balconera horizontal', cut: hojaH, qty: 4, formula: 'L/2 \u2212 26' },
                    { ref:'5.987', desc:'Hoja Balconera vertical',   cut: hojaV, qty: 4, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas (\u00d72)',        cut: hojaH-5, qty: 2, formula: 'hoja_H \u2212 5' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 4, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio por hoja', W: gW, H: gH, qty: 2 }], accessories: accesorios_balconera(2) };
            }
        },
        b1h_ext: {
            name: 'Balconera 1 Hoja Apertura Exterior', page: '28-B12',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var gW = L - K.GLASS_OFFSET_1H, gH = H - K.GLASS_OFFSET_H;
                var hojaH = L - K.HOJA_OFFSET, hojaV = H - K.HOJA_OFFSET;
                return { bars: [
                    { ref:'5.980', desc:'Marco horizontal',       cut: L,     qty: 2, formula: 'L' },
                    { ref:'5.980', desc:'Marco vertical',         cut: H,     qty: 2, formula: 'H' },
                    { ref:'5.988', desc:'H. Balc. Ap.Ext horizontal', cut: hojaH, qty: 2, formula: 'L \u2212 43.6' },
                    { ref:'5.988', desc:'H. Balc. Ap.Ext vertical',   cut: hojaV, qty: 2, formula: 'H \u2212 43.6' },
                    { ref:'9.619', desc:'Vierteaguas hoja',       cut: hojaH - K.VIERTEG_EXTRA, qty: 1, formula: 'L \u2212 48.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio hoja', W: gW, H: gH, qty: 1 }], accessories: accesorios_balconera_ext() };
            }
        },
        p1h_int: {
            name: 'Puerta 1 Hoja Interior', page: '28-B17',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var hojaH = L - K.PUERTA_H_OFFSET, hojaV = H - K.HOJA_OFFSET;
                var gW = L - K.PUERTA_H_GLASS, gH = H - K.GLASS_OFFSET_H;
                return { bars: [
                    { ref:'5.986', desc:'Marco Puerta horizontal top',  cut: L,     qty: 1, formula: 'L' },
                    { ref:'5.986', desc:'Marco Puerta vertical (\u00d72)',   cut: H,     qty: 2, formula: 'H' },
                    { ref:'9.622', desc:'Marco bajo Puerta',            cut: L,     qty: 1, formula: 'L' },
                    { ref:'5.987', desc:'Hoja Balconera horizontal',    cut: hojaH, qty: 2, formula: 'L \u2212 73.6' },
                    { ref:'5.987', desc:'Hoja Balconera vertical',      cut: hojaV, qty: 2, formula: 'H \u2212 43.6' },
                    { ref: jRef,  desc:'Junquillo horizontal', cut: gW + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo vertical',   cut: gH + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [{ desc: 'Vidrio puerta', W: gW, H: gH, qty: 1 }], accessories: accesorios_puerta() };
            }
        },
        p1h_fijo: {
            name: 'Puerta 1 Hoja + Fijo', page: '28-B22',
            calc: function (L, H, qty, opts) {
                var jRef = junquilloRef(opts.glassThick, opts.junquilloType);
                var L_puerta = Math.round(L * 0.6), L_fijo = L - L_puerta;
                var hojaH = L_puerta - K.PUERTA_H_OFFSET, hojaV = H - K.HOJA_OFFSET;
                var gW = L_puerta - K.PUERTA_H_GLASS, gWFijo = L_fijo - K.FIJO_OFFSET, gH = H - K.GLASS_OFFSET_H;
                return { bars: [
                    { ref:'5.986', desc:'Marco Puerta horizontal',   cut: L,     qty: 1, formula: 'L' },
                    { ref:'5.986', desc:'Marco Puerta vertical (\u00d72)', cut: H,    qty: 2, formula: 'H' },
                    { ref:'5.989', desc:'Pilastra Puerta',            cut: H,    qty: 1, formula: 'H' },
                    { ref:'9.622', desc:'Marco bajo Puerta',          cut: L_puerta, qty: 1, formula: 'L_puerta' },
                    { ref:'5.987', desc:'Hoja horizontal',            cut: hojaH, qty: 2, formula: 'L_puerta \u2212 73.6' },
                    { ref:'5.987', desc:'Hoja vertical',              cut: hojaV, qty: 2, formula: 'H \u2212 43.6' },
                    { ref: jRef,  desc:'Junquillo H (hoja)',  cut: gW + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_L + 12' },
                    { ref: jRef,  desc:'Junquillo V (hoja)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                    { ref: jRef,  desc:'Junquillo H (fijo)',  cut: gWFijo + K.JUNQ_EXTRA, qty: 2, formula: 'vidrio_F_L + 12' },
                    { ref: jRef,  desc:'Junquillo V (fijo)',  cut: gH + K.JUNQ_EXTRA,     qty: 2, formula: 'vidrio_H + 12' },
                ], glass: [
                    { desc: 'Vidrio puerta', W: gW, H: gH, qty: 1 },
                    { desc: 'Vidrio fijo',   W: gWFijo, H: gH, qty: 1 },
                ], accessories: accesorios_puerta() };
            }
        },
    };

    function calculate(sysId, L, H, qty, opts) {
        var sys = SYSTEMS[sysId];
        if (!sys) { return null; }
        opts = opts || {};
        if (!opts.glassThick) { opts.glassThick = 20; }
        if (!opts.junquilloType) { opts.junquilloType = 'curvo_clip'; }
        return sys.calc(L, H, qty, opts);
    }

    return {
        PROFILES: PROFILES,
        K: K,
        SYSTEMS: SYSTEMS,
        junquilloRef: junquilloRef,
        thickFromGlassType: thickFromGlassType,
        mm: mm,
        calculate: calculate,
    };
})();
