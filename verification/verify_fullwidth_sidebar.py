from playwright.sync_api import sync_playwright

def run_verification(page):
    # 1. Full-Width Dashboard View with Left Sidebar
    page.goto("http://localhost:8088")
    page.wait_for_timeout(1000)
    page.screenshot(path="/home/jules/verification/screenshots/fullwidth_sidebar_overview.png")

    # 2. Click "Create Daily Report" in Left Sidebar
    page.click(".csa-sidebar-btn[data-tab='csa_tab_create']")
    page.wait_for_timeout(500)
    page.screenshot(path="/home/jules/verification/screenshots/fullwidth_sidebar_create.png")

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            viewport={"width": 1440, "height": 900},
            record_video_dir="/home/jules/verification/videos"
        )
        page = context.new_page()
        try:
            run_verification(page)
        finally:
            context.close()
            browser.close()
