# Importar las clases necesarias de Playwright
from playwright.sync_api import sync_playwright

def run(playwright):
    # Lanzar el navegador
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # Navegar a la página de inicio de sesión
    page.goto("http://jules.google.com/login")

    # Rellenar el formulario de inicio de sesión
    page.get_by_label("Email").fill("admin@example.com")
    page.get_by_label("Password").fill("password")
    page.get_by_role("button", name="Log in").click()

    # Esperar a que la página de configuración del equipo se cargue
    page.wait_for_url("http://jules.google.com/teams/*")

    # Hacer una captura de pantalla del formulario
    page.screenshot(path="jules-scratch/verification/irregular-event-color-form.png")

    # Cerrar el navegador
    browser.close()

with sync_playwright() as playwright:
    run(playwright)
