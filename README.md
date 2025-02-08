# University of Arusha Website

This project is a website for the University of Arusha, providing information about the university, its academic programs, admissions process, research, student life, and contact details. The website also includes a registration form for new students.

## Table of Contents
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Contributing](#contributing)
- [License](#license)

## Project Structure

about.html
Academics.html
Admissions.html
api/
    check_session.php
    get_user_data.php
    home.html
    logout.php
    register.php
app.js
config/
    database.php
contact.html
database.php
database.sql
home.html
images/
    1.png
    2.png
    3.png
    4.png
    image1.JPG
    image2.JPG
    test.webp
    test1.jpg
    test2.jpg
index.html
login.html
logout.html
main.js
register.php
registration.html
registration.js
research.html
script.js
student-life.html
style.css


## Installation
1. Clone the repository to your local machine:

git clone https://github.com/joshhy12/university-of-arusha.git


2. Navigate to the project directory:

cd university-of-arusha


3. Set up a local web server (e.g., XAMPP, WAMP, MAMP) and place the project files in the server's root directory (e.g., htdocs for XAMPP).

4. Import the database:
   - Open your database management tool (e.g., phpMyAdmin)
   - Create a new database named `university_database`
   - Import the `database.sql` file into the newly created database

5. Configure the database connection:
   - Open the `database.php` file
   - Update the database connection details (host, dbname, username, password) as per your local setup

## Usage
1. Start your local web server.

2. Open a web browser and navigate to [http://localhost/university-of-arusha](http://localhost/university-of-arusha) (or the appropriate URL based on your server setup).

3. Explore the website to learn more about the University of Arusha, its programs, and other information.

4. To register as a new student, navigate to the "Apply Now" link and fill out the registration form.

## Features
- Responsive design for various screen sizes
- Navigation menu with links to different sections of the website
- Registration form with validation
- User session management (login, logout)
- Dynamic content loading using JavaScript and AJAX

## Technologies Used
- HTML
- CSS
- JavaScript (jQuery)
- PHP
- MySQL