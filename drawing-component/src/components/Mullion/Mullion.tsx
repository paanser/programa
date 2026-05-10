interface MullionProps {
  x: number;
  y: number;
  width: number;
  height: number;
  profileStroke: string;
}

export function Mullion({ x, y, width, height, profileStroke }: MullionProps) {
  return (
    <g role="presentation">
      {/* Mullion fill */}
      <rect
        x={x}
        y={y}
        width={width}
        height={height}
        fill="url(#wd-frame-fill)"
        stroke={profileStroke}
        strokeWidth="0.7"
      />
      {/* Center axis line */}
      <line
        x1={x + width / 2}
        y1={y + 4}
        x2={x + width / 2}
        y2={y + height - 4}
        stroke={profileStroke}
        strokeWidth="0.4"
        strokeOpacity="0.4"
        strokeDasharray="4 3"
      />
    </g>
  );
}
