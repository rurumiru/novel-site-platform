/**
 * TipTap редактор для глав (CDN, без сборки)
 * esm.sh — загрузка модулей в браузере
 */
import { Editor } from 'https://esm.sh/@tiptap/core@2';
import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2';
import Placeholder from 'https://esm.sh/@tiptap/extension-placeholder@2';
import Image from 'https://esm.sh/@tiptap/extension-image@2';

function uploadImage(file, uploadUrl, csrfToken) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('_token', csrfToken);
    return fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then(r => {
        if (!r.ok) throw new Error('Upload failed');
        return r.json();
    });
}

function initChapterEditor(options) {
    const {
        element,
        content = '',
        placeholder = 'Начните печатать...',
        onUpdate,
        uploadUrl = '/api/upload/chapter-image',
        csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    } = options;

    const el = typeof element === 'string' ? document.querySelector(element) : element;
    if (!el) return null;

    const editor = new Editor({
        element: el,
        extensions: [
            StarterKit,
            Placeholder.configure({ placeholder }),
            Image.configure({
                inline: false,
                allowBase64: true,
                HTMLAttributes: { class: 'max-w-full h-auto rounded-lg my-2', loading: 'lazy' },
            }),
        ],
        content: (content && String(content).trim()) ? content : '<p></p>',
        editorProps: {
            attributes: {
                class: 'prose prose-lg dark:prose-invert max-w-none min-h-[200px] p-4 focus:outline-none',
            },
            handlePaste: (view, event) => {
                const items = event.clipboardData?.items;
                if (!items) return false;
                const imageFiles = [];
                let hasHtml = false;
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.startsWith('image/')) {
                        const file = items[i].getAsFile();
                        if (file) imageFiles.push(file);
                    } else if (items[i].type === 'text/html') {
                        hasHtml = true;
                    }
                }
                if (imageFiles.length === 0) return false;
                if (hasHtml) return false;
                event.preventDefault();
                imageFiles.forEach((file, i) => {
                    uploadImage(file, uploadUrl, csrfToken)
                        .then(data => {
                            const url = (typeof data === 'string' ? data : (data.url || data.path)) || '';
                            if (!url) return;
                            editor.chain().focus().insertContent(`<p><img src="${url}" alt="" class="max-w-full h-auto rounded-lg my-2" loading="lazy"></p>`).run();
                        })
                        .catch(() => console.warn('Failed to upload pasted image'));
                });
                return true;
            },
            handleDrop: (view, event) => {
                const files = event.dataTransfer?.files;
                if (!files?.length) return false;
                const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
                if (imageFiles.length === 0) return false;
                event.preventDefault();
                imageFiles.forEach(file => {
                    uploadImage(file, uploadUrl, csrfToken)
                        .then(data => {
                            const url = (typeof data === 'string' ? data : (data.url || data.path)) || '';
                            if (!url) return;
                            const pos = view.posAtCoords({ left: event.clientX, top: event.clientY });
                            const img = `<p><img src="${url}" alt="" class="max-w-full h-auto rounded-lg my-2" loading="lazy"></p>`;
                            if (pos) editor.chain().focus().insertContentAt(pos.pos, img).run();
                            else editor.chain().focus().insertContent(img).run();
                        })
                        .catch(() => console.warn('Failed to upload dropped image'));
                });
                return true;
            },
        },
        onUpdate: ({ editor: e }) => {
            if (onUpdate) onUpdate(e.getHTML());
        },
    });

    return editor;
}

window.initChapterEditor = initChapterEditor;
