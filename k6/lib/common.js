import http from 'k6/http';
import { check, sleep } from 'k6';

export const BASE_URL = 'http://nginx:80';
export const TEST_PASSWORD = '1234567890';

export function login(email) {
    const loginPage = http.get(`${BASE_URL}/login`);
    check(loginPage, { 'login page loaded': (r) => r.status === 200 });

    let token = '';
    const metaMatch = loginPage.body.match(/<meta name="csrf-token" content="([^"]+)"/);
    if (metaMatch) {
        token = metaMatch[1];
    } else {
        const inputMatch = loginPage.body.match(/name="_token" value="([^"]+)"/);
        if (inputMatch) token = inputMatch[1];
    }

    check(token, { 'CSRF token found': (t) => t.length > 0 });

    const loginRes = http.post(
        `${BASE_URL}/login`,
        {
            email,
            password: TEST_PASSWORD,
            _token: token,
        },
        { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } },
    );

    check(loginRes, {
        'login successful': (r) => r.status === 302 || r.status === 200,
    });

    return token;
}

export function getFreshToken() {
    const uploadPage = http.get(`${BASE_URL}/videos/create`);

    let token = '';
    const metaMatch = uploadPage.body.match(/<meta name="csrf-token" content="([^"]+)"/);
    if (metaMatch) {
        token = metaMatch[1];
    } else {
        const inputMatch = uploadPage.body.match(/name="_token" value="([^"]+)"/);
        if (inputMatch) token = inputMatch[1];
    }

    check(token, { 'fresh CSRF token': (t) => t.length > 0 });
    return token;
}

export function loadTestEmail(vu = __VU) {
    const index = ((vu - 1) % 100) + 1;
    return `loadtest${index}@example.com`;
}

export function thinkTime(minSeconds = 2, maxSeconds = 5) {
    sleep(minSeconds + Math.random() * (maxSeconds - minSeconds));
}
