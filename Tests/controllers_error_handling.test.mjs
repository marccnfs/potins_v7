import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import { fileURLToPath, pathToFileURL } from 'node:url';
import path from 'node:path';

async function loadController(relPath) {
    const abs = path.resolve(fileURLToPath(new URL(relPath, import.meta.url)));
    let src = fs.readFileSync(abs, 'utf8');
    src = src.replace(/import\s+\{\s*Controller\s*\}\s+from\s+"@hotwired\/stimulus";?/, 'class Controller {}');
    const dataUrl = 'data:text/javascript;base64,' + Buffer.from(src).toString('base64');
    return (await import(dataUrl)).default;
}

const AgendaCalendarController = await loadController('../assets/controllers/agenda_calendar_controller.js');
const BoardWeekController = await loadController('../assets/controllers/board_week_controller.js');

// minimal DOM & console stubs
global.window = { location: { origin: 'http://example.test' } };
global.document = { querySelector: () => null };
const originalFetch = global.fetch;
const originalError = console.error;
console.error = () => {};

test('AgendaCalendarController handles fetch errors gracefully', async () => {
    global.fetch = async () => { throw new Error('network'); };
    const ctrl = new AgendaCalendarController({ element: {}, identifier: 'agenda', application: { logger: console } });
    ctrl.viewValue = 'week';
    ctrl.dateValue = '2024-01-01';
    ctrl.fetchUrlValue = '/events';
    ctrl.gridTarget = { innerHTML: '' };

    await ctrl.load();
    assert.match(ctrl.gridTarget.innerHTML, /Erreur/);
});

test('BoardWeekController handles fetch errors gracefully', async () => {
    global.fetch = async () => { throw new Error('network'); };
    const ctrl = new BoardWeekController({ element: {}, identifier: 'board-week', application: { logger: console } });
    ctrl.dateValue = '2024-01-01';
    ctrl.fetchUrlValue = '/events';
    ctrl.gridTarget = { innerHTML: '' };
    ctrl.hasHeadlineTarget = false;

    await ctrl.load();
    assert.match(ctrl.gridTarget.innerHTML, /Erreur/);
});

test.after(() => {
    global.fetch = originalFetch;
    console.error = originalError;
});
