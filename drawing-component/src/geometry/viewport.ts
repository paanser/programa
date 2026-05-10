import type { Viewport, Rect, WindowConfig } from '../types';

// ─── ViewBox Constants ────────────────────────────────────────────────────────

const VIEWBOX_W = 1400;
const VIEWBOX_H = 900;

// Labels panel (right side)
const LABELS_W = 320;

// Drawing area boundaries
const DRAWING_MARGIN_TOP = 55;
const DRAWING_MARGIN_BOTTOM = 130;  // Space for horizontal dimension line
const DRAWING_MARGIN_LEFT = 60;
const DRAWING_MARGIN_RIGHT = 110;   // Space for vertical dimension line

const DRAWING_W = VIEWBOX_W - LABELS_W - DRAWING_MARGIN_LEFT - DRAWING_MARGIN_RIGHT;
const DRAWING_H = VIEWBOX_H - DRAWING_MARGIN_TOP - DRAWING_MARGIN_BOTTOM;

// ─── Viewport Builder ─────────────────────────────────────────────────────────

export function buildViewport(config: WindowConfig): Viewport {
  const { widthMm, heightMm } = config;

  // Scale to fit within drawing area, preserving aspect ratio
  const scale = Math.min(DRAWING_W / widthMm, DRAWING_H / heightMm);

  const scaledW = widthMm * scale;
  const scaledH = heightMm * scale;

  // Center the frame within the drawing area
  const drawingAreaX = DRAWING_MARGIN_LEFT;
  const drawingAreaY = DRAWING_MARGIN_TOP;

  const frameX = drawingAreaX + (DRAWING_W - scaledW) / 2;
  const frameY = drawingAreaY + (DRAWING_H - scaledH) / 2;

  const drawingArea: Rect = {
    x: drawingAreaX,
    y: drawingAreaY,
    width: DRAWING_W,
    height: DRAWING_H,
  };

  const labelsArea: Rect = {
    x: VIEWBOX_W - LABELS_W,
    y: 0,
    width: LABELS_W,
    height: VIEWBOX_H,
  };

  return {
    viewBoxW: VIEWBOX_W,
    viewBoxH: VIEWBOX_H,
    drawingArea,
    labelsArea,
    scale,
    frameOrigin: { x: frameX, y: frameY },
  };
}

// ─── Exported Constants ───────────────────────────────────────────────────────

export const VIEWBOX = `0 0 ${VIEWBOX_W} ${VIEWBOX_H}`;
