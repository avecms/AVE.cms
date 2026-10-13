import { Extension } from '@tiptap/core';
import { Plugin, PluginKey, TextSelection } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from '@tiptap/pm/view';
import { closeHistory } from '@tiptap/pm/history';

export const searchKey = new PluginKey('richSearch');

export function findMatches(doc, query, sensitive) {
  if (!query) { return []; }
  const matches = [];
  const pattern = new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), sensitive ? 'g' : 'gi');
  doc.descendants((node, pos) => {
    if (!node.isTextblock) { return; }
    // Leaf nodes occupy one ProseMirror position; never match across them.
    const text = node.textBetween(0, node.content.size, '', '\ufffc');
    let found;
    while ((found = pattern.exec(text))) {
      if (!found[0].includes('\ufffc')) { matches.push({ from: pos + 1 + found.index, to: pos + 1 + found.index + found[0].length }); }
    }
    return false;
  });
  return matches;
}

export const RichSearch = Extension.create({
  name: 'richSearch',
  addProseMirrorPlugins() {
    return [new Plugin({
      key: searchKey,
      state: {
        init: () => ({ query: '', sensitive: false, active: 0, matches: [] }),
        apply(tr, previous) {
          const state = { ...previous, ...(tr.getMeta(searchKey) || {}) };
          if (tr.docChanged || tr.getMeta(searchKey)) { state.matches = findMatches(tr.doc, state.query, state.sensitive); }
          state.active = Math.max(0, Math.min(state.active, state.matches.length - 1));
          return state;
        }
      },
      props: {
        decorations(state) {
          const search = searchKey.getState(state);
          return DecorationSet.create(state.doc, search.matches.map((match, index) => Decoration.inline(match.from, match.to, { class: index === search.active ? 'rich-search-match is-current' : 'rich-search-match' })));
        }
      }
    })];
  }
});

export const ContentFragments = Extension.create({
  name: 'contentFragments',
  addGlobalAttributes() {
    return [{ types: ['blockquote'], attributes: { class: {
      default: null,
      parseHTML: element => {
        const name = element.getAttribute('class') || '';
        return /\bcontent-callout(?:--(?:warning|info))?\b/.test(name) ? name : null;
      }
    } } }];
  }
});

export function moveBlock(editor, fromIndex, toIndex) {
  const blocks = [];
  editor.state.doc.forEach((node, pos) => blocks.push({ node, pos }));
  if (!blocks[fromIndex] || toIndex < 0 || toIndex > blocks.length || fromIndex === toIndex || fromIndex + 1 === toIndex) { return false; }
  const source = blocks[fromIndex];
  let target = toIndex === blocks.length ? editor.state.doc.content.size : blocks[toIndex].pos;
  if (target > source.pos) { target -= source.node.nodeSize; }
  const tr = closeHistory(editor.state.tr).delete(source.pos, source.pos + source.node.nodeSize).insert(target, source.node);
  tr.setSelection(TextSelection.near(tr.doc.resolve(target))).scrollIntoView();
  editor.view.dispatch(tr);
  return true;
}
