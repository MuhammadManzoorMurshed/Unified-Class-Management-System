# UCMS: Unified Class Management System

## Introduction

UCMS is a web-based platform designed to centralize and organize all class-related activities for both teachers and students in universities. The system aims to eliminate the need for scattered tools like Google Classroom, WhatsApp, and Facebook Messenger by offering a single platform to manage assignments, attendance, marks, announcements, and resources. The goal is to provide a seamless and efficient experience for all academic stakeholders.

## Project Objectives

* **Role-Based Access Control**: Different user roles (Teacher, Student, Admin) with tailored access to relevant features.
* **Real-Time Updates**: Notifications for class announcements, assignments, deadlines, and other updates.
* **Deadline Tracking**: Clear management of upcoming tasks with deadlines for both teachers and students.
* **Communication & Collaboration**: Features like one-on-one chat, group discussions, and forums for better interaction.
* **Scalable Architecture**: Future-proof design allowing easy addition of new features like live classes, online quizzes, and third-party integrations.

## Key Features

* **User Roles & Access Control**: Teachers can manage classes, assignments, and attendance; Students can submit assignments, view grades, and communicate with teachers.
* **Class Creation & Management**: Teachers create and manage courses, and students can join with a unique code or link.
* **Assignments & Submissions**: Teachers post assignments with deadlines; Students submit assignments and receive feedback.
* **Attendance Tracking**: Automated attendance tracking with real-time updates.
* **Exams & Marks**: Teachers can enter marks for various assessments and students can view their grades.
* **Chat & Discussion Forums**: Both one-on-one and group chat options for seamless communication.
* **Resource Sharing**: Teachers can upload study materials such as PDFs, PPTs, and videos for students to access.

## Target Users

* **Teachers**: Can manage classes, post assignments, track attendance, and communicate with students.
* **Students**: Can join classes, submit assignments, view grades, and communicate with teachers and peers.
* **Admin (Optional)**: Manages user roles, monitors activity, and maintains platform stability.

## Tools & Technologies

* **Frontend**: HTML, CSS, JavaScript, Tailwind CSS
* **Backend**: Laravel (PHP MVC Framework)
* **Database**: MySQL
* **Authentication**: JWT/OAuth
* **Real-Time Communication**: Socket.io
* **Version Control**: Git & GitHub
* **Project Management**: Jira, VS Code, Postman

## Software Process Model

* **Agile Development**: Using sprints to deliver incremental features, gather user feedback, and make continuous improvements.

## Non-Functional Requirements

* **Security**: Role-based access control, encrypted credentials, and audit logs.
* **Performance**: Low latency, scalable infrastructure to handle growing user base.
* **Usability**: Responsive design for all devices and accessibility features for users with visual impairments.

## Future Enhancements

* **Live Class Integration**: Conduct live classes and meetings directly within the platform.
* **Quiz/Test Builder**: Enable teachers to create automated quizzes and tests with instant grading.
* **Analytics Dashboard**: Visualize performance trends for both students and teachers.
* **Mobile App & Multi-Language Support**: Extend the platform with an Android and iOS app, and support multiple languages.

## Installation

1. Clone the repository:

   ```
   git clone https://github.com/MuhammadManzoorMurshed/Unified-Class-Management-System.git
   ```

2. Install dependencies:

   ```
   composer install
   npm install
   ```

3. Set up the environment file:

   ```
   cp .env.example .env
   ```

4. Generate the application key:

   ```
   php artisan key:generate
   ```

5. Run migrations:

   ```
   php artisan migrate
   ```

6. Serve the application:

   ```
   php artisan serve
   ```

## Contributing

We welcome contributions to the UCMS project! Please fork this repository, create a new branch for your changes, and submit a pull request with a clear description of your updates.

## License

Distributed under the MIT License. See `LICENSE` for more information.
