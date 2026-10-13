// Clipboard HTML is parsed in an inert document, never inserted into the live DOM.
export function isWordHtml(html) {
  return /\bmso-|class=["']?Mso|urn:schemas-microsoft-com:office|Microsoft Word/i.test(html);
}

export function cleanWordHtml(html) {
  const doc = new DOMParser().parseFromString(html, 'text/html');
  doc.querySelectorAll('script,style,meta,link,iframe,object,embed,svg,math,form,input,button').forEach(node => node.remove());
  const comments = doc.createTreeWalker(doc.body, NodeFilter.SHOW_COMMENT);
  const remove = [];
  while (comments.nextNode()) { remove.push(comments.currentNode); }
  remove.forEach(node => node.remove());

  doc.querySelectorAll('p').forEach(node => {
    const level = (node.className || '').match(/\bMsoHeading([1-6])\b/i) || (node.getAttribute('style') || '').match(/mso-outline-level:\s*([1-6])\b/i);
    if (!level) { return; }
    const heading = doc.createElement('h' + level[1]);
    while (node.firstChild) { heading.appendChild(node.firstChild); }
    node.replaceWith(heading);
  });

  // Desktop Word exports list items as paragraphs with mso-list and marker spans.
  const lists = new Map();
  doc.querySelectorAll('p').forEach(node => {
    const style = node.getAttribute('style') || '';
    const match = style.match(/mso-list:\s*(l\d+)\s+level(\d+)\s+(lfo\d+)/i);
    if (!match) { return; }
    const marker = Array.from(node.querySelectorAll('span')).find(span => /mso-list:\s*ignore/i.test(span.getAttribute('style') || ''));
    const label = marker ? marker.textContent.trim() : '';
    const ordered = /^(?:\d+|[a-z]+)[.)]/i.test(label);
    lists.set(node, { key: match[1] + match[3], level: Number(match[2]), type: ordered ? 'ol' : 'ul', start: /^\d+/.test(label) ? parseInt(label, 10) : 1 });
    if (marker) { marker.remove(); }
  });
  const parents = new Set(Array.from(lists.keys()).map(node => node.parentNode));
  parents.forEach(parent => {
    let stack = [];
    let key = '';
    Array.from(parent.childNodes).forEach(node => {
      if (node.nodeType === 3 && !node.textContent.trim()) { return; }
      const item = lists.get(node);
      if (!item) { stack = []; key = ''; return; }
      if (key !== item.key) { stack = []; key = item.key; }
      while (stack.length && stack[stack.length - 1].level > item.level) { stack.pop(); }
      if (stack.length && stack[stack.length - 1].level === item.level && stack[stack.length - 1].type !== item.type) { stack.pop(); }
      if (!stack.length || stack[stack.length - 1].level < item.level) {
        const list = doc.createElement(item.type);
        if (item.type === 'ol' && item.start > 1) { list.setAttribute('start', item.start); }
        if (stack.length) { stack[stack.length - 1].last.appendChild(list); }
        else { parent.insertBefore(list, node); }
        stack.push({ level: item.level, type: item.type, list: list, last: null });
      }
      const li = doc.createElement('li');
      const entry = stack[stack.length - 1];
      entry.list.appendChild(li);
      li.appendChild(node);
      entry.last = li;
    });
  });

  const allowed = new Set('p div br h1 h2 h3 h4 h5 h6 strong b em i u s strike del blockquote pre code ul ol li a table thead tbody tfoot tr td th hr'.split(' '));
  Array.from(doc.body.querySelectorAll('*')).reverse().forEach(node => {
    if (!node.parentNode) { return; }
    const tag = node.tagName.toLowerCase();
    // Convert meaningful inline formatting before stripping presentation attributes.
    const marks = [];
    const weight = node.style.fontWeight;
    if (weight === 'bold' || Number(weight) >= 600) { marks.push('strong'); }
    if (node.style.fontStyle === 'italic') { marks.push('em'); }
    const decoration = node.style.textDecoration;
    if (/underline/.test(decoration)) { marks.push('u'); }
    if (/line-through/.test(decoration)) { marks.push('s'); }
    marks.forEach(mark => {
      const wrapper = doc.createElement(mark);
      while (node.firstChild) { wrapper.appendChild(node.firstChild); }
      node.appendChild(wrapper);
    });
    Array.from(node.attributes).forEach(attr => {
      const name = attr.name.toLowerCase();
      const value = attr.value.trim();
      const safeLink = tag === 'a' && name === 'href' && /^(https?:\/\/|mailto:|tel:|\/|#)/i.test(value) && !/[\u0000-\u0020]/.test(value);
      const span = /^(td|th)$/.test(tag) && /^(colspan|rowspan)$/.test(name) && /^[1-9]\d{0,2}$/.test(value);
      const start = tag === 'ol' && name === 'start' && /^[1-9]\d{0,5}$/.test(value);
      if (!safeLink && !span && !start) { node.removeAttribute(attr.name); }
    });
    if (!allowed.has(tag)) {
      node.replaceWith(...Array.from(node.childNodes));
    }
  });
  return doc.body.innerHTML;
}
