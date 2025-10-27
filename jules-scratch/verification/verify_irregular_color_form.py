
from playwright.sync_api import sync_playwright, expect
import os

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    # Navigate to the login page
    page.goto("http://127.0.0.1:8000/login", wait_until="networkidle")

    # Fill in the login form and submit
    page.get_by_label("Correo electrónico").fill("test@example.com")
    page.get_by_label("Contraseña").fill("password")
    page.get_by_role("button", name="Entrar").click()

    # Wait for navigation to the dashboard and then go to team settings
    expect(page).to_have_url("http://127.0.0.1:8000/dashboard")
    page.goto("http://127.0.0.1:8000/teams/1")

    # Click on the "Event Management" tab
    page.get_by_role("button", name="Gestión de eventos").click()

    # Check if the "Irregular Event Color" section is visible
    irregular_color_section = page.locator("text=Irregular Event Color")
    expect(irregular_color_section).to_be_visible()

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
