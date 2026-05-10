import { createRoot } from 'react-dom/client';
import { createElement } from 'react';
import ReactDOM from 'react-dom';
import type { WindowConfig } from './types';
import { WindowDrawing } from './components/WindowDrawing';
import { getSvgString } from './export';

// Internal registry: container → React root
const roots = new WeakMap<HTMLElement, ReturnType<typeof createRoot>>();

function render(container: HTMLElement, config: WindowConfig): string {
  let root = roots.get(container);
  if (!root) {
    root = createRoot(container);
    roots.set(container, root);
  }
  // flushSync forces React to render synchronously so getSvgString captures the result immediately
  ReactDOM.flushSync(() => {
    root!.render(createElement(WindowDrawing, { config }));
  });
  return getSvgString(container);
}

function getSvg(container: HTMLElement): string {
  return getSvgString(container);
}

// Expose global API
(window as unknown as Record<string, unknown>).WindowDrawing = { render, getSvg };
