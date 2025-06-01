# Joshy Lucas University Registration System

## Overview
The Joshy Lucas University Registration System is a web application designed to facilitate student registration and management. It allows students to register for their chosen programs, log in to their accounts, and access various features related to their studies.

## Project Structure
```
Joshy_lucas_university_v01
├── config
│   └── database.php          # Database connection settings
├── dashboard
│   └── index.php            # Main dashboard for logged-in users
├── src
│   └── migrations
│       └── create_tables.sql # SQL statements to create necessary tables
├── register.php              # Handles the registration process
├── registration.html         # Front-end form for student registration
├── login.php                 # Manages the login process
└── README.md                 # Project documentation
```

## Features
- **Student Registration**: Users can register by providing their name, phone number, gender, email, password, and selected program.
- **User Authentication**: Students can log in to their accounts using their credentials.
- **Dashboard Access**: After logging in, users are redirected to a dashboard where they can manage their account and access program-related information.

## Setup Instructions
1. **Clone the Repository**: Clone this repository to your local machine.
2. **Database Configuration**: Update the `config/database.php` file with your database connection settings.
3. **Create Database Tables**: Run the SQL statements in `src/migrations/create_tables.sql` to set up the necessary tables in your database.
4. **Start the Server**: Use a local server environment (like XAMPP) to run the application.
5. **Access the Application**: Open your web browser and navigate to `http://localhost/Joshy_lucas_university_v01/registration.html` to start the registration process.

## Usage Guidelines
- Ensure all fields in the registration form are filled out correctly.
- Passwords must be at least 8 characters long.
- Use a valid email format for registration.
- After successful registration, users will receive a unique registration number for their chosen program.

## Contributing
Contributions to improve the project are welcome. Please fork the repository and submit a pull request with your changes.