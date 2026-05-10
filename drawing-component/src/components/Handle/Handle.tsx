import type { LeafGeometry } from '../../types';
import type { HingeSide } from '../../geometry/opening';

interface HandleProps {
  leaf: LeafGeometry;
  side: HingeSide;
}

export function Handle({ leaf, side }: HandleProps) {
  const { outer } = leaf;
  const hw = 5;
  const hh = 28;
  const hx = side === 'left'
    ? outer.x + outer.width - hw - 6
    : outer.x + 6;
  const hy = outer.y + (outer.height / 2) - hh / 2;
  const armX = hx + hw / 2;
  const armY = hy + hh / 2;

  return (
    <g role="presentation">
      {/* Handle body */}
      <rect
        x={hx}
        y={hy}
        width={hw}
        height={hh}
        rx="2"
        fill="rgba(255,255,255,0.95)"
        stroke="rgba(90,100,110,0.6)"
        strokeWidth="0.8"
      />
      {/* Handle arm (T-shape) */}
      <line
        x1={armX}
        y1={armY - 3}
        x2={armX}
        y2={armY + 3}
        stroke="rgba(90,100,110,0.5)"
        strokeWidth="0.7"
      />
      {/* Screw dots */}
      <circle cx={hx + hw / 2} cy={hy + 6} r="1.2" fill="rgba(120,130,140,0.6)" />
      <circle cx={hx + hw / 2} cy={hy + hh - 6} r="1.2" fill="rgba(120,130,140,0.6)" />
    </g>
  );
}
