/**
 * JS раздела «Медиа»: файловый браузер, upload и простой crop-редактор.
 */
(function (window, document) {
  'use strict';

  var Adminx = window.Adminx || (window.Adminx = {});

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
  }

  function submitAction(url, data, onDone) {
    var form = document.getElementById('mediaActionForm');
    var fd;
    if (!form) { return; }
    fd = new FormData(form);
    Object.keys(data || {}).forEach(function (key) {
      fd.set(key, data[key]);
    });
    Adminx.Loader.show();
    Adminx.Ajax.post(url, fd).then(function (payload) {
      Adminx.Loader.hide();
      var d = payload.data || {};
      if (d.success && typeof onDone === 'function') {
        onDone(d);
      }
      Adminx.Ajax.handle(payload);
      if (d.success && !d.redirect) {
        window.location.reload();
      }
    }).catch(function () {
      Adminx.Loader.hide();
      Adminx.Toast.show('Ошибка сети', 'error');
    });
  }

  Adminx.Media = {
    pendingPreview: '',

    init: function () {
      var self = this;
      this.bindUpload();
      this.bindCrop();
      this.bindLightbox();
      this.bindLivePreview();

      document.addEventListener('click', function (e) {
        var folder = e.target.closest('[data-media-folder]');
        var rename = e.target.closest('[data-media-rename]');
        var del = e.target.closest('[data-media-delete]');
        var emptyFolder = e.target.closest('[data-media-empty-folder]');
        var clearThumbs = e.target.closest('[data-media-clear-thumbs]');
        var copy = e.target.closest('[data-copy]');
        var webp = e.target.closest('[data-media-webp]');
        var zoom = e.target.closest('[data-media-result-zoom]');
        var audit = e.target.closest('[data-media-audit-run]');
        var trashRestore = e.target.closest('[data-media-trash-restore]');
        var trashPurge = e.target.closest('[data-media-trash-purge]');
        var trashClear = e.target.closest('[data-media-trash-clear]');

        if (audit) { self.runAudit(audit); return; }
        if (trashRestore) { self.restoreTrash(trashRestore); return; }
        if (trashPurge) { self.purgeTrash(trashPurge); return; }
        if (trashClear) { self.clearTrash(trashClear); return; }
        if (folder) { self.createFolder(); return; }
        if (rename) { self.rename(rename); return; }
        if (del) { self.remove(del); return; }
        if (emptyFolder) { self.emptyFolder(emptyFolder); return; }
        if (clearThumbs) { self.clearThumbnails(clearThumbs); return; }
        if (webp) { self.convertWebp(webp); return; }
        if (zoom) { e.preventDefault(); self.openResultLarge(); return; }
        if (copy) { self.copy(copy.getAttribute('data-copy')); return; }

        var presetsOpen = e.target.closest('[data-media-presets-open]');
        if (presetsOpen) { self.loadPresets(); return; }
        if (e.target.closest('[data-preset-save]')) { self.savePreset(); return; }
        if (e.target.closest('[data-preset-reset]')) { self.resetPresetForm(); return; }
        var presetEdit = e.target.closest('[data-preset-edit]');
        if (presetEdit) { self.editPreset(presetEdit.getAttribute('data-preset-edit')); return; }
        var presetDelete = e.target.closest('[data-preset-delete]');
        if (presetDelete) { self.deletePreset(presetDelete.getAttribute('data-preset-delete')); return; }
      });
    },

    base: function () {
      return Adminx.base();
    },

    runAudit: function (button) {
      var form = document.querySelector('[data-media-audit-form]');
      if (!form || button.disabled) { return; }
      button.disabled = true;
      button.classList.add('is-loading');
      button.innerHTML = '<i class="ti ti-loader-2"></i>Проверяем...';
      Adminx.Loader.show();
      Adminx.Ajax.post(form.action, new FormData(form)).then(function (payload) {
        Adminx.Loader.hide();
        var result = payload.data || {};
        if (!result.success) {
          button.disabled = false;
          button.classList.remove('is-loading');
        }
        Adminx.Ajax.handle(payload);
      }).catch(function () {
        Adminx.Loader.hide();
        button.disabled = false;
        button.classList.remove('is-loading');
        Adminx.Toast.show('Не удалось выполнить проверку медиа', 'error');
      });
    },

    presetData: null,

    loadPresets: function () {
      var self = this;
      Adminx.Loader.show();
      Adminx.Ajax.request(this.base() + '/media/presets').then(function (payload) {
        Adminx.Loader.hide();
        var r = payload.data || {};
        if (!r.success || !r.data) { Adminx.Toast.show(r.message || 'Не удалось загрузить виды', 'error'); return; }
        self.presetData = r.data;
        self.fillPresetSelects(r.data.modes || {}, r.data.formats || {});
        self.renderPresets(r.data.items || []);
      }).catch(function () { Adminx.Loader.hide(); Adminx.Toast.show('Ошибка сети', 'error'); });
    },

    fillPresetSelects: function (modes, formats) {
      var m = document.querySelector('[data-preset-modes]');
      var f = document.querySelector('[data-preset-formats]');
      if (m && !m.getAttribute('data-filled')) {
        m.innerHTML = Object.keys(modes).map(function (k) { return '<option value="' + esc(k) + '">' + esc(modes[k]) + '</option>'; }).join('');
        m.setAttribute('data-filled', '1');
      }
      if (f && !f.getAttribute('data-filled')) {
        f.innerHTML = Object.keys(formats).map(function (k) { return '<option value="' + esc(k) + '">' + esc(formats[k]) + '</option>'; }).join('');
        f.setAttribute('data-filled', '1');
      }
    },

    renderPresets: function (items) {
      var list = document.querySelector('[data-preset-list]');
      if (!list) { return; }
      if (!items.length) { list.innerHTML = '<div class="empty-state">Виды ещё не заданы. Добавьте первый в форме ниже.</div>'; return; }
      var canManage = this.presetData && this.presetData.can_manage;
      list.innerHTML = items.map(function (item) {
        return '<div class="media-preset-row">'
          + '<span class="icon-tile" style="--tile-bg:var(--blue-100);--tile-fg:var(--blue-600)"><i class="ti ti-aspect-ratio"></i></span>'
          + '<div class="media-preset-main"><div><b>' + esc(item.title) + '</b>'
          + (item.group_label ? ' <span class="badge badge-gray">' + esc(item.group_label) + '</span>' : '') + '</div>'
          + '<small>' + esc(item.mode_label) + ' · ' + item.width + '×' + item.height + (item.format !== 'original' ? ' · ' + esc(item.format) : '') + ' · q' + item.quality + (item.webp_twin ? ' · +WebP' : '') + '</small></div>'
          + (canManage ? '<div class="cluster"><button class="btn btn-ghost btn-icon btn-sm" type="button" data-preset-edit="' + item.id + '" data-tooltip="Изменить" aria-label="Изменить"><i class="ti ti-pencil"></i></button>'
          + '<button class="btn btn-ghost btn-icon btn-sm media-action-danger" type="button" data-preset-delete="' + item.id + '" data-tooltip="Удалить" aria-label="Удалить"><i class="ti ti-trash"></i></button></div>' : '')
          + '</div>';
      }).join('');
    },

    editPreset: function (id) {
      if (!this.presetData) { return; }
      var item = (this.presetData.items || []).filter(function (p) { return String(p.id) === String(id); })[0];
      var form = document.getElementById('mediaPresetForm');
      if (!item || !form) { return; }
      form.querySelector('[name="id"]').value = item.id;
      form.querySelector('[name="title"]').value = item.title;
      form.querySelector('[name="group_label"]').value = item.group_label || '';
      form.querySelector('[name="mode"]').value = item.mode;
      form.querySelector('[name="format"]').value = item.format;
      form.querySelector('[name="width"]').value = item.width;
      form.querySelector('[name="height"]').value = item.height;
      form.querySelector('[name="quality"]').value = item.quality;
      var twin = form.querySelector('[name="webp_twin"]');
      if (twin) { twin.checked = !!item.webp_twin; }
      form.querySelectorAll('[data-error]').forEach(function (el) { el.textContent = ''; });
    },

    resetPresetForm: function () {
      var form = document.getElementById('mediaPresetForm');
      if (!form) { return; }
      form.reset();
      form.querySelector('[name="id"]').value = '';
      form.querySelectorAll('[data-error]').forEach(function (el) { el.textContent = ''; });
    },

    savePreset: function () {
      var form = document.getElementById('mediaPresetForm');
      if (!form) { return; }
      var self = this;
      var id = (form.querySelector('[name="id"]').value || '').trim();
      var url = this.base() + '/media/presets' + (id ? '/' + id : '');
      form.querySelectorAll('[data-error]').forEach(function (el) { el.textContent = ''; });
      Adminx.Loader.show();
      Adminx.Ajax.post(url, new FormData(form)).then(function (payload) {
        Adminx.Loader.hide();
        var r = payload.data || {};
        if (!r.success) {
          if (r.errors) { Object.keys(r.errors).forEach(function (k) { var el = form.querySelector('[data-error="' + k + '"]'); if (el) { el.textContent = r.errors[k]; } }); }
          Adminx.Toast.show(r.message || 'Проверьте поля', 'error');
          return;
        }
        Adminx.Toast.show(r.message || 'Сохранено', 'success');
        self.resetPresetForm();
        self.loadPresets();
      }).catch(function () { Adminx.Loader.hide(); Adminx.Toast.show('Ошибка сети', 'error'); });
    },

    deletePreset: function (id) {
      var self = this;
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Удалить вид?',
        message: 'Он исчезнет из списка подгонки. Уже обрезанные картинки не изменятся.',
        confirmLabel: 'Удалить',
        onConfirm: function () {
          Adminx.Loader.show();
          Adminx.Ajax.post(self.base() + '/media/presets/' + id + '/delete').then(function (payload) {
            Adminx.Loader.hide();
            var r = payload.data || {};
            if (!r.success) { Adminx.Toast.show(r.message || 'Не удалось удалить', 'error'); return; }
            Adminx.Toast.show(r.message || 'Удалено', 'success');
            self.loadPresets();
          }).catch(function () { Adminx.Loader.hide(); Adminx.Toast.show('Ошибка сети', 'error'); });
        }
      });
    },

    modalInput: function (cfg, done) {
      var overlay = document.createElement('div');
      overlay.className = 'overlay';
      overlay.innerHTML =
        '<div class="modal media-prompt" role="dialog" aria-modal="true">' +
          '<div class="modal-header">' +
            '<span class="dialog-icon info"><i class="ti ti-pencil"></i></span>' +
            '<div style="flex:1"><h3>' + esc(cfg.title) + '</h3>' +
            '<p class="text-secondary" style="margin-top:4px">' + esc(cfg.message || '') + '</p></div>' +
            '<button class="modal-close" type="button" data-cancel aria-label="Закрыть"><i class="ti ti-x"></i></button>' +
          '</div>' +
          '<div class="modal-body"><input class="input" type="text" value="' + esc(cfg.value || '') + '" data-input></div>' +
          '<div class="modal-footer"><button class="btn btn-ghost" type="button" data-cancel>Отмена</button>' +
          '<button class="btn btn-primary" type="button" data-ok style="margin-left:auto">' + esc(cfg.ok || 'Сохранить') + '</button></div>' +
        '</div>';
      document.body.appendChild(overlay);
      requestAnimationFrame(function () { overlay.classList.add('show'); });
      var input = overlay.querySelector('[data-input]');
      var close = function () {
        overlay.classList.remove('show');
        setTimeout(function () { overlay.remove(); document.removeEventListener('keydown', onKey); }, 180);
      };
      var apply = function () {
        var value = (input.value || '').trim();
        close();
        if (value) { done(value); }
      };
      var onKey = function (e) {
        if (e.key === 'Escape') { close(); }
        if (e.key === 'Enter') { apply(); }
      };
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay || e.target.closest('[data-cancel]')) { close(); }
        if (e.target.closest('[data-ok]')) { apply(); }
      });
      document.addEventListener('keydown', onKey);
      input.focus();
      input.select();
    },

    createFolder: function () {
      var dir = document.querySelector('input[name="dir"]');
      var self = this;
      this.modalInput({
        title: 'Создать папку',
        message: 'Папка будет создана в текущем каталоге.',
        value: '',
        ok: 'Создать'
      }, function (name) {
        submitAction(self.base() + '/media/folders', {
          dir: dir ? dir.value : '/uploads',
          name: name
        });
      });
    },

    rename: function (button) {
      var self = this;
      var kind = button.getAttribute('data-kind') === 'folder' ? 'папку' : 'файл';
      this.modalInput({
        title: 'Переименовать ' + kind,
        message: button.getAttribute('data-path') || '',
        value: button.getAttribute('data-name') || '',
        ok: 'Переименовать'
      }, function (name) {
        submitAction(self.base() + '/media/rename', {
          path: button.getAttribute('data-path') || '',
          name: name
        });
      });
    },

    remove: function (button) {
      var self = this;
      var name = button.getAttribute('data-name') || button.getAttribute('data-path') || '';
      var kind = button.getAttribute('data-kind') === 'folder' ? 'папку и всё содержимое' : 'файл';
      var form = document.getElementById('mediaActionForm');
      if (!form) { return; }
      var fd = new FormData(form);
      fd.set('path', button.getAttribute('data-path') || '');
      Adminx.Loader.show();
      Adminx.Ajax.post(self.base() + '/media/delete-check', fd).then(function (payload) {
        Adminx.Loader.hide();
        var response = payload.data || {};
        if (!response.success) {
          Adminx.Toast.show(response.message || 'Не удалось проверить использование', 'error');
          return;
        }
        var usage = response.data && response.data.usage ? response.data.usage : { use_count: 0, uses: [] };
        var used = Number(usage.use_count || 0);
        var message = used > 0
          ? '«' + name + '» используется в ' + used + ' местах. Объект будет скрыт с сайта и перенесён в корзину, откуда его можно восстановить.'
          : '«' + name + '» будет перенесён в корзину и останется доступен для восстановления.';
        Adminx.Confirm.open({
          kind: used > 0 ? 'warning' : 'danger',
          title: used > 0 ? 'Объект используется' : 'Удалить ' + kind + '?',
          message: message,
          confirmLabel: 'В корзину',
          confirmClass: used > 0 ? 'btn-primary' : 'btn-danger',
          onConfirm: function () {
            submitAction(self.base() + '/media/delete', {
              path: button.getAttribute('data-path') || '',
              confirm_usage: used > 0 ? '1' : '0'
            });
          }
        });
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка проверки медиа', 'error');
      });
    },

    emptyFolder: function (button) {
      var self = this;
      var path = button.getAttribute('data-path') || '';
      var name = button.getAttribute('data-name') || path;
      var form = document.getElementById('mediaActionForm');
      if (!form) { return; }
      var fd = new FormData(form);
      fd.set('path', path);
      Adminx.Loader.show();
      Adminx.Ajax.post(self.base() + '/media/delete-check', fd).then(function (payload) {
        Adminx.Loader.hide();
        var response = payload.data || {};
        if (!response.success) {
          Adminx.Toast.show(response.message || 'Не удалось проверить содержимое папки', 'error');
          return;
        }
        var usage = response.data && response.data.usage ? response.data.usage : { use_count: 0 };
        var used = Number(usage.use_count || 0);
        Adminx.Confirm.open({
          kind: used > 0 ? 'warning' : 'danger',
          title: 'Очистить папку «' + name + '»?',
          message: used > 0
            ? 'Внутри есть файлы, используемые в ' + used + ' местах. Всё содержимое будет перенесено в корзину, сама папка останется.'
            : 'Все файлы и вложенные папки будут перенесены в корзину. Текущая папка останется на месте.',
          confirmLabel: 'Очистить папку',
          confirmClass: used > 0 ? 'btn-primary' : 'btn-danger',
          onConfirm: function () {
            submitAction(self.base() + '/media/empty-folder', {
              path: path,
              confirm_usage: used > 0 ? '1' : '0'
            });
          }
        });
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка проверки медиа', 'error');
      });
    },

    restoreTrash: function (button) {
      var self = this;
      Adminx.Confirm.open({
        kind: 'info',
        title: 'Восстановить объект?',
        message: '«' + (button.getAttribute('data-name') || '') + '» вернётся по исходному пути.',
        confirmLabel: 'Восстановить',
        onConfirm: function () {
          submitAction(self.base() + '/media/trash/' + encodeURIComponent(button.getAttribute('data-media-trash-restore') || '') + '/restore', {});
        }
      });
    },

    purgeTrash: function (button) {
      var self = this;
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Удалить окончательно?',
        message: '«' + (button.getAttribute('data-name') || '') + '» нельзя будет восстановить.',
        confirmLabel: 'Удалить навсегда',
        confirmClass: 'btn-danger',
        onConfirm: function () {
          submitAction(self.base() + '/media/trash/' + encodeURIComponent(button.getAttribute('data-media-trash-purge') || '') + '/purge', {});
        }
      });
    },

    clearTrash: function (button) {
      var self = this;
      if (self.clearingTrash) return;
      var items = Array.from(document.querySelectorAll('[data-media-trash-purge]'));
      if (!items.length) return;
      Adminx.Confirm.open({kind: 'danger', title: 'Очистить корзину медиа?',
        message: 'Все объекты, показанные в корзине, будут удалены окончательно. Восстановить их будет нельзя.',
        confirmLabel: 'Удалить навсегда', confirmClass: 'btn-danger', onConfirm: function () {
          self.clearingTrash = true;
          var done = 0, progress = document.querySelector('[data-media-trash-progress]');
          document.querySelectorAll('[data-media-trash-clear], [data-media-trash-purge], [data-media-trash-restore]').forEach(function (el) { el.disabled = true; });
          function step() {
            progress.textContent = done + ' / ' + items.length;
            if (done === items.length) { window.location.reload(); return Promise.resolve(); }
            var item = items[done];
            return Adminx.Ajax.post(self.base() + '/media/trash/' + encodeURIComponent(item.getAttribute('data-media-trash-purge')) + '/purge', new FormData(document.getElementById('mediaActionForm'))).then(function (payload) {
              var data = payload.data || {};
              if (!data.success) throw new Error(data.message || Adminx.tr('Не удалось очистить корзину'));
              item.closest('tr').remove(); done++; return step();
            });
          }
          step().catch(function (error) {
            self.clearingTrash = false;
            document.querySelectorAll('[data-media-trash-clear], [data-media-trash-purge], [data-media-trash-restore]').forEach(function (el) { el.disabled = false; });
            Adminx.Toast.show(error.message || 'Ошибка сети', 'error');
          });
        }});
    },

    clearThumbnails: function (button) {
      var self = this;
      var name = button.getAttribute('data-name') || button.getAttribute('data-path') || '';
      Adminx.Confirm.open({
        kind: 'warning',
        title: 'Удалить все превью?',
        message: 'Во всех вложенных папках «' + name + '» будут удалены только служебные каталоги превью. Исходные файлы останутся на месте.',
        confirmLabel: 'Удалить превью',
        confirmClass: 'btn-danger',
        onConfirm: function () {
          submitAction(self.base() + '/media/clear-thumbnails', {
            path: button.getAttribute('data-path') || ''
          });
        }
      });
    },

    copy: function (text) {
      if (!text) { return; }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () {
          Adminx.Toast.show('Путь скопирован', 'success');
        });
      } else {
        Adminx.Toast.show(text, 'info');
      }
    },

    bindUpload: function () {
      var form = document.getElementById('mediaUploadForm');
      if (!form) { return; }
      var input = form.querySelector('input[type="file"]');
      var drop = form.querySelector('[data-media-drop]');
      var trigger = document.querySelector('[data-media-upload-trigger]');
      var status = form.querySelector('[data-media-upload-status]');
      var self = this;
      var depth = 0;

      function setStatus(text, cls) {
        if (!status) { return; }
        status.textContent = text || '';
        status.className = cls ? 'is-' + cls : '';
      }

      function upload(files) {
        if (!files || !files.length) {
          setStatus('Файлы не выбраны', 'error');
          return;
        }
        var total = files.length;
        form.classList.add('is-loading');
        setStatus('Подготовка к загрузке: ' + total, 'progress');
        Adminx.Upload.files(self.base() + '/media/upload', files, function (chunk) {
          var fd = new FormData(form);
          fd.delete('files[]');
          chunk.forEach(function (file) { fd.append('files[]', file); });
          return fd;
        }, function (processed, count) {
          setStatus('Загружено: ' + processed + ' из ' + count, 'progress');
        }).then(function (result) {
          form.classList.remove('is-loading');
          setStatus('Загружено файлов: ' + result.files.length, 'success');
          Adminx.Toast.show('Загружено файлов: ' + result.files.length, 'success');
          window.location.href = self.base() + '/media?dir=' + encodeURIComponent(form.getAttribute('data-dir') || '/uploads');
        }).catch(function (error) {
          form.classList.remove('is-loading');
          var uploaded = error && error.uploaded ? error.uploaded.length : 0;
          var message = (error && error.message) || 'Ошибка сети';
          if (uploaded) { message += '. До ошибки загружено: ' + uploaded; }
          setStatus(message, 'error');
          Adminx.Toast.show(message, 'error');
        });
      }

      if (trigger) {
        trigger.addEventListener('click', function () { input.click(); });
      }
      drop.addEventListener('click', function () { input.click(); });
      input.addEventListener('change', function () { upload(input.files); });
      drop.addEventListener('dragenter', function (e) {
        e.preventDefault(); depth++; drop.classList.add('is-dragover'); setStatus('Отпустите файлы', 'progress');
      });
      drop.addEventListener('dragover', function (e) {
        e.preventDefault(); drop.classList.add('is-dragover');
      });
      drop.addEventListener('dragleave', function (e) {
        e.preventDefault(); depth = Math.max(0, depth - 1); if (depth === 0) { drop.classList.remove('is-dragover'); }
      });
      drop.addEventListener('drop', function (e) {
        e.preventDefault(); depth = 0; drop.classList.remove('is-dragover'); upload(e.dataTransfer ? e.dataTransfer.files : null);
      });
    },

    bindLightbox: function () {
      var self = this;
      var modal = document.querySelector('[data-media-lightbox]');
      if (!modal) { return; }
      this.lightbox = {
        modal: modal,
        image: modal.querySelector('[data-media-lightbox-image]'),
        title: modal.querySelector('[data-media-lightbox-title]'),
        meta: modal.querySelector('[data-media-lightbox-meta]')
      };

      document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-media-preview]');
        if (trigger) {
          e.preventDefault();
          e.stopPropagation();
          self.openLightbox(
            trigger.getAttribute('data-media-preview') || '',
            trigger.getAttribute('data-media-preview-name') || 'Предпросмотр',
            trigger.getAttribute('data-media-preview-meta') || ''
          );
          return;
        }
        if (e.target === modal || e.target.closest('[data-media-lightbox-close]')) {
          self.closeLightbox();
        }
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hasAttribute('hidden')) {
          self.closeLightbox();
        }
      });
      // Уходя со страницы с открытым превью — подчистить временный файл.
      window.addEventListener('pagehide', function () { self.clearPreview(true); });
    },

    openLightbox: function (url, title, meta) {
      var lb = this.lightbox;
      if (!lb) { return; }
      if (lb.image) { lb.image.src = url || ''; lb.image.alt = title || ''; }
      if (lb.title) { lb.title.textContent = title || 'Предпросмотр'; }
      if (lb.meta) { lb.meta.textContent = meta || ''; }
      lb.modal.removeAttribute('hidden');
      document.documentElement.classList.add('media-lightbox-open');
    },

    closeLightbox: function () {
      var lb = this.lightbox;
      if (!lb) { return; }
      lb.modal.setAttribute('hidden', 'hidden');
      if (lb.image) { lb.image.src = ''; }
      document.documentElement.classList.remove('media-lightbox-open');
    },

    // Встроенный «Результат»: живой серверный рендер по настройкам + выделению.
    bindLivePreview: function () {
      var self = this;
      var form = document.querySelector('.media-transform-form');
      var result = document.querySelector('[data-media-result]');
      if (!form || !result) { return; }
      this.result = {
        img: result.querySelector('[data-media-result-img]'),
        meta: result.querySelector('[data-media-result-meta]'),
        empty: result.querySelector('[data-media-result-empty]')
      };
      var timer = null;
      var onChange = function () {
        clearTimeout(timer);
        timer = setTimeout(function () { self.refreshResult(); }, 300);
      };
      form.addEventListener('change', onChange);
      form.addEventListener('input', onChange);
      // Первый рендер результата при открытии страницы.
      setTimeout(function () { self.refreshResult(); }, 60);
    },

    // Сгенерировать серверный результат по текущим настройкам и показать во встроенном блоке.
    refreshResult: function () {
      var self = this;
      var form = document.querySelector('.media-transform-form');
      if (!form || !this.result) { return; }
      var reqId = (this.resultReq = (this.resultReq || 0) + 1);
      Adminx.Ajax.post(this.base() + '/media/preview', new FormData(form)).then(function (payload) {
        if (reqId !== self.resultReq) { return; } // пришёл ответ на устаревший запрос
        var d = payload.data || {};
        if (!d.success) { return; }
        var data = d.data || {};
        self.clearPreview(false);
        self.pendingPreview = data.path || '';
        self.resultUrl = (data.url || '') + '?t=' + Date.now();
        self.resultMeta = data.meta || '';
        if (self.result.img) { self.result.img.src = self.resultUrl; self.result.img.style.display = ''; }
        if (self.result.empty) { self.result.empty.style.display = 'none'; }
        if (self.result.meta) { self.result.meta.textContent = data.meta || ''; }
        // Если открыт крупный просмотр — обновить и его.
        if (self.lightbox && !self.lightbox.modal.hasAttribute('hidden') && self.lightbox.image) {
          self.lightbox.image.src = self.resultUrl;
          if (self.lightbox.meta) { self.lightbox.meta.textContent = data.meta || ''; }
        }
      }).catch(function () {});
    },

    // Открыть текущий результат крупно (лупа/кнопка «Крупно»).
    openResultLarge: function () {
      if (this.resultUrl) {
        this.openLightbox(this.resultUrl, 'Результат по настройкам', this.resultMeta || '');
      } else {
        this.refreshResult();
        Adminx.Toast.show('Готовлю результат…', 'info');
      }
    },

    // Удалить временный превью-файл (при закрытии окна или уходе со страницы).
    clearPreview: function (useBeacon) {
      var path = this.pendingPreview;
      if (!path) { return; }
      this.pendingPreview = '';
      var url = this.base() + '/media/preview-clear';
      var fd = new FormData();
      fd.append('_csrf', Adminx.csrf());
      fd.append('path', path);
      if (useBeacon && navigator.sendBeacon) {
        navigator.sendBeacon(url, fd);
        return;
      }
      Adminx.Ajax.post(url, fd).catch(function () {});
    },

    convertWebp: function (button) {
      var form = button.closest('form');
      var fd;
      if (!form) { return; }
      Adminx.Confirm.open({
        kind: 'info',
        title: 'Создать WebP-копию?',
        message: 'Будет создан соседний WebP-файл с тем же именем исходника.',
        confirmLabel: 'Создать WebP',
        confirmClass: 'btn-primary',
        onConfirm: function () {
          fd = new FormData();
          fd.append('_csrf', Adminx.csrf());
          fd.append('path', button.getAttribute('data-path') || '');
          fd.append('quality', form.querySelector('[name="quality"]') ? form.querySelector('[name="quality"]').value : '82');
          Adminx.Loader.show();
          Adminx.Ajax.post(Adminx.Media.base() + '/media/convert-webp', fd).then(function (payload) {
            Adminx.Loader.hide();
            Adminx.Ajax.handle(payload);
          }).catch(function () {
            Adminx.Loader.hide();
            Adminx.Toast.show('Ошибка сети', 'error');
          });
        }
      });
    },

    bindCrop: function () {
      var root = document.querySelector('[data-media-editor]');
      if (!root) { return; }
      var img = root.querySelector('[data-crop-image]');
      var frame = root.querySelector('[data-crop-frame]');
      var box = root.querySelector('[data-crop-box]');
      var form = root.querySelector('.media-transform-form');
      if (!img || !frame || !box || !form) { return; }

      var state = { x: 12, y: 12, w: 76, h: 76, drag: null, sx: 0, sy: 0 };
      var sizeBadge = box.querySelector('[data-crop-size]');
      var ratioSelect = form.querySelector('[data-crop-ratio]');
      var inputs = {
        enabled: form.querySelector('[data-crop-enabled]'),
        x: form.querySelector('[data-crop-input="x"]'),
        y: form.querySelector('[data-crop-input="y"]'),
        w: form.querySelector('[data-crop-input="w"]'),
        h: form.querySelector('[data-crop-input="h"]'),
        outputW: form.querySelector('[name="width"]'),
        outputH: form.querySelector('[name="height"]')
      };

      function aspectLabel(w, h) {
        function gcd(a, b) { return b ? gcd(b, a % b) : a; }
        var g = gcd(w, h) || 1;
        var rw = Math.round(w / g);
        var rh = Math.round(h / g);
        if (rw <= 40 && rh <= 40) { return rw + ':' + rh; }
        return (w / Math.max(1, h)).toFixed(2) + ':1';
      }

      function sync(syncOutputSize) {
        var rect = img.getBoundingClientRect();
        var naturalW = img.naturalWidth || 1;
        var naturalH = img.naturalHeight || 1;
        var full = state.x === 0 && state.y === 0 && state.w === 100 && state.h === 100;
        var px = Math.round((state.x / 100) * naturalW);
        var py = Math.round((state.y / 100) * naturalH);
        var pw = Math.max(1, Math.round((state.w / 100) * naturalW));
        var ph = Math.max(1, Math.round((state.h / 100) * naturalH));
        box.style.left = state.x + '%';
        box.style.top = state.y + '%';
        box.style.width = state.w + '%';
        box.style.height = state.h + '%';
        if (inputs.enabled) { inputs.enabled.value = full ? '0' : '1'; }
        if (inputs.x) { inputs.x.value = px; }
        if (inputs.y) { inputs.y.value = py; }
        if (inputs.w) { inputs.w.value = pw; }
        if (inputs.h) { inputs.h.value = ph; }
        if (syncOutputSize && inputs.outputW && inputs.outputH) {
          var outputScale = Math.min(1, 2400 / pw, 2400 / ph);
          inputs.outputW.value = Math.max(1, Math.round(pw * outputScale));
          inputs.outputH.value = Math.max(1, Math.round(ph * outputScale));
        }
        if (sizeBadge) {
          sizeBadge.textContent = (full ? 'Весь кадр · ' : 'Выделение · ') + pw + '×' + ph + ' px · ' + aspectLabel(pw, ph);
        }
        // Кроп-инпуты меняются программно — уведомим живое превью вручную.
        if (inputs.w) { inputs.w.dispatchEvent(new Event('input', { bubbles: true })); }
        return rect;
      }

      function clamp(preserveSize) {
        if (preserveSize) {
          state.w = Math.max(1, Math.min(100, state.w));
          state.h = Math.max(1, Math.min(100, state.h));
          state.x = Math.max(0, Math.min(100 - state.w, state.x));
          state.y = Math.max(0, Math.min(100 - state.h, state.y));
          return;
        }
        state.x = Math.max(0, Math.min(99, state.x));
        state.y = Math.max(0, Math.min(99, state.y));
        state.w = Math.max(1, Math.min(100 - state.x, state.w));
        state.h = Math.max(1, Math.min(100 - state.y, state.h));
      }

      function ratio(value) {
        if (!value || value === 'free' || value === 'original') { return 0; }
        var p = value.split(':');
        return p.length === 2 ? (parseFloat(p[0]) / parseFloat(p[1])) : 0;
      }

      function setRatio(value) {
        var r = ratio(value);
        var rect = img.getBoundingClientRect();
        var imageRatio = rect.height > 0 ? rect.width / rect.height : ((img.naturalWidth || 1) / (img.naturalHeight || 1));
        if (!r || !imageRatio) {
          state = { x: 0, y: 0, w: 100, h: 100, drag: null, sx: 0, sy: 0 };
        } else if (r >= imageRatio) {
          state.w = 76;
          state.h = (state.w * imageRatio) / r;
          state.x = 12;
          state.y = (100 - state.h) / 2;
        } else {
          state.h = 76;
          state.w = (state.h * r) / imageRatio;
          state.y = 12;
          state.x = (100 - state.w) / 2;
        }
        clamp();
        sync(true);
      }

      box.addEventListener('mousedown', function (e) {
        var handle = e.target.getAttribute('data-crop-handle');
        state.drag = handle || 'move';
        state.sx = e.clientX;
        state.sy = e.clientY;
        e.preventDefault();
      });
      document.addEventListener('mousemove', function (e) {
        var rect;
        var dx;
        var dy;
        if (!state.drag) { return; }
        rect = img.getBoundingClientRect();
        dx = ((e.clientX - state.sx) / rect.width) * 100;
        dy = ((e.clientY - state.sy) / rect.height) * 100;
        state.sx = e.clientX;
        state.sy = e.clientY;
        if (state.drag === 'move') {
          state.x += dx; state.y += dy;
          clamp(true);
          sync(false);
        } else {
          if (state.drag.indexOf('e') !== -1) { state.w += dx; }
          if (state.drag.indexOf('s') !== -1) { state.h += dy; }
          if (state.drag.indexOf('w') !== -1) { state.x += dx; state.w -= dx; }
          if (state.drag.indexOf('n') !== -1) { state.y += dy; state.h -= dy; }
          if (ratioSelect) { ratioSelect.value = 'free'; }
          clamp(false);
          sync(true);
        }
      });
      document.addEventListener('mouseup', function () { state.drag = null; });

      if (ratioSelect) {
        ratioSelect.addEventListener('change', function (e) { setRatio(e.target.value); });
      }

      var presetSelect = form.querySelector('[data-crop-preset]');
      var presetWarn = form.querySelector('[data-crop-preset-warning]');
      if (presetSelect) {
        presetSelect.addEventListener('change', function () {
          var opt = presetSelect.options[presetSelect.selectedIndex];
          var webpTwinInput = form.querySelector('[data-webp-twin]');
          if (!opt || !opt.value) {
            if (webpTwinInput) { webpTwinInput.value = '0'; }
            if (presetWarn) { presetWarn.hidden = true; }
            return;
          }
          if (webpTwinInput) { webpTwinInput.value = opt.getAttribute('data-webp-twin') === '1' ? '1' : '0'; }
          var pw = parseInt(opt.getAttribute('data-width'), 10) || 0;
          var ph = parseInt(opt.getAttribute('data-height'), 10) || 0;
          var pmode = opt.getAttribute('data-mode');
          var pformat = opt.getAttribute('data-format');
          var pquality = parseInt(opt.getAttribute('data-quality'), 10) || 82;
          var modeInput = form.querySelector('[name="mode"]');
          var formatInput = form.querySelector('[name="format"]');
          var qualityInput = form.querySelector('[name="quality"]');
          if (modeInput && pmode) { modeInput.value = pmode; }
          if (formatInput && pformat && formatInput.querySelector('option[value="' + pformat + '"]')) { formatInput.value = pformat; }
          if (qualityInput) { qualityInput.value = pquality; }
          // рамку кропа — под соотношение вида (если заданы обе стороны)
          if (pw > 0 && ph > 0) {
            if (ratioSelect) { ratioSelect.value = 'free'; }
            setRatio(pw + ':' + ph);
          }
          // выходной размер — точные размеры вида (перекрывает пересчёт из sync)
          if (inputs.outputW && pw > 0) { inputs.outputW.value = pw; }
          if (inputs.outputH && ph > 0) { inputs.outputH.value = ph; }
          if (inputs.outputW) { inputs.outputW.dispatchEvent(new Event('input', { bubbles: true })); }
          // предупреждение о растягивании
          if (presetWarn) {
            var nw = img.naturalWidth || 0;
            var nh = img.naturalHeight || 0;
            if ((pw && nw && nw < pw) || (ph && nh && nh < ph)) {
              presetWarn.textContent = 'Исходник ' + nw + '×' + nh + ' меньше вида ' + pw + '×' + ph + ' — картинка растянется и будет мыльной.';
              presetWarn.hidden = false;
            } else {
              presetWarn.hidden = true;
            }
          }
        });
      }
      form.querySelector('[data-crop-reset]').addEventListener('click', function () {
        state = { x: 0, y: 0, w: 100, h: 100, drag: null, sx: 0, sy: 0 };
        sync(true);
      });
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        Adminx.Loader.show();
        Adminx.Ajax.post(form.action, new FormData(form)).then(function (payload) {
          Adminx.Loader.hide();
          Adminx.Ajax.handle(payload);
        }).catch(function () {
          Adminx.Loader.hide();
          Adminx.Toast.show('Ошибка сети', 'error');
        });
      });
      window.addEventListener('resize', sync);
      if (img.complete) { sync(); } else { img.addEventListener('load', sync); }
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { Adminx.Media.init(); });
  } else {
    Adminx.Media.init();
  }
})(window, document);
