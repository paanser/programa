import type { LeafGeometry, WindowConfig } from '../types';
import type { OpeningType } from '../types';

// ─── Sliding Direction ────────────────────────────────────────────────────────

export function getSlidingDirection(
  openingType: OpeningType,
  index: number,
  leafCount: number,
): 1 | -1 {
  if (openingType === 'central') {
    return index < leafCount / 2 ? 1 : -1;
  }
  return openingType === 'derecha' ? 1 : -1;
}

// ─── Casement Hinge Side ──────────────────────────────────────────────────────

export type HingeSide = 'left' | 'right';

export function getCasementHingeSide(
  openingType: OpeningType,
  index: number,
  leafCount: number,
): HingeSide {
  if (openingType === 'central') {
    return index < leafCount / 2 ? 'left' : 'right';
  }
  return openingType === 'derecha' ? 'left' : 'right';
}

// ─── Tilt-Turn Leaf Index ─────────────────────────────────────────────────────

export function getTiltTurnLeafIndex(config: WindowConfig): number {
  if (config.systemType !== 'oscilobatiente') return -1;
  if (config.leaves <= 1) return 0;
  return config.tiltTurnLeaf === 'derecha' ? config.leaves - 1 : 0;
}

// ─── Casement Handle Leaf Index ───────────────────────────────────────────────

export function getCasementHandleLeafIndex(config: WindowConfig): number {
  if (config.systemType === 'oscilobatiente') {
    return getTiltTurnLeafIndex(config);
  }
  if (config.openingType === 'derecha') return config.leaves - 1;
  return 0;
}

// ─── Casement Handle Side ─────────────────────────────────────────────────────

export function getCasementHandleSide(config: WindowConfig): HingeSide {
  if (config.openingType === 'central') return 'right';
  return config.openingType === 'derecha' ? 'right' : 'left';
}

// ─── Swing Arc Path ───────────────────────────────────────────────────────────

// Returns an SVG arc path string for a 90° casement swing arc
export function buildSwingArcPath(leaf: LeafGeometry, hingeSide: HingeSide): string {
  const hingeX = hingeSide === 'left'
    ? leaf.outer.x + leaf.outer.width * 0.1
    : leaf.outer.x + leaf.outer.width * 0.9;

  const bottomY = leaf.outer.y + leaf.outer.height - leaf.outer.height * 0.12;
  const topY = leaf.outer.y + leaf.outer.height * 0.15;

  // Radius = horizontal distance from hinge to opposite edge
  const radius = hingeSide === 'left'
    ? leaf.outer.width * 0.82
    : leaf.outer.width * 0.82;

  // Arc end point (horizontal, at 90° from the vertical hinge line)
  const endX = hingeSide === 'left'
    ? hingeX + radius
    : hingeX - radius;
  const endY = bottomY;

  return `M ${hingeX} ${topY} L ${hingeX} ${bottomY} A ${radius} ${radius} 0 0 ${hingeSide === 'left' ? 1 : 0} ${endX} ${endY}`;
}
