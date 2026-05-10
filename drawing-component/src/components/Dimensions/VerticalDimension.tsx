import { DimensionLine } from './DimensionLine';

interface VerticalDimensionProps {
  frameX: number;
  frameY: number;
  frameHeight: number;
  heightMm: number;
  offsetRight?: number;  // how far right of the frame to place the line
}

export function VerticalDimension({
  frameX,
  frameY,
  frameHeight,
  heightMm,
  offsetRight = 32,
}: VerticalDimensionProps) {
  const dimX = frameX + offsetRight;

  return (
    <g role="presentation">
      <DimensionLine
        x1={dimX}
        y1={frameY}
        x2={dimX}
        y2={frameY + frameHeight}
        label={`H=${heightMm}`}
        fontSize={11}
      />
    </g>
  );
}
