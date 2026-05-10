// ─── Window Configuration ────────────────────────────────────────────────────

export type SystemType = 'corredera' | 'abatible' | 'oscilobatiente' | 'fijo' | 'puerta';
export type OpeningType = 'izquierda' | 'derecha' | 'central';
export type FrameCutType = 'recto' | 'mitered';
export type TiltTurnLeaf = 'izquierda' | 'derecha' | 'unica';

export interface WindowConfig {
  systemType: SystemType;
  openingType: OpeningType;
  widthMm: number;
  heightMm: number;
  leaves: number;
  trimSizeMm: number;
  frameCutType: FrameCutType;
  tiltTurnLeaf?: TiltTurnLeaf;
  profileColorHex: string;
  profileColorName: string;
  glassDescription: string;
  carpentryModel: string;
  carpentryReference: string;
  quantity: number;
  glassPanels: number;
}

// ─── Geometry Primitives ─────────────────────────────────────────────────────

export interface Rect {
  x: number;
  y: number;
  width: number;
  height: number;
}

export interface Point {
  x: number;
  y: number;
}

// ─── Coordinate System & Viewport ────────────────────────────────────────────

export interface Viewport {
  viewBoxW: number;     // Total viewBox width (1400)
  viewBoxH: number;     // Total viewBox height (900)
  drawingArea: Rect;    // Zone where the technical drawing lives
  labelsArea: Rect;     // Zone for the title/info panel (right side)
  scale: number;        // mm → viewBox units conversion factor
  frameOrigin: Point;   // Top-left corner of the outer frame in viewBox coords
}

// ─── Frame Geometry ───────────────────────────────────────────────────────────

export interface FrameGeometry {
  outer: Rect;            // Outer edge of the aluminum frame
  inner: Rect;            // Inner opening (where leaves live)
  profileDepth: number;   // Profile thickness in viewBox units
}

// ─── Leaf/Sash Geometry ───────────────────────────────────────────────────────

export interface LeafGeometry {
  index: number;
  outer: Rect;      // Outer edge of the sash profile
  inner: Rect;      // Inner edge of the sash (glass sight line)
  glassRect: Rect;  // Glass pane area
}

// ─── Drawing Context ──────────────────────────────────────────────────────────

export interface DrawingContext {
  config: WindowConfig;
  viewport: Viewport;
  frame: FrameGeometry;
  leaves: LeafGeometry[];
  mullionWidth: number;   // Width of the meeting rail between leaves (viewBox units)
  trimOffset: number;     // Trim band offset outside frame (viewBox units)
  usesMiterCut: boolean;
}

// ─── Color Palette ────────────────────────────────────────────────────────────

export interface ProfilePalette {
  base: string;
  light: string;
  mid: string;
  dark: string;
  shadow: string;
}

export interface GlassPalette {
  fill: string;
  stroke: string;
  reflection: string;
}
