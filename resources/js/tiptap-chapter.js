
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Image from '@tiptap/extension-image';

const DEFAULT_UPLOAD_URL = '/api/upload/chapter-image';

function uploadImage(file, uploadUrl, csrfToken) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('_token', csrfToken);

    return fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    }).then(r => {
        if (!r.ok) throw new Error('Upload failed');
        return r.json();
    });
}

export function initChapterEditor(options) {
    const {
        element,
        content = '',
        placeholder = 'Начните печатать...',
        onUpdate,
        uploadUrl = DEFAULT_UPLOAD_URL,
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
                HTMLAttributes: {
                    class: 'max-w-full h-auto rounded-lg my-2',
                    loading: 'lazy',
                },
            }),
        ],
        content: content || '<p></p>',
        editorProps: {
            attributes: {
                class: 'prose prose-lg dark:prose-invert max-w-none min-h-[200px] p-4 focus:outline-none',
            },
            handlePaste: (view, event) => {
                const items = event.clipboardData?.items;
                if (!items) return false;
                const imageFiles = [];
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.startsWith('image/')) {
                        const file = items[i].getAsFile();
                        if (file) imageFiles.push(file);
                    }
                }
                if (imageFiles.length === 0) return false;
                event.preventDefault();
                imageFiles.forEach((file, idx) => {
                    uploadImage(file, uploadUrl, csrfToken)
                        .then(data => {
                            const url = data.url || data.path || data;
                            editor.chain().focus().setImage({ src: url }).run();
                        })
                        .catch(() => {
                            console.warn('Failed to upload pasted image');
                        });
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
                            const url = data.url || data.path || data;
                            const pos = view.posAtCoords({ left: event.clientX, top: event.clientY });
                            if (pos) {
                                editor.chain().focus().insertContentAt(pos.pos, { type: 'image', attrs: { src: url } }).run();
                            } else {
                                editor.chain().focus().setImage({ src: url }).run();
                            }
                        })
                        .catch(() => console.warn('Failed to upload dropped image'));
                });
                return true;
            },
        },
        onUpdate: ({ editor }) => {
            const html = editor.getHTML();
            if (onUpdate) onUpdate(html);
        },
    });

    return editor;
}

if (typeof window !== 'undefined') {
    window.initChapterEditor = initChapterEditor;
}

const registerAlpineTiptap = () => {
    if (window.__tiptapChapterRegistered || typeof Alpine === 'undefined') return;
    window.__tiptapChapterRegistered = true;
    Alpine.data('tiptapChapterEditor', () => ({
        editor: null,
        init() {
            const el = this.$refs.editorEl;
            if (!el) return;
            const initFn = window.initChapterEditor;
            if (!initFn) {
                const attempt = (this._attempt = (this._attempt || 0) + 1);
                if (attempt < 30) setTimeout(() => this.init(), 250);
                return;
            }
            const cfgB64 = this.$el.dataset.config;
            const opts = cfgB64 ? JSON.parse(atob(cfgB64)) : {};
            this.editor = initFn({
                element: el,
                content: opts.initialContent || '<p></p>',
                placeholder: opts.placeholder || 'Текст главы...',
                uploadUrl: opts.uploadUrl || '/api/upload/chapter-image',
                onUpdate: (html) => {
                    if (typeof $wire !== 'undefined') $wire.set(opts.model || 'content', html);
                }
            });
            window.__chapterEditor = this.editor;
        },
        insertImage(url) {
            if (!this.editor || !url) return;
            const src = typeof url === 'string' ? url : (url?.url || url?.path);
            if (!src) return;
            const html = '<p><img src="' + src.replace(/"/g, '&quot;') + '" alt="" class="max-w-full h-auto rounded-lg my-2" loading="lazy"></p>';
            this.editor.chain().focus().insertContent(html).run();
        }
    }));
};
document.addEventListener('alpine:init', registerAlpineTiptap);
if (typeof Alpine !== 'undefined') registerAlpineTiptap();
