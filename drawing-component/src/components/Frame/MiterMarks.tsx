import type { Rect } from '../../types';

interface MiterMarksProps {
  outer: Rect;
  inset?: number;
}

export function MiterMarks({ outer, inset = 18 }: MiterMarksProps) {
  const { x, y, width, height } = outer;
  const style = {
    stroke: 'rgba(80, 90, 100, 0.55)',
    strokeWidth: 1.0,
    strokeLinecap: 'round' as const,
    fill: 'none',
  };

  return (
    <g role="presentation">
      {/* Top-left */}
      <line x1={x} y1={y + inset} x2={x + inset} y2={y} {...style} />
      {/* Top-right */}
      <line x1={x + width - inset} y1={y} x2={x + width} y2={y + inset} {...style} />
      {/* Bottom-left */}
      <line x1={x} y1={y + height - inset} x2={x + inset} y2={y + height} {...style} />
      {/* Bottom-right */}
      <line x1={x + width - inset} y1={y + height} x2={x + width} y2={y + height - inset} {...style} />
    </g>
  );
}
