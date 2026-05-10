import type { LeafGeometry, FrameGeometry, WindowConfig } from '../types';

// ─── Profile Thickness Constants (in mm, real-world) ─────────────────────────

const SASH_DEPTH_MM = 55;     // Sash/leaf profile depth (mm)
const GLASS_OFFSET_MM = 12;   // Glass setback from sash inner edge (mm)

// Meeting rail widths (mm) — differ by system type
const MULLION_SLIDING_MM = 80;
const MULLION_CASEMENT_MM = 50;

// ─── Leaf Geometry Builder ────────────────────────────────────────────────────

export function buildLeafGeometries(
  frame: FrameGeometry,
  config: WindowConfig,
): { leaves: LeafGeometry[]; mullionWidth: number } {
  const { systemType, leaves: leafCount } = config;

  // For fixed windows, the single "leaf" is the entire inner frame area
  if (systemType === 'fijo') {
    const sashDepth = SASH_DEPTH_MM * getScale(frame);
    const glassOff = GLASS_OFFSET_MM * getScale(frame);
    const inner = frame.inner;

    const outer = inner;
    const sashInner = {
      x: inner.x + sashDepth,
      y: inner.y + sashDepth,
      width: inner.width - sashDepth * 2,
      height: inner.height - sashDepth * 2,
    };
    const glassRect = {
      x: sashInner.x + glassOff,
      y: sashInner.y + glassOff,
      width: sashInner.width - glassOff * 2,
      height: sashInner.height - glassOff * 2,
    };

    return {
      leaves: [{ index: 0, outer, inner: sashInner, glassRect }],
      mullionWidth: 0,
    };
  }

  const mullionMm = systemType === 'corredera' ? MULLION_SLIDING_MM : MULLION_CASEMENT_MM;
  const mullionWidth = mullionMm * getScale(frame);
  const sashDepth = SASH_DEPTH_MM * getScale(frame);
  const glassOff = GLASS_OFFSET_MM * getScale(frame);

  const totalMullionW = mullionWidth * Math.max(0, leafCount - 1);
  const slotWidth = (frame.inner.width - totalMullionW) / leafCount;

  const leaves: LeafGeometry[] = [];
  for (let i = 0; i < leafCount; i++) {
    const outerX = frame.inner.x + i * (slotWidth + mullionWidth);
    const outerY = frame.inner.y;
    const outer = {
      x: outerX,
      y: outerY,
      width: slotWidth,
      height: frame.inner.height,
    };
    const sashInner = {
      x: outerX + sashDepth,
      y: outerY + sashDepth,
      width: slotWidth - sashDepth * 2,
      height: frame.inner.height - sashDepth * 2,
    };
    const glassRect = {
      x: sashInner.x + glassOff,
      y: sashInner.y + glassOff,
      width: sashInner.width - glassOff * 2,
      height: sashInner.height - glassOff * 2,
    };
    leaves.push({ index: i, outer, inner: sashInner, glassRect });
  }

  return { leaves, mullionWidth };
}

// Helper: extract scale from frame geometry
function getScale(frame: FrameGeometry): number {
  // Derive scale from the relationship between outer dimensions and profile depth
  // profileDepth is already in viewBox units = FRAME_DEPTH_MM * scale
  // We don't store scale in FrameGeometry, so we compute it here
  // profileDepth / FRAME_DEPTH_MM
  return frame.profileDepth / 70; // 70 = FRAME_DEPTH_MM
}

// Re-export for use in components
export { MULLION_SLIDING_MM, MULLION_CASEMENT_MM };
