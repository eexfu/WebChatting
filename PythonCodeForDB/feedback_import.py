import random
import mysql.connector
import pandas as pd
from datetime import datetime, timedelta

def random_date(start_date, end_date):
    return start_date + timedelta(
        seconds=random.randint(0, int((end_date - start_date).total_seconds())))

start_date = datetime(2023, 1, 1)
end_date = datetime(2024, 5, 1)






#Generate random message
def generate_random_message():
    feedback_messages = [
    "The course content was comprehensive and well-structured.",
    "The professor was knowledgeable and approachable.",
    "The course materials were not updated regularly.",
    "The professor's lectures were engaging and insightful.",
    "The course workload was too heavy.",
    "The professor was not clear in explaining complex concepts.",
    "The course was very informative and useful.",
    "The professor was not available for doubts outside of class hours.",
    "The course assignments were challenging but helped in learning.",
    "The professor encouraged active participation in class.",
    "The course could benefit from more real-world examples.",
    "The professor's teaching style was not effective for me.",
    "The course provided a good theoretical foundation.",
    "The professor was very supportive and provided constructive feedback.",
    "The course lacked practical applications of the concepts.",
    "The professor often deviated from the course syllabus.",
    "The course was well-paced and manageable.",
    "The professor's grading was fair and transparent.",
    "The course required a lot of self-study and research.",
    "The professor was enthusiastic and passionate about the subject.",
    "The course did not meet my expectations.",
    "The professor was slow in responding to queries.",
    "The course was highly relevant to my field of study.",
    "The professor provided valuable career advice and guidance.",
    "The course was well-organized and the syllabus was clear.",
    "The professor was very patient and took time to explain concepts.",
    "The course lacked interactive elements and was mostly lecture-based.",
    "The professor was very approachable and open to questions.",
    "The course materials were not sufficient for understanding the subject.",
    "The professor was very knowledgeable but failed to simplify complex topics.",
    "The course was very practical with lots of hands-on assignments.",
    "The professor was very inspiring and motivated us to learn more.",
    "The course was too theoretical with little focus on practical applications.",
    "The professor was punctual and respected the class timings.",
    "The course was very relevant to current industry trends.",
    "The professor was very supportive during project work.",
    "The course could use more guest lectures from industry experts.",
    "The professor's feedback on assignments was very helpful.",
    "The course was very demanding and required a lot of time and effort.",
    "The professor was very engaging and made the classes interesting.",
    "The course was not very challenging and did not push me to learn more.",
    "The professor was very strict about deadlines and submissions.",
    "The course was very flexible and allowed me to learn at my own pace.",
    "The professor was very understanding and accommodated personal circumstances."
]

    return random.choice(feedback_messages)

# Connect to the database
db = mysql.connector.connect(
  host="mysql.studev.groept.be",
  user="a23www106",
  password="iWSM2yvM",
  database="a23www106"
)

cursor = db.cursor()

# Create a DataFrame to store the data
df = pd.DataFrame(columns=['id', 'feedback_string', 'course_id', 'student_id', 'datetime'])


for i in range(1000):
    feedback_id = i+1
    student_id = ('r' + str(random.randint(1, 100)))
    course_id = random.randint(1, 100)
    date = random_date(start_date, end_date)
    feedback = generate_random_message()
    print(f"feedback_id: {feedback_id} feedback_string: {feedback} course_id: {course_id} student_id: {student_id} date: {date}")
    sql = "INSERT INTO Feedback (id, feedback_string , course_id, student_id, date) VALUES (%s, %s, %s, %s, %s)"
    val = (feedback_id, feedback, course_id, student_id, date)
    cursor.execute(sql, val)

    # Add the data to the DataFrame
    df = df._append({'id' : i+1, 'feedback_string': feedback, 'course_id': course_id, 'student_id': student_id, 'date': date}, ignore_index=True)

db.commit()

# Save the DataFrame to an Excel file
df.to_excel("Feedback.xlsx", index=False)