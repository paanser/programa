import type { DrawingContext } from '../../types';

interface TechnicalLabelsProps {
  ctx: DrawingContext;
}

const FONT = "'Courier New', Courier, monospace";
const COLOR_LABEL = 'rgba(80,90,100,0.7)';
const COLOR_VALUE = '#1a2a3a';
const COLOR_TITLE = '#1a2a3a';
const COLOR_BORDER = 'rgba(80,100,120,0.25)';

interface Row {
  label: string;
  value: string;
}

export function TechnicalLabels({ ctx }: TechnicalLabelsProps) {
  const { config, viewport } = ctx;
  const { labelsArea } = viewport;
  const { x, y, width, height } = labelsArea;
  const pad = 14;

  const systemLabels: Record<string, string> = {
    corredera: 'CORREDERA',
    abatible: 'ABATIBLE',
    oscilobatiente: 'OSCILOBATIENTE',
    fijo: 'FIJO',
    puerta: 'PUERTA',
  };

  const rows: Row[] = [
    { label: 'SISTEMA', value: systemLabels[config.systemType] ?? config.systemType.toUpperCase() },
    { label: 'MODELO', value: config.carpentryModel || '—' },
    { label: 'REF.', value: config.carpentryReference || '—' },
    { label: 'COLOR', value: config.profileColorName || config.profileColorHex || '—' },
    { label: 'VIDRIO', value: config.glassDescription || '—' },
    { label: 'MEDIDA', value: `${config.widthMm} × ${config.heightMm} mm` },
    { label: 'HOJAS', value: String(config.leaves) },
    { label: 'CANTIDAD', value: String(config.quantity) },
  ];

  const rowH = 36;
  const headerH = 52;
  const totalContentH = headerH + rows.length * rowH;

  // Center block vertically
  const blockY = y + (height - totalContentH) / 2;

  return (
    <g role="presentation">
      {/* Panel background */}
      <rect
        x={x}
        y={y}
        width={width}
        height={height}
        fill="rgba(248,250,252,0.97)"
        stroke={COLOR_BORDER}
        strokeWidth="0.8"
      />
      {/* Vertical separator line */}
      <line
        x1={x}
        y1={y}
        x2={x}
        y2={y + height}
        stroke={COLOR_BORDER}
        strokeWidth="1.2"
      />

      {/* Header: title */}
      <rect
        x={x}
        y={blockY}
        width={width}
        height={headerH}
        fill="rgba(230,238,248,0.6)"
        stroke="none"
      />
      <text
        x={x + width / 2}
        y={blockY + 20}
        textAnchor="middle"
        fontFamily={FONT}
        fontSize="11"
        fontWeight="700"
        fill={COLOR_TITLE}
        letterSpacing="2"
      >
        DIBUJO TÉCNICO
      </text>
      <text
        x={x + width / 2}
        y={blockY + 38}
        textAnchor="middle"
        fontFamily={FONT}
        fontSize="9"
        fill={COLOR_LABEL}
        letterSpacing="1"
      >
        CARPINTERÍA METÁLICA
      </text>
      <line
        x1={x + pad}
        y1={blockY + headerH - 1}
        x2={x + width - pad}
        y2={blockY + headerH - 1}
        stroke={COLOR_BORDER}
        strokeWidth="0.7"
      />

      {/* Data rows */}
      {rows.map((row, i) => {
        const ry = blockY + headerH + i * rowH;
        return (
          <g key={row.label}>
            <line
              x1={x + pad}
              y1={ry + rowH}
              x2={x + width - pad}
              y2={ry + rowH}
              stroke={COLOR_BORDER}
              strokeWidth="0.5"
            />
            <text
              x={x + pad}
              y={ry + 15}
              fontFamily={FONT}
              fontSize="8"
              fontWeight="600"
              fill={COLOR_LABEL}
              letterSpacing="0.5"
            >
              {row.label}
            </text>
            <text
              x={x + pad}
              y={ry + 28}
              fontFamily={FONT}
              fontSize="10"
              fontWeight="700"
              fill={COLOR_VALUE}
            >
              {row.value}
            </text>
          </g>
        );
      })}

      {/* Profile color swatch */}
      {config.profileColorHex && (
        <rect
          x={x + width - pad - 18}
          y={blockY + headerH + 4}
          width={14}
          height={14}
          rx="2"
          fill={config.profileColorHex}
          stroke={COLOR_BORDER}
          strokeWidth="0.7"
        />
      )}
    </g>
  );
}
