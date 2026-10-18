import puppeteer from 'puppeteer-core';
import path from 'path';
import fs from 'fs';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE_URL = 'http://localhost/pub-campusmate/public';
const SCREENSHOT_DIR = path.resolve('artifacts', 'phase5', 'screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function capture() {
    console.log('Launching headless Chrome via puppeteer-core...');
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--window-size=1440,900'
        ]
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900 });

    const takePic = async (filename, waitSelector = null, sleepMs = 800) => {
        if (waitSelector) {
            try {
                await page.waitForSelector(waitSelector, { timeout: 6000 });
            } catch (e) {
                console.log(`Warning: selector ${waitSelector} not found or timed out.`);
            }
        }
        if (sleepMs > 0) {
            await new Promise(r => setTimeout(r, sleepMs));
        }
        const filepath = path.join(SCREENSHOT_DIR, filename);
        await page.screenshot({ path: filepath, fullPage: false });
        console.log(`[SAVED] ${filename} (${fs.statSync(filepath).size} bytes)`);
    };

    const loginAs = async (identifier, password = 'Password@123') => {
        await page.goto(`${BASE_URL}/logout`, { waitUntil: 'networkidle2' });
        await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
        await page.waitForSelector('#identifier');
        await page.type('#identifier', identifier);
        await page.type('#password', password);
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.click('button[type="submit"]')
        ]);
        console.log(`Logged in as ${identifier}. Current URL: ${page.url()}`);
    };

    try {
        // 1. Login Page
        console.log('1. Capturing Login Page...');
        await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
        await takePic('01_login_page.png', '#loginForm', 500);

        // 2. Student Dashboard
        console.log('2. Logging in as Student...');
        await loginAs('student21');
        await takePic('02_student_dashboard.png', '.metric-card', 800);

        // 11. Student Timetable View
        console.log('11. Capturing Student Timetable View...');
        await page.goto(`${BASE_URL}/timetable`, { waitUntil: 'networkidle2' });
        await takePic('11_student_timetable.png', '.routine-card, .table', 800);

        // 12. Mobile-Width Navigation and Timetable View
        console.log('12. Capturing Mobile Viewport Timetable...');
        await page.setViewport({ width: 375, height: 667, isMobile: true, hasTouch: true });
        await page.goto(`${BASE_URL}/timetable`, { waitUntil: 'networkidle2' });
        await takePic('12_mobile_navigation_timetable.png', null, 800);

        // Reset to desktop viewport
        await page.setViewport({ width: 1440, height: 900 });

        // 3. Teacher Dashboard
        console.log('3. Logging in as Teacher...');
        await loginAs('prof_tanvir');
        await takePic('03_teacher_dashboard.png', '.metric-card', 800);

        // 4. Academic Advisor Dashboard
        console.log('4. Logging in as Academic Advisor...');
        await loginAs('advisor_kazi');
        await takePic('04_advisor_dashboard.png', '.metric-card, .table', 800);

        // 5. Department Coordinator Dashboard
        console.log('5. Logging in as Department Coordinator...');
        await loginAs('coord_hasan');
        await takePic('05_coordinator_dashboard.png', '.metric-card', 800);

        // 6. Coordinator Timetable Page
        console.log('6. Capturing Coordinator Timetable Page...');
        await page.goto(`${BASE_URL}/coordinator/timetable`, { waitUntil: 'networkidle2' });
        await takePic('06_coordinator_timetable.png', '.table, .card', 800);

        // 7. Timetable Create Form
        console.log('7. Capturing Timetable Create/Edit Form...');
        await page.goto(`${BASE_URL}/coordinator/timetable/create`, { waitUntil: 'networkidle2' });
        await takePic('07_timetable_create_form.png', 'form', 800);

        // 8. Examination Schedule Page
        console.log('8. Capturing Examination Schedule Page...');
        await page.goto(`${BASE_URL}/coordinator/exams`, { waitUntil: 'networkidle2' });
        await takePic('08_exam_schedule_page.png', '.table, .card', 800);

        // 9. Make-up Class Page
        console.log('9. Capturing Make-up Class Page...');
        await page.goto(`${BASE_URL}/coordinator/makeup-classes`, { waitUntil: 'networkidle2' });
        await takePic('09_makeup_class_page.png', '.table, .card', 800);

        // 10. Schedule Revision History Page
        console.log('10. Capturing Schedule Revision History...');
        await page.goto(`${BASE_URL}/coordinator/timetable/revisions`, { waitUntil: 'networkidle2' });
        await takePic('10_revision_history.png', '.table, .card', 800);

        console.log('=== All 12 screenshots captured successfully! ===');
    } catch (err) {
        console.error('Error during screenshot capture:', err);
    } finally {
        await browser.close();
    }
}

capture();
