// Serializes the SVG element rendered into the given container to a string
export function getSvgString(container: HTMLElement): string {
  const svg = container.querySelector('svg');
  if (!svg) return '';
  const serializer = new XMLSerializer();
  return serializer.serializeToString(svg);
}
