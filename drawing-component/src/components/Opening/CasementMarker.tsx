import type { LeafGeometry } from '../../types';
import type { HingeSide } from '../../geometry/opening';
import { buildSwingArcPath } from '../../geometry/opening';

interface CasementMarkerProps {
  leaf: LeafGeometry;
  hingeSide: HingeSide;
  label: number;
  showHandle?: boolean;
}

export function CasementMarker({ leaf, hingeSide, label, showHandle = false }: CasementMarkerProps) {
  const { outer } = leaf;
  const arcPath = buildSwingArcPath(leaf, hingeSide);

  const hingeX = hingeSide === 'left'
    ? outer.x + outer.width * 0.1
    : outer.x + outer.width * 0.9;
  const hingeTopY = outer.y + outer.height * 0.15;
  const hingeBottomY = outer.y + outer.height - outer.height * 0.12;

  // Leaf number badge (bottom-center of leaf)
  const badgeCX = outer.x + outer.width / 2;
  const badgeCY = outer.y + outer.height - 14;
  const badgeR = 9;

  // Handle position (opposite side from hinge)
  const handleX = hingeSide === 'left'
    ? outer.x + outer.width - 10
    : outer.x + 10;
  const handleY = outer.y + outer.height / 2;

  return (
    <g role="presentation">
      {/* 90° swing arc */}
      <path
        d={arcPath}
        fill="none"
        stroke="rgba(60,80,100,0.55)"
        strokeWidth="0.9"
        strokeDasharray="5 3"
      />
      {/* Hinge line */}
      <line
        x1={hingeX}
        y1={hingeTopY}
        x2={hingeX}
        y2={hingeBottomY}
        stroke="rgba(60,80,100,0.75)"
        strokeWidth="1.8"
        strokeLinecap="round"
      />
      {/* Hinge node (pivot point) */}
      <circle
        cx={hingeX}
        cy={hingeBottomY}
        r="3"
        fill="rgba(60,80,100,0.7)"
      />
      {/* Handle stub */}
      {showHandle && (
        <rect
          x={hingeSide === 'left' ? handleX - 4 : handleX - 1}
          y={handleY - 14}
          width="5"
          height="28"
          rx="2"
          fill="rgba(255,255,255,0.9)"
          stroke="rgba(90,100,110,0.6)"
          strokeWidth="0.8"
        />
      )}
      {/* Leaf number badge */}
      <circle
        cx={badgeCX}
        cy={badgeCY}
        r={badgeR}
        fill="rgba(255,255,255,0.88)"
        stroke="rgba(90,100,110,0.55)"
        strokeWidth="0.8"
      />
      <text
        x={badgeCX}
        y={badgeCY + 4.5}
        textAnchor="middle"
        fontFamily="'Courier New', Courier, monospace"
        fontSize="10"
        fontWeight="700"
        fill="#2a2a2a"
      >
        {label}
      </text>
    </g>
  );
}
