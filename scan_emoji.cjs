const fs = require('fs');
const path = require('path');
const emojiRe = /\p{Extended_Pictographic}/u;
const walk = d => fs.readdirSync(d, { withFileTypes: true }).flatMap(e => {
    const p = path.join(d, e.name);
    return e.isDirectory() ? walk(p) : [p];
});
const exts = ['.vue', '.js'];
const roots = process.argv.slice(2);
for (const root of roots) {
    if (!fs.existsSync(root)) continue;
    for (const f of walk(root)) {
        if (!exts.includes(path.extname(f))) continue;
        const lines = fs.readFileSync(f, 'utf8').split('\n');
        lines.forEach((l, i) => {
            if (emojiRe.test(l)) {
                console.log(f + ':' + (i + 1) + ': ' + l.trim().slice(0, 160));
            }
        });
    }
}
