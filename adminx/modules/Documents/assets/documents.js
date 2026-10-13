/**
 * JS раздела «Документы»: AJAX-фильтры, быстрые действия списка и сохранение страницы редактирования.
 */
(function (window, document) {
  'use strict';

  var Adminx = window.Adminx || (window.Adminx = {});

  Adminx.Documents = {
    form: null,
    filterTimer: null,
    filterAbort: null,
    filterRequest: 0,
	aliasTimer: null,
	shortAliasTimer: null,
    dirty: false,
    submitting: false,
    aliasState: 'empty',
    currentRevisionId: 0,
    currentRevisionDocumentId: 0,
    currentAliasDocumentId: 0,
    currentRemarkDocumentId: 0,
	draftTimer: null,
	draftCandidate: null,
	workspacePanel: 'main',
		errorTargets: [],
		conditionsInitialized: false,

    init: function () {
      this.form = document.getElementById('documentForm');
      var self = this;

		document.addEventListener('click', function (e) {
        if (e.target.closest('[data-documents-filter-reset]')) { self.resetFilters(); }

        var page = e.target.closest('[data-documents-page]');
        if (page) {
          e.preventDefault();
          self.applyFilterUrl(page.href, true);
        }

        var del = e.target.closest('[data-document-delete]');
        if (del) { self.remove(del.closest('[data-document-row]')); }

        var restore = e.target.closest('[data-document-restore]');
        if (restore) { self.restore(restore.closest('[data-document-row]')); }

        var toggle = e.target.closest('[data-document-toggle]');
        if (toggle) { self.action(toggle.closest('[data-document-row]'), '/toggle'); }

        var copy = e.target.closest('[data-document-copy]');
        if (copy) { self.copy(copy.closest('[data-document-row]')); }

        var purge = e.target.closest('[data-document-purge]');
        if (purge) { self.purge(purge.closest('[data-document-row]')); }

        if (e.target.closest('[data-document-views-clear]')) { self.clearViews(); }
        if (e.target.closest('[data-documents-bulk-apply]')) { self.applyBulk(); }
        if (e.target.closest('[data-documents-bulk-clear]')) { self.clearBulk(); }
		  if (e.target.closest('[data-documents-snapshots-rebuild]')) { self.rebuildSnapshots(); }

		  var createItem = e.target.closest('[data-document-create-item]');
		  if (createItem) { self.selectCreateRubric(createItem.getAttribute('data-document-create-item')); }
		  var presetDelete = e.target.closest('[data-document-preset-delete]');
		  if (presetDelete) { e.preventDefault(); self.deleteCreationPreset(presetDelete); }

        var fieldTab = e.target.closest('[data-document-field-tab]');
        if (fieldTab) { self.activateFieldGroup(fieldTab.getAttribute('data-document-field-tab')); }

		var workspaceTab = e.target.closest('[data-document-workspace-tab]');
		if (workspaceTab && workspaceTab.getAttribute('aria-disabled') !== 'true') { self.setWorkspacePanel(workspaceTab.getAttribute('data-document-workspace-tab'), true); }
		var workspaceOpen = e.target.closest('[data-document-workspace-open]');
		if (workspaceOpen) { self.setWorkspacePanel(workspaceOpen.getAttribute('data-document-workspace-open'), true); }
		if (e.target.closest('[data-document-draft-restore]')) { self.restoreLocalDraft(); }
		if (e.target.closest('[data-document-draft-discard]')) { self.discardLocalDraft(); }
		var errorJump = e.target.closest('[data-document-error-jump]');
		if (errorJump) { self.jumpToError(errorJump.getAttribute('data-document-error-jump')); }

        var mediaPick = e.target.closest('[data-document-media-pick]');
        if (mediaPick) {
          self.openMediaPicker(mediaPick.closest('.documents-picker-input') || mediaPick.closest('[data-document-media-row]') || mediaPick.closest('.documents-single-media-field') || mediaPick.closest('.documents-field-control'));
        }

        var mediaAdd = e.target.closest('[data-document-media-add]');
        if (mediaAdd) { self.addMediaRow(mediaAdd.closest('[data-document-media-list]')); }

        var mediaRemove = e.target.closest('[data-document-media-remove]');
        if (mediaRemove) { self.removeMediaRow(mediaRemove.closest('[data-document-media-row]')); }

        var mediaUp = e.target.closest('[data-document-media-up]');
        if (mediaUp) { self.moveMediaRow(mediaUp.closest('[data-document-media-row]'), -1); }

        var mediaDown = e.target.closest('[data-document-media-down]');
        if (mediaDown) { self.moveMediaRow(mediaDown.closest('[data-document-media-row]'), 1); }

        var mediaClear = e.target.closest('[data-document-media-clear]');
        if (mediaClear) { self.clearMediaRows(mediaClear.closest('[data-document-media-list]')); }

        var mediaClearSingle = e.target.closest('[data-document-media-clear-single]');
        if (mediaClearSingle) { self.clearSingleMedia(mediaClearSingle.closest('[data-document-media-single]')); }

        var mediaReplace = e.target.closest('[data-document-media-replace]');
        if (mediaReplace) { self.openMediaReplacement(mediaReplace.closest('[data-document-media-list], [data-document-media-single]')); }

        var mediaUpload = e.target.closest('[data-document-media-upload]');
        if (mediaUpload) { self.openMediaUpload(mediaUpload.closest('[data-document-media-list], [data-document-media-single]')); }

        var mediaImportFolder = e.target.closest('[data-document-media-import-folder]');
        if (mediaImportFolder) { self.importMediaFolder(mediaImportFolder.closest('[data-document-media-list]')); }

        var valueAdd = e.target.closest('[data-document-value-add]');
        if (valueAdd) { self.addValueRow(valueAdd.closest('[data-document-value-list]')); }

        var valueRemove = e.target.closest('[data-document-value-remove]');
        if (valueRemove) { self.removeValueRow(valueRemove.closest('[data-document-value-row]')); }

        var valueUp = e.target.closest('[data-document-value-up]');
        if (valueUp) { self.moveValueRow(valueUp.closest('[data-document-value-row]'), -1); }

        var valueDown = e.target.closest('[data-document-value-down]');
        if (valueDown) { self.moveValueRow(valueDown.closest('[data-document-value-row]'), 1); }

        var valueClear = e.target.closest('[data-document-value-clear]');
        if (valueClear) { self.clearValueRows(valueClear.closest('[data-document-value-list]')); }

        var relationPick = e.target.closest('[data-document-relation-pick]');
        if (relationPick) { self.openRelationPicker(relationPick.closest('.documents-field-control')); }

        var relationRemove = e.target.closest('[data-document-relation-remove]');
        if (relationRemove) { self.removePickedRelation(relationRemove.closest('[data-document-relation-token]')); }

        var relationClear = e.target.closest('[data-document-relation-clear]');
        if (relationClear) {
          var clearSingle = relationClear.closest('[data-document-relation-single]');
          if (clearSingle) {
            var clearInput = clearSingle.querySelector('[data-document-relation-id]');
            if (clearInput) { clearInput.value = ''; clearInput.dispatchEvent(new Event('input', { bubbles: true })); }
            self.fillRelationSingle(clearSingle, '', '');
          }
        }

        var parentPick = e.target.closest('[data-document-parent-pick]');
        if (parentPick) { self.openRelationPicker(parentPick.closest('.documents-picker-input')); }

        var docPick = e.target.closest('[data-document-media-doc-pick]');
        if (docPick) {
          var wrap = docPick.closest('.documents-picker-input');
          var linkInput = wrap ? wrap.querySelector('[data-document-media-url]') : null;
          if (linkInput) { self.openRelationPicker(null, { input: linkInput, insert: 'linkAlias' }); }
        }

        var slugGen = e.target.closest('[data-slug-generate]');
        if (slugGen) { e.preventDefault(); self.generateSlug(slugGen); }

        var shortGen = e.target.closest('[data-short-generate]');
        if (shortGen) { e.preventDefault(); self.generateShortAlias(shortGen); }

        var catOption = e.target.closest('[data-document-catalog-option]');
        if (catOption) { e.preventDefault(); self.addCatalogToken(catOption); return; }
        var catRemove = e.target.closest('[data-document-catalog-remove]');
        if (catRemove) { e.preventDefault(); self.removeCatalogToken(catRemove.closest('[data-document-catalog-token]')); return; }

        if (e.target.closest('[data-document-revisions]')) { self.openRevisions(); }
        if (e.target.closest('[data-document-aliases]')) { self.openAliases(); }
        if (e.target.closest('[data-document-remarks]')) { self.openRemarks(); }
        if (e.target.closest('[data-document-snapshot]')) { self.openSnapshot(); }
        if (e.target.closest('[data-document-snapshot-rebuild]')) { self.rebuildSnapshot(); }
        if (e.target.closest('[data-document-payload-preview]')) { self.previewPayload(); }
        if (e.target.closest('[data-document-payload-copy]')) { self.copyPayload(); }
        var aliasEdit = e.target.closest('[data-document-alias-edit]');
        if (aliasEdit) { self.editAlias(aliasEdit); }
        var aliasDelete = e.target.closest('[data-document-alias-delete]');
        if (aliasDelete) { self.deleteAlias(aliasDelete.getAttribute('data-document-alias-delete')); }
        if (e.target.closest('[data-document-alias-reset]')) { self.resetAliasForm(); }
        var remarkDelete = e.target.closest('[data-document-remark-delete]');
        if (remarkDelete) { self.deleteRemark(remarkDelete.getAttribute('data-document-remark-delete')); }
        var revisionDelete = e.target.closest('[data-document-revision-delete]');
        if (revisionDelete) { self.deleteRevision(revisionDelete.getAttribute('data-document-revision-delete')); return; }
        var revisionOpen = e.target.closest('[data-document-revision-open]');
        if (revisionOpen) { self.loadRevision(revisionOpen.getAttribute('data-document-revision-open')); }
        if (e.target.closest('[data-document-revisions-clear]')) { self.clearRevisions(); }
        if (e.target.closest('[data-document-revision-restore]')) { self.restoreRevision(); }

        var apiTokenCopy = e.target.closest('[data-api-token-copy]');
        if (apiTokenCopy) { e.preventDefault(); self.copyApiToken(); return; }
        var apiTokenRevoke = e.target.closest('[data-api-token-revoke]');
        if (apiTokenRevoke) { e.preventDefault(); self.revokeApiToken(apiTokenRevoke.closest('[data-api-token-row]')); return; }

		if (e.target.closest('[data-document-submit-stay]')) { self.submit(true); }
		var conditionUndo = e.target.closest('[data-condition-action-undo]');
		if (conditionUndo) { e.preventDefault(); self.undoDocumentFieldValueAction(conditionUndo.closest('.ax-document-field')); }
      });

      if (this.form) {
        this.form.addEventListener('submit', function (e) {
          e.preventDefault();
          self.submit(false);
        });
		this.form.addEventListener('input', function (e) {
		  if (!e.target.matches('[data-document-term-query]')) { self.setDirty(true); }
		  self.clearDocumentFieldValueAction(e.target.closest('.ax-document-field'), true);
		  self.applyFieldConditions();
		});
		this.form.addEventListener('change', function (e) { self.setDirty(true); self.clearDocumentFieldValueAction(e.target.closest('.ax-document-field'), true); self.applyFieldConditions(); });
        var aliasInput = this.field('document_alias');
        this.aliasTouched = !!(aliasInput && aliasInput.value.trim());
        this.form.querySelectorAll('[data-document-boolean]').forEach(function (input) { self.updateBoolean(input); });
        this.updateSeo();
        this.updateAliasTemplateHint();
        this.initTermInputs();
        this.applyFieldConditions();
		this.initWorkspace();
        this.setDirty(false);
		window.setTimeout(function () { self.checkLocalDraft(); }, 180);
        window.addEventListener('beforeunload', function (e) {
          if (!self.dirty || self.submitting) { return; }
          e.preventDefault();
          e.returnValue = '';
        });
      }

		document.addEventListener('change', function (e) {
		  if (e.target.matches('[data-document-workspace-select]')) {
			self.setWorkspacePanel(e.target.value, true);
		  }
		});

		document.addEventListener('keydown', function (e) {
		  var current = e.target.closest('[data-document-workspace-tab]');
		  if (!current || ['ArrowLeft', 'ArrowRight', 'Home', 'End'].indexOf(e.key) === -1) { return; }
		  var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-document-workspace-tab]')).filter(function (tab) {
			return tab.getAttribute('aria-disabled') !== 'true';
		  });
		  if (!tabs.length) { return; }
		  e.preventDefault();
		  var index = tabs.indexOf(current);
		  if (e.key === 'Home') { index = 0; }
		  else if (e.key === 'End') { index = tabs.length - 1; }
		  else if (e.key === 'ArrowLeft') { index = (index - 1 + tabs.length) % tabs.length; }
		  else { index = (index + 1) % tabs.length; }
		  self.setWorkspacePanel(tabs[index].getAttribute('data-document-workspace-tab'), true);
		});

		document.addEventListener('submit', function (e) {
		  var presetForm = e.target.closest('[data-document-preset-form]');
		  if (presetForm) {
			e.preventDefault();
			self.saveCreationPreset(presetForm);
			return;
		  }
		  var aliasForm = e.target.closest('[data-document-alias-form]');
        if (aliasForm) {
          e.preventDefault();
          self.submitAlias(aliasForm);
          return;
        }
        var remarkForm = e.target.closest('[data-document-remark-form]');
        if (remarkForm) {
          e.preventDefault();
          self.submitRemark(remarkForm);
          return;
        }
        var apiTokenForm = e.target.closest('[data-api-token-form]');
        if (apiTokenForm) {
          e.preventDefault();
          self.issueApiToken(apiTokenForm);
          return;
        }
        var filter = e.target.closest('.documents-filter');
        if (!filter) { return; }
        e.preventDefault();
        self.applyFilters(filter, true);
      });

      document.addEventListener('input', function (e) {
        if (e.target.matches('[data-document-create-search]')) {
          self.filterCreateRubrics(e.target.value);
          return;
        }
        if (e.target.matches('[data-document-color-picker]')) {
          var pickerControl = e.target.closest('[data-document-color-control]');
          var colorValue = pickerControl ? pickerControl.querySelector('[data-document-color-value]') : null;
          if (colorValue) { colorValue.value = String(e.target.value || '').toLowerCase(); }
        }
        if (e.target.matches('[data-document-color-value]')) {
          var valueControl = e.target.closest('[data-document-color-control]');
          var colorPicker = valueControl ? valueControl.querySelector('[data-document-color-picker]') : null;
          var color = String(e.target.value || '').trim().toLowerCase();
          if (/^#?[0-9a-f]{3}$/.test(color)) {
            color = color.replace(/^#/, '');
            color = '#' + color[0] + color[0] + color[1] + color[1] + color[2] + color[2];
          } else if (/^#?[0-9a-f]{6}$/.test(color)) {
            color = '#' + color.replace(/^#/, '');
          } else {
            color = '';
          }
          if (colorPicker && color) { colorPicker.value = color; }
        }
        if (self.form && e.target === self.field('document_alias')) {
          self.aliasTouched = true;
          self.scheduleAliasCheck();
          self.updateSeo();
          return;
        }
		if (self.form && e.target === self.field('document_short_alias')) {
			self.scheduleShortAliasCheck();
			return;
		}
        if (self.form && e.target.matches('[data-slug-source]')) {
          self.syncSlugFromTitle();
          self.updateSeo();
        }
        if (self.form && e.target === self.field('document_meta_description')) {
          self.updateSeo();
        }
        var filter = e.target.closest('.documents-filter');
        if (!filter || !e.target.matches('input[type="search"]')) { return; }
        clearTimeout(self.filterTimer);
        self.filterTimer = setTimeout(function () { self.applyFilters(filter, true); }, 350);
      });

		document.addEventListener('change', function (e) {
		  if (e.target.matches('[data-document-revision-group]')) {
			self.toggleRevisionGroup(e.target.getAttribute('data-document-revision-group'), e.target.checked);
			return;
		  }
		  if (e.target.matches('[data-document-revision-select]')) {
			self.updateRevisionSelection();
			return;
		  }
		  if (e.target.matches('[data-document-preset-form] [name="include_fields"]')) {
			self.updatePresetAssetsState(e.target.closest('[data-document-preset-form]'));
		  }
		  if (e.target.matches('[data-document-boolean]')) {
          self.updateBoolean(e.target);
        }
        if (e.target.matches('[data-documents-check-all]')) {
          document.querySelectorAll('[data-document-check]').forEach(function (box) { box.checked = e.target.checked; });
          self.updateBulk();
          return;
        }
        if (e.target.matches('[data-document-check]')) { self.updateBulk(); return; }
        if (e.target.matches('[data-document-view-days]')) {
          self.loadViews(e.target.value, true);
          return;
        }
        if (e.target.matches('[data-document-rubric-select]')) {
          self.changeCreateRubric(e.target.value);
          return;
        }
        if (self.form && e.target === self.field('document_published') && !self.aliasTouched) {
          self.syncSlugFromTitle();
        }
        var filter = e.target.closest('.documents-filter');
        if (!filter || !e.target.matches('select')) { return; }
        self.applyFilters(filter, true);
      });

      document.addEventListener('change', function (e) {
        if (!e.target.matches('[data-document-media-files]')) { return; }
		var replace = e.target.getAttribute('data-replace-upload') === '1';
		e.target.removeAttribute('data-replace-upload');
        self.uploadMediaFiles(e.target.closest('[data-document-media-list], [data-document-media-single]'), e.target.files, replace);
        e.target.value = '';
      });

      document.addEventListener('dragover', function (e) {
        var drop = e.target.closest('[data-document-media-drop]');
        if (!drop) { return; }
        e.preventDefault();
        drop.classList.add('is-dragover');
      });

      document.addEventListener('dragleave', function (e) {
        var drop = e.target.closest('[data-document-media-drop]');
        if (drop) { drop.classList.remove('is-dragover'); }
      });

      document.addEventListener('drop', function (e) {
        var drop = e.target.closest('[data-document-media-drop]');
        if (!drop) { return; }
        e.preventDefault();
        drop.classList.remove('is-dragover');
        self.uploadMediaFiles(drop.closest('[data-document-media-list], [data-document-media-single]'), e.dataTransfer ? e.dataTransfer.files : null);
      });

      // drag-and-drop сортировка строк медиа/значений (по ручке-грипу)
      document.addEventListener('dragstart', function (e) { self.rowDragStart(e); });
      document.addEventListener('dragover', function (e) { self.rowDragOver(e); });
      document.addEventListener('drop', function (e) { if (self.dragRow) { e.preventDefault(); } });
      document.addEventListener('dragend', function () { self.rowDragEnd(); });

      document.addEventListener('blur', function (e) {
		if (!self.form) { return; }
		if (e.target === self.field('document_alias')) { self.checkAlias(false); }
		if (e.target === self.field('document_short_alias')) { self.checkShortAlias(false); }
      }, true);

      window.addEventListener('popstate', function () {
        if (document.querySelector('.documents-views-panel')) {
          var params = new URLSearchParams(window.location.search);
          self.loadViews(params.get('days') || 30, false);
          return;
        }
        self.applyFilterUrl(window.location.href, false);
      });

      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && String(e.key || '').toLowerCase() === 's' && self.form) {
          e.preventDefault();
		  self.saveActiveWorkspace();
        }
      });

      document.addEventListener('input', function (e) {
        if (e.target.matches('[data-document-catalog-search]')) {
          self.searchCatalog(e.target.closest('[data-document-catalog-field]'), e.target.value);
          return;
        }
        if (e.target.matches('[data-document-media-url]')) { self.updateMediaPreview(e.target); }
      });

      document.querySelectorAll('[data-document-catalog-field]').forEach(function (field) { self.updateCatalogFields(field); });

		var createDrawer = document.querySelector('[data-documents-create-auto]');
		if (createDrawer && Adminx.Drawer) { Adminx.Drawer.open(createDrawer.id); }
		document.querySelectorAll('[data-document-preset-form]').forEach(function (form) { self.updatePresetAssetsState(form); });

      document.addEventListener('adminx:drawer:closed', function (e) {
        var drawer = e.detail ? e.detail.drawer : null;
        if (!drawer || drawer.id !== 'documentApiTokenDrawer') { return; }
        if (drawer.getAttribute('data-token-created') === '1') {
          window.location.reload();
          return;
        }
        self.resetApiTokenForm();
      });
    },

    base: function () { return (this.form && this.form.getAttribute('data-base')) || Adminx.base(); },
    field: function (name) { return this.form.querySelector('[name="' + name + '"]'); },

    initTermInputs: function () {
      var self = this;
      if (!this.form) { return; }
      this.form.querySelectorAll('[data-document-term-input]').forEach(function (field) {
        if (field._documentTermReady) { return; }
        field._documentTermReady = true;
        field._termItems = self.normalizeTerms((field.querySelector('[data-document-term-value]') || {}).value || '');
        field._termActive = -1;
        field._termOpen = false;
        self.renderTermChips(field);

        var box = field.querySelector('[data-document-term-box]');
        var input = field.querySelector('[data-document-term-query]');
        var list = field.querySelector('[data-document-term-options]');
        if (!box || !input || !list) { return; }

        box.addEventListener('click', function (e) {
          if (e.target === box) { input.focus(); }
        });
        input.addEventListener('focus', function () {
          field._termOpen = true;
          if (String(input.value || '').trim()) {
            self.searchTermSuggestions(field, input.value, true);
          }
        });
        input.addEventListener('input', function () {
          if (!String(input.value || '').trim()) {
            self.closeTermSuggestions(field);
            return;
          }
          field._termOpen = true;
          clearTimeout(field._termTimer);
          field._termTimer = setTimeout(function () { self.searchTermSuggestions(field, input.value, true); }, 180);
        });
        input.addEventListener('keydown', function (e) { self.termKeydown(field, e); });
        input.addEventListener('paste', function () {
          setTimeout(function () {
            if (!/[,;\n\r]/.test(input.value)) { return; }
            self.addTerms(field, input.value);
            input.value = '';
            self.closeTermSuggestions(field);
          }, 0);
        });
        field.addEventListener('click', function (e) {
          var remove = e.target.closest('[data-document-term-remove]');
          if (remove) {
            e.preventDefault();
            self.removeTerm(field, remove.getAttribute('data-document-term-remove'));
            input.focus();
            return;
          }
          var option = e.target.closest('[data-document-term-option]');
          if (option) {
            e.preventDefault();
            self.addTerms(field, option.getAttribute('data-value') || '');
            input.value = '';
            self.closeTermSuggestions(field);
          }
        });
        list.addEventListener('mousedown', function (e) { e.preventDefault(); });
        field.addEventListener('focusout', function () {
          setTimeout(function () {
            if (!field.contains(document.activeElement)) { self.closeTermSuggestions(field); }
          }, 0);
        });
        document.addEventListener('click', function (e) {
          if (!field.contains(e.target)) { self.closeTermSuggestions(field); }
        });
      });
    },

    normalizeTerms: function (value) {
      var terms = Array.isArray(value) ? value : String(value || '').split(/[,;\n\r]+/);
      var seen = {};
      return terms.reduce(function (result, term) {
        term = String(term || '').replace(/[\x00-\x1f\x7f]/g, '').trim();
        var key = term.toLowerCase();
        if (term && !seen[key]) { seen[key] = true; result.push(term.slice(0, 254)); }
        return result;
      }, []);
    },

    renderTermChips: function (field) {
      var self = this;
      var box = field.querySelector('[data-document-term-box]');
      var input = field.querySelector('[data-document-term-query]');
      var hidden = field.querySelector('[data-document-term-value]');
      if (!box || !input || !hidden) { return; }
      box.querySelectorAll('[data-document-term-chip]').forEach(function (chip) { chip.remove(); });
      (field._termItems || []).forEach(function (term) {
        var chip = document.createElement('span');
        chip.className = 'chip documents-term-chip' + (field.getAttribute('data-term-kind') === 'tags' ? ' chip-teal' : '');
        chip.setAttribute('data-document-term-chip', '');
        var label = document.createElement('span');
        label.textContent = term;
        var remove = document.createElement('button');
        remove.type = 'button';
        remove.setAttribute('data-document-term-remove', term);
        remove.setAttribute('aria-label', 'Удалить «' + term + '»');
        remove.innerHTML = '<i class="ti ti-x" aria-hidden="true"></i>';
        chip.appendChild(label);
        chip.appendChild(remove);
        box.insertBefore(chip, input);
      });
      hidden.value = (field._termItems || []).join(', ');
      box.classList.toggle('is-empty', !(field._termItems || []).length);
      input.setAttribute('aria-label', field.getAttribute('data-term-kind') === 'tags' ? 'Добавить тег' : 'Добавить ключевое слово');
      self.positionTermInput(field);
    },

    positionTermInput: function (field) {
      var input = field.querySelector('[data-document-term-query]');
      if (input) { input.placeholder = (field._termItems || []).length ? 'Добавить ещё…' : (field.getAttribute('data-term-kind') === 'tags' ? 'Найти или добавить тег' : 'Найти или добавить ключевое слово'); }
    },

    addTerms: function (field, value) {
      var additions = this.normalizeTerms(value);
      if (!additions.length) { return false; }
      var items = field._termItems || [];
      var seen = {};
      items.forEach(function (item) { seen[String(item).toLowerCase()] = true; });
      var changed = false;
      additions.forEach(function (item) {
        var key = item.toLowerCase();
        if (!seen[key]) { items.push(item); seen[key] = true; changed = true; }
      });
      if (!changed) { return false; }
      field._termItems = items;
      this.renderTermChips(field);
      this.setDirty(true);
      return true;
    },

    removeTerm: function (field, value) {
      var key = String(value || '').toLowerCase();
      var next = (field._termItems || []).filter(function (item) { return String(item).toLowerCase() !== key; });
      if (next.length === (field._termItems || []).length) { return; }
      field._termItems = next;
      this.renderTermChips(field);
      this.setDirty(true);
    },

    termKeydown: function (field, e) {
      var input = field.querySelector('[data-document-term-query]');
      var options = Array.prototype.slice.call(field.querySelectorAll('[data-document-term-option]'));
      if (!input) { return; }
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        if (!String(input.value || '').trim()) { return; }
        e.preventDefault();
        if (!field._termOpen) { field._termOpen = true; this.searchTermSuggestions(field, input.value, true); }
        if (!options.length) { return; }
        var step = e.key === 'ArrowDown' ? 1 : -1;
        field._termActive = Math.max(0, Math.min(options.length - 1, (field._termActive < 0 ? (step > 0 ? -1 : options.length) : field._termActive) + step));
        options.forEach(function (option, index) { option.setAttribute('aria-selected', index === field._termActive ? 'true' : 'false'); });
        options[field._termActive].scrollIntoView({ block: 'nearest' });
        return;
      }
      if (e.key === 'Enter' || e.key === ',') {
        var option = options[field._termActive];
        var value = option ? option.getAttribute('data-value') : input.value;
        if (!String(value || '').trim()) { return; }
        e.preventDefault();
        this.addTerms(field, value);
        input.value = '';
        this.closeTermSuggestions(field);
        return;
      }
      if (e.key === 'Backspace' && !input.value && (field._termItems || []).length) {
        this.removeTerm(field, field._termItems[field._termItems.length - 1]);
        return;
      }
      if (e.key === 'Escape') {
        e.preventDefault();
        this.closeTermSuggestions(field);
      }
    },

    searchTermSuggestions: function (field, query, immediate) {
      var self = this;
      var list = field.querySelector('[data-document-term-options]');
      var input = field.querySelector('[data-document-term-query]');
      if (!list || !input || !field._termOpen) { return; }
      if (!String(query || '').trim()) {
        this.closeTermSuggestions(field);
        return;
      }
      clearTimeout(field._termTimer);
      if (!immediate) {
        field._termTimer = setTimeout(function () { self.searchTermSuggestions(field, query, true); }, 180);
        return;
      }
      if (field._termAbort && typeof field._termAbort.abort === 'function') { field._termAbort.abort(); }
      field._termAbort = typeof AbortController !== 'undefined' ? new AbortController() : null;
      field._termRequest = (field._termRequest || 0) + 1;
      var requestId = field._termRequest;
      this.renderTermLoading(field);
      var params = new URLSearchParams({
        kind: field.getAttribute('data-term-kind') || 'tags',
        q: String(query || '').trim(),
        rubric_id: field.getAttribute('data-term-rubric-id') || '0',
        limit: '12'
      });
      fetch((field.getAttribute('data-term-url') || (this.base() + '/documents/terms')) + '?' + params.toString(), {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        signal: field._termAbort ? field._termAbort.signal : undefined
      })
        .then(this.json)
        .then(function (payload) {
          if (requestId !== field._termRequest || !field._termOpen) { return; }
          self.renderTermSuggestions(field, ((payload.data || {}).items || []), query);
        })
        .catch(function (error) {
          if (!error || error.name !== 'AbortError') { self.renderTermError(field); }
        });
    },

    renderTermLoading: function (field) {
      var list = field.querySelector('[data-document-term-options]');
      if (!list) { return; }
      list.innerHTML = '<div class="documents-term-status"><i class="ti ti-loader-2"></i><span>Ищем совпадения…</span></div>';
      this.openTermSuggestions(field);
    },

    renderTermError: function (field) {
      var list = field.querySelector('[data-document-term-options]');
      if (!list || !field._termOpen) { return; }
      list.innerHTML = '<div class="documents-term-status is-error"><i class="ti ti-alert-circle"></i><span>Не удалось загрузить варианты</span></div>';
      this.openTermSuggestions(field);
    },

    renderTermSuggestions: function (field, items, query) {
      var self = this;
      var list = field.querySelector('[data-document-term-options]');
      if (!list) { return; }
      list.innerHTML = '';
      field._termActive = -1;
      var selected = {};
      (field._termItems || []).forEach(function (item) { selected[String(item).toLowerCase()] = true; });
      var exact = false;
      (items || []).forEach(function (item) {
        var value = String(item.value || '').trim();
        if (!value) { return; }
        if (value.toLowerCase() === String(query || '').trim().toLowerCase()) { exact = true; }
        if (selected[value.toLowerCase()]) { return; }
        list.appendChild(self.termOption(value, query, item, false));
      });
      query = String(query || '').trim();
      if (query && !exact && !selected[query.toLowerCase()]) {
        list.appendChild(this.termOption(query, query, {}, true));
      }
      if (!list.querySelector('[data-document-term-option]')) {
        var empty = document.createElement('div');
        empty.className = 'documents-term-status';
        empty.innerHTML = '<i class="ti ti-tags-off"></i><span>' + (query ? 'Совпадений нет' : 'Сохранённых значений пока нет') + '</span>';
        list.appendChild(empty);
      }
      this.openTermSuggestions(field);
    },

    termOption: function (value, query, item, isNew) {
      var option = document.createElement('button');
      option.type = 'button';
      option.className = 'combo-option documents-term-option' + (isNew ? ' is-new' : '');
      option.setAttribute('role', 'option');
      option.setAttribute('aria-selected', 'false');
      option.setAttribute('data-document-term-option', '');
      option.setAttribute('data-value', value);
      var icon = document.createElement('i');
      icon.className = isNew ? 'ti ti-plus' : 'ti ti-tag';
      icon.setAttribute('aria-hidden', 'true');
      var label = document.createElement('span');
      label.className = 'documents-term-option-label';
      if (isNew) {
        label.appendChild(document.createTextNode('Добавить «' + value + '»'));
      } else {
        this.highlightTerm(label, value, query);
      }
      option.appendChild(icon);
      option.appendChild(label);
      var meta = document.createElement('span');
      meta.className = 'co-sub';
      meta.textContent = isNew ? 'новое' : String(item.rubric_count > 0 ? item.rubric_count + ' в рубрике' : item.count + ' док.');
      option.appendChild(meta);
      return option;
    },

    highlightTerm: function (target, value, query) {
      var index = String(value).toLowerCase().indexOf(String(query || '').trim().toLowerCase());
      if (index < 0 || !String(query || '').trim()) { target.textContent = value; return; }
      var length = String(query).trim().length;
      target.appendChild(document.createTextNode(value.slice(0, index)));
      var mark = document.createElement('mark');
      mark.textContent = value.slice(index, index + length);
      target.appendChild(mark);
      target.appendChild(document.createTextNode(value.slice(index + length)));
    },

    openTermSuggestions: function (field) {
      var list = field.querySelector('[data-document-term-options]');
      var input = field.querySelector('[data-document-term-query]');
      if (!list || !input || !field._termOpen) { return; }
      list.hidden = false;
      list.classList.add('open');
      input.setAttribute('aria-expanded', 'true');
    },

    closeTermSuggestions: function (field) {
      var list = field.querySelector('[data-document-term-options]');
      var input = field.querySelector('[data-document-term-query]');
      field._termOpen = false;
      field._termActive = -1;
      if (list) { list.hidden = true; list.classList.remove('open'); }
      if (input) { input.setAttribute('aria-expanded', 'false'); }
    },

    filterUrl: function (form) {
      var params = new URLSearchParams(new FormData(form));
      params.delete('page');
      Array.from(params.keys()).forEach(function (key) {
        if (String(params.get(key) || '') === '' || String(params.get(key)) === '0') { params.delete(key); }
      });
      var query = params.toString();
      return (form.getAttribute('action') || (this.base() + '/documents')) + (query ? '?' + query : '');
    },

    applyFilters: function (form, push) {
      if (!form) { return; }
      clearTimeout(this.filterTimer);
      this.filterTimer = null;
      this.applyFilterUrl(this.filterUrl(form), push);
    },

    applyFilterUrl: function (url, push) {
      var self = this;
      var requestId = ++this.filterRequest;
      var active = document.activeElement;
      var focus = active && active.closest && active.closest('.documents-filter') ? {
        name: active.getAttribute('name') || '',
        start: typeof active.selectionStart === 'number' ? active.selectionStart : null,
        end: typeof active.selectionEnd === 'number' ? active.selectionEnd : null
      } : null;
      if (this.filterAbort && typeof this.filterAbort.abort === 'function') { this.filterAbort.abort(); }
      this.filterAbort = typeof AbortController !== 'undefined' ? new AbortController() : null;
      Adminx.Loader.show();
      fetch(url, { method: 'GET', headers: { 'Accept': 'text/html' }, credentials: 'same-origin', signal: this.filterAbort ? this.filterAbort.signal : undefined })
        .then(function (res) {
          return res.text().then(function (html) {
            if (!res.ok) { throw new Error('HTTP ' + res.status); }
            if (requestId === self.filterRequest) { self.replaceList(html, url, push, focus); }
          });
        })
        .catch(function (error) {
          if (!error || error.name !== 'AbortError') { Adminx.Toast.show('Не удалось применить фильтры', 'error'); }
        })
        .finally(function () {
          if (requestId === self.filterRequest) { self.filterAbort = null; Adminx.Loader.hide(); }
        });
    },

    loadViews: function (days, push) {
      var self = this;
      var url = this.base() + '/documents/views?days=' + encodeURIComponent(days || 30);
      Adminx.Loader.show();
      fetch(url, { method: 'GET', headers: { 'Accept': 'text/html' }, credentials: 'same-origin' })
        .then(function (res) { return res.text().then(function (html) { if (!res.ok) { throw new Error('HTTP ' + res.status); } return html; }); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var next = doc.querySelector('.documents-views-panel');
          var current = document.querySelector('.documents-views-panel');
          if (!next || !current) { throw new Error('Views panel not found'); }
          current.replaceWith(next);
          if (push) { window.history.pushState({ adminxDocumentViews: true }, '', url); }
        })
        .catch(function () { Adminx.Toast.show('Не удалось загрузить статистику', 'error'); })
        .finally(function () { Adminx.Loader.hide(); });
    },

    clearViews: function () {
      var self = this;
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Очистить подневную статистику?',
        message: 'Все строки view_count будут удалены. Общие счётчики документов останутся без изменений.',
        confirmLabel: 'Очистить',
        onConfirm: function () {
          var data = new FormData();
          data.append('_csrf', self.csrf());
          self.ajax(self.base() + '/documents/views/clear', data, function (payload) {
            Adminx.Toast.show(payload.message || 'Статистика очищена', 'success');
            var days = document.querySelector('[data-document-view-days]');
            self.loadViews(days ? days.value : 30, false);
          });
        }
      });
    },

    replaceList: function (html, url, push, focus) {
      var doc = new DOMParser().parseFromString(html, 'text/html');
      ['.documents-summary', '.documents-panel'].forEach(function (selector) {
        var next = doc.querySelector(selector);
        var current = document.querySelector(selector);
        if (next && current) { current.replaceWith(next); }
      });
      if (push && window.history && window.history.pushState) {
        window.history.pushState({ adminxDocumentsFilters: true }, '', url);
      }
      if (focus && focus.name) {
        var nextFocus = document.querySelector('.documents-filter [name="' + focus.name.replace(/"/g, '\\"') + '"]');
        if (nextFocus) {
          nextFocus.focus();
          if (focus.start !== null && typeof nextFocus.setSelectionRange === 'function') {
            nextFocus.setSelectionRange(focus.start, focus.end);
          }
        }
      }
    },

    resetFilters: function () {
      var form = document.querySelector('.documents-filter');
      if (!form) { return; }
      this.applyFilterUrl(form.getAttribute('action') || (this.base() + '/documents'), true);
    },

    changeCreateRubric: function (rubricId) {
      if (!this.form || this.field('id').value !== '') { return; }
      this.dirty = false;
      this.submitting = true;
      window.location.href = this.base() + '/documents/create?rubric_id=' + encodeURIComponent(rubricId || '');
    },

	filterCreateRubrics: function (value) {
      var query = String(value || '').trim().toLowerCase();
      var visible = 0;
      document.querySelectorAll('[data-document-create-item]').forEach(function (item) {
        var match = !query || String(item.getAttribute('data-search') || '').indexOf(query) !== -1;
        item.hidden = !match;
        if (match) { visible++; }
      });
	  var empty = document.querySelector('[data-document-create-empty]');
	  if (empty) { empty.hidden = visible > 0; }
	  var current = document.querySelector('[data-document-create-item].is-current:not([hidden])');
	  if (!current) {
		var first = document.querySelector('[data-document-create-item]:not([hidden])');
		if (first) { this.selectCreateRubric(first.getAttribute('data-document-create-item')); }
	  }
	},

	selectCreateRubric: function (rubricId) {
	  rubricId = String(rubricId || '');
	  document.querySelectorAll('[data-document-create-item]').forEach(function (item) {
		var selected = item.getAttribute('data-document-create-item') === rubricId;
		item.classList.toggle('is-current', selected);
		item.setAttribute('aria-selected', selected ? 'true' : 'false');
	  });
	  document.querySelectorAll('[data-document-create-detail]').forEach(function (detail) {
		detail.hidden = detail.getAttribute('data-document-create-detail') !== rubricId;
	  });
	},

	deleteCreationPreset: function (button) {
	  var id = parseInt(button.getAttribute('data-document-preset-delete'), 10) || 0;
	  if (!id) { return; }
	  var self = this;
	  Adminx.Confirm.open({
		kind: 'error',
		title: 'Удалить пресет создания?',
		message: 'Сохранённые документы не изменятся. Заготовка исчезнет из выбора новых документов.',
		confirmLabel: 'Удалить',
		confirmClass: 'btn-danger',
		onConfirm: function () {
		  var data = new FormData();
		  data.append('_csrf', self.csrf());
		  self.ajax(self.base() + '/documents/presets/' + id + '/delete', data, function (payload) {
			Adminx.Toast.show(payload.message || 'Пресет удалён', 'success');
			var url = new URL(window.location.href);
			url.searchParams.set('create', '1');
			window.location.href = url.toString();
		  });
		}
	  });
	},

	saveCreationPreset: function (form) {
	  if (!form || form.getAttribute('aria-busy') === 'true') { return; }
	  form.querySelectorAll('.field-error').forEach(function (error) { error.textContent = ''; });
	  form.setAttribute('aria-busy', 'true');
	  var submit = form.querySelector('button[type="submit"]');
	  if (submit) { submit.disabled = true; }
	  var self = this;
	  this.ajax(form.getAttribute('action'), new FormData(form), function (payload) {
		form.removeAttribute('aria-busy');
		if (submit) { submit.disabled = false; }
		form.reset();
		self.updatePresetAssetsState(form);
		if (Adminx.Drawer) { Adminx.Drawer.close('documentPresetDrawer'); }
		Adminx.Toast.show(payload.message || 'Пресет создания сохранён', 'success');
	  }, function (payload) {
		form.removeAttribute('aria-busy');
		if (submit) { submit.disabled = false; }
		var errors = payload && payload.errors ? payload.errors : {};
		Object.keys(errors).forEach(function (key) {
		  var target = form.querySelector('[data-error="' + key + '"]');
		  if (target) { target.textContent = errors[key]; }
		});
		Adminx.Toast.show((payload && payload.message) || 'Не удалось сохранить пресет', 'error');
		return false;
	  });
	},

	updatePresetAssetsState: function (form) {
	  if (!form) { return; }
	  var fields = form.querySelector('[name="include_fields"]');
	  var assets = form.querySelector('[name="include_assets"]');
	  if (!assets) { return; }
	  assets.disabled = !fields || !fields.checked;
	  if (assets.disabled) { assets.checked = false; }
	},

    activateFieldGroup: function (index) {
      document.querySelectorAll('[data-document-field-tab]').forEach(function (tab) {
        tab.classList.toggle('is-active', tab.getAttribute('data-document-field-tab') === String(index));
        tab.setAttribute('aria-selected', tab.getAttribute('data-document-field-tab') === String(index) ? 'true' : 'false');
      });
      document.querySelectorAll('[data-document-field-panel]').forEach(function (panel) {
        panel.hidden = panel.getAttribute('data-document-field-panel') !== String(index);
      });
      setTimeout(function () {
        if (Adminx.CodeEditor) { Adminx.CodeEditor.refreshAll(); }
      }, 80);
    },

    applyFieldConditions: function () {
      if (!this.form) { return; }
      var context = this.documentFieldConditionContext();
      var self = this;
	  this.form.querySelectorAll('[data-document-field-panel]').forEach(function (panel) {
		var raw = panel.getAttribute('data-group-condition') || '';
		var condition = panel._documentGroupCondition;
		if (condition === undefined) {
		  try { condition = raw ? JSON.parse(raw) : null; }
		  catch (e) { condition = null; }
		  panel._documentGroupCondition = condition;
		}
		var matched = condition && condition.tree ? self.documentConditionMatches(condition.tree, context) : true;
		var mode = condition && condition.mode ? condition.mode : 'show';
		panel._documentGroupState = {
		  visible: !condition || !condition.tree || mode === 'lock' ? true : (mode === 'hide' ? !matched : matched),
		  locked: !!(condition && condition.tree && matched && mode === 'lock')
		};
		var tab = self.form.querySelector('[data-document-field-tab="' + panel.getAttribute('data-document-field-panel') + '"]');
		if (tab) {
		  tab.classList.toggle('is-condition-locked', panel._documentGroupState.locked);
		  var marker = tab.querySelector('.documents-group-lock');
		  if (panel._documentGroupState.locked && !marker) { tab.insertAdjacentHTML('beforeend', '<i class="ti ti-lock documents-group-lock" aria-label="Только чтение"></i>'); }
		  else if (!panel._documentGroupState.locked && marker) { marker.remove(); }
		}
	  });
      this.form.querySelectorAll('.ax-document-field[data-rubric-field-id]').forEach(function (field) {
        var raw = field.getAttribute('data-field-condition') || '';
        var condition = field._documentCondition;
        if (condition === undefined) {
          try { condition = raw ? JSON.parse(raw) : null; }
          catch (e) { condition = null; }
          field._documentCondition = condition;
		}
		var matched = condition && condition.tree ? self.documentConditionMatches(condition.tree, context) : true;
		var matchedBefore = field._documentConditionMatched;
		field._documentConditionMatched = matched;
		if (self.conditionsInitialized && matchedBefore === false && matched && condition && ['set', 'clear'].indexOf(String(condition.value_action || '')) !== -1) {
		  self.applyDocumentFieldValueAction(field, String(condition.value_action), Object.prototype.hasOwnProperty.call(condition, 'action_value') ? String(condition.action_value) : '');
		}
		var mode = condition && condition.mode ? condition.mode : 'show';
		var fieldVisible = !condition || !condition.tree || mode === 'lock' ? true : (mode === 'hide' ? !matched : matched);
		var group = field.closest('[data-document-field-panel]');
		var groupState = group && group._documentGroupState ? group._documentGroupState : { visible: true, locked: false };
		var visible = groupState.visible && fieldVisible;
        var required = visible && (field.getAttribute('data-base-required') === '1' || !!(condition && condition.required));
		var locked = visible && (groupState.locked || (matched && mode === 'lock'));
		var allowedValues = visible && matched && condition && Array.isArray(condition.allowed_values) ? condition.allowed_values.map(String) : null;
        self.setDocumentFieldConditionState(field, visible, required, locked, allowedValues);
	  });
	  this.conditionsInitialized = true;
	  this.updateCatalogGroups();
	},

	fieldValueControls: function (field) {
	  if (!field) { return []; }
	  return Array.prototype.slice.call(field.querySelectorAll('input[name], select[name], textarea[name]')).filter(function (control) {
		return control.type !== 'hidden' && !control.hasAttribute('data-document-term-query');
	  });
	},

	applyDocumentFieldValueAction: function (field, action, value) {
	  var controls = this.fieldValueControls(field);
	  if (!field || !controls.length) { return; }
	  this.setDocumentFieldValueActionOverride(field, false);
	  field._conditionActionUndo = controls.map(function (control) {
		return {
		  control: control,
		  value: control.value,
		  checked: !!control.checked,
		  selected: control.tagName === 'SELECT' && control.multiple
			? Array.prototype.map.call(control.selectedOptions, function (option) { return option.value; }) : null
		};
	  });
	  field._conditionActionApplying = true;
	  controls.forEach(function (control) {
		if (control.tagName === 'SELECT') {
		  if (control.multiple) {
			Array.prototype.forEach.call(control.options, function (option) { option.selected = action === 'set' && String(option.value) === value; });
		  } else { control.value = action === 'set' ? value : ''; }
		} else if (control.type === 'radio') {
		  control.checked = action === 'set' && String(control.value) === value;
		} else if (control.type === 'checkbox') {
		  control.checked = action === 'set' && ['1', 'true', 'yes', 'on'].indexOf(value.toLowerCase()) !== -1;
		} else {
		  control.value = action === 'set' ? value : '';
		}
	  });
	  field._conditionActionApplying = false;
	  this.renderDocumentFieldValueAction(field, action);
	  this.setDirty(true);
	},

	renderDocumentFieldValueAction: function (field, action) {
	  var header = field ? field.querySelector('.ax-attr-label') : null;
	  if (!header) { return; }
	  var marker = header.querySelector('.ax-condition-action');
	  if (!marker) {
		header.insertAdjacentHTML('beforeend', '<span class="ax-condition-action"><i class="ti ti-wand"></i><span></span><button type="button" data-condition-action-undo aria-label="Отменить изменение" data-tooltip-left="Отменить"><i class="ti ti-arrow-back-up"></i></button></span>');
		marker = header.querySelector('.ax-condition-action');
	  }
	  var copy = marker.querySelector('span');
	  if (copy) { copy.textContent = action === 'clear' ? 'Очищено условием' : 'Установлено условием'; }
	},

	clearDocumentFieldValueAction: function (field, markOverride) {
	  if (!field || field._conditionActionApplying) { return; }
	  var hadAction = Array.isArray(field._conditionActionUndo) || !!field.querySelector('.ax-condition-action');
	  if (markOverride && hadAction) { this.setDocumentFieldValueActionOverride(field, true); }
	  field._conditionActionUndo = null;
	  var marker = field.querySelector('.ax-condition-action');
	  if (marker) { marker.remove(); }
	},

	setDocumentFieldValueActionOverride: function (field, enabled) {
	  if (!field) { return; }
	  var control = field.querySelector('[data-condition-action-override]');
	  if (!enabled) {
		if (control) { control.remove(); }
		return;
	  }
	  if (control) { return; }
	  var fieldId = parseInt(field.getAttribute('data-rubric-field-id'), 10) || 0;
	  if (fieldId <= 0) { return; }
	  control = document.createElement('input');
	  control.type = 'hidden';
	  control.name = 'fields[' + fieldId + '][_condition_action_override]';
	  control.value = '1';
	  control.setAttribute('data-condition-action-override', '');
	  field.appendChild(control);
	},

	undoDocumentFieldValueAction: function (field) {
	  if (!field || !Array.isArray(field._conditionActionUndo)) { return; }
	  field._conditionActionApplying = true;
	  field._conditionActionUndo.forEach(function (state) {
		var control = state.control;
		if (!control || !control.isConnected) { return; }
		control.value = state.value;
		if (control.type === 'checkbox' || control.type === 'radio') { control.checked = state.checked; }
		if (control.tagName === 'SELECT' && control.multiple && Array.isArray(state.selected)) {
		  Array.prototype.forEach.call(control.options, function (option) { option.selected = state.selected.indexOf(option.value) !== -1; });
		}
	  });
	  field._conditionActionApplying = false;
	  this.clearDocumentFieldValueAction(field, true);
	  this.setDirty(true);
	  this.applyFieldConditions();
	},

    documentFieldConditionContext: function () {
      var context = {};
	  this.form.querySelectorAll('[data-document-condition-source]').forEach(function (source) {
		var id = String(parseInt(source.getAttribute('data-field-id'), 10) || 0);
		var alias = String(source.getAttribute('data-field-alias') || '').trim().toLowerCase();
		var values = [String(source.value == null ? '' : source.value)];
		context['id:' + id] = values;
		if (alias) { context['alias:' + alias] = values; }
	  });
      this.form.querySelectorAll('.ax-document-field[data-rubric-field-id]').forEach(function (field) {
        var values = [];
        var controls = Array.prototype.slice.call(field.querySelectorAll('input[name], select[name], textarea[name]'));
        controls.forEach(function (control) {
          if ((control.type === 'checkbox' || control.type === 'radio') && !control.checked) { return; }
          if (control.type === 'hidden') {
            var choices = controls.filter(function (other) { return other.name === control.name && (other.type === 'checkbox' || other.type === 'radio'); });
            if (choices.some(function (choice) { return choice.checked; })) { return; }
          }
          if (control.tagName === 'SELECT' && control.multiple) {
            Array.prototype.forEach.call(control.selectedOptions, function (option) { values.push(String(option.value)); });
          } else {
            values.push(String(control.value == null ? '' : control.value));
          }
        });
        var id = String(parseInt(field.getAttribute('data-rubric-field-id'), 10) || 0);
        var alias = String(field.getAttribute('data-field-alias') || '').trim().toLowerCase();
        context['id:' + id] = values;
        if (alias) { context['alias:' + alias] = values; }
      });
      return context;
    },

    documentConditionMatches: function (node, context) {
      if (node && Array.isArray(node.items)) {
        var join = node.operator === 'or' ? 'or' : 'and';
        if (!node.items.length) { return true; }
        for (var i = 0; i < node.items.length; i++) {
          var result = this.documentConditionMatches(node.items[i], context);
          if (join === 'and' && !result) { return false; }
          if (join === 'or' && result) { return true; }
        }
        return join === 'and';
      }
      var values = context[String((node || {}).field || '')] || [''];
      return this.documentConditionCompare(values, String((node || {}).operator || 'equals'), String((node || {}).value || ''));
    },

    documentConditionCompare: function (values, operator, expected) {
      values = Array.isArray(values) ? values.map(String) : [String(values == null ? '' : values)];
      var nonEmpty = values.filter(function (value) { return value.trim() !== ''; });
      if (operator === 'empty') { return nonEmpty.length === 0; }
      if (operator === 'not_empty') { return nonEmpty.length > 0; }
      if (operator === 'equals') { return values.indexOf(expected) !== -1; }
      if (operator === 'not_equals') { return values.indexOf(expected) === -1; }
      if (operator === 'contains' || operator === 'not_contains') {
        var found = expected !== '' && values.some(function (value) { return value.toLowerCase().indexOf(expected.toLowerCase()) !== -1; });
        return operator === 'contains' ? found : !found;
      }
      if (operator === 'in' || operator === 'not_in') {
        var options = expected.split(/[\r\n,]+/).map(function (value) { return value.trim(); }).filter(Boolean);
        var included = values.some(function (value) { return options.indexOf(value) !== -1; });
        return operator === 'in' ? included : !included;
      }
      var number = nonEmpty.length ? Number(nonEmpty[0].replace(',', '.')) : NaN;
      var target = Number(expected.replace(',', '.'));
      if (!Number.isFinite(number) || !Number.isFinite(target)) { return false; }
      if (operator === 'greater') { return number > target; }
      if (operator === 'greater_or_equal') { return number >= target; }
      if (operator === 'less') { return number < target; }
      if (operator === 'less_or_equal') { return number <= target; }
      return false;
    },

    setDocumentFieldConditionState: function (field, visible, required, locked, allowedValues) {
      field.hidden = !visible;
      field.classList.toggle('is-condition-hidden', !visible);
      field.classList.toggle('is-condition-locked', !!locked);
      field.classList.toggle('is-required', required);
      field.setAttribute('aria-hidden', visible ? 'false' : 'true');
      field.setAttribute('aria-disabled', locked ? 'true' : 'false');
	  field.querySelectorAll('input, select, textarea, button').forEach(function (control) {
		if (control.hasAttribute('data-condition-action-undo')) { return; }
        if ((!visible || locked) && !control.disabled) {
          control.disabled = true;
          control.setAttribute('data-condition-disabled', '1');
        } else if (visible && !locked && control.getAttribute('data-condition-disabled') === '1') {
          control.disabled = false;
          control.removeAttribute('data-condition-disabled');
        }
      });
	  this.setDocumentFieldAllowedValues(field, allowedValues);
      field.querySelectorAll('[contenteditable]').forEach(function (control) {
        if ((!visible || locked) && control.getAttribute('contenteditable') !== 'false') {
          control.setAttribute('data-condition-contenteditable', control.getAttribute('contenteditable') || 'true');
          control.setAttribute('contenteditable', 'false');
        } else if (visible && !locked && control.hasAttribute('data-condition-contenteditable')) {
          control.setAttribute('contenteditable', control.getAttribute('data-condition-contenteditable'));
          control.removeAttribute('data-condition-contenteditable');
        }
      });
      var label = field.querySelector('.ax-label');
      var marker = label ? label.querySelector('.ax-required') : null;
      if (required && label && !marker) {
        label.insertAdjacentHTML('beforeend', '<span class="ax-required" title="Обязательное поле" aria-hidden="true">*</span>');
      } else if (!required && marker) {
        marker.remove();
      }
      var header = field.querySelector('.ax-attr-label');
      var lockMarker = header ? header.querySelector('.ax-condition-lock') : null;
      if (locked && header && !lockMarker) {
        header.insertAdjacentHTML('beforeend', '<span class="ax-condition-lock"><i class="ti ti-lock"></i>Только чтение</span>');
      } else if (!locked && lockMarker) {
        lockMarker.remove();
      }
    },

	setDocumentFieldAllowedValues: function (field, allowedValues) {
	  var limited = Array.isArray(allowedValues) && allowedValues.length > 0;
	  var allowed = limited ? allowedValues.map(String) : [];
	  var invalidSelected = false;
	  field.querySelectorAll('select option').forEach(function (option) {
		var unavailable = limited && option.value !== '' && allowed.indexOf(String(option.value)) === -1;
		if (unavailable && option.selected) { invalidSelected = true; }
		option.disabled = unavailable && !option.selected;
		option.classList.toggle('is-condition-unavailable', unavailable);
	  });
	  field.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(function (control) {
		var unavailable = limited && allowed.indexOf(String(control.value)) === -1;
		if (unavailable && control.checked) { invalidSelected = true; }
		if (unavailable && !control.checked) {
		  control.disabled = true;
		  control.setAttribute('data-condition-option-disabled', '1');
		} else if ((!unavailable || control.checked) && control.getAttribute('data-condition-option-disabled') === '1'
		  && !field.hidden && !field.classList.contains('is-condition-locked')) {
		  control.disabled = false;
		  control.removeAttribute('data-condition-option-disabled');
		}
		var label = control.closest('label');
		if (label) { label.classList.toggle('is-condition-unavailable', unavailable); }
	  });
	  var header = field.querySelector('.ax-attr-label');
	  var marker = header ? header.querySelector('.ax-condition-options') : null;
	  if (limited && header && !marker) {
		header.insertAdjacentHTML('beforeend', '<span class="ax-condition-options"><i class="ti ti-filter"></i><span></span></span>');
		marker = header.querySelector('.ax-condition-options');
	  }
	  if (marker) {
		marker.hidden = !limited;
		marker.classList.toggle('is-warning', invalidSelected);
		var copy = marker.querySelector('span');
		if (copy) { copy.textContent = invalidSelected ? 'Выберите доступный вариант' : ('Доступно: ' + allowed.length); }
	  }
	},

    updateCatalogFields: function (catalog) {
      if (!catalog) { return; }
      var tokens = catalog.querySelectorAll('[data-document-catalog-token]');
      var limited = catalog.getAttribute('data-catalog-limits-fields') === '1';
      var allowed = {};
      tokens.forEach(function (token) {
        String(token.getAttribute('data-catalog-fields') || '').split(',').forEach(function (id) { if (id) { allowed[String(Number(id))] = true; } });
      });
      document.querySelectorAll('[data-document-catalog-field]').forEach(function (other) {
        var host = other.closest('[data-rubric-field-id]');
        if (host) { allowed[String(host.getAttribute('data-rubric-field-id'))] = true; }
      });
      document.querySelectorAll('.ax-document-field[data-rubric-field-id]').forEach(function (field) {
        var hide = limited && !allowed[String(field.getAttribute('data-rubric-field-id'))];
        field.classList.toggle('is-catalog-hidden', hide);
      });
      var empty = catalog.querySelector('[data-document-catalog-empty]');
      if (empty) { empty.hidden = tokens.length > 0; }
      this.updateCatalogGroups();
    },

    addCatalogToken: function (option) {
      var catalog = option.closest('[data-document-catalog-field]');
      if (!catalog) { return; }
      var tokens = catalog.querySelector('[data-document-catalog-tokens]');
      var fieldId = catalog.getAttribute('data-document-catalog-field');
      var id = option.getAttribute('data-catalog-id');
      if (!tokens || catalog.querySelector('[data-document-catalog-token][data-catalog-id="' + id + '"]')) { return; }
      var name = option.getAttribute('data-catalog-name') || ('#' + id);
      var fields = option.getAttribute('data-catalog-fields') || '';
      var chip = document.createElement('span');
      chip.className = 'documents-catalog-token';
      chip.setAttribute('data-document-catalog-token', '');
      chip.setAttribute('data-catalog-id', id);
      chip.setAttribute('data-catalog-fields', fields);
      chip.innerHTML = '<i class="ti ti-folder documents-catalog-token-icon" aria-hidden="true"></i>'
        + '<span class="documents-catalog-token-name">' + this.esc(name) + '</span>'
        + '<input type="hidden" name="fields[' + fieldId + '][catalog_ids][]" value="' + this.esc(id) + '">'
        + '<button class="documents-catalog-token-remove" type="button" data-document-catalog-remove aria-label="Убрать раздел"><i class="ti ti-x"></i></button>';
      tokens.appendChild(chip);
      option.classList.add('is-picked');
      var dd = catalog.querySelector('.dropdown');
      if (dd) { dd.classList.remove('open'); }
      var search = catalog.querySelector('[data-document-catalog-search]');
      if (search) { search.value = ''; this.searchCatalog(catalog, ''); }
      this.updateCatalogFields(catalog);
      this.setDirty(true);
    },

    removeCatalogToken: function (token) {
      if (!token) { return; }
      var catalog = token.closest('[data-document-catalog-field]');
      var id = token.getAttribute('data-catalog-id');
      if (token.parentNode) { token.parentNode.removeChild(token); }
      if (!catalog) { return; }
      var option = catalog.querySelector('[data-document-catalog-option][data-catalog-id="' + id + '"]');
      if (option) { option.classList.remove('is-picked'); }
      this.updateCatalogFields(catalog);
      this.setDirty(true);
    },

    updateCatalogGroups: function () {
      var firstVisible = null;
      document.querySelectorAll('[data-document-field-panel]').forEach(function (panel) {
        var count = panel.querySelectorAll('.ax-document-field:not(.is-catalog-hidden):not(.is-condition-hidden)').length;
        var index = panel.getAttribute('data-document-field-panel');
        var tab = document.querySelector('[data-document-field-tab="' + index + '"]');
        if (tab) {
          tab.hidden = count === 0;
          var badge = tab.querySelector('.tab-count');
          if (badge) { badge.textContent = count; }
        }
        if (count > 0 && firstVisible === null) { firstVisible = index; }
      });
      var active = document.querySelector('[data-document-field-tab].is-active:not([hidden])');
      if (!active && firstVisible !== null) { this.activateFieldGroup(firstVisible); }
    },

    searchCatalog: function (catalog, value) {
      if (!catalog) { return; }
      var normalize = function (input) {
        return String(input || '').toLowerCase().replace(/ё/g, 'е').replace(/[^0-9a-zа-я]+/gi, ' ').trim();
      };
      var query = normalize(value);
      var tokens = query ? query.split(/\s+/) : [];
      var nodes = Array.prototype.slice.call(catalog.querySelectorAll('[data-document-catalog-node]'));
      nodes.forEach(function (node) {
        var haystack = normalize(node.getAttribute('data-search'));
        var matches = !tokens.length || tokens.every(function (token) { return haystack.indexOf(token) >= 0; });
        node.classList.toggle('is-filtered', !matches);
      });
      nodes.forEach(function (node) {
        if (node.classList.contains('is-filtered')) { return; }
        var parent = node.parentElement ? node.parentElement.closest('[data-document-catalog-node]') : null;
        while (parent) {
          parent.classList.remove('is-filtered');
          parent = parent.parentElement ? parent.parentElement.closest('[data-document-catalog-node]') : null;
        }
      });
    },

    submit: function (stay, reviewed) {
      if (!this.form || this.submitting) { return; }
      var self = this;
      if (!reviewed && Adminx.RichEditor && Adminx.RichEditor.reviewBeforeSave
          && Adminx.RichEditor.reviewBeforeSave(this.form, function () { self.submit(stay, true); })) { return; }
      var id = parseInt(this.field('id').value, 10) || 0;
      this.clearErrors();
      this.syncEditors();
      this.reindexMediaLists();
      this.reindexValueLists();
	  if (!this.form.checkValidity()) {
		var nativeErrors = {};
		this.form.querySelectorAll(':invalid').forEach(function (field) {
		  if (field.name && !nativeErrors[field.name]) { nativeErrors[field.name] = field.validationMessage || 'Проверьте значение'; }
		});
		this.showErrors(nativeErrors);
		return;
	  }
      this.checkAlias(false, function (ok) {
        if (!ok) { return; }
		self.checkShortAlias(false, function (shortOk) {
			if (!shortOk) { return; }
			self.setSaving(true);
        var submitUrl = self.form.getAttribute('data-submit-url') || (self.base() + '/documents' + (id ? '/' + id : ''));
        var returnUrl = self.form.getAttribute('data-return-url') || (self.base() + '/documents');
        self.ajax(submitUrl, new FormData(self.form), function (payload) {
		  self.clearLocalDraft();
          self.setDirty(false);
          self.setSaving(false);
          Adminx.Toast.show(payload.message || 'Документ сохранён', 'success');
          if (payload.data && payload.data.snapshot_warning) {
            Adminx.Toast.show('Документ сохранён, но JSON-снимок не записан: ' + payload.data.snapshot_warning, 'warning');
          }
		  if (payload.data && payload.data.media_cleanup && payload.data.media_cleanup.errors && payload.data.media_cleanup.errors.length) {
			Adminx.Toast.show('Документ сохранён, но часть старых файлов не перемещена в корзину.', 'warning');
		  }
          if (payload.data && payload.data.id) {
            self.field('id').value = payload.data.id;
            self.form.setAttribute('data-id', payload.data.id);
            if (!id && self.form.getAttribute('data-submit-url')) {
              self.form.setAttribute('data-submit-url', self.base() + '/documents/' + payload.data.id);
            }
          }
		  if (payload.data && payload.data.document_version) {
			var versionField = self.field('document_version');
			if (versionField) { versionField.value = String(payload.data.document_version); }
		  }
          if (payload.data && payload.data.media_draft_token) {
            self.refreshMediaDraft(payload.data.media_draft_token, payload.data.id || id);
          }
		  self.resetMediaReplacements();
          self.refreshSavedHeading(payload.data && payload.data.editor_state);
          if (self.form.getAttribute('data-quick-edit') === '1') {
            self.refreshQuickEditOpener(!stay);
            return;
          }
          if (!stay) {
            window.location.href = returnUrl;
            return;
          }
          if (payload.redirect && !id) {
            var stayTemplate = self.form.getAttribute('data-stay-url-template');
            var stayUrl = stayTemplate && payload.data && payload.data.id ? stayTemplate.replace('{id}', payload.data.id) : payload.redirect;
            self.promoteCreatedDocument(payload.data.id, stayUrl);
          }
        }, function (payload) {
          self.setSaving(false);
		  if (payload.data && payload.data.conflict) {
			self.saveLocalDraft(true);
			self.showEditConflict(payload);
			return false;
		  }
          self.showErrors(payload.errors || {});
			});
		});
      });
    },

    setDirty: function (dirty) {
      if (!this.form) { return; }
      this.dirty = !!dirty;
	  if (this.dirty) { this.scheduleLocalDraft(); }
      this.setSaveState(this.submitting ? 'saving' : (this.dirty ? 'dirty' : 'saved'));
    },

	initWorkspace: function () {
	  if (!this.form || !document.querySelector('[data-document-workspace]')) { return; }
	  var panel = 'main';
	  try { panel = window.localStorage.getItem(this.workspaceKey()) || panel; } catch (e) {}
	  this.setWorkspacePanel(panel, false);
	},

	workspaceKey: function () {
	  var actor = this.form ? this.form.getAttribute('data-actor-id') || '0' : '0';
	  var rubric = this.form ? this.form.getAttribute('data-rubric-id') || '0' : '0';
	  var workspace = document.querySelector('[data-document-workspace]');
	  var kind = workspace && workspace.getAttribute('data-catalog-mode') === '1' ? 'product' : 'document';
	  return 'ave.adminx.document-workspace:' + actor + ':' + rubric + ':' + kind;
	},

	setWorkspacePanel: function (panel, persist) {
	  var workspace = document.querySelector('[data-document-workspace]');
	  if (!workspace) { return; }
	  var button = workspace.querySelector('[data-document-workspace-tab="' + panel + '"]');
	  if (!button || button.getAttribute('aria-disabled') === 'true') {
		panel = 'main';
		button = workspace.querySelector('[data-document-workspace-tab="main"]');
	  }
	  this.workspacePanel = panel;
	  workspace.setAttribute('data-active-panel', panel);
	  workspace.querySelectorAll('[data-document-workspace-tab]').forEach(function (tab) {
		var active = tab.getAttribute('data-document-workspace-tab') === panel;
		tab.classList.toggle('is-active', active);
		tab.setAttribute('aria-selected', active ? 'true' : 'false');
		tab.setAttribute('tabindex', active ? '0' : '-1');
	  });
	  var select = workspace.querySelector('[data-document-workspace-select]');
	  if (select) { select.value = panel; }
	  if (persist) {
		try { window.localStorage.setItem(this.workspaceKey(), panel); } catch (e) {}
	  }
	  window.setTimeout(function () {
		if (Adminx.CodeEditor) { Adminx.CodeEditor.refreshAll(); }
		if (persist && button && typeof button.focus === 'function' && document.activeElement !== select) {
		  button.focus({ preventScroll: true });
		}
	  }, 40);
	},

	workspaceForTarget: function (target) {
	  if (!target) { return 'main'; }
	  var productPanel = target.closest('[data-document-workspace-panel]');
	  if (productPanel) { return productPanel.getAttribute('data-document-workspace-panel') || 'main'; }
	  var section = target.closest('[data-document-section]');
	  return section ? section.getAttribute('data-document-section') || 'main' : 'main';
	},

	setWorkspaceForTarget: function (target) {
	  this.setWorkspacePanel(this.workspaceForTarget(target), true);
	},

	saveActiveWorkspace: function () {
	  var workspace = document.querySelector('[data-document-workspace]');
	  var panel = workspace ? workspace.getAttribute('data-active-panel') || 'main' : 'main';
	  if (panel === 'attributes' || panel === 'shipping' || panel === 'promotions' || panel === 'search') {
		var form = workspace.querySelector('[data-document-workspace-panel="' + panel + '"] form');
		if (form && typeof form.requestSubmit === 'function') { form.requestSubmit(); }
		else if (form) { form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })); }
		return;
	  }
	  if (panel === 'variants') {
		if (Adminx.Toast) { Adminx.Toast.show('В этом разделе нет несохранённых полей', 'info'); }
		return;
	  }
	  this.submit(true);
	},

	draftKey: function () {
	  if (!this.form) { return ''; }
	  var actor = this.form.getAttribute('data-actor-id') || '0';
	  var id = parseInt((this.field('id') || {}).value, 10) || 0;
	  var rubric = this.form.getAttribute('data-rubric-id') || '0';
	  if (id > 0) { return 'ave.adminx.document-draft:' + actor + ':document-' + id; }
	  var instanceKey = 'ave.adminx.document-draft-instance:' + actor + ':' + rubric + ':' + window.location.pathname;
	  var instance = '';
	  try {
		instance = window.sessionStorage.getItem(instanceKey) || '';
		if (!instance) {
		  instance = Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
		  window.sessionStorage.setItem(instanceKey, instance);
		}
	  } catch (e) { instance = 'default'; }
	  return 'ave.adminx.document-draft:' + actor + ':new-' + rubric + '-' + instance;
	},

	scheduleLocalDraft: function () {
	  var self = this;
	  window.clearTimeout(this.draftTimer);
	  this.draftTimer = window.setTimeout(function () { self.saveLocalDraft(false); }, 700);
	},

	formValues: function () {
	  if (!this.form) { return {}; }
	  this.syncEditors();
	  this.reindexMediaLists();
	  this.reindexValueLists();
	  var values = {};
	  var excluded = { _csrf: true, id: true, document_version: true, media_draft_token: true };
	  new FormData(this.form).forEach(function (value, name) {
		if (excluded[name] || typeof value !== 'string') { return; }
		if (!Object.prototype.hasOwnProperty.call(values, name)) { values[name] = []; }
		values[name].push(value);
	  });
	  return values;
	},

	saveLocalDraft: function (immediate) {
	  if (!this.form || (!this.dirty && !immediate)) { return; }
	  window.clearTimeout(this.draftTimer);
	  var version = parseInt((this.field('document_version') || {}).value, 10) || 1;
	  var draft = {
		format: 'ave.adminx.document-draft',
		version: 1,
		document_id: parseInt((this.field('id') || {}).value, 10) || 0,
		rubric_id: parseInt(this.form.getAttribute('data-rubric-id'), 10) || 0,
		server_version: version,
		saved_at: Date.now(),
		values: this.formValues()
	  };
	  try {
		window.localStorage.setItem(this.draftKey(), JSON.stringify(draft));
		this.setSaveState('draft');
	  } catch (e) {}
	},

	checkLocalDraft: function () {
	  if (!this.form) { return; }
	  var raw = '';
	  try { raw = window.localStorage.getItem(this.draftKey()) || ''; } catch (e) { return; }
	  if (!raw) { return; }
	  try { this.draftCandidate = JSON.parse(raw); } catch (e) { this.clearLocalDraft(); return; }
	  if (!this.draftCandidate || this.draftCandidate.format !== 'ave.adminx.document-draft' || !this.draftCandidate.values) { this.clearLocalDraft(); return; }
	  if (JSON.stringify(this.draftCandidate.values) === JSON.stringify(this.formValues())) { this.clearLocalDraft(); return; }
	  var root = this.form.querySelector('[data-document-draft-recovery]');
	  if (!root) { return; }
	  var when = new Date(this.draftCandidate.saved_at || Date.now());
	  var text = root.querySelector('[data-document-draft-recovery-text]');
	  var currentVersion = parseInt((this.field('document_version') || {}).value, 10) || 1;
	  var suffix = this.draftCandidate.server_version !== currentVersion ? ' Серверная версия документа уже изменилась.' : '';
	  if (text) { text.textContent = 'Сохранён в этом браузере ' + when.toLocaleString('ru-RU') + '.' + suffix; }
	  root.hidden = false;
	},

	restoreLocalDraft: function () {
	  if (!this.form || !this.draftCandidate) { return; }
	  var values = this.draftCandidate.values || {};
	  var self = this;
	  Object.keys(values).forEach(function (name) {
		var controls = Array.prototype.slice.call(self.form.querySelectorAll('[name="' + self.escapeSelector(name) + '"]'));
		var saved = Array.isArray(values[name]) ? values[name].map(String) : [String(values[name])];
		controls.forEach(function (control, index) {
		  if (control.type === 'checkbox' || control.type === 'radio') { control.checked = saved.indexOf(String(control.value)) !== -1; return; }
		  if (control.tagName === 'SELECT' && control.multiple) {
			Array.prototype.forEach.call(control.options, function (option) { option.selected = saved.indexOf(String(option.value)) !== -1; });
			return;
		  }
		  control.value = saved[Math.min(index, saved.length - 1)] || '';
		  if (control._adminxCodeMirror) { control._adminxCodeMirror.setValue(control.value); }
		  if (control._adminxTiptap) {
		    if (Adminx.RichEditor && Adminx.RichEditor.replaceHtml) { Adminx.RichEditor.replaceHtml(control, control.value); }
		    else { control._adminxTiptap.commands.setContent(control.value || '<p></p>'); }
		  }
		});
	  });
	  this.form.querySelectorAll('[data-document-term-input]').forEach(function (field) {
		var hidden = field.querySelector('[data-document-term-value]');
		field._termItems = self.normalizeTerms(hidden ? hidden.value : '');
		self.renderTermChips(field);
	  });
	  this.form.querySelectorAll('[data-document-media-url]').forEach(function (input) { self.updateMediaPreview(input); });
	  this.applyFieldConditions();
	  this.updateSeo();
	  this.setDirty(true);
	  var root = this.form.querySelector('[data-document-draft-recovery]');
	  if (root) { root.hidden = true; }
	  Adminx.Toast.show('Локальный черновик восстановлен. Проверьте данные и сохраните документ.', 'success');
	},

	discardLocalDraft: function () {
	  this.clearLocalDraft();
	  this.draftCandidate = null;
	  var root = this.form ? this.form.querySelector('[data-document-draft-recovery]') : null;
	  if (root) { root.hidden = true; }
	},

	clearLocalDraft: function () {
	  window.clearTimeout(this.draftTimer);
	  try { window.localStorage.removeItem(this.draftKey()); } catch (e) {}
	},

	escapeSelector: function (value) {
	  if (window.CSS && typeof window.CSS.escape === 'function') { return window.CSS.escape(String(value)); }
	  return String(value).replace(/(["\\])/g, '\\$1');
	},

    promoteCreatedDocument: function (id, stayUrl) {
      id = parseInt(id, 10) || 0;
      if (!id) { return; }
	  var workspace = document.querySelector('[data-document-workspace]');
	  if (workspace && workspace.getAttribute('data-catalog-mode') === '1') {
		window.location.href = stayUrl;
		return;
	  }
      window.history.replaceState({ adminxDocumentEdit: true }, '', stayUrl);
	  document.querySelectorAll('[data-document-remarks], [data-document-aliases], [data-document-revisions], [data-document-snapshot], [data-open-drawer="documentPresetDrawer"]').forEach(function (button) {
		button.disabled = false;
	  });
	  var presetForm = document.querySelector('[data-document-preset-form]');
	  if (presetForm) { presetForm.setAttribute('action', this.base() + '/documents/' + id + '/presets'); }
      document.querySelectorAll('[data-document-id]').forEach(function (element) {
        element.setAttribute('data-document-id', String(id));
      });
      var note = document.querySelector('[data-document-new-note]');
      if (note) { note.remove(); }
      var factId = document.querySelector('[data-document-fact-id]');
      if (factId) { factId.textContent = String(id); }
    },

    refreshSavedHeading: function (state) {
      if (!state) { return; }
      var heading = document.querySelector('[data-document-edit-heading]');
      var meta = document.querySelector('[data-document-edit-meta]');
      if (heading) { heading.textContent = state.title; }
      if (meta) { meta.textContent = state.meta; }
      document.querySelectorAll('[data-document-state-badge]').forEach(function (badge) {
        badge.classList.remove('badge-blue', 'badge-green', 'badge-amber', 'badge-gray');
        if (['blue', 'green', 'amber', 'gray'].indexOf(state.state_badge) !== -1) {
          badge.classList.add('badge-' + state.state_badge);
        }
        badge.textContent = state.state_label;
      });
      document.title = state.title + ' · AVE.cms';
    },

    setSaving: function (saving) {
      if (!this.form) { return; }
      this.submitting = !!saving;
      this.form.setAttribute('aria-busy', this.submitting ? 'true' : 'false');
      this.form.querySelectorAll('[data-document-submit-stay], button[type="submit"]').forEach(function (button) {
        button.disabled = !!saving;
      });
      this.setSaveState(this.submitting ? 'saving' : (this.dirty ? 'dirty' : 'saved'));
    },

    setSaveState: function (state) {
      var root = document.querySelector('[data-document-save-state]');
      if (!root) { return; }
      var states = {
        saved: { text: 'Изменения сохранены', icon: 'ti ti-circle-check' },
        dirty: { text: 'Есть несохранённые изменения', icon: 'ti ti-point-filled' },
		saving: { text: 'Сохраняем...', icon: 'ti ti-loader-2' },
		draft: { text: 'Черновик сохранён в браузере', icon: 'ti ti-device-floppy' }
      };
      var current = states[state] || states.saved;
      root.setAttribute('data-state', state);
      var text = root.querySelector('[data-document-save-state-text]');
      var icon = root.querySelector('[data-document-save-state-icon]');
      if (text) { text.textContent = current.text; }
      if (icon) { icon.className = current.icon; }
    },

    updateBoolean: function (input) {
      if (!input) { return; }
      var label = input.closest('.switch');
      label = label ? label.querySelector('[data-document-boolean-label]') : null;
      if (label) { label.textContent = input.checked ? 'Включено' : 'Выключено'; }
    },

    refreshQuickEditOpener: function (closeAfter) {
      if (window.opener && !window.opener.closed) {
        try {
          window.opener.location.reload();
        } catch (e) {
          window.opener.postMessage({ type: 'adminx:document-saved' }, window.location.origin);
        }
      }
      if (closeAfter) {
        window.close();
      }
    },

    previewPayload: function () {
      if (!this.form) { return; }
      this.syncEditors();
      this.reindexMediaLists();
      this.reindexValueLists();
      var code = document.querySelector('[data-document-payload-code]');
      var summary = document.querySelector('[data-document-payload-summary]');
      var validation = document.querySelector('[data-document-payload-validation]');
      if (code) { code.textContent = 'Формируем данные...'; }
      if (validation) { validation.hidden = true; validation.innerHTML = ''; }
      if (Adminx.Drawer) { Adminx.Drawer.open('documentPayloadDrawer'); }
      var self = this;
      this.ajax(this.base() + '/documents/preview', new FormData(this.form), function (payload) {
        var data = payload.data || {};
        var preview = data.payload || {};
        if (code) { code.textContent = JSON.stringify(preview, null, 2); }
        var fields = Array.isArray(preview.fields) ? preview.fields.length : 0;
        if (summary) { summary.textContent = (preview.mode === 'create' ? 'Создание' : 'Обновление') + ' · полей: ' + fields + ' · без сохранения'; }
        self.renderPayloadValidation(data.validation_errors || {});
      });
    },

    renderPayloadValidation: function (errors) {
      var target = document.querySelector('[data-document-payload-validation]');
      if (!target) { return; }
      var keys = Object.keys(errors || {});
      if (!keys.length) { target.hidden = true; target.innerHTML = ''; return; }
      target.innerHTML = '<div class="alert alert-warning"><i class="ti ti-alert-triangle alert-ic"></i><div><b class="alert-title">Валидация: ' + keys.length + '</b><span>' + keys.map(function (key) { return selfEsc(key + ': ' + errors[key]); }).join('<br>') + '</span></div></div>';
      target.hidden = false;
      function selfEsc(value) {
        var node = document.createElement('span');
        node.textContent = String(value || '');
        return node.innerHTML;
      }
    },

    copyPayload: function () {
      var code = document.querySelector('[data-document-payload-code]');
      var value = code ? code.textContent : '';
      if (!value) { return; }
      var done = function () { Adminx.Toast.show('JSON скопирован', 'success'); };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(value).then(done);
        return;
      }
      var textarea = document.createElement('textarea');
      textarea.value = value;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      textarea.remove();
      done();
    },

    syncEditors: function () {
      if (!this.form) { return; }
      this.form.querySelectorAll('textarea[data-code-editor]').forEach(function (textarea) {
        if (textarea._adminxCodeMirror) { textarea._adminxCodeMirror.save(); }
      });
      this.form.querySelectorAll('textarea[data-rich-editor]').forEach(function (textarea) {
        if (window.Adminx && Adminx.RichEditor && typeof Adminx.RichEditor.syncTextarea === 'function') {
          Adminx.RichEditor.syncTextarea(textarea, textarea._adminxTiptap || null);
          return;
        }
        var root = textarea.closest('.rich-editor');
        if (root && root.classList.contains('rich-editor-source-open') && root._richSourceEditor) {
          root._richSourceEditor.save();
          textarea.value = root._richSourceEditor.getValue();
          return;
        }
        if (textarea._adminxTiptap && textarea.getAttribute('data-rich-editor-dirty') === '1') {
          textarea.value = textarea._adminxTiptap.getHTML();
        }
      });
    },

    remove: function (row) {
      var self = this;
      if (!row) { return; }
      if (Adminx.Confirm) {
        Adminx.Confirm.open({
          kind: 'error',
          title: 'Удалить документ?',
          message: 'Документ будет помечен удалённым и останется доступен для восстановления.',
          confirmLabel: 'Удалить',
          confirmClass: 'btn-danger',
          onConfirm: function () { self.action(row, '/delete'); }
        });
        return;
      }
      if (confirm('Пометить документ удалённым?')) { this.action(row, '/delete'); }
    },

    restore: function (row) {
      if (!row) { return; }
      this.action(row, '/restore');
    },

    copy: function (row) {
      if (!row) { return; }
      var self = this;
      Adminx.Confirm.open({
        kind: 'info',
        title: 'Создать копию документа?',
        message: 'Будет создан новый черновик со всеми значениями полей.',
        confirmLabel: 'Создать копию',
        onConfirm: function () {
          var id = row.getAttribute('data-id');
          var data = new FormData();
          data.append('_csrf', self.csrf());
          self.ajax(self.base() + '/documents/' + encodeURIComponent(id) + '/copy', data, function (payload) {
            Adminx.Toast.show(payload.message || 'Копия создана', 'success');
            window.location.href = payload.redirect || (self.base() + '/documents/' + ((payload.data || {}).id || '') + '/edit');
          });
        }
      });
    },

    purge: function (row) {
      if (!row) { return; }
      var self = this;
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Удалить документ окончательно?',
        message: 'Будут удалены документ, значения полей, ревизии, редиректы, заметки и статистика. Действие необратимо.',
        confirmLabel: 'Удалить окончательно',
        onConfirm: function () { self.action(row, '/purge'); }
      });
    },

    action: function (row, suffix) {
      var id = row.getAttribute('data-id');
      var data = new FormData();
      data.append('_csrf', this.csrf());
      var self = this;
      this.ajax(this.base() + '/documents/' + encodeURIComponent(id) + suffix, data, function (payload) {
        Adminx.Toast.show(payload.message || 'Готово', 'success');
        self.applyFilterUrl(window.location.href, false);
      });
    },

    updateBulk: function () {
      var selected = document.querySelectorAll('[data-document-check]:checked');
      var bar = document.querySelector('[data-documents-bulk]');
      var count = document.querySelector('[data-documents-selected]');
      var all = document.querySelector('[data-documents-check-all]');
      var total = document.querySelectorAll('[data-document-check]').length;
      if (bar) {
        bar.hidden = selected.length === 0;
        bar.classList.toggle('visible', selected.length > 0);
      }
      if (count) { count.textContent = selected.length; }
      if (all) { all.checked = total > 0 && selected.length === total; all.indeterminate = selected.length > 0 && selected.length < total; }
    },

    clearBulk: function () {
      document.querySelectorAll('[data-document-check], [data-documents-check-all]').forEach(function (box) { box.checked = false; box.indeterminate = false; });
      this.updateBulk();
    },

    applyBulk: function () {
      var action = document.querySelector('[data-documents-bulk-action]');
      var ids = Array.prototype.map.call(document.querySelectorAll('[data-document-check]:checked'), function (box) { return box.value; });
      if (!action || !action.value || !ids.length) { Adminx.Toast.show('Выберите документы и действие', 'warning'); return; }
      var self = this;
      var labels = {publish:'опубликовать',unpublish:'снять с публикации',delete:'переместить в корзину',restore:'восстановить',purge:'удалить окончательно'};
      Adminx.Confirm.open({kind:action.value==='purge'?'danger':'warning',title:'Выполнить пакетное действие?',message:'Документов: '+ids.length+'. Действие: '+labels[action.value]+'.',confirmLabel:'Применить',onConfirm:function(){var data=new FormData();data.append('_csrf',self.csrf());data.append('action',action.value);ids.forEach(function(id){data.append('ids[]',id);});self.ajax(self.base()+'/documents/bulk',data,function(payload){var result=payload.data||{};Adminx.Toast.show((payload.message||'Готово')+' · обработано '+(result.done||0),result.errors&&result.errors.length?'warning':'success');self.applyFilterUrl(window.location.href,false);});}});
    },

    rebuildSnapshots: function () {
      var self = this;
      var rubric = document.querySelector('.documents-filter [name="rubric_id"]');
      var rubricId = rubric ? (parseInt(rubric.value, 10) || 0) : 0;
      var scope = rubricId ? ('выбранной рубрики #' + rubricId) : 'всех рабочих документов';
      Adminx.Confirm.open({
        kind: 'warning',
        title: 'Пересобрать JSON-снимки?',
        message: 'Снимки будут последовательно пересобраны для ' + scope + '. Окно нужно оставить открытым до завершения.',
        confirmLabel: 'Пересобрать',
        onConfirm: function () { self.runSnapshotBatch(rubricId, 0, 0, []); }
      });
    },

    runSnapshotBatch: function (rubricId, after, total, failures) {
      var self = this;
      var data = new FormData();
      data.append('_csrf', this.csrf());
      data.append('rubric_id', rubricId || 0);
      data.append('after', after || 0);
      data.append('limit', 50);
      Adminx.Loader.show();
      fetch(this.base() + '/documents/snapshots/rebuild', { method: 'POST', body: data, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) {
          if (!payload.success) { throw payload; }
          var result = payload.data || {};
          total += parseInt(result.done, 10) || 0;
          failures = failures.concat(result.failed || []);
          if (result.has_more) {
            self.runSnapshotBatch(rubricId, parseInt(result.next_after, 10) || after, total, failures);
            return;
          }
          Adminx.Loader.hide();
          Adminx.Toast.show('JSON-снимки пересобраны: ' + total + (failures.length ? ', ошибок: ' + failures.length : ''), failures.length ? 'warning' : 'success');
        })
        .catch(function (payload) {
          Adminx.Loader.hide();
          Adminx.Toast.show((payload && payload.message) || 'Пакетная пересборка остановлена', 'error');
        });
    },

    openSnapshot: function () {
      if (!this.form) { return; }
      var idField = this.field('id');
      var id = idField ? (parseInt(idField.value, 10) || 0) : 0;
      if (!id) { return; }
      if (Adminx.Drawer) { Adminx.Drawer.open('documentSnapshotDrawer'); }
      this.loadSnapshot(id);
    },

    loadSnapshot: function (id) {
      var self = this;
      var target = document.querySelector('[data-document-snapshot-status]');
      if (target) { target.innerHTML = '<div><dt>Состояние</dt><dd>Загрузка...</dd></div>'; }
      fetch(this.base() + '/documents/' + id + '/snapshot', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) { self.renderSnapshot(payload.data || {}); })
        .catch(function (payload) { Adminx.Toast.show((payload && payload.message) || 'Не удалось получить статус снимка', 'error'); });
    },

    renderSnapshot: function (status) {
      var target = document.querySelector('[data-document-snapshot-status]');
      if (!target) { return; }
      var state = status.current ? '<span class="badge badge-green">актуален</span>' : (status.exists ? '<span class="badge badge-amber">нужна пересборка</span>' : '<span class="badge badge-gray">не создан</span>');
      target.innerHTML = '<div><dt>Состояние</dt><dd>' + state + '</dd></div>'
        + '<div><dt>Сформирован</dt><dd>' + this.esc(status.generated_label || 'ещё не создан') + '</dd></div>'
        + '<div><dt>Поля</dt><dd class="mono">' + this.esc(status.fields_count || 0) + '</dd></div>'
        + '<div><dt>Размер</dt><dd>' + this.esc(status.size_label || '0 Б') + '</dd></div>'
        + '<div><dt>Файл</dt><dd class="mono">' + this.esc(status.path || '') + '</dd></div>';
    },

    rebuildSnapshot: function () {
      if (!this.form) { return; }
      var idField = this.field('id');
      var id = idField ? (parseInt(idField.value, 10) || 0) : 0;
      if (!id) { return; }
      var data = new FormData();
      data.append('_csrf', this.csrf());
      var self = this;
      this.ajax(this.base() + '/documents/' + id + '/snapshot/rebuild', data, function (payload) {
        self.renderSnapshot(payload.data || {});
        Adminx.Toast.show(payload.message || 'JSON-снимок пересобран', 'success');
      });
    },

    ajax: function (url, data, done, fail) {
      Adminx.Loader.show();
      fetch(url, { method: 'POST', body: data, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) {
          if (!payload.success) { throw payload; }
          if (done) { done(payload); }
        })
        .catch(function (payload) {
		  var handled = fail ? fail(payload || {}) === false : false;
		  if (handled) { return; }
          Adminx.Toast.show((payload && payload.message) || 'Ошибка запроса', 'error');
        })
        .finally(function () { Adminx.Loader.hide(); });
    },

	showEditConflict: function (payload) {
	  var data = payload.data || {};
	  var reload = function () { window.location.href = data.reload_url || window.location.href; };
	  if (window.Adminx && Adminx.Confirm && typeof Adminx.Confirm.open === 'function') {
		Adminx.Confirm.open({
		  kind: 'warning',
		  title: 'Документ изменился',
		  message: 'На сервере уже сохранена более новая версия. Обновите страницу, проверьте изменения и сохраните документ повторно.',
		  confirmLabel: 'Обновить страницу',
		  cancelLabel: 'Остаться',
		  onConfirm: reload
		});
		return;
	  }
	  Adminx.Toast.show(payload.message || 'Документ уже изменён. Обновите страницу.', 'warning');
	},

    json: function (res) {
      return res.text().then(function (raw) {
        var payload = {};
        try {
          payload = raw ? JSON.parse(raw) : {};
        } catch (e) {
          payload = {
            success: false,
            message: res.status >= 500
              ? 'Внутренняя ошибка сервера. Обновите страницу и повторите попытку.'
              : 'Сервер вернул некорректный ответ.'
          };
        }
        if (!res.ok || payload.success === false) { throw payload; }
        return payload;
      });
    },

    csrf: function () {
      var field = this.form ? this.field('_csrf') : document.querySelector('[data-documents-csrf]');
      return field ? field.value : '';
    },

    issueApiToken: function (form) {
      if (!form || form.getAttribute('aria-busy') === 'true') { return; }
      var scopes = form.querySelectorAll('[name="scopes[]"]:checked');
      if (!scopes.length) {
        Adminx.Toast.show('Выберите хотя бы одно разрешение', 'warning');
        return;
      }
      var self = this;
      var submit = form.querySelector('[data-api-token-submit]');
      form.setAttribute('aria-busy', 'true');
      if (submit) { submit.disabled = true; }
      var data = new FormData(form);
      data.append('_csrf', this.csrf());
      this.ajax((form.getAttribute('data-base') || this.base()) + '/documents/api/tokens', data, function (payload) {
        var secret = form.querySelector('[data-api-token-secret]');
        var fields = form.querySelector('[data-api-token-fields]');
        var value = form.querySelector('[data-api-token-value]');
        var subtitle = document.querySelector('[data-api-token-subtitle]');
        if (value) { value.value = payload.data && payload.data.token ? payload.data.token : ''; }
        if (fields) { fields.hidden = true; }
        if (secret) { secret.hidden = false; }
        if (submit) { submit.hidden = true; }
        if (subtitle) { subtitle.textContent = 'Секрет готов к копированию.'; }
        form.removeAttribute('aria-busy');
        form.closest('.drawer').setAttribute('data-token-created', '1');
        Adminx.Toast.show(payload.message || 'API-токен создан', 'success');
        window.setTimeout(function () { if (value) { value.focus(); value.select(); } }, 30);
      }, function () {
        form.removeAttribute('aria-busy');
        if (submit) { submit.disabled = false; }
      });
    },

    copyApiToken: function () {
      var value = document.querySelector('[data-api-token-value]');
      var token = value ? value.value : '';
      if (!token) { return; }
      var label = document.querySelector('[data-api-token-copy-label]');
      var done = function () {
        if (label) { label.textContent = 'Скопировано'; }
        Adminx.Toast.show('Токен скопирован', 'success');
        window.setTimeout(function () { if (label) { label.textContent = 'Копировать'; } }, 1600);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(token).then(done).catch(function () {
          value.focus(); value.select(); document.execCommand('copy'); done();
        });
        return;
      }
      value.focus(); value.select(); document.execCommand('copy'); done();
    },

    revokeApiToken: function (row) {
      if (!row) { return; }
      var self = this;
      var id = parseInt(row.getAttribute('data-id'), 10) || 0;
      if (!id) { return; }
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Отозвать API-токен?',
        message: 'Интеграция сразу потеряет доступ. Вернуть этот токен будет невозможно.',
        confirmLabel: 'Отозвать',
        onConfirm: function () {
          var data = new FormData();
          data.append('_csrf', self.csrf());
          self.ajax(self.base() + '/documents/api/tokens/' + id + '/revoke', data, function (payload) {
            var state = row.querySelector('[data-api-token-state]');
            var button = row.querySelector('[data-api-token-revoke]');
            if (state) { state.className = 'badge badge-gray'; state.textContent = 'Отозван'; }
            if (button) { button.remove(); }
            row.classList.add('is-revoked');
            Adminx.Toast.show(payload.message || 'API-токен отозван', 'success');
          });
        }
      });
    },

    resetApiTokenForm: function () {
      var form = document.querySelector('[data-api-token-form]');
      if (!form) { return; }
      form.reset();
      form.removeAttribute('aria-busy');
      var fields = form.querySelector('[data-api-token-fields]');
      var secret = form.querySelector('[data-api-token-secret]');
      var value = form.querySelector('[data-api-token-value]');
      var submit = form.querySelector('[data-api-token-submit]');
      var subtitle = document.querySelector('[data-api-token-subtitle]');
      if (fields) { fields.hidden = false; }
      if (secret) { secret.hidden = true; }
      if (value) { value.value = ''; }
      if (submit) { submit.hidden = false; submit.disabled = false; }
      if (subtitle) { subtitle.textContent = 'Права доступа для одной внешней интеграции.'; }
    },

    clearErrors: function () {
      if (!this.form) { return; }
      this.form.querySelectorAll('.field-error').forEach(function (el) { el.textContent = ''; });
      this.form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
	  this.errorTargets = [];
	  var summary = this.form.querySelector('[data-document-error-summary]');
	  var list = summary ? summary.querySelector('[data-document-error-list]') : null;
	  if (list) { list.innerHTML = ''; }
	  if (summary) { summary.hidden = true; }
    },

    showErrors: function (errors) {
      var self = this;
      var jumpPanel = null;
	  var summaryItems = [];
      Object.keys(errors || {}).forEach(function (key) {
		var fieldMatch = key.match(/^fields(?:\[(\d+)\]|\.(\d+))$/);
		var target = null;
		var label = key;
        if (fieldMatch) {
		  var fieldId = fieldMatch[1] || fieldMatch[2];
		  var container = self.form.querySelector('.ax-document-field[data-rubric-field-id="' + fieldId + '"]');
          if (container) {
            container.classList.add('is-invalid');
			target = container;
			var fieldLabel = container.querySelector('.ax-label');
			if (fieldLabel) { label = fieldLabel.textContent.replace('*', '').trim(); }
            var err = container.querySelector('[data-field-value-error]');
            if (!err) {
              err = document.createElement('div');
              err.className = 'field-error';
              err.setAttribute('data-field-value-error', '');
              (container.querySelector('.ax-document-field-body') || container).appendChild(err);
            }
            err.textContent = errors[key];
            var panel = container.closest('[data-document-field-panel]');
            if (panel && jumpPanel === null) { jumpPanel = panel.getAttribute('data-document-field-panel'); }
          }
		  if (target) { summaryItems.push({ key: key, label: label, message: String(errors[key]), target: target }); }
		  return;
        }
        var error = self.form.querySelector('[data-error="' + key + '"]');
        var field = self.field(key);
        if (error) { error.textContent = errors[key]; }
        if (field) { field.classList.add('is-invalid'); }
		target = field || error;
		if (field) {
		  var fieldRoot = field.closest('.field');
		  var directLabel = fieldRoot ? fieldRoot.querySelector('.field-label') : null;
		  if (directLabel) { label = directLabel.textContent.replace(/\d+\s*\/\s*\d+$/, '').trim(); }
		}
		if (target) { summaryItems.push({ key: key, label: label, message: String(errors[key]), target: target }); }
      });
	  this.renderErrorSummary(summaryItems);
      // если ошибка в поле скрытой группы — переключаемся на её вкладку
      if (jumpPanel !== null) { self.activateFieldGroup(jumpPanel); }
      var firstInvalid = self.form.querySelector('.is-invalid');
      if (firstInvalid) {
		self.setWorkspaceForTarget(firstInvalid);
        window.setTimeout(function () {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          var input = firstInvalid.matches('input,select,textarea') ? firstInvalid : firstInvalid.querySelector('input,select,textarea,[contenteditable="true"]');
          if (input && typeof input.focus === 'function') { input.focus({ preventScroll: true }); }
        }, 80);
      }
    },

	renderErrorSummary: function (items) {
	  var summary = this.form ? this.form.querySelector('[data-document-error-summary]') : null;
	  var list = summary ? summary.querySelector('[data-document-error-list]') : null;
	  var title = summary ? summary.querySelector('[data-document-error-title]') : null;
	  if (!summary || !list || !items.length) { return; }
	  this.errorTargets = items.map(function (item) { return item.target; });
	  list.innerHTML = '';
	  items.forEach(function (item, index) {
		var button = document.createElement('button');
		button.type = 'button';
		button.className = 'documents-error-jump';
		button.setAttribute('data-document-error-jump', String(index));
		var name = document.createElement('b');
		name.textContent = item.label;
		var message = document.createElement('span');
		message.textContent = item.message;
		var icon = document.createElement('i');
		icon.className = 'ti ti-arrow-up-right';
		button.appendChild(name);
		button.appendChild(message);
		button.appendChild(icon);
		list.appendChild(button);
	  });
	  if (title) { title.textContent = 'Найдено ошибок: ' + items.length; }
	  summary.hidden = false;
	  summary.scrollIntoView({ behavior: 'smooth', block: 'start' });
	},

	jumpToError: function (index) {
	  var target = this.errorTargets[parseInt(index, 10) || 0];
	  if (!target) { return; }
	  this.setWorkspaceForTarget(target);
	  var panel = target.closest('[data-document-field-panel]');
	  if (panel) { this.activateFieldGroup(panel.getAttribute('data-document-field-panel')); }
	  window.setTimeout(function () {
		target.scrollIntoView({ behavior: 'smooth', block: 'center' });
		var input = target.matches('input,select,textarea') ? target : target.querySelector('input,select,textarea,[contenteditable="true"]');
		if (input && typeof input.focus === 'function') { input.focus({ preventScroll: true }); }
	  }, 90);
	},

    scheduleAliasCheck: function () {
      var self = this;
      clearTimeout(this.aliasTimer);
      this.setAliasState('', 'Проверка...');
      this.aliasTimer = setTimeout(function () { self.checkAlias(false); }, 350);
    },

    checkAlias: function (required, done) {
      if (!this.form) { if (done) { done(true); } return; }
      var alias = this.field('document_alias').value.trim();
      var id = this.field('id').value || 0;
      var self = this;
      if (!alias) {
        this.setAliasState('empty', 'Alias можно оставить пустым');
        if (done) { done(true); }
        return;
      }
      fetch(this.base() + '/documents/alias-check?alias=' + encodeURIComponent(alias) + '&id=' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) {
          var data = payload.data || {};
          self.setAliasState(data.state || (data.ok ? 'ok' : 'error'), data.message || payload.message || '');
          if (done) { done(data.ok !== false); }
        })
        .catch(function () {
          self.setAliasState('error', 'Не удалось проверить alias');
          if (done) { done(!required); }
        });
    },

    setAliasState: function (state, message) {
      var el = document.querySelector('[data-document-alias-state]');
      this.aliasState = state || '';
      if (!el) { return; }
      el.textContent = message || '';
      el.classList.toggle('is-ok', state === 'ok');
      el.classList.toggle('is-error', state === 'error');
    },

	scheduleShortAliasCheck: function () {
		var self = this;
		clearTimeout(this.shortAliasTimer);
		this.setShortAliasState('', 'Проверка...');
		this.shortAliasTimer = setTimeout(function () { self.checkShortAlias(false); }, 350);
	},

	checkShortAlias: function (required, done) {
		if (!this.form) { if (done) { done(true); } return; }
		var input = this.field('document_short_alias');
		var alias = input ? input.value.trim() : '';
		var id = this.field('id').value || 0;
		var self = this;
		if (!alias) {
			this.setShortAliasState('empty', 'До 10 символов, необязательно.');
			if (done) { done(true); }
			return;
		}

		fetch(this.base() + '/documents/alias-check?kind=short&alias=' + encodeURIComponent(alias) + '&id=' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
			.then(this.json)
			.then(function (payload) {
				var data = payload.data || {};
				self.setShortAliasState(data.state || (data.ok ? 'ok' : 'error'), data.message || payload.message || '');
				if (done) { done(data.ok !== false); }
			})
			.catch(function () {
				self.setShortAliasState('error', 'Не удалось проверить короткий alias');
				if (done) { done(!required); }
			});
	},

	setShortAliasState: function (state, message) {
		var el = document.querySelector('[data-document-short-alias-state]');
		if (!el) { return; }
		el.textContent = message || '';
		el.classList.toggle('is-ok', state === 'ok');
		el.classList.toggle('is-error', state === 'error');
	},

    slugify: function (value) {
      var map = {
        'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'e','ж':'zh','з':'z','и':'i',
        'й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t',
        'у':'u','ф':'f','х':'h','ц':'c','ч':'ch','ш':'sh','щ':'sch','ъ':'','ы':'y','ь':'',
        'э':'e','ю':'yu','я':'ya'
      };
      return String(value || '').toLowerCase().replace(/[а-яё]/g, function (ch) { return map[ch] !== undefined ? map[ch] : ch; })
        .replace(/[^a-z0-9]+/g, '-').replace(/-{2,}/g, '-').replace(/^-+|-+$/g, '');
    },

    rubricAliasTemplate: function () {
      var select = this.field('rubric_id');
      if (!select || !select.options || select.selectedIndex < 0) { return ''; }
      return String(select.options[select.selectedIndex].getAttribute('data-alias-template') || '').replace(/^\/+|\/+$/g, '');
    },

    expandAliasTemplate: function (pattern) {
      var published = this.field('document_published');
      var value = published && published.value ? new Date(published.value) : new Date();
      if (isNaN(value.getTime())) { value = new Date(); }
      var pad = function (part) { return String(part).padStart(2, '0'); };
      var replacements = {
        '%d': pad(value.getDate()), '%m': pad(value.getMonth() + 1), '%Y': String(value.getFullYear()),
        '%y': String(value.getFullYear()).slice(-2), '%H': pad(value.getHours()), '%M': pad(value.getMinutes()), '%S': pad(value.getSeconds())
      };
      pattern = String(pattern || '');
      Object.keys(replacements).forEach(function (token) { pattern = pattern.split(token).join(replacements[token]); });
      return pattern.replace(/^\/+|\/+$/g, '');
    },

    composeRubricAlias: function (leaf) {
      leaf = String(leaf || '').replace(/^\/+|\/+$/g, '');
      var prefix = this.expandAliasTemplate(this.rubricAliasTemplate());
      return prefix ? prefix + '/' + leaf : leaf;
    },

    updateAliasTemplateHint: function () {
      var hint = document.querySelector('[data-document-alias-template-hint]');
      if (!hint) { return; }
      var pattern = this.rubricAliasTemplate();
      hint.textContent = pattern
        ? 'Шаблон рубрики: /' + pattern + '/ + alias документа. Дата берётся из публикации.'
        : 'У рубрики нет шаблона пути: alias документа используется от корня сайта.';
    },

    syncSlugFromTitle: function () {
      if (this.aliasTouched) { return; }
      var title = this.field('document_title');
      var alias = this.field('document_alias');
      if (!title || !alias) { return; }
      var leaf = this.slugify(title.value);
      alias.value = this.composeRubricAlias(leaf);
      this.updateAliasTemplateHint();
      this.scheduleAliasCheck();
    },

    generateSlug: function (button) {
      var self = this;
      var title = this.field('document_title');
      var alias = this.field('document_alias');
      if (!title || !alias) { return; }
      var parent = this.field('document_parent');
      var body = new FormData();
      body.append('title', title.value || '');
      body.append('id', button.getAttribute('data-document-id') || '0');
      body.append('parent', parent ? (parent.value || '0') : '0');
      var rubric = this.field('rubric_id');
      var published = this.field('document_published');
      body.append('rubric_id', rubric ? (rubric.value || '0') : '0');
      body.append('published', published ? (published.value || '') : '');
      body.append('_csrf', this.csrf());
      fetch(button.getAttribute('data-slug-generate'), {
        method: 'POST', body: body,
        headers: { 'Accept': 'application/json', 'X-CSRF-Token': this.csrf() },
        credentials: 'same-origin'
      })
        .then(this.json)
        .then(function (payload) {
          var data = payload.data || {};
          if (data.alias) {
            alias.value = data.alias;
            self.aliasTouched = true;
            self.checkAlias(false);
            self.updateSeo();
          } else if (payload.message && window.Adminx && Adminx.toast) {
            Adminx.toast(payload.message, 'error');
          }
        })
        .catch(function () {
          if (window.Adminx && Adminx.toast) { Adminx.toast('Не удалось сгенерировать alias', 'error'); }
        });
    },

    generateShortAlias: function (button) {
      var self = this;
      var input = this.field('document_short_alias');
      if (!input) { return; }
      var body = new FormData();
      body.append('id', button.getAttribute('data-document-id') || '0');
      body.append('_csrf', this.csrf());
      fetch(button.getAttribute('data-short-generate'), {
        method: 'POST', body: body,
        headers: { 'Accept': 'application/json', 'X-CSRF-Token': this.csrf() },
        credentials: 'same-origin'
      })
        .then(this.json)
        .then(function (payload) {
          var data = payload.data || {};
          if (data.alias) {
            input.value = data.alias;
            input.dispatchEvent(new Event('input', { bubbles: true }));
          } else if (payload.message && window.Adminx && Adminx.toast) {
            Adminx.toast(payload.message, 'error');
          }
        })
        .catch(function () {
          if (window.Adminx && Adminx.toast) { Adminx.toast('Не удалось сгенерировать короткий алиас', 'error'); }
        });
    },

    updateSeo: function () {
      if (!this.form) { return; }
      var title = (this.field('document_title') || {}).value || '';
      var desc = (this.field('document_meta_description') || {}).value || '';
      var alias = (this.field('document_alias') || {}).value || '';
      this.setSeoCount('title', title.length);
      this.setSeoCount('description', desc.length);
      var u = document.querySelector('[data-seo-snippet-url]'); if (u) { u.textContent = '/' + alias; }
      var t = document.querySelector('[data-seo-snippet-title]'); if (t) { t.textContent = title || 'Заголовок документа'; }
      var d = document.querySelector('[data-seo-snippet-desc]'); if (d) { d.textContent = desc || 'Описание появится после заполнения meta description.'; }
    },

    setSeoCount: function (key, len) {
      var el = document.querySelector('[data-seo-count="' + key + '"]');
      if (!el) { return; }
      var max = parseInt(el.getAttribute('data-seo-max'), 10) || 0;
      el.textContent = len + '/' + max;
      el.classList.toggle('is-over', max > 0 && len > max);
    },

    openMediaPicker: function (target) {
      var self = this;
      var input = target ? target.querySelector('[data-document-media-url]') : null;
      var list = target ? target.closest('[data-document-media-list], [data-document-media-single]') : null;
      var fallbackDir = list ? (list.getAttribute('data-picker-dir') || '/uploads') : '/uploads';
      Adminx.MediaPicker.open({
        type: this.mediaPickerType(target),
        dir: this.mediaDirectory(input ? input.value : '', fallbackDir),
        title: 'Выбрать файл',
        description: 'Путь будет записан в поле документа.',
        onPick: function (file) { self.applyPickedMedia(target, file); }
      });
    },

    mediaDirectory: function (value, fallback) {
      var path = String(value || '').trim();
      var safeFallback = String(fallback || '/uploads').trim() || '/uploads';
      if (!path) { return safeFallback; }
      try {
        path = new URL(path, window.location.origin).pathname;
        path = decodeURIComponent(path);
      } catch (e) {
        path = path.split(/[?#]/, 1)[0];
      }
      path = path.replace(/\\/g, '/').replace(/\/+/g, '/');
      if (path !== '/uploads' && path.indexOf('/uploads/') !== 0) { return safeFallback; }
      var slash = path.lastIndexOf('/');
      return slash > 0 ? (path.slice(0, slash) || '/uploads') : safeFallback;
    },

    applyPickedMedia: function (target, file) {
      var input = target ? target.querySelector('[data-document-media-url]') : null;
      if (input && file && file.url) {
        input.value = file.url;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        this.sortMediaRowsByName(target.closest('[data-document-media-list]'));
      }
    },

    mediaPickerType: function (target) {
      var input = target ? target.querySelector('[data-document-picker-type]') : null;
      var type = input ? input.getAttribute('data-document-picker-type') : '';
      if (!type && target && target.closest('[data-media-accept]')) {
        type = target.closest('[data-media-accept]').getAttribute('data-media-accept') || '';
      }
      return type === 'file' || type === 'all' ? type : 'image';
    },

    addMediaRow: function (list) {
      if (!list) { return; }
      var fieldId = list.getAttribute('data-field-id') || '';
      var type = list.getAttribute('data-field-type') || 'image_multi';
      var box = list.querySelector('[data-document-media-items]');
      if (!box) { return; }
      box.insertAdjacentHTML('beforeend', this.mediaRowHtml(fieldId, type, box.querySelectorAll('[data-document-media-row]').length));
      this.updateMediaListEmpty(list);
      this.setDirty(true);
    },

    openMediaUpload: function (list) {
      var input = list ? list.querySelector('[data-document-media-files]') : null;
	  if (input) {
		input.removeAttribute('data-replace-upload');
		input.click();
	  }
    },

	openMediaReplacement: function (container) {
		var input = container ? container.querySelector('[data-document-media-files]') : null;
		if (!input) { return; }
		input.setAttribute('data-replace-upload', '1');
		input.click();
	},

    uploadMediaFiles: function (container, files, replace) {
      if (!container || !files || !files.length) { return; }
      var self = this;
      var buttons = container.querySelectorAll('[data-document-media-upload]');
      buttons.forEach(function (button) { button.disabled = true; });
      Adminx.Loader.show();
      Adminx.Upload.files(
		container.getAttribute('data-upload-url') || (this.base() + '/media/upload'),
		files,
		function (chunk) {
		  var data = new FormData();
		  data.append('_csrf', self.csrf());
		  data.append('dir', container.getAttribute('data-upload-dir') || '/uploads');
		  data.append('field_id', container.getAttribute('data-field-id') || '0');
		  data.append('document_id', (self.field('id') || {}).value || '0');
		  data.append('media_draft_token', (self.field('media_draft_token') || {}).value || '');
		  chunk.forEach(function (file) { data.append('files[]', file); });
		  return data;
		}
      ).then(function (result) {
		var uploaded = result.files || [];
		if (replace && uploaded.length) { self.prepareMediaReplacement(container); }
        if (container.matches('[data-document-media-single]')) {
          self.applyUploadedSingle(container, uploaded[0] || null);
        } else {
          self.appendMediaFiles(container, uploaded);
        }
		Adminx.Toast.show('Загружено файлов: ' + uploaded.length, 'success');
	  }).catch(function (error) {
		var uploaded = error && error.uploaded ? error.uploaded.length : 0;
		var message = (error && error.message) || 'Не удалось загрузить файлы';
		if (uploaded) { message += '. До ошибки загружено: ' + uploaded; }
		Adminx.Toast.show(message, 'error');
	  }).finally(function () {
		Adminx.Loader.hide();
		buttons.forEach(function (button) { button.disabled = false; });
      });
    },

    applyUploadedSingle: function (container, file) {
      var input = container ? container.querySelector('[data-document-media-url]') : null;
      var url = this.mediaFileUrl(file);
      if (!input || !url) { return; }
      input.value = url;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    },

    refreshMediaDraft: function (token, documentId) {
      var tokenInput = this.field('media_draft_token');
      if (tokenInput) { tokenInput.value = token; }
      documentId = parseInt(documentId, 10) || 0;
      document.querySelectorAll('[data-document-media-list], [data-document-media-single]').forEach(function (container) {
        var fieldId = container.getAttribute('data-field-id') || '0';
        container.setAttribute('data-upload-dir', '/uploads/.drafts/' + token + '/' + fieldId);
        var target = container.getAttribute('data-target-dir') || '';
        if (documentId && target.indexOf('{ID}') !== -1) {
          target = target.replace(/\{ID\}/g, String(documentId));
          container.setAttribute('data-target-dir', target);
          container.setAttribute('data-picker-dir', target);
          var targetLabel = container.querySelector('[data-document-media-target]');
          if (targetLabel) { targetLabel.textContent = target; }
        }
      });
    },

    importMediaFolder: function (list) {
      if (!list) { return; }
      var self = this;
      Adminx.MediaPicker.open({
        type: list.getAttribute('data-media-accept') || 'image',
        dir: list.getAttribute('data-picker-dir') || '/uploads',
        title: 'Добавить файлы из папки',
        description: 'Откройте нужную папку и подтвердите выбор. В поле добавятся все подходящие файлы из неё.',
        folderLabel: 'Добавить из этой папки',
        onFolder: function (dir) { self.loadMediaFolder(list, dir); }
      });
    },

    loadMediaFolder: function (list, dir) {
      var self = this;
      var params = new URLSearchParams();
      params.set('dir', dir || list.getAttribute('data-picker-dir') || '/uploads');
      params.set('type', list.getAttribute('data-media-accept') || 'image');
      params.set('limit', 1000);
      fetch(this.base() + '/media/folder-files?' + params.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) {
          var files = ((payload.data || {}).files) || [];
          self.appendMediaFiles(list, files);
          Adminx.Toast.show(files.length ? ('Добавлено из папки: ' + files.length) : 'В папке нет подходящих файлов', files.length ? 'success' : 'info');
        })
        .catch(function (payload) { Adminx.Toast.show((payload && payload.message) || 'Не удалось прочитать папку', 'error'); });
    },

    appendMediaFiles: function (list, files) {
      if (!list || !files || !files.length) { return; }
      var seen = {};
      list.querySelectorAll('[data-document-media-url]').forEach(function (input) {
        var value = String(input.value || '').trim();
        if (value) { seen[value] = true; }
      });
      var added = 0;
      var self = this;
      Array.prototype.forEach.call(files, function (file) {
        var url = self.mediaFileUrl(file);
        if (!url || seen[url]) { return; }
        var row = self.createMediaRow(list);
        var input = row ? row.querySelector('[data-media-key="url"]') : null;
        if (!input) { return; }
        input.value = url;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        var name = row.querySelector('[data-media-key="name"]');
        if (name && !name.value) { name.value = file.name || url.split('/').pop(); }
        seen[url] = true;
        added++;
      });
      if (added) { this.sortMediaRowsByName(list); }
      this.updateMediaListEmpty(list);
      if (!added && files.length) { Adminx.Toast.show('Эти файлы уже есть в поле', 'info'); }
    },

    sortMediaRowsByName: function (list) {
      if (!list || list.getAttribute('data-media-accept') !== 'image') { return; }
      var box = list.querySelector('[data-document-media-items]');
      if (!box) { return; }
      var rows = Array.prototype.slice.call(box.querySelectorAll('[data-document-media-row]'));
      if (rows.length < 2) { return; }
      var self = this;
      var collator = window.Intl && window.Intl.Collator
        ? new window.Intl.Collator(undefined, { numeric: true, sensitivity: 'base' })
        : null;
      rows.sort(function (left, right) {
        var leftName = self.mediaRowFileName(left);
        var rightName = self.mediaRowFileName(right);
        if (!leftName && !rightName) { return 0; }
        if (!leftName) { return 1; }
        if (!rightName) { return -1; }
        return collator ? collator.compare(leftName, rightName) : leftName.localeCompare(rightName);
      });
      rows.forEach(function (row) { box.appendChild(row); });
      this.reindexMediaList(list);
    },

    mediaRowFileName: function (row) {
      var input = row ? row.querySelector('[data-media-key="url"]') : null;
      var value = input ? String(input.value || '').trim() : '';
      if (!value) { return ''; }
      value = value.split(/[?#]/, 1)[0].replace(/\\/g, '/');
      var name = value.slice(value.lastIndexOf('/') + 1);
      try { name = decodeURIComponent(name); } catch (e) {}
      return name;
    },

    createMediaRow: function (list) {
      this.addMediaRow(list);
      var rows = list.querySelectorAll('[data-document-media-row]');
      return rows.length ? rows[rows.length - 1] : null;
    },

    mediaFileUrl: function (file) {
      if (!file) { return ''; }
      return String(file.url || file.path || '').trim();
    },

    removeMediaRow: function (row) {
      if (!row) { return; }
      var list = row.closest('[data-document-media-list]');
      row.remove();
      this.reindexMediaList(list);
      this.updateMediaListEmpty(list);
      this.setDirty(true);
    },

    moveMediaRow: function (row, direction) {
      if (!row) { return; }
      var list = row.closest('[data-document-media-list]');
      var moved = false;
      if (direction < 0 && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
        moved = true;
      }
      if (direction > 0 && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
        moved = true;
      }
      this.reindexMediaList(list);
      if (moved) { this.setDirty(true); }
    },

    clearMediaRows: function (list) {
      if (!list) { return; }
      var self = this;
      var clear = function () {
		self.prepareMediaReplacement(list);
      };
      if (Adminx.Confirm) {
        Adminx.Confirm.open({
          kind: 'danger',
		title: 'Удалить все элементы?',
		message: 'После сохранения элементы исчезнут из документа. Неиспользуемые файлы можно будет восстановить из корзины медиа.',
		confirmLabel: 'Удалить все',
          onConfirm: clear
        });
        return;
      }
      if (confirm('Очистить все элементы поля?')) { clear(); }
    },

	clearSingleMedia: function (container) {
		if (!container) { return; }
		var self = this;
		var clear = function () { self.prepareMediaReplacement(container); };
		if (Adminx.Confirm) {
			Adminx.Confirm.open({
				kind: 'danger',
				title: 'Удалить изображение?',
				message: 'После сохранения изображение исчезнет из документа. Неиспользуемый файл можно будет восстановить из корзины медиа.',
				confirmLabel: 'Удалить',
				onConfirm: clear
			});
			return;
		}
		if (confirm('Удалить изображение из документа?')) { clear(); }
	},

	prepareMediaReplacement: function (container) {
		if (!container) { return; }
		if (container.matches('[data-document-media-list]')) {
			var box = container.querySelector('[data-document-media-items]');
			if (box) { box.innerHTML = ''; }
			this.updateMediaListEmpty(container);
		} else {
			var input = container.querySelector('[data-document-media-url]');
			if (input) {
				input.value = '';
				input.dispatchEvent(new Event('input', { bubbles: true }));
			}
		}

		this.markMediaReplacement(container);
		this.setDirty(true);
	},

	markMediaReplacement: function (container) {
		if (!this.form || !container) { return; }
		var fieldId = parseInt(container.getAttribute('data-field-id'), 10) || 0;
		if (!fieldId) { return; }
		var existing = this.form.querySelector('input[name="media_replace_fields[]"][value="' + fieldId + '"]');
		if (!existing) {
			existing = document.createElement('input');
			existing.type = 'hidden';
			existing.name = 'media_replace_fields[]';
			existing.value = String(fieldId);
			this.form.appendChild(existing);
		}
		container.classList.add('is-replacing');
		var note = container.querySelector('[data-document-media-replacement-note]');
		if (note) { note.hidden = false; }
	},

	resetMediaReplacements: function () {
		if (!this.form) { return; }
		this.form.querySelectorAll('input[name="media_replace_fields[]"]').forEach(function (input) { input.remove(); });
		this.form.querySelectorAll('.is-replacing').forEach(function (container) {
			container.classList.remove('is-replacing');
			var note = container.querySelector('[data-document-media-replacement-note]');
			if (note) { note.hidden = true; }
		});
	},

    updateMediaPreview: function (input) {
      var key = input.getAttribute('data-media-key');
      if (key && key !== 'url') { return; }
      var scope = input.closest('[data-document-media-row]') || input.closest('.documents-single-media-field');
      if (!scope) { return; }
      var url = String(input.value || '').trim();
      var isDoc = scope.classList.contains('documents-media-row-doc_files') || (scope.getAttribute('data-media-accept') === 'all');

      var preview = scope.querySelector('.documents-media-thumb, .documents-media-preview');
      if (preview) {
        if (url && /\.(jpe?g|png|gif|webp|bmp|svg)(\?.*)?$/i.test(url)) {
          preview.innerHTML = '<img src="' + this.esc(url) + '" alt="">';
        } else {
          preview.innerHTML = '<span><i class="ti ti-' + (isDoc ? 'file' : 'photo') + '"></i></span>';
        }
      }

      var path = scope.querySelector('[data-media-path]');
      if (path) {
        path.innerHTML = url
          ? '<code>' + this.esc(url) + '</code>'
          : '<span class="documents-media-path-empty">' + (isDoc ? 'Файл не выбран' : 'Изображение не выбрано') + '</span>';
      }
    },

    updateMediaListEmpty: function (list) {
      if (!list) { return; }
      var empty = list.querySelector('[data-document-media-empty]');
      var count = list.querySelectorAll('[data-document-media-row]').length;
      if (empty) { empty.hidden = count > 0; }
      this.reindexMediaList(list);
    },

    reindexMediaLists: function () {
      var self = this;
      if (!this.form) { return; }
      this.form.querySelectorAll('[data-document-media-list]').forEach(function (list) { self.reindexMediaList(list); });
    },

    reindexMediaList: function (list) {
      if (!list) { return; }
      var fieldId = list.getAttribute('data-field-id') || '';
      list.querySelectorAll('[data-document-media-row]').forEach(function (row, index) {
        row.querySelectorAll('[data-media-key]').forEach(function (input) {
          input.name = 'fields[' + fieldId + '][items][' + index + '][' + input.getAttribute('data-media-key') + ']';
        });
      });
    },

    // ---- drag-and-drop сортировка строк медиа/значений ----
    rowDragStart: function (e) {
      var handle = e.target.closest('[data-doc-drag]');
      if (!handle) { return; }
      var row = handle.closest('[data-document-media-row], [data-document-value-row]');
      if (!row) { return; }
      this.dragRow = row;
      this.dragContainer = row.parentNode;
      e.dataTransfer.effectAllowed = 'move';
      try { e.dataTransfer.setData('text/plain', ''); } catch (err) {}
      setTimeout(function () { row.classList.add('documents-row-dragging'); }, 0);
    },

    rowDragOver: function (e) {
      if (!this.dragRow) { return; }
      var over = e.target.closest('[data-document-media-row], [data-document-value-row]');
      if (!over || over === this.dragRow || over.parentNode !== this.dragContainer) { return; }
      e.preventDefault();
      var rect = over.getBoundingClientRect();
      var after;
      if (Math.abs(e.clientY - (rect.top + rect.height / 2)) > rect.height / 2) {
        after = e.clientY > rect.top + rect.height / 2;
      } else {
        after = e.clientX > rect.left + rect.width / 2;
      }
      this.dragContainer.insertBefore(this.dragRow, after ? over.nextSibling : over);
    },

    rowDragEnd: function () {
      if (!this.dragRow) { return; }
      this.dragRow.classList.remove('documents-row-dragging');
      var mediaList = this.dragRow.closest('[data-document-media-list]');
      var valueList = this.dragRow.closest('[data-document-value-list]');
      this.dragRow = null;
      this.dragContainer = null;
      if (mediaList) { this.reindexMediaList(mediaList); }
      if (valueList) { this.reindexValueList(valueList); }
      this.setDirty(true);
    },

    mediaRowHtml: function (fieldId, type, index) {
      var prefix = 'fields[' + this.esc(fieldId) + '][items][' + this.esc(index) + ']';
      var isImage = type !== 'doc_files';
      var thumb = '<button class="documents-media-thumb" type="button" data-document-media-pick aria-label="Выбрать файл"><span><i class="ti ti-' + (isImage ? 'photo' : 'file') + '"></i></span></button>';
      var path = '<div class="documents-media-path" data-media-path><span class="documents-media-path-empty">' + (isImage ? 'Изображение не выбрано' : 'Файл не выбран') + '</span></div>';
      var actions = '<div class="documents-media-card-actions">'
        + '<button class="btn btn-ghost btn-icon btn-sm documents-drag-handle" type="button" data-doc-drag draggable="true" data-tooltip="Перетащить" aria-label="Перетащить"><i class="ti ti-grip-vertical"></i></button>'
        + '<button class="btn btn-ghost btn-icon btn-sm" type="button" data-document-media-up data-tooltip="Выше" aria-label="Выше"><i class="ti ti-arrow-up"></i></button>'
        + '<button class="btn btn-ghost btn-icon btn-sm" type="button" data-document-media-down data-tooltip="Ниже" aria-label="Ниже"><i class="ti ti-arrow-down"></i></button>'
        + '<button class="btn btn-ghost btn-icon btn-sm documents-media-remove" type="button" data-document-media-remove data-tooltip="Удалить" aria-label="Удалить"><i class="ti ti-trash"></i></button>'
        + '</div>';
      var picker = function (name, placeholder, icon) {
        var pickerType = name === 'link' || type === 'doc_files' ? 'all' : 'image';
        return '<div class="documents-picker-input"><input class="input mono" type="text" name="' + prefix + '[' + name + ']" data-media-key="' + name + '" value="" placeholder="' + placeholder + '" data-document-media-url data-document-picker-type="' + pickerType + '"><button class="btn btn-secondary btn-icon btn-sm" type="button" data-document-media-pick data-tooltip="Выбрать файл" aria-label="Выбрать файл"><i class="ti ti-' + icon + '"></i></button></div>';
      };
      var linkField = function () {
        return '<div class="documents-picker-input documents-picker-input-link"><input class="input mono" type="text" name="' + prefix + '[link]" data-media-key="link" value="" placeholder="Ссылка или документ" data-document-media-url data-document-picker-type="all">'
          + '<button class="btn btn-secondary btn-icon btn-sm" type="button" data-document-media-pick data-tooltip="Выбрать файл" aria-label="Выбрать файл"><i class="ti ti-paperclip"></i></button>'
          + '<button class="btn btn-secondary btn-icon btn-sm" type="button" data-document-media-doc-pick data-tooltip="Выбрать документ" aria-label="Выбрать документ"><i class="ti ti-file-search"></i></button></div>';
      };
      var fields;
      if (type === 'doc_files') {
        fields = '<input class="input" type="text" name="' + prefix + '[name]" data-media-key="name" value="" placeholder="Название файла">'
          + '<textarea class="textarea" name="' + prefix + '[description]" data-media-key="description" rows="2" placeholder="Описание"></textarea>'
          + picker('url', '/uploads/file.pdf', 'paperclip');
      } else if (type === 'image_mega') {
        fields = picker('url', '/uploads/image.jpg', 'photo-plus')
          + '<input class="input" type="text" name="' + prefix + '[title]" data-media-key="title" value="" placeholder="Заголовок изображения">'
          + '<textarea class="textarea" name="' + prefix + '[description]" data-media-key="description" rows="2" placeholder="Описание"></textarea>'
          + linkField();
      } else {
        fields = picker('url', '/uploads/image.jpg', 'photo-plus')
          + '<textarea class="textarea" name="' + prefix + '[description]" data-media-key="description" rows="2" placeholder="Описание"></textarea>';
      }
      return '<div class="documents-media-card documents-media-row documents-media-row-' + this.esc(type) + '" data-document-media-row>'
        + thumb
        + '<div class="documents-media-card-body">'
        + path
        + '<div class="documents-media-row-fields">' + fields + '</div>'
        + actions
        + '</div></div>';
    },

    addValueRow: function (list) {
      if (!list) { return; }
      var fieldId = list.getAttribute('data-field-id') || '';
      var kind = list.getAttribute('data-list-kind') || 'list_pair';
      var type = list.getAttribute('data-field-type') || '';
      var dimensionUnit = list.getAttribute('data-dimension-unit') || 'см';
      var weightUnit = list.getAttribute('data-weight-unit') || 'кг';
      var box = list.querySelector('[data-document-value-items]');
      if (!box) { return; }
      box.insertAdjacentHTML('beforeend', this.valueRowHtml(fieldId, kind, type, box.querySelectorAll('[data-document-value-row]').length, dimensionUnit, weightUnit));
      this.updateValueListEmpty(list);
      this.setDirty(true);
    },

    removeValueRow: function (row) {
      if (!row) { return; }
      var list = row.closest('[data-document-value-list]');
      row.remove();
      this.reindexValueList(list);
      this.updateValueListEmpty(list);
      this.setDirty(true);
    },

    moveValueRow: function (row, direction) {
      if (!row) { return; }
      var list = row.closest('[data-document-value-list]');
      var moved = false;
      if (direction < 0 && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
        moved = true;
      }
      if (direction > 0 && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
        moved = true;
      }
      this.reindexValueList(list);
      if (moved) { this.setDirty(true); }
    },

    clearValueRows: function (list) {
      if (!list) { return; }
      var self = this;
      var clear = function () {
        var box = list.querySelector('[data-document-value-items]');
        if (box) { box.innerHTML = ''; }
        self.updateValueListEmpty(list);
        self.setDirty(true);
      };
      if (Adminx.Confirm) {
        Adminx.Confirm.open({
          kind: 'danger',
          title: 'Очистить поле?',
          message: 'Все строки этого поля будут удалены из документа после сохранения.',
          confirmLabel: 'Очистить',
          onConfirm: clear
        });
        return;
      }
      if (confirm('Очистить все строки поля?')) { clear(); }
    },

    updateValueListEmpty: function (list) {
      if (!list) { return; }
      var empty = list.querySelector('[data-document-value-empty]');
      var count = list.querySelectorAll('[data-document-value-row]').length;
      if (empty) { empty.hidden = count > 0; }
      this.reindexValueList(list);
    },

    reindexValueLists: function () {
      var self = this;
      if (!this.form) { return; }
      this.form.querySelectorAll('[data-document-value-list]').forEach(function (list) { self.reindexValueList(list); });
    },

    reindexValueList: function (list) {
      if (!list) { return; }
      var fieldId = list.getAttribute('data-field-id') || '';
      list.querySelectorAll('[data-document-value-row]').forEach(function (row, index) {
        row.querySelectorAll('[data-value-key]').forEach(function (input) {
          input.name = 'fields[' + fieldId + '][items][' + index + '][' + input.getAttribute('data-value-key') + ']';
        });
      });
    },

    valueRowHtml: function (fieldId, kind, type, index, dimensionUnit, weightUnit) {
      var prefix = 'fields[' + this.esc(fieldId) + '][items][' + this.esc(index) + ']';
      var tools = '<div class="documents-value-row-tools">'
        + '<button class="btn btn-ghost btn-icon btn-sm documents-drag-handle" type="button" data-doc-drag draggable="true" data-tooltip="Перетащить" aria-label="Перетащить"><i class="ti ti-grip-vertical"></i></button>'
        + '<button class="btn btn-ghost btn-icon btn-sm" type="button" data-document-value-up data-tooltip="Выше" aria-label="Выше"><i class="ti ti-arrow-up"></i></button>'
        + '<button class="btn btn-ghost btn-icon btn-sm" type="button" data-document-value-down data-tooltip="Ниже" aria-label="Ниже"><i class="ti ti-arrow-down"></i></button>'
        + '</div>';
      var remove = '<button class="btn btn-ghost btn-icon btn-sm documents-value-remove" type="button" data-document-value-remove data-tooltip="Удалить" aria-label="Удалить"><i class="ti ti-trash"></i></button>';
      var input = function (name, placeholder) {
        return '<input class="input" type="text" name="' + prefix + '[' + name + ']" data-value-key="' + name + '" value="" placeholder="' + placeholder + '">';
      };
      var fields = '';
      if (kind === 'list_single') {
        fields = input('value', 'Значение');
      } else if (kind === 'packages') {
        fields = input('length', 'Длина, ' + (dimensionUnit || 'см'))
          + input('width', 'Ширина, ' + (dimensionUnit || 'см'))
          + input('height', 'Высота, ' + (dimensionUnit || 'см'))
          + input('weight', 'Вес, ' + (weightUnit || 'кг'));
      } else if (kind === 'list_triple') {
        fields = input('param', type === 'multi_list_triple' ? 'Колонка 1' : 'Заголовок')
          + input('value', type === 'multi_list_triple' ? 'Колонка 2' : 'Значение')
          + input('value2', type === 'multi_list_triple' ? 'Колонка 3' : 'Дополнительно');
      } else {
        fields = input('param', type === 'multi_links' ? 'Название' : (type === 'teasers' ? 'Заголовок' : 'Параметр'))
          + input('value', type === 'multi_links' ? 'URL' : (type === 'teasers' ? 'ID тизера / ссылка' : 'Значение'));
      }
      return '<div class="documents-value-row documents-value-row-' + this.esc(kind) + '" data-document-value-row>'
        + tools + '<div class="documents-value-row-fields">' + fields + '</div>' + remove + '</div>';
    },

    openRelationPicker: function (target, opts) {
      var self = this;
      opts = opts || {};
      var subtitle = opts.insert === 'linkAlias'
        ? 'В поле-ссылку будет записан адрес документа.'
        : 'В поле будет записан ID документа.';
      var overlay = document.createElement('div');
      overlay.className = 'overlay documents-picker-overlay';
      overlay.innerHTML = '<div class="modal picker-modal documents-relation-picker" role="dialog" aria-modal="true">'
        + '<div class="modal-header"><span class="dialog-icon info"><i class="ti ti-file-search"></i></span><div style="flex:1"><h3>Выбрать документ</h3><p class="text-secondary" style="margin-top:4px">' + subtitle + '</p></div><button class="modal-close" type="button" data-relation-close aria-label="Закрыть"><i class="ti ti-x"></i></button></div>'
        + '<div class="modal-body"><div class="input-wrap documents-relation-search"><i class="ti ti-search"></i><input class="input" type="search" placeholder="ID, название или alias" data-relation-search></div><div class="documents-picker-status" data-relation-status>Загрузка...</div><div class="documents-relation-list" data-relation-list></div></div>'
        + '<div class="modal-footer"><div class="mf-left documents-picker-count" data-relation-count></div><button class="btn btn-ghost" type="button" data-relation-close>Закрыть</button></div>'
        + '</div>';
      document.body.appendChild(overlay);
      requestAnimationFrame(function () { overlay.classList.add('show'); });
      this.bindRelationPicker(overlay, target, opts);
    },

    bindRelationPicker: function (overlay, target, opts) {
      var self = this;
      opts = opts || {};
      var list = overlay.querySelector('[data-relation-list]');
      var status = overlay.querySelector('[data-relation-status]');
      var search = overlay.querySelector('[data-relation-search]');
      var count = overlay.querySelector('[data-relation-count]');
      var close = function () {
        overlay.classList.remove('show');
        setTimeout(function () { overlay.remove(); document.removeEventListener('keydown', onKey); }, 160);
      };
      var render = function (items) {
        list.innerHTML = '';
        (items || []).forEach(function (item) {
          list.insertAdjacentHTML('beforeend', '<button class="documents-relation-item" type="button" data-relation-id="' + self.esc(item.id) + '" data-relation-alias="' + self.esc(item.alias || '') + '"><span class="documents-relation-id">#' + self.esc(item.id) + '</span><span><b>' + self.esc(item.title || 'Без названия') + '</b><small>' + self.esc(item.alias || '') + '</small></span><em>' + self.esc(item.rubric_title || ('Рубрика #' + item.rubric_id)) + '</em></button>');
        });
        status.hidden = items && items.length > 0;
        status.textContent = search.value.trim() ? 'Ничего не найдено' : 'Начните вводить название или ID';
        count.textContent = items && items.length ? 'Документов: ' + items.length : '';
      };
      var rubricEl = target ? (target.querySelector && target.querySelector('[data-document-picker-rubric]')) : null;
      if (!rubricEl && target && target.matches && target.matches('[data-document-picker-rubric]')) { rubricEl = target; }
      var rubric = rubricEl ? (rubricEl.getAttribute('data-document-picker-rubric') || '') : '';
      var scope = target && target.getAttribute ? (target.getAttribute('data-document-picker-scope') || '') : '';
      var excludeId = target && target.getAttribute ? (target.getAttribute('data-document-picker-exclude') || '') : '';
      var load = function () {
        var params = new URLSearchParams();
        params.set('q', search.value.trim());
        params.set('limit', 20);
        if (rubric) { params.set('rubric_id', rubric); }
        if (scope) { params.set('scope', scope); }
        if (excludeId) { params.set('exclude_id', excludeId); }
        status.textContent = 'Загрузка...';
        status.hidden = false;
        fetch(self.base() + '/documents/picker?' + params.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
          .then(self.json)
          .then(function (payload) { render((payload.data || {}).items || []); })
          .catch(function () { list.innerHTML = ''; status.textContent = 'Не удалось загрузить документы'; status.hidden = false; });
      };
      var timer = null;
      var onKey = function (e) { if (e.key === 'Escape') { close(); } };
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay || e.target.closest('[data-relation-close]')) { close(); return; }
        var item = e.target.closest('[data-relation-id]');
        if (item) {
          if (opts.insert === 'linkAlias' && opts.input) {
            var alias = (item.getAttribute('data-relation-alias') || '').replace(/^\/+/, '');
            opts.input.value = alias ? '/' + alias : '';
            opts.input.dispatchEvent(new Event('input', { bubbles: true }));
          } else {
            var isMultiple = !!(target && target.querySelector('[data-document-relation-list]'));
            self.applyPickedRelation(
              target,
              item.getAttribute('data-relation-id'),
              item.getAttribute('data-relation-alias'),
              item.querySelector('b') ? item.querySelector('b').textContent : ''
            );
            if (isMultiple) { item.classList.add('is-selected'); return; }
          }
          close();
        }
      });
      search.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(load, 220);
      });
      document.addEventListener('keydown', onKey);
      load();
      search.focus();
    },

    applyPickedRelation: function (target, id, alias, title) {
      var multiple = target ? target.querySelector('[data-document-relation-list]') : null;
      if (multiple) {
        this.appendPickedRelation(multiple, id, title);
        return;
      }
      var input = target ? (target.querySelector('[data-document-relation-id]') || target.querySelector('[data-document-parent-id]')) : null;
      if (input) { input.value = id || ''; input.dispatchEvent(new Event('input', { bubbles: true })); }
      // Одиночная связь «Документ из рубрики»: показываем заголовок выбранного документа, а не ID.
      var single = target ? target.querySelector('[data-document-relation-single]') : null;
      if (single) { this.fillRelationSingle(single, id, title); }
      // Выбор родительского документа перестраивает ЧПУ по иерархии: <алиас-родителя>/<leaf>.
      if (input && input.hasAttribute('data-document-parent-id')) {
        this.rebuildAliasFromParent(alias);
      }
    },

    fillRelationSingle: function (single, id, title) {
      if (!single) { return; }
      id = String(id || '').replace(/[^0-9]/g, '');
      var titleEl = single.querySelector('[data-relation-title]');
      var idEl = single.querySelector('[data-relation-idlabel]');
      var clear = single.querySelector('[data-document-relation-clear]');
      if (titleEl) { titleEl.textContent = id ? (title || ('Документ #' + id)) : 'Документ не выбран'; }
      if (idEl) { idEl.textContent = id ? '#' + id : 'нажмите, чтобы выбрать'; }
      single.classList.toggle('is-empty', !id);
      if (clear) { clear.hidden = !id; }
    },

    appendPickedRelation: function (list, id, title) {
      id = String(id || '').replace(/[^0-9]/g, '');
      if (!list || !id || list.querySelector('[data-document-relation-token][data-relation-id="' + id + '"]')) { return; }
      var fieldId = list.getAttribute('data-field-id') || '';
      var tokens = list.querySelector('[data-document-relation-tokens]');
      if (!tokens) { return; }
      tokens.insertAdjacentHTML('beforeend', '<span class="documents-relation-token" data-document-relation-token data-relation-id="' + this.esc(id) + '">'
        + '<span><b>' + this.esc(title || ('Документ #' + id)) + '</b><small>#' + this.esc(id) + '</small></span>'
        + '<input type="hidden" name="fields[' + this.esc(fieldId) + '][document_ids][]" value="' + this.esc(id) + '">'
        + '<button class="btn btn-ghost btn-icon btn-sm" type="button" data-document-relation-remove aria-label="Убрать документ" data-tooltip="Убрать"><i class="ti ti-x"></i></button></span>');
      var empty = list.querySelector('[data-document-relation-empty]');
      if (empty) { empty.hidden = true; }
      this.setDirty(true);
    },

    removePickedRelation: function (token) {
      if (!token) { return; }
      var list = token.closest('[data-document-relation-list]');
      token.remove();
      var empty = list ? list.querySelector('[data-document-relation-empty]') : null;
      if (empty) { empty.hidden = !!list.querySelector('[data-document-relation-token]'); }
      this.setDirty(true);
    },

    rebuildAliasFromParent: function (parentAlias) {
      var aliasInput = this.field('document_alias');
      if (!aliasInput) { return; }
      var current = String(aliasInput.value || '').replace(/^\/+|\/+$/g, '');
      var leaf = current.indexOf('/') >= 0 ? current.split('/').pop() : current;
      if (!leaf) {
        var title = this.field('document_title');
        leaf = title ? this.slugify(title.value) : '';
      }
      var parent = String(parentAlias || '').replace(/^\/+|\/+$/g, '');
      aliasInput.value = parent ? (parent + '/' + leaf) : leaf;
      this.aliasTouched = true;
      this.checkAlias(false);
      this.updateSeo();
    },

    openRemarks: function () {
      if (!this.form) { return; }
      var id = parseInt(this.field('id').value, 10) || 0;
      if (!id) { return; }
      this.currentRemarkDocumentId = id;
      var subtitle = document.querySelector('[data-document-remarks-subtitle]');
      if (subtitle) { subtitle.textContent = '#' + id + ' · ' + (this.field('document_title') ? this.field('document_title').value : 'Документ'); }
      if (Adminx.Drawer) { Adminx.Drawer.open('documentRemarksDrawer'); }
      this.refreshRemarks();
    },

    refreshRemarks: function () {
      if (!this.currentRemarkDocumentId) { return; }
      var self = this;
      var list = document.querySelector('[data-document-remark-list]');
      if (list) { list.innerHTML = '<div class="empty-state">Загрузка...</div>'; }
      fetch(this.base() + '/documents/' + this.currentRemarkDocumentId + '/remarks', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) { self.renderRemarks(((payload.data || {}).remarks) || []); })
        .catch(function () { Adminx.Toast.show('Не удалось загрузить заметки', 'error'); });
    },

    renderRemarks: function (items) {
      var list = document.querySelector('[data-document-remark-list]');
      var self = this;
      if (!list) { return; }
      var badge = document.querySelector('[data-document-remarks-badge]');
      if (badge) { badge.textContent = items.length; }
      if (!items.length) { list.innerHTML = '<div class="empty-state">Заметок пока нет.</div>'; return; }
      list.innerHTML = items.map(function (item) {
        return '<article class="card documents-remark">'
          + '<div class="documents-remark-head"><div><b>' + self.esc(item.title || 'Без заголовка') + '</b><small>' + self.esc(item.published_label || '-') + (item.email ? ' · ' + self.esc(item.email) : '') + '</small></div><button class="btn btn-ghost btn-icon btn-sm documents-action-danger" type="button" data-document-remark-delete="' + self.esc(item.id) + '" data-tooltip="Удалить" aria-label="Удалить"><i class="ti ti-trash"></i></button></div>'
          + '<p>' + self.esc(item.text || '') + '</p></article>';
      }).join('');
    },

    submitRemark: function (form) {
      if (!this.currentRemarkDocumentId || !form) { return; }
      var data = new FormData(form);
      data.append('_csrf', this.csrf());
      var self = this;
      this.ajax(this.base() + '/documents/' + this.currentRemarkDocumentId + '/remarks', data, function (payload) {
        Adminx.Toast.show(payload.message || 'Заметка добавлена', 'success');
        form.reset();
        self.refreshRemarks();
      });
    },

    deleteRemark: function (id) {
      id = parseInt(id, 10) || 0;
      if (!id || !this.currentRemarkDocumentId) { return; }
      var self = this;
      Adminx.Confirm.open({kind: 'danger', title: 'Удалить заметку?', message: 'Заметка будет удалена без восстановления.', confirmLabel: 'Удалить', onConfirm: function () {
        var data = new FormData(); data.append('_csrf', self.csrf());
        self.ajax(self.base() + '/documents/' + self.currentRemarkDocumentId + '/remarks/' + id + '/delete', data, function (payload) { Adminx.Toast.show(payload.message || 'Заметка удалена', 'success'); self.refreshRemarks(); });
      }});
    },

    openAliases: function () {
      if (!this.form) { return; }
      var id = parseInt(this.field('id').value, 10) || 0;
      if (!id) { return; }
      this.currentAliasDocumentId = id;
      this.resetAliasForm();
      var subtitle = document.querySelector('[data-document-aliases-subtitle]');
      if (subtitle) { subtitle.textContent = '#' + id + ' · ' + (this.field('document_title') ? this.field('document_title').value : 'Документ'); }
      if (Adminx.Drawer) { Adminx.Drawer.open('documentAliasesDrawer'); }
      this.refreshAliases();
    },

    refreshAliases: function () {
      if (!this.currentAliasDocumentId) { return; }
      var self = this;
      var list = document.querySelector('[data-document-alias-list]');
      if (list) { list.innerHTML = '<div class="empty-state">Загрузка...</div>'; }
      Adminx.Loader.show();
      fetch(this.base() + '/documents/' + this.currentAliasDocumentId + '/aliases', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) {
          var data = payload.data || {};
          var target = document.querySelector('[data-document-alias-target]');
          var targetAlias = ((data.document || {}).alias) || '';
          if (target) { target.textContent = '/' + targetAlias; target.href = '/' + targetAlias; }
          self.renderAliases(data.aliases || []);
        })
        .catch(function () { Adminx.Toast.show('Не удалось загрузить историю URL', 'error'); })
        .finally(function () { Adminx.Loader.hide(); });
    },

    renderAliases: function (items) {
      var list = document.querySelector('[data-document-alias-list]');
      var self = this;
      if (!list) { return; }
      var count = document.querySelector('[data-document-aliases-count]');
      if (count) { count.textContent = items.length; }
      if (!items.length) {
        list.innerHTML = '<div class="empty-state">Старых URL пока нет.</div>';
        return;
      }
      list.innerHTML = items.map(function (item) {
        return '<article class="list-row documents-alias-row" data-alias="' + self.esc(item.alias) + '" data-header="' + self.esc(item.header) + '">'
          + '<span class="icon-tile documents-alias-icon"><i class="ti ti-route"></i></span>'
          + '<div class="documents-alias-main"><a class="mono" href="/' + self.esc(item.alias) + '" target="_blank" rel="noopener">/' + self.esc(item.alias) + '</a><small>' + self.esc(item.changed_label || '-') + (item.author_name ? ' · ' + self.esc(item.author_name) : '') + '</small></div>'
          + '<span class="badge badge-cyan">' + self.esc(item.header || 301) + '</span>'
          + '<div class="cluster documents-alias-actions">'
          + '<button class="btn btn-ghost btn-icon btn-sm" type="button" data-document-alias-edit="' + self.esc(item.id) + '" data-tooltip="Изменить" aria-label="Изменить"><i class="ti ti-pencil"></i></button>'
          + '<button class="btn btn-ghost btn-icon btn-sm documents-action-danger" type="button" data-document-alias-delete="' + self.esc(item.id) + '" data-tooltip="Удалить" aria-label="Удалить"><i class="ti ti-trash"></i></button>'
          + '</div></article>';
      }).join('');
    },

    editAlias: function (button) {
      var row = button ? button.closest('.documents-alias-row') : null;
      var form = document.querySelector('[data-document-alias-form]');
      if (!row || !form) { return; }
      form.elements.id.value = button.getAttribute('data-document-alias-edit') || '0';
      form.elements.alias.value = row.getAttribute('data-alias') || '';
      form.elements.header.value = row.getAttribute('data-header') || '301';
      var title = form.querySelector('[data-document-alias-form-title]');
      if (title) { title.textContent = 'Редактирование редиректа'; }
      form.elements.alias.focus();
    },

    resetAliasForm: function () {
      var form = document.querySelector('[data-document-alias-form]');
      if (!form) { return; }
      form.reset();
      form.elements.id.value = '0';
      var title = form.querySelector('[data-document-alias-form-title]');
      if (title) { title.textContent = 'Новый редирект'; }
    },

    submitAlias: function (form) {
      if (!this.currentAliasDocumentId || !form) { return; }
      var historyId = parseInt(form.elements.id.value, 10) || 0;
      var data = new FormData(form);
      data.append('_csrf', this.csrf());
      var self = this;
      this.ajax(this.base() + '/documents/' + this.currentAliasDocumentId + '/aliases' + (historyId ? '/' + historyId : ''), data, function (payload) {
        Adminx.Toast.show(payload.message || 'Редирект сохранён', 'success');
        self.resetAliasForm();
        self.refreshAliases();
      });
    },

    deleteAlias: function (id) {
      id = parseInt(id, 10) || 0;
      if (!id || !this.currentAliasDocumentId) { return; }
      var self = this;
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Удалить редирект?',
        message: 'Старый URL перестанет перенаправлять на документ.',
        confirmLabel: 'Удалить',
        onConfirm: function () {
          var data = new FormData();
          data.append('_csrf', self.csrf());
          self.ajax(self.base() + '/documents/' + self.currentAliasDocumentId + '/aliases/' + id + '/delete', data, function (payload) {
            Adminx.Toast.show(payload.message || 'Редирект удалён', 'success');
            self.resetAliasForm();
            self.refreshAliases();
          });
        }
      });
    },

    openRevisions: function () {
      if (!this.form) { return; }
      var id = parseInt(this.field('id').value, 10) || 0;
      if (!id) { return; }
      this.currentRevisionDocumentId = id;
      this.currentRevisionId = 0;
      var title = document.getElementById('documentRevisionsDrawerTitle');
      var subtitle = document.getElementById('documentRevisionsDrawerSubtitle');
      var list = document.querySelector('[data-document-revisions-list]');
      var count = document.querySelector('[data-document-revisions-count]');
      var revisionTitle = document.querySelector('[data-document-revision-title]');
      var revisionMeta = document.querySelector('[data-document-revision-meta]');
      var fields = document.querySelector('[data-document-revision-fields]');
      var restore = document.querySelector('[data-document-revision-restore]');
	  var restoreLabel = document.querySelector('[data-document-revision-restore-label]');
      var remove = document.querySelector('[data-document-revision-delete]');
      var publicPreview = document.querySelector('[data-document-revision-preview]');
      var clear = document.querySelector('[data-document-revisions-clear]');
      if (title) { title.textContent = 'Ревизии документа #' + id; }
      if (subtitle) { subtitle.textContent = this.field('document_title') ? this.field('document_title').value : 'История значений полей'; }
      if (list) { list.innerHTML = '<div class="empty-state">Загрузка...</div>'; }
      if (count) { count.textContent = 'Загрузка...'; }
      if (revisionTitle) { revisionTitle.textContent = 'Выберите ревизию'; }
	  if (revisionMeta) { revisionMeta.textContent = 'Содержимое снимка появится после выбора ревизии.'; }
      if (fields) { fields.innerHTML = ''; }
      if (restore) { restore.disabled = true; }
	  if (restoreLabel) { restoreLabel.textContent = 'Восстановить'; }
      if (remove) { remove.disabled = true; remove.removeAttribute('data-document-revision-delete'); }
      if (publicPreview) { publicPreview.hidden = true; publicPreview.href = '#'; }
      if (clear) { clear.disabled = true; }
      if (Adminx.Drawer) { Adminx.Drawer.open('documentRevisionsDrawer'); }
      this.refreshRevisions();
    },

    refreshRevisions: function () {
      if (!this.currentRevisionDocumentId) { return; }
      var self = this;
      Adminx.Loader.show();
      fetch(this.base() + '/documents/' + this.currentRevisionDocumentId + '/revisions', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) { self.renderRevisions(((payload.data || {}).revisions) || []); })
        .catch(function () { Adminx.Toast.show('Не удалось загрузить ревизии', 'error'); })
        .finally(function () { Adminx.Loader.hide(); });
    },

    renderRevisions: function (items) {
      var list = document.querySelector('[data-document-revisions-list]');
      var count = document.querySelector('[data-document-revisions-count]');
      var clear = document.querySelector('[data-document-revisions-clear]');
      var badge = document.querySelector('[data-document-revisions-badge]');
      var self = this;
      if (count) { count.textContent = items.length ? (items.length + ' снимков') : 'История пока пустая'; }
      if (badge) { badge.textContent = items.length; }
      if (clear) { clear.disabled = !items.length; }
      if (!list) { return; }
      if (!items.length) {
        list.innerHTML = '<div class="empty-state">Ревизий пока нет. Первый снимок появится после сохранения документа.</div>';
        this.showRevisionEmpty();
        return;
      }
      list.innerHTML = items.map(function (item) {
        return '<div class="list-row documents-revision-row" data-document-revision-open="' + self.esc(item.id) + '">'
          + '<span class="icon-tile documents-revision-icon"><i class="ti ti-history"></i></span>'
          + '<div class="documents-revision-row-main">'
		  + '<b>Ревизия #' + self.esc(item.id) + '</b><span class="badge ' + (item.format_version > 1 ? 'badge-green' : 'badge-gray') + '">' + self.esc(item.format_label || 'Только поля') + '</span><span class="badge badge-blue">' + self.esc(item.fields_count || 0) + ' полей</span>'
          + '<div class="text-muted text-xs">' + self.esc(item.created_label || '-') + (item.author_name ? ' · ' + self.esc(item.author_name) : '') + (item.size_label ? ' · ' + self.esc(item.size_label) : '') + '</div>'
          + '</div>'
          + '<button class="btn btn-ghost btn-icon btn-sm documents-revision-delete" type="button" data-document-revision-delete="' + self.esc(item.id) + '" data-tooltip="Удалить ревизию" aria-label="Удалить ревизию"><i class="ti ti-trash"></i></button>'
          + '</div>';
      }).join('');
      this.loadRevision(items[0].id);
    },

    showRevisionEmpty: function () {
      var title = document.querySelector('[data-document-revision-title]');
      var meta = document.querySelector('[data-document-revision-meta]');
      var fields = document.querySelector('[data-document-revision-fields]');
      var restore = document.querySelector('[data-document-revision-restore]');
	  var restoreLabel = document.querySelector('[data-document-revision-restore-label]');
      var remove = document.querySelector('[data-document-revision-delete]');
      var publicPreview = document.querySelector('[data-document-revision-preview]');
      if (title) { title.textContent = 'Выберите ревизию'; }
	  if (meta) { meta.textContent = 'Содержимое снимка появится после выбора ревизии.'; }
      if (fields) { fields.innerHTML = ''; }
      if (restore) { restore.disabled = true; }
	  if (restoreLabel) { restoreLabel.textContent = 'Восстановить'; }
      if (remove) { remove.disabled = true; remove.removeAttribute('data-document-revision-delete'); }
      if (publicPreview) { publicPreview.hidden = true; publicPreview.href = '#'; }
    },

    loadRevision: function (id) {
      id = parseInt(id, 10) || 0;
      if (!id) { return; }
      var self = this;
      Adminx.Loader.show();
      fetch(this.base() + '/documents/revisions/' + id, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(this.json)
        .then(function (payload) { self.showRevision(payload.data || {}); })
        .catch(function () { Adminx.Toast.show('Не удалось загрузить ревизию', 'error'); })
        .finally(function () { Adminx.Loader.hide(); });
    },

    showRevision: function (item) {
      var self = this;
      this.currentRevisionId = parseInt(item.id, 10) || 0;
      document.querySelectorAll('[data-document-revision-open]').forEach(function (row) {
        row.classList.toggle('is-active', row.getAttribute('data-document-revision-open') === String(item.id));
      });
      var title = document.querySelector('[data-document-revision-title]');
      var meta = document.querySelector('[data-document-revision-meta]');
      var fields = document.querySelector('[data-document-revision-fields]');
      var restore = document.querySelector('[data-document-revision-restore]');
      var remove = document.querySelector('[data-document-revision-delete]');
      var publicPreview = document.querySelector('[data-document-revision-preview]');
      if (title) { title.textContent = 'Ревизия #' + (item.id || ''); }
      if (meta) {
		var changedCount = (item.document_preview || []).filter(function (field) { return !!field.changed; }).length
		  + (item.preview || []).filter(function (field) { return !!field.changed; }).length;
		meta.textContent = (item.created_label || '-') + (item.author_name ? ' · ' + item.author_name : '') + (item.size_label ? ' · ' + item.size_label : '') + ' · изменений: ' + changedCount;
	  }
      if (fields) {
        var preview = item.preview || [];
		var documentPreview = item.document_preview || [];
		var systemHtml = documentPreview.length ? '<section class="documents-revision-system"><div class="documents-revision-subhead"><i class="ti ti-settings"></i><b>Основные настройки</b><span>' + self.esc(documentPreview.length) + '</span><label class="documents-revision-group-check"><input type="checkbox" data-document-revision-group="document" checked><span>Все</span></label></div>' + documentPreview.map(function (field) {
		  return '<article class="documents-revision-field documents-revision-system-field ' + (field.changed ? 'is-changed' : 'is-unchanged') + '"><div><label class="documents-revision-check" aria-label="Восстановить ' + self.esc(field.title || field.key) + '"><input type="checkbox" value="' + self.esc(field.key || '') + '" data-document-revision-select="document"' + (field.changed ? ' checked' : '') + '></label><span class="documents-revision-field-name"><b>' + self.esc(field.title || field.key) + '</b><small>' + self.esc(field.key || '') + '</small></span><span class="badge ' + (field.changed ? 'badge-amber' : 'badge-gray') + '">' + (field.changed ? 'изменится' : 'совпадает') + '</span></div><pre>' + self.esc(field.value_preview || '') + '</pre></article>';
		}).join('') + '</section>' : '';
		var fieldsHtml = preview.length ? '<section class="documents-revision-content"><div class="documents-revision-subhead"><i class="ti ti-forms"></i><b>Поля рубрики</b><span>' + self.esc(preview.length) + '</span><label class="documents-revision-group-check"><input type="checkbox" data-document-revision-group="field" checked><span>Все</span></label></div>' + preview.map(function (field) {
          return '<article class="documents-revision-field ' + (field.changed ? 'is-changed' : 'is-unchanged') + '">'
            + '<div><label class="documents-revision-check" aria-label="Восстановить ' + self.esc(field.title || ('Поле #' + field.field_id)) + '"><input type="checkbox" value="' + self.esc(field.field_id) + '" data-document-revision-select="field"' + (field.changed ? ' checked' : '') + '></label><span class="documents-revision-field-name"><b>' + self.esc(field.title || ('Поле #' + field.field_id)) + '</b><small>#' + self.esc(field.field_id) + (field.type ? ' · ' + self.esc(field.type) : '') + '</small></span><span class="badge ' + (field.changed ? 'badge-amber' : 'badge-gray') + '">' + (field.changed ? 'изменится' : 'совпадает') + '</span><span>' + self.esc(field.size_label || '') + '</span></div>'
            + '<pre>' + self.esc(field.value_preview || '') + '</pre>'
            + '</article>';
		}).join('') + '</section>' : '<div class="empty-state">В снимке нет значений полей.</div>';
		fields.innerHTML = systemHtml + fieldsHtml;
      }
      this.updateRevisionSelection();
      if (publicPreview) {
        publicPreview.hidden = !item.preview_url;
        publicPreview.href = item.preview_url || '#';
      }
      if (remove) {
        remove.disabled = !this.currentRevisionId;
        if (this.currentRevisionId) { remove.setAttribute('data-document-revision-delete', String(this.currentRevisionId)); }
      }
    },

	 toggleRevisionGroup: function (group, checked) {
	   document.querySelectorAll('[data-document-revision-select="' + group + '"]').forEach(function (input) {
		 input.checked = !!checked;
	   });
	   this.updateRevisionSelection();
	 },

	 revisionSelection: function () {
	   var all = Array.prototype.slice.call(document.querySelectorAll('[data-document-revision-select]'));
	   var selected = all.filter(function (input) { return input.checked; });
	   return {
		 all: all.length > 0 && selected.length === all.length,
		 count: selected.length,
		 total: all.length,
		 documentKeys: selected.filter(function (input) { return input.getAttribute('data-document-revision-select') === 'document'; }).map(function (input) { return input.value; }),
		 fieldIds: selected.filter(function (input) { return input.getAttribute('data-document-revision-select') === 'field'; }).map(function (input) { return parseInt(input.value, 10) || 0; }).filter(Boolean)
	   };
	 },

	 updateRevisionSelection: function () {
	   var selection = this.revisionSelection();
	   document.querySelectorAll('[data-document-revision-group]').forEach(function (group) {
		 var type = group.getAttribute('data-document-revision-group');
		 var items = Array.prototype.slice.call(document.querySelectorAll('[data-document-revision-select="' + type + '"]'));
		 var checked = items.filter(function (input) { return input.checked; }).length;
		 group.checked = items.length > 0 && checked === items.length;
		 group.indeterminate = checked > 0 && checked < items.length;
	   });
	   var restore = document.querySelector('[data-document-revision-restore]');
	   var label = document.querySelector('[data-document-revision-restore-label]');
	   if (restore) { restore.disabled = !this.currentRevisionId || selection.count === 0; }
	   if (label) { label.textContent = selection.all ? 'Восстановить всё' : 'Восстановить выбранное · ' + selection.count; }
	 },

    deleteRevision: function (id) {
      id = parseInt(id || this.currentRevisionId, 10) || 0;
      if (!id) { return; }
      var self = this;
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Удалить ревизию?',
        message: 'Снимок будет удалён без восстановления.',
        confirmLabel: 'Удалить',
        onConfirm: function () {
          var data = new FormData();
          data.append('_csrf', self.csrf());
          self.ajax(self.base() + '/documents/revisions/' + id + '/delete', data, function (payload) {
            Adminx.Toast.show(payload.message || 'Ревизия удалена', 'success');
            self.currentRevisionId = 0;
            self.refreshRevisions();
          });
        }
      });
    },

    clearRevisions: function () {
      if (!this.currentRevisionDocumentId) { return; }
      var self = this;
      Adminx.Confirm.open({
        kind: 'danger',
        title: 'Удалить все ревизии?',
        message: 'Будет очищена вся история снимков этого документа.',
        confirmLabel: 'Удалить все',
        onConfirm: function () {
          var data = new FormData();
          data.append('_csrf', self.csrf());
          self.ajax(self.base() + '/documents/' + self.currentRevisionDocumentId + '/revisions/delete', data, function (payload) {
            Adminx.Toast.show(payload.message || 'Ревизии удалены', 'success');
            self.currentRevisionId = 0;
            self.refreshRevisions();
          });
        }
      });
    },

    restoreRevision: function () {
      if (!this.currentRevisionId) { return; }
      var self = this;
      var id = this.currentRevisionId;
	  var selection = this.revisionSelection();
	  if (!selection.count) { Adminx.Toast.show('Выберите данные для восстановления', 'warning'); return; }
      Adminx.Confirm.open({
        kind: 'warning',
		title: selection.all ? 'Восстановить ревизию?' : 'Восстановить выбранные данные?',
		message: 'Текущее состояние документа будет сохранено отдельной ревизией. Затем восстановится ' + (selection.all ? 'весь выбранный снимок.' : selection.count + ' отмеченных элементов.'),
        confirmLabel: 'Восстановить',
        onConfirm: function () {
          var data = new FormData();
          data.append('_csrf', self.csrf());
		  if (!selection.all) {
			data.append('selection_mode', 'selected');
			selection.documentKeys.forEach(function (key) { data.append('document_keys[]', key); });
			selection.fieldIds.forEach(function (fieldId) { data.append('field_ids[]', String(fieldId)); });
		  }
          self.ajax(self.base() + '/documents/revisions/' + id + '/restore', data, function (payload) {
            Adminx.Toast.show(payload.message || 'Документ восстановлен', 'success');
            var redirect = payload.redirect || (self.base() + '/documents/' + self.currentRevisionDocumentId + '/edit');
            window.location.href = redirect;
          });
        }
      });
    },

    esc: function (value) {
      return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[ch];
      });
    },

    setSubtitle: function () {}
  };

  document.addEventListener('DOMContentLoaded', function () { Adminx.Documents.init(); });
})(window, document);

/**
 * Universal document batch editor. The server owns the frozen ID list; this
 * client only renders the preview and advances the prepared plan by chunks.
 */
(function (window, document) {
  'use strict';

  var Adminx = window.Adminx || (window.Adminx = {});
  var root = document.querySelector('[data-document-bulk-editor]');
  if (!root) { return; }

  var form = root.querySelector('[data-document-bulk-form]');
  var operation = root.querySelector('[data-bulk-operation]');
  var scope = root.querySelector('[name="scope"]');
  var rubric = root.querySelector('[data-bulk-rubric]');
  var target = root.querySelector('[data-bulk-target]');
  var fieldGroup = root.querySelector('[data-bulk-field-options]');
  var previewPanel = root.querySelector('[data-bulk-preview-panel]');
  var progressPanel = root.querySelector('[data-bulk-progress-panel]');
  var token = '';
  var stopped = false;

  function updateTargetHelp() {
    var help = root.querySelector('[data-bulk-target-help]');
    if (!help || !target) { return; }
    var selected = target.options[target.selectedIndex];
    var editsField = operation && ['fill', 'set', 'clear', 'replace'].indexOf(operation.value) !== -1;
    var message = editsField && selected ? String(selected.getAttribute('data-help') || '') : '';
    if (operation && operation.value === 'strip_description_styles') {
      message = Adminx.tr('Только поле с ролью «Описание товара». Будут удалены атрибуты style, включая отступы, цвета и размеры текста. Другие поля, классы, заголовки и блоки <style> не изменяются. HTML будет нормализован при разборе.');
    }
    if (editsField && selected && selected.value === 'document_title' && scope && scope.value === 'products') {
      message = 'Это заголовок документа в панели. Название товара на сайте обычно берётся из поля рубрики, помеченного «витрина: название товара на сайте».';
    }
    var text = help.querySelector('span');
    if (text) { text.textContent = message; }
    help.hidden = message === '';
  }

  function endpoint(path) {
    return Adminx.base() + '/documents/bulk-editor' + path;
  }

  function body(payload) {
    var response = payload && payload.data ? payload.data : {};
    if (!payload || !payload.ok || response.success === false) {
      throw new Error(response.message || 'Операция не выполнена');
    }
    return response.data || {};
  }

  function post(path, data) {
    return Adminx.Ajax.post(endpoint(path), data).then(body);
  }

  function setVisibility(selector, visible) {
    var node = root.querySelector(selector);
    if (node) { node.hidden = !visible; }
  }

  function updateOperation() {
    var value = operation ? operation.value : '';
    if (scope) {
      Array.prototype.forEach.call(scope.options || [], function (option) {
        option.disabled = value === 'strip_description_styles' && option.value !== 'products';
      });
      if (value === 'strip_description_styles') { scope.value = 'products'; }
    }
    var editsField = ['fill', 'set', 'clear', 'replace'].indexOf(value) !== -1;
    setVisibility('[data-bulk-target-wrap]', editsField);
    setVisibility('[data-bulk-search-wrap]', value === 'replace');
	setVisibility('[data-bulk-value-wrap]', value === 'fill' || value === 'set' || value === 'replace');
	setVisibility('[data-bulk-rubric-target-wrap]', value === 'move');
	setVisibility('[data-bulk-sitemap-frequency-wrap]', value === 'sitemap_frequency');
	setVisibility('[data-bulk-sitemap-priority-wrap]', value === 'sitemap_priority');
	setVisibility('[data-bulk-technical-help]', value === 'technical' || value === 'public');
    updateTargetHelp();
  }

  function loadFields() {
    if (!fieldGroup) { return; }
    fieldGroup.replaceChildren();
    var id = rubric ? Number(rubric.value || 0) : 0;
    if (!id) {
      var empty = document.createElement('option');
      empty.disabled = true;
      empty.textContent = 'Сначала выберите рубрику';
      fieldGroup.appendChild(empty);
      return;
    }
    Adminx.Ajax.request(endpoint('/fields?rubric_id=' + encodeURIComponent(id))).then(function (payload) {
      var data = body(payload);
      (data.items || []).forEach(function (item) {
        var option = document.createElement('option');
        option.value = 'field:' + item.id;
        option.textContent = item.title + (item.alias ? ' · ' + item.alias : '') + ' [' + item.type + ']'
          + (item.usage ? ' · витрина: ' + item.usage : '');
        option.setAttribute('data-help', item.help || '');
        fieldGroup.appendChild(option);
      });
      if (!fieldGroup.children.length) {
        var empty = document.createElement('option');
        empty.disabled = true;
        empty.textContent = 'В рубрике нет полей';
        fieldGroup.appendChild(empty);
      }
      updateTargetHelp();
    }).catch(function (error) {
      Adminx.Toast.show(error.message || 'Не удалось загрузить поля рубрики', 'error');
    });
  }

  function cell(text, className) {
    var td = document.createElement('td');
    if (className) { td.className = className; }
    td.textContent = text == null ? '' : String(text);
    return td;
  }

  function renderPreview(plan) {
    token = plan.token || '';
    var rows = root.querySelector('[data-bulk-preview-rows]');
    rows.replaceChildren();
    var htmlPreview = plan.operation && plan.operation.type === 'strip_description_styles';
    function valueCell(value) {
      var td = cell(htmlPreview ? '' : value, 'documents-bulk-value-cell');
      if (htmlPreview) {
        var code = document.createElement('code');
        code.className = 'documents-bulk-html-preview';
        code.textContent = value || '';
        td.appendChild(code);
      }
      return td;
    }
    (plan.sample || []).forEach(function (item) {
      var tr = document.createElement('tr');
      var identity = document.createElement('td');
      var title = document.createElement('b');
      var meta = document.createElement('small');
      title.textContent = item.title;
      meta.textContent = '#' + item.id + ' · ' + item.state;
      identity.appendChild(title);
      identity.appendChild(meta);
      tr.appendChild(identity);
      tr.appendChild(cell(item.rubric));
      tr.appendChild(valueCell(item.before));
      tr.appendChild(valueCell(item.after));
      var result = cell(item.changed ? 'Изменится' : (item.note || 'Без изменений'));
      result.className = item.changed ? 'documents-bulk-result is-changed' : 'documents-bulk-result is-skipped';
      if (item.changed && item.note) {
        var note = document.createElement('small');
        note.textContent = item.note;
        result.appendChild(note);
      }
      tr.appendChild(result);
      rows.appendChild(tr);
    });
    root.querySelector('[data-bulk-preview-count]').textContent = plan.total || 0;
    root.querySelector('[data-bulk-preview-summary]').textContent =
      (plan.operation && plan.operation.label ? plan.operation.label : 'Действие') +
      (plan.operation && plan.operation.target_label ? ' · поле: ' + plan.operation.target_label : '') +
      ' · найдено ' + (plan.matched_total || plan.total || 0) +
      ', изменится ' + (plan.total || 0) + '.';
    previewPanel.hidden = false;
    progressPanel.hidden = true;
    previewPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function renderProgress(plan) {
    var finished = plan.status === 'completed' || plan.status === 'cancelled';
    var percent = Number(plan.progress || 0);
    progressPanel.hidden = false;
    root.querySelector('[data-bulk-progress-percent]').textContent = percent + '%';
    root.querySelector('[data-bulk-progress-bar]').style.width = percent + '%';
    root.querySelector('[data-bulk-progress-processed]').textContent = plan.processed || 0;
    root.querySelector('[data-bulk-progress-done]').textContent = plan.done || 0;
    root.querySelector('[data-bulk-progress-skipped]').textContent = plan.skipped || 0;
    root.querySelector('[data-bulk-progress-errors]').textContent = (plan.errors || []).length;
    root.querySelector('[data-bulk-progress-title]').textContent =
      plan.status === 'completed' ? 'Массовое изменение завершено' :
      (plan.status === 'cancelled' ? 'Выполнение остановлено' : 'Обрабатываем документы');
    root.querySelector('[data-bulk-progress-message]').textContent =
      'Обработано ' + (plan.processed || 0) + ' из ' + (plan.total || 0);
    root.querySelector('[data-bulk-cancel]').hidden = finished;
    root.querySelector('[data-bulk-finish]').hidden = !finished;
    var errors = root.querySelector('[data-bulk-errors]');
    errors.hidden = !(plan.errors || []).length;
    errors.replaceChildren();
    (plan.errors || []).forEach(function (message) {
      var line = document.createElement('div');
      line.textContent = message;
      errors.appendChild(line);
    });
  }

  function runNext() {
    if (stopped || !token) { return; }
    var data = new FormData();
    data.append('token', token);
    post('/run', data).then(function (result) {
      var plan = result.plan || {};
      renderProgress(plan);
      if (plan.status === 'running') {
        window.setTimeout(runNext, 120);
      } else {
        Adminx.Toast.show(plan.errors && plan.errors.length ? 'Готово с ошибками' : 'Массовое изменение завершено', plan.errors && plan.errors.length ? 'warning' : 'success');
      }
    }).catch(function (error) {
      renderProgress({ status: 'cancelled', total: 0, processed: 0, errors: [error.message] });
      Adminx.Toast.show(error.message || 'Выполнение остановлено из-за ошибки', 'error');
    });
  }

  if (operation) { operation.addEventListener('change', updateOperation); }
  if (rubric) { rubric.addEventListener('change', loadFields); }
  if (scope) { scope.addEventListener('change', updateTargetHelp); }
  if (target) { target.addEventListener('change', updateTargetHelp); }
  updateOperation();
  loadFields();

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var button = form.querySelector('[data-bulk-preview]');
    button.disabled = true;
    Adminx.Loader.show();
    post('/preview', new FormData(form)).then(function (result) {
      renderPreview(result.plan || {});
      Adminx.Toast.show('Предпросмотр подготовлен', 'success');
    }).catch(function (error) {
      Adminx.Toast.show(error.message || 'Не удалось подготовить предпросмотр', 'error');
    }).then(function () {
      button.disabled = false;
      Adminx.Loader.hide();
    });
  });

  root.addEventListener('click', function (event) {
    var run = event.target.closest('[data-bulk-run]');
    if (run) {
      Adminx.Confirm.open({
        kind: 'warning',
        title: 'Применить изменения ко всему набору?',
        message: 'Будут обработаны все документы из зафиксированного предпросмотра. Для изменённых записей сохранятся ревизии.',
        confirmLabel: 'Запустить',
        onConfirm: function () {
          stopped = false;
          previewPanel.hidden = true;
          renderProgress({ status: 'running', total: Number(root.querySelector('[data-bulk-preview-count]').textContent || 0), processed: 0, done: 0, skipped: 0, errors: [], progress: 0 });
          progressPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
          runNext();
        }
      });
      return;
    }
    if (event.target.closest('[data-bulk-cancel]')) {
      stopped = true;
      var data = new FormData();
      data.append('token', token);
      post('/cancel', data).then(function (result) {
        renderProgress(result.plan || {});
        Adminx.Toast.show('Выполнение остановлено', 'warning');
      }).catch(function (error) {
        Adminx.Toast.show(error.message || 'Не удалось остановить выполнение', 'error');
      });
    }
  });
})(window, document);
