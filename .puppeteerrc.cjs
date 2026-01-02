/**
 * @type {import("puppeteer").Configuration}
 */
module.exports = {
    // Puppeteer instalará Chromium automáticamente en node_modules/puppeteer/.local-chromium
    // Solo omitir chrome-headless-shell que no necesitamos
    skipChromeHeadlessShellDownload: true,
};
