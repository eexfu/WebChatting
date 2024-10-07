import random
import names
import mysql.connector
import pandas as pd
import string
import random
import secrets
import json

def generate_token(length):
    # Generate a secure random token
    token = secrets.token_hex(length)
    return token


def generate_password(length):
    # Define the characters that will be used to generate the password
    characters = string.ascii_letters + string.digits + string.punctuation
    # Generate the password using a random choice among the characters
    password = ''.join(random.choice(characters) for i in range(length))
    return password

def generate_random_teacher(num_names=1):
    """Generate random first and last names along with a teacher number.

    Args:
        num_names (int, optional): Number of random names to generate. Defaults to 1.

    Returns:
        list: List of tuples containing first name, last name, teacher number.
    """

    random_names = [(names.get_first_name().lower(), names.get_last_name().lower(), f"r{i+1}") for i in range(num_names)]

    return random_names

# Connect to the database
db = mysql.connector.connect(
  host="mysql.studev.groept.be",
  user="a23www106",
  password="iWSM2yvM",
  database="a23www106"
)

cursor = db.cursor()

# Create a DataFrame to store the data
df = pd.DataFrame(columns=['First Name', 'Last Name', 'U number', 'Email'])


#to generate some admins jsut change the number of teachers to 10 and the role to ROLE_ADMIN
# Generate 100 random teachers
teacher_names = generate_random_teacher(100)
for name in teacher_names:
    password= generate_password(10)
    token = generate_token(16)
    role = json.dumps(["ROLE_USER"])
    print(f"First Name: {name[0]}, Last Name: {name[1]}, Student Number: {name[2]}, Email: {name[0]}.{name[1]}@student.kuleuven.be")
    sql = "INSERT INTO User (id , firstname, lastname, email, roles, password, isVerified, verificationToken) VALUES (%s, %s, %s, %s,%s,%s,%s, %s)"
    val = (name[2], name[0], name[1], f"{name[0]}.{name[1]}@kuleuven.be", role, hash(password), 1, token)
    cursor.execute(sql, val)

    # Add the data to the DataFrame
    #df = df.append({'First Name': name[0], 'Last Name': name[1], 'Student Number': name[2], 'Email': f"{name[0]}.{name[1]}@kuleuven.be"}, ignore_index=True)

db.commit()

# Save the DataFrame to an Excel file
df.to_excel("Teachers.xlsx", index=False)
