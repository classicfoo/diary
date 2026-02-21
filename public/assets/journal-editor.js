(function (global) {
  'use strict';

  function escapeHtml(value) {
    return value
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function decodeHtmlEntities(value) {
    var current = value || '';

    function decodeOnce(input) {
      return input
        .replace(/&#(\d+);/g, function (_, num) {
          var code = parseInt(num, 10);
          return Number.isFinite(code) ? String.fromCodePoint(code) : _;
        })
        .replace(/&#x([0-9a-fA-F]+);/g, function (_, hex) {
          var code = parseInt(hex, 16);
          return Number.isFinite(code) ? String.fromCodePoint(code) : _;
        })
        .replace(/&quot;/g, '"')
        .replace(/&apos;/g, "'")
        .replace(/&#039;/g, "'")
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&');
    }

    for (var i = 0; i < 4; i++) {
      var decoded = decodeOnce(current);
      if (decoded === current) break;
      current = decoded;
    }

    return current;
  }

  function highlightLine(line) {
    var className = 'code-line';
    if (/^\s*[-*]\s\[[ xX]\]/.test(line)) className += ' code-line-task';
    else if (/^\s*(note|idea|thought):/i.test(line)) className += ' code-line-note';
    else if (/^\s*(milestone|goal):/i.test(line)) className += ' code-line-milestone';
    else if (/^\s*#+\s+/.test(line)) className += ' code-line-heading';
    else if (/^\s*(done|completed):/i.test(line)) className += ' code-line-done';

    var html = escapeHtml(line)
      .replace(/(^|[^\\w&])(#[A-Za-z][A-Za-z0-9_-]*)/g, '$1<span class="token-hashtag">$2</span>')
      .replace(/\b(\d{1,2}\s(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s\d{4})\b/gi, '<span class="token-date">$1</span>')
      .replace(/\b((?:https?:\/\/|www\.)[^\s<]+)/gi, '<span class="token-link">$1</span>');

    return '<span class="' + className + '">' + (html || '&nbsp;') + '</span>';
  }

  function initDiaryEntryEditor(wrapper, onChange) {
    if (!wrapper) return null;

    var textarea = wrapper.querySelector('.prism-editor__textarea');
    var code = wrapper.querySelector('code');
    var callback = typeof onChange === 'function' ? onChange : function () {};

    if (!textarea || !code) return null;

    function syncHeight() {
      textarea.style.height = 'auto';
      textarea.style.height = Math.max(textarea.scrollHeight, 320) + 'px';
    }

    function normalizeTextareaValue() {
      var start = textarea.selectionStart;
      var end = textarea.selectionEnd;
      var raw = textarea.value || '';
      var decoded = decodeHtmlEntities(raw);
      if (decoded !== raw) {
        textarea.value = decoded;
        try {
          textarea.setSelectionRange(start, end);
        } catch (e) {
          // Ignore selection restore failures on unsupported inputs.
        }
      }
    }

    function render() {
      normalizeTextareaValue();
      var text = textarea.value || '';
      var lines = text.replace(/\r\n?/g, '\n').split('\n');
      // Each line is a block element; avoid adding extra newline text nodes that
      // can desync caret/line alignment after pressing Enter.
      code.innerHTML = lines.map(highlightLine).join('');
      syncHeight();
    }

    function handleInput() {
      render();
      callback();
    }

    render();

    textarea.addEventListener('input', handleInput);
    textarea.addEventListener('change', handleInput);
    textarea.addEventListener('scroll', function () {
      var preview = wrapper.querySelector('.prism-editor__preview');
      if (preview) {
        preview.scrollTop = textarea.scrollTop;
        preview.scrollLeft = textarea.scrollLeft;
      }
    });

    return {
      render: render,
      textarea: textarea
    };
  }

  global.initDiaryEntryEditor = initDiaryEntryEditor;
})(window);
