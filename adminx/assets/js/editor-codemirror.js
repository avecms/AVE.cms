/**
 * Shared CodeMirror adapter for /adminx.
 *
 * Usage:
 *   <textarea data-code-editor data-mode="smartymixed" data-height="520"></textarea>
 */
(function (window, document) {
  'use strict';

  var Adminx = window.Adminx || (window.Adminx = {});

  function boolAttr(el, name) {
    var value = el.getAttribute(name);
    return value === '' || value === '1' || value === 'true';
  }

  function numberAttr(el, name, fallback) {
    var value = parseInt(el.getAttribute(name), 10);
    return isNaN(value) || value <= 0 ? fallback : value;
  }

  function normalizeMode(mode) {
    if (!mode) { return 'htmlmixed'; }
    if (mode === 'html') { return 'htmlmixed'; }
    if (mode === 'php') { return 'application/x-httpd-php'; }
    if (mode === 'smarty') { return 'smartymixed'; }
    if (mode === 'js') { return 'text/javascript'; }
    if (mode === 'css') { return 'text/css'; }
    if (mode === 'sql') { return 'text/x-sql'; }
    return mode;
  }

  Adminx.CodeEditor = {
    instances: [],

    init: function (root) {
      root = root || document;
      if (!window.CodeMirror) {
        return;
      }
      var nodes = root.querySelectorAll('textarea[data-code-editor]:not([data-code-editor-ready])');
      Array.prototype.forEach.call(nodes, this.create.bind(this));
    },

    create: function (textarea) {
      var height = numberAttr(textarea, 'data-height', 420);
      var theme = textarea.getAttribute('data-theme') ||
        (document.documentElement.getAttribute('data-theme') === 'dark' ? 'material-ocean' : 'default');
      var editor = window.CodeMirror.fromTextArea(textarea, {
        mode: normalizeMode(textarea.getAttribute('data-mode')),
        theme: theme,
        lineNumbers: !boolAttr(textarea, 'data-no-lines'),
        lineWrapping: !boolAttr(textarea, 'data-no-wrap'),
        matchBrackets: true,
        autoCloseTags: true,
        styleActiveLine: true,
        indentUnit: numberAttr(textarea, 'data-indent', 4),
        indentWithTabs: true,
        readOnly: textarea.readOnly || textarea.disabled || boolAttr(textarea, 'data-readonly'),
        extraKeys: {
          'Ctrl-S': function (cm) { Adminx.CodeEditor.saveForm(cm); },
          'Cmd-S': function (cm) { Adminx.CodeEditor.saveForm(cm); },
          'F11': function (cm) { Adminx.CodeEditor.toggleFullscreen(cm); },
          'Cmd-F11': function (cm) { Adminx.CodeEditor.toggleFullscreen(cm); },
          'Esc': function (cm) { Adminx.CodeEditor.closeFullscreen(cm); }
        }
      });

      editor.setSize(textarea.getAttribute('data-width') || '100%', height);
      editor.on('change', function (cm) {
        cm.save();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
      });

      textarea.setAttribute('data-code-editor-ready', '1');
      textarea._adminxCodeMirror = editor;
      this.instances.push(editor);
      setTimeout(function () { editor.refresh(); }, 0);
    },

    saveForm: function (cm) {
      var form, stayButton;
      cm.save();
      form = cm.getTextArea().closest('form');
      if (!form) { return; }
      stayButton = form.querySelector('[data-code-save-stay]');
      if (stayButton && !stayButton.disabled) {
        stayButton.click();
        return;
      }
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    },

    toggleFullscreen: function (cm) {
      var wrapper = cm.getWrapperElement();
      if (wrapper.classList.contains('CodeMirror-fullscreen')) {
        this.closeFullscreen(cm);
        return;
      }
      wrapper.classList.add('CodeMirror-fullscreen');
      document.body.style.overflow = 'hidden';
      cm.refresh();
    },

    closeFullscreen: function (cm) {
      var wrapper = cm.getWrapperElement();
      if (!wrapper.classList.contains('CodeMirror-fullscreen')) { return; }
      wrapper.classList.remove('CodeMirror-fullscreen');
      document.body.style.overflow = '';
      cm.refresh();
    },

    refreshAll: function () {
      this.instances.forEach(function (editor) { editor.refresh(); });
    },

    insert: function (textarea, value) {
      if (!textarea || !value || textarea.disabled || textarea.readOnly) { return; }
      if (textarea._adminxCodeMirror) {
        textarea._adminxCodeMirror.replaceSelection(value);
        textarea._adminxCodeMirror.focus();
        textarea._adminxCodeMirror.save();
      } else {
        var start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : textarea.value.length;
        var end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : start;
        textarea.value = textarea.value.slice(0, start) + value + textarea.value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + value.length;
        textarea.focus();
      }
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
    },

    insertTag: function (button) {
      var palette = button.closest('[data-code-editor-tags]');
      var form = palette ? palette.closest('form') : null;
      var name = palette ? palette.getAttribute('data-code-editor-tags') : '';
      var textarea = form && name ? form.elements[name] : null;
      this.insert(textarea, button.getAttribute('data-code-editor-tag') || '');
    },

    // Синхронизировать значения всех редакторов в их textarea перед отправкой формы.
    syncAll: function (root) {
      var scope = root && root.querySelectorAll ? root : document;
      this.instances.forEach(function (editor) {
        var ta = editor.getTextArea && editor.getTextArea();
        if (ta && (scope === document || scope.contains(ta))) { editor.save(); }
      });
    }
  };

  document.addEventListener('adminx:content-ready', function (e) {
    Adminx.CodeEditor.init(e.detail && e.detail.root ? e.detail.root : document);
  });
  document.addEventListener('click', function (e) {
    var button = e.target.closest('[data-code-editor-tag]');
    if (!button) { return; }
    e.preventDefault();
    Adminx.CodeEditor.insertTag(button);
  });
  window.addEventListener('resize', function () { Adminx.CodeEditor.refreshAll(); });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { Adminx.CodeEditor.init(); });
  } else {
    Adminx.CodeEditor.init();
  }
})(window, document);
