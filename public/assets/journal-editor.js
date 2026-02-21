(function (global) {
  'use strict';

  function escapeHtml(value) {
    return value
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function decodeHtmlEntities(value) {
    var current = value;
    for (var i = 0; i < 3; i++) {
      var textarea = document.createElement('textarea');
      textarea.innerHTML = current;
      var decoded = textarea.value;
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
      .replace(/(#[A-Za-z0-9_-]+)/g, '<span class="token-hashtag">$1</span>')
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

    function render() {
      var text = textarea.value || '';
      var decoded = decodeHtmlEntities(text);
      if (decoded !== text) {
        textarea.value = decoded;
        text = decoded;
      }
      var lines = text.replace(/\r\n?/g, '\n').split('\n');
      code.innerHTML = lines.map(highlightLine).join('\n');
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
