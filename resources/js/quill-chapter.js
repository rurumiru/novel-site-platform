
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import MarkdownIt from 'markdown-it';

const DEFAULT_UPLOAD_URL = '/api/upload/chapter-image';

const mdRenderer = new MarkdownIt({ html: false, linkify: true, breaks: false, typographer: false });

function looksLikeMarkdown(text) {
    if (!text || text.length < 3) return false;
    return /(^|\n)(#{1,6}\s|>\s|[-*+]\s|\d+\.\s|```|---\s*$)/m.test(text)
        || /\*\*[^\s*][^*]+\*\*/.test(text)
        || /\[[^\]]+\]\([^)]+\)/.test(text);
}

const Inline = Quill.import('blots/inline');
class HookBlot extends Inline {
    static create(value) {
        const node = super.create();
        node.setAttribute('data-hook', '1');
        node.setAttribute('data-hook-body', value || '');
        return node;
    }
    static formats(node) {
        return node.getAttribute('data-hook-body') || '';
    }
    format(name, value) {
        if (name === HookBlot.blotName && value) {
            this.domNode.setAttribute('data-hook-body', value);
        } else {
            super.format(name, value);
        }
    }
}
HookBlot.blotName = 'hook';
HookBlot.tagName = 'span';
HookBlot.className = 'hook-mark';
Quill.register(HookBlot, true);

async function uploadImageFile(file, uploadUrl) {
    const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
    const fd = new FormData();
    fd.append('image', file);
    fd.append('_token', csrf);

    const r = await fetch(uploadUrl, {
        method: 'POST',
        body: fd,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    const data = await r.json();
    return (data && (data.url || data.path)) || null;
}

function makeToolbarSticky(wrapper) {
    const toolbar = wrapper.querySelector('.ql-toolbar');
    if (!toolbar) return () => {};

    let placeholder = null;
    let isFixed = false;
    let rafScheduled = false;

    const findStickyHeader = () => {
        let current = wrapper.parentElement;
        while (current && current !== document.body) {
            let sib = current.firstElementChild;
            while (sib && sib !== wrapper && !sib.contains(wrapper)) {
                if (getComputedStyle(sib).position === 'sticky') return sib;
                const inner = sib.querySelector('*');
                if (inner) {
                    const stickyInner = Array.from(sib.querySelectorAll('*'))
                        .find(el => getComputedStyle(el).position === 'sticky');
                    if (stickyInner) return stickyInner;
                }
                sib = sib.nextElementSibling;
            }
            current = current.parentElement;
        }
        return null;
    };
    let cachedHeader = null;
    let headerSearchAttempted = false;
    const getOffset = () => {
        if (!headerSearchAttempted || (cachedHeader && !cachedHeader.isConnected)) {
            cachedHeader = findStickyHeader();
            headerSearchAttempted = true;
            if (cachedHeader) {
                console.info('[quill-chapter] sticky header detected:', cachedHeader);
            } else {
                console.info('[quill-chapter] sticky header not found, using fallback offset');
            }
        }
        if (cachedHeader) {
            const r = cachedHeader.getBoundingClientRect();
            if (r.top < 200 && r.bottom > 0) {
                return Math.floor(r.bottom) - 1;
            }
        }
        return window.matchMedia('(min-width: 768px)').matches ? 116 : 124;
    };

    const apply = () => {
        rafScheduled = false;
        const offset = getOffset();
        const wrapRect = wrapper.getBoundingClientRect();
        const toolbarH = isFixed && placeholder ? placeholder.offsetHeight : toolbar.offsetHeight;

        const shouldFix = wrapRect.top < offset && wrapRect.bottom > offset + toolbarH + 40;

        if (shouldFix) {
            if (!isFixed) {
                const h = toolbar.offsetHeight;
                placeholder = document.createElement('div');
                placeholder.style.height = h + 'px';
                placeholder.style.flexShrink = '0';
                placeholder.setAttribute('data-quill-toolbar-placeholder', '');
                toolbar.parentNode.insertBefore(placeholder, toolbar);
                toolbar.style.position = 'fixed';
                toolbar.style.zIndex = '45';
                toolbar.style.boxShadow = '0 2px 8px rgb(0 0 0 / 0.08)';
                isFixed = true;
            }
            toolbar.style.top = offset + 'px';
            toolbar.style.left = wrapRect.left + 'px';
            toolbar.style.width = wrapRect.width + 'px';
        } else if (isFixed) {
            toolbar.style.position = '';
            toolbar.style.top = '';
            toolbar.style.left = '';
            toolbar.style.width = '';
            toolbar.style.zIndex = '';
            toolbar.style.boxShadow = '';
            if (placeholder) {
                placeholder.remove();
                placeholder = null;
            }
            isFixed = false;
        }
    };

    const schedule = () => {
        if (rafScheduled) return;
        rafScheduled = true;
        requestAnimationFrame(apply);
    };

    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule, { passive: true });
    schedule();

    return () => {
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);
        if (placeholder) {
            placeholder.remove();
            placeholder = null;
        }
        if (isFixed) {
            toolbar.style.cssText = '';
            isFixed = false;
        }
    };
}

function insertImageAtCursor(quill, url) {
    if (!quill || !url) return;
    let range = quill.getSelection(true);
    if (!range) {
        const len = quill.getLength();
        range = { index: Math.max(0, len - 1), length: 0 };
    }
    quill.insertEmbed(range.index, 'image', url, 'user');
    quill.insertText(range.index + 1, '\n', 'user');
    quill.setSelection(range.index + 2, 0, 'silent');
}

export function initChapterQuillEditor(options) {
    const {
        element,
        content = '',
        placeholder = 'Текст главы...',
        uploadUrl = DEFAULT_UPLOAD_URL,
        onUpdate,
    } = options;

    const el = typeof element === 'string' ? document.querySelector(element) : element;
    if (!el) return null;

    while (el.firstChild) el.removeChild(el.firstChild);

    const quill = new Quill(el, {
        theme: 'snow',
        placeholder,
        modules: {
            toolbar: {
                container: [
                    ['bold', 'italic', 'strike'],
                    [{ header: 2 }, { header: 3 }],
                    [{ list: 'ordered' }, { list: 'bullet' }, 'blockquote'],
                    ['image', 'hook', 'clean'],
                    ['undo', 'redo'],
                ],
                handlers: {
                    hook: function () {
                        const q = this.quill;
                        const range = q.getSelection(true);
                        if (!range || range.length === 0) {
                            alert('Выделите фрагмент, к которому хотите прикрепить крючок.');
                            return;
                        }
                        const fmt = q.getFormat(range);
                        const existingBody = typeof fmt.hook === 'string' ? fmt.hook : '';
                        const selectedText = q.getText(range.index, range.length).trim();
                        window.dispatchEvent(new CustomEvent('chapter-hook-open', {
                            detail: {
                                index: range.index,
                                length: range.length,
                                body: existingBody,
                                text: selectedText,
                            },
                        }));
                    },
                    image: function () {
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/*';
                        input.multiple = true;
                        input.onchange = async () => {
                            const files = Array.from(input.files || []);
                            let failed = 0;
                            for (const f of files) {
                                try {
                                    const url = await uploadImageFile(f, uploadUrl);
                                    if (url) insertImageAtCursor(quill, url);
                                } catch (e) {
                                    failed++;
                                    console.warn('Upload failed:', e);
                                }
                            }
                            if (failed > 0) {
                                alert('Не удалось загрузить ' + failed + ' из ' + files.length + ' изображений.');
                            }
                        };
                        input.click();
                    },
                    undo: function () { this.quill.history.undo(); },
                    redo: function () { this.quill.history.redo(); },
                },
            },
            clipboard: {
                matchVisual: false,
            },
            history: {
                delay: 1000,
                maxStack: 200,
                userOnly: true,
            },
        },
    });

    if (content) {
        try {
            const delta = quill.clipboard.convert({ html: content });
            quill.setContents(delta, 'silent');
        } catch (e) {
            console.warn('quill: clipboard.convert failed, falling back to innerHTML', e);
            quill.root.innerHTML = content;
        }
    }

    quill.root.addEventListener('paste', async (e) => {
        const items = e.clipboardData?.items;
        if (items) {
            const files = [];
            for (const it of items) {
                if (it.type && it.type.startsWith('image/')) {
                    const f = it.getAsFile();
                    if (f) files.push(f);
                }
            }
            if (files.length) {
                e.preventDefault();
                e.stopImmediatePropagation();
                for (const f of files) {
                    try {
                        const url = await uploadImageFile(f, uploadUrl);
                        if (url) insertImageAtCursor(quill, url);
                    } catch (err) {
                        console.warn('Paste upload failed:', err);
                    }
                }
                return;
            }
        }

        const plain = e.clipboardData?.getData('text/plain') || '';
        if (!plain || !looksLikeMarkdown(plain)) return;

        e.preventDefault();
        e.stopImmediatePropagation();
        try {
            const renderedHtml = mdRenderer.render(plain);
            const delta = quill.clipboard.convert({ html: renderedHtml });
            const range = quill.getSelection(true) || { index: Math.max(0, quill.getLength() - 1), length: 0 };
            const Delta = Quill.import('delta');
            const changeDelta = new Delta().retain(range.index).delete(range.length).concat(delta);
            quill.updateContents(changeDelta, 'user');
            quill.setSelection(range.index + delta.length(), 0, 'user');
        } catch (err) {
            console.warn('Markdown paste failed, falling back to default paste', err);
            const range = quill.getSelection(true) || { index: Math.max(0, quill.getLength() - 1), length: 0 };
            quill.deleteText(range.index, range.length, 'user');
            quill.insertText(range.index, plain, 'user');
            quill.setSelection(range.index + plain.length, 0, 'user');
        }
    }, { capture: true });

    quill.root.addEventListener('drop', async (e) => {
        const files = Array.from(e.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'));
        if (!files.length) return;
        e.preventDefault();
        e.stopPropagation();
        for (const f of files) {
            try {
                const url = await uploadImageFile(f, uploadUrl);
                if (url) insertImageAtCursor(quill, url);
            } catch (err) {
                console.warn('Drop upload failed:', err);
            }
        }
    });

    quill.on('text-change', (_delta, _oldDelta, source) => {
        if (source === 'silent') return;
        if (onUpdate) onUpdate(quill.root.innerHTML);
    });

    const imageInsertHandler = (evt) => {
        const url = evt?.detail?.url ?? evt?.detail;
        if (typeof url === 'string' && url) {
            insertImageAtCursor(quill, url);
        }
    };
    window.addEventListener('chapter-image-insert', imageInsertHandler);

    const hookApplyHandler = (evt) => {
        const d = evt?.detail || {};
        if (typeof d.index !== 'number' || typeof d.length !== 'number' || d.length <= 0) return;
        const body = (d.body || '').trim();
        if (body) {
            quill.formatText(d.index, d.length, 'hook', body, 'user');
        } else {
            quill.formatText(d.index, d.length, 'hook', false, 'user');
        }
        quill.setSelection(d.index + d.length, 0, 'silent');
    };
    window.addEventListener('chapter-hook-apply', hookApplyHandler);

    const wrapper = el.closest('.quill-chapter-wrapper') || el.parentElement;
    const stickyCleanup = wrapper ? makeToolbarSticky(wrapper) : () => {};

    window.__chapterQuillEditor = quill;
    quill.__cleanup = () => {
        try { stickyCleanup(); } catch (e) {}
        window.removeEventListener('chapter-image-insert', imageInsertHandler);
        window.removeEventListener('chapter-hook-apply', hookApplyHandler);
        if (window.__chapterQuillEditor === quill) {
            window.__chapterQuillEditor = null;
        }
    };

    return quill;
}

if (typeof window !== 'undefined') {
    window.initChapterQuillEditor = initChapterQuillEditor;

    if (!window.__quillChapterNavCleanupBound) {
        window.__quillChapterNavCleanupBound = true;
        document.addEventListener('livewire:navigating', () => {
            const ed = window.__chapterQuillEditor;
            if (ed && typeof ed.__cleanup === 'function') {
                try { ed.__cleanup(); } catch (e) {}
            }
            window.__chapterQuillEditor = null;
        });
    }
}
