from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

# Set up the Chrome WebDriver
service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service)

def test_login(email, password, expected_url_part):
    try:
        # Navigate to the login page
        driver.get("http://localhost/AshesiSmartDiner/Login/login.php")

        # Wait for the email field to be present
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "login-email")))

        # Fill in the login form
        driver.find_element(By.ID, "login-email").send_keys(email)
        driver.find_element(By.ID, "login-password").send_keys(password)

        # Submit the form
        driver.find_element(By.CLASS_NAME, "btn-login").click()

        # Wait for the page to load and check the URL
        WebDriverWait(driver, 10).until(EC.url_changes(driver.current_url))
        current_url = driver.current_url

        # Verify the redirection based on the user's role
        if expected_url_part in current_url:
            print(f"Redirected to {expected_url_part}")
        else:
            print(f"Unexpected redirection. Current URL: {current_url}")

    finally:
        # Close the browser
        driver.quit()

# Test with Student credentials
#test_login("john.nani@ashesi.edu.gh", "password123", "http://localhost/AshesiSmartDiner/View_Folder/studentHome.php")

# Test with Admin credentials
test_login("jackline.mpaye@ashesi.edu.gh", "1234567890", "http://localhost/AshesiSmartDiner/View_Folder/admin_dashboard.php")
