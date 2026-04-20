import { expect, test } from '@playwright/test';

test.skip('user can open chatbot modal and receive a reply', async ({ page }) => {
  const suffix = Date.now();
  const email = `playwright_${suffix}@example.test`;
  const password = 'Password123!';

  await page.goto('/register');

  await page.locator('#Name').fill('Playwright User');
  await page.locator('#Email').fill(email);
  await page.locator('#password').fill(password);
  await page.locator('#password-confirm').fill(password);
  await page.locator('#terms').check();

  // Fill required selects (division/district/upazila, gender) used by the registration form JS.
  await page.selectOption('#Gender', 'male');
  await page.selectOption('#Division', { label: 'Dhaka' });
  // Wait for districts to populate then choose Dhaka district and a sample upazila.
  await page.waitForSelector('#District option:has-text("Dhaka")', { state: 'attached' });
  await page.selectOption('#District', { label: 'Dhaka' });
  // Wait for any upazila option to be attached, then pick the first non-placeholder option.
  await page.waitForSelector('#Upazila option:not([value=""])', { state: 'attached' });
  const upazilaValue = await page.locator('#Upazila option:not([value=""])').first().getAttribute('value');
  await page.selectOption('#Upazila', upazilaValue || '');

  // UI shows "Sign Up" for the registration submit button.
  await page.getByRole('button', { name: /sign up/i }).click();

  // Create a verified test user and log in (test-only helper route).
  await page.goto(`/_playwright/create-test-user?email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`);

  // Ensure auth session is established before testing chatbot route/UI.
  await page.goto('/suggestions');
  await expect(page).toHaveURL(/\/suggestions/);

  await page.route('**/chatbot/message', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        reply: 'Mocked chatbot reply from browser E2E test.',
      }),
    });
  });

  await page.locator('.chatbot-icon').click();
  await expect(page.locator('#chatbotModal')).toHaveClass(/show/);

  await page.locator('#chatInput').fill('I have mild headache, what should I do?');
  await page.locator('.chatbot-input button').click();

  await expect(page.locator('#chatMessages')).toContainText('Mocked chatbot reply from browser E2E test.');
});
