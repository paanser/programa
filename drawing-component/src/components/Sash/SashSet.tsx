import type { DrawingContext } from '../../types';
import { Sash } from './Sash';
import { Mullion } from '../Mullion/Mullion';

interface SashSetProps {
  ctx: DrawingContext;
  profileStroke: string;
}

export function SashSet({ ctx, profileStroke }: SashSetProps) {
  const { leaves, mullionWidth, frame, config } = ctx;
  const isFixed = config.systemType === 'fijo';

  return (
    <g role="presentation">
      {leaves.map((leaf) => (
        <Sash
          key={leaf.index}
          leaf={leaf}
          isFixed={isFixed}
          profileStroke={profileStroke}
        />
      ))}
      {/* Meeting rails between leaves */}
      {!isFixed && leaves.slice(0, -1).map((leaf) => {
        const railX = leaf.outer.x + leaf.outer.width;
        return (
          <Mullion
            key={`mullion-${leaf.index}`}
            x={railX}
            y={frame.inner.y}
            width={mullionWidth}
            height={frame.inner.height}
            profileStroke={profileStroke}
          />
        );
      })}
    </g>
  );
}
