import type { LeafGeometry } from '../../types';

interface TiltMarkProps {
  leaf: LeafGeometry;
}

// UNE ISO 4157 tilt-turn symbol: diamond (rhombus) centered in the leaf
export function TiltMark({ leaf }: TiltMarkProps) {
  const { outer } = leaf;
  const cx = outer.x + outer.width / 2;
  const cy = outer.y + outer.height / 2;

  const dw = Math.min(outer.width * 0.28, 36);
  const dh = Math.min(outer.height * 0.22, 28);

  // Diamond points: top, right, bottom, left
  const points = [
    `${cx},${cy - dh}`,
    `${cx + dw},${cy}`,
    `${cx},${cy + dh}`,
    `${cx - dw},${cy}`,
  ].join(' ');

  // Horizontal centerline through diamond
  const lineY = cy;

  return (
    <g role="presentation">
      {/* Tilt-turn diamond */}
      <polygon
        points={points}
        fill="rgba(255,255,255,0.85)"
        stroke="rgba(60,80,100,0.7)"
        strokeWidth="1"
      />
      {/* Inner diamond (half-size) for tilt indicator */}
      <polygon
        points={[
          `${cx},${cy - dh * 0.45}`,
          `${cx + dw * 0.45},${cy}`,
          `${cx},${cy + dh * 0.45}`,
          `${cx - dw * 0.45},${cy}`,
        ].join(' ')}
        fill="none"
        stroke="rgba(60,80,100,0.45)"
        strokeWidth="0.6"
        strokeDasharray="3 2"
      />
      {/* Horizontal axis line */}
      <line
        x1={cx - dw - 6}
        y1={lineY}
        x2={cx + dw + 6}
        y2={lineY}
        stroke="rgba(60,80,100,0.35)"
        strokeWidth="0.55"
        strokeDasharray="4 3"
      />
    </g>
  );
}
