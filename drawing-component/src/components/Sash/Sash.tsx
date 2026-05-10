import type { LeafGeometry } from '../../types';
import { GlassPane } from './GlassPane';

interface SashProps {
  leaf: LeafGeometry;
  isFixed?: boolean;
  profileStroke: string;
}

export function Sash({ leaf, isFixed = false, profileStroke }: SashProps) {
  const { outer, inner, glassRect } = leaf;

  if (isFixed) {
    // Fixed lite: no sash profile, just a glass pane inside the frame inner
    return <GlassPane rect={glassRect} />;
  }

  return (
    <g role="presentation">
      {/* Sash outer profile */}
      <rect
        x={outer.x}
        y={outer.y}
        width={outer.width}
        height={outer.height}
        fill="url(#wd-frame-fill)"
        stroke={profileStroke}
        strokeWidth="0.9"
      />
      {/* Sash inner edge (sight line) */}
      <rect
        x={inner.x}
        y={inner.y}
        width={inner.width}
        height={inner.height}
        fill="none"
        stroke={profileStroke}
        strokeWidth="0.55"
        strokeOpacity="0.55"
      />
      {/* Glass pane */}
      <GlassPane rect={glassRect} />
    </g>
  );
}
