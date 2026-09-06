const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const storage = new Map();
const windowListeners = {};
let closeClicks = 0;

const fakeModal = {
    textContent: 'Hooshina Ai',
    classList: {
        contains(name) { return name === 'MuiModal-root'; },
        add() {},
        remove() {}
    },
    querySelector(selector) {
        if (selector !== '[data-testid="CloseIcon"]') return null;
        return { closest() { return { click() { closeClicks += 1; } }; } };
    }
};

class FakeXhr {
    constructor() {
        this.listeners = {};
        this.responseType = '';
        this.responseText = '';
    }

    open(method, url) {
        this.method = method;
        this.url = url;
    }

    addEventListener(name, callback) {
        this.listeners[name] = callback;
    }

    send() {
        this.responseText = JSON.stringify(FakeXhr.nextResponse);
        if (this.listeners.load) this.listeners.load.call(this);
    }
}

const document = {
    body: null,
    readyState: 'loading',
    addEventListener() {},
    getElementById() { return null; },
    querySelectorAll(selector) { return selector.indexOf('.MuiModal-root') !== -1 ? [fakeModal] : []; },
    createElement() {
        return {
            _text: '',
            set textContent(value) { this._text = String(value); },
            get innerHTML() { return this._text; }
        };
    }
};

const window = {
    hai_data: {
        ajax_url: 'https://wordpress.test/wp-admin/admin-ajax.php',
        nonce: 'test-nonce',
        queue: {
            storage_key: 'queue-test',
            session_key: 'session-a',
            post_id: 22,
            max_age: 86400,
            poll_intervals: [5, 10]
        },
        texts: { modal_heading: 'Hooshina Ai' }
    },
    XMLHttpRequest: FakeXhr,
    FormData: class FormData {},
    URLSearchParams,
    localStorage: {
        getItem(key) { return storage.has(key) ? storage.get(key) : null; },
        setItem(key, value) { storage.set(key, value); },
        removeItem(key) { storage.delete(key); }
    },
    setTimeout() { return 1; },
    clearTimeout() {},
    addEventListener(name, callback) { windowListeners[name] = callback; },
    fetch() { throw new Error('Polling is not expected in this contract test.'); }
};

const context = vm.createContext({
    window,
    document,
    URLSearchParams,
    FormData: window.FormData,
    console,
    Date,
    Math,
    Array,
    JSON,
    Number,
    String
});

const source = fs.readFileSync(__dirname + '/../assets/js/queue.js', 'utf8');
vm.runInContext(source, context, { filename: 'queue.js' });

FakeXhr.nextResponse = {
    success: true,
    data: { status: 'queued', content_id: 913, content: '__hai_pending__' }
};

const queuedRequest = new window.XMLHttpRequest();
queuedRequest.open('POST', window.hai_data.ajax_url);
queuedRequest.send('action=hai_generate_content&type=image&buttonAction=block-post-thumbnail&subject=Queue+test');

let tasks = JSON.parse(storage.get('queue-test'));
assert.ok(closeClicks >= 1, 'The generation modal should close as soon as the request enters the activity queue.');
assert.equal(tasks.length, 1);
assert.equal(tasks[0].status, 'queued');
assert.equal(tasks[0].contentId, 913);
assert.equal(tasks[0].buttonAction, 'block-post-thumbnail');
assert.equal(tasks[0].target.postId, 22);
const unloadEvent = { prevented: false, returnValue: null, preventDefault() { this.prevented = true; } };
windowListeners.beforeunload(unloadEvent);
assert.equal(unloadEvent.prevented, true, 'An active generation should protect the page from accidental closing.');
assert.equal(unloadEvent.returnValue, '', 'The browser should display its native leave-page warning.');

FakeXhr.nextResponse = {
    success: true,
    data: { status: 'done', content_id: 914, content: '<p>Generated</p>' }
};

const textRequest = new window.XMLHttpRequest();
textRequest.open('POST', window.hai_data.ajax_url);
textRequest.send('action=hai_generate_content&type=text&buttonAction=block-post-title&subject=Text+test');

tasks = JSON.parse(storage.get('queue-test'));
assert.equal(tasks.length, 2);
assert.equal(tasks[0].type, 'text');
assert.equal(tasks[0].status, 'applied');
assert.equal(tasks[1].contentId, 913);

console.log('Activity queue contract tests passed.');
