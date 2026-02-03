/**
 * PlantWallK Logo Manager
 * Rotates logos on refresh and adapts SVG color to theme.
 */
(function() {
  const LOGO_COUNT = 6;
  const KEY = 'plantwallk_logo_idx';
  
  // 1. Cycle Logo Index
  let idx = 0;
  try {
    const stored = localStorage.getItem(KEY);
    // Cycle 1..6
    idx = stored ? (parseInt(stored, 10) % LOGO_COUNT) + 1 : 1;
    localStorage.setItem(KEY, idx);
  } catch(e) {
    idx = 1;
  }

  const file = `./images/logo_${idx}.svg`;

  // 2. Update Favicon
  const iconLink = document.querySelector('link[rel="icon"]');
  if (iconLink) iconLink.href = file;

  // 3. Inline and Adapt Logo
  document.addEventListener('DOMContentLoaded', () => {
    const targets = document.querySelectorAll('.site-logo__img img, .mobile-logo__img');
    
    targets.forEach(img => {
      fetch(file)
        .then(res => {
          if (!res.ok) throw new Error('Logo fetch failed');
          return res.text();
        })
        .then(svgText => {
          // Parse SVG
          const parser = new DOMParser();
          const doc = parser.parseFromString(svgText, 'image/svg+xml');
          const svg = doc.querySelector('svg');

          if (svg) {
            // Transfer classes and dimensions
            svg.setAttribute('class', img.getAttribute('class'));
            svg.setAttribute('width', img.getAttribute('width') || '160');
            svg.setAttribute('height', img.getAttribute('height') || '160');

            // Force theme color (CSS variable adaptation)
            // We apply it to the SVG root and all paths to ensure visibility
            svg.style.fill = 'var(--text-primary)';
            svg.querySelectorAll('path, rect, circle').forEach(el => {
              el.style.fill = 'var(--text-primary)';
            });

            // Replace
            img.replaceWith(svg);
          }
        })
        .catch(() => {
          // Fallback: do nothing, keep original img
          console.debug('Logo rotation fallback enabled.');
        });
    });
  });
})();