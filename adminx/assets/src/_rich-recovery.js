import { toolDialog } from './_rich-tools';

export function installRecovery(api, root, textarea, editor) {
  const form = textarea.form;
  // Documents own a full-form draft already, including rich editors and source mode.
  if (form && form.querySelector('[data-document-draft-recovery]')) { return; }
  const actor = (form && form.dataset.actorId) || document.querySelector('[data-attention-user]')?.dataset.attentionUser;
  if (!actor || actor === '0' || !textarea.name) { return; }
  const key = 'ave.rich-draft:' + actor + ':' + location.pathname + location.search + ':' + (form?.id || form?.getAttribute('action') || '') + ':' + textarea.name;
  const baseline = textarea.value;
  let candidate;
  let timer;
  let disposed = false;
  const remove = () => { try { localStorage.removeItem(key); } catch (e) {} };
  try {
    candidate = JSON.parse(localStorage.getItem(key) || 'null');
    if (candidate && (typeof candidate.html !== 'string' || Date.now() - candidate.saved > 7 * 86400000 || candidate.html === baseline)) { remove(); candidate = null; }
  } catch (e) { candidate = null; }
  const banner = document.createElement('div');
  banner.className = 'rich-recovery';
  banner.hidden = !candidate;
  banner.innerHTML = '<span>Найден несохранённый текст</span><button type="button" class="btn btn-secondary btn-sm" data-recover>Посмотреть</button><button type="button" class="btn btn-ghost btn-icon btn-sm" aria-label="Удалить локальную копию" data-discard><i class="ti ti-trash"></i></button>';
  root.querySelector('.rich-editor-toolbar').after(banner);
  banner.querySelector('[data-discard]').addEventListener('click', () => { candidate = null; banner.hidden = true; remove(); });
  banner.querySelector('[data-recover]').addEventListener('click', () => {
    if (!candidate) { return; }
    const dialog = toolDialog('Восстановить текст', '<div class="rich-recovery-compare"><label class="field"><span class="field-label">Сейчас на сервере</span><textarea class="textarea" readonly data-current></textarea></label><label class="field"><span class="field-label">Локальная копия</span><textarea class="textarea" readonly data-local></textarea></label></div>', editor);
    dialog.body.querySelector('[data-current]').value = baseline;
    dialog.body.querySelector('[data-local]').value = candidate.html;
    const restore = document.createElement('button');
    restore.type = 'button'; restore.className = 'btn btn-primary'; restore.textContent = 'Восстановить в редактор';
    restore.addEventListener('click', () => { api.replaceHtml(textarea, candidate.html); candidate = null; banner.hidden = true; dialog.close(); });
    dialog.footer.appendChild(restore);
  });
  const save = () => {
    clearTimeout(timer);
    if (disposed || candidate || editor.isDestroyed) { return; }
    const html = api.syncTextarea(textarea, editor);
    if (html === baseline) { remove(); return; }
    if (html.length > 1000000) { return; }
    try { localStorage.setItem(key, JSON.stringify({ html, saved: Date.now() })); } catch (e) {}
  };
  const schedule = () => { clearTimeout(timer); timer = setTimeout(save, 700); };
  textarea.addEventListener('input', schedule);
  window.addEventListener('pagehide', save);
  const acknowledged = () => { clearTimeout(timer); remove(); };
  if (form) { form.addEventListener('rich:saved', acknowledged); }
  editor.on('destroy', () => {
    disposed = true; clearTimeout(timer);
    textarea.removeEventListener('input', schedule);
    window.removeEventListener('pagehide', save);
    if (form) { form.removeEventListener('rich:saved', acknowledged); }
  });
}
