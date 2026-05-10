import type { Rect } from '../../types';

interface GlassPaneProps {
  rect: Rect;
  showReflection?: boolean;
}

export function GlassPane({ rect, showReflection = true }: GlassPaneProps) {
  const { x, y, width, height } = rect;

  return (
    <g role="presentation">
      {/* Glass fill */}
      <rect
        x={x}
        y={y}
        width={width}
        height={height}
        fill="url(#wd-glass-fill)"
        stroke="#4a8da8"
        strokeWidth="0.7"
      />
      {/* Reflection band (diagonal highlight, top-left) */}
      {showReflection && (
        <rect
          x={x}
          y={y}
          width={width}
          height={height}
          fill="url(#wd-glass-reflection)"
        />
      )}
    </g>
  );
}
