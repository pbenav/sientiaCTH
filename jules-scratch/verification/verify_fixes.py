from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()
    try:
        # Intentamos abrir el formulario de regularización.
        page.goto("file:///app/resources/views/livewire/events/exceptional-clock-in.blade.php")
        page.screenshot(path="jules-scratch/verification/verification.png")
    except Exception as e:
        print(f"Error: {e}")
    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
