import random
import names
import mysql.connector
import pandas as pd
import string
import random
import secrets
import json


# List of possible announcements for students
announcements = [
    "📚 Dear students, please remember to review the chapters discussed this week for our upcoming quiz.",
    "🌐 For those interested in extra credit, there will be a guest lecture on global economics this Thursday.",
    "📝 Reminder: Your term paper proposals are due next Monday. Please submit them on time.",
    "🔬 Lab sessions will be extended by 30 minutes next week. Adjust your schedules accordingly.",
    "🎓 Graduating students, make sure to confirm your attendance for the commencement ceremony.",
    "🏛️ The library will have reduced hours during the spring break. Plan your visits accordingly.",
    "👥 Group project teams have been posted on the course website. Check your assignments!",
    "📘 Attention students: The deadline for scholarship applications has been extended to the end of the month. Don't miss this opportunity!",
"🌟 A reminder that our department's annual research symposium is accepting submissions. Showcase your work!","📅 Office hours update: I will be available for extra office hours this Friday for any questions regarding the midterm exam.",
"💡 There will be a workshop on academic writing next Wednesday in the main auditorium. Highly beneficial for your thesis work!",
"🌍 Study abroad applications for the next academic year are now open. Explore the programs available and broaden your horizons.",
"🔎 The university career center is hosting a job fair next month. A great chance to network and find internships!",
"🏆 Congratulations to those who participated in the innovation challenge. The winning projects will be displayed in the student center.",
"🤝 We're looking for volunteers for the upcoming community service day. A wonderful way to give back and earn service credits.",
"📚 The new edition of the core textbook is now available in the library reserve. Please use it for reference as it contains the latest updates.",
"🎤 Guest speaker announcement: Next week, a Nobel laureate in Physics will be discussing quantum computing advancements. Don't miss it!"
]



# Connect to the database
db = mysql.connector.connect(
  host="mysql.studev.groept.be",
  user="a23www106",
  password="iWSM2yvM",
  database="a23www106"
)

cursor = db.cursor()


# Generate 100 random announcements
for i in range(100):
    id = i +1
    userId = "u" + str(random.randint(1, 100))
    courseId = random.randint(1, 100)
    announcement = random.choice(announcements)
    sql = "INSERT INTO Announcements (id, user_id, course_id, announcement) VALUES (%s, %s, %s, %s)"
    val = (id, userId, courseId, announcement)
    cursor.execute(sql, val)
db.commit()

