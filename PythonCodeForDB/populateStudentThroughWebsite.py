import requests
from bs4 import BeautifulSoup
import random
import names
import string
import urllib3
import time

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

def generate_password(length):
    characters = string.ascii_letters + string.digits + string.punctuation
    password = ''.join(random.choice(characters) for i in range(length))
    return password

def generate_random_student():
    random_first_name = names.get_first_name().lower()
    random_last_name = names.get_last_name().lower()
    random_email = random_first_name + random_last_name + "@student.kuleuven.be"
    return [random_first_name, random_last_name, random_email]

for i in range(100):
    time.sleep(1)
    registration_url = 'http://a23www106.studev.groept.be/public/register'
    register_url = 'https://a23www106.studev.groept.be/public/register'
    session = requests.Session()

    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    })

    try:
        response = session.get(register_url, timeout=20, verify=False)  # Increased timeout
        print(response)

        if response.status_code != 200:
            print('Failed to load the registration page')
        else:
            soup = BeautifulSoup(response.text, 'html.parser')
            csrf_token = soup.find('input', {'name': 'registration_form[_token]'})
            if csrf_token:
                csrf_token = csrf_token.get('value')
                print(f"CSRF Token: {csrf_token}")
            else:
                print('CSRF token not found')
                exit(1)

            user_id = "r" + str(i + 5000)
            random_user = generate_random_student()
            random_password = generate_password(12)

            form_data = {
                'registration_form[id]': user_id,
                'registration_form[email]': random_user[2],
                'registration_form[firstName]': random_user[0],
                'registration_form[lastName]': random_user[1],
                'registration_form[agreeTerms]': '1',
                'registration_form[plainPassword]': 'TestPassword',
                'registration_form[_token]': csrf_token,
            }

            print("Form Data:")
            for key, value in form_data.items():
                print(f"{key}: {value}")

            # Retry logic
            retries = 5
            for attempt in range(retries):
                try:
                    response = session.post(register_url, data=form_data, timeout=20, verify=False)  # Increased timeout
                    print(response)

                    if response.status_code == 200:
                        print('Registration successful. Please check your email for verification.')
                    else:
                        print('Registration failed. Check the form data and try again.')

                    soup = BeautifulSoup(response.text, 'html.parser')
                    error_elements = soup.find_all(class_='form-error-message')
                    if error_elements:
                        for error in error_elements:
                            print(f"Validation Error: {error.text.strip()}")
                    else:
                        print("No specific validation errors found in the response content.")
                    break
                except requests.exceptions.RequestException as e:
                    print(f'Attempt {attempt + 1} failed: {e}')
                    time.sleep(1)  # Wait before retrying

    except requests.exceptions.RequestException as e:
        print(f'An error occurred: {e}')
