(function (window, document) {
    'use strict';

    if (!window.hai_data || !window.hai_data.queue || !window.XMLHttpRequest) {
        return;
    }

    var config = window.hai_data.queue;
    var texts = window.hai_data.texts || {};
    var storageKey = config.storage_key || 'hooshina_ai_queue_v1';
    var sessionKey = config.session_key || String(Date.now());
    var maxAge = Number(config.max_age || 86400) * 1000;
    var pollIntervals = Array.isArray(config.poll_intervals) ? config.poll_intervals : [5, 10, 15, 30, 60];
    var tasks = loadTasks();
    var pollTimers = {};
    var queueRoot = null;
    var queueTouched = false;

    function now() { return Date.now(); }

    function uid() {
        return 'hai-' + now().toString(36) + '-' + Math.random().toString(36).slice(2, 9);
    }

    function parseBody(body) {
        if (typeof body === 'string') {
            return new URLSearchParams(body);
        }
        if (window.FormData && body instanceof FormData) {
            var params = new URLSearchParams();
            body.forEach(function (value, key) {
                if (typeof value === 'string') params.append(key, value);
            });
            return params;
        }
        return null;
    }

    function loadTasks() {
        try {
            var parsed = JSON.parse(window.localStorage.getItem(storageKey) || '[]');
            if (!Array.isArray(parsed)) return [];

            return parsed.filter(function (task) {
                return task && task.createdAt && now() - task.createdAt < maxAge;
            }).map(function (task) {
                if (task.session !== sessionKey && task.status === 'submitting' && !task.contentId) {
                    task.status = 'interrupted';
                    task.message = texts.queue_interrupted || 'The request was interrupted.';
                }
                return task;
            });
        } catch (error) {
            return [];
        }
    }

    function saveTasks() {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(tasks.slice(0, 30)));
        } catch (error) {
            // The queue remains usable for this page when storage is disabled.
        }
    }

    function getTask(id) {
        return tasks.find(function (task) { return task.id === id; });
    }

    function getTaskByContentId(contentId) {
        return tasks.find(function (task) { return String(task.contentId) === String(contentId); });
    }

    function upsertTask(task) {
        var index = tasks.findIndex(function (item) { return item.id === task.id; });
        if (index === -1) tasks.unshift(task);
        else tasks[index] = task;
        saveTasks();
        render();
        schedulePolls();
    }

    function updateTask(id, changes) {
        var task = getTask(id);
        if (!task) return null;
        Object.keys(changes).forEach(function (key) { task[key] = changes[key]; });
        task.updatedAt = now();
        upsertTask(task);
        return task;
    }

    function escapeHtml(value) {
        var node = document.createElement('div');
        node.textContent = value == null ? '' : String(value);
        return node.innerHTML;
    }

    function currentPostId() {
        if (config.post_id) return Number(config.post_id);
        var input = document.getElementById('post_ID');
        return input ? Number(input.value) : 0;
    }

    function snapshotBlockTarget() {
        var target = { postId: currentPostId(), path: [], blockName: null, clientId: null };
        try {
            if (!window.wp || !wp.data) return target;
            var editor = wp.data.select('core/block-editor');
            var clientId = editor && editor.getSelectedBlockClientId && editor.getSelectedBlockClientId();
            if (!clientId) return target;

            target.clientId = clientId;
            target.blockName = editor.getBlockName(clientId);
            var path = [];
            var cursor = clientId;
            while (cursor) {
                var rootId = editor.getBlockRootClientId(cursor) || '';
                path.unshift(editor.getBlockIndex(cursor, rootId));
                cursor = rootId;
            }
            target.path = path;
        } catch (error) {
            // Classic editor and non-editor admin pages have no block target.
        }
        return target;
    }

    function requestLabel(params) {
        var subject = params.get('subject') || '';
        if (subject.length > 75) subject = subject.slice(0, 72) + '…';
        if (subject) return subject;
        return params.get('type') === 'image' ? 'تولید تصویر' : 'تولید محتوا';
    }

    function statusText(task) {
        var map = {
            submitting: texts.queue_submitting || 'Sending request...',
            queued: texts.queue_queued || 'Waiting in queue',
            processing: texts.queue_processing || 'Generating...',
            done: texts.queue_done || 'Generation completed',
            applied: texts.queue_applied || 'Completed and placed in the editor',
            failed: task.message || texts.queue_failed || 'Generation failed',
            interrupted: task.message || texts.queue_interrupted || 'The request was interrupted.'
        };
        return map[task.status] || map.processing;
    }

    function iconFor(task) {
        if (task.status === 'done' || task.status === 'applied') return '✓';
        if (task.status === 'failed' || task.status === 'interrupted') return '!';
        if (task.type === 'image') return '▧';
        if (task.type === 'text-to-speech') return '♪';
        return 'AI';
    }

    function isBusy(task) {
        return ['submitting', 'queued', 'processing'].indexOf(task.status) !== -1;
    }

    function ensureUi() {
        if (queueRoot || !document.body) return;
        queueRoot = document.createElement('aside');
        queueRoot.className = 'hai-queue';
        queueRoot.setAttribute('aria-live', 'polite');
        queueRoot.innerHTML =
            '<button type="button" class="hai-queue__toggle" aria-expanded="false">' +
                '<span class="hai-queue__pulse"></span>' +
                '<span>' + escapeHtml(texts.queue_title || 'Hooshina activity') + '</span>' +
                '<span class="hai-queue__count">0</span>' +
            '</button>' +
            '<section class="hai-queue__panel" aria-label="' + escapeHtml(texts.queue_title || 'Hooshina activity') + '">' +
                '<header class="hai-queue__header"><span>' + escapeHtml(texts.queue_title || 'Hooshina activity') + '</span>' +
                '<button type="button" class="hai-queue__close" aria-label="Close">×</button></header>' +
                '<div class="hai-queue__warning" role="status">' + escapeHtml(texts.queue_keep_open || 'An activity is in progress. Do not close or refresh this page until it finishes.') + '</div>' +
                '<ul class="hai-queue__list"></ul>' +
            '</section>';
        document.body.appendChild(queueRoot);

        queueRoot.querySelector('.hai-queue__toggle').addEventListener('click', function () {
            queueTouched = true;
            var open = queueRoot.classList.toggle('hai-queue--open');
            this.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        queueRoot.querySelector('.hai-queue__close').addEventListener('click', function () {
            queueTouched = true;
            queueRoot.classList.remove('hai-queue--open');
            queueRoot.querySelector('.hai-queue__toggle').setAttribute('aria-expanded', 'false');
        });
        queueRoot.addEventListener('click', handleQueueAction);
    }

    function render() {
        ensureUi();
        if (!queueRoot) return;

        var visible = tasks.filter(function (task) { return !task.dismissed; });
        var busyCount = visible.filter(isBusy).length;
        queueRoot.classList.toggle('hai-queue--busy', busyCount > 0);
        queueRoot.querySelector('.hai-queue__count').textContent = String(busyCount || visible.length);
        queueRoot.querySelector('.hai-queue__toggle').setAttribute('aria-label', texts.queue_show || 'Show Hooshina activity');

        var list = queueRoot.querySelector('.hai-queue__list');
        if (!visible.length) {
            list.innerHTML = '<li class="hai-queue__empty">' + escapeHtml(texts.queue_empty || 'There is no active generation.') + '</li>';
            return;
        }

        list.innerHTML = visible.map(function (task) {
            var actions = '';
            if (task.status === 'done' && task.result) {
                actions += '<button type="button" class="hai-queue__action hai-queue__action--primary" data-hai-action="apply" data-task-id="' + escapeHtml(task.id) + '">' + escapeHtml(texts.queue_apply || 'Place in editor') + '</button>';
            }
            if ((task.status === 'failed' || task.status === 'interrupted') && task.contentId) {
                actions += '<button type="button" class="hai-queue__action" data-hai-action="retry" data-task-id="' + escapeHtml(task.id) + '">' + escapeHtml(texts.queue_retry_status || 'Check again') + '</button>';
            }
            if (!isBusy(task)) {
                actions += '<button type="button" class="hai-queue__action" data-hai-action="dismiss" data-task-id="' + escapeHtml(task.id) + '">' + escapeHtml(texts.queue_dismiss || 'Dismiss') + '</button>';
            }
            return '<li class="hai-queue__item hai-queue__item--' + escapeHtml(task.status) + '">' +
                '<div class="hai-queue__row"><span class="hai-queue__icon">' + escapeHtml(iconFor(task)) + '</span>' +
                '<div class="hai-queue__body"><div class="hai-queue__subject" title="' + escapeHtml(task.subject) + '">' + escapeHtml(task.subject) + '</div>' +
                '<div class="hai-queue__status">' + escapeHtml(statusText(task)) + '</div>' +
                (isBusy(task) ? '<div class="hai-queue__hint">' + escapeHtml(texts.queue_continue || 'You can continue editing; the result will be placed automatically.') + '</div>' : '') +
                (isBusy(task) ? '<div class="hai-queue__progress"></div>' : '') +
                (actions ? '<div class="hai-queue__actions">' + actions + '</div>' : '') +
                '</div></div></li>';
        }).join('');
    }

    function handleQueueAction(event) {
        var button = event.target.closest('[data-hai-action]');
        if (!button) return;
        var task = getTask(button.getAttribute('data-task-id'));
        if (!task) return;
        var action = button.getAttribute('data-hai-action');
        if (action === 'dismiss') {
            task.dismissed = true;
            upsertTask(task);
        } else if (action === 'retry') {
            task.status = 'queued';
            task.pollAttempt = 0;
            task.message = '';
            upsertTask(task);
            pollTask(task, true);
        } else if (action === 'apply' && task.result) {
            if (applyResult(task, task.result)) updateTask(task.id, { status: 'applied' });
        }
    }

    function minimizeGenerationDialog() {
        var heading = String(texts.modal_heading || 'Hooshina').toLowerCase();
        Array.prototype.forEach.call(document.querySelectorAll('.MuiModal-root, [role="dialog"]'), function (node) {
            if (String(node.textContent || '').toLowerCase().indexOf(heading) === -1) return;
            var modal = node.classList.contains('MuiModal-root') ? node : (node.closest('.MuiModal-root') || node);
            modal.classList.add('hai-generation-backgrounded');
            document.body.classList.remove('MuiModal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        });
    }

    function dismissGenerationDialog(attempt) {
        attempt = Number(attempt || 0);
        var heading = String(texts.modal_heading || 'Hooshina').toLowerCase();
        var modal = Array.prototype.find.call(document.querySelectorAll('.MuiModal-root'), function (node) {
            return String(node.textContent || '').toLowerCase().indexOf(heading) !== -1;
        });
        if (!modal) return;

        var closeIcon = modal.querySelector('[data-testid="CloseIcon"]');
        var closeButton = closeIcon && closeIcon.closest('button');
        if (closeButton) {
            modal.classList.remove('hai-generation-backgrounded');
            closeButton.click();
            return;
        }
        if (attempt < 5) {
            window.setTimeout(function () { dismissGenerationDialog(attempt + 1); }, 1200);
        }
    }

    function openQueueBriefly() {
        ensureUi();
        if (!queueRoot) return;
        var wasOpen = queueRoot.classList.contains('hai-queue--open');
        if (!wasOpen) queueTouched = false;
        queueRoot.classList.add('hai-queue--open');
        queueRoot.querySelector('.hai-queue__toggle').setAttribute('aria-expanded', 'true');
        window.setTimeout(function () {
            if (!queueTouched && queueRoot) {
                queueRoot.classList.remove('hai-queue--open');
                queueRoot.querySelector('.hai-queue__toggle').setAttribute('aria-expanded', 'false');
            }
        }, 4000);
    }

    function handleGenerationStart(xhr, params) {
        var task = {
            id: uid(),
            session: sessionKey,
            createdAt: now(),
            updatedAt: now(),
            status: 'submitting',
            type: params.get('type') || 'text',
            subject: requestLabel(params),
            buttonAction: params.get('buttonAction') || '',
            target: snapshotBlockTarget(),
            contentId: null,
            pollAttempt: 0,
            dismissed: false
        };
        xhr.__haiQueueTaskId = task.id;
        upsertTask(task);
        openQueueBriefly();
        // Close the React form while its async handler keeps the captured
        // destination and response callback alive. This also lets the user
        // start another independent generation without waiting for this one.
        dismissGenerationDialog(0);
        window.setTimeout(minimizeGenerationDialog, 250);
    }

    function responseData(xhr) {
        try {
            var payload = xhr.responseType === 'json' && xhr.response ? xhr.response : JSON.parse(xhr.responseText || '{}');
            return payload && payload.data ? { success: payload.success === true, data: payload.data } : { success: false, data: {} };
        } catch (error) {
            return { success: false, data: {} };
        }
    }

    function targetStillExists(task) {
        var action = task.buttonAction || '';
        if (['block-post-title', 'editor-post-title', 'post-seo-title', 'post-seo-description', 'post-seo-keyword', 'block-post-thumbnail', 'editor-post-thumbnail', 'editor-product-gallery', 'post-excerpt', 'comment-reply', 'product-review-reply', 'medias-page', 'product-reviews-summary'].indexOf(action) !== -1) {
            return true;
        }
        if (!task.target || !task.target.clientId) return true;
        try {
            return Boolean(window.wp && wp.data && wp.data.select('core/block-editor').getBlock(task.target.clientId));
        } catch (error) {
            return false;
        }
    }

    function handleGenerationResponse(xhr) {
        var task = getTask(xhr.__haiQueueTaskId);
        if (!task) return;
        var response = responseData(xhr);
        if (!response.success) {
            updateTask(task.id, { status: 'failed', message: response.data.msg || texts.generate_error || texts.queue_failed });
            return;
        }

        var data = response.data || {};
        if (data.status && data.status !== 'done' && data.content_id) {
            updateTask(task.id, {
                status: data.status === 'queued' ? 'queued' : 'processing',
                contentId: data.content_id,
                acceptedAt: now()
            });
            dismissGenerationDialog(0);
            return;
        }

        updateTask(task.id, {
            status: targetStillExists(task) ? 'applied' : 'done',
            contentId: data.content_id || null,
            result: { content: data.content || '', id: data.id || null }
        });
    }

    function handleBundleStatusResponse(xhr, params) {
        var task = getTaskByContentId(params.get('content_id'));
        if (!task) return;
        var response = responseData(xhr);
        if (!response.success) return;
        var data = response.data || {};
        if (data.status === 'done' && data.content) {
            // The existing editor promise receives this same response and
            // places it at its captured target immediately after this event.
            updateTask(task.id, {
                status: targetStillExists(task) ? 'applied' : 'done',
                bundleDoneSeen: true,
                result: { content: data.content, id: data.id || null }
            });
        } else if (data.status === 'failed' || data.status === 'error') {
            updateTask(task.id, { status: 'failed', message: texts.queue_failed });
        } else {
            updateTask(task.id, { status: 'processing' });
            window.setTimeout(function () { dismissGenerationDialog(0); }, 100);
        }
    }

    var NativeXhr = window.XMLHttpRequest;
    var nativeOpen = NativeXhr.prototype.open;
    var nativeSend = NativeXhr.prototype.send;

    NativeXhr.prototype.open = function (method, url) {
        this.__haiUrl = String(url || '');
        return nativeOpen.apply(this, arguments);
    };

    NativeXhr.prototype.send = function (body) {
        var params = this.__haiUrl.indexOf('admin-ajax.php') !== -1 ? parseBody(body) : null;
        if (params) {
            var action = params.get('action');
            if (action === 'hai_generate_content') handleGenerationStart(this, params);
            this.addEventListener('load', function () {
                if (action === 'hai_generate_content') handleGenerationResponse(this);
                if (action === 'hai_check_image_status' || action === 'hai_check_audio_status') {
                    handleBundleStatusResponse(this, params);
                }
            });
            this.addEventListener('error', function () {
                if (this.__haiQueueTaskId) updateTask(this.__haiQueueTaskId, { status: 'failed', message: texts.generate_error || texts.queue_failed });
            });
        }
        return nativeSend.apply(this, arguments);
    };

    function lockKey(task) { return storageKey + '_lock_' + task.contentId; }

    function acquirePollLock(task) {
        var key = lockKey(task);
        try {
            var lock = JSON.parse(window.localStorage.getItem(key) || '{}');
            if (lock.expires > now() && lock.session !== sessionKey) return false;
            window.localStorage.setItem(key, JSON.stringify({ session: sessionKey, expires: now() + 45000 }));
        } catch (error) {
            return true;
        }
        return true;
    }

    function releasePollLock(task) {
        try {
            var key = lockKey(task);
            var lock = JSON.parse(window.localStorage.getItem(key) || '{}');
            if (lock.session === sessionKey) window.localStorage.removeItem(key);
        } catch (error) {
            // Expiring locks are self-healing.
        }
    }

    function nextDelay(task) {
        var index = Math.min(Number(task.pollAttempt || 0), pollIntervals.length - 1);
        return Number(pollIntervals[index] || 30) * 1000;
    }

    function schedulePolls() {
        tasks.forEach(function (task) {
            if (!task.contentId || !isBusy(task) || pollTimers[task.id]) return;
            var initialDelay = task.session === sessionKey ? 15000 : 500;
            pollTimers[task.id] = window.setTimeout(function () {
                delete pollTimers[task.id];
                pollTask(task);
            }, initialDelay);
        });
    }

    function pollTask(task, force) {
        task = getTask(task.id);
        if (!task || !task.contentId || (!force && !isBusy(task))) return;
        if (!acquirePollLock(task)) {
            pollTimers[task.id] = window.setTimeout(function () {
                delete pollTimers[task.id];
                schedulePolls();
            }, 7000);
            return;
        }

        var body = new URLSearchParams();
        body.set('action', task.type === 'text-to-speech' ? 'hai_check_audio_status' : 'hai_check_image_status');
        body.set('nonce', window.hai_data.nonce);
        body.set('content_id', task.contentId);
        body.set('type', task.type);

        window.fetch(window.hai_data.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        }).then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        }).then(function (payload) {
            releasePollLock(task);
            var data = payload && payload.data ? payload.data : {};
            if (!payload.success) throw new Error(data.msg || 'Status unavailable');

            if (data.status === 'done' && data.content) {
                var result = { content: data.content, id: data.id || null };
                var latest = getTask(task.id);
                if (latest && latest.session === sessionKey && !latest.bundleDoneSeen) {
                    // Give the original editor poll one cycle to consume the
                    // cached final response. If it stopped, recover locally.
                    updateTask(task.id, { status: 'done', result: result });
                    window.setTimeout(function () {
                        var pendingApply = getTask(task.id);
                        if (pendingApply && pendingApply.status === 'done' && !pendingApply.bundleDoneSeen && applyResult(pendingApply, result)) {
                            updateTask(task.id, { status: 'applied' });
                        }
                    }, 3500);
                } else if (applyResult(task, result)) {
                    updateTask(task.id, { status: 'applied', result: result });
                } else {
                    updateTask(task.id, { status: 'done', result: result });
                }
                return;
            }
            if (data.status === 'failed' || data.status === 'error') {
                updateTask(task.id, { status: 'failed', message: texts.queue_failed });
                return;
            }

            task.pollAttempt = Number(task.pollAttempt || 0) + 1;
            updateTask(task.id, { status: 'processing', pollAttempt: task.pollAttempt });
        }).catch(function () {
            releasePollLock(task);
            task = getTask(task.id);
            if (!task) return;
            task.pollAttempt = Number(task.pollAttempt || 0) + 1;
            task.status = task.pollAttempt >= 8 ? 'failed' : 'queued';
            task.message = task.status === 'failed' ? (texts.queue_failed || 'Status unavailable') : '';
            upsertTask(task);
        });
    }

    function resolveBlockPath(path) {
        if (!window.wp || !wp.data || !Array.isArray(path) || !path.length) return null;
        var selector = wp.data.select('core/block-editor');
        var root = '';
        var block = null;
        for (var index = 0; index < path.length; index++) {
            var blocks = selector.getBlocks(root);
            block = blocks[path[index]] || null;
            if (!block) return null;
            root = block.clientId;
        }
        return block;
    }

    function applyResult(task, result) {
        if (!result || !result.content) return false;
        if (task.target && task.target.postId && currentPostId() && Number(task.target.postId) !== currentPostId()) return false;

        var action = task.buttonAction || '';
        var attachmentId = Number(result.id || 0);
        try {
            if ((action === 'block-post-thumbnail' || action === 'editor-post-thumbnail') && attachmentId) {
                if (window.wp && wp.data && wp.data.dispatch('core/editor')) {
                    wp.data.dispatch('core/editor').editPost({ featured_media: attachmentId });
                    return true;
                }
                if (typeof window.WPSetAsThumbnail === 'function') {
                    window.WPSetAsThumbnail(attachmentId);
                    return true;
                }
            }

            if (action === 'editor-product-gallery' && attachmentId) {
                var gallery = document.getElementById('product_image_gallery');
                if (gallery) {
                    var ids = (gallery.value || '').split(',').filter(Boolean);
                    if (ids.indexOf(String(attachmentId)) === -1) ids.push(String(attachmentId));
                    gallery.value = ids.join(',');
                    if (window.jQuery) window.jQuery(gallery).trigger('change');
                    return true;
                }
            }

            if (window.wp && wp.blocks && wp.data) {
                var dispatch = wp.data.dispatch('core/block-editor');
                var targetBlock = resolveBlockPath(task.target && task.target.path);
                var blockName = task.type === 'text-to-speech' ? 'core/audio' : 'core/image';
                var attributes = { url: result.content };
                if (attachmentId) attributes.id = attachmentId;

                if (targetBlock && targetBlock.name === blockName) {
                    dispatch.updateBlockAttributes(targetBlock.clientId, attributes);
                    return true;
                }

                var newBlock = wp.blocks.createBlock(blockName, attributes);
                var insertIndex = task.target && task.target.path && task.target.path.length ? task.target.path[0] : undefined;
                dispatch.insertBlock(newBlock, insertIndex);
                return true;
            }
        } catch (error) {
            return false;
        }
        return false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        render();
        schedulePolls();
    });

    window.addEventListener('storage', function (event) {
        if (event.key !== storageKey) return;
        tasks = loadTasks();
        render();
        schedulePolls();
    });

    window.addEventListener('beforeunload', function (event) {
        if (!tasks.some(isBusy)) return;
        event.preventDefault();
        event.returnValue = '';
    });

    if (document.readyState !== 'loading') {
        render();
        schedulePolls();
    }
})(window, document);
