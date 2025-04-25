from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import Select

# Set up the Chrome WebDriver
service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service)

try:
    # Navigate to the registration page
    driver.get("http://localhost/AshesiSmartDiner/Login/register.php")

    # Wait for the registration form to be present
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "signup-username")))

    # Fill in the registration form
    driver.find_element(By.ID, "signup-username").send_keys("John Doe")
    driver.find_element(By.ID, "signup-email").send_keys("john.doe@ashesi.edu.gh")
    driver.find_element(By.ID, "signup-password").send_keys("SecurePassword123")

    # Select the role from the dropdown
    role_select = Select(driver.find_element(By.ID, "role"))
    role_select.select_by_visible_text("Student")  # or "Admin" based on your test case

    # Submit the form
    driver.find_element(By.CLASS_NAME, "btn-signup").click()

    # Wait for the success message to be present
    WebDriverWait(driver, 10).until(
        EC.text_to_be_present_in_element((By.CLASS_NAME, "success-message"), "Registration successful!")
    )

    # Verify the success message
    success_message = driver.find_element(By.CLASS_NAME, "success-message").text
    assert "Registration successful!" in success_message
    print("Registration test passed.")

except Exception as e:
    print(f"Registration test failed: {e}")

finally:
    # Close the browser
    driver.quit()
