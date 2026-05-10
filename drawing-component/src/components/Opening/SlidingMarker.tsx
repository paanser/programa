import type { LeafGeometry } from '../../types';

interface SlidingMarkerProps {
  leaf: LeafGeometry;
  direction: 1 | -1;
  label: number;
}

export function SlidingMarker({ leaf, direction, label }: SlidingMarkerProps) {
  const { outer } = leaf;
  const cx = outer.x + outer.width / 2;
  const cy = outer.y + outer.height / 2;

  const arrowW = Math.min(outer.width * 0.65, 90);
  const arrowH = 22;
  const bodyLen = arrowW * 0.6;
  const halfH = arrowH / 2;

  const tailX = cx - (direction * arrowW) / 2;
  const bodyX = tailX + direction * bodyLen;
  const tipX = tailX + direction * arrowW;

  const points = [
    `${tailX},${cy - halfH}`,
    `${bodyX},${cy - halfH}`,
    `${tipX},${cy}`,
    `${bodyX},${cy + halfH}`,
    `${tailX},${cy + halfH}`,
  ].join(' ');

  return (
    <g role="presentation">
      <polygon
        points={points}
        fill="rgba(255,255,255,0.92)"
        stroke="rgba(90,100,110,0.65)"
        strokeWidth="0.9"
      />
      <text
        x={cx}
        y={cy + 5}
        textAnchor="middle"
        fontFamily="'Courier New', Courier, monospace"
        fontSize="13"
        fontWeight="700"
        fill="#2a2a2a"
      >
        {label}
      </text>
    </g>
  );
}
