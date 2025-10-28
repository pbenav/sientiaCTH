from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    # Log in
    page.goto("http://jules.google.com/login")
    page.fill('input[name="email"]', "test@example.com")
    page.fill('input[name="password"]', "password")
    page.click('button[type="submit"]')
    page.wait_for_load_state("networkidle")

    # Navigate to team settings
    page.goto("http://jules.google.com/teams/1")
    page.wait_for_load_state("networkidle")

    # Click the first "Move" button
    page.locator('.mt-10 .space-y-6 > div').first.locator('button:has-text("Move")').click()

    # Select the destination team
    page.wait_for_selector('select#destination_team')
    page.select_option('select#destination_team', label='jules')

    # Click the "Move User" button
    page.locator('button:has-text("Move User")').click()
    page.wait_for_load_state("networkidle")

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
