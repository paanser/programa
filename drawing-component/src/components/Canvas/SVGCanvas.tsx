import type { ReactNode } from 'react';
import { VIEWBOX } from '../../geometry/viewport';
import type { ProfilePalette, GlassPalette } from '../../types';
import { Defs } from './Defs';

interface SVGCanvasProps {
  profile: ProfilePalette;
  glass: GlassPalette;
  children: ReactNode;
}

export function SVGCanvas({ profile, glass, children }: SVGCanvasProps) {
  return (
    <svg
      viewBox={VIEWBOX}
      role="img"
      aria-label="Dibujo técnico del cerramiento"
      xmlns="http://www.w3.org/2000/svg"
      style={{ width: '100%', height: '100%', display: 'block' }}
    >
      <Defs profile={profile} glass={glass} />

      {/* White sheet background */}
      <rect x="0" y="0" width="1400" height="900" fill="#ffffff" />

      {/* Subtle grid lines for technical appearance */}
      <rect x="0" y="0" width="1400" height="900" fill="none" stroke="#e8ecf0" strokeWidth="0.3" />

      {children}
    </svg>
  );
}
