/**
 * Forum hero & interactions.
 *
 * 1) Polling /forum/api/feed каждые N мс — обновляет 3 списка (новинки / топ / главы)
 *    с плавными анимациями появления и без перерисовки DOM целиком (diff по id).
 * 2) Подписка на тему, реакции, цитирование, репорты, инлайн-редактирование постов.
 *
 * Без зависимостей. CSRF берётся из <meta name="csrf-token">. Совместимо с Livewire
 * (поллинг не сбивается на livewire:navigated — переинициализируется).
 */
(function () {
    'use strict';

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ---------- HERO POLLING ----------

    let heroTimer = null;
    let heroLast  = '';   // checksum последнего фида, чтобы не дёргать DOM зря

    function initHero() {
        const root = document.querySelector('[data-forum-hero]');
        if (!root) return;
        const url      = root.dataset.feedUrl;
        const interval = Math.max(8000, parseInt(root.dataset.pollInterval || '20000', 10));
        const status   = root.querySelector('[data-hero-status]');

        function setStatus(text, ok = true) {
            if (!status) return;
            status.textContent = text;
            status.parentElement.style.opacity = ok ? '1' : '.65';
        }

        async function tick() {
            try {
                const r = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!r.ok) throw new Error('feed http ' + r.status);
                const data = await r.json();
                const csum = data.updated_at + '|' +
                    (data.new_novels || []).map(x => x.id).join(',') + '|' +
                    (data.top_novels || []).map(x => x.id).join(',') + '|' +
                    (data.latest_chapters || []).map(x => x.id).join(',');
                if (csum !== heroLast) {
                    heroLast = csum;
                    applyHero(root, 'new_novels',      data.new_novels      || []);
                    applyHero(root, 'top_novels',      data.top_novels      || []);
                    applyHero(root, 'latest_chapters', data.latest_chapters || []);
                }
                setStatus('обновлено: ' + relTime(data.updated_at), true);
            } catch (e) {
                setStatus('нет связи — повтор…', false);
            }
        }

        if (heroTimer) clearInterval(heroTimer);
        // не дёргаем сервер сразу — initial-snapshot уже на странице
        heroTimer = setInterval(tick, interval);
        // если вкладка ушла в фон — приостанавливаем
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (heroTimer) { clearInterval(heroTimer); heroTimer = null; }
            } else if (!heroTimer) {
                heroTimer = setInterval(tick, interval);
                tick();
            }
        });
    }

    function applyHero(root, key, items) {
        const list = root.querySelector(`[data-hero-list="${key}"]`);
        if (!list) return;

        const existing = new Map();
        list.querySelectorAll('[data-id]').forEach(li => existing.set(String(li.dataset.id), li));

        const next = document.createDocumentFragment();
        items.forEach((item, idx) => {
            const id = String(item.id);
            let li = existing.get(id);
            if (li) {
                existing.delete(id);
                next.appendChild(li);                                  // переставим в новом порядке
            } else {
                li = buildItem(key, item, idx);
                if (li) {
                    li.classList.add('is-new');                        // анимация появления
                    next.appendChild(li);
                    // снять класс is-new после окончания анимации
                    setTimeout(() => li.classList.remove('is-new'), 700);
                }
            }
        });

        // Удалим элементы, которых больше нет в фиде
        existing.forEach(li => {
            li.style.transition = 'opacity .25s, transform .25s';
            li.style.opacity = '0';
            li.style.transform = 'translateY(-4px)';
            setTimeout(() => li.remove(), 250);
        });

        // Заменим содержимое; placeholder DOM в next == финальный порядок
        list.appendChild(next);

        // Обновим номера рангов для top_novels
        if (key === 'top_novels') {
            [...list.children].forEach((li, i) => {
                const r = li.querySelector('.forum-hero__rank');
                if (r) r.textContent = String(i + 1);
            });
        }
    }

    function buildItem(key, item, idx) {
        const li = document.createElement('li');
        li.className = 'forum-hero__item';
        li.dataset.id = String(item.id);

        const a = document.createElement('a');
        a.className = 'forum-hero__item-inner';
        a.href = item.url || '#';

        if (key === 'top_novels') {
            const rank = document.createElement('span');
            rank.className = 'forum-hero__rank';
            rank.textContent = String(idx + 1);
            a.appendChild(rank);

            const meta = document.createElement('span');
            meta.className = 'forum-hero__meta';
            meta.innerHTML = `
                <span class="forum-hero__name">${escapeHtml(item.title || '')}</span>
                ${item.rating ? `<span class="forum-hero__sub"><i class="fa-solid fa-star"></i> ${Number(item.rating).toFixed(1)}</span>` : ''}
            `;
            a.appendChild(meta);
        } else {
            const cover = document.createElement('span');
            cover.className = 'forum-hero__cover' + (key === 'latest_chapters' ? ' forum-hero__cover--sm' : '');
            if (item.cover) {
                const img = new Image();
                img.src = item.cover; img.loading = 'lazy'; img.alt = '';
                cover.appendChild(img);
            } else {
                const fb = document.createElement('span');
                fb.className = 'forum-hero__cover-fallback';
                fb.innerHTML = '<i class="fa-solid fa-book"></i>';
                cover.appendChild(fb);
            }
            a.appendChild(cover);

            const meta = document.createElement('span');
            meta.className = 'forum-hero__meta';
            const sub = key === 'latest_chapters'
                ? (item.novel_title || '')
                : (item.author || '');
            meta.innerHTML = `
                <span class="forum-hero__name">${escapeHtml(item.title || '')}</span>
                ${sub ? `<span class="forum-hero__sub">${escapeHtml(sub)}</span>` : ''}
            `;
            a.appendChild(meta);
        }

        li.appendChild(a);
        return li;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }
    function relTime(iso) {
        if (!iso) return '';
        const d = new Date(iso); if (isNaN(d)) return '';
        const sec = Math.max(0, Math.round((Date.now() - d.getTime()) / 1000));
        if (sec < 30) return 'только что';
        if (sec < 60) return sec + ' сек назад';
        const min = Math.floor(sec / 60);
        if (min < 60) return min + ' мин назад';
        const h = Math.floor(min / 60);
        return h + ' ч назад';
    }

    // ---------- THREAD: subscribe / react / quote / report ----------

    async function postJson(url, body = {}) {
        const r = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    }

    function initSubscribeButtons() {
        document.querySelectorAll('[data-forum-subscribe]').forEach(btn => {
            if (btn.__bound) return; btn.__bound = true;
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                try {
                    const data = await postJson(btn.dataset.url);
                    btn.dataset.active = data.subscribed ? '1' : '0';
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-solid', !!data.subscribed);
                        icon.classList.toggle('fa-regular', !data.subscribed);
                    }
                    btn.title = data.subscribed ? 'Отписаться' : 'Подписаться';
                } catch (e) { console.warn(e); }
                finally { btn.disabled = false; }
            });
        });
    }

    function initReactionButtons() {
        document.querySelectorAll('.forum-react').forEach(btn => {
            if (btn.__bound) return; btn.__bound = true;
            btn.addEventListener('click', async () => {
                if (btn.disabled) return;
                const url = btn.dataset.url;
                const emoji = btn.dataset.emoji;
                if (!url || !emoji) return;
                btn.disabled = true;
                try {
                    const data = await postJson(url, { emoji });
                    // обновим контейнер реакций
                    const wrap = btn.parentElement;
                    wrap.querySelectorAll('.forum-react').forEach(b => {
                        const e = b.dataset.emoji;
                        const c = data.breakdown?.[e] ?? 0;
                        const countEl = b.querySelector('.forum-react__count');
                        if (countEl) countEl.textContent = c > 0 ? c : '';
                        b.classList.toggle('is-empty', c === 0);
                    });
                    // is-on только на нажатой
                    btn.classList.toggle('is-on', !!data.added);
                } catch (e) { console.warn(e); }
                finally { btn.disabled = false; }
            });
        });
    }

    function initQuote() {
        document.querySelectorAll('[data-forum-quote]').forEach(btn => {
            if (btn.__bound) return; btn.__bound = true;
            btn.addEventListener('click', () => {
                const postId = btn.dataset.forumQuote;
                const form = document.querySelector('[data-forum-reply]');
                if (!form) return;
                form.querySelector('[data-forum-parent]').value = postId;
                const author = btn.closest('.forum-post')?.querySelector('.forum-post__author a')?.textContent?.trim() ?? '';
                const banner = form.querySelector('[data-forum-quote-banner]');
                if (banner) {
                    banner.hidden = false;
                    banner.querySelector('[data-forum-quote-author]').textContent = author;
                }
                const body = form.querySelector('[data-forum-body]');
                if (body) {
                    body.focus();
                    body.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
        document.querySelectorAll('[data-forum-quote-clear]').forEach(b => {
            if (b.__bound) return; b.__bound = true;
            b.addEventListener('click', () => {
                const form = b.closest('[data-forum-reply]');
                if (!form) return;
                form.querySelector('[data-forum-parent]').value = '';
                form.querySelector('[data-forum-quote-banner]').hidden = true;
            });
        });
        document.querySelectorAll('[data-forum-body]').forEach(ta => {
            if (ta.__bound) return; ta.__bound = true;
            ta.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    ta.closest('form')?.submit();
                }
            });
        });
    }

    function initReport() {
        document.querySelectorAll('[data-forum-report-post]').forEach(btn => {
            if (btn.__bound) return; btn.__bound = true;
            btn.addEventListener('click', async () => {
                const reason = prompt('Причина (spam, abuse, offtopic, nsfw, other):', 'spam');
                if (!reason) return;
                const comment = prompt('Комментарий (необязательно):', '') || null;
                try {
                    await postJson(btn.dataset.url, { reason, comment });
                    btn.textContent = 'Жалоба отправлена';
                    btn.disabled = true;
                } catch (e) { console.warn(e); alert('Не удалось отправить жалобу'); }
            });
        });
    }

    function initEdit() {
        document.querySelectorAll('[data-forum-edit-post]').forEach(btn => {
            if (btn.__bound) return; btn.__bound = true;
            btn.addEventListener('click', () => {
                const postEl = btn.closest('.forum-post');
                if (!postEl) return;
                const body = postEl.querySelector('[data-post-body]');
                if (!body || body.dataset.editing === '1') return;

                body.dataset.editing = '1';
                const original = body.innerHTML;
                const source = btn.dataset.source || body.innerText;
                const id = postEl.id.replace('post-', '');

                const ta = document.createElement('textarea');
                ta.className = 'forum-textarea'; ta.value = source;
                const actions = document.createElement('div');
                actions.className = 'forum-reply__actions';
                actions.innerHTML = `
                    <button type="button" class="forum-btn forum-btn--ghost">Отмена</button>
                    <button type="button" class="forum-btn forum-btn--primary">Сохранить</button>`;

                body.replaceChildren(ta, actions);

                actions.children[0].onclick = () => {
                    body.innerHTML = original;
                    body.dataset.editing = '0';
                };
                actions.children[1].onclick = async () => {
                    try {
                        const res = await fetch('/forum/posts/' + id, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': csrfToken(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: '_method=PUT&body=' + encodeURIComponent(ta.value),
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const data = await res.json();
                        body.innerHTML = data.body_html || ta.value;
                        body.dataset.editing = '0';
                    } catch (e) {
                        console.warn(e); alert('Не удалось сохранить.');
                    }
                };
            });
        });
    }

    function initAll() {
        initHero();
        initSubscribeButtons();
        initReactionButtons();
        initQuote();
        initReport();
        initEdit();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
    // Совместимость с Livewire-навигацией
    document.addEventListener('livewire:navigated', initAll);
})();
