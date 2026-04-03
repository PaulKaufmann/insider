import { test, expect } from '@playwright/test';

const HOME_PATH = process.env.HOME_PATH ?? '/';
const FEED_PATH = process.env.FEED_PATH ?? '/miteinander';
const FEED_POST_SELECTOR = process.env.FEED_POST_SELECTOR ?? 'article,.post-item,.entry';
// #more_posts ist der feste Selektor aus functions.php:688
// dotenv interpretiert # als Kommentar – daher hartkodiert als Fallback
const LOAD_MORE_SELECTOR = process.env.LOAD_MORE_SELECTOR || '#more_posts';

test.describe('Feed', () => {
  test('feed lädt mindestens einen post', async ({ page }) => {
    await page.goto(HOME_PATH);
    const posts = page.locator(FEED_POST_SELECTOR);
    await expect(posts.first()).toBeVisible({ timeout: 10_000 });
    expect(await posts.count()).toBeGreaterThan(0);
  });

  test('"mehr laden"-button lädt weitere posts nach', async ({ page }) => {
    await page.goto(HOME_PATH);
    const posts = page.locator(FEED_POST_SELECTOR);
    await expect(posts.first()).toBeVisible({ timeout: 10_000 });
    const initialCount = await posts.count();

    const loadMoreBtn = page.locator(LOAD_MORE_SELECTOR).first();
    await expect(loadMoreBtn).toBeVisible({ timeout: 5_000 });
    await loadMoreBtn.click();

    await expect(async () => {
      expect(await posts.count()).toBeGreaterThan(initialCount);
    }).toPass({ timeout: 5_000 });
  });

  test('kategorie-feed lädt posts', async ({ page }) => {
    await page.goto(FEED_PATH);
    const posts = page.locator(FEED_POST_SELECTOR);
    await expect(posts.first()).toBeVisible({ timeout: 10_000 });
    expect(await posts.count()).toBeGreaterThan(0);
  });
});
