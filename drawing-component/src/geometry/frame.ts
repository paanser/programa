import type { FrameGeometry, Viewport, WindowConfig } from '../types';

// ─── Profile Thickness Constants (in mm, real-world) ─────────────────────────

const FRAME_DEPTH_MM = 70;   // Outer frame profile depth (typical aluminum)

// ─── Frame Geometry Builder ───────────────────────────────────────────────────

export function buildFrameGeometry(viewport: Viewport, config: WindowConfig): FrameGeometry {
  const { scale, frameOrigin } = viewport;
  const { widthMm, heightMm } = config;

  const profileDepth = FRAME_DEPTH_MM * scale;

  const outer = {
    x: frameOrigin.x,
    y: frameOrigin.y,
    width: widthMm * scale,
    height: heightMm * scale,
  };

  const inner = {
    x: outer.x + profileDepth,
    y: outer.y + profileDepth,
    width: outer.width - profileDepth * 2,
    height: outer.height - profileDepth * 2,
  };

  return { outer, inner, profileDepth };
}

// ─── Trim Offset Calculation ─────────────────────────────────────────────────

export function getTrimOffset(trimSizeMm: number, scale: number): number {
  if (trimSizeMm >= 80) return 8 * scale;
  if (trimSizeMm >= 60) return 6 * scale;
  if (trimSizeMm >= 40) return 4 * scale;
  return 0;
}
