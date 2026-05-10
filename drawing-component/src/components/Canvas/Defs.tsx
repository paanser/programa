import type { ProfilePalette, GlassPalette } from '../../types';
import { profileGradientStops } from '../../palette/colors';

interface DefsProps {
  profile: ProfilePalette;
  glass: GlassPalette;
}

export function Defs({ profile, glass }: DefsProps) {
  const stops = profileGradientStops(profile);

  return (
    <defs>
      {/* Frame / sash gradient — diagonal, light to dark */}
      <linearGradient id="wd-frame-fill" x1="0%" y1="0%" x2="100%" y2="100%">
        {stops.map((s) => (
          <stop key={s.offset} offset={s.offset} stopColor={s.color} />
        ))}
      </linearGradient>

      {/* Glass fill gradient — subtle top-light */}
      <linearGradient id="wd-glass-fill" x1="0%" y1="0%" x2="0%" y2="100%">
        <stop offset="0%" stopColor={glass.reflection} stopOpacity="0.9" />
        <stop offset="30%" stopColor={glass.fill} stopOpacity="0.85" />
        <stop offset="100%" stopColor={glass.fill} stopOpacity="0.7" />
      </linearGradient>

      {/* Glass reflection band */}
      <linearGradient id="wd-glass-reflection" x1="0%" y1="0%" x2="100%" y2="0%">
        <stop offset="0%" stopColor="white" stopOpacity="0.0" />
        <stop offset="30%" stopColor="white" stopOpacity="0.18" />
        <stop offset="60%" stopColor="white" stopOpacity="0.0" />
      </linearGradient>

      {/* Arrow marker for dimension lines */}
      <marker id="wd-dim-arrow-start" markerWidth="8" markerHeight="8" refX="4" refY="3" orient="auto">
        <path d="M 8 0 L 0 3 L 8 6" fill="none" stroke="#333" strokeWidth="0.8" />
      </marker>
      <marker id="wd-dim-arrow-end" markerWidth="8" markerHeight="8" refX="4" refY="3" orient="auto-start-reverse">
        <path d="M 8 0 L 0 3 L 8 6" fill="none" stroke="#333" strokeWidth="0.8" />
      </marker>

      {/* Hinge dot */}
      <marker id="wd-hinge-dot" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
        <circle cx="3" cy="3" r="2.5" fill="#3a4048" />
      </marker>
    </defs>
  );
}
