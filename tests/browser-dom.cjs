const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { JSDOM } = require('jsdom');
const jquery = require('jquery');

(async () => {
    const rows = id => `<div id="${id}" class="soft79_bulk_rules"><p class="soft79_bulk_row"><input name="rule"><a class="soft79_wcpr_delete_row" href="#">X</a></p><p class="soft79_wcpr_add_rule"><a href="#">Add row</a></p><div class="soft79_wcpr_new_row" style="display:none"><p class="soft79_bulk_row"><input name="rule"><a class="soft79_wcpr_delete_row" href="#">X</a></p></div></div>`;
    const product = id => `<div id="${id}" class="product"><div class="soft79-single-product-discount-wrap">original-${id}</div><form class="cart variations_form"></form></div>`;
    const dom = new JSDOM(`<form>${rows('a')}${rows('b')}</form>${product('p1')}${product('p2')}`, { runScripts: 'outside-only' });
    const $ = jquery(dom.window);
    dom.window.jQuery = $;
    dom.window.$ = $;
    for (const file of ['assets/js/admin/admin.js', 'assets/js/frontend/single-product-summary.js']) {
        dom.window.eval(fs.readFileSync(path.join(__dirname, '..', file), 'utf8'));
    }
    await new Promise(resolve => $(resolve));
    let changes = 0;
    $('#a').on('change', 'input', () => changes++);
    $('#a .soft79_wcpr_add_rule a').trigger('click');
    assert.equal($('#a > .soft79_bulk_row').length, 2, 'Add affects selected variation');
    assert.equal($('#b > .soft79_bulk_row').length, 1, 'Other variation stays unchanged');
    assert.equal(changes, 1, 'Add marks variation dirty');
    $('#a > .soft79_bulk_row .soft79_wcpr_delete_row').first().trigger('click');
    assert.equal($('#a > .soft79_bulk_row').length, 1, 'Remove affects selected row');
    assert.equal(changes, 2, 'Remove marks variation dirty');
    $('#p1 form').trigger('found_variation', [{ j79_soft79_single_product_discount: '<b>discount</b>' }]);
    assert.equal($('#p1 .soft79-single-product-discount-wrap').html(), '<b>discount</b>', 'Variation table updates');
    assert.equal($('#p2 .soft79-single-product-discount-wrap').html(), 'original-p2', 'Other product is unaffected');
    $('#p1 form').trigger('found_variation', [{ j79_soft79_single_product_discount: '' }]);
    assert.equal($('#p1 .soft79-single-product-discount-wrap').html(), '', 'Excluded variation clears table');
    $('#p1 form').trigger('reset_data');
    assert.equal($('#p1 .soft79-single-product-discount-wrap').html(), 'original-p1', 'Reset restores parent table');
    $('#p1 form').trigger('found_variation', [{}]);
    assert.equal($('#p1 .soft79-single-product-discount-wrap').html(), '', 'Missing payload clears stale table');
    console.log('PASS 10 DOM assertions');
    dom.window.close();
})().catch(error => { console.error(error); process.exitCode = 1; });
