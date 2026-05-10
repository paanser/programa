import { DimensionLine } from './DimensionLine';

interface HorizontalDimensionProps {
  frameX: number;
  frameY: number;
  frameWidth: number;
  widthMm: number;
  // Optional per-leaf widths
  leafWidths?: { x: number; width: number; label: string }[];
  offsetBelow?: number;  // how far below the frame to place the line
}

export function HorizontalDimension({
  frameX,
  frameY,
  frameWidth,
  widthMm,
  leafWidths = [],
  offsetBelow = 32,
}: HorizontalDimensionProps) {
  const dimY = frameY + offsetBelow;

  return (
    <g role="presentation">
      {/* Per-leaf sub-dimensions */}
      {leafWidths.length > 1 && leafWidths.map((lw, i) => (
        <DimensionLine
          key={i}
          x1={lw.x}
          y1={dimY - 14}
          x2={lw.x + lw.width}
          y2={dimY - 14}
          label={lw.label}
          fontSize={9}
        />
      ))}
      {/* Total width dimension */}
      <DimensionLine
        x1={frameX}
        y1={dimY}
        x2={frameX + frameWidth}
        y2={dimY}
        label={`L=${widthMm}`}
        fontSize={11}
      />
    </g>
  );
}
