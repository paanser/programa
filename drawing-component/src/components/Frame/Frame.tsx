import type { FrameGeometry } from '../../types';

interface FrameProps {
  frame: FrameGeometry;
  profileColorHex: string;
}

export function Frame({ frame, profileColorHex }: FrameProps) {
  const { outer, inner } = frame;
  const profileStroke = profileColorHex === '#f2efe8' ? '#b0a898' : '#4a5260';

  return (
    <g role="presentation">
      {/* Outer frame fill — aluminum profile */}
      <rect
        x={outer.x}
        y={outer.y}
        width={outer.width}
        height={outer.height}
        fill="url(#wd-frame-fill)"
        stroke={profileStroke}
        strokeWidth="1.2"
      />
      {/* Inner edge — sight-line of the frame */}
      <rect
        x={inner.x}
        y={inner.y}
        width={inner.width}
        height={inner.height}
        fill="none"
        stroke={profileStroke}
        strokeWidth="0.7"
        strokeOpacity="0.6"
      />
      {/* Profile depth lines — cross-section hint at the four sides */}
      <line x1={outer.x} y1={outer.y} x2={inner.x} y2={inner.y} stroke={profileStroke} strokeWidth="0.5" strokeOpacity="0.4" />
      <line x1={outer.x + outer.width} y1={outer.y} x2={inner.x + inner.width} y2={inner.y} stroke={profileStroke} strokeWidth="0.5" strokeOpacity="0.4" />
      <line x1={outer.x} y1={outer.y + outer.height} x2={inner.x} y2={inner.y + inner.height} stroke={profileStroke} strokeWidth="0.5" strokeOpacity="0.4" />
      <line x1={outer.x + outer.width} y1={outer.y + outer.height} x2={inner.x + inner.width} y2={inner.y + inner.height} stroke={profileStroke} strokeWidth="0.5" strokeOpacity="0.4" />
    </g>
  );
}
