import { searchKey, moveBlock } from './_rich-extensions';
import { closeHistory } from '@tiptap/pm/history';

const escape = value => String(value == null ? '' : value).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
export const tr = value => window.Adminx.tr ? window.Adminx.tr(value) : value;
const button = (action, title, icon, text = '') => `<button type="button" class="btn btn-ghost btn-icon btn-sm" data-tool="${action}" aria-label="${escape(tr(title))}" data-tooltip="${escape(tr(title))}"><i class="ti ti-${icon}"></i>${escape(text)}</button>`;

export function readOptions(textarea) {
  let options = {};
  try { options = JSON.parse(textarea.getAttribute('data-rich-options') || '{}'); } catch (e) {}
  const profile = options.profile || (textarea.getAttribute('data-rich-preset') === 'slim' ? 'compact' : 'full');
  let headings = options.headings || 'profile';
  if (headings === 'profile') { headings = ['product', 'news'].includes(profile) ? 'no_h1' : 'all'; }
  return { ...options, profile, headings, product: !!textarea.closest('[data-catalog-mode="1"]'), snippets: options.snippets || 'all' };
}

// One reusable dialog for the editor tools; content is trusted markup, values are escaped.
export function toolDialog(title, content, editor, onClose) {
  const overlay = document.createElement('div');
  overlay.className = 'overlay rich-editor-tools-overlay show';
  overlay.innerHTML = `<div class="modal rich-tools-modal" role="dialog" aria-modal="true" aria-label="${escape(tr(title))}"><div class="modal-header"><h3>${escape(tr(title))}</h3><button class="modal-close" type="button" data-tools-close aria-label="${escape(tr('Закрыть'))}"><i class="ti ti-x"></i></button></div><div class="modal-body">${content}</div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-tools-close>${escape(tr('Закрыть'))}</button></div></div>`;
  document.body.appendChild(overlay);
  let closed = false;
  const previous = document.activeElement;
  const close = () => {
    if (closed) { return; }
    closed = true;
    if (editor) { editor.off('destroy', close); }
    document.removeEventListener('keydown', key, true);
    overlay.remove();
    if (onClose) { onClose(); }
    if (previous && previous.isConnected) { previous.focus({ preventScroll: true }); }
  };
  const key = event => {
    if (event.key === 'Escape') { event.preventDefault(); event.stopImmediatePropagation(); close(); }
    if (event.key === 'Tab') {
      const nodes = Array.from(overlay.querySelectorAll('button:not(:disabled),input,select,textarea,a[href]')).filter(node => !node.hidden && node.getClientRects().length);
      if (!nodes.length) { return; }
      const index = nodes.indexOf(document.activeElement);
      event.preventDefault();
      nodes[(index + (event.shiftKey ? nodes.length - 1 : 1)) % nodes.length].focus();
    }
  };
  document.addEventListener('keydown', key, true);
  if (editor) { editor.on('destroy', close); }
  overlay.addEventListener('click', event => { if (event.target === overlay || event.target.closest('[data-tools-close]')) { close(); } });
  const first = overlay.querySelector('.modal-body input, .modal-body select, .modal-body button, .modal-close');
  if (first) { first.focus(); }
  return { overlay, close, body: overlay.querySelector('.modal-body'), footer: overlay.querySelector('.modal-footer') };
}

export function openSearch(editor) {
  const dialog = toolDialog('Найти и заменить', `<label class="field"><span class="field-label">${tr('Найти')}</span><input class="input" data-find autocomplete="off"></label><label class="field"><span class="field-label">${tr('Заменить на')}</span><input class="input" data-replace autocomplete="off"></label><label class="checkbox"><input type="checkbox" data-case> ${tr('Учитывать регистр')}</label><div class="rich-tools-actions">${button('prev', 'Предыдущее', 'arrow-up')}${button('next', 'Следующее', 'arrow-down')}<output data-find-count aria-live="polite"></output></div><div class="rich-tools-actions"><button type="button" class="btn btn-secondary" data-replace-one>${tr('Заменить')}</button><button type="button" class="btn btn-secondary" data-replace-all>${tr('Заменить все')}</button></div>`, editor, () => {
    editor.off('transaction', updateCount);
    if (!editor.isDestroyed) { editor.view.dispatch(editor.state.tr.setMeta(searchKey, { query: '' })); }
  });
  const query = dialog.body.querySelector('[data-find]');
  const replacement = dialog.body.querySelector('[data-replace]');
  const sensitive = dialog.body.querySelector('[data-case]');
  function updateCount() {
    const state = searchKey.getState(editor.state);
    dialog.body.querySelector('[data-find-count]').textContent = state.matches.length ? `${state.active + 1} / ${state.matches.length}` : tr('Нет совпадений');
    dialog.body.querySelectorAll('[data-replace-one],[data-replace-all],[data-tool]').forEach(node => { node.disabled = !state.matches.length; });
  }
  function search() { editor.view.dispatch(editor.state.tr.setMeta(searchKey, { query: query.value, sensitive: sensitive.checked, active: 0 })); }
  function next(step) {
    const state = searchKey.getState(editor.state);
    if (!state.matches.length) { return; }
    const active = (state.active + step + state.matches.length) % state.matches.length;
    editor.view.dispatch(editor.state.tr.setMeta(searchKey, { active }));
    const node = editor.view.dom.querySelector('.rich-search-match.is-current');
    if (node) { node.scrollIntoView({ block: 'nearest' }); }
  }
  query.addEventListener('input', search);
  sensitive.addEventListener('change', search);
  query.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); next(event.shiftKey ? -1 : 1); } });
  dialog.body.addEventListener('click', event => {
    const control = event.target.closest('button');
    if (!control || control.disabled) { return; }
    if (control.dataset.tool) { next(control.dataset.tool === 'prev' ? -1 : 1); return; }
    const state = searchKey.getState(editor.state);
    const matches = control.hasAttribute('data-replace-all') ? state.matches : [state.matches[state.active]];
    const transaction = closeHistory(editor.state.tr);
    matches.slice().reverse().forEach(match => {
      // Insert plain text with the first character's marks, never parse replacement as HTML.
      if (replacement.value) { transaction.replaceWith(match.from, match.to, editor.schema.text(replacement.value, transaction.doc.nodeAt(match.from)?.marks || [])); }
      else { transaction.delete(match.from, match.to); }
    });
    editor.view.dispatch(transaction);
    editor.view.dispatch(closeHistory(editor.state.tr));
  });
  editor.on('transaction', updateCount);
  const selected = editor.state.doc.textBetween(editor.state.selection.from, editor.state.selection.to, ' ');
  query.value = selected;
  search();
}

export function openSymbols(editor) {
  const selection = { from: editor.state.selection.from, to: editor.state.selection.to };
  const symbols = [['\u00a0', 'Неразрывный пробел'], ['°', 'Градус'], ['×', 'Умножение'], ['±', 'Плюс-минус'], ['®', 'Зарегистрированный знак'], ['©', 'Авторское право'], ['™', 'Товарный знак'], ['≤', 'Меньше или равно'], ['≥', 'Больше или равно'], ['µ', 'Микро'], ['²', 'Квадрат'], ['³', 'Куб'], ['«', 'Открывающая кавычка'], ['»', 'Закрывающая кавычка'], ['–', 'Короткое тире'], ['—', 'Длинное тире'], ['…', 'Многоточие']];
  const dialog = toolDialog('Специальные символы', `<div class="rich-symbols">${symbols.map(([symbol, name], index) => `<button type="button" class="btn btn-secondary" data-symbol="${index}" aria-label="${escape(tr(name))}" data-tooltip="${escape(tr(name))}">${index ? escape(symbol) : '␣'}</button>`).join('')}</div>`, editor);
  dialog.body.addEventListener('click', event => {
    const item = event.target.closest('[data-symbol]');
    if (!item) { return; }
    editor.chain().focus().insertContentAt(selection, { type: 'text', text: symbols[Number(item.dataset.symbol)][0] }).run();
    dialog.close();
  });
}

const snippets = [
  { id: 'warning', title: 'Внимание', group: 'info', html: '<blockquote class="content-callout content-callout--warning"><p><strong>Внимание</strong></p><p>Текст предупреждения.</p></blockquote>' },
  { id: 'important', title: 'Важно', group: 'info', html: '<blockquote class="content-callout content-callout--info"><p><strong>Важно</strong></p><p>Дополнительная информация.</p></blockquote>' },
  { id: 'equipment', title: 'Комплектация', group: 'product', html: '<h2>Комплектация</h2><ul><li><p>Наименование позиции</p></li></ul>' },
  { id: 'specs', title: 'Таблица характеристик', group: 'product', html: '<h2>Характеристики</h2><table><tr><th><p>Параметр</p></th><th><p>Значение</p></th></tr><tr><td><p>Наименование</p></td><td><p>Значение</p></td></tr></table>' }
];

export function openSnippets(editor, options) {
  const selection = { from: editor.state.selection.from, to: editor.state.selection.to };
  const available = options.snippets === 'none' ? [] : snippets.filter(item => options.snippets === 'all' || item.group === options.snippets).filter(item => !options.hide_table || item.id !== 'specs');
  const dialog = toolDialog('Готовые фрагменты', `<div class="rich-snippets">${available.map(item => `<button type="button" class="btn btn-secondary" data-snippet="${item.id}"><i class="ti ti-${item.id === 'specs' ? 'table' : 'template'}"></i>${escape(tr(item.title))}</button>`).join('')}</div>`, editor);
  dialog.body.addEventListener('click', event => {
    const control = event.target.closest('[data-snippet]');
    const item = control && available.find(candidate => candidate.id === control.dataset.snippet);
    if (!item) { return; }
    editor.chain().focus().insertContentAt(selection, item.html).run();
    dialog.close();
  });
}

export function openBlocks(editor) {
  const dialog = toolDialog('Порядок блоков', '<div class="rich-block-list" data-block-list></div>', editor, () => editor.off('update', render));
  const list = dialog.body.firstElementChild;
  let dragged = null;
  function render() {
    const rows = [];
    editor.state.doc.forEach((node, pos, index) => {
      const names = { paragraph: 'Абзац', heading: 'Заголовок', bulletList: 'Список', orderedList: 'Нумерованный список', blockquote: 'Цитата', image: 'Изображение', table: 'Таблица', horizontalRule: 'Разделитель', codeBlock: 'Код' };
      const title = node.type.name === 'heading' ? `H${node.attrs.level}` : tr(names[node.type.name] || node.type.name);
      rows.push(`<div class="rich-block-row" draggable="true" data-block-index="${index}"><i class="ti ti-grip-vertical" aria-hidden="true"></i><span><b>${escape(title)}</b><small>${escape((node.textContent || node.attrs.alt || node.attrs.src || '').slice(0, 120))}</small></span>${button('up', 'Переместить вверх', 'arrow-up')}${button('down', 'Переместить вниз', 'arrow-down')}</div>`);
    });
    list.innerHTML = rows.join('');
    if (list.firstElementChild) { list.firstElementChild.querySelector('[data-tool=up]').disabled = true; list.lastElementChild.querySelector('[data-tool=down]').disabled = true; }
  }
  list.addEventListener('dragstart', event => {
    const row = event.target.closest('[data-block-index]');
    if (!row) { return; }
    dragged = Number(row.dataset.blockIndex);
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(dragged));
  });
  list.addEventListener('dragover', event => { if (dragged !== null) { event.preventDefault(); event.dataTransfer.dropEffect = 'move'; } });
  list.addEventListener('drop', event => {
    if (dragged === null) { return; }
    event.preventDefault();
    const row = event.target.closest('[data-block-index]');
    let target = editor.state.doc.childCount;
    if (row) { target = Number(row.dataset.blockIndex) + (event.clientY > row.getBoundingClientRect().top + row.offsetHeight / 2 ? 1 : 0); }
    moveBlock(editor, dragged, target);
    dragged = null;
  });
  list.addEventListener('dragend', () => { dragged = null; });
  list.addEventListener('click', event => {
    const control = event.target.closest('[data-tool]');
    if (!control || control.disabled) { return; }
    const index = Number(control.closest('[data-block-index]').dataset.blockIndex);
    moveBlock(editor, index, control.dataset.tool === 'up' ? index - 1 : index + 2);
  });
  editor.on('update', render);
  render();
}

export function inspectHtml(html, options) {
  const doc = new DOMParser().parseFromString(html, 'text/html');
  const issues = [];
  doc.body.querySelectorAll('*').forEach(node => {
    const tag = node.tagName;
    const sample = node.textContent.trim().slice(0, 90);
    if (tag === 'H1' && (options.product || options.headings !== 'all')) { issues.push({ node, kind: 'heading', title: 'H1 в тексте', sample }); }
    else if (/^H[1-6]$/.test(tag) && !node.textContent.trim() && !node.querySelector('img')) { issues.push({ node, kind: 'empty', title: 'Пустой заголовок', sample }); }
    if (tag === 'A' && (!(node.getAttribute('href') || '').trim() || (!node.textContent.trim() && !node.querySelector('img')))) { issues.push({ node, kind: 'link', title: 'Пустая ссылка', sample }); }
    if (node.hasAttribute('style')) { issues.push({ node, kind: 'style', title: 'Встроенное оформление', sample: `${sample} (${node.getAttribute('style')})`.slice(0, 140) }); }
    if (tag === 'P' && !node.textContent.trim() && !node.querySelector('img,table') && node.previousElementSibling && node.previousElementSibling.tagName === 'P' && !node.previousElementSibling.textContent.trim()) { issues.push({ node, kind: 'empty', title: 'Лишний пустой абзац', sample }); }
  });
  return { doc, issues };
}

export function openReview(editor, textarea, options) {
  const api = window.Adminx.RichEditor;
  const dialog = toolDialog('Проверка оформления', '<div data-review-list></div>', editor);
  function render() {
    const result = inspectHtml(api.syncTextarea(textarea, editor), options);
    dialog.body.firstElementChild.innerHTML = result.issues.length ? result.issues.map((issue, index) => `<div class="rich-review-row"><span><b>${escape(tr(issue.title))}</b><small>${escape(issue.sample)}</small></span><button type="button" class="btn btn-secondary btn-sm" data-fix="${index}">${tr('Исправить')}</button></div>`).join('') : `<div class="rich-tools-empty"><i class="ti ti-circle-check"></i>${tr('Замечаний нет')}</div>`;
    dialog.body.querySelectorAll('[data-fix]').forEach(control => control.addEventListener('click', () => {
      const issue = result.issues[Number(control.dataset.fix)];
      if (issue.kind === 'style') { issue.node.removeAttribute('style'); }
      if (issue.kind === 'empty') { issue.node.remove(); }
      if (issue.kind === 'link') { issue.node.replaceWith(...Array.from(issue.node.childNodes)); }
      if (issue.kind === 'heading') {
        const heading = result.doc.createElement('h2');
        Array.from(issue.node.attributes).forEach(attr => heading.setAttribute(attr.name, attr.value));
        while (issue.node.firstChild) { heading.appendChild(issue.node.firstChild); }
        issue.node.replaceWith(heading);
      }
      api.replaceHtml(textarea, result.doc.body.innerHTML);
      render();
    }));
  }
  render();
}

export function reviewBeforeSave(form, done) {
  const fields = Array.from(form.querySelectorAll('textarea[data-rich-editor]')).filter(node => node._adminxTiptap && !node._adminxTiptap.isDestroyed);
  const issues = fields.map(field => ({ field, result: inspectHtml(window.Adminx.RichEditor.syncTextarea(field, field._adminxTiptap), readOptions(field)) })).filter(item => item.result.issues.length);
  if (!issues.length) { return false; }
  const dialog = toolDialog('Проверьте оформление', `<div class="rich-review-summary">${issues.map((item, index) => `<div class="rich-review-row"><span><b>${escape(item.field.closest('.ax-document-field')?.querySelector('.field-label')?.textContent || item.field.name || tr('Текст'))}</b><small>${escape(item.result.issues.map(issue => tr(issue.title)).filter((v, i, a) => a.indexOf(v) === i).join(', '))}</small></span><button type="button" class="btn btn-secondary btn-sm" data-check-field="${index}">${tr('Проверить')}</button></div>`).join('')}</div>`, null);
  const save = document.createElement('button');
  save.type = 'button'; save.className = 'btn btn-primary'; save.textContent = tr('Сохранить без исправлений');
  save.addEventListener('click', () => { dialog.close(); done(); });
  dialog.footer.appendChild(save);
  dialog.body.addEventListener('click', event => {
    const control = event.target.closest('[data-check-field]');
    if (!control) { return; }
    const field = issues[Number(control.dataset.checkField)].field;
    dialog.close();
    openReview(field._adminxTiptap, field, readOptions(field));
  });
  return true;
}

export function installTools(api, root, textarea, editor, options) {
  const footer = document.createElement('div');
  footer.className = 'rich-editor-status';
  root.appendChild(footer);
  const tablebar = document.createElement('div');
  tablebar.className = 'rich-table-tools';
  tablebar.hidden = true;
  const commands = [
    ['addRowBefore', 'Строка выше', 'row-insert-top'], ['addRowAfter', 'Строка ниже', 'row-insert-bottom'], ['deleteRow', 'Удалить строку', 'row-remove'],
    ['addColumnBefore', 'Столбец слева', 'column-insert-left'], ['addColumnAfter', 'Столбец справа', 'column-insert-right'], ['deleteColumn', 'Удалить столбец', 'column-remove'],
    ['mergeCells', 'Объединить ячейки', 'table-alias'], ['splitCell', 'Разделить ячейку', 'table-options'], ['toggleHeaderRow', 'Строка заголовков', 'table-row'], ['toggleHeaderColumn', 'Столбец заголовков', 'table-column'], ['deleteTable', 'Удалить таблицу', 'table-minus']
  ];
  tablebar.innerHTML = commands.map(([name, title, icon]) => button(name, title, icon)).join('');
  root.querySelector('.rich-editor-toolbar').after(tablebar);
  tablebar.addEventListener('mousedown', event => event.preventDefault());
  tablebar.addEventListener('click', event => { const control = event.target.closest('[data-tool]'); if (control && !control.disabled) { editor.chain().focus()[control.dataset.tool]().run(); } });
  let lastDoc;
  let total = '';
  const stats = value => `${tr('Слов')}: ${(value.trim().match(/\S+/gu) || []).length} · ${tr('Символов')}: ${Array.from(value).length}`;
  function update() {
    const source = root.classList.contains('rich-editor-source-open');
    tablebar.hidden = source || !editor.isActive('table') || options.hide_table;
    if (!tablebar.hidden) { commands.forEach(([name]) => { tablebar.querySelector(`[data-tool="${name}"]`).disabled = !editor.can()[name](); }); }
    const selected = editor.state.doc.textBetween(editor.state.selection.from, editor.state.selection.to, ' ');
    if (source && root._richSourceEditor) {
      total = stats(new DOMParser().parseFromString(root._richSourceEditor.getValue(), 'text/html').body.textContent);
      lastDoc = null;
    } else if (lastDoc !== editor.state.doc) {
      lastDoc = editor.state.doc;
      total = stats(lastDoc.textBetween(0, lastDoc.content.size, ' '));
    }
    footer.textContent = total + (!source && selected ? ' | ' + tr('Выделено') + ': ' + stats(selected) : '');
  }
  editor.on('transaction', update);
  root.addEventListener('rich:source-change', update);
  root.addEventListener('rich:mode-change', update);
  update();
  editor.on('destroy', () => { editor.off('transaction', update); root.removeEventListener('rich:source-change', update); root.removeEventListener('rich:mode-change', update); });
}
