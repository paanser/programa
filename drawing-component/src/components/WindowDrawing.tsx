import type { WindowConfig, DrawingContext } from '../types';
import { buildViewport } from '../geometry/viewport';
import { buildFrameGeometry, getTrimOffset } from '../geometry/frame';
import { buildLeafGeometries } from '../geometry/leaves';
import { buildProfilePalette, buildGlassPalette } from '../palette/colors';
import { SVGCanvas } from './Canvas/SVGCanvas';
import { Frame } from './Frame/Frame';
import { MiterMarks } from './Frame/MiterMarks';
import { SashSet } from './Sash/SashSet';
import { OpeningSet } from './Opening/OpeningSet';
import { TrimBand } from './Trim/TrimBand';
import { HorizontalDimension } from './Dimensions/HorizontalDimension';
import { VerticalDimension } from './Dimensions/VerticalDimension';
import { TechnicalLabels } from './Labels/TechnicalLabels';

interface WindowDrawingProps {
  config: WindowConfig;
}

export function WindowDrawing({ config }: WindowDrawingProps) {
  const viewport = buildViewport(config);
  const frame = buildFrameGeometry(viewport, config);
  const { leaves, mullionWidth } = buildLeafGeometries(frame, config);
  const trimOffset = getTrimOffset(config.trimSizeMm, viewport.scale);
  const usesMiterCut = config.frameCutType === 'mitered';

  const ctx: DrawingContext = {
    config,
    viewport,
    frame,
    leaves,
    mullionWidth,
    trimOffset,
    usesMiterCut,
  };

  const profile = buildProfilePalette(config.profileColorHex);
  const glass = buildGlassPalette();

  const profileStroke = config.profileColorHex === '#f2efe8' ? '#b0a898' : '#4a5260';

  // Leaf widths for sub-dimensions (only when multiple leaves)
  const leafWidths = leaves.length > 1
    ? leaves.map((leaf) => ({
        x: leaf.outer.x,
        width: leaf.outer.width,
        label: `${Math.round(leaf.outer.width / viewport.scale)}`,
      }))
    : [];

  return (
    <SVGCanvas profile={profile} glass={glass}>
      {/* Trim band (tapajuntas exterior) */}
      {trimOffset > 0 && (
        <TrimBand frame={frame} trimOffset={trimOffset} profileStroke={profileStroke} />
      )}

      {/* Miter corner marks */}
      {usesMiterCut && (
        <MiterMarks outer={frame.outer} />
      )}

      {/* Main frame */}
      <Frame frame={frame} profileColorHex={config.profileColorHex} />

      {/* Leaves (sashes) and mullions */}
      <SashSet ctx={ctx} profileStroke={profileStroke} />

      {/* Opening markers (arrows, arcs, hinges) */}
      <OpeningSet ctx={ctx} />

      {/* Dimension lines */}
      <HorizontalDimension
        frameX={frame.outer.x}
        frameY={frame.outer.y + frame.outer.height}
        frameWidth={frame.outer.width}
        widthMm={config.widthMm}
        leafWidths={leafWidths}
        offsetBelow={28}
      />
      <VerticalDimension
        frameX={frame.outer.x + frame.outer.width}
        frameY={frame.outer.y}
        frameHeight={frame.outer.height}
        heightMm={config.heightMm}
        offsetRight={28}
      />

      {/* Technical info panel */}
      <TechnicalLabels ctx={ctx} />
    </SVGCanvas>
  );
}
