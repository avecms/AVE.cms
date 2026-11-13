/**
 * JS раздела «Настройки»: tabs, conditional fields, ajax-save и запуск
 * и операций обслуживания нативной схемы.
 */
(function (window, document) {
  'use strict';

  var Adminx = window.Adminx || (window.Adminx = {});

  Adminx.Settings = {
    root: null,
    form: null,
    dirty: false,
    layoutDrag: null,
    sectionDrag: null,
    sessionCleanup: null,

    init: function () {
      this.root = document.querySelector('[data-settings-page]');
      this.form = document.getElementById('settingsForm');
      if (!this.root) { return; }

      var self = this;
      document.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-settings-tab]');
        if (tab) { self.openTab(tab.getAttribute('data-settings-tab')); }

        if (e.target.closest('[data-settings-reset]')) { window.location.reload(); }
        if (e.target.closest('[data-pagination-new]')) { self.fillPaginationNew(); }
        var paginationEdit = e.target.closest('[data-pagination-edit]');
        if (paginationEdit) { self.fillPaginationEdit(paginationEdit.closest('[data-pagination-row]')); }
        var paginationDelete = e.target.closest('[data-pagination-delete]');
        if (paginationDelete) { self.deletePagination(paginationDelete.closest('[data-pagination-row]')); }
        var cacheClear = e.target.closest('[data-cache-clear]');
        if (cacheClear) { self.clearCache(cacheClear.getAttribute('data-cache-clear')); }
        var maintenanceClear = e.target.closest('[data-maintenance-clear]');
        if (maintenanceClear) { self.clearMaintenance(maintenanceClear.getAttribute('data-maintenance-clear')); }
        if (e.target.closest('[data-session-cleanup]')) { self.confirmSessionCleanup(); }
        if (e.target.closest('[data-session-stop]') && self.sessionCleanup) {
          self.sessionCleanup.stopped = true;
          e.target.closest('[data-session-stop]').disabled = true;
        }
        var systemFile = e.target.closest('[data-system-file-edit]');
        if (systemFile) { self.openSystemFile(systemFile.getAttribute('data-system-file-edit')); }
        if (e.target.closest('[data-constant-new]')) { self.fillConstantNew(); }
        var constantEditorTab = e.target.closest('[data-constant-editor-tab]');
        if (constantEditorTab) { self.openConstantEditorTab(constantEditorTab.getAttribute('data-constant-editor-tab')); }
        var constantBool = e.target.closest('[data-constant-bool]');
        if (constantBool) { self.setConstantBool(constantBool.getAttribute('data-constant-bool')); }
        var constantEdit = e.target.closest('[data-constant-edit]');
        if (constantEdit) { self.fillConstantEdit(constantEdit.closest('[data-constant-row]')); }
        var constantDelete = e.target.closest('[data-constant-delete]');
        if (constantDelete) { self.deleteConstant(constantDelete.closest('[data-constant-row]')); }
        if (e.target.closest('[data-thumbnail-preset-scan]')) { self.scanThumbnailPresets(); }
        var layoutMove = e.target.closest('[data-layout-move]');
        if (layoutMove) { self.moveLayout(layoutMove); }
        var sectionMove = e.target.closest('[data-section-order-move]');
        if (sectionMove) { self.moveSection(sectionMove); }
      });

      if (this.form) {
        this.form.addEventListener('input', function () { self.markDirty(); self.applyDependencies(); });
        this.form.addEventListener('change', function (e) {
          self.markDirty(); self.applyDependencies(); self.updateSwitchLabels();
          if (e.target.matches('[data-section-order-visible]')) {
            e.target.closest('[data-section-order-item]').classList.toggle('is-disabled', !e.target.checked);
            self.syncSectionOrder(e.target.closest('[data-section-order]'));
          }
        });
        this.form.addEventListener('submit', function (e) { e.preventDefault(); self.save(); });
        this.form.addEventListener('dragstart', function (e) { self.sectionDragStart(e); });
        this.form.addEventListener('dragover', function (e) { self.sectionDragOver(e); });
        this.form.addEventListener('drop', function (e) { if (self.sectionDrag) { e.preventDefault(); } });
        this.form.addEventListener('dragend', function () { self.sectionDragEnd(); });
      }

      var paginationForm = document.getElementById('paginationForm');
      if (paginationForm) {
        paginationForm.addEventListener('submit', function (e) {
          e.preventDefault();
          self.savePagination(paginationForm);
        });
      }

      var constantForm = document.getElementById('constantForm');
      if (constantForm) {
        constantForm.addEventListener('submit', function (e) {
          e.preventDefault();
          self.syncConstantValue();
          self.saveConstant(constantForm);
        });
        constantForm.addEventListener('input', function (e) {
          if (e.target.matches('[data-constant-input]')) { self.syncConstantValue(e.target); }
          if (e.target.matches('[data-constant-options]')) { self.updateConstantEditor(); }
        });
        constantForm.addEventListener('change', function (e) {
          if (e.target.matches('[data-constant-type]')) { self.updateConstantEditor(); }
          if (e.target.matches('[data-constant-input]')) { self.syncConstantValue(e.target); }
        });
      }

      var systemFileForm = document.querySelector('[data-system-file-form]');
      if (systemFileForm) {
        systemFileForm.addEventListener('submit', function (e) {
          e.preventDefault();
          self.saveSystemFile(systemFileForm);
        });
      }

      var interfaceForm = document.querySelector('[data-interface-layout-form]');
      if (interfaceForm) {
        interfaceForm.addEventListener('submit', function (e) { e.preventDefault(); self.saveInterface(interfaceForm); });
        interfaceForm.addEventListener('change', function (e) {
          if (e.target.matches('[data-layout-visible]')) {
            e.target.closest('[data-layout-item]').classList.toggle('is-disabled', !e.target.checked);
          }
        });
        interfaceForm.addEventListener('dragstart', function (e) { self.layoutDragStart(e); });
        interfaceForm.addEventListener('dragover', function (e) { self.layoutDragOver(e); });
        interfaceForm.addEventListener('drop', function (e) { if (self.layoutDrag) { e.preventDefault(); } });
        interfaceForm.addEventListener('dragend', function () { self.layoutDragEnd(); });
      }

      if (this.form) { this.applyDependencies(); this.updateSwitchLabels(); }
    },

    base: function () {
      return (this.root ? this.root.getAttribute('data-base') : '') || Adminx.base();
    },

    layoutDragStart: function (event) {
      var handle = event.target.closest('[data-layout-handle]');
      var row = handle ? handle.closest('[data-layout-item]') : null;
      if (!row || handle.getAttribute('draggable') !== 'true') { event.preventDefault(); return; }
      this.layoutDrag = row;
      row.classList.add('is-dragging');
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', row.getAttribute('data-code') || '');
      }
    },

    layoutDragOver: function (event) {
      if (!this.layoutDrag) { return; }
      var target = event.target.closest('[data-layout-item]');
      var list = event.target.closest('[data-layout-list]');
      if (!target || !list || target === this.layoutDrag || this.layoutDrag.parentNode !== list) { return; }
      event.preventDefault();
      var rect = target.getBoundingClientRect();
      list.insertBefore(this.layoutDrag, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling);
    },

    layoutDragEnd: function () {
      if (this.layoutDrag) { this.layoutDrag.classList.remove('is-dragging'); }
      this.layoutDrag = null;
    },

    moveLayout: function (button) {
      var row = button.closest('[data-layout-item]');
      if (!row || button.disabled) { return; }
      if (button.getAttribute('data-layout-move') === 'up' && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
      } else if (button.getAttribute('data-layout-move') === 'down' && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
      }
    },

    sectionDragStart: function (event) {
      var handle = event.target.closest('[data-section-order-handle]');
      var row = handle ? handle.closest('[data-section-order-item]') : null;
      if (!row || row.getAttribute('draggable') !== 'true') { event.preventDefault(); return; }
      this.sectionDrag = row;
      row.classList.add('is-dragging');
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', row.getAttribute('data-code') || '');
      }
    },

    sectionDragOver: function (event) {
      if (!this.sectionDrag) { return; }
      var target = event.target.closest('[data-section-order-item]');
      var list = event.target.closest('[data-section-order]');
      if (!target || !list || target === this.sectionDrag || this.sectionDrag.parentNode !== list) { return; }
      event.preventDefault();
      var rect = target.getBoundingClientRect();
      list.insertBefore(this.sectionDrag, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling);
      this.syncSectionOrder(list);
    },

    sectionDragEnd: function () {
      if (this.sectionDrag) {
        var list = this.sectionDrag.closest('[data-section-order]');
        this.sectionDrag.classList.remove('is-dragging');
        this.syncSectionOrder(list);
      }
      this.sectionDrag = null;
    },

    moveSection: function (button) {
      var row = button.closest('[data-section-order-item]');
      if (!row || button.disabled) { return; }
      if (button.getAttribute('data-section-order-move') === 'up' && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
      } else if (button.getAttribute('data-section-order-move') === 'down' && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
      }
      this.syncSectionOrder(row.parentNode);
      this.markDirty();
    },

    syncSectionOrder: function (list) {
      if (!list) { return; }
      var input = list.parentNode.querySelector('[data-section-order-value]');
      if (!input) { return; }
      input.value = JSON.stringify(Array.prototype.map.call(list.querySelectorAll('[data-section-order-item]'), function (row) {
        var toggle = row.querySelector('[data-section-order-visible]');
        return { code: row.getAttribute('data-code') || '', visible: !!(toggle && toggle.checked) };
      }));
    },

    layoutValues: function (scope) {
      var list = document.querySelector('[data-layout-list="' + scope + '"]');
      if (!list) { return []; }
      return Array.prototype.map.call(list.querySelectorAll('[data-layout-item]'), function (row) {
        var toggle = row.querySelector('[data-layout-visible]');
        return { code: row.getAttribute('data-code') || '', visible: !!(toggle && toggle.checked) };
      });
    },

    saveInterface: function (form) {
      var data = new FormData(form);
      data.set('navigation', JSON.stringify(this.layoutValues('navigation')));
      data.set('dashboard', JSON.stringify(this.layoutValues('dashboard')));
      Adminx.Loader.show();
      Adminx.Ajax.post(this.base() + '/settings/interface', data).then(function (payload) {
        Adminx.Loader.hide();
        var result = payload.data || {};
        if (!result.success) {
          Adminx.Toast.show(result.message || 'Не удалось сохранить интерфейс', 'error');
          return;
        }
        Adminx.Toast.show(result.message || 'Интерфейс обновлен', 'success');
        window.setTimeout(function () { window.location.reload(); }, 250);
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка сети', 'error');
      });
    },

    openTab: function (code) {
      document.querySelectorAll('[data-settings-tab]').forEach(function (tab) {
        tab.setAttribute('aria-selected', tab.getAttribute('data-settings-tab') === code ? 'true' : 'false');
      });
      document.querySelectorAll('[data-settings-panel]').forEach(function (panel) {
        panel.classList.toggle('active', panel.getAttribute('data-settings-panel') === code);
      });
    },

    openInitialTab: function () {
      var hash = (window.location.hash || '').replace(/^#/, '');
      if (hash && document.querySelector('[data-settings-tab="' + hash + '"]')) {
        this.openTab(hash);
      }
    },

    markDirty: function () {
      this.dirty = true;
      var bar = document.getElementById('settingsSavebar');
      if (bar) { bar.hidden = false; }
    },

    clearErrors: function () {
      this.form.querySelectorAll('[data-error]').forEach(function (s) { s.textContent = ''; });
      this.form.querySelectorAll('.input, .select, .textarea').forEach(function (el) { el.classList.remove('is-invalid'); });
    },

    save: function () {
      var self = this;
      this.clearErrors();
      Adminx.Loader.show();
      Adminx.Ajax.post(this.base() + '/settings', new FormData(this.form)).then(function (payload) {
        Adminx.Loader.hide();
        var d = payload.data || {};
        if (d.success) {
          self.dirty = false;
          var bar = document.getElementById('settingsSavebar');
          if (bar) { bar.hidden = true; }
          Adminx.Toast.show(d.message || 'Настройки сохранены', 'success');
          return;
        }
        var errors = d.errors || {};
        Object.keys(errors).forEach(function (field) {
          var span = self.form.querySelector('[data-error="' + field + '"]');
          if (span) { span.textContent = errors[field]; }
          var input = self.form.querySelector('[name="' + field + '"]');
          if (input) { input.classList.add('is-invalid'); }
        });
        Adminx.Toast.show(d.message || 'Не удалось сохранить', 'error');
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка сети', 'error');
      });
    },

    paginationForm: function () {
      return document.getElementById('paginationForm');
    },

    fillPaginationNew: function () {
      var form = this.paginationForm();
      if (!form) { return; }
      form.reset();
      form.querySelector('[name="id"]').value = '';
      document.getElementById('paginationDrawerTitle').textContent = 'Новый шаблон пагинации';
    },

    fillPaginationEdit: function (row) {
      var form = this.paginationForm();
      if (!form || !row) { return; }
      var id = row.getAttribute('data-id');
      this.fillPaginationNew();
      document.getElementById('paginationDrawerTitle').textContent = 'Изменение: ' + row.getAttribute('data-name');
      Adminx.Loader.show();
      Adminx.Ajax.request(this.base() + '/settings/paginations/' + id).then(function (payload) {
        Adminx.Loader.hide();
        var d = payload.data || {};
        if (!d.success) {
          Adminx.Toast.show(d.message || 'Шаблон не найден', 'error');
          return;
        }
        var data = d.data || {};
        Object.keys(data).forEach(function (key) {
          var el = form.querySelector('[name="' + key + '"]');
          if (el) { el.value = data[key] == null ? '' : data[key]; }
        });
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка сети', 'error');
      });
    },

    savePagination: function (form) {
      var id = (form.querySelector('[name="id"]').value || '').trim();
      var url = this.base() + '/settings/paginations' + (id ? '/' + id : '');
      Adminx.Loader.show();
      Adminx.Ajax.post(url, new FormData(form)).then(function (payload) {
        Adminx.Loader.hide();
        var d = payload.data || {};
        if (d.success) {
          Adminx.Ajax.handle(payload);
          if (!d.redirect) { window.location.reload(); }
        } else {
          Adminx.Toast.show(d.message || 'Не удалось сохранить шаблон', 'error');
        }
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка сети', 'error');
      });
    },

    deletePagination: function (row) {
      if (!row) { return; }
      var self = this;
      Adminx.Confirm.open({
        kind: 'error',
        title: 'Удалить шаблон пагинации?',
        message: '«' + row.getAttribute('data-name') + '» будет удалён.',
        confirmLabel: 'Удалить',
        confirmClass: 'btn-danger',
        onConfirm: function () {
          Adminx.Loader.show();
          Adminx.Ajax.post(self.base() + '/settings/paginations/' + row.getAttribute('data-id') + '/delete').then(function (payload) {
            Adminx.Loader.hide();
            var d = payload.data || {};
            if (d.success) {
              row.remove();
              Adminx.Toast.show(d.message || 'Шаблон удалён', 'success');
            } else {
              Adminx.Toast.show(d.message || 'Не удалось удалить', 'error');
            }
          }).catch(function () {
            Adminx.Loader.hide();
            Adminx.Toast.show('Ошибка сети', 'error');
          });
        }
      });
    },

    clearCache: function (source) {
      var self = this;
      Adminx.Confirm.open({
        kind: 'warning',
        title: 'Очистить кеш?',
        message: 'Будет очищен источник «' + source + '».',
        confirmLabel: 'Очистить',
        onConfirm: function () {
          Adminx.Loader.show();
          Adminx.Ajax.post(self.base() + '/settings/cache/' + encodeURIComponent(source) + '/clear').then(function (payload) {
            Adminx.Loader.hide();
            var d = payload.data || {};
            if (d.success) {
              var row = document.querySelector('[data-cache-row="' + source + '"]');
              if (row && d.data && d.data.size) {
                var cell = row.querySelector('[data-cache-size]');
                if (cell) { cell.textContent = d.data.size; }
              }
              Adminx.Toast.show(d.message || 'Кеш очищен', 'success');
            } else {
              Adminx.Toast.show(d.message || 'Не удалось очистить кеш', 'error');
            }
          }).catch(function () {
            Adminx.Loader.hide();
            Adminx.Toast.show('Ошибка сети', 'error');
          });
        }
      });
    },

    clearMaintenance: function (target) {
      var self = this;
      var label = target === 'revisions'
        ? 'все ревизии документов, блоков, шаблонов, рубрик, тем, представлений и оформления каталога'
        : 'всю подневную статистику просмотров';
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Очистить служебные данные?',
        message: 'Будут удалены ' + label + '. Действие необратимо.',
        confirmLabel: 'Очистить',
        onConfirm: function () {
          Adminx.Loader.show();
          Adminx.Ajax.post(self.base() + '/settings/maintenance/' + encodeURIComponent(target) + '/clear').then(function (payload) {
            Adminx.Loader.hide();
            var d = payload.data || {};
            Adminx.Toast.show(d.message || (d.success ? 'Данные очищены' : 'Не удалось очистить данные'), d.success ? 'success' : 'error');
            if (d.success) { window.location.reload(); }
          }).catch(function () { Adminx.Loader.hide(); Adminx.Toast.show('Ошибка сети', 'error'); });
        }
      });
    },

    confirmSessionCleanup: function () {
      if (this.sessionCleanup) { return; }
      var self = this;
      Adminx.Confirm.open({
        kind: 'warning',
        title: 'Очистить просроченные сессии?',
        message: 'Действующие сессии, заказы и сохранённые брошенные корзины останутся. Очистка выполняется порциями; дождитесь завершения на этой странице.',
        confirmLabel: 'Очистить',
        onConfirm: function () {
          if (self.sessionCleanup) { return; }
          var root = document.querySelector('[data-session-maintenance]');
          self.sessionCleanup = {root: root, count: 0, total: Number(root.querySelector('[data-session-expired]').textContent) || 0, cutoff: 0, stopped: false};
          root.querySelector('[data-session-cleanup]').disabled = true;
          root.querySelector('[data-session-progress]').hidden = false;
          root.querySelector('[data-session-progress-bar]').style.width = '0%';
          root.querySelector('[role=progressbar]').setAttribute('aria-valuenow', '0');
          root.querySelector('[data-session-progress-status]').textContent = Adminx.tr('Очистка…');
          root.querySelector('[data-session-stop]').hidden = false;
          root.querySelector('[data-session-stop]').disabled = false;
          self.clearSessionBatch();
        }
      });
    },

    clearSessionBatch: function () {
      var self = this;
      var job = this.sessionCleanup;
      if (!job) { return; }
      if (job.stopped) { self.finishSessionCleanup(false); return; }
      var form = new FormData();
      form.append('cutoff', String(job.cutoff));
      Adminx.Ajax.post(self.base() + '/settings/maintenance/sessions/clear', form).then(function (payload) {
        var response = payload.data || {};
        if (!payload.ok || !response.success || !response.data) {
          throw new Error(response.message || Adminx.tr('Не удалось очистить данные'));
        }
        var result = response.data;
        job.cutoff = result.cutoff;
        job.count += Number(result.count) || 0;
        var percent = result.done ? 100 : Math.min(99, Math.round(100 * job.count / Math.max(1, job.total)));
        job.root.querySelector('[data-session-progress-bar]').style.width = percent + '%';
        job.root.querySelector('[role=progressbar]').setAttribute('aria-valuenow', String(percent));
        job.root.querySelector('[data-session-expired]').textContent = String(Math.max(0, job.total - job.count));
        job.root.querySelector('[data-session-progress-status]').textContent = Adminx.tr('Удалено сессий:') + ' ' + job.count;
        if (result.done || job.stopped) {
          self.finishSessionCleanup(Boolean(result.done));
        } else {
          window.setTimeout(function () { self.clearSessionBatch(); }, 200);
        }
      }).catch(function (error) { self.finishSessionCleanup(false, error.message || Adminx.tr('Ошибка сети')); });
    },

    finishSessionCleanup: function (done, error) {
      var job = this.sessionCleanup;
      if (!job) { return; }
      job.root.querySelector('[data-session-cleanup]').disabled = false;
      job.root.querySelector('[data-session-stop]').hidden = true;
      var message = (done ? Adminx.tr('Очистка завершена.') : Adminx.tr('Очистка остановлена.')) + ' ' + Adminx.tr('Удалено сессий:') + ' ' + job.count;
      if (done) { job.root.querySelector('[data-session-expired]').textContent = '0'; }
      job.root.querySelector('[data-session-progress-status]').textContent = error ? message + '. ' + error : message;
      Adminx.Toast.show(error || message, error ? 'error' : (done ? 'success' : 'info'));
      this.sessionCleanup = null;
    },

    openSystemFile: function (code) {
      var form = document.querySelector('[data-system-file-form]');
      if (!form) { return; }
      Adminx.Loader.show();
      Adminx.Ajax.request(this.base() + '/settings/files/' + encodeURIComponent(code)).then(function (payload) {
        Adminx.Loader.hide();
        var d = payload.data || {};
        if (!d.success) { Adminx.Toast.show(d.message || 'Файл не найден', 'error'); return; }
        var file = d.data || {};
        form.elements.code.value = file.code || code;
        var textarea = form.elements.content;
        textarea.value = file.content || '';
        textarea.setAttribute('data-code-mode', file.mode || 'text/plain');
        if (textarea._adminxCodeMirror) {
          textarea._adminxCodeMirror.setOption('mode', file.mode || 'text/plain');
          textarea._adminxCodeMirror.setValue(file.content || '');
          textarea._adminxCodeMirror.setSize('100%', '100%');
          setTimeout(function () { textarea._adminxCodeMirror.refresh(); }, 80);
        }
        var title = document.getElementById('systemFileDrawerTitle');
        var path = document.querySelector('[data-system-file-path]');
        if (title) { title.textContent = file.label || 'Системный файл'; }
        if (path) { path.textContent = file.path || ''; }
      }).catch(function () { Adminx.Loader.hide(); Adminx.Toast.show('Ошибка сети', 'error'); });
    },

    saveSystemFile: function (form) {
      var code = form.elements.code.value || '';
      var textarea = form.elements.content;
      if (textarea._adminxCodeMirror) { textarea._adminxCodeMirror.save(); }
      Adminx.Loader.show();
      Adminx.Ajax.post(this.base() + '/settings/files/' + encodeURIComponent(code), new FormData(form)).then(function (payload) {
        Adminx.Loader.hide();
        var d = payload.data || {};
        Adminx.Toast.show(d.message || (d.success ? 'Файл сохранён' : 'Не удалось сохранить файл'), d.success ? 'success' : 'error');
      }).catch(function () { Adminx.Loader.hide(); Adminx.Toast.show('Ошибка сети', 'error'); });
    },

    constantForm: function () {
      return document.getElementById('constantForm');
    },

    fillConstantNew: function () {
      var form = this.constantForm();
      if (!form) { return; }
      form.reset();
      form.querySelector('[name="name"]').readOnly = false;
      form.querySelector('[name="is_system"]').value = '0';
      form.querySelector('[name="sort_order"]').value = '100';
      form.querySelector('[data-constant-value]').value = '';
      var presetResult = form.querySelector('[data-thumbnail-preset-result]');
      if (presetResult) { presetResult.hidden = true; presetResult.textContent = ''; }
      this.openConstantEditorTab('value');
      this.updateConstantEditor();
      document.getElementById('constantDrawerTitle').textContent = 'Новая константа';
    },

    fillConstantEdit: function (row) {
      var form = this.constantForm();
      if (!form || !row) { return; }
      var self = this;
      this.fillConstantNew();
      var name = row.getAttribute('data-name');
      document.getElementById('constantDrawerTitle').textContent = 'Изменение: ' + name;
      Adminx.Loader.show();
      Adminx.Ajax.request(this.base() + '/settings/constants/' + encodeURIComponent(name)).then(function (payload) {
        Adminx.Loader.hide();
        var d = payload.data || {};
        if (!d.success) {
          Adminx.Toast.show(d.message || 'Константа не найдена', 'error');
          return;
        }
        var data = d.data || {};
        Object.keys(data).forEach(function (key) {
          var el = form.querySelector('[name="' + key + '"]');
          if (el) { el.value = data[key] == null ? '' : data[key]; }
        });
        form.querySelector('[name="name"]').readOnly = true;
        if (Array.isArray(data.options_array)) {
          form.querySelector('[name="options"]').value = data.options_array.join('\n');
        }
        self.updateConstantEditor();
        self.openConstantEditorTab('value');
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка сети', 'error');
      });
    },

    saveConstant: function (form) {
      Adminx.Loader.show();
      Adminx.Ajax.post(this.base() + '/settings/constants', new FormData(form)).then(function (payload) {
        Adminx.Loader.hide();
        var d = payload.data || {};
        if (d.success) {
          Adminx.Ajax.handle(payload);
          if (!d.redirect) { window.location.reload(); }
        } else {
          Adminx.Toast.show(d.message || 'Не удалось сохранить константу', 'error');
        }
      }).catch(function () {
        Adminx.Loader.hide();
        Adminx.Toast.show('Ошибка сети', 'error');
      });
    },

    openConstantEditorTab: function (code) {
      var form = this.constantForm(); if (!form) { return; }
      form.querySelectorAll('[data-constant-editor-tab]').forEach(function (tab) {
        var active = tab.getAttribute('data-constant-editor-tab') === code;
        tab.classList.toggle('is-active', active); tab.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      form.querySelectorAll('[data-constant-editor-panel]').forEach(function (panel) {
        panel.hidden = panel.getAttribute('data-constant-editor-panel') !== code;
      });
    },

    constantOptions: function () {
      var form = this.constantForm(); if (!form) { return []; }
      var raw = form.querySelector('[data-constant-options]').value || '', seen = {};
      return raw.split(/[\r\n,]+/).map(function (item) { return item.trim(); }).filter(function (item) {
        if (!item || seen[item]) { return false; } seen[item] = true; return true;
      });
    },

    updateConstantEditor: function () {
      var form = this.constantForm(); if (!form) { return; }
      var type = form.querySelector('[data-constant-type]').value || 'string';
      var value = form.querySelector('[data-constant-value]').value || '';
      var name = (form.querySelector('[name="name"]').value || '').trim().toUpperCase();
      var thumbnailTools = form.querySelector('[data-thumbnail-preset-tools]');
      if (thumbnailTools) { thumbnailTools.hidden = name !== 'THUMBNAIL_SIZES'; }
      form.querySelectorAll('[data-constant-control]').forEach(function (control) { control.hidden = control.getAttribute('data-constant-control') !== type; });
      var optionsField = form.querySelector('[data-constant-options-field]');
      optionsField.hidden = type !== 'select' && type !== 'tags';
      var hints = { string: 'Обычная текстовая строка.', path: 'Путь относительно корня проекта или абсолютный путь.', int: 'Только целое число.', bool: 'Выберите одно из двух состояний.', select: 'Выберите один из настроенных вариантов.', tags: 'Введите значения по одному на строку.' };
      form.querySelector('[data-constant-value-hint]').textContent = hints[type] || '';
      if (type === 'bool') {
        this.setConstantBool(value === '1' || value === 'true' ? '1' : '0'); return;
      }
      if (type === 'select') {
        var select = form.querySelector('[data-constant-input="select"]'), options = this.constantOptions();
        select.innerHTML = '';
        options.forEach(function (option) { var node = document.createElement('option'); node.value = option; node.textContent = option; select.appendChild(node); });
        if (options.indexOf(value) < 0 && value !== '') { var current = document.createElement('option'); current.value = value; current.textContent = value + ' (текущее)'; select.insertBefore(current, select.firstChild); }
        select.value = value; select.disabled = options.length === 0 && value === '';
        form.querySelector('[data-constant-select-empty]').hidden = !select.disabled;
        return;
      }
      var input = form.querySelector('[data-constant-input="' + type + '"]');
      if (input) {
        input.value = type === 'tags' && name === 'THUMBNAIL_SIZES'
          ? value.split(/[\s,;]+/).filter(Boolean).join('\n')
          : value;
      }
    },

    scanThumbnailPresets: function () {
      var form = this.constantForm();
      if (!form) { return; }
      var self = this;
      var button = form.querySelector('[data-thumbnail-preset-scan]');
      if (button) { button.disabled = true; }
      Adminx.Loader.show();
      Adminx.Ajax.post(this.base() + '/settings/constants/thumbnail-sizes/scan').then(function (payload) {
        Adminx.Loader.hide();
        if (button) { button.disabled = false; }
        var response = payload.data || {};
        if (!response.success) {
          Adminx.Toast.show(response.message || 'Не удалось собрать размеры', 'error');
          return;
        }
        var data = response.data || {};
        var sizes = Array.isArray(data.sizes) ? data.sizes : [];
        form.querySelector('[data-constant-type]').value = 'tags';
        form.querySelector('[data-constant-value]').value = sizes.join(',');
        self.updateConstantEditor();
        var input = form.querySelector('[data-constant-input="tags"]');
        if (input) { input.value = sizes.join('\n'); }
        self.syncConstantValue(input);
        var result = form.querySelector('[data-thumbnail-preset-result]');
        if (result) {
          result.hidden = false;
          result.textContent = 'Найдено: ' + sizes.length + ' · файлов: ' + (data.files_scanned || 0)
            + ' · таблиц БД: ' + (data.database_tables_scanned || 0)
            + (Array.isArray(data.warnings) && data.warnings.length ? ' · предупреждений: ' + data.warnings.length : '');
        }
        Adminx.Toast.show(response.message || 'Размеры собраны', 'success');
      }).catch(function () {
        Adminx.Loader.hide();
        if (button) { button.disabled = false; }
        Adminx.Toast.show('Ошибка сети', 'error');
      });
    },

    setConstantBool: function (value, sync) {
      var form = this.constantForm(); if (!form) { return; }
      value = String(value) === '1' ? '1' : '0';
      form.querySelectorAll('[data-constant-bool]').forEach(function (button) { button.setAttribute('aria-pressed', button.getAttribute('data-constant-bool') === value ? 'true' : 'false'); });
      if (sync !== false) { form.querySelector('[data-constant-value]').value = value; }
    },

    syncConstantValue: function (control) {
      var form = this.constantForm(); if (!form) { return; }
      var type = form.querySelector('[data-constant-type]').value || 'string';
      if (type === 'bool') { return; }
      control = control || form.querySelector('[data-constant-input="' + type + '"]');
      if (control) { form.querySelector('[data-constant-value]').value = control.value; }
    },

    deleteConstant: function (row) {
      if (!row) { return; }
      var self = this;
      var name = row.getAttribute('data-name');
      Adminx.Confirm.open({
        kind: 'error',
        title: 'Удалить константу?',
        message: name + ' будет удалена из управляемой БД. PHP-файл изменится только после сборки.',
        confirmLabel: 'Удалить',
        confirmClass: 'btn-danger',
        onConfirm: function () {
          Adminx.Loader.show();
          Adminx.Ajax.post(self.base() + '/settings/constants/' + encodeURIComponent(name) + '/delete').then(function (payload) {
            Adminx.Loader.hide();
            var d = payload.data || {};
            if (d.success) {
              row.remove();
              Adminx.Toast.show(d.message || 'Константа удалена', 'success');
            } else {
              Adminx.Toast.show(d.message || 'Не удалось удалить', 'error');
            }
          }).catch(function () {
            Adminx.Loader.hide();
            Adminx.Toast.show('Ошибка сети', 'error');
          });
        }
      });
    },

    applyDependencies: function () {
      var self = this;
      this.form.querySelectorAll('[data-depends-key]').forEach(function (field) {
        var key = field.getAttribute('data-depends-key');
        var value = field.getAttribute('data-depends-value');
        var control = self.form.querySelector('[name="' + key + '"]');
        var visible = control && control.value === value;
        field.hidden = !visible;
      });
    },

    updateSwitchLabels: function () {
      this.form.querySelectorAll('.settings-switch-line').forEach(function (line) {
        var input = line.querySelector('input[type="checkbox"]');
        var label = line.querySelector('span b');
        if (input && label) { label.textContent = input.checked ? 'Включено' : 'Выключено'; }
      });
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { Adminx.Settings.init(); });
  } else {
    Adminx.Settings.init();
  }
})(window, document);
