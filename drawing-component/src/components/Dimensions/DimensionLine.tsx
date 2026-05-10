interface DimensionLineProps {
  x1: number;
  y1: number;
  x2: number;
  y2: number;
  label: string;
  offset?: number;       // perpendicular offset for the dimension line from baseline
  tickSize?: number;     // length of end ticks
  fontSize?: number;
  color?: string;
}

// Renders a single annotated dimension line with arrow ticks at both ends
export function DimensionLine({
  x1, y1, x2, y2,
  label,
  tickSize = 6,
  fontSize = 11,
  color = 'rgba(50,60,80,0.75)',
}: DimensionLineProps) {
  const isHorizontal = Math.abs(y2 - y1) < Math.abs(x2 - x1);
  const mx = (x1 + x2) / 2;
  const my = (y1 + y2) / 2;

  // Perpendicular tick direction
  const dx = x2 - x1;
  const dy = y2 - y1;
  const len = Math.sqrt(dx * dx + dy * dy) || 1;
  const px = -dy / len;
  const py = dx / len;

  // Arrowhead points (open tick style)
  function tickPoints(ax: number, ay: number, dirX: number, dirY: number): string {
    const tip = { x: ax, y: ay };
    const left = { x: ax - dirX * tickSize * 0.7 + py * tickSize * 0.4, y: ay - dirY * tickSize * 0.7 + px * tickSize * 0.4 };
    const right = { x: ax - dirX * tickSize * 0.7 - py * tickSize * 0.4, y: ay - dirY * tickSize * 0.7 - px * tickSize * 0.4 };
    return `M ${left.x},${left.y} L ${tip.x},${tip.y} L ${right.x},${right.y}`;
  }

  const normDX = dx / len;
  const normDY = dy / len;

  return (
    <g role="presentation">
      {/* Main dimension line */}
      <line x1={x1} y1={y1} x2={x2} y2={y2} stroke={color} strokeWidth="0.8" />
      {/* Tick at start */}
      <path d={tickPoints(x1, y1, normDX, normDY)} fill="none" stroke={color} strokeWidth="0.8" strokeLinecap="round" />
      {/* Tick at end */}
      <path d={tickPoints(x2, y2, -normDX, -normDY)} fill="none" stroke={color} strokeWidth="0.8" strokeLinecap="round" />
      {/* Extension ticks perpendicular */}
      <line x1={x1} y1={y1} x2={x1 + px * tickSize} y2={y1 + py * tickSize} stroke={color} strokeWidth="0.6" strokeOpacity="0.6" />
      <line x1={x2} y1={y2} x2={x2 + px * tickSize} y2={y2 + py * tickSize} stroke={color} strokeWidth="0.6" strokeOpacity="0.6" />
      {/* Label */}
      <text
        x={mx + (isHorizontal ? 0 : -6)}
        y={my + (isHorizontal ? -5 : 4)}
        textAnchor="middle"
        fontFamily="'Courier New', Courier, monospace"
        fontSize={fontSize}
        fontWeight="600"
        fill={color}
        transform={isHorizontal ? undefined : `rotate(-90, ${mx}, ${my})`}
      >
        {label}
      </text>
    </g>
  );
}
