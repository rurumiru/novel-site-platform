import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';

export function initTiptapEditor(selector, { content = '', placeholder = 'Начните печатать...', onUpdate }) {
  const el = document.querySelector(selector);
  if (!el) return null;

  const editor = new Editor({
    element: el,
    extensions: [
      StarterKit,
      Placeholder.configure({ placeholder }),
    ],
    content: content || '',
    editorProps: {
      attributes: {
        class: 'prose prose-lg dark:prose-invert max-w-none min-h-[300px] p-4 focus:outline-none',
      },
    },
    onUpdate: ({ editor }) => {
      const html = editor.getHTML();
      if (onUpdate) onUpdate(html);
    },
  });

  return editor;
}
