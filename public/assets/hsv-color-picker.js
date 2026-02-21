(function (global) {
  'use strict';

  function clamp(n, min, max) {
    return Math.min(max, Math.max(min, n));
  }

  function hsvToHex(h, s, v) {
    var c = v * s;
    var x = c * (1 - Math.abs(((h / 60) % 2) - 1));
    var m = v - c;
    var r = 0, g = 0, b = 0;

    if (h < 60) { r = c; g = x; }
    else if (h < 120) { r = x; g = c; }
    else if (h < 180) { g = c; b = x; }
    else if (h < 240) { g = x; b = c; }
    else if (h < 300) { r = x; b = c; }
    else { r = c; b = x; }

    var toHex = function (n) {
      return Math.round((n + m) * 255).toString(16).padStart(2, '0');
    };

    return '#' + toHex(r) + toHex(g) + toHex(b);
  }

  function hexToHsv(hex) {
    var h = (hex || '#1e1f23').replace('#', '');
    if (!/^[0-9a-fA-F]{6}$/.test(h)) h = '1e1f23';

    var r = parseInt(h.slice(0, 2), 16) / 255;
    var g = parseInt(h.slice(2, 4), 16) / 255;
    var b = parseInt(h.slice(4, 6), 16) / 255;

    var max = Math.max(r, g, b);
    var min = Math.min(r, g, b);
    var d = max - min;
    var hue = 0;

    if (d !== 0) {
      if (max === r) hue = ((g - b) / d) % 6;
      else if (max === g) hue = (b - r) / d + 2;
      else hue = (r - g) / d + 4;
      hue = Math.round(hue * 60);
      if (hue < 0) hue += 360;
    }

    var sat = max === 0 ? 0 : d / max;
    var val = max;
    return { h: hue, s: sat, v: val };
  }

  function initPicker(root) {
    if (!root || root.__hsvInitialized) return;
    var inputId = root.getAttribute('data-hsv-input');
    if (!inputId) return;
    var input = document.getElementById(inputId);
    if (!input) return;

    root.classList.add('hsv-picker-ready');
    root.innerHTML =
      '<div class="hsv-sv" role="slider" aria-label="Saturation and value"><span class="hsv-sv-cursor"></span></div>' +
      '<input type="range" class="hsv-hue" min="0" max="360" step="1" aria-label="Hue">' +
      '<div class="hsv-meta"><span class="hsv-preview"></span><code class="hsv-hex"></code></div>';

    var sv = root.querySelector('.hsv-sv');
    var cursor = root.querySelector('.hsv-sv-cursor');
    var hueInput = root.querySelector('.hsv-hue');
    var preview = root.querySelector('.hsv-preview');
    var hexText = root.querySelector('.hsv-hex');

    var state = hexToHsv(input.value);

    function render() {
      var hex = hsvToHex(state.h, state.s, state.v);
      input.value = hex;
      sv.style.background =
        'linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, hsl(' + state.h + ',100%,50%))';
      hueInput.value = String(state.h);
      cursor.style.left = (state.s * 100) + '%';
      cursor.style.top = ((1 - state.v) * 100) + '%';
      preview.style.background = hex;
      hexText.textContent = hex;
    }

    function setHex(hex) {
      state = hexToHsv(hex);
      render();
    }

    function applySvFromEvent(event) {
      var rect = sv.getBoundingClientRect();
      var x = clamp((event.clientX - rect.left) / rect.width, 0, 1);
      var y = clamp((event.clientY - rect.top) / rect.height, 0, 1);
      state.s = x;
      state.v = 1 - y;
      render();
    }

    var dragging = false;
    sv.addEventListener('pointerdown', function (event) {
      dragging = true;
      sv.setPointerCapture(event.pointerId);
      applySvFromEvent(event);
    });
    sv.addEventListener('pointermove', function (event) {
      if (!dragging) return;
      applySvFromEvent(event);
    });
    sv.addEventListener('pointerup', function (event) {
      dragging = false;
      sv.releasePointerCapture(event.pointerId);
    });

    hueInput.addEventListener('input', function () {
      state.h = clamp(parseInt(hueInput.value || '0', 10), 0, 360);
      render();
    });

    root.__hsvPicker = { setHex: setHex, getHex: function () { return input.value; } };
    root.__hsvInitialized = true;
    setHex(input.value);
  }

  function initHsvColorPickers(scope) {
    var root = scope || document;
    root.querySelectorAll('.hsv-picker[data-hsv-input]').forEach(function (picker) {
      initPicker(picker);
      var input = document.getElementById(picker.getAttribute('data-hsv-input'));
      if (input && picker.__hsvPicker) {
        picker.__hsvPicker.setHex(input.value);
      }
    });
  }

  global.initHsvColorPickers = initHsvColorPickers;
})(window);
