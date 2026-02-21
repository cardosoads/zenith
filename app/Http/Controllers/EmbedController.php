<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class EmbedController extends Controller
{
    public function script(): Response
    {
        $content = <<<'JS'
(function () {
  const ALLOWED_THEME = ['light', 'dark', 'auto'];
  const ALLOWED_ACCENT = ['sky', 'honey', 'green'];
  const ALLOWED_DENSITY = ['comfortable', 'medium'];
  const ALLOWED_PRESET = ['clean', 'contrast', 'soft', 'editorial'];

  const scripts = document.querySelectorAll('script[data-za-provider][data-za-agenda]');

  scripts.forEach((script) => {
    const provider = script.dataset.zaProvider;
    const agenda = script.dataset.zaAgenda;

    if (!provider || !agenda) {
      return;
    }

    const theme = ALLOWED_THEME.includes(script.dataset.zaTheme || '') ? script.dataset.zaTheme : null;
    const accent = ALLOWED_ACCENT.includes(script.dataset.zaAccent || '') ? script.dataset.zaAccent : null;
    const density = ALLOWED_DENSITY.includes(script.dataset.zaDensity || '') ? script.dataset.zaDensity : null;
    const preset = ALLOWED_PRESET.includes(script.dataset.zaPreset || '') ? script.dataset.zaPreset : null;

    const query = new URLSearchParams();
    if (theme) query.set('theme', theme);
    if (accent) query.set('accent', accent);
    if (density) query.set('density', density);
    if (preset) query.set('preset', preset);

    const iframe = document.createElement('iframe');
    const queryString = query.toString();
    iframe.src = `/w/${provider}/${agenda}${queryString ? `?${queryString}` : ''}`;
    iframe.style.width = '100%';

    const height = Number(script.dataset.zaHeight || 0);
    iframe.style.minHeight = `${height > 0 ? height : 680}px`;

    iframe.style.border = '0';
    iframe.style.borderRadius = '0';
    iframe.setAttribute('loading', 'lazy');
    iframe.setAttribute('title', 'Zenith Widget');

    script.parentNode.insertBefore(iframe, script.nextSibling);
  });
})();
JS;

        return response($content, 200)->header('Content-Type', 'application/javascript');
    }
}
