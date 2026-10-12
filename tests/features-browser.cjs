const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
    await page.goto('http://127.0.0.1:8091/HRMS/login.php');
    await page.locator('input[name="email"]').fill('admin@feature.example');
    await page.locator('input[name="password"]').fill('TemporaryFeaturePassword!');
    await Promise.all([page.waitForURL('**/admin/dashboard.php'), page.locator('button[type="submit"]').click()]);
    await page.goto('http://127.0.0.1:8091/HRMS/admin/reports.php?year=2026&month=10&chart=pie');
    await page.locator('.pie').waitFor();
    const gradient = await page.locator('.pie').evaluate(element => getComputedStyle(element).backgroundImage);
    if (!gradient.includes('rgb(59, 130, 246)') || !gradient.includes('rgb(248, 113, 113)')) throw Error('Attendance pie colors are incorrect');
    if (!(await page.locator('main').innerText()).includes('Oct 02, 2026 12:00 AM')) throw Error('Report time is not AM/PM');
    const downloadPromise = page.waitForEvent('download');
    await page.getByRole('link', { name: 'Download CSV' }).click();
    const download = await downloadPromise;
    if (!download.suggestedFilename().endsWith('.csv')) throw Error('CSV filename missing');
    const csv = fs.readFileSync(await download.path(), 'utf8');
    if (!csv.includes('Admin accomplishment') || !csv.includes('12:00 AM') || csv.includes('<html')) throw Error('CSV download is incomplete');
    await page.context().addInitScript(() => { window.print = () => { window.__printRequested = true; }; });
    const popupPromise = page.waitForEvent('popup');
    await page.getByRole('link', { name: 'Print / Save PDF' }).click();
    const printPage = await popupPromise;
    await printPage.waitForLoadState();
    await printPage.waitForFunction(() => window.__printRequested === true);
    if (await printPage.locator('.sidebar').count() !== 0) throw Error('Print view includes navigation');
    await printPage.emulateMedia({ media: 'print' });
    await printPage.waitForTimeout(300);
    const printLayout = await printPage.locator('.main').evaluate(element => ({ margin: getComputedStyle(element).marginLeft, background: getComputedStyle(document.body).backgroundColor }));
    if (printLayout.margin !== '0px' || printLayout.background !== 'rgb(255, 255, 255)') throw Error('Print layout is not suitable for PDF: ' + JSON.stringify(printLayout));
    await printPage.screenshot({ path: path.join(__dirname, 'features-report-print.png'), fullPage: true });
    await printPage.close();
    await page.screenshot({ path: path.join(__dirname, 'features-report-light.png'), fullPage: true });
    await page.locator('.theme-toggle-btn:visible').click();
    await page.waitForTimeout(300);
    await page.screenshot({ path: path.join(__dirname, 'features-report-dark.png'), fullPage: true });
    await page.locator('select[name="chart"]').selectOption('bar');
    await Promise.all([page.waitForURL('**/admin/reports.php?**chart=bar'), page.getByRole('button', { name: 'Create report' }).click()]);
    if (await page.locator('.report-bar').count() < 3) throw Error('Report bars missing');
    const bars = await page.locator('.report-grid section').first().locator('.report-bar').evaluateAll(elements => elements.map(element => getComputedStyle(element).backgroundColor));
    if (bars[0] !== 'rgb(59, 130, 246)' || bars[2] !== 'rgb(248, 113, 113)') throw Error('Attendance bar colors are incorrect');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.screenshot({ path: path.join(__dirname, 'features-report-mobile.png'), fullPage: true });
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    if (overflow) {
      console.error(await page.evaluate(() => [...document.querySelectorAll('body *')].filter(element => element.getBoundingClientRect().right > innerWidth + 1 && getComputedStyle(element).position !== 'absolute').slice(0, 12).map(element => ({tag: element.tagName, className: element.className, width: element.getBoundingClientRect().width}))));
      throw Error('Mobile page exceeds viewport');
    }
    console.log('PASS report browser checks: CSV download, print action/layout, AM/PM, chart colors, light, dark and mobile');
    let buttonCount = 0;
    const responsiveFailures = [];
    const rolePages = {
      admin: ['dashboard.php', 'create_user_account.php', 'departments.php', 'attendance.php', 'holidays.php', 'login_logs.php', 'reports.php', 'documents.php', 'evaluations.php', 'kpi.php', 'payroll.php'],
      hr: ['dashboard.php', 'hr_leave_requests.php', 'attendance.php', 'reports.php', '201_files.php', 'documents.php', 'evaluations.php', 'kpi.php', 'task_submissions.php', 'payroll.php'],
      employee: ['dashboard.php', 'request_leave.php', 'attendance.php', 'reports.php', 'documents.php', 'kpi.php', 'payroll.php'],
    };
    for (const [role, paths] of Object.entries(rolePages)) {
      const auditPage = await browser.newPage();
      try {
        await auditPage.goto('http://127.0.0.1:8091/HRMS/login.php');
        await auditPage.locator('input[name="email"]').fill(role + '@feature.example');
        await auditPage.locator('input[name="password"]').fill('TemporaryFeaturePassword!');
        await Promise.all([auditPage.waitForURL('**/' + role + '/dashboard.php'), auditPage.locator('button[type="submit"]').click()]);
        for (const file of paths) {
          await auditPage.setViewportSize({ width: 1440, height: 900 });
          await auditPage.goto('http://127.0.0.1:8091/HRMS/' + role + '/' + file);
          for (const theme of ['light', 'dark']) {
            await auditPage.evaluate(value => document.documentElement.setAttribute('data-theme', value), theme);
            const buttons = await auditPage.locator('button, input[type="submit"], input[type="button"], input[type="reset"]').evaluateAll(elements => elements.map(element => {
              const style = getComputedStyle(element);
              return { text: element.textContent.trim(), appearance: style.appearance, radius: parseFloat(style.borderTopLeftRadius), padding: parseFloat(style.paddingLeft), cursor: style.cursor, disabled: element.disabled };
            }));
            const failures = buttons.filter(button => button.appearance !== 'none' || button.radius < 4 || button.padding < 6 || button.cursor !== (button.disabled ? 'not-allowed' : 'pointer'));
            if (failures.length) throw Error('Unstyled buttons on ' + role + '/' + file + ' (' + theme + '): ' + JSON.stringify(failures));
            buttonCount += buttons.length;
          }
          for (const width of [320, 390, 768, 1024, 1440]) {
            await auditPage.setViewportSize({ width, height: 900 });
            await auditPage.waitForTimeout(50);
            const layout = await auditPage.evaluate(() => {
              const main = document.querySelector('.main');
              return { width: document.documentElement.scrollWidth, viewport: innerWidth, mainWidth: main.getBoundingClientRect().width, menuVisible: getComputedStyle(document.querySelector('.mobile-header')).display !== 'none' };
            });
            if (layout.width > width + 1 || layout.mainWidth > width + 1 || layout.menuVisible !== (width <= 1024)) {
              console.error(await auditPage.evaluate(() => [...document.querySelectorAll('.main *')].filter(element => element.getBoundingClientRect().right > innerWidth + 1).slice(0, 15).map(element => ({tag: element.tagName, className: element.className, width: element.getBoundingClientRect().width}))));
              responsiveFailures.push(role + '/' + file + ' at ' + width + ': ' + JSON.stringify(layout));
            }
          }
          if (file === 'dashboard.php') {
            await auditPage.setViewportSize({ width: 390, height: 844 });
            await auditPage.locator('#sidebar-toggle').click();
            await auditPage.waitForTimeout(250);
            if (!(await auditPage.locator('#sidebar-toggle').getAttribute('aria-expanded') === 'true')) throw Error('Menu did not open');
            await auditPage.screenshot({ path: path.join(__dirname, 'features-' + role + '-mobile-menu.png'), fullPage: false });
            await auditPage.locator('#sidebar-close').click();
            if (await auditPage.locator('#sidebar-toggle').getAttribute('aria-expanded') !== 'false') throw Error('X did not close menu');
            await auditPage.locator('#sidebar-toggle').click();
            await auditPage.keyboard.press('Escape');
            if (await auditPage.locator('#sidebar-toggle').getAttribute('aria-expanded') !== 'false') throw Error('Escape did not close menu');
            await auditPage.locator('#sidebar-toggle').click();
            await auditPage.locator('#sidebar-backdrop').click({ position: { x: 380, y: 400 } });
            if (await auditPage.locator('#sidebar-toggle').getAttribute('aria-expanded') !== 'false') throw Error('Outside click did not close menu');
            await auditPage.locator('#sidebar-toggle').click();
            await auditPage.setViewportSize({ width: 1440, height: 900 });
            if (await auditPage.locator('#app-sidebar').evaluate(element => element.inert)) throw Error('Desktop menu stayed inert after resizing');
            await auditPage.setViewportSize({ width: 390, height: 844 });
            await auditPage.waitForTimeout(250);
            await auditPage.screenshot({ path: path.join(__dirname, 'features-' + role + '-mobile.png'), fullPage: true });
          }
        }
      } finally { await auditPage.close(); }
    }
    console.log('PASS shared button styling: ' + buttonCount + ' controls checked across all roles in light and dark themes');
    if (responsiveFailures.length) throw Error('Responsive layout failures: ' + responsiveFailures.join('; '));
    console.log('PASS responsive layouts: all role pages at 320, 390, 768, 1024 and 1440 pixels; hamburger, X, outside click, Escape and resize');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
