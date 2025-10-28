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
    page.wait_for_selector('button > img')
    page.click('button > img')
    page.click('a[href="http://jules.google.com/teams/1"]')
    page.wait_for_load_state("networkidle")

    # Click on the "User Management" tab
    page.click('button:has-text("User Management")')
    page.wait_for_load_state("networkidle")

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
