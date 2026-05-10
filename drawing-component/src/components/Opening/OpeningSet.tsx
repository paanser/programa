import type { DrawingContext } from '../../types';
import {
  getSlidingDirection,
  getCasementHingeSide,
  getTiltTurnLeafIndex,
  getCasementHandleLeafIndex,
} from '../../geometry/opening';
import { SlidingMarker } from './SlidingMarker';
import { CasementMarker } from './CasementMarker';
import { TiltMark } from './TiltMark';
import { Handle } from '../Handle/Handle';

interface OpeningSetProps {
  ctx: DrawingContext;
}

export function OpeningSet({ ctx }: OpeningSetProps) {
  const { config, leaves } = ctx;
  const { systemType, openingType, leaves: leafCount } = config;

  if (systemType === 'fijo') return null;

  if (systemType === 'corredera') {
    return (
      <g role="presentation">
        {leaves.map((leaf) => {
          const direction = getSlidingDirection(openingType, leaf.index, leafCount);
          return (
            <SlidingMarker
              key={leaf.index}
              leaf={leaf}
              direction={direction}
              label={leaf.index + 1}
            />
          );
        })}
      </g>
    );
  }

  if (systemType === 'abatible' || systemType === 'puerta') {
    const handleLeafIdx = getCasementHandleLeafIndex(config);
    return (
      <g role="presentation">
        {leaves.map((leaf) => {
          const hingeSide = getCasementHingeSide(openingType, leaf.index, leafCount);
          const isHandleLeaf = leaf.index === handleLeafIdx;
          return (
            <g key={leaf.index}>
              <CasementMarker
                leaf={leaf}
                hingeSide={hingeSide}
                label={leaf.index + 1}
                showHandle={isHandleLeaf}
              />
              {isHandleLeaf && (
                <Handle leaf={leaf} side={hingeSide === 'left' ? 'right' : 'left'} />
              )}
            </g>
          );
        })}
      </g>
    );
  }

  if (systemType === 'oscilobatiente') {
    const tiltLeafIdx = getTiltTurnLeafIndex(config);
    const handleLeafIdx = getCasementHandleLeafIndex(config);
    return (
      <g role="presentation">
        {leaves.map((leaf) => {
          const hingeSide = getCasementHingeSide(openingType, leaf.index, leafCount);
          const isTiltLeaf = leaf.index === tiltLeafIdx;
          const isHandleLeaf = leaf.index === handleLeafIdx;
          return (
            <g key={leaf.index}>
              <CasementMarker
                leaf={leaf}
                hingeSide={hingeSide}
                label={leaf.index + 1}
                showHandle={isHandleLeaf}
              />
              {isTiltLeaf && <TiltMark leaf={leaf} />}
              {isHandleLeaf && (
                <Handle leaf={leaf} side={hingeSide === 'left' ? 'right' : 'left'} />
              )}
            </g>
          );
        })}
      </g>
    );
  }

  return null;
}
