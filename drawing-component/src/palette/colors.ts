import type { ProfilePalette, GlassPalette } from '../types';

// ─── Color Utilities ──────────────────────────────────────────────────────────

function hexToRgb(hex: string): { r: number; g: number; b: number } {
  const clean = hex.trim().replace('#', '');
  if (!/^[0-9a-fA-F]{6}$/.test(clean)) {
    return { r: 242, g: 239, b: 232 };
  }
  return {
    r: parseInt(clean.slice(0, 2), 16),
    g: parseInt(clean.slice(2, 4), 16),
    b: parseInt(clean.slice(4, 6), 16),
  };
}

function rgbToHex(r: number, g: number, b: number): string {
  return '#' + [r, g, b]
    .map((v) => Math.round(Math.max(0, Math.min(255, v))).toString(16).padStart(2, '0'))
    .join('');
}

function mixColor(hex: string, targetHex: string, amount: number): string {
  const base = hexToRgb(hex);
  const target = hexToRgb(targetHex);
  return rgbToHex(
    base.r + (target.r - base.r) * amount,
    base.g + (target.g - base.g) * amount,
    base.b + (target.b - base.b) * amount,
  );
}

// ─── Profile Palette ─────────────────────────────────────────────────────────

export function buildProfilePalette(colorHex: string): ProfilePalette {
  return {
    base: colorHex,
    light: mixColor(colorHex, '#ffffff', 0.6),
    mid: mixColor(colorHex, '#c9cdd2', 0.15),
    dark: mixColor(colorHex, '#2f343a', 0.2),
    shadow: mixColor(colorHex, '#000000', 0.3),
  };
}

// ─── Glass Palette ────────────────────────────────────────────────────────────

export function buildGlassPalette(): GlassPalette {
  return {
    fill: '#cce8f0',
    stroke: '#5a9eb5',
    reflection: '#e8f6fa',
  };
}

// ─── CSS-ready gradient stops ─────────────────────────────────────────────────

export function profileGradientStops(palette: ProfilePalette): Array<{ offset: string; color: string }> {
  return [
    { offset: '0%', color: palette.light },
    { offset: '40%', color: palette.base },
    { offset: '80%', color: palette.mid },
    { offset: '100%', color: palette.dark },
  ];
}
