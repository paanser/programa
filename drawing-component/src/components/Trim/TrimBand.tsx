import type { FrameGeometry } from '../../types';

interface TrimBandProps {
  frame: FrameGeometry;
  trimOffset: number;
  profileStroke: string;
}

export function TrimBand({ frame, trimOffset, profileStroke }: TrimBandProps) {
  if (trimOffset <= 0) return null;

  const { outer } = frame;
  const tx = outer.x - trimOffset;
  const ty = outer.y - trimOffset;
  const tw = outer.width + trimOffset * 2;
  const th = outer.height + trimOffset * 2;

  return (
    <g role="presentation">
      {/* Trim band outer edge */}
      <rect
        x={tx}
        y={ty}
        width={tw}
        height={th}
        fill="none"
        stroke={profileStroke}
        strokeWidth="0.7"
        strokeOpacity="0.45"
        strokeDasharray="6 4"
      />
      {/* Trim band fill strip (thin band between outer trim and frame outer) */}
      <rect
        x={tx}
        y={ty}
        width={tw}
        height={th}
        fill="rgba(200,210,220,0.08)"
        stroke="none"
      />
    </g>
  );
}
