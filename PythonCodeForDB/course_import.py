import random
import mysql.connector
import pandas as pd
from datetime import datetime, timedelta

# Define the days of the week
days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']

#Define the course titles
course_titles = [
    "Introduction to Civil Engineering", "Mechanical Engineering Fundamentals", "Electrical Engineering Basics", "Chemical Engineering Principles", "Aerospace Engineering Overview", "Biomedical Engineering Essentials", "Environmental Engineering Concepts", "Software Engineering Practices", "Structural Engineering Design", "Thermodynamics in Mechanical Engineering", "Circuit Analysis in Electrical Engineering", "Process Control in Chemical Engineering", "Flight Mechanics in Aerospace Engineering", "Medical Imaging in Biomedical Engineering", "Water and Wastewater Treatment in Environmental Engineering", "Object-Oriented Programming in Software Engineering", "Materials Science in Civil Engineering", "Fluid Mechanics in Mechanical Engineering", "Power Systems in Electrical Engineering", "Chemical Reaction Engineering", "Aerodynamics in Aerospace Engineering", "Biomechanics in Biomedical Engineering", "Air Pollution Control in Environmental Engineering", "Database Systems in Software Engineering", "Geotechnical Engineering", "Heat Transfer in Mechanical Engineering", "Control Systems in Electrical Engineering", "Separation Processes in Chemical Engineering", "Propulsion Systems in Aerospace Engineering", "Biomaterials in Biomedical Engineering", "Solid Waste Management in Environmental Engineering", "Data Structures in Software Engineering", "Hydraulic Engineering", "Robotics in Mechanical Engineering", "Digital Signal Processing in Electrical Engineering", "Polymer Engineering in Chemical Engineering", "Navigation Systems in Aerospace Engineering", "Radiology Engineering in Biomedical Engineering", "Renewable Energy Systems in Environmental Engineering", "Operating Systems in Software Engineering", "Construction Engineering", "Automotive Engineering in Mechanical Engineering", "Microelectronics in Electrical Engineering", "Industrial Biotechnology in Chemical Engineering", "Satellite Technology in Aerospace Engineering", "Biomedical Instrumentation in Biomedical Engineering", "Noise Control in Environmental Engineering", "Cloud Computing in Software Engineering", "Architectural Engineering", "Manufacturing Engineering in Mechanical Engineering", "Embedded Systems in Electrical Engineering", "Pharmaceutical Engineering", "Aircraft Structures in Aerospace Engineering", "Bioinformatics in Biomedical Engineering", "Green Building in Environmental Engineering", "Machine Learning in Software Engineering", "Coastal Engineering", "Acoustical Engineering in Mechanical Engineering", "Power Electronics in Electrical Engineering", "Nanotechnology in Chemical Engineering", "Spacecraft Propulsion in Aerospace Engineering", "Biomedical Optics in Biomedical Engineering", "Air Quality Engineering in Environmental Engineering", "Web Development in Software Engineering", "Urban Engineering", "Nuclear Engineering in Mechanical Engineering", "Semiconductor Devices in Electrical Engineering", "Petrochemical Engineering", "Avionics in Aerospace Engineering", "Genetic Engineering in Biomedical Engineering", "Sustainable Engineering in Environmental Engineering", "Artificial Intelligence in Software Engineering", "Bridge Engineering", "Thermal Engineering in Mechanical Engineering", "Wireless Communication in Electrical Engineering", "Metallurgical Engineering",  "Introduction to Data Science",
    "Advanced Machine Learning", "Principles of Microeconomics", "Organic Chemistry Fundamentals", "Modern World History", "Creative Writing Workshop", "Introduction to Philosophy", "Web Development Basics","Digital Marketing Strategies","Environmental Science",
    "Introduction to Psychology","Business Analytics","Quantum Physics","Classical Literature", "Game Design Principles","Artificial Intelligence Concepts","Financial Accounting", "Human Anatomy and Physiology","Introduction to Sociology","Mobile App Development",
    "Cybersecurity Fundamentals","Astronomy: Exploring the Universe","Music Theory Basics", "Entrepreneurship and Innovation"
]

# Define the possible durations of a lecture in minutes
lecture_durations = [60, 90, 120, 150, 180, 210, 240]

# Function to generate a random timestamp within specified hours
def random_time():
    # Generate a random hour and minute within the range 8am to 6pm
    hour = random.choice(range(8, 18)) # 24-hour format
    minute = random.choice([0, 30]) # round hour or half an hour
    return timedelta(hours=hour, minutes=minute).seconds // 60


# Function to generate a random day of the week with a random timestamp
def random_day_time():
    day = random.choice(days_of_week)
    start_time = random_time()
    duration = random.choice(lecture_durations)
    end_time = start_time + duration
    if end_time >= 18*60: # If the end time is after 6pm, adjust it
        end_time = 18*60
    return day, start_time, end_time


# Connect to the database
db = mysql.connector.connect(
  host="mysql.studev.groept.be",
  user="a23www106",
  password="iWSM2yvM",
  database="a23www106"
)

cursor = db.cursor()

# Create a DataFrame to store the data
df = pd.DataFrame(columns=['course_id', 'course_title', 'teacher', 'day', 'begin', 'end'])

for i in range(len(course_titles)):
    course_id = i +1
    course_title = course_titles[i]
    teacher = 'u' + str(i+1)
    day, start_time, end_time = random_day_time()
    begin = str(timedelta(minutes=start_time))[:-3]
    end = str(timedelta(minutes=end_time))[:-3]
    print (course_id, course_title, 'given by :', teacher,'day:', day, 'begin:',begin , 'end:', end)
    sql = "INSERT INTO Courses (course_id, course_title , teacher_id, day, begin, end) VALUES (%s, %s, %s, %s, %s, %s)"
    val = (course_id, course_title, teacher, day, begin, end)
    cursor.execute(sql, val)

    # Add the data to the DataFrame
    df = df._append({'course_id' : course_id, 'course_tile': course_title, 'teacher': teacher, 'day': day, 'begin': begin, 'end' : end}, ignore_index=True)

db.commit()

# Save the DataFrame to an Excel file
df.to_excel("Courses.xlsx", index=False)